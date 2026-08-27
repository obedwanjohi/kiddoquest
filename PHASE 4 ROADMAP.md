# Phase 4 Roadmap — Parent Reports, AI, Lesson Videos & Beta Testing

> **Status:** Planning Phase — No code changes yet.  
> **Last Updated:** 2026-07-13  
> **Goal:** Define everything we need before we start coding Phase 4.

---

## Table of Contents
1. [Current System Audit](#1-current-system-audit)
2. [Feature A: CBC Parent Reports](#2-feature-a-cbc-parent-reports)
3. [Feature B: AI Integration](#3-feature-b-ai-integration)
4. [Feature C: Lesson Video System](#4-feature-c-lesson-video-system)
5. [Feature D: Beta Testing Preparation](#5-feature-d-beta-testing-preparation)
6. [Database Migration Checklist](#6-database-migration-checklist)
7. [Dependency & Infrastructure Checklist](#7-dependency--infrastructure-checklist)
8. [Build Order & Priority](#8-build-order--priority)
9. [Open Questions](#9-open-questions)

---

## 1. Current System Audit

### What we already have ✅
| Component | Status | Notes |
|-----------|--------|-------|
| **Quiz Engine** | ✅ Complete | 12 question types, auto-advance, confetti, sound |
| **QuizAttempt** | ✅ Exists | Stores `score`, `total`, `stars`, `passed`, `answers` (JSON), `time_spent` |
| **ChildProgress** | ✅ Exists | Tracks `status`, `stars_earned`, `started_at`, `completed_at` per lesson |
| **ChildBadge** | ✅ Exists | Badge awarding system |
| **Lesson model** | ✅ Exists | Has `video_url`, `content_type`, `content`, `summary` |
| **Media model** | ✅ Exists | Admin media management with file uploads |
| **Guardian auth** | ✅ Exists | Login, register, dashboard, child management |
| **Content hierarchy** | ✅ Exists | `Subject → Topic → Lesson → Quiz → Question → Option` |
| **Kid hierarchy** | ✅ Exists | `AdventureWorld → WorldLesson` (kid-facing) |

### What's missing ❌
| Component | Impact |
|-----------|--------|
| **CBC competency framework** | No way to link questions to curriculum outcomes |
| **CBC outcome → question mapping** | Can't generate outcome-based reports |
| **AI service** | No AI integration at all |
| **Video upload/streaming** | `video_url` field exists but no upload or player |
| **Parent report views** | No report pages exist |
| **Analytics event tracking** | No detailed question-level analytics for AI |

---

## 2. Feature A: CBC Parent Reports

### A.1 — CBC Competency Framework

**What is CBC?**  
Kenya's Competency Based Curriculum (CBC) organizes learning into:
- **Learning Areas** (e.g., "Mathematics Activities", "Environmental Activities")
- **Strands** (e.g., "Numbers", "Measurement")
- **Sub-Strands** (e.g., "Counting 1-10", "Addition")
- **Specific Learning Outcomes (SLOs)** (e.g., "Count objects 1-10 accurately")
- **Core Competencies** (e.g., "Communication", "Critical Thinking")

**What we need to build:**

```
Database Tables Needed:
┌─────────────────────────────┐
│ cbc_learning_areas          │ ← Pre-PP1, PP1, PP2, Grade 1-3
│   id, name, level, code     │
├─────────────────────────────┤
│ cbc_strands                 │ ← Major topics within a learning area
│   id, learning_area_id,     │
│   name, code                │
├─────────────────────────────┤
│ cbc_sub_strands             │ ← Sub-topics
│   id, strand_id, name, code │
├─────────────────────────────┤
│ cbc_outcomes                │ ← Specific Learning Outcomes
│   id, sub_strand_id,        │
│   code, description,        │
│   core_competency           │
└─────────────────────────────┘
```

**Link to existing content:**
- `quiz_questions` table → add `cbc_outcome_id` column
- `lessons` table → add `cbc_sub_strand_id` column
- This lets us trace: *"Which outcome does this question test?"*

### A.2 — Report Data Aggregation

**What reports do parents see?**

1. **Strength Dashboard** — Top 3 areas where child performs well
2. **Weak Areas Alert** — Bottom 3 areas needing attention
3. **Progress Timeline** — Line chart of scores over time per subject
4. **CBC Outcome Grid** — Checkmarks/percentages per outcome
5. **Recommendations** — "Practice counting with household objects"

**Query logic needed:**
```php
// Per outcome: success rate across all attempts
$outcomeStats = QuizAttempt::where('child_id', $child->id)
    ->whereHas('quiz.questions', fn($q) => $q->where('cbc_outcome_id', $outcomeId))
    ->with('answers')  // JSON has per-question correctness
    ->get()
    // Aggregate: count correct vs total per outcome
```

**Views needed:**
| Route | View | Description |
|-------|------|-------------|
| `GET /guardian/reports` | `guardian.reports.index` | Overview dashboard |
| `GET /guardian/reports/{child}` | `guardian.reports.detail` | Per-child deep dive |
| `GET /guardian/reports/{child}/cbc` | `guardian.reports.cbc` | CBC outcome grid |
| `GET /guardian/reports/{child}/export` | (PDF) | Downloadable report |

### A.3 — Report Components Needed
- [ ] `CBC Learning Area Seeder` — all PP1–Grade 3 learning areas, strands, sub-strands
- [ ] `ReportController` — aggregates data, builds report objects
- [ ] `ReportDataService` — service class for reusable queries
- [ ] Chart library (Chart.js or ApexCharts) for visual graphs
- [ ] PDF export (laravel-dompdf or barryvdh/laravel-dompdf)

---

## 3. Feature B: AI Integration

### B.1 — AI Use Cases

| Use Case | Trigger | Output |
|----------|---------|--------|
| **Personalized reinforcement** | Child scores < 50% on a quiz | Generate 3 extra practice questions |
| **Smart recommendations** | Weekly / on-demand | "Focus on counting; try these lessons" |
| **Parent summaries** | End of week | Natural language paragraph: "This week, Mia improved in..." |
| **Adaptive difficulty** | Child answers 3 correct in a row | Bump difficulty level up |
| **Content generation** | Admin creates lesson | AI suggests quiz questions from lesson content |

### B.2 — AI Architecture Options

**Option 1: OpenAI API (Recommended)**
- ✅ Best quality (GPT-4o-mini is cheap and excellent)
- ✅ Fast integration (HTTP API)
- ❌ Requires API key + payment
- ❌ Data sent to external server

**Option 2: Local LLM (Ollama / LM Studio)**
- ✅ Privacy (data stays local)
- ✅ No per-request cost
- ❌ Requires powerful hardware
- ❌ Lower quality for kid-specific content

**Option 3: Hybrid (Rule-based + LLM)**
- ✅ Rule-based recommendations (free, deterministic)
- ✅ LLM only for summaries (minimal API calls)
- ✅ Graceful degradation if API is down

**Recommendation:** Start with **Option 3 (Hybrid)**.  
Rule-based recommendations for the core, LLM for natural language summaries.

### B.3 — Database Changes Needed
```
New Tables:
┌──────────────────────────────────┐
│ ai_recommendations               │
│   id, child_id, type,            │
│   content (JSON), generated_at,  │
│   read_at (nullable)             │
├──────────────────────────────────┤
│ ai_summaries                     │
│   id, child_id, period,          │
│   summary_text, created_at       │
├──────────────────────────────────┤
│ question_generations             │ ← AI-generated practice questions
│   id, child_id, outcome_id,      │
│   question_data (JSON),          │
│   used (boolean)                 │
└──────────────────────────────────┘

New Config:
│ .env → OPENAI_API_KEY=sk-...
│ config/ai.php → model, temperature, max_tokens
```

### B.4 — Service Classes Needed
- [ ] `app/Services/AI/AIClient.php` — abstract API wrapper
- [ ] `app/Services/AI/RecommendationEngine.php` — rule-based + AI
- [ ] `app/Services/AI/SummaryGenerator.php` — weekly summaries
- [ ] `app/Services/AI/QuestionGenerator.php` — practice question creation
- [ ] Scheduled job: `WeeklySummaryJob` (runs every Sunday)

---

## 4. Feature C: Lesson Video System

### C.1 — Video Upload & Storage

**What exists:** `Lesson.video_url` column (currently unused)

**What we need:**

| Component | Description |
|-----------|-------------|
| **Admin upload UI** | Drag-and-drop video uploader in lesson create/edit |
| **File validation** | mp4, webm, max 200MB |
| **Thumbnail generation** | Auto-capture frame at 0:01 for poster image |
| **Storage strategy** | Local (for beta) → Cloud CDN (for production) |

**Storage Options:**

| Option | Cost | Setup | Best For |
|--------|------|-------|----------|
| **Local storage** (`storage/app/videos`) | Free | Simple | Beta testing (few videos) |
| **Bunny Stream CDN** | $0.01/GB | Easy API | Production (cheap, global CDN) |
| **AWS S3 + CloudFront** | ~$0.09/GB | Complex | Enterprise scale |
| **YouTube (unlisted)** | Free | Manual | Zero budget |

**Recommendation:** **Local storage for beta**, migrate to **Bunny Stream** for production.

### C.2 — Kid Video Player

**Requirements:**
- [x] Big, colorful play button
- [ ] Auto-pause on tab switch (keep kids focused)
- [ ] No skip-forward (watch at least once) — optional
- [ ] Captions / subtitles support
- [ ] Human narration audio toggle (video can have no audio, narration separate)
- [ ] "Lesson complete" → unlock quiz

**Player features:**
```javascript
// Custom kid-friendly video player
- Large touch target play/pause
- Progress bar with star markers at key moments
- Mascot "Leo" appears on pause: "Ready to continue? 🦁"
- Celebration when video ends → auto-open quiz
```

### C.3 — Database Changes Needed
```
Lessons table additions:
├── video_path (local file or CDN URL)
├── video_thumbnail (auto-generated poster image)
├── video_duration (seconds — for display)
├── video_status: 'none' | 'processing' | 'ready' | 'failed'
├── transcript (JSON — for captions + AI)
└── narration_type: 'embedded' | 'separate_audio'

New Table (optional):
┌──────────────────────────────────┐
│ lesson_video_views               │ ← Track per-child watch progress
│   id, child_id, lesson_id,       │
│   watched_seconds, completed,    │
│   last_watched_at                │
└──────────────────────────────────┘
```

### C.4 — Files Needed
- [ ] `Migration: add_video_fields_to_lessons_table`
- [ ] `Migration: create_lesson_video_views_table`
- [ ] `app/Services/VideoUploadService.php` — handles file storage + thumbnails
- [ ] Update `Admin/LessonController` — video upload handling
- [ ] Update `resources/views/admin/lessons/create.blade.php` — video upload UI
- [ ] Update `resources/views/admin/lessons/edit.blade.php` — video upload UI
- [ ] New `resources/views/kids/lesson-player.blade.php` — kid video player view
- [ ] New `public/js/kid/video-player.js` — custom player logic
- [ ] `config/filesystems.php` — add `videos` disk

---

## 5. Feature D: Beta Testing Preparation

### D.1 — Pre-Beta Checklist

#### Content Readiness
- [ ] At least **5 complete lessons** with video + quiz per learning area
- [ ] All quizzes have **6+ questions** (variety of types)
- [ ] At least **2 adventure worlds** fully playable
- [ ] All lessons mapped to CBC outcomes
- [ ] Seed data covers PP1 + PP2 + Grade 1

#### Technical Readiness
- [ ] **Error logging** — Laravel telescope or Sentry
- [ ] **Cron jobs** — scheduler running for AI summaries
- [ ] **Storage permissions** — `storage/`, `bootstrap/cache/` writable
- [ ] **Upload limits** — `php.ini` max_filesize >= 200M, post_max_size >= 210M
- [ ] **HTTPS** — SSL certificate (Cloudflare tunnel already set up ✅)
- [ ] **Database backups** — daily automated backup
- [ ] **Queue worker** — for video processing + AI generation (Redis/database queue)

#### UX Readiness
- [ ] **Onboarding flow** — Guardian registers → adds child → child picks avatar → plays
- [ ] **Offline indicator** — show banner if network drops
- [ ] **Audio cues** — all buttons have sound feedback ✅ (already done)
- [ ] **Error pages** — friendly 404/500 pages with Leo mascot
- [ ] **Session timeout** — gentle warning before auto-logout (60 min)

### D.2 — Analytics & Feedback

**What to track during beta:**
| Event | Tool | Purpose |
|-------|------|---------|
| Page views, button clicks | Google Analytics / Plausible | Usage patterns |
| Quiz drop-off points | Custom event log | Where kids quit |
| Error logs | Sentry / Laravel logs | Bugs in production |
| Session recordings | Hotjar / PostHog | UX observation |
| Parent feedback | In-app feedback form | Feature requests |

**In-app feedback system:**
```
Database Table:
┌──────────────────────────────────┐
│ feedback                         │
│   id, guardian_id, child_id,     │
│   type (bug/suggestion/praise),  │
│   message, screenshot (nullable),│
│   status, created_at             │
└──────────────────────────────────┘
```

### D.3 — Beta Testing Plan

| Phase | Duration | Participants | Focus |
|-------|----------|-------------|-------|
| **Alpha** | 1 week | 2-3 trusted families | Critical bugs, crash testing |
| **Closed Beta** | 2 weeks | 10-15 families | UX feedback, content gaps |
| **Open Beta** | 2 weeks | 50+ families | Load testing, edge cases |
| **Launch** | — | Public | Marketing + onboarding |

### D.4 — Beta Readiness Audit Questions
1. Can a parent register and add a child without help?
2. Can a child complete a full lesson + quiz cycle independently?
3. Does the app work on a basic Android phone (not just iPhone)?
4. Does it work in landscape and portrait?
5. Does speech recognition work on the target devices?
6. Are videos loading within 3 seconds?
7. Does progress sync if a child switches devices?

---

## 6. Database Migration Checklist

All migrations to create (in build order):

### Phase 4A — CBC Framework
1. `create_cbc_learning_areas_table`
2. `create_cbc_strands_table`
3. `create_cbc_sub_strands_table`
4. `create_cbc_outcomes_table`
5. `add_cbc_outcome_id_to_quiz_questions_table`
6. `add_cbc_sub_strand_id_to_lessons_table`
7. `CBCSeeder` — all learning areas, strands, sub-strands, outcomes

### Phase 4B — AI Integration
8. `create_ai_recommendations_table`
9. `create_ai_summaries_table`
10. `create_question_generations_table`

### Phase 4C — Lesson Videos
11. `add_video_fields_to_lessons_table`
12. `create_lesson_video_views_table`

### Phase 4D — Beta Infrastructure
13. `create_feedback_table`
14. `create_analytics_events_table` (optional — for detailed tracking)

---

## 7. Dependency & Infrastructure Checklist

### Composer Packages to Install
```bash
# PDF export for reports
composer require barryvdh/laravel-dompdf

# FFmpeg for video thumbnail generation (system dependency, not composer)
# → Install FFmpeg on server: https://ffmpeg.org/download.html

# Queue/Redis (for async AI + video processing)
composer require predis/predis
# → Already likely in Laravel. Configure in .env: QUEUE_CONNECTION=redis

# HTTP client for AI API calls (Laravel has built-in Http facade — no extra package needed)
```

### NPM Packages to Install
```bash
# Charting for parent reports
npm install chart.js    # OR apexcharts

# Video player (optional — can build custom with HTML5 <video>)
npm install plyr        # OR video.js
```

### System Dependencies
| Tool | Purpose | Install |
|------|---------|---------|
| **FFmpeg** | Video thumbnails, transcoding | Windows: download exe; Linux: `apt install ffmpeg` |
| **Redis** | Queue + cache for AI jobs | Windows: WSL or Memurai; Linux: `apt install redis` |
| **OpenAI API Key** | AI features | Register at platform.openai.com |

### Environment Variables (`.env` additions)
```env
# AI
OPENAI_API_KEY=sk-...
AI_MODEL=gpt-4o-mini
AI_ENABLED=true

# Queue
QUEUE_CONNECTION=database   # or redis

# Video
VIDEO_DISK=local             # local | bunny | s3
BUNNY_API_KEY=
BUNNY_LIBRARY_ID=

# File upload limits
FILESYSTEM_DISK=local
```

---

## 8. Build Order & Priority

### Sprint 1: CBC Foundation (Week 1)
**Priority:** HIGH — everything depends on this

1. Create CBC migrations + models + seeder
2. Add CBC outcome selector to admin quiz question editor
3. Map existing seeded questions to CBC outcomes
4. Build `ReportDataService` — aggregate query logic
5. Build basic parent report view (table-based, no charts yet)

**Deliverable:** Parent can see a basic report showing which outcomes their child has practiced and success rates.

### Sprint 2: Lesson Video System (Week 2)
**Priority:** HIGH — needed for content completeness

1. Add video fields to lessons table
2. Build admin video upload (local storage)
3. Build kid video player view
4. Connect video completion → quiz unlock
5. Add lesson video view tracking

**Deliverable:** Admin can upload a video for a lesson; child watches it then takes the quiz.

### Sprint 3: Parent Reports Polish (Week 3)
**Priority:** MEDIUM — enhance the basic report

1. Add Chart.js visualizations
2. Strength/weakness algorithm
3. Progress timeline charts
4. PDF export
5. Recommendations (rule-based, no AI yet)

**Deliverable:** Beautiful visual report parent can download as PDF.

### Sprint 4: AI Integration (Week 4)
**Priority:** MEDIUM — adds intelligence layer

1. Set up `AIClient` service (OpenAI)
2. Build `RecommendationEngine` (hybrid rules + AI)
3. Build `SummaryGenerator` — weekly parent summaries
4. Build `QuestionGenerator` — adaptive practice questions
5. Scheduled jobs for weekly summaries
6. AI settings in admin panel (enable/disable, API key)

**Deliverable:** Parents receive weekly AI-generated summary; kids get personalized recommendations.

### Sprint 5: Beta Prep & Polish (Week 5)
**Priority:** Depends on all above

1. Feedback system
2. Error pages + Sentry integration
3. Onboarding flow testing
4. Content completeness audit
5. Load testing (10 concurrent users)
6. Documentation for content creators

**Deliverable:** Ready for closed beta with 10-15 families.

---

## 9. Open Questions

### For the Product Owner (You!)
1. **CBC Curriculum:** Do you have an official CBC outcomes document (PDF/Excel) we can use to seed the competency framework? Or should we research and compile it ourselves?

2. **AI Budget:** Are you comfortable using OpenAI API (~$0.15 per 1M tokens for GPT-4o-mini)? Monthly cost for 50 kids ≈ $5-10.

3. **Video Hosting Budget:** For beta, local storage is fine (free). For production, Bunny Stream (~$1-5/month for 50 kids). Is that acceptable?

4. **Target Devices:** What devices will beta testers primarily use? (Android tablets, iPads, parent phones?) This affects testing priority.

5. **Languages:** Should reports and AI summaries be in English only, or also Swahili? CBC is taught in both.

6. **Content Creators:** Who will record the lesson narrations? You mentioned "human-recorded" — is this you, or a team?

7. **Age Range:** The app currently seems PP1–Grade 1. Should we expand to Grade 2-3 for beta, or keep it narrow?

8. **Monetization:** Will beta be completely free? Any plans for subscription/pay-per-learning-area?

---

## Appendix: File Structure Preview

```
app/
├── Http/Controllers/
│   ├── Guardian/
│   │   ├── ReportController.php         # NEW — parent reports
│   │   └── FeedbackController.php       # NEW — beta feedback
│   └── Admin/
│       └── CBCController.php            # NEW — CBC management
├── Models/
│   ├── CBC/
│   │   ├── LearningArea.php             # NEW
│   │   ├── Strand.php                   # NEW
│   │   ├── SubStrand.php                # NEW
│   │   └── Outcome.php                  # NEW
│   ├── AIRecommendation.php             # NEW
│   ├── AISummary.php                    # NEW
│   ├── LessonVideoView.php              # NEW
│   └── Feedback.php                     # NEW
├── Services/
│   ├── ReportDataService.php            # NEW
│   ├── VideoUploadService.php           # NEW
│   └── AI/
│       ├── AIClient.php                 # NEW
│       ├── RecommendationEngine.php     # NEW
│       ├── SummaryGenerator.php         # NEW
│       └── QuestionGenerator.php        # NEW
└── Jobs/
    ├── GenerateWeeklySummaryJob.php     # NEW
    └── ProcessVideoUploadJob.php        # NEW

database/
├── migrations/
│   ├── create_cbc_learning_areas_table  # NEW
│   ├── create_cbc_strands_table         # NEW
│   ├── ... (see Section 6)
│   └── create_feedback_table            # NEW
└── seeders/
    ├── CBCSeeder.php                    # NEW
    └── VideoDemoSeeder.php              # NEW

resources/views/
├── guardian/
│   ├── reports/
│   │   ├── index.blade.php              # NEW
│   │   ├── detail.blade.php             # NEW
│   │   └── cbc.blade.php                # NEW
│   └── feedback/
│       └── create.blade.php             # NEW
├── kids/
│   └── lesson-player.blade.php          # NEW (video player)
└── admin/
    ├── cbc/
    │   └── index.blade.php              # NEW
    └── lessons/
        └── (update create/edit with video upload)

config/
├── ai.php                               # NEW
└── cbc.php                              # NEW
```

---

*This document is the master plan. Once you answer the open questions in Section 9, we'll start building Sprint 1 (CBC Foundation).*