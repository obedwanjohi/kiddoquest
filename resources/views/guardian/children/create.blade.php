@extends('layouts.app')
@section('title', 'Add a Child — KiddoQuest CBC')

@section('content')
<div class="min-h-screen bg-[#FCFAFF]">
    <header class="bg-white border-b border-purple-100 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('kids.profiles') }}" class="text-slate-500 hover:text-purple-600 text-sm font-bold">← Back to Profiles</a>
                <span class="text-2xl">🦁</span>
                <h1 class="text-xl font-black text-purple-700 font-heading">Add a New Child</h1>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <div class="text-center mb-6">
                <div class="text-5xl mb-2">🧒</div>
                <h2 class="text-2xl font-bold text-gray-800">Create a New Profile</h2>
                <p class="text-gray-500 text-sm mt-1">Each child gets their own adventure!</p>
            </div>

            <form action="{{ route('guardian.children.store') }}" method="POST" id="createForm">
                @csrf

                {{-- Name --}}
                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Child's Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                        placeholder="e.g., Emma"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Avatar Picker (identifier-based) --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pick a Character Friend</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                        @foreach($avatars as $id => $avatar)
                            <label class="cursor-pointer group">
                                <input type="radio" name="avatar" value="{{ $id }}" id="avatar-{{ $id }}" class="peer sr-only" @if(old('avatar') === $id) checked @endif>
                                <div class="w-full aspect-square flex flex-col items-center justify-center bg-gray-50 border-2 border-transparent rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-100 transition p-1">
                                    <span class="text-2xl sm:text-3xl">{{ $avatar['emoji'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p id="avatarName" class="text-center text-sm text-purple-600 font-medium mt-3"></p>
                    @error('avatar') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Favorite Color --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Favorite Color <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <p class="text-xs text-gray-400 mb-2">Leo will use this color to make the experience feel personal!</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $colorName => $colorHex)
                            <label class="cursor-pointer">
                                <input type="radio" name="favorite_color" value="{{ $colorName }}" id="color-{{ $colorName }}" class="peer sr-only" @if(old('favorite_color') === $colorName) checked @endif>
                                <div class="w-10 h-10 rounded-full border-2 border-gray-200 peer-checked:border-gray-800 peer-checked:scale-110 transition shadow-sm"
                                     style="background-color: {{ $colorHex }};"></div>
                            </label>
                        @endforeach
                    </div>
                    @error('favorite_color') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Birthdate with Live Level Recommendation --}}
                <div class="mb-8">
                    <label for="birthdate" class="block text-sm font-bold text-gray-700 mb-2">
                        Birthdate <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate') }}"
                        max="{{ date('Y-m-d', strtotime('-1 year')) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        oninput="updateLevelPreview()">
                    <div id="levelPreview" class="hidden mt-3 bg-purple-50 border border-purple-200 rounded-xl px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl" id="levelEmoji">📊</span>
                            <div>
                                <p class="text-sm text-gray-600">Recommended Level</p>
                                <p class="text-lg font-bold text-purple-700" id="levelText">PP1</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">You can change this later.</p>
                    </div>
                    @error('birthdate') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('kids.profiles') }}" class="text-slate-500 hover:text-slate-700 text-sm font-bold">Cancel</a>
                    <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3.5 rounded-2xl font-black text-lg hover:shadow-lg transition transform hover:scale-102 cursor-pointer font-heading">
                        ✨ Create Profile
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Show avatar character name on selection
document.querySelectorAll('input[name="avatar"]').forEach(input => {
    input.addEventListener('change', function() {
        const names = {
            'lion': '🦁 Leo the Lion', 'elephant': '🐘 Eli the Elephant',
            'giraffe': '🦒 Gigi the Giraffe', 'monkey': '🐒 Milo the Monkey',
            'tiger': '🐯 Tara the Tiger', 'fox': '🦊 Finn the Fox',
            'panda': '🐼 Pip the Panda', 'koala': '🐨 Koko the Koala',
            'rabbit': '🐰 Ruby the Rabbit', 'frog': '🐸 Flick the Frog',
            'owl': '🦉 Olive the Owl', 'cat': '🐱 Cleo the Cat',
            'dog': '🐶 Dash the Dog', 'cow': '🐮 Clover the Cow',
            'pig': '🐷 Penny the Pig', 'unicorn': '🦄 Uma the Unicorn',
        };
        document.getElementById('avatarName').textContent = names[this.value] || '';
    });
});

// Live level recommendation from birthdate
function updateLevelPreview() {
    const dateStr = document.getElementById('birthdate').value;
    const preview = document.getElementById('levelPreview');

    if (!dateStr) {
        preview.classList.add('hidden');
        return;
    }

    const birth = new Date(dateStr);
    const now = new Date();
    let age = now.getFullYear() - birth.getFullYear();
    const m = now.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) age--;

    let level = 'PP1';
    let emoji = '🎨';

    if (age <= 3) { level = 'Play Group'; emoji = '🧸'; }
    else if (age === 4) { level = 'PP1'; emoji = '🎨'; }
    else if (age === 5) { level = 'PP2'; emoji = '📖'; }
    else if (age === 6) { level = 'Grade 1'; emoji = '✏️'; }
    else if (age === 7) { level = 'Grade 2'; emoji = '📚'; }
    else { level = 'Grade 3'; emoji = '🏆'; }

    document.getElementById('levelText').textContent = level;
    document.getElementById('levelEmoji').textContent = emoji;
    preview.classList.remove('hidden');
}
</script>
@endsection