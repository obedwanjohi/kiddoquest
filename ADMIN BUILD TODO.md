# ADMIN/CMS SIDE — MASTER BUILD TO-DO LIST

> **Product:** AI Children's Learning Platform (Ages 3–8)
> **Scope:** Admin/CMS Side (content production layer)
> **Tech Stack:** Laravel (Modular Monolith) + MySQL + Blade
> **Principle:** Teach via Movie → Test via Questions
> **Workflow:** One phase at a time. Fully tested. Each phase produces something visible and clickable.

---

## PHASE 0 — PLANNING & SETUP

- [ ] **0.1** Confirm tech stack: Laravel, MySQL, Blade (admin UI)
- [ ] **0.2** Lock the curriculum data model (tables, fields, relationships) on paper first
- [ ] **0.3** Lock the quiz type library (13 types — see Phase 6)
- [ ] **0.4** Define file/folder structure per domain module (TAS §11.3)
- [ ] **0.5** Initialize Git repo, `.gitignore`, README, project config

---

## PHASE 1 — PROJECT FOUNDATION (ADP Stage 1)

- [ ] **1.1** Create Laravel project in `c:/xampp/htdocs/kid`
- [ ] **1.2** Configure database connection (MySQL via XAMPP)
- [ ] **1.3** Set up modular folder structure (`app/Modules/Curriculum`, `CMS`, `Identity`, etc.)
- [ ] **1.4** Configure coding standards (PSR-12, naming conventions from TAS §11.4–§11.5)
- [ ] **1.5** Set up testing framework (PHPUnit)
- [ ] **1.6** Create base migrations for the curriculum schema
- [ ] **1.7** Seed initial reference data (levels, quiz types, statuses)
- [ ] **1.8** Verify: app boots, DB connects, tests run

---

## PHASE 2 — ADMIN AUTHENTICATION

- [ ] **2.1** `admins` table + migrations (separate from `parents`)
- [ ] **2.2** Admin registration/seed (first super-admin)
- [ ] **2.3** Admin login / logout / session management
- [ ] **2.4** Role-based access (Super Admin, Content Editor, Reviewer) — minimal V1
- [ ] **2.5** Password hashing, rate limiting, audit logging (TAS §8)
- [ ] **2.6** Admin auth tests

---

## PHASE 3 — ADMIN DASHBOARD SHELL

- [ ] **3.1** Admin layout (sidebar nav, top bar, breadcrumbs) — Blade templates
- [ ] **3.2** Dashboard home: content stats (lessons count, drafts, published, pending review)
- [ ] **3.3** Empty-state pages for each module (Curriculum, Lessons, Media, Users)
- [ ] **3.4** Responsive admin design (desktop-first but mobile-friendly)
- [ ] **3.5** Flash notifications + error handling patterns

---

## PHASE 4 — CURRICULUM MANAGER (the content tree)

- [ ] **4.1** `subjects` CRUD (English, Math, CRE, etc.)
- [ ] **4.2** `topics` CRUD (linked to subject)
- [ ] **4.3** `learning_objectives` CRUD (linked to topic — the measurable atom)
- [ ] **4.4** `levels` management (Pre-Primary Entry, PP1, PP2)
- [ ] **4.5** Tree view: Subject → Topic → Objectives (drill-down navigation)
- [ ] **4.6** Validation, business rules, tests

---

## PHASE 5 — LESSON BUILDER (the heart of the CMS)

- [ ] **5.1** `lessons` table + CRUD (title, description, objective link, level, difficulty, duration, status)
- [ ] **5.2** Lesson ↔ Objective linking (a lesson teaches one or more objectives)
- [ ] **5.3** Lesson workflow states: Draft → In Review → Published → Archived (TAS §2.15)
- [ ] **5.4** **Movie attachment** — link one teaching video per lesson
- [ ] **5.5** **Quiz builder** — attach a quiz bundle to the lesson:
  - [ ] 5.5a `quizzes` table (linked to lesson)
  - [ ] 5.5b `quiz_questions` table (type, prompt, media, points, order)
  - [ ] 5.5c `question_options` table (text/image/audio, is_correct, order)
  - [ ] 5.5d Implement all 13 quiz types as configurable templates
  - [ ] 5.5e Drag-to-reorder questions
  - [ ] 5.5f Per-question preview
- [ ] **5.6** Reinforcement assets linking (story/song/game — optional, linked to same objective)
- [ ] **5.7** Lesson validation rules + tests

---

## PHASE 6 — QUIZ TYPE ENGINE (the 13 types)

- [ ] **6.1** Define `quiz_types` reference table
- [ ] **6.2** Implement builder UI per type (Blade admin forms)
- [ ] **6.3** Implement validation/scoring rules per type (correct answer logic)
- [ ] **6.4** Seed all 13 types:

| ID | Quiz Type | Example (Letter A) |
|----|-----------|-------------------|
| QT-01 | Multiple Choice (tap answer) | "Tap the letter A" — 4 options |
| QT-02 | True / False (Yes/No) | "Is this the letter A?" |
| QT-03 | Matching | Match "A" to "Apple" |
| QT-04 | Drag & Drop — Sort | Drag all A's into the box |
| QT-05 | Drag & Drop — Sequence | Put letters A, B, C in order |
| QT-06 | Listen & Choose | 🔊 "Ah" → tap the right letter |
| QT-07 | Speak & Repeat | Child says "A" (future/AI) |
| QT-08 | Spell / Fill the Blank | "A__ple" → drag A |
| QT-09 | Count the Objects | "How many apples?" → tap number |
| QT-10 | Complete the Pattern | A B A B A __ → ? |
| QT-11 | Memory Match | Find pairs (letter + picture) |
| QT-12 | Tracing | Trace the letter A |
| QT-13 | Spot / Find | "Find all the A's in the picture" |

- [ ] **6.5** Tests for each type's scoring logic

---

## PHASE 7 — MEDIA LIBRARY

- [ ] **7.1** `media` table (movies, images, audio) with metadata
- [ ] **7.2** Upload endpoints (video, image, audio) with validation
- [ ] **7.3** Storage config (local for prototype → object storage later, TAS §10.6)
- [ ] **7.4** Media browser (filter by type, subject, tag)
- [ ] **7.5** Reuse media across multiple lessons (one-to-many)
- [ ] **7.6** Thumbnail generation, duration capture for videos

---

## PHASE 8 — CONTENT REVIEW & PUBLISH WORKFLOW

- [ ] **8.1** Submit lesson for review (Draft → In Review)
- [ ] **8.2** Reviewer approves/rejects (In Review → Published / back to Draft)
- [ ] **8.3** Versioning: published lessons are immutable; edits create a new version (TAS §4.6)
- [ ] **8.4** Archive/unpublish
- [ ] **8.5** Audit log of all content changes (TAS §8.11)

---

## PHASE 9 — CONTENT PREVIEW (the bridge to the Learning side)

- [ ] **9.1** "Preview as Child" mode — render the lesson exactly as a learner sees it
- [ ] **9.2** Play the movie
- [ ] **9.3** Step through the quiz interactively
- [ ] **9.4** See scoring result (no real progress saved — preview only)
- [ ] **9.5** This becomes the reusable renderer for the Learning side later

---

## PHASE 10 — ADMIN POLISH & METADATA

- [ ] **10.1** Search & filter across lessons, objectives, media
- [ ] **10.2** Bulk actions (publish, archive, assign level)
- [ ] **10.3** Dashboard analytics (content counts, coverage gaps)
- [ ] **10.4** Settings page (platform config, levels, quiz types)
- [ ] **10.5** Documentation: admin user guide, data dictionary

---

## PROGRESS TRACKER

| Phase | Status | Started | Completed |
|-------|--------|---------|-----------|
| Phase 0 — Planning & Setup | ⬜ Not Started | — | — |
| Phase 1 — Project Foundation | ⬜ Not Started | — | — |
| Phase 2 — Admin Authentication | ⬜ Not Started | — | — |
| Phase 3 — Dashboard Shell | ⬜ Not Started | — | — |
| Phase 4 — Curriculum Manager | ⬜ Not Started | — | — |
| Phase 5 — Lesson Builder | ⬜ Not Started | — | — |
| Phase 6 — Quiz Type Engine | ⬜ Not Started | — | — |
| Phase 7 — Media Library | ⬜ Not Started | — | — |
| Phase 8 — Review & Publish | ⬜ Not Started | — | — |
| Phase 9 — Content Preview | ⬜ Not Started | — | — |
| Phase 10 — Polish & Metadata | ⬜ Not Started | — | — |

---

## DECISIONS LOG

| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-07-08 | Admin/CMS side built before Learning side | Content producer must exist before consumer; CMS is the competitive moat |
| 2026-07-08 | Use Blade for admin UI | Founder experience with Blade; fast iteration for admin tooling |
| 2026-07-08 | Lesson model: Movie teaches + Quiz tests | Teach via movie, test via questions; feeds mastery engine |
| 2026-07-08 | 13 quiz types locked | Variety keeps assessment playful; all map to learning objectives |

---

*This file is our shared source of truth for the Admin build. Update checkboxes as we complete each task.*