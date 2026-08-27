# 🎯 Question Type Master Roadmap (Phase Beta)

> **Last Updated:** 2026-07-13
> **Goal:** Build every question type to production quality, one at a time, before moving to AI or parent analytics.

---

## 📊 Master Status Table

| Code | Type | UI | Seeded | Tested | Mobile | Status |
|------|------|----|----|----|------|--------|
| QT-01 | Multiple Choice (Tap Answer) | ✅ | ✅ (3) | ✅ | ⬜ | 🟢 **Production-ready** |
| QT-02 | True / False (Yes/No) | ✅ | ✅ (2) | ✅ | ⬜ | 🟢 **Production-ready** |
| QT-03 | Matching | ✅ | ✅ (2) | ✅ | ⬜ | 🟢 **Production-ready** |
| QT-08 | Spell / Fill the Blank | ✅ | ✅ (1) | ✅ | ⬜ | 🟢 **Production-ready** |
| — | Flashcard (bonus) | ✅ | — | ✅ | ⬜ | 🟢 **Production-ready** |
| QT-05 | Drag & Drop — Sequence | ⬜ (generic MC fallback) | ✅ (1) | ⬜ | ⬜ | 🟡 **Seeded, needs dedicated UI** |
| QT-06 | Listen & Choose | ⬜ (generic MC fallback) | ✅ (1) | ⬜ | ⬜ | 🟡 **Seeded, needs dedicated UI** |
| QT-07 | Speak & Repeat | ⬜ (generic MC fallback) | ✅ (1) | ⬜ | ⬜ | 🟡 **Seeded, needs dedicated UI** |
| QT-09 | Count the Objects | ⬜ (generic MC fallback) | ✅ (1) | ⬜ | ⬜ | 🟡 **Seeded, needs dedicated UI** |
| QT-10 | Complete the Pattern | ✅ | ✅ (3) | ⬜ | ⬜ | 🟡 **Built — awaiting test** |
| QT-04 | Drag & Drop — Sort | ❌ | ❌ | ❌ | ❌ | 🔴 **Not started** |
| QT-11 | Memory Match | ❌ | ❌ | ❌ | ❌ | 🔴 **Not started** |
| QT-12 | Tracing | ✅ | ✅ (6) | ⬜ | ⬜ | 🟡 **Built — awaiting test** |
| QT-13 | Spot / Find | ❌ | ❌ | ❌ | ❌ | 🔴 **Not started** |

### Legend
- ✅ = Done and verified
- ⬜ = Not yet done
- ❌ = Not present at all
- 🟢 Production-ready · 🟡 Needs work · 🔴 Not started

---

## ✅ Definition of Done — Every Question Type Must Pass This Checklist

A question type is **LOCKED** (considered complete) only when ALL of these are true:

- [ ] **R1 — Dedicated UI:** Has its own render path in `engine.blade.php` (not falling back to generic multiple-choice).
- [ ] **R2 — Classroom-quality seed data:** At least 1 realistic, pedagogically meaningful question with rich options (not "Apple/Banana/Cat/Dog" dev stubs).
- [ ] **R3 — Portrait + Landscape layouts:** Looks correct on a phone in both orientations, and on tablet/desktop.
- [ ] **R4 — Touch targets ≥ 48px:** All interactive elements are large enough for a 3–5 year old's finger.
- [ ] **R5 — Audio narration support:** Leo the mascot reads the prompt; the type gracefully handles audio files if present.
- [ ] **R6 — Gentle wrong-answer feedback:** Never says "Wrong!" — always encouraging ("Almost!", "Try again! 💪"), reveals correct answer after 3 failed attempts.
- [ ] **R7 — Celebration on correct answer:** Confetti, sound, Leo bouncing.
- [ ] **R8 — Scores & saves correctly:** Submits `score`, `total`, `stars`, `time_spent`, and `answers` to `KidQuizController@submit`. Progress updates in DB.
- [ ] **R9 — Idle hint works:** If child does nothing for 15+ seconds, Leo prompts them.
- [ ] **R10 — Reduced-motion safe:** Respects `prefers-reduced-motion`.
- [ ] **R11 — Manual approval:** You (the stakeholder) tested it on a phone and said "approved."
- [ ] **R12 — Works in a mixed quiz:** When combined with other question types in one quiz, transitions cleanly.

---

## 🏗️ Build Workflow — For Every Remaining Type

```
1. PLAN     — Decide the UX (sketch the layout, interaction, scoring)
2. BUILD UI — Add dedicated render path in engine.blade.php
3. SEED     — Create classroom-quality example data
4. PLAY     — Test locally in the browser
5. FIX UX   — Refine animations, sizes, feedback
6. MOBILE   — Test on phone/tablet (portrait + landscape)
7. APPROVE  — Stakeholder says "approved"
8. LOCK     — Mark as 🟢 in this roadmap
9. NEXT     — Move to the next type
```

**Rule:** Never start the next type until the current one reaches APPROVE.

---

## 🔢 Priority Order — What to Build Next

Ordered by **educational value × ease of implementation**.

### Wave 1 — Polish Existing (Quick Wins)

These already have seed data and fallback UI. They just need a dedicated UI to shine.

| Priority | Type | Why First? | Effort |
|----------|------|-----------|--------|
| **P1** | QT-09 Count the Objects | Core math skill, very common in PP1/PP2, simple tap UI | 🟢 Low |
| **P2** | QT-10 Complete the Pattern | Important pre-math skill, simple tap UI | 🟢 Low |
| **P3** | QT-06 Listen & Choose | Critical for phonics, needs audio play button + tap | 🟡 Medium |
| **P4** | QT-05 Drag & Drop Sequence | Needs HTML5 drag (or tap-to-place), medium complexity | 🟡 Medium |
| **P5** | QT-07 Speak & Repeat | Placeholder mic UI (real speech recognition = Phase Gamma AI) | 🟢 Low |

### Wave 2 — New Types (More Work)

| Priority | Type | Why? | Effort |
|----------|------|------|--------|
| **P6** | QT-11 Memory Match | Kids love it, good for letter/picture matching, tap-based | 🟡 Medium |
| **P7** | QT-04 Drag & Drop Sort | Categorization skill, needs drag mechanics | 🟠 High |
| **P8** | QT-13 Spot / Find | Visual discrimination, needs image with tap zones | 🟠 High |
| ~~P9~~ | ~~QT-12 Tracing~~ | ✅ **BUILT** — Canvas-based finger tracing with coverage detection | ✅ Done |

### Wave 3 — Review Existing (Polish Pass)

After all types are built, revisit QT-01/02/03/08 for:
- Mobile testing
- Animation refinement
- Audio narration hookup
- Landscape layout check
- Wrong-answer experience review

---

## 📱 Mobile Testing Preparation (Task 5)

To enable phone testing over Wi-Fi:

### Step 1: Find Your PC's Local IP
```cmd
ipconfig
```
Look for "IPv4 Address" (e.g., `192.168.1.100`).

### Step 2: Configure Laravel
In `.env`:
```
APP_URL=http://192.168.1.100:8081
```

### Step 3: Allow Port in Windows Firewall
```cmd
netsh advfirewall firewall add rule name="KidLearn 8081" dir=in action=allow protocol=TCP localport=8081
```

### Step 4: Serve on All Interfaces
```cmd
php artisan serve --host=0.0.0.0 --port=8081
```

### Step 5: Test on Phone
On your phone's browser (connected to same Wi-Fi):
```
http://192.168.1.100:8081
```

### Mobile Checklist (apply to EVERY type)
- [ ] Portrait mode: all elements visible, no horizontal scroll
- [ ] Landscape mode: layout adapts, no cut-off content
- [ ] Touch targets ≥ 48px
- [ ] No hover-dependent interactions (touch only)
- [ ] Audio plays on tap (not blocked by autoplay policy)
- [ ] Animations smooth on mid-range phone
- [ ] Text legible at arm's length

---

## 🌱 Classroom-Quality Seed Data Standard (Task 4)

Every seeded question should feel like a **real lesson a teacher would use**.

### ❌ Bad (developer stub):
```
Question: "Which is red?"
Options: Apple, Banana, Cat, Dog
```

### ✅ Good (classroom quality):
```
Question: "Look at these fruits! 🍎🍌🍇 Which one is RED?"
Image: 🍎 (large, colorful)
Options: 🍎 Apple (correct), 🍌 Banana, 🍇 Grapes, 🥑 Avocado
Hint: "Red is the color of a fire truck and a strawberry!"
Explanation: "An apple is red! 🔴"
```

### Seed Data Rules:
1. **Use emojis as visuals** — they render everywhere, no image files needed
2. **Add hints** — always one short, encouraging sentence
3. **Add explanations** — teaches WHY the answer is correct
4. **Make options plausible** — not obviously wrong distractors
5. **Connect to the lesson** — the question should reinforce what was taught
6. **Vary difficulty** — within a quiz, mix easy + challenging

---

## 🚫 Explicitly Deferred (Do NOT Build Yet)

These wait until ALL question types are locked:

- ❌ AI integration (speech recognition, adaptive difficulty)
- ❌ Adaptive reinforcement engine
- ❌ Parent analytics dashboard
- ❌ AI recommendations & reports
- ❌ Achievement intelligence / smart badges
- ❌ Lesson video system

**Reason:** The quiz engine is the foundation. Everything else depends on reliable learning data. Rushing ahead creates rework.

---

## 📋 Approval Cycle (Lock-It Protocol)

```
Developer builds type
        ↓
Developer tests locally (browser)
        ↓
Stakeholder tests on phone
        ↓
Stakeholder lists issues
        ↓
Developer fixes issues
        ↓
Stakeholder approves ← "STEP X ✅ Approved"
        ↓
🔒 LOCKED — add to "Production-ready" list
        ↓
Next question type
```

Once locked, a type should NOT be modified unless a critical bug is found.

---

## 📐 Architecture Notes for Implementation

### Engine Structure (`resources/views/kids/quiz/engine.blade.php`)

The engine uses Alpine.js with this pattern for each type:

```php
<template x-if="currentQuestion.type === 'type_slug_here'">
    <!-- Dedicated UI for this type -->
</template>
```

**Slug normalization:** DB uses hyphens (`multiple-choice`), JS uses underscores (`multiple_choice`). The engine converts:
```php
$typeSlug = str_replace('-', '_', $rawSlug);
```

So in Alpine templates, use underscores: `multiple_choice`, `true_false`, `drag_sequence`, etc.

### Supported types in the engine today:
- `multiple_choice`, `tap_answer`, `true_false`, `fill_blank` → shared "answer-grid" UI
- `matching` → dedicated two-column matching board
- `flashcard` → dedicated flashcard UI
- `tracing` → dedicated canvas finger-tracing UI (QT-12)
- Everything else → **falls back to generic answer-grid** (needs dedicated UI)

### Key Alpine.js methods that all types use:
- `selectOption(index)` — for tap-based types
- `handleCorrect(index)` / `handleWrong(index)` — scoring logic
- `nextQuestion()` — navigation
- `spawnConfetti(count)` — celebration
- `resetIdleTimer()` — idle hint system
- `submitQuiz()` — saves to DB via form POST

### Files involved:
| File | Role |
|------|------|
| `resources/views/kids/quiz/engine.blade.php` | Main quiz player (Alpine.js) |
| `app/Http/Controllers/Kid/KidQuizController.php` | Loads quiz, handles submit |
| `database/seeders/ContentSeeder.php` | Seeds quiz data |
| `database/seeders/QuizTypeSeeder.php` | Defines all 13 question types |
| `public/js/kid/quiz-event-bus.js` | Event system |
| `public/js/kid/quiz-sound-layer.js` | Audio feedback |
| `public/js/kid/quiz-reward-layer.js` | Star/reward tracking |

---

## 🎯 Immediate Next Steps (Recommended Order)

1. **Enable mobile testing** (configure `.env` + firewall + `--host=0.0.0.0`)
2. **Build QT-09 Count the Objects** (P1 — easiest, highest value)
   - Add dedicated UI with large emoji groups + number tap options
   - Improve seed data (🐄🐄🐄 How many cows?)
3. **Build QT-10 Complete the Pattern** (P2)
   - Add dedicated UI showing pattern visually + tap to complete
4. **Build QT-06 Listen & Choose** (P3)
   - Add audio play button + tap-to-answer
5. Continue down the priority list...

---

*This document is the single source of truth for question type status. Update it after every type is built, tested, or locked.*