# 🎮 Quiz Type Rendering Plan — How Each Type Works for Kids

> **Purpose:** Define exactly how each of the 13 question types will look and behave when a child plays the quiz. Use this as our discussion reference.

---

## 📊 Status Summary

| Code | Type | Kid Interaction | Status | Action Needed |
|------|------|----------------|--------|--------------|
| QT-01 | Multiple Choice | Tap answer | ✅ **Ready** | None |
| QT-02 | True/False | Tap Yes/No | ✅ **Ready** | None |
| QT-03 | Matching | Draw lines / drag pairs | ⚠️ **Needs discussion** | Define left/right sets |
| QT-04 | Sort / Category | Drag into buckets | ⚠️ **Needs discussion** | Define category buckets |
| QT-05 | Sequence | Drag into order | ✅ **Ready** | Confirm rendering |
| QT-06 | Listen & Choose | Hear audio → tap | ✅ **Ready** | None |
| QT-07 | Speak & Repeat | Say word aloud | ⚠️ **Needs discussion** | MVP scope? |
| QT-08 | Spell / Fill Blank | Drag letter tiles | ✅ **Ready** | None |
| QT-09 | Count Objects | Count → tap number | ✅ **Ready** | None |
| QT-10 | Complete Pattern | Tap next item | ✅ **Ready** | None |
| QT-11 | Memory Match | Flip card pairs | ⚠️ **Needs discussion** | Define pairs structure |
| QT-12 | Tracing | Finger trace | ⚠️ **Needs discussion** | SVG template or image? |
| QT-13 | Spot & Find | Tap on image | ⚠️ **Needs discussion** | How to validate taps? |

---

## ✅ READY TYPES (7) — No changes needed

### QT-01 — Multiple Choice (Tap Answer) 👆
**Kid sees:**
```
┌─────────────────────────────────┐
│  Which word starts with A?      │
│  [optional prompt image]         │
├──────────┬──────────┬──────────┤
│  Apple ✅ │  Ball    │  Cat     │
│          │          │          │
└──────────┴──────────┴──────────┘
```
- **Layout:** 2×2 grid (or 1×4 row) of big touchable cards
- **Options:** Text or Image (toggle in builder)
- **Feedback:** Card turns green (✅) or red (❌), celebration animation
- **Media spec:** Prompt image optional (landscape 4:3), option images square 1:1 @ 300×300px

---

### QT-02 — True / False ✅❌
**Kid sees:**
```
┌─────────────────────────────────┐
│  "Ant" starts with the letter A │
│  [optional image]                │
├────────────┬────────────────────┤
│            │                    │
│   ✅ TRUE  │    ❌ FALSE        │
│            │                    │
└────────────┴────────────────────┘
```
- **Layout:** Two huge side-by-side buttons
- **Options:** Auto-filled (True/False) — builder handles this
- **Media spec:** Optional prompt image, any ratio

---

### QT-05 — Drag & Drop Sequence 🔢
**Kid sees:**
```
┌─────────────────────────────────┐
│  Put these in order:            │
│                                 │
│  [3]  [1]  [2]    ← scrambled   │
│                                 │
│  ┌───┐ ┌───┐ ┌───┐              │
│  │ ? │ │ ? │ │ ? │  ← drop zone │
│  │ 1 │ │ 2 │ │ 3 │              │
│  └───┘ └───┘ └───┘              │
└─────────────────────────────────┘
```
- **Layout:** Scrambled cards on top, numbered drop slots below
- **match_key** = target position (1, 2, 3...)
- **Options:** Text or Image cards
- **Media spec:** Option cards square 1:1 @ 200×200px

---

### QT-06 — Listen & Choose 🔊
**Kid sees:**
```
┌─────────────────────────────────┐
│         🔊 Listen carefully     │
│      [ ▶ PLAY SOUND ]           │
│                                 │
├──────────┬──────────┬──────────┤
│   🔤 A   │   🔤 B   │   🔤 C   │
└──────────┴──────────┴──────────┘
```
- **Layout:** Big play button at top (auto-plays once on load), answer grid below
- **Audio** = prompt_audio_url (REQUIRED)
- **Options:** Text or Image
- **Media spec:** Audio MP3 (3-10 sec), option images square 1:1 @ 300×300px

---

### QT-08 — Spell / Fill the Blank ✏️
**Kid sees:**
```
┌─────────────────────────────────┐
│        _ P P L E                │
│       (drag the missing letter) │
│                                 │
│  ┌───┐ ┌───┐ ┌───┐ ┌───┐        │
│  │ A │ │ B │ │ E │ │ Z │        │
│  └───┘ └───┘ └───┘ └───┘        │
└─────────────────────────────────┘
```
- **Layout:** Word with blank at top, letter tiles below (draggable)
- **Options:** Letter tiles, one marked correct
- **Media spec:** None required

---

### QT-09 — Count the Objects 🔢
**Kid sees:**
```
┌─────────────────────────────────┐
│  How many apples?               │
│  [image of 3 apples]            │
│                                 │
├────┬────┬────┬────┤
│ 1  │ 2  │ 3✅│ 4  │
└────┴────┴────┴────┘
```
- **Layout:** Counting image at top (REQUIRED), number buttons below
- **Prompt image** = REQUIRED (4:3 or 16:9, 600×400px)
- **Options:** Numbers (text)

---

### QT-10 — Complete the Pattern 🔁
**Kid sees:**
```
┌─────────────────────────────────┐
│  What comes next?               │
│  🔴 🔵 🔴 🔵 🔴 ❓              │
│                                 │
├──────┬──────┬──────┤
│  🔴  │  🔵  │  🟢  │
└──────┴──────┴──────┘
```
- **Layout:** Pattern sequence at top (can be text or images), answer options below
- **Options:** Text or Image
- **Media spec:** Option images square 1:1 @ 150×150px

---

## ⚠️ TYPES NEEDING DISCUSSION (6)

### QT-03 — Matching 🔗
**Current problem:** All options are in one flat list with `match_key`. But matching needs **two visible sets** — left column and right column.

**Kid should see:**
```
┌─────────────┬─────────────┐
│  LEFT       │   RIGHT     │
│  (images)   │   (words)   │
├─────────────┼─────────────┤
│  🍎        ↔  "Apple"    │
│  🍌        ↔  "Banana"   │
│  ☀️        ↔  "Sun"      │
└─────────────┴─────────────┘
```

**Options to discuss:**
1. **Use match_key as-is** — pair items that share the same key. JS splits them into left/right automatically. ✅ Simple, no DB change.
2. **Add a `column` field** to options (`left`/`right`) — more explicit. Requires migration.
3. **Add a second options set** — more complex.

**My recommendation:** Option 1 — use match_key. In the builder, admin adds pairs: Item A (match_key: "apple") + Item B (match_key: "apple"). Frontend groups by match_key and renders two columns.

**Media spec:** Option images square 1:1 @ 200×200px

---

### QT-04 — Sort / Category 📦
**Current problem:** Items have `match_key` = category name, but the **category bucket labels** aren't defined anywhere.

**Kid should see:**
```
┌─────────────────────────────────┐
│  Drag each animal to its home   │
│                                 │
│  ┌──────┐ ┌──────┐ ┌──────┐    │
│  │ FARM │ │ ZOO  │ │ PET  │    │  ← buckets
│  │  🐄  │ │  🦁  │ │  🐶  │    │
│  │  🐔  │ │      │ │  🐱  │    │
│  └──────┘ └──────┘ └──────┘    │
│                                 │
│  [🐷] [🦒] [🐰]  ← to drag     │
└─────────────────────────────────┘
```

**Options to discuss:**
1. **Extract buckets from match_key values** — collect unique match_keys, use them as bucket labels. ✅ No DB change. But bucket names are just text.
2. **Add a `categories` JSON field** to the question — defines bucket labels, icons, colors.
3. **Add bucket items as a special option type** — an option flagged as "is_category".

**My recommendation:** Option 1 for MVP — extract unique match_keys as bucket names. Each item with that match_key starts outside, child drags into the right bucket.

---

### QT-07 — Speak & Repeat 🎤
**Current status:** Non-scoring type. Future AI speech recognition.

**Options to discuss:**
1. **MVP: Record + parent check** — child records, parent/teacher verifies. Simple.
2. **MVP: Play target + child repeats + self-check** — child taps "I said it!" button. Minimal.
3. **Future: Web Speech API** — browser recognizes speech, auto-scores. Requires HTTPS + mic permission.
4. **Future: AI backend** — send recording to API for scoring.

**My recommendation:** Option 2 for MVP — child hears the word, repeats it, taps a big "✓ I said it!" button. Mark as completed (not scored). Add speech recognition later.

---

### QT-11 — Memory Match 🃏
**Current problem:** Need pairs of cards. Current structure has flat options with match_key, but memory match needs exactly 2 cards per pair, all face-down.

**Kid should see:**
```
┌─────────────────────────────────┐
│  Find the matching pairs!       │
│                                 │
│  🂠  🂠  🂠  🂠                  │
│  🂠  🂠  🂠  🂠                  │
│                                 │
│  Flips: 0 | Pairs: 0/4          │
└─────────────────────────────────┘
```

**Options to discuss:**
1. **Use match_key pairs** — 2 options with same match_key = a pair. Works for image+image, text+text, text+image.
2. **Add `pair_id` field** — more explicit pairing.

**My recommendation:** Option 1 — 2 items with same match_key form a pair. Admin adds pairs in the builder. Frontend shuffles all cards face-down. Child flips 2 at a time; if match_key matches, they stay open.

**Media spec:** Card images square 1:1 @ 150×150px

---

### QT-12 — Tracing ✍️
**Current problem:** Need a traceable template. Options are empty for this type.

**Kid should see:**
```
┌─────────────────────────────────┐
│  Trace the letter A with your   │
│  finger!                        │
│                                 │
│    Ａ  (dashed outline)         │
│   ╱  ╲                          │
│  ╱    ╲                         │
│ ───────                         │
│                                 │
│  [canvas drawing area]          │
└─────────────────────────────────┘
```

**Options to discuss:**
1. **SVG path data** — store SVG path in `prompt_image_url` or a new field. Canvas renders it dashed. ✅ Scalable, clean.
2. **Dashed PNG image** — admin uploads a dashed outline image. Child traces over it on canvas.
3. **Pre-built letter library** — hardcode A-Z SVG paths, admin just picks a letter.

**My recommendation:** Option 2 for MVP — admin uploads a dashed outline image. HTML5 canvas overlays it with semi-transparent, child draws on top with their finger. Option 3 for future enhancement (auto-generate from letter picker).

---

### QT-13 — Spot & Find 🔍
**Current problem:** Child taps on a big image to find things. How do we know if they tapped the right spot?

**Kid should see:**
```
┌─────────────────────────────────┐
│  Find all the letter A's!       │
│  Found: 0 / 5                   │
│                                 │
│  ┌─────────────────────────┐    │
│  │                         │    │
│  │   [big scene image]     │    │
│  │   Tap on each A!        │    │
│  │                         │    │
│  └─────────────────────────┘    │
└─────────────────────────────────┘
```

**Options to discuss:**
1. **MVP: Tap count only** — child taps the image N times (where N = correct count). Simple but doesn't verify location.
2. **Coordinate-based** — admin defines clickable hotspots (x, y, radius) on the image. Requires a visual hotspot editor.
3. **AI-based** — future, use image recognition to detect what was tapped.

**My recommendation:** Option 2 for MVP with a simple hotspot editor — admin uploads image, clicks to add circular hotspots (stores x%, y%, radius%). Child taps within hotspot → counts as found. This gives precise control.

**Alternative for MVP:** Option 1 — simpler, child just taps N times. We can upgrade later.

---

## 🎨 Media Size Cheat Sheet (All Types)

| Usage | Aspect Ratio | Min Size | Format |
|-------|-------------|----------|--------|
| Multiple choice option | 1:1 (square) | 300×300px | JPG/PNG |
| Matching item | 1:1 (square) | 200×200px | JPG/PNG |
| Sort/Category item | 1:1 (square) | 200×200px | JPG/PNG |
| Sequence card | 1:1 (square) | 200×200px | JPG/PNG |
| Pattern item | 1:1 (square) | 150×150px | JPG/PNG |
| Memory card face | 1:1 (square) | 150×150px | JPG/PNG |
| Count objects prompt | 4:3 or 16:9 | 600×400px | JPG/PNG |
| Spot & Find scene | 16:9 (landscape) | 800×450px | JPG/PNG |
| Tracing template | 1:1 or portrait | 400×500px | PNG (transparent) |
| Prompt image (any type) | Any | 600×400px | JPG/PNG |
| Audio clip | — | — | MP3, 3-10 sec |

---

## 🤔 Questions for You

1. **QT-03 Matching:** Are you OK with using `match_key` to pair items (Option 1)? Or do you prefer a separate left/right column field?

2. **QT-04 Sort:** Can we auto-extract bucket names from match_key values? Or do you want explicit bucket definitions with icons/colors?

3. **QT-07 Speak:** For MVP, is the "I said it!" self-check button OK? Or do you want parent verification?

4. **QT-11 Memory:** Same question as matching — use match_key for pairs?

5. **QT-12 Tracing:** Do you prefer uploaded dashed images or built-in SVG letter paths?

6. **QT-13 Spot & Find:** Tap-count (simple) or hotspot editor (precise)?

7. **Priority:** Which types should we build the kid-facing rendering for FIRST?