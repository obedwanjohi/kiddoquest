# Phase 3.5 — Content Architecture & Golden Lesson

> **Status:** Ready to build  
> **Created:** 2026-07-14  
> **Principle:** Build the structure, not the curriculum. Placeholder data now, real CBC names later.

---

## Table of Contents
1. [Objective](#1-objective)
2. [Database Changes](#2-database-changes)
3. [Model Changes](#3-model-changes)
4. [Admin Panel Restructure](#4-admin-panel-restructure)
5. [Question Bank Architecture](#5-question-bank-architecture)
6. [Build Order](#6-build-order)
7. [Golden Lesson Target](#7-golden-lesson-target)

---

## 1. Objective

Validate one complete learning loop end-to-end before scaling to CBC reports, AI, or mass content production.

```
Admin builds lesson → Child watches video → Child plays quiz → Stars earned → Progress saved → Parent sees result
```

**Rule:** Placeholder assets (AI video, AI audio) are fine. The *structure* must be production-ready.

---

## 2. Database Changes

### 2.1 New Tables

#### `levels` (REAL data — Play Group, PP1, PP2)
```sql
id
name           -- "Play Group", "PP1", "PP2"
slug           -- auto-generated
code           -- "PG", "PP1", "PP2"
description    -- nullable
sort_order     -- 0, 1, 2
status         -- draft | published
created_at, updated_at
```

#### `sub_strands` (placeholder data until CBC PDF)
```sql
id
strand_id      -- FK → topics (renamed conceptually to "strands")
name           -- "Counting 1–5" (placeholder: "Sub-Strand A")
slug           -- auto-generated
description    -- nullable
code           -- nullable (for CBC codes later)
sort_order
status         -- draft | published
created_at, updated_at
```

#### `narrations` (reusable, multilingual)
```sql
id
text           -- "How many apples do you see?"
audio_path     -- nullable (path to file in Media Library)
language       -- "en" now, "sw" later
voice          -- nullable (for future: "leo", "eli", etc.)
created_at, updated_at
```

#### `lesson_assets` (organizer — points to Media Library)
```sql
id
lesson_id      -- FK → lessons
asset_type     -- intro_audio | summary_audio | lesson_video | worksheet | background_music | celebration_audio | thumbnail
media_id       -- FK → media (nullable — can be empty during creation)
display_order
created_at, updated_at
```

#### `question_banks` (the 30-question pool)
```sql
id
lesson_id      -- FK → lessons
name           -- "Counting 1–5 — Main Pool"
description    -- nullable
pool_size      -- number of questions to pull per attempt (default: 5)
pass_threshold -- percent (default: 70)
max_attempts   -- default: 3
shuffle        -- boolean
status         -- draft | published
sort_order
created_at, updated_at
```

### 2.2 Modified Tables

#### `subjects` — add Level parent
```sql
ADD level_id    -- FK → levels (nullable for backward compat)
```

#### `lessons` — add curriculum + media fields
```sql
ADD sub_strand_id           -- FK → sub_strands (nullable during migration)
ADD video_path              -- nullable string
ADD video_duration_seconds  -- nullable integer
ADD intro_narration_id      -- FK → narrations (nullable)
ADD summary_narration_id    -- FK → narrations (nullable)
ADD cbc_outcome_code        -- nullable string (for future CBC mapping)
ADD estimated_minutes       -- default 8
```

#### `quiz_questions` — add narration + outcome links
```sql
ADD narration_id            -- FK → narrations (nullable)
ADD cbc_outcome_code        -- nullable string
ADD difficulty              -- easy | medium | hard (default: easy)
ADD question_bank_id        -- FK → question_banks (nullable — for pool membership)
```

### 2.3 Rename: `topics` → conceptually `strands`

**Decision:** We will NOT rename the table (avoids breaking existing code). Instead, we treat `topics` AS strands in the UI and documentation. The admin sidebar will say "Strands" and link to the topics controller.

If we want a clean rename later, we can do a migration: `rename_table topics → strands`. For now, the code stays as `Topic` model / `topics` table, but the admin UI calls them "Strands."

---

## 3. Model Changes

### New Models
| Model | File | Notes |
|-------|------|-------|
| `Level` | `app/Models/Level.php` | Top of hierarchy |
| `SubStrand` | `app/Models/SubStrand.php` | Between Strand (Topic) and Lesson |
| `Narration` | `app/Models/Narration.php` | Reusable audio/text |
| `LessonAsset` | `app/Models/LessonAsset.php` | Asset organizer |
| `QuestionBank` | `app/Models/QuestionBank.php` | Question pool container |

### Modified Models
| Model | Changes |
|-------|---------|
| `Subject` | Add `level_id`, `belongsTo(Level)` |
| `Topic` | No code change (conceptually = Strand) |
| `Lesson` | Add `sub_strand_id`, `video_path`, narration links, `hasMany(LessonAsset)`, `hasMany(QuestionBank)` |
| `QuizQuestion` | Add `narration_id`, `difficulty`, `question_bank_id` |
| `Media` | Add `created_by` provenance field (metadata only) |

---

## 4. Admin Panel Restructure

### 4.1 Current Sidebar (flat)
```
Dashboard | Subjects | Topics | Lessons | Quizzes | Media | Admins | Settings
```

### 4.2 New Sidebar (guided drill-down)
```
Dashboard
Curriculum
  ├── Levels          → /admin/levels
  ├── Subjects        → /admin/subjects (now filtered by Level)
  ├── Strands         → /admin/strands (topics, filtered by Subject)
  ├── Sub-Strands     → /admin/sub-strands (filtered by Strand)
  └── Lessons         → /admin/lessons (filtered by Sub-Strand)
Question Banks        → /admin/question-banks
Media Library         → /admin/media
Adventure Worlds      → /admin/worlds
Admin Users           → /admin/admins
Settings              → /admin/settings
```

### 4.3 Guided Flow
When admin clicks **Curriculum**, they see:
```
Level: Play Group | PP1 | PP2
```
Click PP1 → See Subjects in PP1 → Click Mathematics → See Strands → Click Numbers → See Sub-Strands → Click Counting 1–5 → See Lessons

### 4.4 Lesson Builder (enhanced)
The lesson create/edit page becomes a **Lesson Package builder** with tabs:
1. **Info** — title, summary, sub_strand, estimated_minutes
2. **Video** — upload video_path, set duration
3. **Narration** — pick/create intro_narration, summary_narration
4. **Assets** — manage lesson_assets (link media library items)
5. **Question Bank** — create bank, add questions, set pool_size
6. **Preview** — preview as child sees it

---

## 5. Question Bank Architecture

### How it works
```
Lesson (1) ──→ QuestionBank (1) ──→ QuizQuestion (30)
                                        │
                                   tagged by:
                                   • difficulty (easy/medium/hard)
                                   • CBC outcome (later)
                                   • question type
```

### Attempt Flow
```
Child starts quiz
  → System pulls 5 random questions from bank (pool_size)
  → Child answers
  → Score < threshold?
    → YES: Pull 5 DIFFERENT questions (reinforcement)
    → Repeat up to max_attempts
  → Score >= threshold?
    → YES: Lesson complete, award stars, save progress
```

### Implementation
- `QuestionBank` belongs to `Lesson`
- `QuizQuestion` gets `question_bank_id` (instead of/in addition to `quiz_id`)
- The kid quiz controller pulls from the bank, not fixed questions
- Each attempt logs which questions were shown (in `QuizAttempt.answers` JSON)

### Backward Compatibility
Existing quizzes (with `quiz_id` on questions) continue to work. New lessons use Question Banks. We can migrate old data later.

---

## 6. Build Order

### Step 1: Database Migrations (1-2 hours)
1. Create `levels` table + seed Play Group/PP1/PP2
2. Add `level_id` to `subjects`
3. Create `sub_strands` table
4. Add `sub_strand_id` + media fields to `lessons`
5. Create `narrations` table
6. Create `lesson_assets` table
7. Create `question_banks` table
8. Add `narration_id`, `difficulty`, `question_bank_id` to `quiz_questions`
9. Add `created_by` provenance to `media`

### Step 2: Models (30 min)
Create all new models with relationships.
Update existing models with new relationships.

### Step 3: Seeders (30 min)
- `LevelSeeder` — Play Group, PP1, PP2 (real)
- `CurriculumStructureSeeder` — dummy Subject/Strand/Sub-Strand/Lesson chain

### Step 4: Admin Controllers & Views (3-4 hours)
- `LevelController` + views (simple CRUD)
- `SubStrandController` + views (CRUD filtered by Strand)
- Enhance `LessonController` — add video, narration, assets tabs
- Enhance `Lesson` create/edit views
- Restructure sidebar navigation

### Step 5: Question Bank Builder (2-3 hours)
- `QuestionBankController` + views
- Bank create/edit with question management
- Pool size configuration
- Difficulty tagging on questions

### Step 6: Kid-Facing Quiz Update (1-2 hours)
- Update `KidQuizController` to pull from Question Bank (not fixed quiz)
- Random selection logic
- Reinforcement logic (different 5 on retry)

### Step 7: Golden Lesson Build (2-3 hours)
- Seed: Play Group → Mathematics → Numbers → Counting 1–5 → Count Objects Lesson
- Create Question Bank with 10-15 placeholder questions (we'll expand to 30)
- Link to Adventure World with mission story
- Test end-to-end

### Step 8: Phone Testing (ongoing)
- Start cloudflare tunnel
- Test on Android phone
- Fix mobile-specific issues

---

## 7. Golden Lesson Target

```
Level:      Play Group
Subject:    Mathematics
Strand:     Numbers
Sub-Strand: Counting 1–5
Lesson:     Count Objects 1–5
  ├── Video:      placeholder (AI-generated or simple slideshow)
  ├── Narration:  intro + summary (AI TTS)
  ├── Assets:     thumbnail, background music
  ├── Bank:       15+ questions (multiple types, easy difficulty)
  └── Mission:    Whispering Forest → "Help Eli Count Apples"
```

### Acceptance Criteria
- [ ] Admin can navigate: Level → Subject → Strand → Sub-Strand → Lesson
- [ ] Admin can create a lesson with video_path and narrations
- [ ] Admin can create a Question Bank with 15+ questions
- [ ] Child sees the lesson as a Mission in Whispering Forest
- [ ] Child watches video → plays quiz → gets random 5 questions
- [ ] Child fails → gets different 5 questions on retry
- [ ] Child passes → stars awarded → progress saved
- [ ] Works on Android phone via tunnel

---

*This plan is actionable. Each step has no dependency on CBC content — only on structure. We build now, fill names later.*