# 🎨 Kid UI Design System — The Visual Constitution

> *"Before we build the playable experience, we define the rules. Every component, every color, every animation, every sound. This document is the single source of truth for the kid-facing interface."*

**Version:** 1.0  
**Status:** PRE-MILESTONE-2 PLANNING  
**Approved by:** Product Owner

---

## Table of Contents

1. [Design Philosophy](#1-design-philosophy)
2. [Navigation Flow — The Adventure Loop](#2-navigation-flow--the-adventure-loop)
3. [Master Component Library](#3-master-component-library)
4. [Color System & World Theming](#4-color-system--world-theming)
5. [Typography](#5-typography)
6. [Button States & Interactions](#6-button-states--interactions)
7. [Animation Rules](#7-animation-rules)
8. [Sound & Audio Design](#8-sound--audio-design)
9. [Mascot System](#9-mascot-system)
10. [Card Layouts](#10-card-layouts)
11. [Responsive & Landscape Rules](#11-responsive--landscape-rules)
12. [Reward Economy Architecture](#12-reward-economy-architecture)
13. [Celebration & Feedback Patterns](#13-celebration--feedback-patterns)
14. [Loading & Transition States](#14-loading--transition-states)
15. [Accessibility & Safe Touch Areas](#15-accessibility--safe-touch-areas)
16. [Terminology Dictionary](#16-terminology-dictionary)

---

## 1. Design Philosophy

### Core Principles

| Principle | What It Means |
|-----------|---------------|
| **No Pages, Only Adventure** | Children never feel they "navigated." Every screen is a scene in one continuous story. Transitions are animated, not abrupt. |
| **One Continuous World** | The app is a place, not a website. Backgrounds persist. Mascots travel with the child. |
| **Touch-First** | Every interactive element is at least 60×60px. No hover-dependent states. |
| **Forgiving by Default** | Wrong answers never punish. They encourage retry. No red X marks. |
| **Always Reward Effort** | Even partial completion gives feedback. Stars, sounds, mascot reactions. |
| **Landscape-First** | Designed primarily for tablets held in landscape. Portrait is a degraded but functional mode. |

### The Feeling We Want

```
A child opens the app.

They don't "log in."
They "enter a world."

They don't "take a quiz."
They "play a game."

They don't "complete a lesson."
They "finish a mission."

They don't "see results."
They "celebrate with friends."
```

---

## 2. Navigation Flow — The Adventure Loop

### The Full Journey

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  GUARDIAN LOGIN                                     │
│  (Parent enters credentials)                        │
│         │                                           │
│         ▼                                           │
│  CHOOSE CHILD                                       │
│  (Tap your avatar card)                             │
│         │                                           │
│         ▼                                           │
│  MASCOT WELCOME                                     │
│  (Leo: "Hello Emma! I've been waiting for you!")    │
│         │                                           │
│         ▼                                           │
│  ADVENTURE MAP                                      │
│  (Worlds visible, locked/unlocked, progress shown)  │
│         │                                           │
│         ▼                                           │
│  WORLD ENTER                                        │
│  (Theme transition — forest sounds fade in)         │
│         │                                           │
│         ▼                                           │
│  MISSION PATH                                       │
│  (Visual trail of missions, some locked)            │
│         │                                           │
│         ▼                                           │
│  MISSION INTRO                                      │
│  (Mascot: "Help me count the apples!")              │
│         │                                           │
│         ▼                                           │
│  LESSON (Story Mode)                                │
│  (Interactive content, narrated, auto-advance)      │
│         │                                           │
│         ▼                                           │
│  MINI GAME (Quiz)                                   │
│  (Tap answer, drag & drop, sequencing)              │
│         │                                           │
│         ▼                                           │
│  CELEBRATION                                        │
│  (Confetti, stars fly, mascot cheers)               │
│         │                                           │
│         ▼                                           │
│  REWARD                                             │
│  (Stars added, coins earned, badge unlocked?)       │
│         │                                           │
│         ▼                                           │
│  NEXT MISSION or BACK TO MAP                        │
│  (Auto-advance option or child chooses)             │
│         │                                           │
│         ▼                                           │
│  ADVENTURE MAP                                      │
│  (Cycle repeats — the Loop)                         │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Key Rules

- **Never** show a URL or breadcrumb to the child.
- **Always** animate between scenes (slide, fade, zoom — never instant).
- **Never** show more than one CTA at a time to a child under 6.
- **Always** allow the child to exit to the Map with one tap (the Exit button).
- **Never** trap a child in a quiz — always allow "Ask Leo" hint.

---

## 3. Master Component Library

> Every UI element below will be built as a **Blade component** (`<x-kid.primary-button>`) and reused everywhere. No inline button styling in views.

### 3.1 — Buttons

| Component | Usage | Size | Min Touch Target |
|-----------|-------|------|------------------|
| `<x-kid.primary-button>` | Main action ("Let's Go!", "Continue") | Large | 64px height |
| `<x-kid.secondary-button>` | Secondary action ("Back", "Skip") | Medium | 56px height |
| `<x-kid.answer-card>` | Quiz answer choice | Full-width | 72px height |
| `<x-kid.icon-button>` | Small controls (sound toggle, exit) | Small | 48px height |
| `<x-kid.exit-button>` | Always top-right — returns to map | Small | 48px height |

### 3.2 — Feedback Components

| Component | Usage |
|-----------|-------|
| `<x-kid.star-counter>` | Shows current star total (animates when increasing) |
| `<x-kid.coin-counter>` | Shows current coin total |
| `<x-kid.progress-bar>` | Fills with themed color as child progresses |
| `<x-kid.celebration-modal>` | Full-screen celebration overlay |
| `<x-kid.reward-popup>` | Small popup showing what was earned |
| `<x-kid.mascot-bubble>` | Speech bubble from Leo or other mascots |
| `<x-kid.hint-bubble>` | Subtle pulsing hint for young children |

### 3.3 — Content Components

| Component | Usage |
|-----------|-------|
| `<x-kid.world-card>` | Adventure world on the map |
| `<x-kid.mission-card>` | Individual mission on the trail |
| `<x-kid.lesson-card>` | Lesson preview in guardian dashboard |
| `<x-kid.avatar-card>` | Child profile selector |
| `<x-kid.badge-display>` | Achievement badge grid |
| `<x-kid.story-banner>` | Full-width illustrated banner for mission intro |

### 3.4 — Navigation Components

| Component | Usage |
|-----------|-------|
| `<x-kid.exit-bar>` | Top bar with exit button + star counter |
| `<x-kid.bottom-nav>` | Optional — only for older kids (Grade 1+) |
| `<x-kid.back-button>` | Curved arrow, always accessible |

---

## 4. Color System & World Theming

### 4.1 — Global Palette

```
PURPOSE         HEX         USAGE
─────────────── ─────────── ─────────────────────
Primary         #7C3AED     Main CTAs, active states
Primary Light   #A78BFA     Hover, secondary fills
Success         #22C55E     Correct answers, completion
Success Dark    #16A34A     Pressed state
Encourage       #F59E0B     Stars, coins, rewards
Warning         #EF4444     NEVER used for wrong answers*
                *Wrong answers use gentle gray + retry prompt
Background      #FFF9F0     Warm off-white (softer than pure white)
Text Dark       #1F2937     Body text
Text Muted      #6B7280     Captions
```

### 4.2 — World Theme Colors

Each world has a **primary color** and **ambient palette** that controls backgrounds, cards, and transitions.

| World | Primary | Background Gradient | Accent |
|-------|---------|-------------------|--------|
| 🌳 Whispering Forest | `#16A34A` | `from-green-300 to-emerald-400` | `#FBBF24` (sunlight) |
| 🦁 Safari Plains | `#D97706` | `from-amber-300 to-orange-400` | `#84CC16` (grass) |
| 🌊 Ocean Cove | `#0284C7` | `from-blue-300 to-cyan-400` | `#F97316` (coral) |
| 🏰 Castle of Discovery | `#7C3AED` | `from-purple-300 to-fuchsia-400` | `#FACC15` (gold) |
| 🚀 Star Valley | `#4338CA` | `from-indigo-400 to-violet-500` | `#22D3EE` (neon) |

### 4.3 — World Identity Specs

```
WORLD: Whispering Forest
  Background Elements:
    - Layered tree silhouettes (parallax, 3 depths)
    - Floating butterflies (CSS animation, 3 variants)
    - Falling leaves (particle effect, gentle)
    - Sun rays from top-right corner
  Ambient Sound:
    - Birds chirping (loop, volume: 15%)
    - Gentle breeze (loop, volume: 10%)
  Mascot: Leo the Lion
  Transition In: Camera zoom through trees
  Transition Out: Leaves swirl and fade

WORLD: Safari Plains
  Background Elements:
    - Acacia tree silhouettes
    - Distant mountains (parallax)
    - Floating clouds (slow drift)
    - Grass tufts at bottom (sway)
  Ambient Sound:
    - Distant drums (loop, volume: 8%)
    - Wind across grass (loop, volume: 12%)
  Mascot: Eli the Elephant
  Transition In: Sweep across savanna
  Transition Out: Dust cloud dissolve

WORLD: Ocean Cove
  Background Elements:
    - Layered water waves (animated)
    - Bubbles rising (particle)
    - Fish swimming across (random)
    - Sunlight rays through water
  Ambient Sound:
    - Ocean waves (loop, volume: 20%)
    - Distant whale song (loop, volume: 5%)
  Mascot: Gigi the Giraffe (wearing snorkel)
  Transition In: Dive into water
  Transition Out: Bubble rise and fade

WORLD: Castle of Discovery
  Background Elements:
    - Castle silhouette in distance
    - Floating sparkles
    - Banner flags waving
    - Magical orbs drifting
  Ambient Sound:
    - Harp melody (loop, volume: 15%)
    - Wind through towers (loop, volume: 8%)
  Mascot: Pip the Panda (wearing crown)
  Transition In: Gates open
  Transition Out: Magical portal spin

WORLD: Star Valley
  Background Elements:
    - Twinkling stars (particle)
    - Shooting stars (occasional)
    - Planet silhouettes
    - Nebula clouds (slow shift)
  Ambient Sound:
    - Ambient space synth (loop, volume: 12%)
    - Soft chimes (random intervals)
  Mascot: Tara the Tiger (wearing space helmet)
  Transition In: Rocket launch zoom
  Transition Out: Warp speed lines
```

---

## 5. Typography

### Font Stack

```
Primary:    'Nunito', 'Baloo 2', sans-serif
Headings:   'Baloo 2', 'Nunito', sans-serif
Body:       'Nunito', sans-serif
Numbers:    'Nunito', tabular-nums
```

**Rationale:** Nunito and Baloo 2 are rounded, friendly fonts that are highly legible for children. Both are Google Fonts (free, fast).

### Size Scale

| Element | Size | Weight | Line Height |
|---------|------|--------|-------------|
| Hero Title (Map) | 2.5rem (40px) | 800 | 1.1 |
| Section Title | 1.75rem (28px) | 700 | 1.2 |
| Mission Title | 1.25rem (20px) | 700 | 1.3 |
| Body Text | 1rem (16px) | 600 | 1.5 |
| Caption | 0.875rem (14px) | 600 | 1.4 |
| Quiz Question | 1.5rem (24px) | 700 | 1.4 |
| Answer Option | 1.125rem (18px) | 600 | 1.4 |
| Star/Counter | 1.125rem (18px) | 800 | 1.0 |
| Mascot Speech | 1.125rem (18px) | 700 | 1.4 |

---

## 6. Button States & Interactions

### Primary Button States

```
NORMAL:
  Background: var(--world-primary)
  Text: white
  Shadow: 0 4px 0 rgba(0,0,0,0.15) (3D bottom edge)
  Scale: 1.0

PRESSED (active):
  Background: slightly darker
  Shadow: 0 1px 0 rgba(0,0,0,0.15) (compressed)
  Scale: 0.96
  Transition: 100ms ease-out

DISABLED:
  Background: #D1D5DB (gray)
  Text: #9CA3AF
  Shadow: none
  Cursor: default
  Opacity: 0.7

SUCCESS (after correct answer):
  Background: #22C55E
  Text: white
  Animation: bounce-pulse (300ms)
  Checkmark icon fades in

RETRY (after wrong answer):
  NO color change to red.
  Instead: gentle shake animation (200ms)
  Mascot bubble: "Try again! You can do it!"
  Button returns to normal state
```

### Answer Card States

```
DEFAULT:
  Background: white
  Border: 3px solid #E5E7EB
  Shadow: soft

TAPPED:
  Background: var(--world-primary-light)
  Border: 3px solid var(--world-primary)
  Scale: 0.97
  Haptic: light vibration (if supported)

CORRECT:
  Background: #DCFCE7 (green-100)
  Border: 3px solid #22C55E
  Checkmark bounces in
  Confetti particles burst

INCORRECT:
  Background: #F3F4F6 (gray-100)
  Border: 3px solid #9CA3AF
  Gentle shake
  Mascot: "Oops! Let's try again!"
  Card returns to default after 1.5s
```

---

## 7. Animation Rules

### Timing Standards

| Type | Duration | Easing |
|------|----------|--------|
| Button press | 100ms | ease-out |
| Card appear | 300ms | spring (back-out) |
| Page transition | 500ms | ease-in-out |
| Celebration burst | 1000ms | custom (explosive) |
| Mascot entrance | 600ms | bounce-in |
| Star fly to counter | 800ms | arc-path |
| Confetti fall | 2000ms | gravity (linear) |
| Hint pulse | 1500ms loop | ease-in-out |

### Keyframe Definitions (CSS)

```css
/* Bounce In — for card appearances */
@keyframes kid-bounce-in {
  0%   { transform: scale(0.3); opacity: 0; }
  50%  { transform: scale(1.05); opacity: 1; }
  70%  { transform: scale(0.95); }
  100% { transform: scale(1.0); }
}

/* Gentle Shake — for wrong answers */
@keyframes kid-shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-8px); }
  75% { transform: translateX(8px); }
}

/* Celebration Pulse — for correct answers */
@keyframes kid-celebrate {
  0%   { transform: scale(1.0); }
  50%  { transform: scale(1.15); }
  100% { transform: scale(1.0); }
}

/* Float — for ambient world elements */
@keyframes kid-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Pulse Hint — for young children */
@keyframes kid-hint-pulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(124,58,237,0.4); }
  50% { transform: scale(1.03); box-shadow: 0 0 0 12px rgba(124,58,237,0); }
}
```

### Rules

- **Never** animate faster than 100ms — children's eyes can't track it.
- **Never** animate slower than 2000ms (except ambient loops) — feels broken.
- **Always** prefer `spring` or `back-out` easing for playfulness.
- **Never** use linear easing for UI elements (feels robotic).
- **Always** animate the mascot when giving feedback.

---

## 8. Sound & Audio Design

### Sound Categories

| Category | Volume | When |
|----------|--------|------|
| Ambient (world) | 10–20% | Continuous while in a world |
| Button tap | 30% | Every tap |
| Correct answer | 40% | Correct answer selected |
| Wrong answer | 30% | Wrong answer — gentle "boing", never harsh |
| Star earned | 40% | Stars fly to counter |
| Coin earned | 35% | Coins collected |
| Badge unlocked | 50% | Achievement fanfare |
| Celebration | 60% | Mission complete |
| Mascot speech | 50% | Mascot talks (future TTS) |

### Sound File Specs

```
Format:     .mp3 (smaller) or .ogg (better quality)
Bitrate:    96kbps (voice), 128kbps (music)
Duration:   < 2s for SFX, 10-30s loops for ambient
Files:
  /sounds/sfx/tap.mp3
  /sounds/sfx/correct.mp3
  /sounds/sfx/wrong.mp3
  /sounds/sfx/star.mp3
  /sounds/sfx/coin.mp3
  /sounds/sfx/badge.mp3
  /sounds/sfx/celebrate.mp3
  /sounds/ambient/forest.mp3
  /sounds/ambient/safari.mp3
  /sounds/ambient/ocean.mp3
  /sounds/ambient/castle.mp3
  /sounds/ambient/space.mp3
```

### Mute System

- Sound toggle in top-right (always accessible).
- Preference stored in session.
- Default: ON.
- If a parent sets mute in guardian settings, it overrides.

---

## 9. Mascot System

### Primary Mascot: Leo the Lion 🦁

Leo is the child's companion throughout the entire app. He is always present, always encouraging, never judging.

```
Role:       Guide, cheerleader, friend
Personality: Warm, enthusiastic, patient, funny
Tone:       "You can do it!" / "Great job!" / "Let's try again!"
Speech Style:
  - Short sentences (max 8 words)
  - Use child's name frequently
  - Ask rhetorical questions ("Can you find the red one?")
  - Celebrate everything ("WOW! Amazing!")
```

### Mascot Bubble Component

```
┌─────────────────────────────┐
│  ╭───────────────────────╮  │
│  │ Leo: "What color is   │  │
│  │ the sky?"             │  │
│  ╰───────────────────────╯  │
│          ◢◤◢◤◢◤             │
│  🦁                         │
└─────────────────────────────┘
```

- Positioned bottom-left by default.
- Can move to top-center during quizzes.
- Text appears with a typewriter effect (30ms per character).
- A small "tap to continue" indicator pulses after text completes.

### Mascot Reactions

| Situation | Leo's Reaction |
|-----------|----------------|
| Correct answer | Jumps, arms up, big smile |
| Wrong answer | Thoughtful chin rub, gentle nod |
| Mission complete | Dance, confetti, stars |
| Idle (10s no action) | "Hmm... what should we do?" |
| Hint requested | Points toward correct direction |
| World entered | Waves, "Welcome to [World]!" |

---

## 10. Card Layouts

### World Card (Adventure Map)

```
┌─────────────────────────┐
│  ┌───────────────────┐  │
│  │                   │  │
│  │       🌳          │  │  ← World icon (large, centered)
│  │                   │  │
│  └───────────────────┘  │
│                         │
│   Whispering Forest     │  ← World name (themed color)
│   Help the forest       │  ← Description (muted, small)
│   friends!              │
│                         │
│  ┌─────────────────┐    │
│  │ ⭐ 3/5 missions │    │  ← Progress badge
│  └─────────────────┘    │
└─────────────────────────┘
  Border-top: 8px solid [world color]
  Hover: scale-105, shadow-xl
```

### Mission Card (World Trail)

```
┌─────────────────────────┐
│  🍎  Help Eli Count     │  ← Mission icon + title
│      Apples             │
│                         │
│  ⭐⭐⭐ ☆ ☆              │  ← Stars earned (out of 3)
│                         │
│  [▶ Start Mission]      │  ← CTA button
└─────────────────────────┘
```

### Avatar Card (Profile Selection)

```
┌─────────────────────────┐
│                         │
│          🦁             │  ← Avatar emoji (large)
│                         │
│      Emma               │  ← Name
│      Age 5 · PP1        │  ← Age & level
│                         │
│  ⭐ 24  🪙 12  💎 3     │  ← Currencies
│                         │
│  ████████░░ 68%         │  ← Progress bar
└─────────────────────────┘
```

---

## 11. Responsive & Landscape Rules

### Target Devices

| Device | Orientation | Priority |
|--------|-------------|----------|
| iPad (10.2") | Landscape | P1 — Primary design target |
| iPad Mini | Landscape | P1 |
| Android Tablet (10") | Landscape | P1 |
| Phone (6.1") | Landscape | P2 — Must work, degraded layout |
| Phone (6.1") | Portrait | P3 — Emergency mode only |

### Breakpoints

```css
/* Primary: Landscape tablet */
@media (min-width: 768px) and (orientation: landscape) {
  /* Full layout — 3 columns, large cards */
}

/* Landscape phone */
@media (max-width: 767px) and (orientation: landscape) {
  /* Compact — 2 columns, smaller cards */
}

/* Portrait (fallback) */
@media (orientation: portrait) {
  /* Stack vertically, single column */
  /* Show "Rotate for better experience" prompt */
}
```

### Layout Grid

```
TABLET LANDSCAPE (1024×768):
  Content max-width: 960px
  Padding: 32px horizontal
  Grid: 3 columns, 24px gap
  Card min-width: 280px

PHONE LANDSCAPE (667×375):
  Content max-width: 100%
  Padding: 16px horizontal
  Grid: 2 columns, 16px gap
  Card min-width: 240px
```

---

## 12. Reward Economy Architecture

### Currencies

| Currency | Earned By | Spent On | Storage |
|----------|-----------|----------|---------|
| ⭐ Stars | Completing missions, correct answers | — (reputation only) | `children.total_stars` |
| 🪙 Coins | Completing missions (2-5 per mission) | Shop (avatars, themes) | `children.total_coins` (NEW) |
| 💎 Gems | Perfect missions, weekly challenges | Premium shop items | `children.total_gems` (NEW) |
| 🏅 Badges | Milestones (10 missions, 5-day streak, etc.) | — (permanent) | `child_badges` table |
| 🎁 Treasure Chests | Random reward after some missions | Contains coins/gems/stars | `child_treasure_chests` (FUTURE) |
| 🔥 Daily Streak | Playing consecutive days | Multiplier bonus | `children.daily_streak` (NEW) |

### Earning Rules

```
Per Correct Answer:    +1 ⭐
Per Mission Complete:  +3 ⭐ + 2-5 🪙 (based on accuracy)
Perfect Mission:       +1 💎 (bonus)
Daily Login:           Streak +1
3-Day Streak:          ×1.5 coin multiplier
7-Day Streak:          ×2.0 coin multiplier + free treasure chest
10 Missions:           🏅 "Explorer" Badge
25 Missions:           🏅 "Adventurer" Badge
50 Missions:           🏅 "Master Explorer" Badge
```

### Spending Rules (Future Shop)

```
New Avatar:        50 🪙
World Theme:       100 🪙
Treasure Key:      10 💎
Special Badge:     25 💎
```

### Database Changes Needed (Future Migration)

```php
// Add to children table:
$table->integer('total_coins')->default(0);
$table->integer('total_gems')->default(0);
$table->integer('daily_streak')->default(0);
$table->date('last_played_date')->nullable();

// New tables:
// child_treasure_chests (id, child_id, contents_json, opened_at)
// reward_transactions (id, child_id, type, amount, reason, created_at)
```

---

## 13. Celebration & Feedback Patterns

### Celebration Tiers

| Tier | Trigger | Animation | Duration |
|------|---------|-----------|----------|
| Micro | Correct answer | Card flash green + checkmark | 500ms |
| Small | Mission section complete | Star flies to counter | 800ms |
| Medium | Full mission complete | Confetti + mascot dance + stars | 2000ms |
| Large | World section complete | Big celebration modal + badges | 3000ms |
| Mega | First time perfect | Full-screen celebration + fireworks | 4000ms |

### Celebration Modal (Medium Tier)

```
┌──────────────────────────────┐
│                              │
│         🎉 🎊 🎉             │  ← Confetti rain
│                              │
│      MISSION COMPLETE!       │  ← Title (animated)
│                              │
│     ⭐ ⭐ ⭐                  │  ← Stars earned
│                              │
│      +5 🪙                   │  ← Coins earned
│                              │
│  ╭────────────────────────╮  │
│  │  🦁 "Amazing work,    │  │  ← Mascot praise
│  │     Emma!"             │  │
│  ╰────────────────────────╯  │
│                              │
│  [▶ Next Mission]            │  ← CTA
│  [🗺️ Back to Map]           │  ← Secondary
│                              │
└──────────────────────────────┘
```

---

## 14. Loading & Transition States

### Scene Transitions

```
Map → World:
  1. Tap world card
  2. Card scales up to fill screen (500ms)
  3. World ambient fades in
  4. Leo enters: "Welcome to [World]!"
  5. Mission trail fades in

World → Mission:
  1. Tap mission card
  2. Card expands to fullscreen (400ms)
  3. Lesson content fades in
  4. Leo: "Let's start!"

Mission → Quiz:
  1. Lesson complete
  2. Leo: "Ready for a game?"
  3. Quiz cards slide in from bottom (staggered, 100ms each)
  4. First question appears

Quiz → Celebration:
  1. Last answer submitted
  2. Brief pause (500ms — anticipation)
  3. Celebration modal bursts in
  4. Stars fly to counter
```

### Loading Screen

```
┌──────────────────────────────┐
│                              │
│                              │
│                              │
│          🦁                  │
│     Leo is packing           │
│     your adventure...        │
│                              │
│     ● ● ●                    │  ← Bouncing dots
│                              │
│                              │
│                              │
└──────────────────────────────┘
```

- Never show a spinner. Always show Leo + a message.
- Max acceptable load time: 1.5s before optimization.

---

## 15. Accessibility & Safe Touch Areas

### Touch Target Rules

```
Minimum touch target:    48 × 48 px
Recommended touch target: 64 × 64 px
Spacing between targets: 16 px minimum
```

### Safe Areas

```
Top:    20px safe area (status bar / camera notch)
Bottom: 20px safe area (home indicator)
Sides:  16px minimum padding
```

### Visual Accessibility

- **Contrast ratio:** Minimum 4.5:1 for all text.
- **Color blindness:** Never rely on color alone — always pair with icon or shape.
- **Font size:** Body text never below 16px.
- **Focus indicators:** 3px outline in primary color for keyboard users.

---

## 16. Terminology Dictionary

> **CRITICAL:** These are the words children and parents see. Internal code names can differ.

| Internal (Code) | Child-Facing | Parent-Facing |
|-----------------|-------------|---------------|
| `Subject` | — | "Subject" |
| `Topic` | — | "Topic" |
| `Lesson` | **"Mission"** | "Lesson" |
| `Quiz` | **"Game"** / **"Challenge"** | "Quiz" |
| `Quiz Question` | **"Puzzle"** | "Question" |
| `Question Option` | **"Answer"** | "Option" |
| `Adventure World` | **"World"** | "World" |
| `World Lesson` | **"Mission"** | "Lesson" |
| `Stars` | **"Stars"** ⭐ | "Stars" |
| `Coins` | **"Coins"** 🪙 | "Coins" |
| `Gems` | **"Gems"** 💎 | "Gems" |
| `Badge` | **"Trophy"** 🏅 | "Badge" |
| `Guardian` | — | "Parent" |
| `Child` | **"Adventurer"** | "Child" |
| `Login` | — | "Sign In" |
| `Dashboard` | **"Adventure Map"** | "Dashboard" |

---

## Implementation Priority

```
PHASE 1 (Foundation):
  □ Create Blade components directory: resources/views/components/kid/
  □ Build: primary-button, secondary-button, exit-button, answer-card
  □ Build: star-counter, progress-bar, mascot-bubble
  □ Build: world-card, mission-card, avatar-card
  □ Create CSS: animations, color variables, typography
  □ Add landscape-only layout to kids layout

PHASE 2 (Reward Economy):
  □ Migration: add total_coins, total_gems, daily_streak
  □ Seeder: update existing children with starting currencies
  □ Build: coin-counter, reward-popup, celebration-modal
  □ Build: badge-display grid

PHASE 3 (World Theming):
  □ CSS per-world background system
  □ Ambient sound loader (Howler.js or Web Audio API)
  □ World transition animations
  □ Parallax background layers

PHASE 4 (Quiz Engine):
  □ Rebrand "Quiz" → "Game" in all kid views
  □ Rebrand "Lesson" → "Mission" in all kid views
  □ Build quiz type renderers (tap, drag, sequence, etc.)
  □ Build celebration transitions

PHASE 5 (Polish):
  □ Sound effects library
  □ Haptic feedback (mobile API)
  □ Idle detection + mascot prompts
  □ Portrait "rotate device" prompt
```

---

## Sign-Off

> This document defines the visual and interaction rules for the entire kid-facing experience. All future UI work must conform to these standards. Components must be built once, reused everywhere. No one-off button styles. No one-off animations. Everything is a system.

**Next Step:** Begin Phase 1 — Build the Master Component Library.