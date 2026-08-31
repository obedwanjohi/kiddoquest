<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KiddoQuest — Kenya's #1 Early Childhood Learning App for Playgroup, PP1 & PP2</title>
    <meta name="description" content="Engaging voiceover lessons, interactive quizzes, and magical adventure worlds in Maths, English & CRE for Kenyan kids ages 3 to 6.">
    <meta name="google-site-verification" content="google18e2c53420c6970b">
    <meta name="keywords" content="kiddoquest, kiddo quest, kiddoquest.co.ke, kiddo quest kenya, obed wanjohi, early childhood learning kenya, playgroup pp1 pp2, kenya kids app">
    <link rel="canonical" href="https://www.kiddoquest.co.ke/">

    {{-- OpenGraph & Social Cards --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.kiddoquest.co.ke/">
    <meta property="og:title" content="KiddoQuest — Kenya's #1 Early Childhood Learning App">
    <meta property="og:description" content="Engaging voiceover lessons, interactive quizzes, letter & number tracing, and magical adventure worlds for Playgroup, PP1 & PP2 learners.">

    {{-- JSON-LD Google Structured Data --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "KiddoQuest",
      "applicationCategory": "EducationalApplication",
      "operatingSystem": "Web, Android, iOS",
      "offers": {
        "@type": "Offer",
        "price": "200",
        "priceCurrency": "KES"
      },
      "author": {
        "@type": "Person",
        "name": "Obed Wanjohi"
      },
      "description": "Kenya's #1 Early Childhood Learning App for Playgroup, PP1 & PP2 learners."
    }
    </script>

    {{-- Favicon --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🦁</text></svg>">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800;900&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CSS v3 via Official Play CDN (Guarantees all slate, gradients, and modern utilities work 100%) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"Baloo 2"', 'cursive', 'sans-serif'],
                        body: ['"Nunito"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            purple: '#7C3AED',
                            pink: '#EC4899',
                            amber: '#F59E0B',
                            orange: '#F97316',
                            emerald: '#10B981',
                            dark: '#0F172A',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Nunito', sans-serif; }
        .font-heading { font-family: 'Baloo 2', cursive, sans-serif; }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(3deg); }
        }
        @keyframes float-reverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(10px) rotate(-3deg); }
        }
        @keyframes pulse-gentle {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.04); }
        }
        @keyframes wiggle {
            0%, 100% { transform: rotate(-4deg); }
            50% { transform: rotate(4deg); }
        }

        .animate-float { animation: float-slow 4s ease-in-out infinite; }
        .animate-float-rev { animation: float-reverse 5s ease-in-out infinite; }
        .animate-pulse-gentle { animation: pulse-gentle 3s ease-in-out infinite; }
        .animate-wiggle { animation: wiggle 2s ease-in-out infinite; }

        .hero-pattern {
            background-color: #FAF5FF;
            background-image: radial-gradient(#E9D5FF 1.5px, transparent 1.5px), radial-gradient(#FDE68A 1.5px, #FAF5FF 1.5px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
        }

        /* Glass and 3D card effects */
        .card-3d {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .card-3d:hover {
            transform: translateY(-6px) scale(1.02);
        }
        .text-gradient {
            background: linear-gradient(135deg, #7C3AED 0%, #EC4899 50%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body class="bg-[#FAF5FF] text-slate-900 antialiased overflow-x-hidden" x-data="{ authModal: {{ $errors->any() ? 'true' : 'false' }}, authTab: '{{ old('name') || $errors->has('name') ? 'register' : 'login' }}', mobileMenu: false }">

    {{-- Error Toast Notification --}}
    @if($errors->any())
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
             class="fixed top-5 right-5 z-50 bg-rose-600 text-white font-black px-5 py-3 rounded-2xl shadow-2xl border-2 border-white text-xs sm:text-sm flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- TOP BANNER --}}
    <div class="bg-gradient-to-r from-purple-700 via-pink-600 to-amber-500 text-white text-xs sm:text-sm font-extrabold py-2 px-3 text-center shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-1.5 flex-wrap">
            <span class="inline-block animate-wiggle">🇰🇪</span>
            <span>Designed for Kenyan Kids Aged 3 to 6</span>
            <span class="hidden sm:inline">•</span>
            <span class="hidden sm:inline">825+ Voiceover Missions for Playgroup, PP1 & PP2!</span>
        </div>
    </div>

    {{-- NAVBAR --}}
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-purple-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-20 flex items-center justify-between">
            {{-- Brand Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-gradient-to-br from-amber-400 via-pink-500 to-purple-600 flex items-center justify-center text-2xl sm:text-3xl shadow-md transform group-hover:scale-105 transition-transform">
                    🦁
                </div>
                <div>
                    <span class="font-heading text-2xl sm:text-3xl font-black tracking-tight text-purple-900">
                        KiddoQuest<span class="text-amber-500">.</span>
                    </span>
                    <span class="block text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-purple-600 -mt-1">Early Years Learning</span>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-6 lg:gap-8 font-extrabold text-sm text-slate-700">
                <a href="#worlds" class="hover:text-purple-600 transition">Subject Worlds 🌍</a>
                <a href="#levels" class="hover:text-purple-600 transition">Learning Levels 📚</a>
                <a href="#parent-powers" class="hover:text-purple-600 transition">Parent Zone 🛡️</a>
                <a href="#pricing" class="hover:text-purple-600 transition">M-Pesa Pricing 💳</a>
            </div>

            {{-- Desktop Actions --}}
            <div class="hidden sm:flex items-center gap-3">
                @if(Auth::guard('guardian')->check())
                    <a href="{{ route('kids.profiles') }}"
                       class="font-heading font-black text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white px-5 py-2.5 rounded-2xl shadow-md hover:shadow-purple-500/30 transform hover:scale-105 transition flex items-center gap-2">
                        <span>🌟 Who's Playing?</span>
                    </a>
                @else
                    <button @click="authModal = true; authTab = 'login'"
                            class="font-extrabold text-sm text-slate-700 hover:text-purple-700 px-4 py-2 rounded-xl transition">
                        Parent Sign In
                    </button>
                    <button @click="authModal = true; authTab = 'register'"
                            class="font-heading font-black text-sm bg-gradient-to-r from-amber-500 via-orange-500 to-pink-600 text-white px-5 py-2.5 rounded-2xl shadow-md hover:shadow-orange-500/30 transform hover:scale-105 transition flex items-center gap-1.5">
                        <span>Start Free 🚀</span>
                    </button>
                @endif
            </div>

            {{-- Mobile Menu Trigger Button --}}
            <div class="flex items-center gap-2 sm:hidden">
                @if(Auth::guard('guardian')->check())
                    <a href="{{ route('kids.profiles') }}" class="bg-purple-600 text-white text-xs font-black px-3.5 py-2 rounded-xl">
                        🌟 Kids
                    </a>
                @else
                    <button @click="authModal = true; authTab = 'login'" class="text-slate-800 text-xs font-extrabold px-2.5 py-1.5 border border-slate-300 rounded-xl">
                        Sign In
                    </button>
                    <button @click="authModal = true; authTab = 'register'" class="bg-gradient-to-r from-amber-500 to-pink-600 text-white text-xs font-black px-3.5 py-2 rounded-xl shadow-sm">
                        Start Free 🚀
                    </button>
                @endif
            </div>
        </div>
    </nav>

    {{-- HERO SECTION --}}
    <section class="relative hero-pattern pt-8 pb-16 sm:pt-16 sm:pb-24 overflow-hidden">
        {{-- Floating Badges --}}
        <div class="absolute top-8 left-4 sm:left-12 text-3xl sm:text-5xl animate-float opacity-70 select-none">⭐</div>
        <div class="absolute top-16 right-4 sm:right-20 text-3xl sm:text-5xl animate-float-rev opacity-70 select-none">🎈</div>
        <div class="absolute bottom-10 left-8 sm:left-24 text-3xl sm:text-5xl animate-float opacity-60 select-none">🎨</div>
        <div class="absolute bottom-12 right-6 sm:right-28 text-3xl sm:text-5xl animate-float-rev opacity-60 select-none">🧩</div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                {{-- Left Text Column --}}
                <div class="lg:col-span-7 text-center lg:text-left">
                    {{-- Curriculum Pill --}}
                    <div class="inline-flex items-center gap-2 bg-purple-100 border border-purple-300 text-purple-900 text-xs sm:text-sm font-black px-4 py-1.5 rounded-full mb-5 shadow-sm">
                        <span class="text-base">🏆</span> Kenya's #1 Early Childhood Learning App
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="font-heading text-3xl sm:text-5xl lg:text-6xl font-black text-slate-950 leading-[1.15] mb-5 tracking-tight">
                        Where Little Explorers <br class="hidden sm:inline">
                        <span class="text-gradient">Fall in Love With Learning!</span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-base sm:text-lg text-slate-700 font-bold leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-8">
                        Turn screen time into active mastery. <strong>825+ voiceover video missions</strong>, interactive quizzes, and magical adventure worlds in <strong>Mathematics, English & CRE</strong> tailored specifically for Kenyan children ages 3 to 6.
                    </p>

                    {{-- Hero CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center lg:justify-start gap-3.5 mb-8">
                        @if(Auth::guard('guardian')->check())
                            <a href="{{ route('kids.profiles') }}"
                               class="font-heading font-black text-lg bg-gradient-to-r from-amber-500 via-pink-600 to-purple-700 text-white px-8 py-4 rounded-2xl shadow-xl hover:shadow-purple-500/40 transform hover:scale-105 transition flex items-center justify-center gap-3 text-center">
                                <span>🚀 Open KiddoQuest Player</span>
                            </a>
                        @else
                            <button @click="authModal = true; authTab = 'register'"
                                    class="font-heading font-black text-base sm:text-lg bg-gradient-to-r from-amber-500 via-orange-500 to-pink-600 text-white px-8 py-4 rounded-2xl shadow-xl hover:shadow-orange-500/40 transform hover:scale-105 transition flex items-center justify-center gap-3 cursor-pointer text-center">
                                <span>✨ Start Free 7-Day Trial</span>
                                <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs font-bold">No Card Required</span>
                            </button>
                            <button @click="authModal = true; authTab = 'login'"
                                    class="font-heading font-black text-base bg-white border-2 border-purple-200 text-purple-900 px-6 py-4 rounded-2xl hover:border-purple-400 hover:bg-purple-50 transition shadow-sm flex items-center justify-center gap-2 cursor-pointer text-center">
                                <span>🔐 Parent Sign In</span>
                            </button>
                        @endif
                    </div>

                    {{-- Trust Numbers Grid --}}
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-6 border-t border-purple-200/80 max-w-lg mx-auto lg:mx-0">
                        <div class="bg-white/80 border border-purple-100 rounded-2xl p-3 text-center lg:text-left shadow-sm">
                            <div class="font-heading text-xl sm:text-2xl font-black text-purple-800">825+</div>
                            <div class="text-[11px] sm:text-xs font-extrabold text-slate-600">Audio Missions</div>
                        </div>
                        <div class="bg-white/80 border border-purple-100 rounded-2xl p-3 text-center lg:text-left shadow-sm">
                            <div class="font-heading text-xl sm:text-2xl font-black text-pink-600">100%</div>
                            <div class="text-[11px] sm:text-xs font-extrabold text-slate-600">Ad-Free & Safe</div>
                        </div>
                        <div class="bg-white/80 border border-purple-100 rounded-2xl p-3 text-center lg:text-left shadow-sm">
                            <div class="font-heading text-xl sm:text-2xl font-black text-amber-600">PG-PP2</div>
                            <div class="text-[11px] sm:text-xs font-extrabold text-slate-600">Built for Kenya</div>
                        </div>
                    </div>
                </div>

                {{-- Right Interactive Character Card --}}
                <div class="lg:col-span-5 relative mt-4 lg:mt-0">
                    <div class="relative bg-white border-4 border-purple-200 rounded-[2.5rem] shadow-2xl p-5 sm:p-7">
                        {{-- Top Header inside card --}}
                        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-3xl shadow-inner">🦁</div>
                                <div>
                                    <h4 class="font-heading font-black text-lg text-slate-900">Leo the Lion</h4>
                                    <p class="text-xs font-extrabold text-amber-700">Your Learning Guide</p>
                                </div>
                            </div>
                            <div class="bg-amber-100 border border-amber-300 rounded-full px-3 py-1 flex items-center gap-1.5">
                                <span class="text-sm">⭐</span>
                                <span class="font-black text-xs sm:text-sm text-amber-900">1,450 Stars</span>
                            </div>
                        </div>

                        {{-- Character Squad Grid --}}
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2.5">Meet Your Kiddo Friends</p>
                        <div class="grid grid-cols-4 gap-2 sm:gap-3 mb-5">
                            <div class="bg-purple-50 hover:bg-purple-100 rounded-2xl p-2.5 text-center border-2 border-purple-300 transition cursor-pointer">
                                <div class="text-3xl mb-1 animate-bounce">🦁</div>
                                <span class="block text-[11px] font-black text-purple-900">Leo</span>
                            </div>
                            <div class="bg-blue-50 hover:bg-blue-100 rounded-2xl p-2.5 text-center border-2 border-blue-300 transition cursor-pointer">
                                <div class="text-3xl mb-1">🐘</div>
                                <span class="block text-[11px] font-black text-blue-900">Eli</span>
                            </div>
                            <div class="bg-pink-50 hover:bg-pink-100 rounded-2xl p-2.5 text-center border-2 border-pink-300 transition cursor-pointer">
                                <div class="text-3xl mb-1">🦒</div>
                                <span class="block text-[11px] font-black text-pink-900">Gigi</span>
                            </div>
                            <div class="bg-emerald-50 hover:bg-emerald-100 rounded-2xl p-2.5 text-center border-2 border-emerald-300 transition cursor-pointer">
                                <div class="text-3xl mb-1">🐼</div>
                                <span class="block text-[11px] font-black text-emerald-900">Pip</span>
                            </div>
                        </div>

                        {{-- Interactive Question Preview --}}
                        <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 text-white rounded-2xl p-4 sm:p-5 shadow-xl relative overflow-hidden"
                             x-data="{ answered: false, correct: false }">
                            <div class="flex items-center justify-between text-[11px] font-extrabold text-indigo-300 mb-2">
                                <span>🔢 Math Adventure Mission #12</span>
                                <span class="bg-white/10 px-2 py-0.5 rounded-full">🔊 Voiceover</span>
                            </div>
                            <p class="font-heading text-base sm:text-lg font-black text-amber-300 mb-3">
                                "How many juicy red apples are there? 🍎🍎🍎"
                            </p>

                            <div class="grid grid-cols-3 gap-2">
                                <button @click="answered = true; correct = false"
                                        class="py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl font-black text-sm border border-white/20 transition">
                                    2
                                </button>
                                <button @click="answered = true; correct = true"
                                        :class="answered && correct ? 'bg-emerald-500 text-white border-emerald-400 shadow-lg' : 'bg-white/15 hover:bg-white/25 text-white'"
                                        class="py-2.5 rounded-xl font-black text-sm border border-white/20 transition">
                                    3 ⭐
                                </button>
                                <button @click="answered = true; correct = false"
                                        class="py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl font-black text-sm border border-white/20 transition">
                                    4
                                </button>
                            </div>

                            <div x-show="answered" x-transition class="mt-3 text-center text-xs font-black"
                                 :class="correct ? 'text-emerald-300' : 'text-rose-300'">
                                <span x-text="correct ? '🎉 Super Smart! +10 Star Coins Earned!' : 'Try again! You can do it! 💪'"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- SUBJECT WORLDS SHOWCASE --}}
    <section id="worlds" class="py-16 sm:py-24 bg-white border-t border-b border-purple-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-black uppercase tracking-widest text-purple-700 bg-purple-100 px-4 py-1.5 rounded-full border border-purple-200">
                    🌍 Comprehensive Early Childhood Learning
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-950 mt-4 mb-3">
                    3 Magical Subject Worlds
                </h2>
                <p class="text-sm sm:text-base text-slate-600 font-bold">
                    Every lesson includes native voice acting, playful audio, and visual feedback made for young learners.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">

                {{-- Mathematics --}}
                <div class="card-3d bg-gradient-to-b from-amber-50/90 to-white border-2 border-amber-300 rounded-[2.5rem] p-6 sm:p-8 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-amber-400 text-3xl flex items-center justify-center shadow-md mb-5">
                            🔢
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">Maths Mountain</h3>
                        <p class="text-xs sm:text-sm text-slate-600 font-bold mb-5">
                            Build rock-solid number sense, counting confidence, and real-world math skills.
                        </p>
                        <ul class="space-y-2.5 text-xs sm:text-sm font-extrabold text-slate-700 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 text-base">✓</span> Number recognition 1 to 20
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 text-base">✓</span> Shapes, sizes & color sorting
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 text-base">✓</span> Kenyan Currency Coins (KES 1, 5, 10, 20)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-600 text-base">✓</span> Visual addition & subtraction
                            </li>
                        </ul>
                    </div>
                    <div class="bg-amber-100 rounded-2xl p-3 text-center text-xs font-black text-amber-900 border border-amber-200">
                        415+ Interactive Missions
                    </div>
                </div>

                {{-- English --}}
                <div class="card-3d bg-gradient-to-b from-purple-50/90 to-white border-2 border-purple-300 rounded-[2.5rem] p-6 sm:p-8 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-purple-600 text-white text-3xl flex items-center justify-center shadow-md mb-5">
                            🔤
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">English Jungle</h3>
                        <p class="text-xs sm:text-sm text-slate-600 font-bold mb-5">
                            From first phonics sounds to fluent sentence reading and confident pronunciation.
                        </p>
                        <ul class="space-y-2.5 text-xs sm:text-sm font-extrabold text-slate-700 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-purple-600 text-base">✓</span> Phonics & letter sound blending
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-purple-600 text-base">✓</span> Digraphs, blends & rhyming words
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-purple-600 text-base">✓</span> High-frequency sight words
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-purple-600 text-base">✓</span> Spoken pronunciation & vocabulary
                            </li>
                        </ul>
                    </div>
                    <div class="bg-purple-100 rounded-2xl p-3 text-center text-xs font-black text-purple-900 border border-purple-200">
                        350+ Reading & Phonics Missions
                    </div>
                </div>

                {{-- CRE --}}
                <div class="card-3d bg-gradient-to-b from-pink-50/90 to-white border-2 border-pink-300 rounded-[2.5rem] p-6 sm:p-8 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-pink-500 text-white text-3xl flex items-center justify-center shadow-md mb-5">
                            🕊️
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">CRE & Values Safari</h3>
                        <p class="text-xs sm:text-sm text-slate-600 font-bold mb-5">
                            Nurture moral values, Bible stories, kindness, empathy, and positive character.
                        </p>
                        <ul class="space-y-2.5 text-xs sm:text-sm font-extrabold text-slate-700 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="text-pink-600 text-base">✓</span> God's beautiful creation & nature
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-pink-600 text-base">✓</span> Bible heroes (David, Noah, Moses, Jesus)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-pink-600 text-base">✓</span> Kindness, sharing & helping family
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-pink-600 text-base">✓</span> Daily prayers & Christian values
                            </li>
                        </ul>
                    </div>
                    <div class="bg-pink-100 rounded-2xl p-3 text-center text-xs font-black text-pink-900 border border-pink-200">
                        200+ Values & Story Missions
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- CBC LEVELS --}}
    <section id="levels" class="py-16 sm:py-24 bg-[#FAF5FF]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-black uppercase tracking-widest text-indigo-700 bg-indigo-100 px-4 py-1.5 rounded-full border border-indigo-200">
                    🎯 Adapted to Every Age
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-950 mt-4 mb-3">
                    Tailored for Playgroup, PP1 & PP2
                </h2>
                <p class="text-sm sm:text-base text-slate-600 font-bold">
                    Age-appropriate pacing ensures your child learns happily without frustration.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                {{-- Playgroup --}}
                <div class="bg-white border-2 border-indigo-200 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <div class="text-4xl mb-3">🧸</div>
                    <div class="inline-block bg-blue-100 text-blue-900 text-xs font-black px-3 py-1 rounded-full mb-3">
                        Ages 3 to 4
                    </div>
                    <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">Play Group</h3>
                    <p class="text-xs sm:text-sm text-slate-600 font-bold leading-relaxed mb-6">
                        Gentle, visual, audio-led learning. Big buttons, friendly farm animals, color matching, and 60-second bite-sized missions.
                    </p>
                    <div class="text-xs font-black text-purple-700">Exploration & Sensory Skills →</div>
                </div>

                {{-- PP1 --}}
                <div class="bg-gradient-to-b from-purple-600 to-indigo-700 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden transform md:-translate-y-2">
                    <div class="text-4xl mb-3">🎨</div>
                    <div class="inline-block bg-amber-400 text-amber-950 text-xs font-black px-3 py-1 rounded-full mb-3">
                        Ages 4 to 5 • Most Popular
                    </div>
                    <h3 class="font-heading text-2xl font-black text-white mb-2">Pre-Primary 1 (PP1)</h3>
                    <p class="text-xs sm:text-sm text-purple-100 font-bold leading-relaxed mb-6">
                        Core foundation building. Phonics blends, counting objects to 10, recognizing shapes, tracing patterns, and basic storytelling.
                    </p>
                    <div class="text-xs font-black text-amber-300">Foundation & Literacy →</div>
                </div>

                {{-- PP2 --}}
                <div class="bg-white border-2 border-indigo-200 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <div class="text-4xl mb-3">📖</div>
                    <div class="inline-block bg-pink-100 text-pink-900 text-xs font-black px-3 py-1 rounded-full mb-3">
                        Ages 5 to 6
                    </div>
                    <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">Pre-Primary 2 (PP2)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 font-bold leading-relaxed mb-6">
                        Primary school readiness! Simple math addition/subtraction, Kenyan currency, full sentence comprehension, and Bible story recall.
                    </p>
                    <div class="text-xs font-black text-purple-700">Grade 1 Preparation →</div>
                </div>
            </div>
        </div>
    </section>

    {{-- PARENT SUPERPOWERS (PIN & CONTROLS) --}}
    <section id="parent-powers" class="py-16 sm:py-24 bg-slate-950 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <div class="lg:col-span-6">
                    <span class="text-xs font-black uppercase tracking-widest text-pink-400 bg-pink-950 border border-pink-700 px-4 py-1.5 rounded-full">
                        🛡️ Peace of Mind for Parents
                    </span>
                    <h2 class="font-heading text-3xl sm:text-5xl font-black text-white mt-4 mb-4 leading-tight">
                        You Stay in Full Control Behind the <span class="text-amber-400">Parent PIN</span>
                    </h2>
                    <p class="text-sm sm:text-base text-slate-300 font-bold mb-8">
                        KiddoQuest is built to give your children total joy and give you total clarity. Check accuracy heatmaps, set screen-time timers, and watch them thrive.
                    </p>

                    <div class="space-y-5">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-900 border border-indigo-500 flex items-center justify-center text-2xl shrink-0">
                                ⏱️
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-white">Daily Screen-Time Limits</h4>
                                <p class="text-xs sm:text-sm text-slate-400 font-semibold mt-0.5">Set 15, 30, or 45-minute timers. When time is up, Leo bids goodnight with zero tantrums.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-900 border border-emerald-500 flex items-center justify-center text-2xl shrink-0">
                                📊
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-white">Smart Skill Heatmaps</h4>
                                <p class="text-xs sm:text-sm text-slate-400 font-semibold mt-0.5">Instantly see which skills your child has mastered (e.g. 95% in counting) and where they need encouragement.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-900 border border-amber-500 flex items-center justify-center text-2xl shrink-0">
                                🔒
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-white">4-Digit Parent Security Gate</h4>
                                <p class="text-xs sm:text-sm text-slate-400 font-semibold mt-0.5">Kids cannot leave the learning zone or access subscription settings without your secret PIN.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Parent Dashboard Preview Card --}}
                <div class="lg:col-span-6">
                    <div class="bg-slate-900 border-2 border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl">
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/30 text-purple-300 flex items-center justify-center text-xl">
                                    📊
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">Obed's Learning Report</h4>
                                    <p class="text-xs text-slate-400">Pre-Primary 1 • Active Today</p>
                                </div>
                            </div>
                            <span class="bg-emerald-500/20 text-emerald-400 text-xs font-black px-3 py-1 rounded-full border border-emerald-500/40">
                                88% Accuracy
                            </span>
                        </div>

                        {{-- Progress Bars --}}
                        <div class="space-y-4 mb-6">
                            <div>
                                <div class="flex justify-between text-xs font-extrabold text-slate-200 mb-1.5">
                                    <span>Counting Objects (1–10)</span>
                                    <span class="text-emerald-400 font-black">95% (Mastered)</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-3 border border-slate-700">
                                    <div class="bg-emerald-500 h-3 rounded-full" style="width: 95%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-extrabold text-slate-200 mb-1.5">
                                    <span>Letter Sounds & Phonics</span>
                                    <span class="text-indigo-400 font-black">85% (Strong)</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-3 border border-slate-700">
                                    <div class="bg-indigo-500 h-3 rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-extrabold text-slate-200 mb-1.5">
                                    <span>Shape Identification</span>
                                    <span class="text-amber-400 font-black">75% (Growing)</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-3 border border-slate-700">
                                    <div class="bg-amber-500 h-3 rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-950 rounded-2xl p-4 border border-slate-800 text-xs text-slate-300 font-medium">
                            <span class="text-amber-400 font-black">💡 Leo's Tip for Parents:</span>
                            "Obed is doing great! Point out 3-sided pizza slices vs 4-sided books during snack time to solidify shapes!"
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- PRICING (M-PESA) --}}
    <section id="pricing" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-black uppercase tracking-widest text-emerald-700 bg-emerald-100 px-4 py-1.5 rounded-full border border-emerald-200">
                    💳 Simple & Affordable Kenyan Pricing
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-950 mt-4 mb-3">
                    One Family Subscription • Unlimited Kids
                </h2>
                <p class="text-sm sm:text-base text-slate-600 font-bold">
                    Pay securely via Safaricom M-Pesa STK push. No credit cards, no hidden fees.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 max-w-4xl mx-auto items-stretch">

                {{-- Monthly Plan --}}
                <div class="bg-[#FAF5FF] border-2 border-purple-200 rounded-[2.5rem] p-7 sm:p-9 shadow-sm flex flex-col justify-between text-center">
                    <div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-1">Monthly Quest</h3>
                        <p class="text-xs text-slate-500 font-extrabold mb-5">Month-to-month flexibility</p>
                        <div class="mb-6">
                            <span class="text-xs font-black text-slate-500">KES</span>
                            <span class="font-heading text-5xl font-black text-slate-950">200</span>
                            <span class="text-xs text-slate-500 font-extrabold">/ month</span>
                        </div>

                        <ul class="space-y-3 text-xs sm:text-sm font-extrabold text-slate-700 mb-8 text-left max-w-xs mx-auto">
                            <li class="flex items-center gap-2.5"><span class="text-emerald-600 font-black">✓</span> All 825+ Learning Missions</li>
                            <li class="flex items-center gap-2.5"><span class="text-emerald-600 font-black">✓</span> Up to 4 Child Profiles</li>
                            <li class="flex items-center gap-2.5"><span class="text-emerald-600 font-black">✓</span> Instant M-Pesa STK Push</li>
                            <li class="flex items-center gap-2.5"><span class="text-emerald-600 font-black">✓</span> Parent Analytics & Screen-Time</li>
                        </ul>
                    </div>

                    <button @click="authModal = true; authTab = 'register'"
                            class="w-full font-heading font-black text-base bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-2xl transition cursor-pointer">
                        Get Started →
                    </button>
                </div>

                {{-- Annual Plan --}}
                <div class="bg-gradient-to-b from-purple-700 via-purple-800 to-indigo-900 text-white border-4 border-amber-400 rounded-[2.5rem] p-7 sm:p-9 shadow-2xl flex flex-col justify-between text-center relative overflow-hidden">
                    <div class="absolute top-4 right-4 bg-amber-400 text-amber-950 font-black text-[10px] uppercase px-3 py-1 rounded-full shadow-md">
                        Save 25% 🔥
                    </div>

                    <div>
                        <h3 class="font-heading text-2xl font-black text-white mb-1">Annual Champion</h3>
                        <p class="text-xs text-purple-200 font-extrabold mb-5">Full year of uninterrupted mastery</p>
                        <div class="mb-6">
                            <span class="text-xs font-black text-purple-200">KES</span>
                            <span class="font-heading text-5xl font-black text-amber-300">1,800</span>
                            <span class="text-xs text-purple-200 font-extrabold">/ year</span>
                        </div>

                        <ul class="space-y-3 text-xs sm:text-sm font-extrabold text-purple-100 mb-8 text-left max-w-xs mx-auto">
                            <li class="flex items-center gap-2.5"><span class="text-amber-300 font-black">✓</span> All 825+ Learning Missions</li>
                            <li class="flex items-center gap-2.5"><span class="text-amber-300 font-black">✓</span> Unlimited Child Profiles</li>
                            <li class="flex items-center gap-2.5"><span class="text-amber-300 font-black">✓</span> 3 Months Completely Free</li>
                            <li class="flex items-center gap-2.5"><span class="text-amber-300 font-black">✓</span> Priority Parent Support</li>
                        </ul>
                    </div>

                    <button @click="authModal = true; authTab = 'register'"
                            class="w-full font-heading font-black text-base bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-300 hover:to-orange-400 text-slate-950 py-4 rounded-2xl shadow-lg transform hover:scale-105 transition cursor-pointer">
                        Claim Best Value Plan 🚀
                    </button>
                </div>

            </div>
        </div>
    </section>

    {{-- PARENT REVIEWS --}}
    <section class="py-16 sm:py-24 bg-[#FAF5FF] border-t border-purple-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <span class="text-xs font-black uppercase tracking-widest text-pink-700 bg-pink-100 px-4 py-1.5 rounded-full border border-pink-200">
                    💬 Loved by Kenyan Families
                </span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-slate-950 mt-4 mb-2">
                    Hear From Fellow Parents
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-purple-100">
                    <div class="flex text-amber-500 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-700 font-bold leading-relaxed mb-4">
                        "My 4-year-old son used to just watch mindless cartoons. Now he wakes up asking to do his English Jungle and counting with Leo the Lion! Incredible improvement."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center font-black text-purple-700 text-xs">FM</div>
                        <div>
                            <p class="font-extrabold text-xs text-slate-900">Faith Mwangi</p>
                            <p class="text-[10px] text-slate-500 font-bold">PP1 Parent • Nairobi</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-purple-100">
                    <div class="flex text-amber-500 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-700 font-bold leading-relaxed mb-4">
                        "The fact that it matches what pre-primary children learn in Kenyan schools made all the difference. She breezed through her PP2 school assessments!"
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-pink-100 flex items-center justify-center font-black text-pink-700 text-xs">DO</div>
                        <div>
                            <p class="font-extrabold text-xs text-slate-900">David Omondi</p>
                            <p class="text-[10px] text-slate-500 font-bold">PP2 Parent • Kisumu</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-purple-100">
                    <div class="flex text-amber-500 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-700 font-bold leading-relaxed mb-4">
                        "Zero advertisements and the 4-digit PIN lock gave me complete confidence to let my 3-year-old play safely on the tablet while I prepare dinner."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center font-black text-amber-800 text-xs">AK</div>
                        <div>
                            <p class="font-extrabold text-xs text-slate-900">Amina Kiprop</p>
                            <p class="text-[10px] text-slate-500 font-bold">Playgroup Parent • Nakuru</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ABOUT US & FOUNDER SECTION --}}
    <section id="about" class="py-16 sm:py-24 bg-gradient-to-b from-slate-900 to-slate-950 text-white relative overflow-hidden border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="bg-amber-500/20 text-amber-300 font-extrabold text-xs tracking-wider uppercase px-4 py-1.5 rounded-full border border-amber-500/30 mb-4 inline-block">
                    🌱 Our Story & Founder Vision
                </span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-white leading-tight">
                    Built to Empower the Next Generation of African Learners
                </h2>
                <p class="mt-4 text-sm sm:text-base text-slate-300 font-semibold leading-relaxed">
                    KiddoQuest was created with a bold vision: to transform early childhood education in Kenya by turning foundational learning concepts into interactive, joyful digital learning adventures.
                </p>
            </div>

            {{-- FOUNDER CARD --}}
            <div class="max-w-4xl mx-auto bg-slate-900/90 rounded-3xl p-6 sm:p-10 border border-slate-800 shadow-2xl relative">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    
                    {{-- Founder Image Container --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="relative group cursor-pointer">
                            {{-- Glowing Aura on Hover --}}
                            <div class="absolute -inset-1 bg-gradient-to-r from-amber-400 via-pink-500 to-purple-600 rounded-3xl blur-md opacity-70 group-hover:opacity-100 group-hover:blur-lg transition-all duration-500"></div>
                            
                            @php
                                $founderImg = file_exists(public_path('images/founder.jpg')) 
                                    ? asset('images/founder.jpg') 
                                    : (file_exists(public_path('images/founder.jpg.jpg')) 
                                        ? asset('images/founder.jpg.jpg') 
                                        : 'https://ui-avatars.com/api/?name=Obed+Wanjohi&background=7C3AED&color=fff&size=200');
                            @endphp
                            {{-- Image Container (object-top fixes head crop) --}}
                            <div class="relative w-44 h-52 sm:w-48 sm:h-56 rounded-2xl overflow-hidden bg-slate-800 border-2 border-amber-400/80 group-hover:border-amber-300 transition-all duration-500 flex items-center justify-center shadow-2xl">
                                <img src="{{ $founderImg }}" 
                                     alt="Obed Wanjohi - Founder & CEO" 
                                     class="w-full h-full object-cover object-top transform group-hover:scale-105 transition-transform duration-500">
                            </div>
                        </div>
                        <h3 class="font-heading text-xl font-black text-white mt-4 tracking-wide group-hover:text-amber-300 transition-colors">Obed Wanjohi</h3>
                        <span class="text-xs font-black text-amber-400 bg-amber-500/20 px-3 py-1 rounded-full border border-amber-500/30 mt-1 shadow-sm">
                            Founder & CEO
                        </span>
                    </div>

                    {{-- Founder Vision Text --}}
                    <div class="md:col-span-2 space-y-4 text-slate-300 text-xs sm:text-sm font-semibold leading-relaxed">
                        <div class="flex items-center gap-2 text-amber-400 font-heading text-lg font-black">
                            <span>🚀</span> <span>A Founder's Vision</span>
                        </div>
                        <p>
                            "Every child deserves an education that feels like an exciting quest, not a chore. I started building KiddoQuest single-handedly with a clear vision: to create an ad-free, culturally relevant early learning platform designed specifically for Kenyan children in Playgroup, PP1, and PP2."
                        </p>
                        <p>
                            "From voiceover movie stories to interactive counting, letter tracing, and moral values, every screen in KiddoQuest is crafted to inspire curiosity and confidence in Playgroup, PP1, and PP2 learners."
                        </p>

                        <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center gap-6 text-xs">
                            <div class="flex items-center gap-2 text-slate-400">
                                <span class="text-amber-400 font-black text-base">20+</span>
                                <span>Math & Tracing Missions</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-400">
                                <span class="text-emerald-400 font-black text-base">100%</span>
                                <span>Ad-Free & Safe</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-400">
                                <span class="text-pink-400 font-black text-base">Kenya</span>
                                <span>Built</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-slate-950 text-white pt-14 pb-24 sm:pb-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-pink-500 flex items-center justify-center text-xl">🦁</div>
                        <span class="font-heading text-2xl font-black text-white">KiddoQuest</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm leading-relaxed mb-3 font-semibold">
                        Empowering Kenyan pre-primary learners through gamified early learning mastery, playful voiceover adventures, and peace of mind for parents.
                    </p>
                    <p class="text-xs text-slate-500 font-bold">
                        Nairobi, Kenya • Made with ❤️ for Kenyan Kids
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-300 mb-3">Quick Navigation</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-bold">
                        <li><a href="#worlds" class="hover:text-white transition">Subject Worlds</a></li>
                        <li><a href="#levels" class="hover:text-white transition">Learning Levels (PG, PP1, PP2)</a></li>
                        <li><a href="#about" class="text-amber-400 hover:text-amber-300 transition flex items-center gap-1">🌱 About Us & Founder</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">M-Pesa Pricing</a></li>
                        <li><a href="{{ route('guardian.login') }}" class="hover:text-white transition">Parent Portal</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-300 mb-3">Portals & Admin</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-bold">
                        <li><a href="{{ route('kids.profiles') }}" class="hover:text-white transition">Kids Profile Launcher</a></li>
                        <li><a href="{{ route('guardian.login') }}" class="hover:text-white transition">Parent Login / Sign Up</a></li>
                        <li><a href="{{ route('admin.login') }}" class="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">⚙️ Admin Content Portal</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-900 text-center text-xs text-slate-500 font-bold flex flex-col sm:flex-row items-center justify-between gap-3">
                <p>&copy; {{ date('Y') }} KiddoQuest. All Rights Reserved.</p>
                <p class="flex items-center gap-3">
                    <span>100% Kid Safe & Ad-Free</span>
                    <span>•</span>
                    <span>Safaricom M-Pesa Enabled</span>
                </p>
            </div>
        </div>
    </footer>

    {{-- FLOATING MOBILE BOTTOM BAR (Thumb-friendly CTA for phones) --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-purple-200 p-3 sm:hidden shadow-2xl flex items-center gap-2">
        @if(Auth::guard('guardian')->check())
            <a href="{{ route('kids.profiles') }}"
               class="flex-1 font-heading font-black text-center text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-xl shadow-md">
                🌟 Open Kid Player
            </a>
        @else
            <button @click="authModal = true; authTab = 'login'"
                    class="font-heading font-black text-xs text-slate-700 bg-slate-100 border border-slate-300 px-4 py-3 rounded-xl">
                Sign In
            </button>
            <button @click="authModal = true; authTab = 'register'"
                    class="flex-1 font-heading font-black text-center text-sm bg-gradient-to-r from-amber-500 via-orange-500 to-pink-600 text-white py-3 rounded-xl shadow-lg">
                Start Free Trial 🚀
            </button>
        @endif
    </div>

    {{-- AUTH POPUP MODAL (LOGIN & SIGN UP) --}}
    <div x-show="authModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="authModal = false"
             class="bg-white rounded-[2.5rem] shadow-2xl p-6 sm:p-8 max-w-md w-full relative border-2 border-purple-200 max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-90 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            {{-- Close Button --}}
            <button @click="authModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-800 text-xl font-black p-2">
                ✕
            </button>

            {{-- Mascot Header --}}
            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center text-3xl mx-auto mb-2 shadow-inner">
                    🦁
                </div>
                <h3 class="font-heading text-2xl font-black text-slate-900">
                    <span x-show="authTab === 'register'">Create Free Parent Account</span>
                    <span x-show="authTab === 'login'">Welcome Back Parent!</span>
                </h3>
                <p class="text-xs text-slate-600 font-bold mt-0.5">
                    <span x-show="authTab === 'register'">Start your child's 7-day learning adventure</span>
                    <span x-show="authTab === 'login'">Sign in to manage kids & view reports</span>
                </p>
            </div>

            {{-- Tabs --}}
            <div class="flex bg-slate-100 p-1 rounded-2xl mb-5 border border-slate-200">
                <button @click="authTab = 'register'"
                        :class="authTab === 'register' ? 'bg-white shadow-md text-purple-800 font-black' : 'text-slate-600 font-bold'"
                        class="flex-1 py-2 text-xs rounded-xl transition">
                    Sign Up Free 🚀
                </button>
                <button @click="authTab = 'login'"
                        :class="authTab === 'login' ? 'bg-white shadow-md text-purple-800 font-black' : 'text-slate-600 font-bold'"
                        class="flex-1 py-2 text-xs rounded-xl transition">
                    Sign In 🔐
                </button>
            </div>

            {{-- Error banner inside modal --}}
            @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-300 text-rose-700 px-4 py-3 rounded-2xl text-xs font-black text-center">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            {{-- Register Form --}}
            <form x-show="authTab === 'register'" action="{{ url('/parent/register') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Parent Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Jane Mwangi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="parent@example.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">M-Pesa Phone Number (Optional)</label>
                    <input type="text" name="phone" placeholder="0712 345 678"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="At least 8 characters"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-type password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <button type="submit"
                        class="w-full font-heading font-black text-base bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white py-3.5 rounded-2xl shadow-lg hover:shadow-purple-500/30 transition transform hover:scale-102 cursor-pointer mt-2">
                    Create Account & Add Kids →
                </button>
            </form>

            {{-- Login Form --}}
            <form x-show="authTab === 'login'" action="{{ url('/parent/login') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="parent@example.com"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none font-bold">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-bold">
                        <input type="checkbox" name="remember" class="rounded text-purple-600">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full font-heading font-black text-base bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3.5 rounded-2xl shadow-lg hover:shadow-purple-500/30 transition transform hover:scale-102 cursor-pointer mt-2">
                    Sign In & Continue →
                </button>
            </form>

        </div>
    </div>

</body>
</html>
