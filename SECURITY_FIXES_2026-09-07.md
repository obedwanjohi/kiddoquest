# Security & operations fixes — 7 September 2026

These changes close the live risks found while studying the site for the Flutter app plan
(`../KIDDOQUEST_FLUTTER_APP_PLAN.md`, §1.11 and §7.7). Nothing here changes the learning
content or the kid-facing game flow; it changes who is allowed to do what.

## 1. What was wrong and what changed

| # | Risk (before) | Fix (after) | Files |
|---|---------------|-------------|-------|
| 1 | `docker-entrypoint.sh` ran `db:seed` on **every** container boot. `GuardianSeeder` began with `Child::delete()` / `Guardian::delete()`, and the content seeders delete and recreate missions, so a redeploy could wipe every family and cascade away child progress. | Seeding only runs when `SEED_ON_BOOT=true`. `GuardianSeeder` uses `firstOrCreate` and never deletes; in production it is skipped unless `SEED_DEMO_ACCOUNTS=true`. | `docker-entrypoint.sh`, `database/seeders/GuardianSeeder.php` |
| 2 | `AdminSeeder` committed three real admin e-mails with the shared password to git. | Seeds one admin from `ADMIN_EMAIL` / `ADMIN_PASSWORD`; in production it does nothing when they are unset (use `/admin/setup`). Locally it prints a random password. **The old password is still in git history: change it for all three accounts.** | `database/seeders/AdminSeeder.php` |
| 3 | `/admin/*` (the whole CMS) had **no middleware**. Every seed/purge/reset/debug tool (`/seed-*`, `/purge-*`, `/reset-*`, `/debug-*`, `/clear-cache`, `/convert-speak-webp`, `/build-all-full-scripts-now`) was public. `/dev/admin` logged anyone in as the first admin. | The admin group and all tool routes sit behind `admin.auth`. All `/dev/*` routes are only registered when `APP_ENV` is not `production`. Login, setup, logout and the M-Pesa webhook stay public. | `routes/web.php` |
| 4 | The Supabase **service-role JWT** (full read/write to the project) was hard-coded as a constructor default. | Read only from `config('services.supabase.*')` → `.env`. Uploads log an error and return `null` when the key is missing. **Rotate the key in the Supabase dashboard; the old one is in git history.** | `app/Services/SupabaseStorageService.php`, `config/services.php`, `.env.example` |
| 5 | Parent PIN stored in plaintext; `verifyPin` accepted `1234` for **every** account; no attempt limit; the PIN page printed the default PIN; most parent actions never checked the gate. | `parent_pin` is a `hashed` cast (bcrypt). `Guardian::verifyPin()` compares hashes (and transparently upgrades a legacy plaintext row). 5 wrong tries → 60 s lockout per guardian. New `parent.unlocked` middleware requires a PIN unlock in the last 30 minutes for the dashboard, PIN/screen-time/devotional updates, focus-mission assignment, AI coach and M-Pesa checkout. The starter-PIN hint is shown only to accounts that never changed it, and the dashboard nags them. | `app/Models/Guardian.php`, `app/Http/Controllers/Parent/ParentDashboardController.php`, `app/Http/Middleware/EnsureParentUnlocked.php`, `database/migrations/2026_09_07_100000_secure_guardian_pins_and_feature_flags.php`, views |
| 6 | `Guardian::first()` / `Child::all()` / `Child::first()` fallbacks in the parent zone, subscription, kid profile picker and songs hub meant an **anonymous visitor operated on the first family in the database**. `/kids/enter/{child}` auto-logged-in that child's guardian for anyone who guessed an id. | All parent routes require `guardian.auth`; every controller uses the signed-in guardian and `->children()` scoping. `/kids/profiles`, `/kids/dashboard`, `/kids/enter/{child}` require a signed-in parent and ownership. `activeChild()` helpers reject when no guardian or a foreign child is in session. | `KidController`, `KidMissionController`, `KidShopController`, `KidSongController`, `SubscriptionController`, `routes/web.php` |
| 7 | Mission results trusted the browser: `stars`, `score`, `total` and `time_spent` were saved as sent. | `total` is capped at the mission's `questions_per_session`, `score` at `total`, `stars` recomputed server-side with the engine's thresholds, `time_spent` capped at 2 h, answers truncated. | `app/Http/Controllers/Kid/KidMissionController.php` |
| 8 | Two conflicting price lists (landing page KES 200 / 1,800 vs checkout 499 / 1,200 / 3,999) and `EnsureActiveSubscription` was never applied. `simulatePayment` could mark any payment paid, `checkStatus` leaked any payment. | Single source `config/plans.php` (landing page, checkout cards, Alpine state, `MpesaService`, expiry days all read it). `subscription.active` is applied to every mission route but is **off** until `SUBSCRIPTION_ENFORCE=true`. First world per subject (by `sort_order`) plus `free_world_slugs` are free. `simulatePayment` returns 404 in production and both endpoints are scoped to the caller's own payments. | `config/plans.php`, `app/Http/Middleware/EnsureActiveSubscription.php`, `app/Services/MpesaService.php`, `SubscriptionController`, `welcome.blade.php`, `parent/subscription.blade.php` |
| 9 | `guardian.dashboard` was referenced from 5 places but never defined (500 on redirect). `enable_devotional` / `enable_songs_hub` columns were created **at runtime from a controller**. `MpesaService` read `env()` directly (returns null once config is cached). | Route added at `/parent/home`. Columns created by the migration. Credentials read through `config('services.mpesa.*')`. | `routes/web.php`, migration, `MpesaService` |

## 2. What the operator must do (in this order)

1. **Rotate secrets that were in git**: the Supabase service-role key (Settings → API → regenerate) and the three admin passwords (sign in at `/admin/login` and change them, or update them from tinker).
2. **Set environment variables on Render** (see the "KiddoQuest" block in `.env.example`): `SUPABASE_SERVICE_KEY`, `SUPABASE_PROJECT_REF`, `SUPABASE_STORAGE_BUCKET`, the `MPESA_*` values, `SEED_ON_BOOT=false`, `SUBSCRIPTION_ENFORCE=false` (flip to `true` when ready to charge).
3. **Deploy.** The entrypoint still runs `php artisan migrate --force`; the new migration hashes every existing PIN in place and adds the missing columns. It is safe to re-run.
4. **Prices**: `config/plans.php` now defaults to the values the public landing page advertised (**KES 200 / 30 days, KES 1,800 / 365 days**). If 499 / 1,200 / 3,999 was the intended list, change it there (or via `PLAN_MONTHLY_KES` / `PLAN_ANNUAL_KES`) and uncomment the `termly` example.
5. Existing parents keep their PIN (it is hashed by the migration, or on first successful use). New sign-ups get the starter PIN from `PARENT_DEFAULT_PIN` and are told to change it.

## 3. Behaviour changes users will notice

- The "Who is playing?" picker and the Parent Zone now require the parent to be signed in (`/guardian/login`). Previously any visitor landed on the first family in the database.
- The Parent Zone locks itself 30 minutes after the last PIN entry, and after 5 wrong PINs it pauses for a minute.
- The "(Default PIN for testing: 1234)" hint is gone for anyone who set their own PIN.
- The "Test Instant Unlock" button and all `/dev/*` QA routes are gone in production.

## 4. Not fixed here (needs separate work)

- **Content gap**: only ~110 of the 825 mission CSVs are seeded into playable worlds. That is an ingestion pipeline, not a bug fix — see the plan, §7.8 and Phase 1.
- The remaining seeders (`PlaygroupMathSeeder`, `CreMissionsSeeder`, `TracingSeeder`, …) still delete-and-recreate their missions. They no longer run on boot, and only an admin can trigger them, but running them on production still resets progress for those missions. Make them upsert by slug before using them on live data.
- Admin accounts have no 2FA and no rate limiting on `/admin/login`.
- Media served through `/storage/{path}` is still public by URL (fine for lesson media, but nothing private should go there).
