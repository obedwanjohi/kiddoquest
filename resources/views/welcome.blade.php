<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KiddoQuest CBC — Kenya's #1 Learning Adventure for Playgroup, PP1 & PP2</title>
    <meta name="description" content="Engaging voiceover lessons, CBC curriculum quizzes, and magical adventure worlds in Maths, English & CRE for Kenyan kids ages 3 to 6.">

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CSS & Alpine.js --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Nunito', sans-serif; }
        .font-heading { font-family: 'Baloo 2', cursive, sans-serif; }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(3deg); }
        }
        @keyframes float-reverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(10px) rotate(-3deg); }
        }
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 15px rgba(251, 191, 36, 0.4)); }
            50% { transform: scale(1.05); filter: drop-shadow(0 0 25px rgba(251, 191, 36, 0.8)); }
        }
        @keyframes wiggle {
            0%, 100% { transform: rotate(-3deg); }
            50% { transform: rotate(3deg); }
        }

        .animate-float { animation: float-slow 4s ease-in-out infinite; }
        .animate-float-rev { animation: float-reverse 5s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .animate-wiggle { animation: wiggle 2s ease-in-out infinite; }

        .blob-bg {
            background: linear-gradient(135deg, #6366F1 0%, #A855F7 50%, #EC4899 100%);
        }
        .hero-pattern {
            background-color: #fcfaff;
            background-image: radial-gradient(#e0e7ff 1.5px, transparent 1.5px), radial-gradient(#fae8ff 1.5px, #fcfaff 1.5px);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
        }
        .card-3d {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .card-3d:hover {
            transform: translateY(-8px) scale(1.02);
        }
    </style>
</head>

<body class="bg-[#FCFAFF] text-slate-800 antialiased overflow-x-hidden" x-data="{ authModal: false, authTab: 'register' }">

    {{-- TOP NOTICE BANNER --}}
    <div class="bg-gradient-to-r from-amber-500 via-pink-500 to-indigo-600 text-white text-xs sm:text-sm font-black py-2 px-4 text-center shadow-sm">
        <span class="inline-block animate-wiggle mr-1">🇰🇪</span>
        Aligned with Kenya CBC Curriculum 2026 • Over 800+ Voiceover Missions & Quizzes for Playgroup, PP1 & PP2!
    </div>

    {{-- NAVBAR --}}
    <nav class="sticky top-0 z-40 bg-white/90 backdrop-filter backdrop-blur-md border-b border-indigo-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 via-pink-500 to-purple-600 flex items-center justify-center text-2xl shadow-md transform group-hover:scale-110 transition-transform">
                    🦁
                </div>
                <div>
                    <span class="font-heading text-2xl sm:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-700 via-indigo-600 to-pink-600 tracking-tight">
                        KiddoQuest<span class="text-amber-500">.</span>
                    </span>
                    <span class="block text-[10px] font-extrabold uppercase tracking-widest text-indigo-500 -mt-1">CBC Early Years Adventure</span>
                </div>
            </a>

            {{-- Navigation Links (Desktop) --}}
            <div class="hidden md:flex items-center gap-8 font-bold text-sm text-slate-600">
                <a href="#worlds" class="hover:text-purple-600 transition">Subject Worlds 🌍</a>
                <a href="#levels" class="hover:text-purple-600 transition">CBC Levels 📚</a>
                <a href="#parent-powers" class="hover:text-purple-600 transition">Parent Zone 🛡️</a>
                <a href="#pricing" class="hover:text-purple-600 transition">M-Pesa Pricing 💳</a>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3">
                @if(Auth::guard('guardian')->check())
                    <a href="{{ route('kids.profiles') }}"
                       class="font-heading font-black text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white px-5 py-2.5 rounded-2xl shadow-lg hover:shadow-purple-500/30 transform hover:scale-105 transition flex items-center gap-2">
                        <span>🌟 Who's Playing?</span>
                    </a>
                @else
                    <button @click="authModal = true; authTab = 'login'"
                            class="font-bold text-sm text-slate-700 hover:text-purple-600 px-4 py-2 rounded-xl transition">
                        Sign In
                    </button>
                    <button @click="authModal = true; authTab = 'register'"
                            class="font-heading font-black text-sm bg-gradient-to-r from-amber-400 via-orange-500 to-pink-500 text-white px-5 py-2.5 rounded-2xl shadow-md hover:shadow-orange-500/30 transform hover:scale-105 transition flex items-center gap-1.5 cursor-pointer">
                        <span>Start Free 🚀</span>
                    </button>
                @endif
            </div>
        </div>
    </nav>

    {{-- HERO SECTION --}}
    <section class="relative hero-pattern pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden">
        {{-- Floating Background Badges --}}
        <div class="absolute top-12 left-6 sm:left-16 text-4xl animate-float opacity-80 select-none">⭐</div>
        <div class="absolute top-24 right-8 sm:right-24 text-5xl animate-float-rev opacity-80 select-none">🎈</div>
        <div class="absolute bottom-16 left-12 sm:left-32 text-4xl animate-float opacity-70 select-none">🎨</div>
        <div class="absolute bottom-20 right-10 sm:right-36 text-5xl animate-float-rev opacity-80 select-none">🧩</div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                {{-- Left Copy --}}
                <div class="lg:col-span-7 text-center lg:text-left">
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 bg-purple-100/80 border border-purple-200 text-purple-800 text-xs sm:text-sm font-extrabold px-4 py-1.5 rounded-full mb-6 shadow-sm">
                        <span class="text-base">🏆</span> Rated Kenya's #1 Early Childhood CBC Learning App
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 leading-[1.1] mb-6">
                        Where Little Explorers <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-500 to-amber-500">
                            Fall in Love With Learning!
                        </span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="text-lg sm:text-xl text-slate-600 font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-8">
                        Turn screen time into active mastery. <strong>825+ voiceover video missions</strong>, interactive quizzes, and magical adventure worlds in <strong>Mathematics, English & CRE</strong> designed specifically for Kenyan children aged 3 to 6.
                    </p>

                    {{-- Hero CTAs --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-8">
                        @if(Auth::guard('guardian')->check())
                            <a href="{{ route('kids.profiles') }}"
                               class="w-full sm:w-auto font-heading font-black text-lg bg-gradient-to-r from-amber-400 via-pink-500 to-purple-600 text-white px-8 py-4 rounded-3xl shadow-xl hover:shadow-purple-500/40 transform hover:scale-105 transition flex items-center justify-center gap-3">
                                <span>🚀 Open KiddoQuest Player</span>
                            </a>
                        @else
                            <button @click="authModal = true; authTab = 'register'"
                                    class="w-full sm:w-auto font-heading font-black text-lg bg-gradient-to-r from-amber-400 via-orange-500 to-pink-500 text-white px-8 py-4 rounded-3xl shadow-xl hover:shadow-orange-500/40 transform hover:scale-105 transition flex items-center justify-center gap-3 cursor-pointer">
                                <span>✨ Start Free 7-Day Trial</span>
                                <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs">No Card Required</span>
                            </button>
                            <button @click="authModal = true; authTab = 'login'"
                                    class="w-full sm:w-auto font-heading font-extrabold text-base bg-white border-2 border-slate-200 text-slate-700 px-6 py-4 rounded-3xl hover:border-purple-300 hover:text-purple-600 transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                <span>🔐 Parent Sign In</span>
                            </button>
                        @endif
                    </div>

                    {{-- Trust Metric Badges --}}
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-200/80 max-w-lg mx-auto lg:mx-0">
                        <div class="text-center lg:text-left">
                            <div class="font-heading text-2xl font-black text-purple-700">825+</div>
                            <div class="text-xs font-bold text-slate-500">Audio Missions</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="font-heading text-2xl font-black text-pink-600">100%</div>
                            <div class="text-xs font-bold text-slate-500">Ad-Free & Safe</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="font-heading text-2xl font-black text-amber-500">CBC</div>
                            <div class="text-xs font-bold text-slate-500">Aligned Curriculum</div>
                        </div>
                    </div>
                </div>

                {{-- Right Interactive Character Showcase --}}
                <div class="lg:col-span-5 relative">
                    {{-- Decorative Glow Circle --}}
                    <div class="absolute inset-0 bg-gradient-to-tr from-purple-400/30 via-pink-400/30 to-amber-300/40 rounded-[3rem] filter blur-2xl transform rotate-6 scale-95"></div>

                    {{-- Main Game Card Preview --}}
                    <div class="relative bg-white border-4 border-white rounded-[2.5rem] shadow-2xl p-6 sm:p-8 transform hover:rotate-1 transition-transform">
                        {{-- Top Header inside card --}}
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-3xl shadow-inner">🦁</div>
                                <div>
                                    <h4 class="font-heading font-black text-lg text-slate-800">Leo the Lion</h4>
                                    <p class="text-xs font-bold text-amber-600">Your Learning Guide</p>
                                </div>
                            </div>
                            <div class="bg-amber-50 border border-amber-200 rounded-full px-3 py-1 flex items-center gap-1.5">
                                <span class="text-sm">⭐</span>
                                <span class="font-black text-sm text-amber-700">1,450 Stars</span>
                            </div>
                        </div>

                        {{-- Character Squad Grid --}}
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3">Meet Your Kiddo Friends</p>
                        <div class="grid grid-cols-4 gap-3 mb-6">
                            <div class="bg-purple-50 hover:bg-purple-100 rounded-2xl p-3 text-center border-2 border-purple-200 transition cursor-pointer">
                                <div class="text-3xl mb-1 animate-bounce">🦁</div>
                                <span class="block text-[11px] font-black text-purple-800">Leo</span>
                            </div>
                            <div class="bg-blue-50 hover:bg-blue-100 rounded-2xl p-3 text-center border-2 border-blue-200 transition cursor-pointer">
                                <div class="text-3xl mb-1">🐘</div>
                                <span class="block text-[11px] font-black text-blue-800">Eli</span>
                            </div>
                            <div class="bg-pink-50 hover:bg-pink-100 rounded-2xl p-3 text-center border-2 border-pink-200 transition cursor-pointer">
                                <div class="text-3xl mb-1">🦒</div>
                                <span class="block text-[11px] font-black text-pink-800">Gigi</span>
                            </div>
                            <div class="bg-emerald-50 hover:bg-emerald-100 rounded-2xl p-3 text-center border-2 border-emerald-200 transition cursor-pointer">
                                <div class="text-3xl mb-1">🐼</div>
                                <span class="block text-[11px] font-black text-emerald-800">Pip</span>
                            </div>
                        </div>

                        {{-- Mini Interactive Sample Question --}}
                        <div class="bg-gradient-to-br from-indigo-900 to-purple-950 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden"
                             x-data="{ answered: false, correct: false }">
                            <div class="flex items-center justify-between text-xs font-bold text-indigo-300 mb-2">
                                <span>🔢 Math Adventure Mission #12</span>
                                <span>🔊 Voiceover Active</span>
                            </div>
                            <p class="font-heading text-lg font-black text-amber-300 mb-3">
                                "How many juicy red apples are there? 🍎🍎🍎"
                            </p>

                            <div class="grid grid-cols-3 gap-2">
                                <button @click="answered = true; correct = false"
                                        class="py-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-black text-sm border border-white/20 transition">
                                    2
                                </button>
                                <button @click="answered = true; correct = true"
                                        :class="answered && correct ? 'bg-emerald-500 text-white border-emerald-400' : 'bg-white/10 hover:bg-white/20'"
                                        class="py-2.5 rounded-xl font-black text-sm border border-white/20 transition">
                                    3 ⭐
                                </button>
                                <button @click="answered = true; correct = false"
                                        class="py-2.5 bg-white/10 hover:bg-white/20 rounded-xl font-black text-sm border border-white/20 transition">
                                    4
                                </button>
                            </div>

                            <div x-show="answered" x-transition class="mt-3 text-center text-xs font-black"
                                 :class="correct ? 'text-emerald-400' : 'text-rose-400'">
                                <span x-text="correct ? '🎉 Super Smart! +10 Star Coins Earned!' : 'Try again! You can do it! 💪'"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- SUBJECT WORLDS SHOWCASE --}}
    <section id="worlds" class="py-20 bg-white border-t border-b border-indigo-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-purple-600 bg-purple-50 px-4 py-1.5 rounded-full border border-purple-200">
                    🌍 Comprehensive Curriculum
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-900 mt-4 mb-4">
                    3 Magical Subject Worlds to Explore
                </h2>
                <p class="text-base sm:text-lg text-slate-600 font-medium">
                    Every lesson is crafted with native Kenyan voice acting, playful sound effects, and visual reinforcement tailored for young hands and minds.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- Subject 1: Mathematics --}}
                <div class="card-3d bg-gradient-to-b from-amber-50/80 to-white border-2 border-amber-200/80 rounded-[2.5rem] p-8 shadow-sm hover:shadow-xl relative flex flex-col justify-between">
                    <div>
                        <div class="w-16 h-16 rounded-3xl bg-amber-400 text-3xl flex items-center justify-center shadow-lg mb-6">
                            🔢
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">Maths Mountain</h3>
                        <p class="text-sm text-slate-600 font-medium mb-6">
                            Build rock-solid number sense, counting confidence, and real-world math skills.
                        </p>
                        <ul class="space-y-3 text-xs sm:text-sm font-bold text-slate-700 mb-6">
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-500">✓</span> Number recognition 1 to 20
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-500">✓</span> Shapes, sizes & color sorting
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-500">✓</span> Kenyan Currency Coins (KES 1, 5, 10, 20)
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-amber-500">✓</span> Visual addition & subtraction
                            </li>
                        </ul>
                    </div>
                    <div class="bg-amber-100/60 rounded-2xl p-3 text-center text-xs font-black text-amber-800">
                        415+ Interactive Missions
                    </div>
                </div>

                {{-- Subject 2: English & Phonics --}}
                <div class="card-3d bg-gradient-to-b from-purple-50/80 to-white border-2 border-purple-200/80 rounded-[2.5rem] p-8 shadow-sm hover:shadow-xl relative flex flex-col justify-between">
                    <div>
                        <div class="w-16 h-16 rounded-3xl bg-purple-600 text-white text-3xl flex items-center justify-center shadow-lg mb-6">
                            🔤
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">English Jungle</h3>
                        <p class="text-sm text-slate-600 font-medium mb-6">
                            From first phonics sounds to fluent, confident sentence reading and speech.
                        </p>
                        <ul class="space-y-3 text-xs sm:text-sm font-bold text-slate-700 mb-6">
                            <li class="flex items-center gap-2.5">
                                <span class="text-purple-600">✓</span> Phonics & letter sound blending
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-purple-600">✓</span> Digraphs, blends & rhyming words
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-purple-600">✓</span> High-frequency sight words
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-purple-600">✓</span> Spoken pronunciation & vocabulary
                            </li>
                        </ul>
                    </div>
                    <div class="bg-purple-100/60 rounded-2xl p-3 text-center text-xs font-black text-purple-800">
                        350+ Phonics & Reading Missions
                    </div>
                </div>

                {{-- Subject 3: CRE / Values --}}
                <div class="card-3d bg-gradient-to-b from-pink-50/80 to-white border-2 border-pink-200/80 rounded-[2.5rem] p-8 shadow-sm hover:shadow-xl relative flex flex-col justify-between">
                    <div>
                        <div class="w-16 h-16 rounded-3xl bg-pink-500 text-white text-3xl flex items-center justify-center shadow-lg mb-6">
                            🕊️
                        </div>
                        <h3 class="font-heading text-2xl font-black text-slate-900 mb-2">CRE & Values Safari</h3>
                        <p class="text-sm text-slate-600 font-medium mb-6">
                            Nurture moral values, Bible stories, kindness, empathy, and positive character.
                        </p>
                        <ul class="space-y-3 text-xs sm:text-sm font-bold text-slate-700 mb-6">
                            <li class="flex items-center gap-2.5">
                                <span class="text-pink-500">✓</span> God's beautiful creation & nature
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-pink-500">✓</span> Bible heroes (David, Noah, Moses, Jesus)
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-pink-500">✓</span> Kindness, sharing & helping family
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="text-pink-500">✓</span> Daily prayers & Christian values
                            </li>
                        </ul>
                    </div>
                    <div class="bg-pink-100/60 rounded-2xl p-3 text-center text-xs font-black text-pink-800">
                        200+ Story & Values Missions
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- CBC LEVELS BREAKDOWN --}}
    <section id="levels" class="py-20 bg-[#FCFAFF]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-200">
                    🎯 Built for Your Child's Age
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-900 mt-4 mb-4">
                    Tailored for Every CBC Stage
                </h2>
                <p class="text-base sm:text-lg text-slate-600 font-medium">
                    Content automatically adapts to your child's age group so they never feel overwhelmed or bored.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Playgroup --}}
                <div class="bg-white border-2 border-indigo-100 rounded-3xl p-8 shadow-sm relative overflow-hidden">
                    <div class="text-4xl mb-4">🧸</div>
                    <div class="inline-block bg-blue-100 text-blue-800 text-xs font-extrabold px-3 py-1 rounded-full mb-3">
                        Ages 3 to 4
                    </div>
                    <h3 class="font-heading text-2xl font-black text-slate-900 mb-3">Play Group</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-6">
                        Gentle, visual, audio-led learning. Big buttons, friendly farm animals, color matching, and 60-second bite-sized missions.
                    </p>
                    <div class="text-xs font-extrabold text-indigo-600 flex items-center gap-1">
                        <span>Exploration & Sensory Skills</span> →
                    </div>
                </div>

                {{-- PP1 --}}
                <div class="bg-gradient-to-b from-purple-500 to-indigo-600 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden transform md:-translate-y-2">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="text-4xl mb-4">🎨</div>
                    <div class="inline-block bg-amber-400 text-amber-950 text-xs font-black px-3 py-1 rounded-full mb-3">
                        Ages 4 to 5 • Most Popular
                    </div>
                    <h3 class="font-heading text-2xl font-black text-white mb-3">Pre-Primary 1 (PP1)</h3>
                    <p class="text-xs sm:text-sm text-purple-100 leading-relaxed font-medium mb-6">
                        Core foundation building. Phonics blends, counting objects to 10, recognizing shapes, tracing patterns, and basic storytelling.
                    </p>
                    <div class="text-xs font-extrabold text-amber-300 flex items-center gap-1">
                        <span>Foundation & Literacy</span> →
                    </div>
                </div>

                {{-- PP2 --}}
                <div class="bg-white border-2 border-indigo-100 rounded-3xl p-8 shadow-sm relative overflow-hidden">
                    <div class="text-4xl mb-4">📖</div>
                    <div class="inline-block bg-pink-100 text-pink-800 text-xs font-extrabold px-3 py-1 rounded-full mb-3">
                        Ages 5 to 6
                    </div>
                    <h3 class="font-heading text-2xl font-black text-slate-900 mb-3">Pre-Primary 2 (PP2)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-6">
                        Primary school readiness! Simple math addition/subtraction, Kenyan currency, full sentence comprehension, and Bible story recall.
                    </p>
                    <div class="text-xs font-extrabold text-indigo-600 flex items-center gap-1">
                        <span>Grade 1 Preparation</span> →
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PARENT SUPERPOWERS SECTION --}}
    <section id="parent-powers" class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <div class="lg:col-span-6">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-pink-400 bg-pink-950/60 border border-pink-800/60 px-4 py-1.5 rounded-full">
                        🛡️ Peace of Mind for Parents
                    </span>
                    <h2 class="font-heading text-3xl sm:text-5xl font-black text-white mt-4 mb-6 leading-tight">
                        You Stay in Full Control Behind the <span class="text-amber-400">Parent PIN</span>
                    </h2>
                    <p class="text-base sm:text-lg text-slate-300 font-medium mb-8">
                        KiddoQuest is built to give your children total joy and give you total clarity. Check accuracy heatmaps, set healthy limits, and watch them thrive.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-2xl shrink-0">
                                ⏱️
                            </div>
                            <div>
                                <h4 class="font-bold text-base text-white">Daily Screen-Time Limits</h4>
                                <p class="text-xs sm:text-sm text-slate-400 mt-1">Set 15, 30, or 45-minute timers. When time is up, Leo bids goodnight with zero tantrums.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600/30 border border-emerald-500/40 flex items-center justify-center text-2xl shrink-0">
                                📊
                            </div>
                            <div>
                                <h4 class="font-bold text-base text-white">Smart Skill Heatmaps</h4>
                                <p class="text-xs sm:text-sm text-slate-400 mt-1">Instantly see which skills your child has mastered (e.g. 95% in counting) and where they need a little encouragement.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-600/30 border border-amber-500/40 flex items-center justify-center text-2xl shrink-0">
                                🔒
                            </div>
                            <div>
                                <h4 class="font-bold text-base text-white">4-Digit Parent Security Gate</h4>
                                <p class="text-xs sm:text-sm text-slate-400 mt-1">Kids cannot leave the learning zone or access subscription/M-Pesa settings without your secret PIN.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Parent Dashboard Preview Card --}}
                <div class="lg:col-span-6">
                    <div class="bg-slate-800 border-2 border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl">
                        <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-xl">
                                    📊
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">Winnie's Learning Report</h4>
                                    <p class="text-xs text-slate-400">Pre-Primary 1 • Active Today</p>
                                </div>
                            </div>
                            <span class="bg-emerald-500/20 text-emerald-400 text-xs font-extrabold px-3 py-1 rounded-full border border-emerald-500/30">
                                88% Accuracy
                            </span>
                        </div>

                        {{-- Progress Bars --}}
                        <div class="space-y-4 mb-6">
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                    <span>Counting Objects (1–10)</span>
                                    <span class="text-emerald-400">95% (Mastered)</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2.5">
                                    <div class="bg-emerald-500 h-2.5 rounded-full" style="width: 95%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                    <span>Letter Sounds & Phonics</span>
                                    <span class="text-indigo-400">85% (Strong)</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2.5">
                                    <div class="bg-indigo-500 h-2.5 rounded-full" style="width: 85%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                    <span>Shape Identification</span>
                                    <span class="text-amber-400">75% (Growing)</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2.5">
                                    <div class="bg-amber-500 h-2.5 rounded-full" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-900/80 rounded-2xl p-4 border border-slate-700/80 text-xs text-slate-300">
                            <span class="text-amber-400 font-bold">💡 Leo's Tip for Parents:</span>
                            "Winnie confuses triangles and squares sometimes. Point out 3-sided pizza slices during lunch today!"
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- PRICING SECTION (M-PESA) --}}
    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-4 py-1.5 rounded-full border border-emerald-200">
                    💳 Simple & Affordable Kenyan Pricing
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-slate-900 mt-4 mb-4">
                    Invest in Your Child's Future for Less Than a Snack
                </h2>
                <p class="text-base sm:text-lg text-slate-600 font-medium">
                    One simple subscription covers your <strong>entire family</strong> with unlimited child profiles. Pay securely via Safaricom M-Pesa STK push.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto items-center">

                {{-- Monthly Plan --}}
                <div class="bg-[#FCFAFF] border-2 border-slate-200 rounded-[2.5rem] p-8 shadow-sm text-center">
                    <h3 class="font-heading text-2xl font-black text-slate-800 mb-2">Monthly Quest</h3>
                    <p class="text-xs text-slate-500 font-semibold mb-6">Flexible month-to-month learning</p>
                    <div class="mb-6">
                        <span class="text-xs font-bold text-slate-400">KES</span>
                        <span class="font-heading text-5xl font-black text-slate-900">200</span>
                        <span class="text-xs text-slate-500 font-bold">/ month</span>
                    </div>

                    <ul class="space-y-3 text-xs sm:text-sm font-bold text-slate-600 mb-8 text-left max-w-xs mx-auto">
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> All 825+ CBC Missions</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Up to 4 Child Profiles</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Instant M-Pesa STK Push</li>
                        <li class="flex items-center gap-2"><span class="text-emerald-500">✓</span> Full Parent Analytics</li>
                    </ul>

                    <button @click="authModal = true; authTab = 'register'"
                            class="w-full font-heading font-black text-base bg-slate-900 hover:bg-slate-800 text-white py-4 rounded-2xl transition cursor-pointer">
                        Get Started →
                    </button>
                </div>

                {{-- Annual Plan (Best Value) --}}
                <div class="bg-gradient-to-b from-purple-600 to-indigo-700 text-white border-4 border-amber-400 rounded-[2.5rem] p-8 shadow-2xl text-center relative overflow-hidden">
                    <div class="absolute top-4 right-4 bg-amber-400 text-amber-950 font-black text-[10px] uppercase px-3 py-1 rounded-full shadow-md">
                        Save 25% 🔥
                    </div>

                    <h3 class="font-heading text-2xl font-black text-white mb-2">Annual Champion</h3>
                    <p class="text-xs text-purple-200 font-semibold mb-6">Full year of uninterrupted mastery</p>
                    <div class="mb-6">
                        <span class="text-xs font-bold text-purple-200">KES</span>
                        <span class="font-heading text-5xl font-black text-amber-300">1,800</span>
                        <span class="text-xs text-purple-200 font-bold">/ year</span>
                    </div>

                    <ul class="space-y-3 text-xs sm:text-sm font-bold text-purple-100 mb-8 text-left max-w-xs mx-auto">
                        <li class="flex items-center gap-2"><span class="text-amber-300">✓</span> All 825+ CBC Missions</li>
                        <li class="flex items-center gap-2"><span class="text-amber-300">✓</span> Unlimited Child Profiles</li>
                        <li class="flex items-center gap-2"><span class="text-amber-300">✓</span> 3 Months Completely Free</li>
                        <li class="flex items-center gap-2"><span class="text-amber-300">✓</span> Priority Parent Support</li>
                    </ul>

                    <button @click="authModal = true; authTab = 'register'"
                            class="w-full font-heading font-black text-base bg-amber-400 hover:bg-amber-300 text-slate-900 py-4 rounded-2xl shadow-lg transform hover:scale-105 transition cursor-pointer">
                        Claim Best Value Plan 🚀
                    </button>
                </div>

            </div>
        </div>
    </section>

    {{-- PARENT REVIEWS / TESTIMONIALS --}}
    <section class="py-20 bg-[#FCFAFF] border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-pink-600 bg-pink-50 px-4 py-1.5 rounded-full border border-pink-200">
                    💬 Loved by Kenyan Families
                </span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-slate-900 mt-4 mb-2">
                    Hear From Fellow Parents
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <div class="flex text-amber-400 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-4">
                        "My 4-year-old son used to just watch mindless cartoons. Now he wakes up asking to do his English Jungle and counting with Leo the Lion! Incredible improvement."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center font-bold text-purple-700 text-xs">FM</div>
                        <div>
                            <p class="font-bold text-xs text-slate-800">Faith Mwangi</p>
                            <p class="text-[10px] text-slate-400 font-semibold">PP1 Parent • Nairobi</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <div class="flex text-amber-400 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-4">
                        "The fact that it aligns directly with the CBC terms taught in Kenyan pre-primary schools made all the difference. She breezed through her PP2 school assessments!"
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-pink-100 flex items-center justify-center font-bold text-pink-700 text-xs">DO</div>
                        <div>
                            <p class="font-bold text-xs text-slate-800">David Omondi</p>
                            <p class="text-[10px] text-slate-400 font-semibold">PP2 Parent • Kisumu</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <div class="flex text-amber-400 text-sm mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium mb-4">
                        "Zero advertisements and the 4-digit PIN lock gave me complete confidence to let my 3-year-old play safely on the tablet while I prepare dinner."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center font-bold text-amber-700 text-xs">AK</div>
                        <div>
                            <p class="font-bold text-xs text-slate-800">Amina Kiprop</p>
                            <p class="text-[10px] text-slate-400 font-semibold">Playgroup Parent • Nakuru</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-slate-950 text-white pt-16 pb-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-pink-500 flex items-center justify-center text-xl">🦁</div>
                        <span class="font-heading text-2xl font-black text-white">KiddoQuest CBC</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-sm leading-relaxed mb-4">
                        Empowering Kenyan pre-primary learners through gamified CBC mastery, playful voiceover adventures, and peace of mind for parents.
                    </p>
                    <p class="text-xs text-slate-500 font-semibold">
                        Nairobi, Kenya • Made with ❤️ for Kenyan Kids
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-300 mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-semibold">
                        <li><a href="#worlds" class="hover:text-white transition">Subject Worlds</a></li>
                        <li><a href="#levels" class="hover:text-white transition">CBC Levels (PG, PP1, PP2)</a></li>
                        <li><a href="#pricing" class="hover:text-white transition">M-Pesa Pricing</a></li>
                        <li><a href="{{ route('guardian.login') }}" class="hover:text-white transition">Parent Portal</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-300 mb-4">Portals & Admin</h4>
                    <ul class="space-y-2 text-xs text-slate-400 font-semibold">
                        <li><a href="{{ route('kids.profiles') }}" class="hover:text-white transition">Kids Profile Launcher</a></li>
                        <li><a href="{{ route('guardian.login') }}" class="hover:text-white transition">Parent Login / Sign Up</a></li>
                        <li><a href="{{ route('admin.login') }}" class="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">⚙️ Admin Content Portal</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-900 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; {{ date('Y') }} KiddoQuest CBC. All Rights Reserved.</p>
                <p class="flex items-center gap-4">
                    <span>100% Kid Safe & Ad-Free</span>
                    <span>•</span>
                    <span>Safaricom M-Pesa Enabled</span>
                </p>
            </div>
        </div>
    </footer>

    {{-- AUTH POPUP MODAL (LOGIN & SIGN UP) --}}
    <div x-show="authModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-filter backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div @click.away="authModal = false"
             class="bg-white rounded-[2.5rem] shadow-2xl p-6 sm:p-8 max-w-md w-full relative border border-purple-100 transform"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-90 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">

            {{-- Close Button --}}
            <button @click="authModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-700 text-xl font-black">
                ✕
            </button>

            {{-- Mascot Header --}}
            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center text-3xl mx-auto mb-2 shadow-inner">
                    🦁
                </div>
                <h3 class="font-heading text-2xl font-black text-slate-900">
                    <span x-show="authTab === 'register'">Create Free Parent Account</span>
                    <span x-show="authTab === 'login'">Welcome Back Parent!</span>
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    <span x-show="authTab === 'register'">Start your child's 7-day learning adventure</span>
                    <span x-show="authTab === 'login'">Sign in to manage kids & view reports</span>
                </p>
            </div>

            {{-- Tabs --}}
            <div class="flex bg-slate-100 p-1 rounded-2xl mb-6">
                <button @click="authTab = 'register'"
                        :class="authTab === 'register' ? 'bg-white shadow-sm text-purple-700 font-black' : 'text-slate-500 font-bold'"
                        class="flex-1 py-2 text-xs rounded-xl transition">
                    Sign Up Free 🚀
                </button>
                <button @click="authTab = 'login'"
                        :class="authTab === 'login' ? 'bg-white shadow-sm text-purple-700 font-black' : 'text-slate-500 font-bold'"
                        class="flex-1 py-2 text-xs rounded-xl transition">
                    Sign In 🔐
                </button>
            </div>

            {{-- Register Form --}}
            <form x-show="authTab === 'register'" action="{{ url('/parent/register') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Parent Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Jane Mwangi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="parent@example.com"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">M-Pesa Phone Number (Optional)</label>
                    <input type="text" name="phone" placeholder="0712 345 678"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="At least 8 characters"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-type password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <button type="submit"
                        class="w-full font-heading font-black text-base bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3.5 rounded-2xl shadow-lg hover:shadow-purple-500/30 transition transform hover:scale-102 cursor-pointer mt-2">
                    Create Account & Add Kids →
                </button>
            </form>

            {{-- Login Form --}}
            <form x-show="authTab === 'login'" action="{{ url('/parent/login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="parent@example.com"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none font-medium">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-semibold">
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
