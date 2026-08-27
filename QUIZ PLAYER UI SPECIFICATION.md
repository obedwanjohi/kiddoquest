# 🎮 Quiz Player UI Specification

> **Purpose:** The definitive developer guide for the child-facing Quiz Player. Every Phase 1 question type has exact specs for layout, touch targets, animations, audio, and accessibility.
>
> **Rule:** The quiz player is the **heart of the product**. It must feel delightful on every screen size.

---

## 📐 Universal Layout System

### Screen Zones (Landscape — Primary Orientation)

```
┌─────────────────────────────────────────────────────────────┐
│  [🗺️ Exit]                          [⭐ 12]    EXIT BAR (64px)│
├─────────────────────────────────────────────────────────────┤
│  🦁 "You can do it!"           Progress: ████░░░  2 / 5      │ HEADER (80px)
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                                                             │
│                   QUESTION CONTENT AREA                     │  MAIN (flex-1)
│                                                             │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│              [Next →]  /  [🎉 See My Score!]                │ FOOTER (88px)
└─────────────────────────────────────────────────────────────┘
```

### Screen Zones (Portrait — Fallback)

```
┌───────────────────────────┐
│  [🗺️]          [⭐ 12]     │  EXIT BAR (56px)
├───────────────────────────┤
│  🦁 "You can do it!" 2/5  │  HEADER (64px)
│  ████████░░░              │
├───────────────────────────┤
│                           │
│   QUESTION CONTENT        │  MAIN (flex-1, scroll)
│                           │
│                           │
├───────────────────────────┤
│       [Next →]            │  FOOTER (72px)
└───────────────────────────┘
```

---

## 📏 Touch Target Rules

| Element | Minimum Size | Recommended |
|---------|-------------|-------------|
| Answer option (text) | 56×56px | 72×72px |
| Answer option (image) | 80×80px | 120×120px |
| Primary button | 56px height | 64px height |
| Icon button | 44×44px | 48×48px |
| Drag handle | 44×44px | 56×56px |

**Safe spacing:** Minimum 12px between touchable elements to prevent mis-taps.

---

## 🎨 Color System (Quiz States)

| State | Background | Border | Icon |
|-------|-----------|--------|------|
| Default | `--kid-bg` (#F9FAFB) | transparent | — |
| Hover | `--kid-bg` + scale(1.02) | `--kid-primary` | — |
| Selected | `--kid-primary-light` (#EDE9FE) | `--kid-primary` (#7C3AED) | — |
| Correct | #DCFCE7 | `--kid-success` (#22C55E) | ✅ |
| Incorrect | #FEE2E2 | `--kid-error` (#EF4444) | ❌ |
| Disabled | opacity 0.5 | — | — |

---

## ⏱️ Animation Timing

| Event | Animation | Duration |
|-------|-----------|----------|
| Question enter | slide from right | 300ms ease-out |
| Question leave | slide to left | 200ms ease-in |
| Option hover | scale up | 200ms ease |
| Correct answer | bounce | 500ms ease |
| Wrong answer | shake | 500ms ease |
| Feedback overlay | pop in | 300ms ease |
| Feedback dismiss | fade out | 200ms ease |
| Confetti burst | fall + rotate | 1500ms ease-out |
| Results screen | scale in | 500ms ease-out |
| Star reveal | bounce (staggered) | 100ms delay each |

---

## 🔊 Audio Behavior

| Event | Sound | When |
|-------|-------|------|
| Page load | Gentle chime | On first question |
| Correct answer | Celebration jingle (0.5s) | Immediately on tap |
| Wrong answer | Soft "aww" (0.3s) | Immediately on tap |
| Quiz complete | Triumph fanfare (2s) | On results screen |
| Star earned | Sparkle ding (0.3s) | Per star reveal |

**Rule:** Audio NEVER autoplays louder than 50% volume. All sounds must be skippable.

---

## ♿ Accessibility Checklist

- [ ] All touch targets ≥ 44×44px
- [ ] Color contrast ratio ≥ 4.5:1 (WCAG AA)
- [ ] Never rely on color alone (always include icon + text)
- [ ] `aria-label` on all icon-only buttons
- [ ] Keyboard navigable (Tab + Enter/Space)
- [ ] Focus visible (2px purple outline)
- [ ] Animations respect `prefers-reduced-motion`
- [ ] Screen reader announces question type + prompt on load

---

## 🏗️ Phase 1 Question Type Specs (6 Types)

These are the **six types** we build first. Each has a complete specification below.

---

### QT-01: Multiple Choice (Tap Answer) 👆

**Interaction:** Child taps one correct answer from 2–4 options.

#### Layout — Landscape

```
┌───────────────────────────────────────────────────────┐
│                  Which letter is A?                    │
│                                                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐│
│  │          │  │          │  │          │  │        ││
│  │   🅰️    │  │    B     │  │    C     │  │   D    ││
│  │          │  │          │  │          │  │        ││
│  └──────────┘  └──────────┘  └──────────┘  └────────┘│
│    (tap)        (tap)        (tap)        (tap)      │
└───────────────────────────────────────────────────────┘
```

#### Layout — Portrait

```
┌───────────────────────┐
│   Which letter is A?  │
│                       │
│  ┌─────────────────┐  │
│  │       🅰️       │  │
│  └─────────────────┘  │
│  ┌─────────────────┐  │
│  │        B        │  │
│  └─────────────────┘  │
│  ┌─────────────────┐  │
│  │        C        │  │
│  └─────────────────┘  │
└───────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Grid (landscape) | 1 row × N columns (if ≤4 options) |
| Grid (portrait) | 1 column × N rows |
| Option size (text) | min-height 80px, full width |
| Option size (image) | 120×120px, square |
| Font size | `--kid-text-title` (28px) |
| Feedback | Selected → bounce, Correct → green flash + confetti, Wrong → shake |
| Image aspect | 1:1 square @ 300×300px min |

#### State Machine

```
[DEFAULT] → tap → [SELECTED] → evaluate → [CORRECT] or [INCORRECT]
                                         → [LOCKED] (no more taps)
                                         → show [Next] button
```

---

### QT-02: True / False ✅❌

**Interaction:** Child taps one of two huge buttons.

#### Layout — Landscape

```
┌───────────────────────────────────────────────────────┐
│          "Banana" starts with the letter B            │
│                                                       │
│  ┌─────────────────────┐  ┌─────────────────────┐    │
│  │                     │  │                     │    │
│  │      ✅ TRUE        │  │     ❌ FALSE        │    │
│  │                     │  │                     │    │
│  └─────────────────────┘  └─────────────────────┘    │
└───────────────────────────────────────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Layout | Always 2 side-by-side buttons |
| Button height | 160px minimum |
| Button width | 45% each, 4% gap |
| True button color | Green tint (#DCFCE7 bg, #22C55E text) |
| False button color | Red tint (#FEE2E2 bg, #EF4444 text) |
| Font size | `--kid-text-hero` (36px) |
| Icons | ✅ (True), ❌ (False) — always present, 32px |

---

### QT-06: Listen & Choose 🔊

**Interaction:** Audio auto-plays on load. Child taps correct visual answer.

#### Layout

```
┌───────────────────────────────────────────────────────┐
│                                                       │
│                    🔊 Listen!                         │
│                                                       │
│              ┌───────────────────┐                    │
│              │                   │                    │
│              │   ▶ PLAY SOUND    │   ← Big audio btn  │
│              │      (80px)       │     (tappable)     │
│              │                   │                    │
│              └───────────────────┘                    │
│                                                       │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│  │    A     │  │    B     │  │    C     │            │
│  └──────────┘  └──────────┘  └──────────┘            │
└───────────────────────────────────────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Audio button | 120×120px, circular, pulse animation while playing |
| Autoplay | Yes, on question load (500ms delay) |
| Replay | Audio button stays tappable |
| Options grid | Same as QT-01 |
| Audio format | MP3, 3–10 seconds |
| Visual feedback | Button pulses (scale 1.0→1.1) while audio plays |
| Accessibility | Auto-transcript shown for screen readers |

---

### QT-09: Count the Objects 🔢

**Interaction:** Child counts objects in image, taps the correct number.

#### Layout

```
┌───────────────────────────────────────────────────────┐
│              How many apples do you see?               │
│                                                       │
│         ┌─────────────────────────────────┐           │
│         │                                 │           │
│         │      🍎 🍎 🍎                   │           │
│         │      🍎 🍎                      │           │
│         │                                 │           │
│         └─────────────────────────────────┘           │
│                                                       │
│    ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐                │
│    │ 1  │ │ 2  │ │ 3  │ │ 4  │ │ 5  │                │
│    └────┘ └────┘ └────┘ └────┘ └────┘                │
└───────────────────────────────────────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Counting image | REQUIRED, 16:9 landscape, max-height 50vh |
| Number buttons | Grid: 1 row × N (landscape), 2 rows × N/2 (portrait) |
| Button size | 80×80px minimum |
| Number font | `--kid-text-title` (28px), bold |
| Image border | Rounded corners (var(--kid-radius-lg)) |

---

### QT-10: Complete the Pattern 🔁

**Interaction:** Child identifies what comes next in a sequence.

#### Layout

```
┌───────────────────────────────────────────────────────┐
│               What comes next?                         │
│                                                       │
│         🔴   🔵   🔴   🔵   🔴   ❓                  │
│                                       ↑               │
│                                  (mystery slot)       │
│                                                       │
│    ┌──────┐  ┌──────┐  ┌──────┐                      │
│    │  🔴  │  │  🔵  │  │  🟢  │                      │
│    └──────┘  └──────┘  └──────┘                      │
└───────────────────────────────────────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Pattern display | Horizontal row, centered, scrollable if >6 items |
| Mystery slot | Dashed border, question mark icon, pulsing animation |
| Pattern item size | 64×64px (landscape), 48×48px (portrait if >5 items) |
| Answer options | 3 cards, same as QT-01 but smaller (100×100px) |
| Option spacing | 16px gap |

---

### QT-11: Memory Match 🃏

**Interaction:** Child flips cards two at a time to find matching pairs.

#### Layout — Landscape

```
┌───────────────────────────────────────────────────────┐
│           Find the matching pairs!   Pairs: 0/3       │
│                                                       │
│    ┌────┐ ┌────┐ ┌────┐ ┌────┐                       │
│    │ ?  │ │ ?  │ │ 🍎 │ │ ?  │                       │
│    └────┘ └────┘ └────┘ └────┘                       │
│    ┌────┐ ┌────┐                                     │
│    │ ?  │ │ 🅰️ │                                     │
│    └────┘ └────┘                                     │
│                                                       │
│              Flips: 4                                 │
└───────────────────────────────────────────────────────┘
```

#### Specs

| Property | Value |
|----------|-------|
| Card grid | Auto: 4 cols × N rows (landscape), 3 cols (portrait) |
| Card size | 100×100px min, maintains 1:1 aspect |
| Card back | Purple gradient with Leo's face 🦁 |
| Card front | White bg, centered content |
| Flip animation | 3D Y-axis rotate, 400ms |
| Match found | Cards glow green, bounce, then fade to 50% opacity |
| No match | Cards flip back after 800ms delay |
| Win condition | All pairs found → auto-advance after celebration |

#### Game Logic

```
1. All cards face-down (shuffled)
2. Child taps card → flips to face-up
3. Child taps second card → flips to face-up
4. If match_key matches:
   → Both cards stay open, glow green
   → pairs_found++
   → If pairs_found == total_pairs: WIN
5. If no match:
   → Both cards flip back after 800ms
   → flip_count += 2
```

---

## 🚀 Out of Scope (Phase 2+)

These types are **deferred** and will be specified later:

| Type | Reason |
|------|--------|
| QT-03 Matching | Needs drag-line or tap-pair UX research |
| QT-04 Sort | Needs bucket definitions in metadata |
| QT-05 Sequence | Needs drag-and-drop library |
| QT-07 Speak | Needs Web Speech API integration |
| QT-08 Spell | Needs drag-and-drop letter tiles |
| QT-12 Tracing | Needs HTML5 Canvas drawing |
| QT-13 Spot & Find | Needs hotspot editor in admin |

---

## 📋 QA Checklist — Apply to Every Type

```
VISUAL
□ Desktop works (1920px)
□ Tablet landscape works (1024px)
□ Tablet portrait works (768px)
□ Phone landscape works (667px)
□ Phone portrait works (375px)
□ No horizontal overflow
□ No text truncation

INTERACTION
□ All buttons reachable (no dead zones)
□ Touch targets ≥ 44px
□ Tap response < 100ms
□ No double-trigger on fast tap

FLOW
□ Correct answer → green flash → feedback
□ Wrong answer → shake → feedback
□ Next button appears after answer
□ Progress bar updates
□ Can't skip questions
□ Can't change answer after submit

STATES
□ Loading state (if fetching)
□ Empty state (if no questions)
□ Error state (if fetch fails)
□ Offline state (if no connection)

ACCESSIBILITY
□ Keyboard navigation works
□ Focus visible
□ Screen reader announces content
□ Color contrast passes
□ Animations respect reduced-motion
```

---

## 🔗 File Structure

```
resources/views/kids/quiz/
├── engine.blade.php           # Main quiz shell (Alpine.js)
├── renderers/
│   ├── multiple-choice.blade.php   # QT-01
│   ├── true-false.blade.php        # QT-02
│   ├── listen-choose.blade.php     # QT-06
│   ├── count-objects.blade.php     # QT-09
│   ├── complete-pattern.blade.php  # QT-10
│   └── memory-match.blade.php      # QT-11
└── partials/
    ├── feedback-overlay.blade.php  # Correct/wrong popup
    ├── progress-header.blade.php   # Leo + progress bar
    └── results-screen.blade.php    # Final score + stars

public/sounds/quiz/
├── correct.mp3
├── wrong.mp3
├── complete.mp3
├── star.mp3
└── page-load.mp3
```

---

*This specification is frozen for Phase 1. Any changes require approval.*