<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    protected MpesaService $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Display Subscription Manager & M-Pesa Checkout Page.
     */
    public function showSubscriptionPage(): View
    {
        $guardian = Auth::guard('guardian')->user() ?? Guardian::first();
        
        $activeSubscription = Subscription::where('guardian_id', $guardian->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        $recentPayments = Payment::where('guardian_id', $guardian->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('parent.subscription', compact('guardian', 'activeSubscription', 'recentPayments'));
    }

    /**
     * Initiate M-Pesa STK Push Prompt.
     */
    public function initiateStkPush(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|min:9|max:14',
            'plan_type'    => 'required|in:monthly,termly,annual',
        ]);

        $guardian = Auth::guard('guardian')->user() ?? Guardian::first();
        $phone = $request->input('phone_number');
        $planType = $request->input('plan_type');

        $result = $this->mpesaService->initiateStkPush($guardian, $phone, $planType);

        return response()->json($result);
    }

    /**
     * Simulated Instant Payment Trigger (for Quick Dev Testing).
     */
    public function simulatePayment(Request $request): JsonResponse
    {
        $checkoutRequestId = $request->input('checkout_request_id');
        $success = $this->mpesaService->completePayment($checkoutRequestId);

        return response()->json([
            'success' => $success,
            'message' => $success ? '🎉 M-Pesa Payment Successful! World 2 & 3 Unlocked!' : 'Payment record not found.',
        ]);
    }

    /**
     * Poll Checkout Request ID Status.
     */
    public function checkStatus(string $checkoutRequestId): JsonResponse
    {
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json([
            'status'         => $payment->status,
            'is_completed'   => $payment->status === 'completed',
            'receipt_number' => $payment->mpesa_receipt_number,
        ]);
    }

    /**
     * Webhook Endpoint for Safaricom M-Pesa Daraja Callback.
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        if (isset($content['Body']['stkCallback'])) {
            $callback = $content['Body']['stkCallback'];
            $resultCode = $callback['ResultCode'];
            $checkoutRequestId = $callback['CheckoutRequestID'];

            if ($resultCode == 0) {
                // Payment Success
                $receiptNumber = null;
                if (isset($callback['CallbackMetadata']['Item'])) {
                    foreach ($callback['CallbackMetadata']['Item'] as $item) {
                        if ($item['Name'] === 'MpesaReceiptNumber') {
                            $receiptNumber = $item['Value'];
                        }
                    }
                }
                $this->mpesaService->completePayment($checkoutRequestId, $receiptNumber);
            } else {
                // Payment Failed or Cancelled
                $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();
                if ($payment) {
                    $payment->status = 'failed';
                    $payment->result_desc = $callback['ResultDesc'] ?? 'User cancelled STK push.';
                    $payment->save();
                }
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
