@extends('kids.layouts.app')

@section('title', "{$mission->title} — BZabc Kids")

@section('kid-content')
{{-- Global Alpine state to manage immersive mode across the whole page --}}
<div class="min-h-screen pb-24 transition-colors duration-500" 
     x-data="{ isImmersive: false }" 
     @set-immersive.window="isImmersive = $event.detail"
     :style="isImmersive ? 'background: #000;' : 'background: linear-gradient(180deg, #F0FDF4 0%, #DCFCE7 100%);'">
    
    {{-- Top UI Elements that hide during playback --}}
    <div x-show="!isImmersive" x-transition.opacity.duration.300ms>
        {{-- Exit Bar --}}
        <x-kid.exit-bar :stars="$child->total_stars" :exitRoute="'kids.world'" :exitRouteParam="[$world]" :title="$mission->title" />

        <div class="pt-20 px-4 max-w-2xl mx-auto flex flex-col items-center">
            {{-- Sleek Mascot Banner --}}
            <div class="w-full bg-white rounded-3xl p-4 shadow-[0_6px_0_rgba(0,0,0,0.05)] flex items-center gap-4 mb-6 cursor-pointer transform transition-transform active:scale-95" onclick="speakLessonIntro()" id="mascot-banner">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center text-4xl shadow-inner flex-shrink-0 animate-bounce">
                    🦁
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-lg leading-tight" id="leo-text">
                        Watch the video carefully!
                    </h3>
                    <p class="text-gray-500 text-sm font-medium">Tap me to listen.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- The "Toy Tablet" Video Player & Controls --}}
    <div class="transition-all duration-500 flex flex-col items-center justify-center" 
         :class="isImmersive ? 'fixed inset-0 z-50 bg-black p-2 pb-6 md:p-6' : 'px-4 max-w-4xl mx-auto mt-4'">
        
        <div class="w-full h-full flex flex-col max-w-5xl mx-auto" x-data="kidVideoPlayer()" x-init="initPlayer()">
            
            {{-- TV Chassis --}}
            <div class="bg-blue-400 p-2 md:p-4 rounded-[2rem] shadow-[0_8px_0_#2563EB,0_15px_20px_rgba(0,0,0,0.2)] w-full mb-4 relative z-10 transition-all duration-500 flex-1 min-h-0 flex flex-col"
                 :class="isImmersive ? 'rounded-2xl md:rounded-[2rem]' : ''">
                                 @if($mission->videoMedia)
                        <video x-ref="video" preload="auto" class="w-full h-full object-contain block cursor-pointer rounded-2xl"
                               controls
                               playsinline
                               controlsList="nodownload">
                            <source src="{{ $mission->videoMedia->url }}" type="{{ $mission->videoMedia->mime_type ?? 'video/mp4' }}">
                            Your browser does not support video playback.
                        </video>
                    @elseif($mission->video_url)
                        <div class="w-full h-full relative">
                            <iframe class="absolute top-0 left-0 w-full h-full rounded-2xl" src="{{ $mission->video_url }}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="text-white text-center p-8">
                            <div class="text-6xl mb-4 opacity-50">📺</div>
                            <p class="font-bold text-xl">Video coming soon!</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Always Visible "Start Mission Game" Button --}}
            @if($mission->question_bank_id)
            <div class="w-full max-w-md mx-auto my-4 px-2">
                <a href="{{ route('kids.mission.play', [$world, $mission]) }}"
                   class="w-full flex items-center justify-center gap-3 font-black text-slate-950 bg-gradient-to-b from-yellow-400 to-amber-500 border-4 border-yellow-200 py-4 px-6 rounded-3xl shadow-[0_8px_0_#b45309,0_10px_25px_rgba(245,158,11,0.5)] active:shadow-none active:translate-y-2 transition-all text-xl sm:text-2xl text-center">
                    <span class="text-3xl">🎯</span> Start Mission Game!
                </a>
            </div>
            @endif

            {{-- Alpine Video Logic --}}
            <script>
                function kidVideoPlayer() {
                    return {
                        isPlaying: false,
                        isFinished: false,
                        isScrubbing: false,
                        progress: 0,
                        playbackRate: 1.0,
                        showControls: true,
                        controlsTimeout: null,

                        initPlayer() {
                            this.$watch('isPlaying', value => {
                                // Dispatch event to toggle immersive full-screen background
                                this.$dispatch('set-immersive', value);
                                
                                if (value) {
                                    this.resetControlsTimeout();
                                } else {
                                    this.showControls = true;
                                    clearTimeout(this.controlsTimeout);
                                }
                            });
                        },
                        togglePlay() {
                            const v = this.$refs.video;
                            if (v.paused) {
                                v.play();
                                this.isPlaying = true;
                                this.isFinished = false;
                            } else {
                                v.pause();
                                this.isPlaying = false;
                            }
                        },
                        resetControlsTimeout() {
                            this.showControls = true;
                            clearTimeout(this.controlsTimeout);
                            this.controlsTimeout = setTimeout(() => {
                                if (this.isPlaying && !this.isScrubbing) this.showControls = false;
                            }, 2500);
                        },
                        updateProgress(e) {
                            if (this.isScrubbing) return;
                            const v = e.target;
                            if (v.duration) {
                                this.progress = (v.currentTime / v.duration) * 100;
                            }
                        },
                        scrub() {
                            const v = this.$refs.video;
                            if (v.duration) {
                                v.currentTime = (this.progress / 100) * v.duration;
                            }
                            this.resetControlsTimeout();
                        },
                        setSpeed(speed) {
                            this.playbackRate = speed;
                            this.$refs.video.playbackRate = speed;
                        },
                        onEnded() {
                            this.isPlaying = false;
                            this.isFinished = true;
                            this.progress = 100;
                            this.showControls = false;
                        },
                        replayVideo() {
                            this.isFinished = false;
                            this.$refs.video.currentTime = 0;
                            this.togglePlay();
                        }
                    }
                }
            </script>

        </div>
    </div>
</div>

{{-- Audio TTS Logic --}}
@php
    $introText = $mission->intro_narration_text ?? 'Watch the video, then tap Go to Test!';
    $hasIntroAudio = false; 
    $narrationVoiceGender = $mission->intro_voice_profile === 'male' || $mission->intro_voice_profile === 'david' ? 'male' : 'female';
@endphp
<script>
    window.LESSON_NARRATION = { gender: @json($narrationVoiceGender), lang: 'en' };
    window.pickNarrationVoice = function () {
        const cfg = window.LESSON_NARRATION;
        const voices = window.speechSynthesis.getVoices();
        const langRe = new RegExp('^' + (cfg.lang || 'en'), 'i');
        return voices.find(v => langRe.test(v.lang) && cfg.gender === 'female' && /female|samantha|zira|google uk english female/i.test(v.name))
            || voices.find(v => langRe.test(v.lang) && cfg.gender === 'male' && /male|david|daniel|google uk english male/i.test(v.name))
            || voices.find(v => /^en/i.test(v.lang));
    };
    
    let introPlayed = false;
    function speakLessonIntro() {
        if (introPlayed) return;
        introPlayed = true;
        
        const text = @json($introText);
        if ('speechSynthesis' in window && text && document.getElementById('mascot-banner')) {
            const utter = new SpeechSynthesisUtterance(text);
            utter.rate = 0.9;
            utter.pitch = (window.LESSON_NARRATION.gender === 'male') ? 0.9 : 1.2;
            const preferred = window.pickNarrationVoice();
            if (preferred) utter.voice = preferred;
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(utter);
            
            const banner = document.getElementById('mascot-banner');
            banner.classList.add('ring-4', 'ring-yellow-400');
            setTimeout(() => banner.classList.remove('ring-4', 'ring-yellow-400'), 2000);
        }
    }
    
    if ('speechSynthesis' in window) { window.speechSynthesis.getVoices(); }
    document.addEventListener('click', function once() {
        speakLessonIntro();
        document.removeEventListener('click', once);
    }, { once: true });
</script>

@endsection