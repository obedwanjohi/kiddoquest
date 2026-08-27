# 📋 Module 1 (Curriculum & Levels) — Progress Report

**Date:** 2026-07-15
**Status:** Build in progress (~30%)
**Rule for this module:** Audit first → Discuss findings → Build → Test → Approve → Lock

---

## ✅ Audit — complete

- **Curriculum:** no real entity existed (empty `app/Models/Curriculum/` folder + hardcoded "CBC" text in the sidebar and `resources/views/admin/curriculum/index.blade.php`). Level was effectively the top of the tree, so multiple curricula were not supported architecturally.
- **Levels:** model (`app/Models/Level.php`) + migration (`2026_07_14_160000_create_levels_table.php`) + `subjects.level_id` link existed and were migrated, but there was **no admin CRUD** — only a read-only hierarchy browser (`CurriculumController` → `admin.curriculum.*`).
- **Level fields present:** `name, slug, code, description, stage, min_age, max_age, color, icon, sort_order, status` + timestamps.
- **Levels ↔ Subjects:** linked via `subjects.level_id` (nullOnDelete); `Level::subjects()` hasMany + `Subject::level()` belongsTo exist.
- **Bug found:** `status` is inconsistent — model/migration default `published`, but the seeder writes `active` (and DB rows are `active`); sibling entity Subjects use `draft/published/archived`.

### Level CRUD state at audit time
| Capability | Status |
|---|---|
| List page (management) | ❌ (only read-only browser card grid) |
| Create page | ❌ |
| Edit page | ❌ |
| Delete / Archive | ❌ |
| Manage display order (`sort_order`) | ❌ (field exists, no UI) |
| Activate / deactivate | ❌ (no UI; status field conflicted) |
| Subject count per level | ✅ (via `withCount('subjects')`) |
| Seeded data | ✅ 5 levels (Play Group, Reception, Grade 1–3); Grade 1 has 5 subjects |

---

## ✅ Decisions locked (approved by user)

1. **Curriculum layer:** build it now — real `curricula` table + `Curriculum` model + `curriculum_id` FK on levels + admin CRUD (list/create/edit). Levels are created under a curriculum. This is the true multi-curriculum architecture (CBC now; Cambridge/Montessori later) and avoids a painful retrofit.
2. **Level lifecycle:** `draft / published / archived` + **soft delete**, matching the Subjects convention. This also fixes the `active` vs `published` inconsistency.

---

## ✅ Files written so far (4)

| File | What |
|---|---|
| `database/migrations/2026_07_15_000000_create_curricula_table.php` | **New** `curricula` table (name, slug, code, description, color, icon, sort_order, status, timestamps, soft deletes) |
| `database/migrations/2026_07_15_000100_add_curriculum_id_and_soft_deletes_to_levels_table.php` | **New** — adds `curriculum_id` FK + `deleted_at` to `levels`; seeds a default **CBC Curriculum**; back-fills all existing levels onto it; normalizes legacy `active` → `published` |
| `app/Models/Curriculum.php` | **New** model — soft deletes, auto-unique-slug, `levels()` relation, `is_published` accessor |
| `app/Models/Level.php` | **Updated** — added `SoftDeletes`, `curriculum()` relation, `curriculum_id` fillable, auto-unique-slug, status default now `draft` |

---

## ⚠️ IMPORTANT — code is currently AHEAD of the database

The two new migrations have **NOT been run yet**. But `Level.php` now uses `SoftDeletes` and expects `curriculum_id` / `deleted_at` columns that don't exist yet.

**Consequence:** until the migrations run, any page that queries Levels (the Curriculum browser, the dashboard) will throw a SQL error.

**First step next session — one command to resync:**

```bash
php artisan migrate
```

Only these two new migrations are pending; safe to run.

---

## ⏳ Not started yet

- [ ] Run `php artisan migrate` (resync DB with the two new migrations)
- [ ] `CurriculaController` + `LevelController` (CRUD, reorder, trash/restore, subject-count guard on delete)
- [ ] Blade views: `admin/curricula/{index,create,edit,show}` + `admin/levels/{index,create,edit,show}`
- [ ] Routes in `routes/web.php` (`admin.curricula.*`, `admin.levels.*`)
- [ ] Sidebar nav entries + `.badge-archived` CSS class (currently missing from `public/css/admin/app.css`)
- [ ] Fix `CurriculumSeeder` (`active` → `published`, create/link CBC curriculum)
- [ ] Migrate → seed → **test end-to-end**
- [ ] Write final deliverables report → user review → **Lock Module 1**

---

## Notes / open decisions for next session

- The existing read-only **Curriculum browser** (`CurriculumController` → `admin.curriculum.*`) will be left intact; new CRUD will live at `admin.curricula.*` / `admin.levels.*`. **Decide final sidebar labels** so "Curriculum" (browser) vs "Curricula" (manage) isn't confusing.
- **Delete strategy planned:** soft-delete for both entities; block **force-delete** when a curriculum still has levels / a level still has subjects (to avoid orphaning).
- Route-model binding kept on `id` for both Level and Curriculum (Subjects bind by slug — not adopting that here to avoid changing the existing browser's URLs).

---

## Scope guardrail

Module 1 covers **only** the top two layers: **Curriculum → Level**.
Do **not** work on Subjects, Sub-Strands, Lessons, Question Banks, Questions, Quizzes, Media, AI, or Reports until Module 1 is tested, approved, and locked.
