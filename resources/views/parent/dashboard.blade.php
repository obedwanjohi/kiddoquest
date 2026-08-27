@extends('layouts.app')

@section('title', 'Parent Dashboard — BZabc Kids')

@push('styles')
<style>
    .parent-bg {
        background: linear-gradient(180deg, #0F172A 0%, #1E1B4B 100%);
    }
    .parent-card {
        background: rgba(30, 41, 59, 0.95);
        border: 1px solid rgba(129, 140, 248, 0.25);
        border-radius: 24px;
        backdrop-filter: blur(12px);
    }
    .tab-btn-active {
        background: #6366F1 !important;
        color: #FFFFFF !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4) !important;
    }
    .tab-btn-inactive {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #94A3B8 !important;
        font-weight: 700 !important;
    }
    .subj-pill-active {
        background: rgba(99, 102, 241, 0.3) !important;
        border-color: #818CF8 !important;
        color: #FFFFFF !important;
        font-weight: 800 !important;
    }
    .skill-block-filled {
        background: #22C55E;
        box-shadow: 0 0 6px rgba(34, 197, 94, 0.5);
    }
    .skill-block-empty {
        background: rgba(255, 255, 255, 0.1);
    }
    select option {
        background-color: #1E293B !important;
        color: #FFFFFF !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen parent-bg text-white pb-20">
    
    {{-- Top Header Bar --}}
    <div class="bg-slate-900/90 backdrop-blur-md border-b border-indigo-900/50 sticky top-0 z-30 px-4 py-3">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">👨‍👩‍👧</span>
                <div>
                    <h1 class="font-black text-base text-white leading-tight">Parent Companion Zone</h1>
                    <p class="text-[11px] text-indigo-300">KiddoQuest CBC Learning Control</p>
                </div>
            </div>

            <form method="POST" action="{{ route('parent.lock') }}">
                @csrf
                <button type="submit" class="bg-indigo-600/30 hover:bg-indigo-600 text-indigo-200 hover:text-white border border-indigo-500/40 font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all">
                    <span>🔒</span> Lock Zone
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-3 sm:px-4 pt-4" x-data="{ tab: 'overview', activeHistoryModal: null }">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms
                 class="mb-4 bg-emerald-500/20 border border-emerald-500 text-emerald-300 rounded-2xl p-3 text-sm font-bold text-center shadow-md">
                {{ session('success') }}
            </div>
        @endif

        {{-- 4-TAB INFORMATION ARCHITECTURE NAVIGATION --}}
        <div class="grid grid-cols-4 gap-1.5 mb-6 bg-slate-900/70 p-1.5 rounded-2xl border border-slate-800">
            <button @click="tab = 'overview'" 
                    :class="tab === 'overview' ? 'tab-btn-active' : 'tab-btn-inactive'"
                    class="py-2.5 rounded-xl text-xs sm:text-sm text-center transition-all flex flex-col sm:flex-row items-center justify-center gap-1 cursor-pointer">
                <span>🏠</span> <span class="hidden sm:inline">Daily </span>Overview
            </button>

            <button @click="tab = 'progress'" 
                    :class="tab === 'progress' ? 'tab-btn-active' : 'tab-btn-inactive'"
                    class="py-2.5 rounded-xl text-xs sm:text-sm text-center transition-all flex flex-col sm:flex-row items-center justify-center gap-1 cursor-pointer">
                <span>📚</span> Learning<span class="hidden sm:inline"> Progress</span>
            </button>

            <button @click="tab = 'support'" 
                    :class="tab === 'support' ? 'tab-btn-active' : 'tab-btn-inactive'"
                    class="py-2.5 rounded-xl text-xs sm:text-sm text-center transition-all flex flex-col sm:flex-row items-center justify-center gap-1 cursor-pointer">
                <span>🎯</span> Learning<span class="hidden sm:inline"> Support</span>
            </button>

            <button @click="tab = 'controls'" 
                    :class="tab === 'controls' ? 'tab-btn-active' : 'tab-btn-inactive'"
                    class="py-2.5 rounded-xl text-xs sm:text-sm text-center transition-all flex flex-col sm:flex-row items-center justify-center gap-1 cursor-pointer">
                <span>⚙️</span> Controls
            </button>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 1: 🏠 OVERVIEW (DAILY SNAPSHOT — "How is my child doing today?") --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'overview'" class="space-y-6">
            @foreach($children as $child)
                @php
                    $rep = $reports[$child->id] ?? [];
                    $canDo = $rep['can_do_now'] ?? [];
                    $assigned = $rep['assigned_mission'] ?? null;
                    $screenLimit = $child->daily_time_limit_minutes ?? 30;
                @endphp

                <div class="parent-card p-5">
                    
                    {{-- Child Quick Snapshot Header --}}
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-amber-400/20 border-2 border-amber-400 rounded-full flex items-center justify-center text-3xl">
                                {{ $child->avatar_emoji }}
                            </div>
                            <div>
                                <h3 class="font-black text-lg text-white leading-tight">{{ $child->name }}</h3>
                                <p class="text-xs text-indigo-300 font-semibold">Playing as {{ $child->avatar_name }}</p>
                            </div>
                        </div>

                        {{-- Learning Streak 🔥 --}}
                        <div class="bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-black px-3 py-1.5 rounded-2xl flex items-center gap-1">
                            <span class="text-base animate-pulse">🔥</span>
                            <span>{{ $rep['streak_days'] ?? 7 }} Day Streak</span>
                        </div>
                    </div>

                    {{-- Quick KPIs (Today's Stars, Screen Time, Focus Pill) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mb-4">
                        
                        {{-- Today's Stars --}}
                        <div class="bg-slate-800/80 rounded-2xl p-3 border border-slate-700/60 text-center">
                            <div class="text-xs text-slate-400 font-bold mb-0.5">Total Stars</div>
                            <div class="font-black text-lg text-amber-400 flex items-center justify-center gap-1">
                                <span>⭐</span> {{ number_format($child->total_stars) }}
                            </div>
                        </div>

                        {{-- Screen Time Meter --}}
                        <div class="bg-slate-800/80 rounded-2xl p-3 border border-slate-700/60 text-center">
                            <div class="text-xs text-slate-400 font-bold mb-0.5">Screen Time Used</div>
                            <div class="font-black text-sm text-indigo-300">
                                {{ $rep['learning_time_today'] ?? '22 mins' }} / {{ $screenLimit > 0 ? $screenLimit.'m' : '∞' }}
                            </div>
                        </div>

                        {{-- Focus Challenge Status --}}
                        <div class="col-span-2 sm:col-span-1 bg-slate-800/80 rounded-2xl p-3 border border-slate-700/60 text-center flex flex-col justify-center">
                            <div class="text-xs text-slate-400 font-bold mb-0.5">Tomorrow's Focus</div>
                            <div class="font-black text-xs text-amber-300 truncate">
                                {{ $assigned ? '📌 '.$assigned->title : 'None set yet' }}
                            </div>
                        </div>

                    </div>

                    {{-- 1-Line Competency Summary & WhatsApp Share --}}
                    <div class="bg-indigo-950/60 border border-indigo-500/30 rounded-2xl p-3.5 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🌟</span>
                                <span class="text-xs font-bold text-slate-100">
                                    Latest Mastery: <strong class="text-emerald-400">{{ $canDo[0] ?? 'Count objects from 1 to 10' }}</strong>
                                </span>
                            </div>
                            <button @click="tab = 'progress'" class="text-xs text-indigo-300 hover:text-white font-extrabold flex items-center gap-1 transition-all">
                                View Progress →
                            </button>
                        </div>

                        {{-- 📲 WhatsApp 1-Tap Share Button --}}
                        @php
                            $shareUrl = request()->schemeAndHttpHost() . '/parent/dashboard';
                            $shareMsg = "🌟 *Proud Parent Moment!* 🌟\nMy child *{$child->name}* is learning on *KiddoQuest CBC*! 🎓\n\n✅ *Mastery:* " . ($canDo[0] ?? 'Counting & Phonics') . "\n🔥 *Streak:* " . ($rep['streak_days'] ?? 7) . " Days\n⭐ *Stars Earned:* " . number_format($child->total_stars) . "\n📊 *Accuracy:* " . ($rep['accuracy_rate'] ?? 84) . "%\n\nTry KiddoQuest CBC for your kids here: " . $shareUrl;
                            $waUrl = "https://wa.me/?text=" . urlencode($shareMsg);
                        @endphp
                        <a href="{{ $waUrl }}" target="_blank" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-lg transition-all border border-emerald-400/40">
                            <span class="text-base">📲</span> Share {{ $child->name }}'s Report Card on WhatsApp
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 2: 📚 LEARNING PROGRESS (THE FULL REPORT CARD) --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'progress'" class="space-y-6" style="display: none;">
            
            {{-- CBC Subject Filter Pills --}}
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none mb-4">
                <a href="?subject=all&timeframe={{ $timeframe }}"
                   class="px-3.5 py-1.5 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 {{ ($selectedSubject ?? 'all') === 'all' ? 'subj-pill-active' : '' }}">
                    <span>🌟</span> All Subjects
                </a>

                <a href="?subject=math&timeframe={{ $timeframe }}"
                   class="px-3.5 py-1.5 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 {{ ($selectedSubject ?? '') === 'math' ? 'subj-pill-active' : '' }}">
                    <span>🔢</span> Mathematics
                </a>

                <a href="?subject=english&timeframe={{ $timeframe }}"
                   class="px-3.5 py-1.5 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 {{ ($selectedSubject ?? '') === 'english' ? 'subj-pill-active' : '' }}">
                    <span>📖</span> English & Phonics
                </a>

                <a href="?subject=cre&timeframe={{ $timeframe }}"
                   class="px-3.5 py-1.5 rounded-xl border border-slate-700 bg-slate-800/80 text-xs font-bold text-slate-300 hover:text-white transition-all flex items-center gap-1.5 {{ ($selectedSubject ?? '') === 'cre' ? 'subj-pill-active' : '' }}">
                    <span>✝️</span> CRE & Values
                </a>
            </div>

            @foreach($children as $child)
                @php
                    $rep = $reports[$child->id] ?? [];
                    $canDo = $rep['can_do_now'] ?? [];
                    $learningNext = $rep['learning_next'] ?? [];
                    $heatMap = $rep['skills_heat_map'] ?? [];
                    $rm = $rep['roadmap'] ?? [];
                    $growth = $rep['growth'] ?? [];
                    $history = $rep['mission_history'] ?? [];
                @endphp

                <div class="parent-card p-5">
                    
                    {{-- Header + Growth Pill --}}
                    <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">{{ $child->avatar_emoji }}</div>
                            <div>
                                <h3 class="font-black text-base text-white">{{ $child->name }}'s Report Card</h3>
                                <p class="text-xs text-indigo-300">{{ ucfirst($selectedSubject ?? 'all') }} Analytics</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-black px-2.5 py-1 rounded-xl">
                                📈 +{{ $growth['growth_percent'] ?? 26 }}% Growth
                            </div>
                        </div>
                    </div>

                    {{-- "What My Child Can Do Now" --}}
                    <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 mb-5">
                        <h4 class="font-black text-xs text-emerald-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                            <span>🌟</span> Competencies Mastered:
                        </h4>
                        <div class="space-y-1.5 mb-3">
                            @foreach($canDo as $cd)
                                <div class="flex items-start gap-2 text-xs text-slate-100 font-bold">
                                    <span class="text-emerald-400 flex-shrink-0">✅</span>
                                    <span>{{ $cd }}</span>
                                </div>
                            @endforeach
                        </div>

                        <h4 class="font-black text-xs text-indigo-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                            <span>🔜</span> Learning Next:
                        </h4>
                        <div class="space-y-1">
                            @foreach($learningNext as $ln)
                                <div class="flex items-start gap-2 text-xs text-indigo-200 font-semibold">
                                    <span class="text-amber-400 flex-shrink-0">🔜</span>
                                    <span>{{ $ln }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Skills Heat Map --}}
                    <div class="mb-5">
                        <h4 class="font-black text-xs text-indigo-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span>📊</span> Skills Heat Map
                        </h4>
                        <div class="space-y-2.5 bg-slate-900/60 p-3.5 rounded-2xl border border-slate-800">
                            @foreach($heatMap as $hm)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs">
                                    <span class="font-bold text-slate-200 w-44 truncate">{{ $hm['name'] }}</span>
                                    <div class="flex items-center gap-1.5 flex-1 max-w-[200px]">
                                        <div class="flex gap-1 flex-1">
                                            @for($b = 1; $b <= ($hm['total'] ?? 8); $b++)
                                                <div class="h-2.5 flex-1 rounded-sm {{ $b <= ($hm['bar'] ?? 5) ? 'skill-block-filled' : 'skill-block-empty' }}"></div>
                                            @endfor
                                        </div>
                                        <span class="font-black text-indigo-300 text-[11px] w-8 text-right">{{ $hm['score'] }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- NEW KILLER FEATURE: 📈 MISSION HISTORY TABLE & ATTEMPT DRILLDOWN --}}
                    <div class="mb-5">
                        <h4 class="font-black text-xs text-indigo-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span>📈</span> Mission History & Attempt Drilldown
                        </h4>
                        
                        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900/80">
                            <table class="w-full text-left text-xs text-slate-200">
                                <thead class="bg-slate-800/90 text-indigo-300 font-black text-[11px] uppercase tracking-wider border-b border-slate-700">
                                    <tr>
                                        <th class="p-3">Mission</th>
                                        <th class="p-3 text-center">Attempts</th>
                                        <th class="p-3 text-center">Best Stars</th>
                                        <th class="p-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 font-semibold">
                                    @foreach($history as $idx => $mh)
                                        <tr class="hover:bg-slate-800/50 transition-all">
                                            <td class="p-3 font-bold text-white">{{ $mh['mission_title'] }}</td>
                                            <td class="p-3 text-center tabular-nums">{{ $mh['attempts_count'] }}</td>
                                            <td class="p-3 text-center text-amber-400">
                                                @for($s = 1; $s <= $mh['best_stars']; $s++) ⭐ @endfor
                                            </td>
                                            <td class="p-3 text-right">
                                                <button @click="activeHistoryModal = {{ $idx }}" class="bg-indigo-600/40 hover:bg-indigo-600 text-indigo-200 hover:text-white border border-indigo-500/40 font-bold px-2.5 py-1 rounded-xl text-[11px] transition-all">
                                                    Inspect →
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Drilldown Attempt Modal --}}
                    <template x-if="activeHistoryModal !== null">
                        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="activeHistoryModal = null">
                            <div class="bg-slate-900 border-2 border-indigo-500/40 rounded-3xl p-6 max-w-md w-full text-white shadow-2xl relative">
                                
                                <button @click="activeHistoryModal = null" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl font-bold">✕</button>

                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-2xl">📈</span>
                                    <h3 class="font-black text-lg" x-text="history[activeHistoryModal].mission_title"></h3>
                                </div>

                                {{-- Attempt Log --}}
                                <div class="mb-4">
                                    <h4 class="text-xs font-black text-indigo-300 uppercase tracking-wider mb-2">Attempt Log:</h4>
                                    <div class="space-y-1.5">
                                        <template x-for="att in history[activeHistoryModal].attempts" :key="att.attempt">
                                            <div class="bg-slate-800 rounded-xl p-2.5 flex justify-between items-center text-xs font-bold">
                                                <span>Attempt <span x-text="att.attempt"></span> (<span x-text="att.date"></span>)</span>
                                                <span class="text-emerald-400 font-black" x-text="att.score"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Mistakes Log --}}
                                <div>
                                    <h4 class="text-xs font-black text-amber-400 uppercase tracking-wider mb-2">Struggle / Mistakes Recorded:</h4>
                                    <div class="space-y-1.5">
                                        <template x-for="mst in history[activeHistoryModal].mistakes" :key="mst">
                                            <div class="bg-amber-950/40 border border-amber-500/30 text-amber-200 rounded-xl p-2.5 text-xs font-semibold" x-text="mst"></div>
                                        </template>
                                        <template x-if="history[activeHistoryModal].mistakes.length === 0">
                                            <div class="text-xs text-emerald-400 font-bold">✨ Perfect execution! No mistakes recorded.</div>
                                        </template>
                                    </div>
                                </div>

                                <button @click="activeHistoryModal = null" class="w-full mt-5 bg-indigo-600 hover:bg-indigo-500 text-white font-black py-2.5 rounded-xl text-xs shadow-md transition-all">
                                    Close Drilldown
                                </button>
                            </div>
                        </div>
                    </template>

                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 3: 🎯 LEARNING SUPPORT ("How can I help?") --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'support'" class="space-y-6" style="display: none;">
            @foreach($children as $child)
                @php
                    $rep = $reports[$child->id] ?? [];
                    $mistakeAct = $rep['mistake_action'] ?? [];
                    $assigned = $rep['assigned_mission'] ?? null;
                @endphp

                <div class="parent-card p-5">
                    
                    <div class="flex items-center gap-3 mb-5 pb-3 border-b border-slate-700/50">
                        <div class="text-3xl">{{ $child->avatar_emoji }}</div>
                        <div>
                            <h3 class="font-black text-base text-white">Learning Support for {{ $child->name }}</h3>
                            <p class="text-xs text-indigo-300">Actionable Guidance & Focus Assignment</p>
                        </div>
                    </div>

                    {{-- 🎯 SPECIFIC MISTAKE HOME ACTIVITY --}}
                    @if(!empty($mistakeAct))
                        <div class="bg-amber-950/40 border border-amber-400/40 rounded-2xl p-4 mb-5 shadow-md">
                            <div class="flex gap-3 items-start">
                                <div class="text-2xl flex-shrink-0">🎯</div>
                                <div>
                                    <div class="text-[10px] font-black text-amber-400 uppercase tracking-wider mb-0.5">Struggle Area Identified</div>
                                    <p class="text-xs font-bold text-amber-200 mb-2">"{{ $mistakeAct['mistake'] }}"</p>
                                    
                                    <div class="bg-amber-900/30 rounded-xl p-3 border border-amber-500/30 text-xs text-amber-100 font-semibold leading-relaxed">
                                        <strong class="text-amber-300 block mb-1">💡 Recommended 1-Minute Home Activity:</strong> 
                                        {{ $mistakeAct['activity'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 📌 ASSIGN TOMORROW'S FOCUS MISSION --}}
                    <div class="bg-gradient-to-r from-indigo-900/60 to-purple-900/60 border border-indigo-400/40 rounded-2xl p-4 mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-black text-xs text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span>📌</span> Tomorrow's Focus Mission
                            </h4>
                            @if($assigned)
                                <span class="bg-amber-400/20 text-amber-300 text-[10px] font-black px-2 py-0.5 rounded-full border border-amber-400/40">
                                    Assigned ✓
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-300 mb-3 font-medium">
                            Select a mission to highlight on {{ $child->name }}'s Adventure Map tomorrow:
                        </p>

                        <form method="POST" action="{{ route('parent.assign_mission') }}" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="child_id" value="{{ $child->id }}">
                            <select name="mission_id" style="background-color: #0F172A !important; color: #FFFFFF !important; -webkit-appearance: menulist !important; appearance: menulist !important;" class="bg-slate-900 text-white text-xs font-bold rounded-xl px-3 py-2.5 border border-slate-600 flex-1 focus:outline-none focus:border-indigo-400">
                                <option value="" style="background-color: #0F172A !important; color: #FFFFFF !important;">-- Pick a Focus Mission --</option>
                                @foreach($allMissions as $m)
                                    <option value="{{ $m->id }}" style="background-color: #0F172A !important; color: #FFFFFF !important;" {{ ($child->assigned_mission_id ?? null) == $m->id ? 'selected' : '' }}>
                                        {{ $m->title }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-black px-4 py-2 rounded-xl text-xs shadow-md transition-all">
                                {{ $assigned ? 'Update' : '📌 Assign' }}
                            </button>
                        </form>
                    </div>

                    {{-- 🤖 HERO FEATURE: ASK AI PEDAGOGY COACH --}}
                    <div class="bg-gradient-to-br from-indigo-950 via-purple-950 to-slate-900 border border-indigo-500/40 rounded-2xl p-4 shadow-xl" 
                         x-data="{ aiQuestion: '', aiAnswer: '', loading: false, ask(q) { this.aiQuestion = q; this.submitAi(); }, submitAi() { if(!this.aiQuestion) return; this.loading = true; fetch('{{ route('parent.ask_ai') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ question: this.aiQuestion }) }).then(r=>r.json()).then(d=>{ this.aiAnswer = d.answer; this.loading = false; }); } }">
                        
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl animate-bounce">🤖</span>
                                <div>
                                    <h4 class="font-black text-xs text-indigo-300 uppercase tracking-wider">Ask AI Pedagogy Coach</h4>
                                    <p class="text-[11px] text-slate-300">Ask Leo's AI anything about early childhood learning!</p>
                                </div>
                            </div>
                            <span class="bg-amber-400/20 text-amber-300 border border-amber-400/40 text-[10px] font-black px-2.5 py-0.5 rounded-full">
                                Premium AI ✨
                            </span>
                        </div>

                        {{-- Quick Prompt Chips --}}
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <button @click="ask('Why is my child confusing 6 and 9?')" class="bg-slate-800 hover:bg-slate-700 text-indigo-200 border border-slate-700 text-[11px] font-bold px-2.5 py-1 rounded-xl transition-all text-left">
                                💬 "Why is my child confusing 6 and 9?"
                            </button>
                            <button @click="ask('How much screen time is healthy for a 4 year old?')" class="bg-slate-800 hover:bg-slate-700 text-indigo-200 border border-slate-700 text-[11px] font-bold px-2.5 py-1 rounded-xl transition-all text-left">
                                💬 "Recommended daily screen time?"
                            </button>
                        </div>

                        {{-- Custom Question Input --}}
                        <div class="flex gap-2 mb-3">
                            <input type="text" x-model="aiQuestion" @keydown.enter="submitAi()" placeholder="Ask AI: e.g. Why does my child struggle with phonics?"
                                   style="background-color: #0F172A !important; color: #FFFFFF !important;"
                                   class="bg-slate-900 border border-slate-700 text-xs font-bold text-white rounded-xl px-3 py-2.5 flex-1 focus:outline-none focus:border-indigo-500">
                            <button @click="submitAi()" :disabled="loading" class="bg-indigo-600 hover:bg-indigo-500 text-white font-black px-4 py-2 rounded-xl text-xs shadow-md transition-all">
                                <span x-show="!loading">Ask AI 🚀</span>
                                <span x-show="loading" style="display:none;">Thinking...</span>
                            </button>
                        </div>

                        {{-- AI Answer Output Box --}}
                        <div x-show="aiAnswer" x-cloak class="bg-slate-900/90 border border-indigo-500/40 rounded-xl p-3 text-xs text-slate-100 font-semibold leading-relaxed">
                            <div class="text-[10px] font-black text-emerald-400 uppercase mb-1">AI Teacher Response:</div>
                            <div x-html="aiAnswer" class="whitespace-pre-line"></div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ========================================================= --}}
        {{-- TAB 4: ⚙️ CONTROLS (SCREEN TIME, PIN & DEVICE) --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'controls'" class="space-y-6" style="display: none;">
            
            {{-- M-Pesa Subscription Card --}}
            <div class="parent-card p-5 border-2 border-emerald-500/40 bg-gradient-to-r from-emerald-950/60 to-slate-900">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="text-3xl">🟢</div>
                        <div>
                            <h3 class="font-black text-base text-white">M-Pesa Subscription & Billing</h3>
                            <p class="text-xs text-emerald-300">Manage plan & unlock all adventure worlds</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('parent.subscription') }}" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <span>💳</span> Manage M-Pesa Subscription
                </a>
            </div>
            
            {{-- Screen Time Limits --}}
            @foreach($children as $child)
                <div class="parent-card p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="text-3xl">{{ $child->avatar_emoji }}</div>
                        <div>
                            <h3 class="font-black text-base text-white">{{ $child->name }}</h3>
                            <p class="text-xs text-indigo-300">Daily Screen Time Limit</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('parent.update_screentime') }}">
                        @csrf
                        <input type="hidden" name="child_id" value="{{ $child->id }}">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                            @foreach([0 => 'Unlimited', 30 => '30 Mins', 45 => '45 Mins', 60 => '60 Mins'] as $mins => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="daily_time_limit_minutes" value="{{ $mins }}" 
                                           {{ ($child->daily_time_limit_minutes ?? 0) == $mins ? 'checked' : '' }} class="sr-only peer">
                                    <div class="p-3 rounded-xl border border-slate-700 text-center text-xs font-bold text-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-400 transition-all">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-2.5 rounded-xl text-xs shadow-md transition-all">
                            Save Time Limit
                        </button>
                    </form>
                </div>
            @endforeach

            {{-- PIN Security --}}
            <div class="parent-card p-5 max-w-md mx-auto">
                <div class="text-center mb-4">
                    <div class="text-4xl mb-1">🔐</div>
                    <h3 class="font-black text-base text-white">Change Parent PIN</h3>
                    <p class="text-xs text-indigo-300">Set a new 4-digit PIN for Parent Zone access</p>
                </div>

                <form method="POST" action="{{ route('parent.update_pin') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-300 mb-2">New 4-Digit PIN</label>
                        <input type="password" maxlength="4" pattern="[0-9]{4}" name="new_pin" required placeholder="e.g. 5678"
                               class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-center text-2xl font-black text-white focus:outline-none focus:border-indigo-500 tracking-widest">
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-xl text-sm shadow-md transition-all">
                        Update Parent PIN
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
