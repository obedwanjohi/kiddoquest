# 🎮 KID INTERACTION GUIDELINES
### The Personality Bible for Kids Learn

> *"A polished, joyful experience matters more to parents and children than dozens of extra features."*

---

## 📖 Purpose

This document defines the **personality** of the app — the invisible rules that make every screen feel like it was designed by the same person, for the same child.

These rules are not suggestions. Every screen, every interaction, every animation must comply.

If a design violates these rules, it is a bug — even if the code works.

---

## 0. GOLDEN RULES (Non-Negotiable)

```
1. A child should never feel stupid.
2. A child should never feel lost.
3. A child should never feel bored.
4. A parent should never feel anxious.
5. Every screen should feel alive.
```

---

## 1. ANIMATION TIMING

### Standard Durations

| Event | Duration | Easing | Why |
|-------|----------|--------|-----|
| Button press feedback | 100ms | ease-out | Instant confirmation of touch |
| Card hover | 150ms | ease-out | Gentle invitation |
| Card appear (question enter) | 300ms | spring (back-out) | Playful entrance |
| Page transition | 500ms | smooth | Calm, not jarring |
| Correct answer bounce | 500ms | spring | Joy without overstimulation |
| Wrong answer shake | 400ms | ease | Gentle, never violent |
| Leo mascot entrance | 600ms | spring | Bounces in like a friend |
| Celebration burst | 1000ms | custom | The BIG moment |
| Confetti fall | 2000ms | gravity | Let them enjoy it |
| Star reward pop | 300ms | spring | Feels earned |

### Motion Philosophy

**Animations must answer "why", not just "how".**

| Element | ❌ Bad | ✅ Good |
|---------|--------|---------|
| Button | Teleports in | Fade + Scale + Ease |
| Mascot | Appears suddenly | Slide → Bounce → Wave |
| Correct Answer | Just turns green | Card grows → Glow → Star burst → Leo celebrates → Next button fades in |
| Wrong Answer | Red flash + buzzer | Tiny shake → Gentle sound → Hint appears → Retry enabled |
| Page Change | Hard cut | Slide or fade with direction hint |

### The "Never" List

- **Never** use dramatic shaking for wrong answers
- **Never** use loud buzzers or error sounds
- **Never** make a child wait more than 1 second without visual feedback
- **Never** teleport elements (no `display: none` → `display: block` without transition)
- **Never** use linear easing for organic elements (buttons, cards, characters)
- **Never** autoplay celebrations — they must be triggered by an action

---

## 2. TAP ECONOMY (How Many Taps?)

### Maximum Taps Per Task

| Age Group | Max Taps to Complete Any Task |
|-----------|-------------------------------|
| Ages 3-4 | 2 taps |
| Ages 5-6 | 3 taps |
| Ages 7-8 | 4 taps |

### Task Examples

| Task | Taps | Flow |
|------|------|------|
| Answer a question | 2 | Tap answer → Tap "Next" (or auto-advance) |
| Start a lesson | 1 | Tap mission card |
| Return to map | 1 | Tap exit/home button |
| Switch profile | 2 | Tap "Switch Player" → Tap avatar |
| Replay a quiz | 2 | Tap "Play Again" → Tap "Start" |

### Rule

> If a task requires more than 3 taps for a 5-year-old, simplify the design.

---

## 3. COGNITIVE LOAD RULES

### Maximum Words On Screen

| Age Group | Max Words On Screen | Max Words Per Sentence |
|-----------|--------------------|-----------------------|
| Ages 3-4 | 5 words | 4 words |
| Ages 5-6 | 10 words | 7 words |
| Ages 7-8 | 15 words | 10 words |

### Reading Level

| Age Group | Reading Level Target |
|-----------|---------------------|
| Ages 3-4 | No reading required — Leo narrates everything |
| Ages 5-6 | Pre-reader (sight words + audio support) |
| Ages 7-8 | Grade 2 reading level (audio support optional) |

### Rule

> If you remove all audio, can a 7-year-old still understand what to do? If not, simplify the text.

---

## 4. VISUAL RULES

### Minimum Image Sizes

| Context | Minimum Size | Why |
|---------|-------------|-----|
| Answer option image | 80×80px | Must be recognizable |
| Lesson illustration | 200×200px | Must be engaging |
| Avatar / mascot | 48×48px (mini), 120×120px (full) | Emotional connection |
| Icon / badge | 32×32px | Must be identifiable |
| Emoji in question | 36px font | Must be visible |

### Maximum Content Per Screen

| Element | Maximum |
|---------|---------|
| Answer options | 6 (if more, paginate) |
| Text paragraphs | 1 (break into cards/chunks) |
| Missions per world | 8 (scroll if more) |
| Stars/coins visible | Track number, show 3-star max graphic |
| Buttons on screen | 3 (primary + secondary + exit) |

### Color Rules

| Context | Rule |
|---------|------|
| Correct answer | Always `--kid-success` (#22C55E) + green checkmark |
| Wrong answer | Never `--kid-danger` red for the answer itself — use muted gray + gentle shake |
| Primary action | Always `--kid-primary` (purple) |
| Exit/Delete | Only place `--kid-danger` is used |
| World themes | Each world overrides `--world-*` tokens, but structure stays consistent |

---

## 5. AUDIO RULES

### Autoplay Policy

| Context | Autoplay? | Volume |
|---------|-----------|--------|
| Page load / lesson start | ❌ No | — |
| After user taps "Start" | ✅ Yes — background music | 30% |
| Question appears | ✅ Yes — narration prompt | 70% |
| Correct answer | ✅ Yes — success chime | 60% |
| Wrong answer | ✅ Yes — gentle "try again" | 50% |
| Leo speaks | ✅ Yes — after tap or prompt | 80% |
| Celebration | ✅ Yes — fanfare | 70% |

### Narration Timing

| Event | Delay Before Audio | Why |
|-------|-------------------|-----|
| Question appears | 500ms | Let child read first, then narrate |
| Leo encouragement | 300ms after action | Feels responsive, not canned |
| Correct feedback | 200ms after visual | Sound follows sight |
| Wrong feedback | 400ms after visual | Gentler delay softens the mistake |

### Audio "Never" List

- **Never** autoplay music on page load without user interaction (browser policy + child policy)
- **Never** use harsh/buzzer sounds for wrong answers
- **Never** make audio unskippable for narration longer than 5 seconds
- **Never** overlap Leo's voice with background music at equal volume

---

## 6. IDLE BEHAVIOR (What Leo Does When Child Is Inactive)

Leo the Lion is the child's companion. He should never feel absent.

### Idle Timeline

| Time Inactive | Leo's Action | Purpose |
|--------------|-------------|---------|
| 5 seconds | Slight head tilt, blink | Shows he's "alive" |
| 10 seconds | Small wave | Gentle reminder |
| 15 seconds | Speech bubble: "Tap an answer!" | Direct hint |
| 20 seconds | Speech bubble + points at the correct area | Strong hint |
| 30 seconds | Audio: "Need help? Tap here!" + highlights first option | Rescue |
| 60 seconds | "Let's take a break!" → option to return to map | Prevent frustration |

### Implementation

- Track `lastInteractionTime` on every touch/click
- Reset timer on any user action
- Use `setTimeout` cascade or timestamp comparison in render loop
- Never penalize idle time — it's always a hint, never a timeout/failure

---

## 7. REWARD FREQUENCY

### Star Rewards

| Event | Stars | Cumulative |
|-------|-------|-----------|
| Correct answer (first try) | 1⭐ | Running total |
| Perfect quiz (all first-try) | Bonus 3⭐ | Big reward |
| Complete a lesson | 2⭐ | Milestone |
| Complete a world | 5⭐ | Major milestone |

### Rule

> Stars should feel **earned**, not given. A child should earn a star every 2-3 minutes of play.

### Celebration Frequency

| Event | Celebration Level |
|-------|------------------|
| Single correct answer | Mini: card bounce + chime (1 second) |
| 3 correct in a row | Small: Leo thumbs up + sparkle (2 seconds) |
| Complete a quiz | Medium: confetti + Leo dance (3 seconds) |
| Complete a world | Large: full celebration page + badge reveal (5+ seconds) |

### The "Over-Celebration" Warning

> ⚠️ **Never make every answer a huge celebration.** If every correct answer triggers confetti, confetti loses its magic. Reserve BIG celebrations for milestones. Small wins get small rewards.

---

## 8. WRONG ANSWER PROTOCOL

This is the most emotionally sensitive part of the app.

### State Machine

```
Child taps wrong answer
        ↓
[400ms] Gentle shake animation (small amplitude)
        ↓
[Simultaneous] Soft "try again" sound (never a buzzer)
        ↓
[600ms] Leo speech bubble: "Almost! Try again!" or "Not quite — you've got this!"
        ↓
[200ms] Wrong option fades to 50% opacity (not red — gray)
        ↓
[0ms] All other options remain tappable
        ↓
[After 2nd wrong try] Leo gently highlights the correct answer area
        ↓
[After 3rd wrong try] Leo says the answer aloud + reveals it
```

### Rules

- **Never** use the word "wrong" or "incorrect" in child-facing text
- **Never** use red (`--kid-danger`) for wrong answer cards — use muted gray
- **Never** lock the child out after a wrong answer — always allow retry
- **Always** use encouraging language: "Almost!", "Good try!", "You're getting it!"
- **Always** reveal the correct answer after 3 attempts (never leave a child stuck)

---

## 9. PROGRESS & MOMENTUM

### Pacing Rules

| Metric | Rule |
|--------|------|
| Questions per quiz | 5 (max 10) |
| Quiz duration | 3-5 minutes (max 7) |
| Lesson duration | 2-4 minutes (max 5) |
| World completion | 20-30 minutes across multiple sessions |
| Session length | 10-15 minutes recommended, 20 max |
| Break reminder | After 15 minutes, Leo suggests a break |

### Progress Visibility

| Screen | What Child Sees |
|--------|----------------|
| Quiz | Progress bar + "Q3 of 5" |
| World map | Path with completed/current/locked missions |
| Stars | Always visible in header counter |
| Session | No timer shown to child (break reminder only) |

---

## 10. ACCESSIBILITY (WCAG 2.1 AA)

### Contrast

| Element | Minimum Contrast |
|---------|-----------------|
| Body text | 4.5:1 |
| Large text (>24px) | 3:1 |
| Interactive elements | 3:1 against background |
| Focus indicators | 3:1 against adjacent colors |

### Touch Targets

| Element | Minimum Size |
|---------|-------------|
| Any tappable element | 44×44px (Apple HIG) |
| Primary answer buttons | 48×48px recommended |
| Exit button | 48×48px (safety-critical) |

### Reduced Motion

- Detect `prefers-reduced-motion` media query
- Replace bounce/shake with simple fade
- Keep timing 2× faster (shorter durations)
- Never remove feedback entirely — just simplify it

### Keyboard Navigation

| Key | Action |
|-----|--------|
| Tab | Move between options |
| Enter / Space | Select option |
| Arrow keys | Navigate options (alternative) |
| Escape | Exit / go back |

---

## 11. EMOTION TEST CHECKLIST

Before approving any screen, answer these questions:

| # | Question | If "Yes"... |
|---|----------|------------|
| 1 | 😊 Does this screen make a child smile? | ✅ Ship it |
| 2 | 😮 Is it exciting? | ✅ Ship it |
| 3 | 🤔 Is it confusing? | ❌ **Redesign** |
| 4 | 😢 Would a wrong answer make them feel bad? | ❌ **Redesign** |
| 5 | 👆 Is the next action obvious? | ✅ Ship it |
| 6 | 🧒 Can a 4-year-old figure out what to do without instructions? | ✅ Ship it |
| 7 | 👀 Can a parent glance at this and know what their child is learning? | ✅ Ship it |
| 8 | 🔇 Does it work with sound off? | ✅ Ship it |
| 9 | ⏱️ Does it load in under 1 second? | ✅ Ship it |
| 10 | 🎨 Does it look like it belongs in this app? | ✅ Ship it |

> **If you answer "yes" to confusion (#3) or sadness (#4), redesign that screen immediately. No exceptions.**

---

## 12. STRESS TEST CHECKLIST

Every renderer must survive these edge cases without breaking:

| Test | Expected Behavior |
|------|------------------|
| Very long question (100+ chars) | Text wraps, card grows, no overflow |
| Very long answer (50+ chars) | Text wraps, font shrinks, card grows |
| 20 answer options | Paginate or scroll, never render all at once |
| Huge image (3000×3000) | Scales to container, never overflows |
| Tiny image (40×40) | Centered, upscaled to minimum 80×80 |
| No image provided | Fallback placeholder emoji/icon |
| No audio available | Button disabled + tooltip "Audio coming soon" |
| Empty question text | Show "Loading..." or fallback |
| Slow network (3G) | Skeleton loader, Leo "Loading..." animation |
| Offline | Cache last state, show "You're offline — keep playing!" |
| Rapid tapping | Debounce — ignore taps during animations |
| Orientation change mid-quiz | Smooth transition, no state loss |

---

## 13. THE DESIGN SANDBOX (Planned Testing Hub)

A permanent testing environment at `/dev/sandbox` that lets you test:

```
Design Sandbox
├── 🎨 World Themes (all 8 themes, switchable)
├── 🔘 Buttons (all states, all sizes)
├── 🃏 Cards (answer cards, mission cards, world cards)
├── ✨ Animations (every animation in isolation)
├── 📊 Progress Bars (0%, 50%, 100%, error state)
├── 🏅 Badges & Rewards
├── ⏳ Loading States (skeleton, spinner, Leo loading)
├── ❌ Error States (network, empty, offline)
├── 📭 Empty States (no lessons, no quizzes, new world)
├── 🔊 Audio Controls (volume sliders, mute, narration)
├── 🎯 Question Types (all 6 Phase 1 renderers)
├── 🎉 Celebration (all 4 levels: mini, small, medium, large)
├── ♿ Accessibility (reduced motion, high contrast, keyboard nav)
└── 💪 Stress Tests (long text, huge images, rapid taps, edge cases)
```

This sandbox is the **source of truth** for visual QA. No production merge without sandbox approval.

---

## 14. IMPLEMENTATION PRIORITY

| Priority | Guideline | Status |
|----------|-----------|--------|
| P0 | Animation timing tokens (already in `tokens.css`) | ✅ Done |
| P0 | Wrong answer protocol (gentle, never harsh) | 🔨 In prototype |
| P0 | Idle behavior system (Leo hints) | 📋 Next milestone |
| P1 | Reduced motion support | 📋 After idle system |
| P1 | Stress test edge cases | 📋 Part of sandbox |
| P2 | Full Design Sandbox page | 📋 After quiz engine |
| P2 | Audio volume management system | 📋 After quiz engine |

---

## 15. SIGN-OFF CHECKLIST

Before any screen reaches production:

- [ ] Passes the **Emotion Test** (Section 11)
- [ ] Passes the **Stress Test** for its content type (Section 12)
- [ ] Complies with **Animation Timing** (Section 1)
- [ ] Complies with **Tap Economy** (Section 2) — max 3 taps
- [ ] Complies with **Cognitive Load** (Section 3) — word count OK
- [ ] Complies with **Visual Rules** (Section 4) — image sizes OK
- [ ] Complies with **Audio Rules** (Section 5) — no harsh sounds
- [ ] Has **Idle Behavior** defined (Section 6)
- [ ] Reward frequency is **appropriate** (Section 7)
- [ ] Wrong answer is **gentle and encouraging** (Section 8)
- [ ] Works at **375px, 768px, 1024px+** widths (Section 4)
- [ ] Works with **sound off** (Section 11, #8)
- [ ] Works with **keyboard only** (Section 10)

> **No checklist = no merge.**

---

*This document is a living standard. Update it as the app evolves and as we learn from real child testing.*