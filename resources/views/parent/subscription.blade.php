@extends('layouts.app')

@section('title', 'M-Pesa Subscription — KiddoQuest CBC')

@push('styles')
<style>
    .mpesa-bg {
        background: linear-gradient(180deg, #064E3B 0%, #0F172A 100%);
    }
    .mpesa-card {
        background: rgba(15, 23, 42, 0.9);
        border: 2px solid rgba(16, 185, 129, 0.3);
        border-radius: 24px;
        backdrop-filter: blur(12px);
    }
    .plan-card-selected {
        border-color: #10B981 !important;
        background: rgba(16, 185, 129, 0.15) !important;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3) !important;
    }
    .mpesa-btn {
        background: linear-gradient(180deg, #10B981 0%, #059669 100%);
        box-shadow: 0 4px 0 #047857;
    }
    .mpesa-btn:active {
        transform: translateY(2px);
        box-shadow: 0 1px 0 #047857;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen mpesa-bg text-white pb-20" x-data="mpesaCheckout()">
    
    {{-- Top Header Bar --}}
    <div class="bg-emerald-950/90 backdrop-blur-md border-b border-emerald-500/30 sticky top-0 z-30 px-4 py-3">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🟢</span>
                <div>
                    <h1 class="font-black text-base text-white leading-tight">Lipa na M-PESA</h1>
                    <p class="text-[11px] text-emerald-300">KiddoQuest CBC Premium Access</p>
                </div>
            </div>

            <a href="{{ route('parent.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1 transition-all">
                ← Parent Zone
            </a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 pt-6">
        
        {{-- Flash Error --}}
        @if(session('error'))
            <div class="mb-5 bg-rose-500/20 border border-rose-500 text-rose-300 rounded-2xl p-3.5 text-xs font-bold text-center">
                {{ session('error') }}
            </div>
        @endif

        {{-- Active Subscription Banner --}}
        @if($activeSubscription)
            <div class="mb-6 bg-gradient-to-r from-emerald-900/80 to-teal-900/80 border-2 border-emerald-400 rounded-3xl p-5 text-center shadow-xl">
                <div class="text-4xl mb-2 animate-bounce">🎉</div>
                <h2 class="font-black text-lg text-white mb-1">Active M-Pesa Subscription!</h2>
                <p class="text-xs text-emerald-200 font-semibold mb-3">
                    Plan: <strong class="uppercase text-white">{{ $activeSubscription->plan_type }}</strong> • 
                    Expires: <strong class="text-amber-300">{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('M d, Y') : 'Lifetime' }}</strong>
                </p>
                <div class="inline-block bg-emerald-500 text-slate-950 text-xs font-black px-4 py-1.5 rounded-full shadow-md">
                    ✓ All Adventure Worlds Unlocked
                </div>
            </div>
        @endif

        {{-- Subscription Pricing Options --}}
        <div class="text-center mb-6">
            <h2 class="text-2xl font-black text-white mb-1">Choose Your Plan</h2>
            <p class="text-xs text-emerald-200 font-semibold">World 1 is FREE • Unlock all worlds & AI features below</p>
        </div>

        {{-- Pricing Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            
            {{-- Monthly Plan --}}
            <div @click="selectedPlan = 'monthly'; amount = 499" 
                 :class="{ 'plan-card-selected': selectedPlan === 'monthly' }"
                 class="mpesa-card p-4 text-center cursor-pointer transition-all relative overflow-hidden">
                <div class="text-2xl mb-1">💳</div>
                <h3 class="font-black text-sm text-white">Monthly</h3>
                <div class="font-black text-xl text-emerald-400 my-1">KES 499</div>
                <div class="text-[10px] text-slate-300 font-semibold">30 Days Full Access</div>
            </div>

            {{-- Termly Plan (Recommended) --}}
            <div @click="selectedPlan = 'termly'; amount = 1200" 
                 :class="{ 'plan-card-selected': selectedPlan === 'termly' }"
                 class="mpesa-card p-4 text-center cursor-pointer transition-all relative overflow-hidden border-2 border-emerald-400">
                <span class="absolute top-0 right-0 bg-amber-400 text-slate-950 font-black text-[9px] px-2 py-0.5 rounded-bl-xl uppercase">
                    Save 20%
                </span>
                <div class="text-2xl mb-1">🏫</div>
                <h3 class="font-black text-sm text-white">School Term</h3>
                <div class="font-black text-xl text-amber-300 my-1">KES 1,200</div>
                <div class="text-[10px] text-slate-300 font-semibold">90 Days Access</div>
            </div>

            {{-- Annual VIP Plan --}}
            <div @click="selectedPlan = 'annual'; amount = 3999" 
                 :class="{ 'plan-card-selected': selectedPlan === 'annual' }"
                 class="mpesa-card p-4 text-center cursor-pointer transition-all relative overflow-hidden">
                <span class="absolute top-0 right-0 bg-emerald-400 text-slate-950 font-black text-[9px] px-2 py-0.5 rounded-bl-xl uppercase">
                    Best Value
                </span>
                <div class="text-2xl mb-1">🏆</div>
                <h3 class="font-black text-sm text-white">Annual VIP</h3>
                <div class="font-black text-xl text-emerald-300 my-1">KES 3,999</div>
                <div class="text-[10px] text-slate-300 font-semibold">365 Days Access</div>
            </div>

        </div>

        {{-- Safaricom Phone Input Card --}}
        <div class="mpesa-card p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-emerald-500/20 border border-emerald-400 rounded-full flex items-center justify-center text-xl">
                    📲
                </div>
                <div>
                    <h3 class="font-black text-sm text-white">Safaricom M-Pesa Checkout</h3>
                    <p class="text-xs text-emerald-300">Enter your phone number to receive STK Push prompt</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-300 mb-2">M-Pesa Registered Phone Number</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-emerald-400 text-sm font-black">🇰🇪 +254</span>
                    <input type="tel" x-model="phone" placeholder="712 345 678"
                           style="background-color: #0F172A !important; color: #FFFFFF !important;"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-20 pr-4 py-3 text-base font-black text-white focus:outline-none focus:border-emerald-400 tracking-wider">
                </div>
            </div>

            <button type="button" @click="sendStkPush()" :disabled="loading" 
                    class="w-full mpesa-btn text-white font-black py-3.5 rounded-xl text-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span x-show="!loading">🟢 Pay KES <span x-text="amount"></span> via M-Pesa</span>
                <span x-show="loading" style="display:none;">Sending STK Push Prompt...</span>
            </button>
        </div>

        {{-- STK Push Interactive Waiting Modal --}}
        <div x-show="showStkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md">
            <div class="bg-slate-900 border-2 border-emerald-400 rounded-3xl p-6 max-w-sm w-full text-center text-white shadow-2xl relative overflow-hidden">
                
                <div class="text-5xl mb-3 animate-pulse">📲</div>
                <h3 class="font-black text-lg mb-1">Check Your Phone!</h3>
                <p class="text-xs text-emerald-200 font-semibold mb-4">
                    Safaricom M-Pesa prompt has been sent to <strong class="text-white" x-text="phone"></strong>. Enter your M-Pesa PIN to complete payment.
                </p>

                <div class="bg-emerald-950/60 border border-emerald-500/40 rounded-2xl p-3.5 mb-5 text-xs text-slate-200">
                    <div class="font-black text-emerald-400 mb-1">Prompt Details:</div>
                    <div>Paybill: <strong>174379</strong></div>
                    <div>Amount: <strong class="text-amber-300">KES <span x-text="amount"></span></strong></div>
                </div>

                {{-- Dev Test Helper --}}
                <div class="pt-2 border-t border-slate-800">
                    <button type="button" @click="simulatePayment()" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-black py-2.5 rounded-xl text-xs shadow-md transition-all mb-2">
                        ⚡ Test Instant Unlock (Dev QA)
                    </button>
                    <button type="button" @click="showStkModal = false" class="text-xs text-slate-400 hover:text-white font-bold">
                        Cancel
                    </button>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    function mpesaCheckout() {
        return {
            selectedPlan: 'monthly',
            amount: 499,
            phone: '{{ $guardian->mpesa_phone ?? "0712345678" }}',
            loading: false,
            showStkModal: false,
            checkoutRequestId: null,

            sendStkPush() {
                if (!this.phone || this.phone.length < 9) {
                    alert('Please enter a valid Safaricom phone number!');
                    return;
                }

                this.loading = true;

                fetch('{{ route("parent.subscription.stk_push") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone_number: this.phone,
                        plan_type: this.selectedPlan
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.checkoutRequestId = data.checkout_request_id;
                        this.showStkModal = true;
                    } else {
                        alert(data.message || 'Error initiating M-Pesa payment.');
                    }
                })
                .catch(err => {
                    this.loading = false;
                    alert('Connection error. Please try again.');
                });
            },

            simulatePayment() {
                if (!this.checkoutRequestId) return;

                fetch('{{ route("parent.subscription.simulate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        checkout_request_id: this.checkoutRequestId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('🎉 Payment confirmed! All worlds unlocked!');
                        window.location.reload();
                    }
                });
            }
        }
    }
</script>
@endsection
