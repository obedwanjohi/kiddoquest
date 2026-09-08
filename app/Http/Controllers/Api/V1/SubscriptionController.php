<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * Paying, from inside the app.
 *
 * The app never sees a card, a PIN or a password: it asks Safaricom to prompt a
 * phone, and then waits. The money is confirmed by Daraja's callback to the
 * server, never by anything the device claims, so a modified app cannot buy
 * itself a subscription.
 *
 * Prices come from config/plans.php and travel through /config, so the app
 * never holds a number of its own and a price change needs no release.
 */
class SubscriptionController extends ApiController
{
    public function __construct(protected MpesaService $mpesa)
    {
    }

    /** What this family has, and what they could buy. */
    public function show(Request $request): JsonResponse
    {
        $guardian = $this->guardian($request);

        $active = Subscription::where('guardian_id', $guardian->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        return response()->json([
            'entitlement' => [
                'status'             => $active ? 'active' : 'none',
                'plan'               => $active?->plan_type,
                'expires_at'         => $active?->expires_at?->toIso8601String(),
                'offline_grace_days' => (int) config('plans.offline_grace_days', 7),
                'enforced'           => (bool) config('plans.enforce_subscription', false),
            ],
            'currency' => config('plans.currency', 'KES'),
            'plans'    => $this->plans(),
            'payments' => Payment::where('guardian_id', $guardian->id)
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Payment $payment) => [
                    'amount'      => (int) $payment->amount,
                    'status'      => $payment->status,
                    'receipt'     => $payment->mpesa_receipt_number,
                    'phone'       => $payment->phone_number,
                    'created_at'  => $payment->created_at?->toIso8601String(),
                ]),
        ]);
    }

    /**
     * Ask Safaricom to put a payment prompt on a phone.
     *
     * Throttled hard, and deliberately. An STK push makes somebody's phone buzz
     * and ask for their M-Pesa PIN; without a limit a signed-in account could
     * point that at any number in Kenya, over and over. Six a minute is more
     * than a parent who mistyped their number will ever need.
     */
    public function stkPush(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone_number' => ['required', 'string', 'min:9', 'max:14'],
            'plan_type'    => ['required', Rule::in(array_keys(config('plans.plans', [])))],
        ]);

        $guardian = $this->guardian($request);
        $key = 'api-stk:' . $guardian->id;

        if (RateLimiter::tooManyAttempts($key, 6)) {
            return $this->fail(
                'too_many_attempts',
                'Too many payment attempts. Wait ' . RateLimiter::availableIn($key) . ' seconds and try again.',
                429
            );
        }

        RateLimiter::hit($key, 60);

        $result = $this->mpesa->initiateStkPush($guardian, $data['phone_number'], $data['plan_type']);

        if (! ($result['success'] ?? false)) {
            return $this->fail('mpesa_failed', $result['message'] ?? 'M-Pesa could not be reached.', 502);
        }

        return response()->json([
            'checkout_request_id' => $result['checkout_request_id'],
            'amount'              => (int) $result['amount'],
            'phone'               => $result['phone'],
            'plan_type'           => $result['plan_type'],
            'message'             => $result['message'],
            'poll_seconds'        => 3,
        ]);
    }

    /**
     * Has it gone through?
     *
     * Only ever reports what the callback has already written. The app polls
     * this; it cannot tell the server that a payment succeeded.
     */
    public function status(Request $request, string $checkoutRequestId): JsonResponse
    {
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)
            ->where('guardian_id', $this->guardian($request)->id)
            ->first();

        if (! $payment) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json([
            'status'  => $payment->status,
            'paid'    => $payment->status === 'completed',
            'receipt' => $payment->mpesa_receipt_number,
            'message' => $payment->result_desc,
        ]);
    }

    /** @return array<int,array> */
    protected function plans(): array
    {
        $plans = [];

        foreach (config('plans.plans', []) as $type => $plan) {
            $plans[] = [
                'type'      => $type,
                'name'      => $plan['name'] ?? $type,
                'emoji'     => $plan['emoji'] ?? '💳',
                'blurb'     => $plan['blurb'] ?? '',
                'amount'    => (int) ($plan['amount'] ?? 0),
                'days'      => (int) ($plan['days'] ?? 30),
                'badge'     => $plan['badge'] ?? null,
                'highlight' => (bool) ($plan['highlight'] ?? false),
            ];
        }

        return $plans;
    }
}
