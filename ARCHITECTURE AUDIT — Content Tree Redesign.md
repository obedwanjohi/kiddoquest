# Architecture Audit — BZabc Content Tree Redesign

> **Purpose:** Compare the current database and admin architecture against the proposed Content Tree model, identify every gap, and outline the implementation plan.

---

## 1. Proposed Content Tree

```
Level
   ↓
Subject (many-to-many with Level)
   ↓
Topic
   ↓
Lesson (curriculum container ONLY)
   ↓
Mission (the actual learning experience)
   ↓
Question Bank (reusable pool)
   ↓
Questions → Options
```

---

## 2. Current Architecture — What Exists

### Entities That Exist and Match the Proposal

| Entity | Status | Notes |
|---|---|---|
| **Level** | ✅ Exists, correct | Play Group, PP1, PP2, Grade 1–3 |
| **Subject** | ✅ Exists, correct | Many-to-many with Level via pivot |
| **Topic** | ✅ Exists, correct | Belongs to Subject |
| **SubStrand** | ✅ Exists | Additional grouping layer (can be kept or merged into Topic) |
| **QuestionBank** | ✅ Exists | But currently tied to `Lesson` — needs to be freed |
| **QuizQuestion** | ✅ Exists | But can belong directly to Quiz OR to Bank — needs cleanup |
| **QuestionOption** | ✅ Exists, correct | Belongs to Question |

### Entities That Exist but Are WRONG

| Entity | Problem |
|---|---|
| **Lesson** | 🔴 **Overloaded.** Contains teaching content (video, narration, content), assessment (quizzes), AND curriculum metadata. Should be a thin container. |
| **Quiz** | 🔴 **Tied to Lesson.** A Quiz belongs directly to a Lesson. In the new model, the Quiz concept is replaced by Mission → Question Bank. |

### Entities That DON'T Exist

| Entity | Impact |
|---|---|
| **Mission** | 🔴 **Missing entirely.** This is the core learning unit (intro → video → questions → completion). |

---

## 3. Gap Analysis — Detailed

### GAP 1: No Mission Entity

**Current state:** A child opens a "Lesson" which contains a video, then the lesson has quizzes attached. There's no intermediate "mission" concept.

**What's needed:** A new `missions` table and `Mission` model that contains:
- `lesson_id` (belongs to Lesson)
- `title`
- `thumbnail_media_id`
- `intro_narration_text` (TTS)
- `intro_voice_profile`
- `video_url` / `video_media_id`
- `allow_replay` (boolean)
- `outro_narration_text` (TTS)
- `outro_voice_profile`
- `question_bank_id` (links to a bank)
- `pass_threshold_percent`
- `stars_reward`
- `estimated_minutes`
- `status`
- `sort_order`

**Migration required:** Yes — new `missions` table.

---

### GAP 2: Lesson Is Overloaded

**Current state:** The `lessons` table has 30+ columns including:
- Teaching content: `content`, `video_url`, `video_path`, `video_duration_seconds`
- Narration: `intro_narration_text`, `summary_narration_text`, `narration_voice_id`
- Media: `thumbnail_media_id`, `video_media_id`, `intro_narration_id`, `summary_narration_id`
- Relationships: `quizzes()`, `questionBanks()`

**What's needed:** Lesson should be stripped down to:
- `title`
- `slug`
- `description` / `summary`
- `thumbnail_media_id`
- `difficulty`
- `estimated_minutes`
- `status`
- `sort_order`
- `topic_id`

Teaching content, narration, and video move to **Mission**.

**Migration required:** Yes — move columns from `lessons` to `missions` (data migration).

---

### GAP 3: Quiz Is Tied to Lesson

**Current state:** `quizzes` table has `lesson_id`. Questions are created inside a Quiz.

**What's needed:** The Quiz entity becomes optional or is replaced. In the new model:
- Mission links to a **Question Bank**
- Questions belong ONLY to a Bank
- When a child plays a Mission, the system randomly picks N questions from the Bank

**Migration required:** Yes — but can be done gradually. Existing quizzes remain functional; new missions use banks.

---

### GAP 4: Questions Can Belong to Quiz OR Bank

**Current state:** `quiz_questions` table has both `quiz_id` and `question_bank_id`. Questions can be orphaned or duplicated.

**What's needed:** Questions should belong ONLY to a Question Bank. The `quiz_id` column becomes nullable/deprecated.

**Migration required:** Yes — but existing data is preserved. New questions go to banks.

---

### GAP 5: Question Bank Is Tied to Lesson

**Current state:** `question_banks` table has `lesson_id`.

**What's needed:** Banks should be reusable across missions. They can optionally link to a Topic (for organization) but should NOT be locked to a single lesson.

**Migration required:** Minor — make `lesson_id` nullable, add optional `topic_id`.

---

### GAP 6: No Mission-Based Kid Flow

**Current state:** Kid flow is: World → Map → Lesson (video + quiz).

**What's needed:** Kid flow becomes: World → Map → Lesson → Mission(s) → (intro → video → questions → stars).

**Views/controllers required:** New kid-facing mission views.

---

### GAP 7: Random Question Selection

**Current state:** All questions in a quiz are shown in order.

**What's needed:** A Mission should randomly select N questions from its linked Bank. This requires:
- A `questions_per_session` column on Mission
- A selection algorithm in the kid controller

---

## 4. Implementation Plan

### Phase 1: Create the Mission Entity (Admin Only)

**Step 1:** Create `missions` table migration
- All fields listed in GAP 1
- Foreign keys to `lessons` and `question_banks`

**Step 2:** Create `Mission` model
- Relationships: `belongsTo(Lesson)`, `belongsTo(QuestionBank)`, `belongsTo(Media)` for thumbnail/video

**Step 3:** Create `MissionController` (Admin)
- CRUD: index, create, store, show, edit, update, destroy
- Fields: title, thumbnail, intro narration text, intro voice, video, replay, outro narration text, outro voice, question bank selector, pass score, stars, duration, status, sort order

**Step 4:** Create Admin views for Missions
- `resources/views/admin/missions/index.blade.php`
- `resources/views/admin/missions/create.blade.php`
- `resources/views/admin/missions/edit.blade.php`
- `resources/views/admin/missions/show.blade.php`

**Step 5:** Add routes
- `admin/lessons/{lesson}/missions` (nested under lessons)

### Phase 2: Free Question Banks

**Step 1:** Migration to make `question_banks.lesson_id` nullable
**Step 2:** Add optional `topic_id` to `question_banks`
**Step 3:** Update QuestionBank admin views to work standalone

### Phase 3: Simplify Lesson (Gradual)

**Step 1:** Stop adding teaching content to new lessons
**Step 2:** Create a data migration to move existing lesson content (video, narration) into Missions
**Step 3:** Lesson admin views become simple (title, thumbnail, description, difficulty, status)

### Phase 4: Wire the Kid Flow

**Step 1:** Update kid routes: `/kids/lessons/{lesson}/missions/{mission}`
**Step 2:** Mission Intro view (Leo speaks intro narration)
**Step 3:** Video player view (teaching video)
**Step 4:** Random question selection from Bank
**Step 5:** Mission Complete view (stars, outro narration)

### Phase 5: Cleanup

**Step 1:** Mark `quizzes` table as deprecated for new content
**Step 2:** Migrate existing quiz questions to Question Banks
**Step 3:** Remove `quiz_id` from `quiz_questions` (after data migration)

---

## 5. What Can Be Reused (No Changes Needed)

| Component | Status |
|---|---|
| Level CRUD | ✅ Keep as-is |
| Subject CRUD | ✅ Keep as-is |
| Topic CRUD | ✅ Keep as-is |
| Question Bank CRUD | ✅ Keep (minor changes to free it from Lesson) |
| Question Builder (QT-01 through QT-10) | ✅ Keep as-is |
| Media Library | ✅ Keep as-is |
| Admin Auth | ✅ Keep as-is |
| Kid Auth | ✅ Keep as-is |

---

## 6. Risk Assessment

| Risk | Mitigation |
|---|---|
| Existing kid flow breaks | Phase 4 is additive — old lesson routes keep working until new mission routes are ready |
| Data loss during Lesson simplification | Phase 3 is gradual — data is copied to Missions, not deleted |
| Question Bank migration complexity | Banks already exist and work — we're just freeing them, not rebuilding |
| Random selection performance | Use `ORDER BY RAND() LIMIT N` or a shuffle in PHP (fine for <100 questions per bank) |

---

## 7. Recommended Order

1. **Create Mission entity** (admin only — no kid changes)
2. **Free Question Banks** from Lesson
3. **Build Mission admin CRUD** (content creators can start building)
4. **Wire kid flow** (Mission Intro → Video → Questions → Stars)
5. **Migrate existing content** (lessons → missions)
6. **Simplify Lesson admin** (remove video/narration from lesson editor)

This order ensures we never break the existing kid experience while building the new architecture.