# 🎯 Quiz Type Guide — All 13 Question Types

This guide explains **every question type** available in the quiz builder, how it works for the child, and step-by-step instructions to create each one.

---

## 📋 Quick Reference Table

| Code | Name | Icon | How Child Interacts | Has Options? | Auto-Scored? |
|------|------|------|---------------------|--------------|--------------|
| QT-01 | Multiple Choice | 👆 | Taps one answer | ✅ | ✅ |
| QT-02 | True / False | ✅ | Taps Yes or No | ✅ | ✅ |
| QT-03 | Matching | 🔗 | Draws lines between pairs | ✅ (pairs) | ✅ |
| QT-04 | Drag & Sort | 📦 | Drags items into buckets | ✅ (items) | ✅ |
| QT-05 | Sequence | 🔢 | Drags items into order | ✅ | ✅ |
| QT-06 | Listen & Choose | 🔊 | Hears audio, taps answer | ✅ | ✅ |
| QT-07 | Speak & Repeat | 🎤 | Speaks aloud | ❌ | ⚡ AI/Future |
| QT-08 | Spell / Fill Blank | ✏️ | Drags letters to fill | ✅ | ✅ |
| QT-09 | Count Objects | 🔢 | Counts, taps number | ✅ | ✅ |
| QT-10 | Complete Pattern | 🔁 | Taps next item in pattern | ✅ | ✅ |
| QT-11 | Memory Match | 🃏 | Flips cards to find pairs | ✅ (pairs) | ✅ |
| QT-12 | Tracing | ✍️ | Traces with finger | ❌ | ⚡ AI/Future |
| QT-13 | Spot & Find | 🔍 | Taps all instances in image | ❌ (hotspots) | ✅ |

---

## 🛠️ How to Create Any Quiz (Universal Steps)

1. Go to **Quizzes** → **+ New Quiz**
2. A page loads with:
   - **Quiz Details** (top) — Lesson, Title, Pass %, Attempts, Status
   - **Questions Builder** (bottom) — First blank question card auto-appears
3. In each question card:
   - **Click a type card** to select the question type
   - **Type your question prompt** (the text/voice the child hears)
   - **Fill in the options** (the answer editor changes based on type)
   - Optional: add image, audio, hint, explanation
4. Click **➕ Add New Question** to add more
5. Click **💾 Create Quiz** at the bottom to save everything

---

## 📝 The 13 Question Types — Detailed Guide

---

### QT-01 👆 Multiple Choice (Tap Answer)

**What it is:** The classic quiz question. The child sees a question and taps the one correct answer.

**Example:** "Which letter is A?" → Options: 🅰️ B C D

**How scoring works:** Child must tap the option marked ✅ correct.

#### How to create:
1. Click the **👆 Multiple Choice** type card
2. Type your question: `Which letter is A?`
3. Two empty option rows appear automatically
4. In **Option 1**: type `A` and **tick the ✅ checkbox**
5. In **Option 2**: type `B` (leave unchecked)
6. Click **➕ Add Option** for more wrong answers (C, D, etc.)
7. 🎨 **Tip:** Use the 📝/🖼️ toggle to make image options instead of text

**Database fields:**
- `options.is_correct = true` on the right answer
- `options.content_type` = text or image

---

### QT-02 ✅ True / False (Yes/No)

**What it is:** A statement with only two options — True or False.

**Example:** "Is this the letter B?" (showing 🅱️) → ✅ True

**How scoring works:** Child taps True or False. One is marked correct.

#### How to create:
1. Click the **✅ True/False** type card
2. Type your statement: `Is this the letter B?`
3. Two options **auto-fill**: "True" and "False"
4. **Tick the ✅ checkbox** on the correct one
5. You can rename "True"/"False" to "Yes"/"No" if you prefer

---

### QT-03 🔗 Matching

**What it is:** The child draws lines (or taps) to connect left items with right items.

**Example:** Match 🍎 → "Apple", 🅰️ → "A"

**How scoring works:** Every pair shares a `match_key`. Child must connect all pairs correctly.

#### How to create:
1. Click the **🔗 Matching** type card
2. Type your question: `Match the pictures to words`
3. A **pair editor** appears with rows: **Left ↔ Right**
4. Each row has:
   - Left slot: type `🍎` (or click 🖼️ to use an image)
   - Right slot: type `Apple`
5. Click **➕ Add Pair** for more matches
6. Each pair auto-gets a hidden `match_key` (pair_1, pair_2, etc.)

**Behind the scenes:** Both items in a row store the same `match_key`, so the game knows they belong together.

---

### QT-04 📦 Drag & Drop — Sort

**What it is:** The child drags items into category "buckets."

**Example:** Sort animals — drag 🐄 into "Farm," 🦁 into "Wild"

**How scoring works:** Each item has a `match_key` matching its bucket. Child must place all items in the right bucket.

#### How to create:
1. Click the **📦 Sort** type card
2. Type your question: `Sort these animals`
3. A **two-zone editor** appears:
   - **Top zone (Buckets):** Define your categories
     - Each bucket has: color picker, name ("Farm"), emoji icon (🐄)
     - Click ➕ Add Bucket for more
   - **Bottom zone (Items):** Add things to sort
     - Type item name (e.g., "Cow")
     - Pick which bucket it belongs to from dropdown
     - Click ➕ Add Sort Item for more
4. The bucket definitions are saved as special metadata

**Database fields:**
- `metadata.buckets` = array of {key, name, icon, color}
- `options.match_key` = which bucket the item belongs to

---

### QT-05 🔢 Drag & Drop — Sequence

**What it is:** The child arranges items in the correct order.

**Example:** Put these in order: 1, 2, 3

**How scoring works:** Each option has a `match_key` = its position (1, 2, 3...). Child must drag them into that order.

#### How to create:
1. Click the **🔢 Sequence** type card
2. Type your question: `Put these numbers in order`
3. Three option rows appear, each with a **Position #** field
4. Fill in:
   - Position 1: type `First`
   - Position 2: type `Second`
   - Position 3: type `Third`
5. The position number IS the correct answer slot

---

### QT-06 🔊 Listen & Choose

**What it is:** Audio plays automatically, then the child taps the correct visual answer.

**Example:** 🔊 plays "Ah" sound → child taps letter A

**How scoring works:** Same as Multiple Choice — one option is marked ✅ correct.

#### How to create:
1. Click the **🔊 Listen & Choose** type card
2. Type your question: `Tap the letter you hear`
3. An **🔊 Audio URL** field appears (under the prompt)
4. Click **🔊 Browse** to pick audio from Media Library (or paste a URL)
5. Add answer options:
   - Option 1: `A` → tick ✅
   - Option 2: `B` (wrong)
6. The child will hear the audio, then see the options

---

### QT-07 🎤 Speak & Repeat

**What it is:** The child repeats a word or sound aloud. Uses AI speech recognition (future feature).

**Example:** "Say 'Apple'" → child speaks → AI checks pronunciation

**How scoring works:** Currently not auto-scored. Will use AI speech recognition in the future.

#### How to create:
1. Click the **🎤 Speak & Repeat** type card
2. Type your question: `Say the word: Apple`
3. An **🔊 Audio URL** field appears — add a sample pronunciation audio
4. **No options needed** — this is a voice-only interaction
5. You'll see a notice: "No answer options needed"

---

### QT-08 ✏️ Spell / Fill the Blank

**What it is:** The child fills in missing letters by dragging letter tiles.

**Example:** "A__ple" → child drags the letter "P"

**How scoring works:** Each letter tile is an option. The correct letter(s) are marked ✅.

#### How to create:
1. Click the **✏️ Spell/Fill** type card
2. Type your question: `Fill in the blank: A__ple`
3. Add letter options:
   - Option 1: `P` → tick ✅ (this is the missing letter)
   - Option 2: `Q` (wrong distractor)
   - Option 3: `Z` (wrong distractor)
4. The child will drag the correct letter into the blank

---

### QT-09 🔢 Count the Objects

**What it is:** The child counts objects in an image and taps the correct number.

**Example:** Image shows 3 apples → child taps "3"

**How scoring works:** The correct number option is marked ✅.

#### How to create:
1. Click the **🔢 Count Objects** type card
2. Type your question: `How many apples?`
3. An **🖼️ Image URL** field appears — add an image showing objects to count
4. Add number options:
   - Option 1: `2` (wrong)
   - Option 2: `3` → tick ✅ (correct count)
   - Option 3: `4` (wrong)

---

### QT-10 🔁 Complete the Pattern

**What it is:** The child identifies what comes next in a repeating pattern.

**Example:** 🔴 🔵 🔴 🔵 🔴 __ → child taps 🔵

**How scoring works:** One option is the correct next item, marked ✅.

#### How to create:
1. Click the **🔁 Complete Pattern** type card
2. Type your pattern: `🔴 🔵 🔴 🔵 🔴 ___?`
3. Add possible answers:
   - Option 1: `🔵` → tick ✅ (correct next)
   - Option 2: `🔴` (wrong)
   - Option 3: `🟢` (wrong)
4. Use 📝/🖼️ toggle for image-based patterns

---

### QT-11 🃏 Memory Match

**What it is:** Cards are face-down. Child flips two at a time to find matching pairs.

**Example:** Flip a card showing "A" and another showing 🍎 — they match!

**How scoring works:** Same as Matching — pairs share a `match_key`. Child wins when all pairs are found.

#### How to create:
1. Click the **🃏 Memory Match** type card
2. Type your question: `Find the matching pairs!`
3. A **pair editor** appears (same as QT-03 Matching):
   - Left card: type `A` (or use 🖼️ image)
   - Right card: type `Apple` (or use 🖼️ image)
4. Click ➕ Add Pair for more card pairs
5. At game time, all cards shuffle face-down

---

### QT-12 ✍️ Tracing

**What it is:** The child traces a letter or shape with their finger over a faded outline.

**Example:** Trace the letter A (dashed outline shown)

**How scoring works:** Currently not auto-scored. Future AI will analyze the traced path.

#### How to create:
1. Click the **✍️ Tracing** type card
2. Type your question: `Trace the letter A`
3. An **🖼️ Image URL** field appears — upload a **dashed/outline PNG** of the letter
4. A preview shows how it looks faded for the child
5. **No options needed** — this is a drawing interaction
6. 📌 **Important:** Use a transparent PNG with dashed/outline strokes

---

### QT-13 🔍 Spot & Find

**What it is:** The child looks at a busy scene image and taps all instances of a target.

**Example:** "Find all the letter A's" in a picture full of letters

**How scoring works:** You define "hotspots" (tap targets) on the image. Child must tap all of them.

#### How to create:
1. Click the **🔍 Spot & Find** type card
2. Type your question: `Find all the letter A's!`
3. An **🖼️ Image URL** field appears — upload a **16:9 landscape scene image**
4. Once the image loads, a **hotspot editor** appears below
5. **Click on the image** where each target is — green numbered dots appear
6. Click a dot to remove it if misplaced
7. The hotspot coordinates are saved automatically

**Behind the scenes:**
- `metadata.hotspots` = array of {x: %, y: %} coordinates
- Each hotspot is a correct tap target

---

## 🎨 Pro Tips

### Image Options vs Text Options
For QT-01, QT-06, QT-09, QT-10, QT-11 — click the **📝/🖼️ toggle** on any option to switch between typing text or using an image URL.

### Media Library
Click any **🖼️ Browse** or **🔊 Browse** button to open the Media Library popup. You can:
- Search by name
- Filter by subject
- Click an item to select it
- Click outside the modal to apply

### Metadata Storage
Complex types store extra data in the `metadata` JSON column:
- **QT-04 Sort:** `{"buckets": [{key, name, icon, color}, ...]}`
- **QT-13 Spot & Find:** `{"hotspots": [{x: 25.5, y: 40.2}, ...]}`

### Scoring Summary
- **Auto-scored types** (QT-01, 02, 03, 04, 05, 06, 08, 09, 10, 11, 13): The game checks `is_correct` flags or `match_key` pairs automatically
- **Future AI types** (QT-07 Speak, QT-12 Trace): Store media for later AI evaluation

---

## 🏗️ Architecture Notes (For Developers)

### Data Flow
```
Quiz Builder (JS)
  ├─ Questions array → quiz_questions table
  │   ├─ metadata JSON field (buckets, hotspots)
  │   └─ Options array → question_options table
  │       ├─ is_correct (boolean)
  │       ├─ match_key (string, for pairing/sorting)
  │       └─ content_type (text/image/audio)
  └─ Saved via single POST to /admin/quizzes
```

### Key Tables
| Table | Purpose |
|-------|---------|
| `quizzes` | Quiz settings (lesson, pass %, attempts) |
| `quiz_questions` | Each question (prompt, type, metadata) |
| `question_options` | Answer choices (text/image/audio, correct flag) |
| `quiz_types` | The 13 type definitions (QT-01 through QT-13) |

---

*This guide covers all 13 question types. For the full admin testing checklist, see the main testing guide.*