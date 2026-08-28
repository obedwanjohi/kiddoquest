@extends('kids.layouts.app')

@section('title', "Sticker Book — KiddoQuest")

@section('kid-content')
<script>
    function stickerCanvas() {
        return {
            placedStickers: [],
            zIndexCounter: 10,

            addSticker(emoji) {
                const canvas = document.getElementById('canvas-area');
                let width = 300, height = 300;
                if (canvas) {
                    const rect = canvas.getBoundingClientRect();
                    if (rect.width > 100) width = rect.width;
                    if (rect.height > 100) height = rect.height;
                }
                
                const x = Math.floor(Math.random() * (width - 120)) + 30;
                const y = Math.floor(Math.random() * (height - 120)) + 30;

                this.placedStickers.push({
                    id: Date.now() + Math.random(),
                    emoji: emoji,
                    x: x,
                    y: y,
                    z: ++this.zIndexCounter
                });
            },

            removeSticker(index) {
                this.placedStickers.splice(index, 1);
            },

            clearCanvas() {
                this.placedStickers = [];
            },

            startDrag(event, index) {
                if (event.cancelable) event.preventDefault();
                const stk = this.placedStickers[index];
                if (!stk) return;

                stk.z = ++this.zIndexCounter;

                const canvas = document.getElementById('canvas-area');
                const rect = canvas ? canvas.getBoundingClientRect() : { left: 0, top: 0, width: 300, height: 300 };

                const getTouchPos = (e) => {
                    if (e.touches && e.touches.length > 0) {
                        return { x: e.touches[0].clientX, y: e.touches[0].clientY };
                    }
                    if (e.changedTouches && e.changedTouches.length > 0) {
                        return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
                    }
                    return { x: e.clientX, y: e.clientY };
                };

                const startPos = getTouchPos(event);
                const offsetX = startPos.x - (rect.left + stk.x);
                const offsetY = startPos.y - (rect.top + stk.y);

                const onMove = (moveEvent) => {
                    if (moveEvent.cancelable) moveEvent.preventDefault();
                    const pos = getTouchPos(moveEvent);
                    stk.x = Math.max(10, Math.min(rect.width - 80, pos.x - rect.left - offsetX));
                    stk.y = Math.max(10, Math.min(rect.height - 80, pos.y - rect.top - offsetY));
                };

                const onEnd = () => {
                    window.removeEventListener('mousemove', onMove);
                    window.removeEventListener('mouseup', onEnd);
                    window.removeEventListener('touchmove', onMove);
                    window.removeEventListener('touchend', onEnd);
                };

                window.addEventListener('mousemove', onMove, { passive: false });
                window.addEventListener('mouseup', onEnd);
                window.addEventListener('touchmove', onMove, { passive: false });
                window.addEventListener('touchend', onEnd);
            }
        }
    }
</script>

<x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" :exitRoute="'kids.shop'" :title="'Sticker Canvas'" />

<div class="pt-20 pb-12 min-h-screen" style="background: linear-gradient(180deg, #F3E8FF 0%, #E9D5FF 100%);" x-data="stickerCanvas()">
    
    <div class="max-w-4xl mx-auto px-4">
        
        {{-- Interactive Canvas Area --}}
        <div class="bg-gradient-to-b from-sky-300 via-sky-100 to-emerald-200 rounded-3xl h-[420px] md:h-[500px] shadow-[0_8px_0_#9333ea] border-4 border-purple-300 relative overflow-hidden mb-6" id="canvas-area">
            
            {{-- Background Decorations --}}
            <div class="absolute top-4 left-6 text-5xl opacity-40 animate-pulse pointer-events-none">☁️</div>
            <div class="absolute top-8 right-12 text-6xl opacity-40 animate-pulse pointer-events-none">☀️</div>
            <div class="absolute bottom-2 left-4 text-6xl opacity-30 pointer-events-none">🌳</div>
            <div class="absolute bottom-2 right-6 text-6xl opacity-30 pointer-events-none">🌲</div>

            {{-- Placed Stickers --}}
            <template x-for="(stk, index) in placedStickers" :key="stk.id">
                <div class="absolute cursor-move select-none text-6xl md:text-7xl transform transition-transform hover:scale-110 active:scale-95 touch-none"
                     :style="`left: ${stk.x}px; top: ${stk.y}px; z-index: ${stk.z}; touch-action: none;`"
                     @mousedown="startDrag($event, index)"
                     @touchstart="startDrag($event, index)">
                    <span x-text="stk.emoji"></span>
                    <button @click.stop="removeSticker(index)" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full w-7 h-7 text-xs font-bold flex items-center justify-center shadow-lg border-2 border-white z-50">✕</button>
                </div>
            </template>

            {{-- Empty Canvas Message --}}
            <div x-show="placedStickers.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-purple-700/60 pointer-events-none">
                <div class="text-6xl mb-2 animate-bounce">👇</div>
                <p class="font-black text-xl text-center px-4" style="font-family: var(--kid-font-heading);">
                    Tap stickers from your tray below to place them on the canvas!
                </p>
            </div>

            {{-- Canvas Controls --}}
            <div class="absolute bottom-4 right-4 flex gap-2 z-20">
                <button @click="clearCanvas" class="bg-white/90 backdrop-blur-sm text-purple-800 font-black px-4 py-2 rounded-xl text-sm shadow-md hover:bg-white transition-all active:scale-95">
                    🧹 Clear Canvas
                </button>
            </div>
        </div>

        {{-- Sticker Tray --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_6px_0_rgba(147,51,234,0.2)] border-2 border-purple-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-black text-purple-900 text-lg flex items-center gap-2" style="font-family: var(--kid-font-heading);">
                    <span>🎨</span> Your Sticker Tray
                </h3>
                <a href="{{ route('kids.shop') }}" class="text-sm font-bold text-purple-600 hover:text-purple-800">
                    Get More Packs 🛍️
                </a>
            </div>

            @if($allStickers->count() > 0)
                <div class="flex items-center gap-3 overflow-x-auto pb-2">
                    @foreach($allStickers as $stk)
                        <button @click="addSticker('{{ $stk }}')" 
                                type="button"
                                class="flex-shrink-0 w-16 h-16 bg-purple-50 hover:bg-purple-100 rounded-2xl border-2 border-purple-200 text-4xl flex items-center justify-center shadow-sm active:scale-90 transition-all cursor-pointer">
                            {{ $stk }}
                        </button>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-gray-400">
                    <div class="text-4xl mb-2">📦</div>
                    <p class="font-bold text-sm">No stickers unlocked yet!</p>
                    <a href="{{ route('kids.shop') }}" class="inline-block mt-2 bg-purple-500 text-white font-black px-4 py-2 rounded-xl text-xs shadow-md">
                        Visit Shop to Buy Packs 🪙
                    </a>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
