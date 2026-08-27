@extends('layouts.app')

@section('title', 'Parent Gate — KiddoQuest CBC')

@push('styles')
<style>
    .pin-bg {
        background: radial-gradient(circle at center, #1E1B4B 0%, #0F172A 100%);
    }
    .pin-card {
        background: rgba(30, 41, 59, 0.95);
        border: 2px solid rgba(129, 140, 248, 0.4);
        border-radius: 28px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
    }
    .pin-digit-box {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        border: 2px solid #818CF8;
        transition: all 0.15s ease;
    }
    .pin-digit-filled {
        background: #6366F1 !important;
        box-shadow: 0 0 14px #818CF8 !important;
        transform: scale(1.2);
    }
    .pin-key-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        color: #FFFFFF;
        font-weight: 900;
        font-size: 24px;
        border-radius: 20px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 4px 0 rgba(0, 0, 0, 0.4);
        transition: all 0.05s ease;
        touch-action: manipulation;
    }
    .pin-key-btn:active {
        transform: translateY(2px);
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.4);
        background: rgba(99, 102, 241, 0.4);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen pin-bg flex items-center justify-center p-4">
    
    <div class="pin-card p-6 sm:p-8 max-w-sm w-full text-center relative overflow-hidden">
        
        {{-- Shield Icon --}}
        <div class="text-5xl mb-2 animate-bounce">🔐</div>
        
        <h1 class="text-2xl font-black text-white mb-1">Parent Zone</h1>
        <p class="text-indigo-200 text-xs font-semibold mb-5">Enter your 4-Digit Parent PIN</p>

        {{-- Flash Error --}}
        @if(session('error'))
            <div class="mb-4 bg-rose-500/20 border border-rose-500 text-rose-300 rounded-xl p-2.5 text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        {{-- PIN Indicator Bullets --}}
        <div class="flex justify-center gap-4 mb-6">
            <div id="dot-1" class="pin-digit-box"></div>
            <div id="dot-2" class="pin-digit-box"></div>
            <div id="dot-3" class="pin-digit-box"></div>
            <div id="dot-4" class="pin-digit-box"></div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('parent.verify_pin') }}" id="pin-form">
            @csrf
            <input type="hidden" name="pin" id="pin-input" value="">
        </form>

        {{-- Direct Hardcoded Keypad Grid with Pure JS Onclick --}}
        <div class="grid grid-cols-3 gap-3 mb-6 max-w-[260px] mx-auto">
            <button type="button" onclick="pressPin('1')" class="pin-key-btn">1</button>
            <button type="button" onclick="pressPin('2')" class="pin-key-btn">2</button>
            <button type="button" onclick="pressPin('3')" class="pin-key-btn">3</button>
            
            <button type="button" onclick="pressPin('4')" class="pin-key-btn">4</button>
            <button type="button" onclick="pressPin('5')" class="pin-key-btn">5</button>
            <button type="button" onclick="pressPin('6')" class="pin-key-btn">6</button>
            
            <button type="button" onclick="pressPin('7')" class="pin-key-btn">7</button>
            <button type="button" onclick="pressPin('8')" class="pin-key-btn">8</button>
            <button type="button" onclick="pressPin('9')" class="pin-key-btn">9</button>
            
            <button type="button" onclick="clearPin()" class="pin-key-btn text-rose-400 text-base">Clear</button>
            <button type="button" onclick="pressPin('0')" class="pin-key-btn">0</button>
            <button type="button" onclick="submitPin()" class="pin-key-btn text-emerald-400 text-base">✓</button>
        </div>

        {{-- Footer Link --}}
        <div class="flex flex-col gap-2">
            <a href="{{ route('kids.profiles') }}" class="text-xs text-indigo-300 hover:text-white font-bold transition-all">
                ← Back to Kids App
            </a>
            <span class="text-[11px] text-indigo-400/60 font-semibold">
                (Default PIN for testing: <strong class="text-indigo-200">1234</strong>)
            </span>
        </div>

    </div>

</div>

<script>
    var currentPin = '';

    function updateDots() {
        for (var i = 1; i <= 4; i++) {
            var dot = document.getElementById('dot-' + i);
            if (dot) {
                if (currentPin.length >= i) {
                    dot.classList.add('pin-digit-filled');
                } else {
                    dot.classList.remove('pin-digit-filled');
                }
            }
        }
    }

    function pressPin(n) {
        if (currentPin.length < 4) {
            currentPin += String(n);
            document.getElementById('pin-input').value = currentPin;
            updateDots();

            if (currentPin.length === 4) {
                setTimeout(function() {
                    submitPin();
                }, 150);
            }
        }
    }

    function clearPin() {
        currentPin = '';
        document.getElementById('pin-input').value = '';
        updateDots();
    }

    function submitPin() {
        if (currentPin.length === 4) {
            document.getElementById('pin-form').submit();
        }
    }
</script>
@endsection
