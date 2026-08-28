@extends('layouts.app')
@section('title', 'Create Child Profile — KiddoQuest CBC')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-[#F5F3FF] via-[#FAF5FF] to-[#FFFBEB] py-6 sm:py-10 px-4 sm:px-6">
    
    {{-- TOP NAVBAR --}}
    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between">
        <a href="{{ route('kids.profiles') }}" 
           class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-2xl border-2 border-purple-200 text-purple-900 font-extrabold text-xs sm:text-sm shadow-sm hover:bg-white hover:border-purple-400 hover:shadow-md transition cursor-pointer">
            <span>←</span>
            <span>Back to Profiles</span>
        </a>
        <div class="flex items-center gap-2 bg-amber-100 border border-amber-300 px-3 py-1.5 rounded-full text-amber-900 font-black text-xs">
            <span>⭐</span>
            <span>New Explorer Pass</span>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <main class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl sm:rounded-[36px] shadow-2xl border-4 border-purple-100 p-6 sm:p-10 relative overflow-hidden">
            
            {{-- Decorative Floating Bubbles --}}
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-200/40 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-pink-200/40 rounded-full blur-2xl pointer-events-none"></div>

            {{-- HEADER --}}
            <div class="text-center mb-8 relative">
                <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-tr from-purple-500 to-pink-500 text-white shadow-xl shadow-purple-500/20 text-4xl sm:text-5xl mx-auto mb-4 transform hover:rotate-6 transition">
                    ✨
                </div>
                <h1 class="font-heading text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    Add Your Little Explorer
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 font-bold mt-1.5 max-w-md mx-auto">
                    Set up their personalized CBC learning adventure with their very own character buddy!
                </p>
            </div>

            {{-- LIVE PREVIEW BADGE CARD --}}
            <div id="livePassportCard" 
                 class="mb-8 p-4 sm:p-5 rounded-3xl bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-800 text-white shadow-xl border-2 border-purple-400/30 flex items-center justify-between gap-4 transition-all transform hover:scale-[1.01]">
                <div class="flex items-center gap-4 min-w-0">
                    <div id="previewAvatarBox" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-md border border-white/40 flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0 shadow-inner">
                        🦁
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-wider bg-white/25 px-2.5 py-0.5 rounded-full">
                                KiddoQuest Passport
                            </span>
                            <span id="previewLevelBadge" class="text-[10px] font-black uppercase tracking-wider bg-amber-400 text-amber-950 px-2 py-0.5 rounded-full">
                                PP1
                            </span>
                        </div>
                        <h2 id="previewName" class="font-heading text-xl sm:text-2xl font-black truncate mt-0.5">
                            Little Explorer
                        </h2>
                        <p id="previewBuddy" class="text-xs text-purple-200 font-semibold truncate">
                            Playing alongside Leo the Lion 🦁
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex flex-col items-end flex-shrink-0">
                    <span class="text-xs text-purple-200 font-bold">Starting Rewards</span>
                    <span class="text-lg font-black text-amber-300 flex items-center gap-1">
                        <span>⭐</span> 0 Stars
                    </span>
                </div>
            </div>

            {{-- FORM --}}
            <form action="{{ route('guardian.children.store') }}" method="POST" id="createForm" class="space-y-7">
                @csrf

                {{-- 1. CHILD NAME --}}
                <div>
                    <label for="name" class="block font-heading text-base sm:text-lg font-black text-slate-800 mb-2">
                        1. What is your child's name or nickname? <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                               placeholder="e.g., Liam, Zuri, Emma, Ethan"
                               oninput="updateLivePreview()"
                               class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white focus:ring-4 focus:ring-purple-100 outline-none text-base sm:text-lg font-bold text-slate-900 transition shadow-inner">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-xl pointer-events-none">
                            ✍️
                        </div>
                    </div>
                    @error('name') 
                        <p class="text-rose-600 text-xs font-black mt-2 flex items-center gap-1">
                            <span>⚠️</span> {{ $message }}
                        </p> 
                    @enderror
                </div>

                {{-- 2. CHARACTER COMPANION PICKER --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="font-heading text-base sm:text-lg font-black text-slate-800">
                            2. Choose their Learning Buddy <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-100">
                            Tap to pick friend
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-[360px] overflow-y-auto p-1.5 rounded-2xl bg-slate-50 border-2 border-slate-200">
                        @foreach($avatars as $id => $avatar)
                            @php
                                $isDefault = (old('avatar', 'lion') === $id);
                                $roles = [
                                    'lion' => 'Safari Guide',
                                    'elephant' => 'Math Wizard',
                                    'giraffe' => 'Word Master',
                                    'monkey' => 'Joyful Play',
                                    'tiger' => 'Brave Thinker',
                                    'fox' => 'Swift Solver',
                                    'panda' => 'Calm Explorer',
                                    'koala' => 'Nature Scout',
                                    'rabbit' => 'Speedy Learner',
                                    'frog' => 'Curious Leaper',
                                    'owl' => 'Wisdom Bird',
                                    'cat' => 'Super Creative',
                                    'dog' => 'Loyal Friend',
                                    'cow' => 'Farm Champion',
                                    'pig' => 'Happy Artist',
                                    'unicorn' => 'Magic Spark',
                                    'dino' => 'Dino Power',
                                    'robot' => 'Tech Genius',
                                    'dragon' => 'Fire Champion',
                                ];
                                $role = $roles[$id] ?? 'Learning Friend';
                            @endphp
                            <label class="cursor-pointer relative group select-none">
                                <input type="radio" name="avatar" value="{{ $id }}" id="avatar-{{ $id }}" 
                                       class="peer sr-only avatar-radio" 
                                       @if($isDefault) checked @endif
                                       onchange="updateLivePreview()">
                                
                                <div class="p-3 rounded-2xl bg-white border-2 border-slate-200 text-center transition transform group-hover:scale-102 group-hover:border-purple-300 peer-checked:border-purple-600 peer-checked:bg-purple-50 peer-checked:shadow-lg peer-checked:shadow-purple-500/15 peer-checked:ring-2 peer-checked:ring-purple-400 flex flex-col items-center justify-between min-h-[110px]">
                                    
                                    {{-- Selected Badge --}}
                                    <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-purple-600 text-white text-[10px] items-center justify-center font-black hidden peer-checked:flex shadow">
                                        ✓
                                    </div>

                                    <div class="text-4xl sm:text-5xl my-1 transform group-hover:scale-110 transition">
                                        {{ $avatar['emoji'] }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-slate-800 leading-tight">
                                            {{ $avatar['name'] }}
                                        </div>
                                        <div class="text-[10px] font-bold text-purple-600 mt-0.5">
                                            {{ $role }}
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('avatar') 
                        <p class="text-rose-600 text-xs font-black mt-2">⚠️ {{ $message }}</p> 
                    @enderror
                </div>

                {{-- 3. CBC GRADE LEVEL / AGE --}}
                <div>
                    <label class="block font-heading text-base sm:text-lg font-black text-slate-800 mb-2">
                        3. Select CBC Grade or Age Stage
                    </label>
                    <p class="text-xs text-slate-500 font-bold mb-3">
                        This customizes difficulty, font sizes, and voice narration pacing for their age.
                    </p>

                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-4">
                        @php
                            $stages = [
                                ['code' => 'Play Group', 'label' => 'Playgroup', 'age' => 'Ages 2-3', 'emoji' => '🧸', 'years' => 3],
                                ['code' => 'PP1', 'label' => 'PP1', 'age' => 'Age 4', 'emoji' => '🎨', 'years' => 4],
                                ['code' => 'PP2', 'label' => 'PP2', 'age' => 'Age 5', 'emoji' => '📖', 'years' => 5],
                                ['code' => 'Grade 1', 'label' => 'Grade 1', 'age' => 'Age 6', 'emoji' => '✏️', 'years' => 6],
                                ['code' => 'Grade 2', 'label' => 'Grade 2', 'age' => 'Age 7', 'emoji' => '📚', 'years' => 7],
                                ['code' => 'Grade 3', 'label' => 'Grade 3', 'age' => 'Age 8+', 'emoji' => '🏆', 'years' => 8],
                            ];
                        @endphp
                        @foreach($stages as $stage)
                            <button type="button" 
                                    onclick="selectGradePill('{{ $stage['code'] }}', {{ $stage['years'] }})"
                                    id="grade-btn-{{ Str::slug($stage['code']) }}"
                                    class="grade-pill p-2.5 rounded-2xl border-2 border-slate-200 bg-white text-center hover:border-purple-400 transition cursor-pointer flex flex-col items-center justify-center">
                                <span class="text-2xl mb-1">{{ $stage['emoji'] }}</span>
                                <span class="text-xs font-black text-slate-900 leading-tight">{{ $stage['label'] }}</span>
                                <span class="text-[10px] text-slate-500 font-semibold">{{ $stage['age'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Optional Birthdate picker --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="text-xs font-bold text-slate-600">
                            <span>🎂 Or set Exact Birthday:</span>
                        </div>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate') }}"
                               max="{{ date('Y-m-d', strtotime('-1 year')) }}"
                               oninput="updateBirthdateLevel()"
                               class="w-full sm:w-auto px-4 py-2 rounded-xl bg-white border border-slate-300 text-xs font-bold text-slate-800 focus:border-purple-600 focus:outline-none">
                    </div>
                    @error('birthdate') 
                        <p class="text-rose-600 text-xs font-black mt-2">⚠️ {{ $message }}</p> 
                    @enderror
                </div>

                {{-- 4. FAVORITE COLOR --}}
                <div>
                    <label class="block font-heading text-base sm:text-lg font-black text-slate-800 mb-2">
                        4. Child's Favorite Adventure Color <span class="text-slate-400 text-xs font-normal">(Optional)</span>
                    </label>
                    <p class="text-xs text-slate-500 font-bold mb-3">
                        Their buddy will personalize the dashboard buttons and sparkles with this theme!
                    </p>

                    <div class="flex flex-wrap gap-3">
                        @foreach($colors as $colorName => $colorHex)
                            @php $isColorDefault = (old('favorite_color', 'purple') === $colorName); @endphp
                            <label class="cursor-pointer group">
                                <input type="radio" name="favorite_color" value="{{ $colorName }}" id="color-{{ $colorName }}" 
                                       class="peer sr-only color-radio" 
                                       @if($isColorDefault) checked @endif
                                       onchange="updateLivePreview()">
                                <div class="w-12 h-12 rounded-2xl border-4 border-white shadow-md peer-checked:ring-4 peer-checked:ring-purple-600 peer-checked:scale-110 transition transform group-hover:scale-105 flex items-center justify-center text-white text-xs font-black"
                                     style="background-color: {{ $colorHex }};">
                                    <span class="hidden peer-checked:inline">✓</span>
                                </div>
                                <span class="block text-center text-[10px] font-black text-slate-600 capitalize mt-1">
                                    {{ $colorName }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('favorite_color') 
                        <p class="text-rose-600 text-xs font-black mt-2">⚠️ {{ $message }}</p> 
                    @enderror
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="pt-4 border-t-2 border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('kids.profiles') }}" 
                       class="text-slate-500 hover:text-slate-800 font-extrabold text-sm order-2 sm:order-1">
                        ← Cancel
                    </a>
                    
                    <button type="submit" 
                            class="w-full sm:w-auto font-heading font-black text-base sm:text-lg bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white px-8 py-4 rounded-2xl shadow-xl shadow-purple-500/25 hover:shadow-purple-500/40 transition transform hover:scale-102 active:scale-98 cursor-pointer order-1 sm:order-2 flex items-center justify-center gap-2">
                        <span>✨ Launch Child Adventure</span>
                        <span>→</span>
                    </button>
                </div>

            </form>
        </div>
    </main>

</div>

{{-- SCRIPT FOR INSTANT REACTIVITY --}}
<script>
const avatarMap = {
    'lion': { emoji: '🦁', name: 'Leo the Lion' },
    'elephant': { emoji: '🐘', name: 'Eli the Elephant' },
    'giraffe': { emoji: '🦒', name: 'Gigi the Giraffe' },
    'monkey': { emoji: '🐒', name: 'Milo the Monkey' },
    'tiger': { emoji: '🐯', name: 'Tara the Tiger' },
    'fox': { emoji: '🦊', name: 'Finn the Fox' },
    'panda': { emoji: '🐼', name: 'Pip the Panda' },
    'koala': { emoji: '🐨', name: 'Koko the Koala' },
    'rabbit': { emoji: '🐰', name: 'Ruby the Rabbit' },
    'frog': { emoji: '🐸', name: 'Flick the Frog' },
    'owl': { emoji: '🦉', name: 'Olive the Owl' },
    'cat': { emoji: '🐱', name: 'Cleo the Cat' },
    'dog': { emoji: '🐶', name: 'Dash the Dog' },
    'cow': { emoji: '🐮', name: 'Clover the Cow' },
    'pig': { emoji: '🐷', name: 'Penny the Pig' },
    'unicorn': { emoji: '🦄', name: 'Uma the Unicorn' },
    'dino': { emoji: '🦖', name: 'Rex the Dino' },
    'robot': { emoji: '🤖', name: 'Beep the Robot' },
    'dragon': { emoji: '🐉', name: 'Ignis the Dragon' }
};

let currentLevel = 'PP1';

function selectGradePill(code, years) {
    currentLevel = code;
    document.querySelectorAll('.grade-pill').forEach(btn => {
        btn.classList.remove('border-purple-600', 'bg-purple-50', 'ring-2', 'ring-purple-400');
        btn.classList.add('border-slate-200', 'bg-white');
    });
    
    const target = document.getElementById('grade-btn-' + code.toLowerCase().replace(/\s+/g, '-'));
    if (target) {
        target.classList.remove('border-slate-200', 'bg-white');
        target.classList.add('border-purple-600', 'bg-purple-50', 'ring-2', 'ring-purple-400');
    }

    // Set estimated birthdate
    const d = new Date();
    d.setFullYear(d.getFullYear() - years);
    document.getElementById('birthdate').value = d.toISOString().split('T')[0];

    updateLivePreview();
}

function updateBirthdateLevel() {
    const val = document.getElementById('birthdate').value;
    if (!val) return;
    const birth = new Date(val);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const m = now.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) age--;

    if (age <= 3) currentLevel = 'Play Group';
    else if (age === 4) currentLevel = 'PP1';
    else if (age === 5) currentLevel = 'PP2';
    else if (age === 6) currentLevel = 'Grade 1';
    else if (age === 7) currentLevel = 'Grade 2';
    else currentLevel = 'Grade 3';

    updateLivePreview();
}

function updateLivePreview() {
    const nameInput = document.getElementById('name').value.trim();
    const previewName = document.getElementById('previewName');
    previewName.textContent = nameInput || 'Little Explorer';

    const selectedAvatar = document.querySelector('input[name="avatar"]:checked')?.value || 'lion';
    const buddyInfo = avatarMap[selectedAvatar] || avatarMap['lion'];

    document.getElementById('previewAvatarBox').textContent = buddyInfo.emoji;
    document.getElementById('previewBuddy').textContent = `Playing alongside ${buddyInfo.name} ${buddyInfo.emoji}`;
    document.getElementById('previewLevelBadge').textContent = currentLevel;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    selectGradePill('PP1', 4);
    updateLivePreview();
});
</script>
@endsection