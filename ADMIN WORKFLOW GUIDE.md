# 📚 BZabc Admin Workflow Guide

> **Your roadmap for creating, publishing, and previewing lessons.**

---

## 🗂️ The Content Pipeline

```
Curriculum → Subjects → Topics → Lessons → Quizzes → Adventure World → Kids Play
```

### Quick Analogy
| Level | What It Is | Example |
|-------|-----------|---------|
| **Level** | Grade band | PP1, PP2, Grade 1 |
| **Subject** | Learning area | Mathematics, English |
| **Topic** | Sub-category | Counting Fun, Letter Sounds |
| **Lesson** | A teaching unit | "Counting 1 to 5 with Leo!" |
| **Quiz** | Game attached to lesson | "Counting Challenge!" |
| **Adventure World** | Themed map node | 🌲 Forest of Numbers |

---

## 🔄 Lesson Lifecycle (Workflow States)

Every lesson moves through these states:

```
draft → in_review → published → archived
           ↑              │
           └── rejected ──┘  (sends back to draft with reason)
```

| State | Icon | Meaning |
|-------|------|---------|
| `draft` | ⬜ | Being written. Not visible to kids. |
| `in_review` | 🔍 | Submitted for approval. Locked for editing. |
| `published` | ✅ | Live! Kids can play it. |
| `archived` | 📦 | Hidden from kids but kept for records. |

### How to Move Between States

1. **Draft → Review**: Open lesson → click **"📤 Submit for Review"**
2. **Review → Published**: Open lesson → add optional review notes → click **"✅ Approve & Publish"**
3. **Review → Draft (Reject)**: Open lesson → click **"❌ Reject"** → enter reason → submit
4. **Published → Archived**: Open lesson → click **"📦 Archive"**
5. **Archived → Published**: Open lesson → click **"♻️ Restore"**

> **💡 Tip**: The audit history on the right side of the lesson detail page shows every change.

---

## 📝 Creating a Lesson — Step by Step

### Step 1: Prerequisites
Before creating a lesson, ensure you have:
- ✅ A **Subject** (e.g., Mathematics)
- ✅ A **Topic** under that subject (e.g., Counting Fun)
- ✅ Optionally, media files uploaded (video, images, audio)

### Step 2: Create the Lesson
1. Go to **Lessons → New Lesson**
2. Fill in:
   - **Topic**: Select the parent topic
   - **Title**: Kid-friendly name (e.g., "Counting 1 to 5 with Leo!")
   - **Slug**: URL-safe version (auto-generated if left blank)
   - **Summary**: 1-2 sentences shown on mission intro
   - **Content**: HTML lesson content (shown on the lesson page)
   - **Content Type**: `text`, `video`, or `interactive`
3. **Media & Narration** (optional but recommended):
   - **Thumbnail Image**: Shows on cards and mission screens
   - **Lesson Video**: MP4 video played after intro narration
   - **Intro Narration**: Audio played before content (upload via Media)
   - **Summary Narration**: Audio played after content as recap
4. Set **Duration** (minutes) and **Sort Order**
5. Click **Save**

### Step 3: Create the Quiz
1. Go to **Quizzes → New Quiz**
2. Select the lesson you just created
3. Add a title and instructions
4. Add **at least 1 question** (recommend 3-5 for a full mission)
5. For each question:
   - Choose a **Quiz Type** (multiple-choice, count-objects, etc.)
   - Write the **prompt** (what the child sees/hears)
   - Set a **prompt image** (emoji or URL)
   - Add **audio text** in metadata for TTS fallback
   - Create **options** and mark the correct one
6. Set quiz status to **published**

### Step 4: Wire to Adventure World
1. Go to the Adventure World where this lesson belongs
2. Attach the lesson via the `world_lessons` pivot table
3. Set `sort_order` so it appears in the right sequence

### Step 5: Publish!
1. Go back to the lesson
2. Submit for review → Approve & Publish
3. **Preview as Child** (👁️ button) to verify it looks right

---

## 👁️ Previewing as a Child

The **"Preview as Child"** button on any lesson detail page opens the kid-facing view in a new tab. This lets you:

- ✅ See exactly what the child will see
- ✅ Test the quiz engine
- ✅ Verify audio/video plays
- ✅ Check images and layout

> **Note**: Preview mode does **not** save progress to the database.

---

## 🎬 Media & Narration

### Media Library (`/admin/media`)
Upload and manage:
- **Images** (PNG, JPG, SVG) — for thumbnails, question visuals
- **Videos** (MP4) — for lesson content
- **Audio** (MP3, WAV) — for narration sound files

### Narrations
Narrations are text-to-speech or recorded audio clips attached to lessons:
- **Intro Narration**: Played before the lesson (Leo greets the child)
- **Summary Narration**: Played after the lesson (Leo celebrates)

If `audio_path` is null, the kid view falls back to browser **Text-to-Speech (TTS)**.

---

## 🎯 Quiz Types Cheat Sheet

| Slug | Name | Best For |
|------|------|----------|
| `multiple-choice` | Multiple Choice | General knowledge, recall |
| `count-objects` | Count Objects | Math, counting practice |
| `sort-order` | Sort / Sequence | Algorithms, ordering |
| `memory-match` | Memory Match | Visual memory |
| `speak-repeat` | Speak & Repeat | Pronunciation, language |
| `tracing` | Tracing | Writing, fine motor |

---

## 🌟 Stars & Scoring

| Stars | Threshold | Meaning |
|-------|-----------|---------|
| ⭐⭐⭐ (3) | 100% | Perfect! |
| ⭐⭐ (2) | ≥80% | Great job |
| ⭐ (1) | ≥60% | Passed |
| 0 | <60% | Keep trying (no pass) |

- **Pass threshold**: 60%
- Stars are **saved per child** and never downgraded if they do worse on retry
- Net-new stars are added to the child's `total_stars` (global currency)

---

## 🚀 Quick Start: Run the Golden Lesson Seeder

The **Golden Lesson** is a fully-wired demo lesson with everything connected:

```bash
php artisan db:seed --class=ContentWiringSeeder
```

This creates:
- ✅ Topic: "Counting Fun"
- ✅ Lesson: "Counting 1 to 5 with Leo!" (published)
- ✅ Intro + Summary narrations (TTS fallback)
- ✅ Quiz: "Counting Challenge!" (3 questions, published)
- ✅ Wired to the first Adventure World

After seeding, visit the kid map to play it end-to-end.

---

## 📋 Content Creation Checklist

Before publishing a lesson, verify:

- [ ] Lesson has a clear title and summary
- [ ] Content is HTML-formatted and kid-friendly
- [ ] Thumbnail image attached (16:9 recommended)
- [ ] Intro narration created (text or audio file)
- [ ] Quiz created with ≥1 question
- [ ] Each question has a prompt, image, and audio text
- [ ] Quiz status is `published`
- [ ] Lesson wired to an Adventure World
- [ ] Previewed via "Preview as Child" button
- [ ] Lesson submitted and approved

---

## 🔧 Troubleshooting

| Problem | Solution |
|---------|----------|
| Lesson doesn't show on kid map | Check it's `published` AND wired to a world |
| Quiz doesn't appear | Check quiz `status = published` |
| No audio plays | Narration exists? Browser allows autoplay? TTS fallback active? |
| Stars don't save | Check child session is set (`active_child_id`) |
| Preview shows blank | Lesson must be published; quiz must have questions |

---

*Last updated: Phase 3.5 — July 2026*