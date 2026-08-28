@extends('kids.layouts.app')

@section('title', "Reward Shop — KiddoQuest CBC")

@push('kid-styles')
<style>
    .shop-bg {
        background: linear-gradient(180deg, #FEF9C3 0%, #FEF08A 100%);
    }
    .shop-card {
        background: #FFFFFF;
        border-radius: 24px;
        border: 3px solid #F3F4F6;
        padding: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        position: relative;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .shop-card:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
    }
    .shop-tab-active-char {
        background: #D97706 !important;
        color: #FFFFFF !important;
        font-weight: 900 !important;
        border-radius: 18px !important;
        box-shadow: 0 4px 0 #92400E !important;
    }
    .shop-tab-active-hat {
        background: #7C3AED !important;
        color: #FFFFFF !important;
        font-weight: 900 !important;
        border-radius: 18px !important;
        box-shadow: 0 4px 0 #5B21B6 !important;
    }
    .shop-tab-inactive {
        background: #FFFFFF !important;
        color: #374151 !important;
        font-weight: 800 !important;
        border-radius: 18px !important;
        border: 2px solid #E5E7EB !important;
    }
    .btn-3d-amber {
        background: linear-gradient(180deg, #FBBF24 0%, #F59E0B 100%);
        color: #FFFFFF;
        font-weight: 900;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        border: none;
        border-bottom: 4px solid #B45309;
        cursor: pointer;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        transition: transform 0.05s ease;
    }
    .btn-3d-amber:active {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }
    .btn-3d-purple {
        background: linear-gradient(180deg, #A78BFA 0%, #8B5CF6 100%);
        color: #FFFFFF;
        font-weight: 900;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        border: none;
        border-bottom: 4px solid #6D28D9;
        cursor: pointer;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        transition: transform 0.05s ease;
    }
    .btn-3d-purple:active {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }
    .btn-3d-pink {
        background: linear-gradient(180deg, #F472B6 0%, #EC4899 100%);
        color: #FFFFFF;
        font-weight: 900;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        border: none;
        border-bottom: 4px solid #BE185D;
        cursor: pointer;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        transition: transform 0.05s ease;
    }
    .btn-3d-pink:active {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }
    .btn-3d-green {
        background: linear-gradient(180deg, #4ADE80 0%, #22C55E 100%);
        color: #FFFFFF;
        font-weight: 900;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        border: none;
        border-bottom: 4px solid #15803D;
        cursor: pointer;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        transition: transform 0.05s ease;
    }
    .btn-3d-green:active {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }
    .btn-playing {
        background: #DCFCE7;
        color: #15803D;
        font-weight: 900;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        border: 2px solid #86EFAC;
        text-align: center;
        display: block;
    }
    .btn-take-off {
        background: #F3F4F6;
        color: #4B5563;
        font-weight: 800;
        font-size: 12px;
        padding: 8px 12px;
        border-radius: 12px;
        width: 100%;
        border: 2px solid #E5E7EB;
        cursor: pointer;
    }
    .badge-active {
        background: #22C55E;
        color: #FFFFFF;
        font-size: 10px;
        font-weight: 900;
        padding: 3px 8px;
        border-radius: 9999px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .badge-unlocked {
        background: #FEF3C7;
        color: #92400E;
        font-size: 10px;
        font-weight: 900;
        padding: 3px 8px;
        border-radius: 9999px;
        border: 1px solid #FDE68A;
    }
    .badge-free {
        background: #FFE4E6;
        color: #E11D48;
        font-size: 10px;
        font-weight: 900;
        padding: 3px 8px;
        border-radius: 9999px;
        border: 1px solid #FDA4AF;
        animation: pulse 1.5s infinite;
    }
    @media (orientation: landscape) and (max-height: 500px) {
        .shop-container {
            padding-top: 55px !important;
            padding-bottom: 20px !important;
        }
        .fitting-room-bar {
            padding: 6px 12px !important;
            margin-bottom: 8px !important;
        }
    }
</style>
@endpush

@section('kid-content')
<x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" :exitRoute="'kids.map'" :title="'Reward Shop'" />

<div class="pt-16 pb-20 min-h-screen shop-bg shop-container">
    
    <div class="max-w-4xl mx-auto px-3 sm:px-4">

        {{-- Ultra-Compact Fitting Room Bar --}}
        <div class="fitting-room-bar bg-white/90 backdrop-blur-md rounded-2xl p-3 mt-1 mb-4 border-2 border-amber-200 flex items-center justify-between shadow-sm">
            
            {{-- Left: Mini Fitting Room Avatar --}}
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center border-2 border-amber-300 shadow-inner">
                    <span class="text-3xl">{{ $child->avatar_emoji }}</span>
                    @if($child->equipped_hat_emoji)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 text-xl drop-shadow-sm animate-bounce">
                            {{ $child->equipped_hat_emoji }}
                        </span>
                    @endif
                </div>
                <div>
                    <h2 class="font-black text-gray-900 text-sm sm:text-base leading-tight" style="font-family: var(--kid-font-heading);">
                        {{ $child->name }}
                    </h2>
                    <span class="text-[11px] font-extrabold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full inline-block mt-0.5">
                        {{ $child->avatar_name }}
                    </span>
                </div>
            </div>

            {{-- Right: High-Contrast Gold Coin Balance Badge --}}
            <div class="flex items-center gap-1.5 bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 px-3.5 py-1.5 rounded-2xl font-black text-xs sm:text-sm shadow-[0_3px_0_#b45309] border-2 border-yellow-100">
                <span class="text-base animate-bounce">🪙</span>
                <span class="font-black text-slate-950" style="font-family: var(--kid-font-heading);">{{ number_format($child->star_coins ?? 0) }} Coins</span>
            </div>
        </div>

        {{-- Tabbed Catalog --}}
        <div x-data="{ activeTab: 'characters' }">
            
            {{-- Tab Switcher --}}
            <div class="flex justify-center gap-2 mb-4">
                <button @click="activeTab = 'characters'" 
                        :class="activeTab === 'characters' ? 'shop-tab-active-char' : 'shop-tab-inactive'"
                        class="text-xs sm:text-sm px-5 py-2.5 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>🦁</span> Characters
                </button>

                <button @click="activeTab = 'hats'" 
                        :class="activeTab === 'hats' ? 'shop-tab-active-hat' : 'shop-tab-inactive'"
                        class="text-xs sm:text-sm px-5 py-2.5 transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>👒</span> Hats & Badges
                </button>
            </div>

            {{-- Category 1: Characters (Dense 2-Column Mobile Grid) --}}
            <div x-show="activeTab === 'characters'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 sm:gap-4">
                @foreach(collect($items)->where('type', 'character') as $item)
                    @php
                        $owned = $child->hasUnlockedItem($item['id']);
                        $isActiveChar = $child->avatar === $item['avatar_key'];
                        $canAfford = ($child->star_coins ?? 0) >= $item['cost'];
                        $isFree = $item['cost'] === 0;
                    @endphp

                    <div class="shop-card {{ $isActiveChar ? 'border-emerald-400 bg-emerald-50/20' : '' }}">
                        
                        {{-- Top Right Badge --}}
                        <div class="w-full flex justify-end h-5 mb-1">
                            @if($isActiveChar)
                                <span class="badge-active">Active ✓</span>
                            @elseif($isFree && !$owned)
                                <span class="badge-free">FREE 🎁</span>
                            @elseif($owned)
                                <span class="badge-unlocked">Unlocked</span>
                            @endif
                        </div>

                        {{-- Character Emoji --}}
                        <div class="text-5xl sm:text-6xl my-1 transform hover:scale-110 transition-transform">
                            {{ $item['emoji'] }}
                        </div>

                        {{-- Name --}}
                        <h3 class="font-black text-gray-900 text-xs sm:text-sm truncate w-full mt-1" style="font-family: var(--kid-font-heading);">
                            {{ $item['name'] }}
                        </h3>

                        {{-- Price label --}}
                        <div class="text-[11px] font-extrabold my-1">
                            @if($isFree && !$owned)
                                <span style="color:#E11D48;">FREE GIFT</span>
                            @elseif($owned)
                                <span style="color:#9CA3AF;">Owned</span>
                            @else
                                <span style="color:#D97706;">🪙 {{ $item['cost'] }}</span>
                            @endif
                        </div>

                        {{-- Action Button --}}
                        <div class="w-full mt-1">
                            @if($isActiveChar)
                                <div class="btn-playing">
                                    Playing ✓
                                </div>
                            @elseif($owned)
                                <form method="POST" action="{{ route('kids.shop.equip') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="character">
                                    <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                                    <input type="hidden" name="avatar_key" value="{{ $item['avatar_key'] }}">
                                    <button type="submit" class="btn-3d-amber">
                                        Switch
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('kids.shop.purchase') }}">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                                    <button type="submit" 
                                            @if(!$canAfford) disabled @endif
                                            class="{{ $isFree ? 'btn-3d-pink' : 'btn-3d-green' }}"
                                            style="{{ !$canAfford ? 'opacity: 0.4; cursor: not-allowed;' : '' }}">
                                        @if($isFree)
                                            🎁 Claim Free
                                        @else
                                            🪙 Buy
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Category 2: Hats & Accessories (Dense 2-Column Mobile Grid) --}}
            <div x-show="activeTab === 'hats'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 sm:gap-4" style="display: none;">
                @foreach(collect($items)->where('type', 'hat') as $item)
                    @php
                        $owned = $child->hasUnlockedItem($item['id']);
                        $isEquipped = $child->equipped_hat === $item['id'];
                        $canAfford = ($child->star_coins ?? 0) >= $item['cost'];
                        $isFree = $item['cost'] === 0;
                    @endphp

                    <div class="shop-card {{ $isEquipped ? 'border-purple-400 bg-purple-50/20' : '' }}">
                        
                        {{-- Top Right Badge --}}
                        <div class="w-full flex justify-end h-5 mb-1">
                            @if($isEquipped)
                                <span class="badge-active" style="background:#7C3AED;">Wearing ✓</span>
                            @elseif($isFree && !$owned)
                                <span class="badge-free">FREE 🎁</span>
                            @elseif($owned)
                                <span class="badge-unlocked" style="background:#F3E8FF; color:#6B21A8; border-color:#D8B4FE;">Unlocked</span>
                            @endif
                        </div>

                        {{-- Hat Emoji --}}
                        <div class="text-5xl sm:text-6xl my-1 transform hover:scale-110 transition-transform">
                            {{ $item['emoji'] }}
                        </div>

                        {{-- Name --}}
                        <h3 class="font-black text-gray-900 text-xs sm:text-sm truncate w-full mt-1" style="font-family: var(--kid-font-heading);">
                            {{ $item['name'] }}
                        </h3>

                        {{-- Price label --}}
                        <div class="text-[11px] font-extrabold my-1">
                            @if($isFree && !$owned)
                                <span style="color:#E11D48;">FREE GIFT</span>
                            @elseif($owned)
                                <span style="color:#9CA3AF;">Owned</span>
                            @else
                                <span style="color:#7C3AED;">🪙 {{ $item['cost'] }}</span>
                            @endif
                        </div>

                        {{-- Action Button --}}
                        <div class="w-full mt-1">
                            @if($isEquipped)
                                <form method="POST" action="{{ route('kids.shop.equip') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="hat">
                                    <input type="hidden" name="item_id" value="">
                                    <button type="submit" class="btn-take-off">
                                        Take Off
                                    </button>
                                </form>
                            @elseif($owned)
                                <form method="POST" action="{{ route('kids.shop.equip') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="hat">
                                    <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                                    <button type="submit" class="btn-3d-purple">
                                        Wear It!
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('kids.shop.purchase') }}">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                                    <button type="submit" 
                                            @if(!$canAfford) disabled @endif
                                            class="{{ $isFree ? 'btn-3d-pink' : 'btn-3d-green' }}"
                                            style="{{ !$canAfford ? 'opacity: 0.4; cursor: not-allowed;' : '' }}">
                                        @if($isFree)
                                            🎁 Claim Free
                                        @else
                                            🪙 Buy
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>
</div>
@endsection
