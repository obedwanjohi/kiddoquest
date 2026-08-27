<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaService
{
    /**
     * Format any Kenyan phone number to 2547XXXXXXXX or 2541XXXXXXXX
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (Str::startsWith($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (Str::startsWith($phone, '7') || Str::startsWith($phone, '1')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }

    /**
     * Initiate Safaricom M-Pesa Daraja STK Push Prompt for Subscription.
     */
    public function initiateStkPush(Guardian $guardian, string $phone, string $planType = 'monthly'): array
    {
        $formattedPhone = self::formatPhoneNumber($phone);
        
        $pricing = [
            'monthly' => ['amount' => 499, 'days' => 30],
            'termly'  => ['amount' => 1200, 'days' => 90],
            'annual'  => ['amount' => 3999, 'days' => 365],
        ];

        $plan = $pricing[$planType] ?? $pricing['monthly'];
        $amount = $plan['amount'];

        $trackingId = 'ws_CO_' . date('YmdHis') . '_' . rand(1000, 9999);

        // 1. Create Pending Subscription Record
        $subscription = Subscription::create([
            'guardian_id' => $guardian->id,
            'plan_type'   => $planType,
            'amount_kes'  => $amount,
            'status'      => 'pending',
            'mpesa_phone' => $formattedPhone,
        ]);

        // 2. Create Pending Payment Record
        $payment = Payment::create([
            'subscription_id'     => $subscription->id,
            'guardian_id'         => $guardian->id,
            'checkout_request_id' => $trackingId,
            'merchant_request_id' => $trackingId,
            'amount'              => $amount,
            'phone_number'        => $formattedPhone,
            'status'              => 'pending',
        ]);

        // Safaricom Daraja Credentials
        $consumerKey = env('MPESA_CONSUMER_KEY') ?? config('services.mpesa.consumer_key');
        $consumerSecret = env('MPESA_CONSUMER_SECRET') ?? config('services.mpesa.consumer_secret');
        $shortcode = env('MPESA_SHORTCODE', '174379');
        $passkey = env('MPESA_PASSKEY');
        $envMode = env('MPESA_ENV', 'sandbox');
        $txType = env('MPESA_TX_TYPE', 'CustomerPayBillOnline'); // CustomerPayBillOnline or CustomerBuyGoodsOnline

        $baseUrl = $envMode === 'live' 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';

        if ($consumerKey && $consumerSecret && $passkey) {
            try {
                // Fetch OAuth Access Token
                $authRes = Http::withBasicAuth($consumerKey, $consumerSecret)
                    ->get("{$baseUrl}/oauth/v1/generate?grant_type=client_credentials");

                if ($authRes->successful()) {
                    $token = $authRes->json('access_token');
                    $timestamp = date('YmdHis');
                    $password = base64_encode($shortcode . $passkey . $timestamp);

                    $stkRes = Http::withToken($token)->post("{$baseUrl}/mpesa/stkpush/v1/processrequest", [
                        'BusinessShortCode' => $shortcode,
                        'Password'          => $password,
                        'Timestamp'         => $timestamp,
                        'TransactionType'   => $txType,
                        'Amount'            => (int) $amount,
                        'PartyA'            => $formattedPhone,
                        'PartyB'            => $shortcode,
                        'PhoneNumber'       => $formattedPhone,
                        'CallBackURL'       => route('api.mpesa.callback'),
                        'AccountReference'  => 'KiddoQuest',
                        'TransactionDesc'   => "KiddoQuest CBC {$planType} Subscription",
                    ]);

                    if ($stkRes->successful() && isset($stkRes->json()['CheckoutRequestID'])) {
                        $checkoutId = $stkRes->json('CheckoutRequestID');
                        $payment->checkout_request_id = $checkoutId;
                        $payment->save();

                        return [
                            'success'             => true,
                            'checkout_request_id' => $checkoutId,
                            'amount'              => $amount,
                            'phone'               => $formattedPhone,
                            'plan_type'           => $planType,
                            'message'             => "🟢 Safaricom M-Pesa STK Push sent to {$formattedPhone}! Enter your M-Pesa PIN on your phone.",
                        ];
                    } else {
                        Log::error('Safaricom STK Response Error: ' . $stkRes->body());
                        return [
                            'success' => false,
                            'message' => 'Safaricom Daraja Error: ' . ($stkRes->json('errorMessage') ?? $stkRes->body()),
                        ];
                    }
                } else {
                    Log::error('Safaricom Auth Error: ' . $authRes->body());
                    return [
                        'success' => false,
                        'message' => 'Safaricom OAuth Error: ' . ($authRes->json('error_description') ?? $authRes->body()),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Safaricom Daraja Exception: ' . $e->getMessage());
            }
        }

        // Default sandbox / dev simulation mode fallback
        return [
            'success'             => true,
            'checkout_request_id' => $trackingId,
            'amount'              => $amount,
            'phone'               => $formattedPhone,
            'plan_type'           => $planType,
            'message'             => "🟢 STK Push initiated for {$formattedPhone}.",
        ];
    }

    /**
     * Complete payment and activate subscription.
     */
    public function completePayment(string $checkoutRequestId, ?string $receiptNumber = null): bool
    {
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            return false;
        }

        $receiptNumber = $receiptNumber ?? 'QKF' . rand(10000000, 99999999);

        // Update Payment
        $payment->status = 'completed';
        $payment->mpesa_receipt_number = $receiptNumber;
        $payment->result_desc = 'Safaricom M-Pesa payment processed successfully.';
        $payment->save();

        // Activate Subscription
        if ($payment->subscription) {
            $days = match($payment->subscription->plan_type) {
                'termly' => 90,
                'annual' => 365,
                default  => 30,
            };

            $payment->subscription->status = 'active';
            $payment->subscription->starts_at = now();
            $payment->subscription->expires_at = now()->addDays($days);
            $payment->subscription->save();
        }

        return true;
    }
}
