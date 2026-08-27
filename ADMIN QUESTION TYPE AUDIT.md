# Module 6 — Admin Audit of Every Question Type (Content Creator Perspective)

> **Scope:** This audit is 100% focused on the **Admin Panel** question-creation capabilities. The kid-facing UI is already verified and is **not** audited here.
>
> **Goal:** Determine whether you, as the content creator, can build every question type entirely from the admin panel — no database edits, no code changes.

---

## How Questions Are Created Today

All questions are built inside the **Quiz Builder** (`admin/quizzes/create` or `admin/quizzes/{id}` → Add Question). The builder is a single-page JavaScript app that renders a different "editor" depending on the selected question type:

| Editor | Used by |
| --- | --- |
| **Generic option editor** | QT-01, QT-02, QT-05, QT-06, QT-08, QT-09, QT-10 |
| **Pair editor** | QT-03 (Matching), QT-11 (Memory Match) |
| **Bucket editor** | QT-04 (Sort) |
| **Hotspot editor** | QT-13 (Spot/Find) |
| **Tracing notice** | QT-12 (Tracing) |
| **No-options notice** | QT-07 (Speak & Repeat) |

---

## Universal Fields (Available on EVERY question type)

These fields are present in the builder regardless of type:

| Field | In Create Form? | In Edit Form (`show.blade.php`)? | Notes |
| --- | --- | --- | --- |
| Question Type | ✅ Required card selector | ❌ Locked after creation | **Cannot change type after save** |
| Prompt (text) | ✅ | ✅ | Required |
| Points | ✅ (1–10) | ✅ | |
| Hint | ✅ | ✅ | |
| Explanation | ✅ | ✅ | |
| Question Image (`prompt_image_url`) | ✅ Conditional¹ | ✅ | ¹Only shown for media-prompt types |
| Narration Audio (`prompt_audio_url`) | ✅ Conditional² | ✅ | ²Only shown for QT-06, QT-07 |
| Sort Order | ✅ Hidden auto | ✅ | |
| **Difficulty** (`difficulty`) | ❌ **MISSING in create** | ❌ **MISSING in edit** | Column exists, fillable, but no UI input |
| **CBC Outcome Code** (`cbc_outcome_code`) | ❌ **MISSING in create** | ❌ **MISSING in edit** | Column exists, fillable, but no UI input |
| **Narration Text** (morphTo `narrations` table) | ❌ **MISSING** | ❌ **MISSING** | `narration_id` exists on question but no UI to create/link a Narration row |
| **Voice Profile** (`voice_profile`) | ❌ **MISSING** | ❌ **MISSING** | On `narrations` table, no UI at all |

---

## Per-Type Status & Detail

### QT-01 — Multiple Choice (Tap Answer)

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets (content creator must prepare):**
- Optional: 1 question image
- Optional: 2–6 answer options (text or image)
- Optional: narration audio

**Fields available:** Prompt, image, audio (hidden unless re-enabled), points, hint, explanation, options with text/image toggle, correct-answer checkbox.

**Missing:** Difficulty selector, narration text/voice fields.

**Workflow:**
1. Add Question → select "Multiple Choice"
2. Type prompt
3. (Optional) pick image via Media Picker
4. Add Options (default 2) → toggle 📝/🖼️ → mark ✅ correct
5. Save

---

### QT-02 — True / False (Yes/No)

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** None (text-only). Optional narration audio.

**Fields available:** Auto-seeds True/False options with True marked correct. Same as QT-01 otherwise.

**Missing:** Difficulty selector.

---

### QT-03 — Matching

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 2–6 left/right pairs. Each side can be text or image.

**Fields available:** Pair editor with 📝/🖼️ toggle per side, `match_key` auto-set per row (`pair_1`, `pair_2`…).

**Missing:** Difficulty selector. No drag-reorder of pairs.

---

### QT-04 — Drag & Drop Sort (by Category)

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 2+ category buckets (name + emoji + color), 2+ sort items (text or image) assigned to a bucket.

**Fields available:** Bucket editor (color swatch, name, icon), item editor with per-item bucket dropdown. Buckets saved into `metadata.buckets`.

**Missing:** Difficulty selector. No reordering of items within a bucket.

---

### QT-05 — Drag & Drop Sequence (Order)

| Aspect | Status |
| --- | --- |
| **Status** | 🟡 Partially supported |
| **Admin Ready?** | 🟡 Needs UX improvement |

**Required Assets:** 3+ items in correct order.

**Fields available:** Generic editor with `match_key` = position number (1, 2, 3…). Seeds 3 options.

**Problem:** The `match_key` field is a **free-text "Position #" input**. There's no visual sequence indicator, no drag-to-reorder, and no validation that keys are unique/sequential. Content creator must manually type 1,2,3 and keep track. This is error-prone at scale.

**Missing:** Difficulty selector. No reorder UI.

---

### QT-06 — Listen & Choose

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 1 audio clip (the listening prompt), 2+ answer options.

**Fields available:** Audio URL row appears (🔊 Browse). Same option editor as QT-01.

**Missing:** Difficulty selector. No narration-text field (only raw audio URL).

---

### QT-07 — Speak & Repeat

| Aspect | Status |
| --- | --- |
| **Status** | 🟡 Partially supported |
| **Admin Ready?** | 🟡 Needs TTS text |

**Required Assets:** 1 audio clip of the word/phrase to repeat.

**Fields available:** Audio URL row appears. Shows "No options needed" notice.

**Problem:** There's **no field to type the word/phrase text** for text-to-speech fallback. The audio is the only input. If you want narration text + voice profile (for TTS), there's no UI — the `narrations` morphTo table is unused.

**Missing:** Difficulty selector, narration text, voice profile, expected pronunciation text.

---

### QT-08 — Spell / Fill the Blank

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** Letter/word tiles. Correct one marked.

**Fields available:** Generic editor, correct-answer checkbox shown.

**Missing:** Difficulty selector. No "blank position" indicator in the prompt.

---

### QT-09 — Count the Objects

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 1 image containing objects to count, number options.

**Fields available:** Image row auto-shows. Generic option editor for number answers.

**Missing:** Difficulty selector.

---

### QT-10 — Complete the Pattern

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 2+ option items; one marked correct as the "next" in pattern.

**Fields available:** Generic editor with image toggle.

**Missing:** Difficulty selector. No way to show the pattern sequence itself (e.g., 🔴🔴🔵🔴🔴🔵❓) — must go in the prompt text.

---

### QT-11 — Memory Match

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 2–6 card pairs (text or image each side).

**Fields available:** Reuses the Pair editor (same as QT-03).

**Missing:** Difficulty selector. No grid-size selector (2×2, 2×3, etc.) — inferred from pair count.

---

### QT-12 — Tracing

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 1 dashed/outline PNG image.

**Fields available:** Image row auto-shows, tracing notice rendered, faded preview shown.

**Missing:** Difficulty selector. No stroke-order data field.

---

### QT-13 — Spot / Find (Hotspot)

| Aspect | Status |
| --- | --- |
| **Status** | ✅ Fully supported |
| **Admin Ready?** | ✅ Yes |

**Required Assets:** 1 scene image (16:9 recommended), 1+ tap targets.

**Fields available:** Hotspot editor — click image to add dots, click dot to remove. Coordinates saved to `metadata.hotspots` as `{x%, y%}`.

**Missing:** Difficulty selector. No per-hotspot label/feedback text.

---

## Summary Table

| # | Question Type | Status | Assets Needed | Admin Ready? | Missing |
|---|---|---|---|---|---|
| QT-01 | Multiple Choice | ✅ | Optional image + options | ✅ | Difficulty, narration text |
| QT-02 | True / False | ✅ | None | ✅ | Difficulty |
| QT-03 | Matching | ✅ | Pairs (text/image) | ✅ | Difficulty, pair reorder |
| QT-04 | Drag Sort | ✅ | Buckets + items | ✅ | Difficulty, item reorder |
| QT-05 | Drag Sequence | 🟡 | Ordered items | 🟡 | Difficulty, reorder UI, match_key UX |
| QT-06 | Listen & Choose | ✅ | Audio + options | ✅ | Difficulty, narration text |
| QT-07 | Speak & Repeat | 🟡 | Audio | 🟡 | Difficulty, narration text, voice profile, expected text |
| QT-08 | Spell / Fill | ✅ | Letter tiles | ✅ | Difficulty |
| QT-09 | Count Objects | ✅ | Counting image + numbers | ✅ | Difficulty |
| QT-10 | Complete Pattern | ✅ | Pattern options | ✅ | Difficulty, pattern preview |
| QT-11 | Memory Match | ✅ | Card pairs | ✅ | Difficulty, grid selector |
| QT-12 | Tracing | ✅ | Outline PNG | ✅ | Difficulty, stroke order |
| QT-13 | Spot / Find | ✅ | Scene image + hotspots | ✅ | Difficulty, hotspot labels |

---

## Global Missing Admin Features (affect ALL types)

These are absent from the builder but the **database + models already support them**:

1. **Difficulty selector** — `difficulty` column exists on `quiz_questions`, is fillable, but **no dropdown** in create or edit forms. Defaults to NULL.
2. **CBC Outcome Code** — `cbc_outcome_code` column exists, is fillable, **no input** anywhere.
3. **Narration Text + Voice Profile** — The `narrations` morphTo table exists (`text`, `audio_path`, `voice_profile`, `language`) and `quiz_questions.narration_id` is fillable, but there is **zero UI** to create or link a Narration row. Currently narration is handled only via the raw `prompt_audio_url` string field.
4. **Cannot change question type after creation** — The type card selector only appears in the create builder. On the edit (`show.blade.php`) form, the type is fixed.
5. **No Duplicate Question button** — Cannot clone a question (with its options) to speed up creating similar items.
6. **No Duplicate Option button** — Cannot clone an option row.
7. **No Bulk Import** — No CSV/JSON import for mass question creation.
8. **No live Preview before saving** — The builder shows form fields, but no "what the child will see" preview until after save (and only via the separate Preview page).
9. **No drag-to-reorder** for questions within a quiz or options within a question — only manual `sort_order` numbers on edit.
10. **No Auto-save Draft** — If you navigate away from the create builder, all unsaved questions are lost.

---

## Ranking

### ✅ Ready for Production (no blocker)
- QT-01 Multiple Choice
- QT-02 True / False
- QT-03 Matching
- QT-04 Drag Sort
- QT-06 Listen & Choose
- QT-08 Spell / Fill
- QT-09 Count Objects
- QT-10 Complete Pattern
- QT-11 Memory Match
- QT-12 Tracing
- QT-13 Spot / Find

### 🟡 Needs Small Improvements
- **QT-05 Drag Sequence** — Add visual order numbers + reorder buttons; the raw `match_key` text box is too error-prone for hundreds of questions.
- **QT-07 Speak & Repeat** — Add narration text + voice-profile + expected-pronunciation fields so TTS can be used instead of manual audio uploads.

### ❌ Needs Major Work
- *(None at the type level.)* The **global gaps** below are the real blockers for content-creation throughput.

---

## Recommended Improvements (ranked by time-saving impact)

1. **Add Difficulty dropdown** to both create and edit forms. *(Trivial — field already exists.)*
2. **Add CBC Outcome Code input** to both forms. *(Trivial.)*
3. **Duplicate Question button** on `show.blade.php` — clones question + options in one click. *(High impact for creating variations.)*
4. **Duplicate Option button** inside the builder. *(Medium impact.)*
5. **Live Preview pane** inside the builder (toggle "Edit / Preview"). *(Medium — requires rendering component per type.)*
6. **Narration Text + Voice Profile fields** — Wire the `narrations` morphTo table into the question editor so you can type text and pick a voice instead of uploading audio manually. *(High impact for QT-06, QT-07, and any audio-narrated question.)*
7. **Drag-to-reorder** for sequence types (QT-05) and for questions within a quiz. *(Medium.)*
8. **Bulk Import (CSV/JSON)** for questions — accept prompt, type, options, correct flag, image URLs. *(High impact at scale.)*
9. **Allow changing question type after creation** (with a migration path for options). *(Low priority but reduces rework.)*
10. **Auto-save drafts** of the in-progress builder to `localStorage`. *(Low effort, prevents data loss.)*

---

*Audit complete. No code was changed. Awaiting approval to implement improvements type-by-type.*