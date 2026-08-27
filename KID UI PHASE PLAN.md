# 🦁 Kid UI Phase Plan — The Learning Friends Adventure

> **Status:** Planning — Approved Decisions Locked
> **Phase:** Kid-Facing Experience (after Admin CMS completion)
> **Last Updated:** 2026-07-10

---

## 🎯 1. The Vision (Locked)

Children don't "take quizzes." They **go on adventures with Leo the Lion** and his friends. Every lesson is a place to explore. Every quiz is a way to help a friend. Every correct answer builds a bridge, finds a treasure, or unlocks a new path.

**Core Promise:** *A child should feel like they're playing an adventure game while quietly mastering the CBC curriculum.*

---

## 🦌 2. The Learning Friends World

### Main Guide
🦁 **Leo the Lion** — African identity, gender-neutral, welcoming. Appears on the home map, celebrates achievements, introduces new worlds.

### Subject Friends (appear within their subjects)

| Subject | Friend | Role | Personality |
|---------|--------|------|-------------|
| 🔤 English/Literacy | 🦜 **Pip the Parrot** | Teaches words & letters | Talkative, cheerful |
| ➕ Mathematics | 🐘 **Eli the Elephant** | Counts & calculates | Wise, patient, never rushes |
| 🌍 Environment | 🦒 **Gerry the Giraffe** | Sees far — animals, places, plants | Curious, gentle |
| 📖 CRE / Bible Stories | 🕊️ **Dove** | Peace & kindness themes | Calm, kind |
| 🎨 Art | 🐵 **Milo the Monkey** | Creative activities | Playful, messy, fun |
| 🎵 Music | 🐦 **Tweety the Bird** | Songs & rhythm | Musical, bouncy |

**Design rule:** Each friend is rounded, friendly, expressive, with large eyes and soft edges. Never sharp, never scary.

---

## 🗺️ 3. The Adventure Map (Navigation Concept)

**Not** a list of lessons. **Not** a grid of subjects. An **adventure map**.

```
🏡 Home Village (Leo welcomes you)
  ↓
🌳 Whispering Forest  (first lessons live here)
  ↓
🐘 Safari Plains      (counting, animals)
  ↓
🌊 Ocean Cove         (colors, water themes)
  ↓
🏰 Castle of Letters  (advanced alphabet)
  ↓
🚀 Star Base          (rewards, bonus content)
```

Each world contains:
- 📍 **Lesson stops** (a lesson page with mascot narration)
- 🎯 **Challenge gates** (a quiz — but framed as "help the lion")
- 🏆 **Treasure chests** (badge/reward unlocks)
- 🌟 **Stars** earned per activity

**The child taps a path node, not a menu item.** Locked nodes show a 🔒 until prerequisites are met.

---

## 🎨 4. Visual Design System (Locked)

### Color Palette

| Use | Color | Hex | Feeling |
|-----|-------|-----|---------|
| Primary — Trust | 🟦 Sky Blue | `#38BDF8` | Calm, sky, safe |
| Primary — Learning | 🟩 Green | `#22C55E` | Growth, go |
| Primary — Energy | 🟨 Yellow | `#FACC15` | Sunshine, happy |
| Primary — Fun | 🟧 Orange | `#FB923C` | Playful, warm |
| Accent — Rewards | 🟣 Purple | `#A78BFA` | Magic, badges |
| ⚠️ Red | Use ONLY for celebration pops or important alerts | — | Never for "wrong" feedback |

**Wrong-answer feedback** uses gentle colors (soft blue, encouraging) — never red. Children should never feel punished.

### Typography
- **Headings:** Fredoka (rounded, friendly)
- **Body:** Baloo 2 (highly readable for early learners)
- **Fallbacks:** system rounded fonts

### UI Principles
- ✅ Big rounded buttons (min 60px touch target)
- ✅ Soft shadows (never harsh)
- ✅ Large illustrations, minimal text
- ✅ Voice narration on EVERY screen (kids may not read yet)
- ✅ Landscape-first (matches our quiz data model)
- ✅ Themed backgrounds per world (not white screens)

### Layout
- **Tablet landscape** is the primary target (1024×768 and up)
- Must degrade gracefully to phone landscape
- Portrait mode: stack content vertically, keep big buttons

---

## 🎭 5. The Narrative Layer (The Big Innovation)

**Problem:** A child sees "Question 1 of 5" and thinks "test." They disengage.

**Solution:** Every quiz is wrapped in a story micro-scenario.

### How it works

| Traditional Quiz | Adventure Reframe |
|------------------|-------------------|
| "Question 1 of 5" | 🦁 "Leo needs to cross the river! Tap the right stone." |
| "Correct!" | 🦁 "You did it! The bridge is built! 🎉" |
| "Wrong, try again" | 🦁 "Hmm, that stone wobbled. Let's try another! 💪" |
| Score: 4/5 | 🌟 You earned 4 stars! Gerry the Giraffe is proud! |

### Narrative Templates (Phase 1 — fixed per quiz type)

We'll create 6 story templates. The admin doesn't write stories yet — the system auto-applies them based on quiz type:

| Quiz Type | Story Frame |
|-----------|-------------|
| QT-01 Multiple Choice | "Help Leo choose the right path!" |
| QT-02 True/False | "Pip the Parrot says something — is it true?" |
| QT-03 Matching | "Match the friends to their homes!" |
| QT-05 Sequence | "Put the stepping stones in order!" |
| QT-08 Spell/Fill | "Fill in the missing letters on the treasure map!" |
| QT-09 Count | "Count the apples for Eli the Elephant!" |

*Future: AI personalizes narratives per child. Phase 1 uses templates.*

---

## 🎮 6. Phase 1 Scope — The 6 Game Renderers

These are the quiz types we'll build as **playable games** in Phase 1:

| # | Type | Interaction | Game UI |
|---|------|-------------|---------|
| 1 | QT-01 Multiple Choice | Tap one answer | Big answer cards with images/text |
| 2 | QT-02 True/False | Tap Yes or No | Two giant buttons |
| 3 | QT-03 Matching | Tap pairs (connect) | Grid of cards, tap two to match |
| 4 | QT-05 Sequence | Drag to reorder | Movable tiles in a row |
| 5 | QT-08 Spell/Fill | Drag letter to blank | Letter tiles + slot |
| 6 | QT-09 Count Objects | Tap number | Image + number buttons |

**Each game includes:**
- 🗣️ Auto-played audio prompt (Web Speech API or audio file)
- ✅ Animated correct feedback (confetti + mascot cheer)
- 💪 Gentle wrong feedback (soft color, "try again" — never red)
- 🌟 Star awarded per correct answer
- ⏭️ Next button appears after answering

**Deferred to Phase 2+:** QT-04 Sort, QT-06 Listen, QT-10 Pattern, QT-11 Memory, QT-07 Speak, QT-12 Trace, QT-13 Spot & Find

---

## 🏗️ 7. Technical Architecture

### New Database Tables Needed

```
parents
  ├─ id, name, email, password, phone
  └─ created_at

children
  ├─ id, parent_id, name, avatar, birthdate
  └─ created_at

child_progress
  ├─ id, child_id, lesson_id, status, stars_earned, completed_at
  └─ tracks per-lesson completion

quiz_attempts
  ├─ id, child_id, quiz_id, score, total, stars, passed, completed_at
  └─ tracks quiz results

child_badges
  ├─ id, child_id, badge_id, awarded_at
  └─ earned achievements

adventure_worlds (optional Phase 1 — can hardcode initially)
  ├─ id, name, slug, theme_color, icon, sort_order
  └─ Forest, Safari, Ocean, Castle, Space
```

### New Routes (Kid-Facing)

```
GET  /kids                    → Profile picker (list of children under logged-in parent)
GET  /kids/enter/{child}      → Enter as this child (sets session)
GET  /kids/map                → Adventure map home
GET  /kids/world/{world}      → A themed world view
GET  /kids/lesson/{lesson}    → Lesson play screen
GET  /kids/quiz/{quiz}        → Quiz game engine
POST /kids/quiz/{quiz}/submit → Save attempt + stars
GET  /kids/celebration        → Reward/badge screen
```

### New Models
- `Parent`, `Child`, `ChildProgress`, `QuizAttempt`, `ChildBadge`, `AdventureWorld`

### View Structure
```
resources/views/kids/
  ├─ layouts/
  │   └─ adventure.blade.php      → Kid layout (no admin sidebar, big buttons, mascot)
  ├─ profiles.blade.php           → "Who's playing?" avatar picker
  ├─ map.blade.php                → Adventure map home
  ├─ world.blade.php              → A themed world
  ├─ lesson.blade.php             → Lesson view with mascot narration
  ├─ quiz/
  │   ├─ engine.blade.php         → Main quiz container
  │   └─ games/
  │       ├─ multiple-choice.blade.php
  │       ├─ true-false.blade.php
  │       ├─ matching.blade.php
  │       ├─ sequence.blade.php
  │       ├─ spell-fill.blade.php
  │       └─ count-objects.blade.php
  └─ celebration.blade.php        → Stars + badges screen
```

### Assets
- `public/css/kids/adventure.css` — Kid theme (separate from admin)
- `public/js/kids/quiz-engine.js` — Game logic (drag, tap, score)
- `public/images/mascots/` — Leo, Pip, Eli, Gerry, Dove, Milo, Tweety

---

## 📋 8. Build Sequence — Milestone-Gated (Locked)

> **🛑 CRITICAL RULE: Build one milestone at a time. After each milestone, STOP. Wait for review and approval before continuing.**

No jumping ahead. Build → Review → Test → Approve → Next.

### Milestone 0 — Project Foundation
**Goal:** Verify the architecture before any features.

**Deliverables:**
- Folder structure for kid-facing app
- Routes (kid routes group, parent routes group)
- Models (Parent, Child, ChildProgress, QuizAttempt, ChildBadge, AdventureWorld)
- Controllers (KidController, ParentController, AuthController)
- Authentication strategy (parent guard vs admin guard)
- Database migrations (all new tables)
- Theme structure (kid layout shell)
- No business logic yet — just the skeleton

**🛑 STOP FOR REVIEW**

### Milestone 1 — Parent & Child System
**Goal:** Parents can register, create child profiles, and kids can pick their avatar.

**Deliverables:**
- Parent registration + login
- Child profile creation (name, avatar, birthdate)
- Profile picker ("Who's Playing?")
- Parent dashboard shell
- Child session switching (tap avatar → enter kid mode)

**🛑 STOP FOR REVIEW — Test everything**

### Milestone 2 — Adventure Framework
**Goal:** The adventure map and Leo the Lion come alive.

**Deliverables:**
- Kid layout (landscape-first, Fredoka/Baloo fonts)
- Adventure map home screen
- Leo the Lion mascot (emoji/SVG placeholder)
- World navigation (Forest, Safari, Ocean, Castle, Space)
- Progress indicators on map
- Locked/unlocked world states

**🛑 STOP FOR REVIEW** — Check animations, spacing, responsiveness, usability

### Milestone 3 — Lesson Player
**Goal:** Kids can play through a lesson with narration.

**Deliverables:**
- Lesson screen with themed background
- Mascot friend appears (per subject)
- Audio narration (Web Speech API)
- Images/content display
- Progress saving (lesson started/completed)
- Next lesson flow

**🛑 STOP FOR REVIEW**

### Milestone 4 — Quiz Engine (The Big One)
**Goal:** Build the engine, then each game one at a time.

**Deliverables (in order):**
1. Quiz container with narrative wrapper ("Help Leo!")
2. **Game 1: QT-01 Multiple Choice** → 🛑 Review
3. **Game 2: QT-02 True/False** → 🛑 Review
4. **Game 3: QT-09 Count Objects** → 🛑 Review
5. **Game 4: QT-03 Matching** → 🛑 Review
6. **Game 5: QT-05 Sequence** → 🛑 Review
7. **Game 6: QT-08 Spell/Fill** → 🛑 Review
8. Scoring + stars + attempt saving

**🛑 STOP FOR REVIEW after EACH game**

### Milestone 5 — Rewards
**Goal:** Stars, badges, celebrations make learning feel magical.

**Deliverables:**
- Star system (earn per correct answer)
- Badges (first: "Forest Explorer")
- Treasure chests on map
- Celebration screen (confetti + mascot cheer)
- Celebration animations

**🛑 STOP FOR REVIEW**

### Milestone 6 — Parent Dashboard
**Goal:** Parents see real curriculum progress, not game stats.

**Deliverables:**
- Progress by subject (CBC strands)
- Learning history timeline
- Weak area identification
- Time spent reports
- Recommendations feed (pre-AI, rule-based)

**🛑 STOP FOR REVIEW**

### Milestone 7 — AI Engine
**Goal:** Smart personalization (only after everything else is stable).

**Deliverables:**
- Adaptive recommendations
- Weakness analysis
- Pattern detection
- Personalized adventure suggestions
- Learning velocity tracking

**🛑 STOP FOR REVIEW**

---

## ❓ 9. Open Questions (Decide Before Coding)

These don't block planning, but we should align before Step 1:

1. **Parent auth: same `admins` table or new `parents` table?**
   *My lean: New `parents` table — parents aren't admins. Separate auth guard.*

2. **Adventure worlds: hardcoded or database-driven in Phase 1?**
   *My lean: Hardcode 5 worlds in Phase 1 (Forest, Safari, Ocean, Castle, Space). Move to DB in Phase 2 when admin can edit them.*

3. **Audio: pre-recorded files or text-to-speech?**
   *My lean: Web Speech API (browser TTS) for Phase 1 — zero production cost, works offline-ish. Allow audio file override per lesson later.*

4. **Mascot art: do you have illustrations, or should I use emoji/SVG placeholders?**
   *My lean: Emoji + CSS-styled placeholders for Phase 1. Commission real art before launch.*

5. **Offline support: Phase 1 or later?**
   *My lean: Later. Phase 1 requires internet.*

---

## ✅ 10. Decisions Locked (From Our Brainstorm)

| Decision | Choice |
|----------|--------|
| Mascot model | 🦁 Leo the Lion (main) + 6 subject friends |
| Phase 1 quiz types | 6: QT-01, QT-02, QT-03, QT-05, QT-08, QT-09 |
| Visual style | Bright, rounded, chunky, themed worlds |
| Color palette | Sky Blue, Green, Yellow, Orange + Purple accent |
| Fonts | Fredoka (headings) + Baloo 2 (body) |
| Navigation | Adventure map, NOT lesson lists |
| Quiz framing | "Help the lion" narrative, NOT "Question 1 of 5" |
| Wrong feedback | Gentle, never red |
| Orientation | Landscape-first, tablet-optimized |
| Login model | Parent account → child profiles (tap avatar, no password) |

---

## 📚 12. Curriculum & Learning Architecture (Required)

> **Purpose:** Ensure the adventure never hides the educational purpose. The child experiences an adventure, while parents and teachers always understand the curriculum being taught.

---

### 12.1 The Core Philosophy

The application has **two different experiences** using the same learning data.

#### 👧 Child Experience
Children should **never feel like they are taking an exam**.

Instead, they experience:
- 🌍 Adventure Worlds
- 🦁 Learning Friends
- ⭐ Stars
- 🏆 Treasure Chests
- 🎮 Mini Games
- 🎉 Celebrations

The child thinks: *"I'm helping Leo!"* — not — *"I'm doing Mathematics."*

#### 👨‍👩‍👧 Parent Experience
Parents should always understand exactly what their child is learning.

Parents must see:
- Subject
- CBC Strand
- CBC Sub-Strand
- Learning Outcome
- Skills Mastered
- Weak Areas
- Progress Percentage
- AI Recommendations

The parent thinks: *"My child is improving in Mathematics but needs more practice in English."*

---

### 12.2 The Learning Hierarchy

The system always follows this hierarchy:

```
Curriculum
    ↓
Subject
    ↓
Strand
    ↓
Sub-Strand
    ↓
Learning Outcome
    ↓
Lesson
    ↓
Quiz
    ↓
Question
```

The adventure layer sits **on top** of this educational structure — it never replaces it.

---

### 12.3 Adventure Layer (Presentation)

Every educational lesson belongs to an adventure world.

```
Adventure World: 🌳 Whispering Forest
    ↓
Lesson: Help Eli Count Apples
    ↓
Subject: Mathematics
    ↓
Strand: Numbers
    ↓
Learning Outcome: Count objects from 1–5
    ↓
Quiz: QT-09 Count Objects
```

- The **child sees:** 🌳 *Help Eli Count the Apples!*
- The **parent sees:** Mathematics → Numbers → Counting 1–5 → ✅ Completed

---

### 12.4 Parent Dashboard

The dashboard organizes learning by **subject**, not by adventure world.

```
📊 Child Progress

➕ Mathematics       ████████░░ 80%
   Weak Area: Comparing Numbers
   Recommendation: Practice Counting 6–10

🔤 English          ██████░░░░ 60%
   Weak Area: Beginning Sounds
   Recommendation: Play Letter A activities

🌍 Environment      ██████████ 100%
   Excellent!
```

---

### 12.5 Adventure Worlds Are Themes, Not Subjects

Every world may contain lessons from **multiple subjects**:

```
🌳 Whispering Forest
 ├─ Count Forest Animals (Math)
 ├─ Letter A Adventure (English)
 ├─ God Created Trees (CRE)
 └─ Forest Plants (Environment)
```

This keeps learning varied and exciting.

---

### 12.6 AI Learning Engine

The AI analyzes learning by **educational outcomes** — not by adventure worlds.

The AI tracks:
- Skills mastered
- Skills needing reinforcement
- Time per activity
- Accuracy
- Confidence
- Learning patterns

The AI may recommend:
```
Today's Adventure
Leo wants your help!
Let's visit the Forest today.
Reason: You need a little more practice counting to 5.
```

- The **child** receives an adventure.
- The **parent** receives an educational explanation.

---

### 12.7 Reporting

Reports always use **curriculum language**.

Never report:
```
Forest — 75%
```

Instead report:
```
Mathematics → Counting — 75%
Completed in: Forest Adventure
```

The adventure becomes additional context — not the primary educational report.

---

### 12.8 Guiding Principle

> **Children navigate the application through Adventure Worlds.**
> **Parents understand progress through Subjects and CBC Outcomes.**
> **The AI connects the two by recommending the next adventure based on educational mastery.**

---

### 12.9 ⚠️ Non-Negotiable Architecture Rule

> **The Adventure Layer must never contain curriculum data. It only references it.**

Curriculum (Subjects, Strands, Lessons, Quizzes, Questions) remains the **source of truth**. Adventure Worlds, Learning Friends, Stories, Rewards, and Narratives are **presentation layers** that sit on top of the curriculum.

This separation ensures that:
- ✅ One lesson can be reused in different adventures
- ✅ New adventure themes can be introduced without changing educational content
- ✅ The same curriculum can support different countries or curricula in the future
- ✅ AI recommendations always work from educational mastery rather than game progression

**Implementation:** Adventure worlds link to lessons via a `lesson_id` foreign key. The lesson itself never knows which adventure it belongs to. This is a one-way reference: `adventure_world_lesson.lesson_id → lessons.id`.

---

## 🚀 13. What Happens Next

1. **You review this plan** ✅
2. **We answer the 5 open questions** above
3. **I scaffold Step 1** (parents/children models + profile picker + kid layout)
4. **You test the foundation**, then we build the quiz games

No code until you say "go." This plan is our shared blueprint. 🗺️🦁