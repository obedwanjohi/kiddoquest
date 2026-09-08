# Implementation status — 7 September 2026

What exists in this repository against `KIDDOQUEST_FLUTTER_APP_PLAN.md`. Written
so the next person can tell built from planned without reading the code.

## Where things live

| | |
|---|---|
| Laravel app, admin CMS, parent web portal | this directory |
| App API | `routes/api.php`, `app/Http/Controllers/Api/V1/`, `app/Services/Learning/`, `app/Services/Content/` |
| Flutter app | `mobile/` |
| Shared scoring fixtures | `fixtures/scoring/` — run by both test suites |

The plan puts the Flutter app in a sibling `app/` directory. It is at
`mobile/` instead: the git repository root is the Laravel application, and
`app/` is already Laravel's own source directory.

## Built

### Backend

- **Token authentication** with Sanctum. A parent signs in; children never have
  accounts. Thirty-day tokens, a `sanctum` guard over the existing `guardians`
  provider.
- **Television sign-in**: the TV asks for a six-character code and polls, the
  phone approves it. Codes expire in ten minutes and can be claimed once.
- **Children**: list, create, update, delete, snapshot, screen time, focus
  mission. Ownership is checked by one middleware rather than in each controller.
- **Content packs.** `php artisan content:build-pack` exports an adventure world
  as versioned JSON plus a media manifest, content-addressed and checksummed.
  Publishing writes a new version and keeps the last three.
- **Catalogue and download** endpoints with ETags, and an entitlement check on
  paid worlds.
- **Sync.** One endpoint takes a batch of events, stores them idempotently,
  projects them into the existing `mission_attempts`, `child_progress` and
  `child_question_attempts` tables, and returns the authoritative snapshot.
- **Server-side scoring** for all thirteen question types, with the client's
  claim used only for instant feedback.
- **Daily statistics** (`child_daily_stats`), so the parent dashboard stops
  aggregating attempts on every page load.
- **A per-child projection job**, so the write path scales by adding workers.
  It runs inline until a real queue is configured.
- **Config endpoint** carrying prices, question caps, feature flags and the
  minimum app version, so those can change without an app release.
- **A Publish button in the admin CMS**, on the adventure worlds list, showing
  the version currently live on devices and publishing a new one in place.

Also fixed along the way:

- The **M-Pesa callback was a web route**, which means the CSRF middleware would
  have rejected every callback Safaricom ever sent. It is now an API route at
  the same URL.
- **`SampleMissionsSeeder` used the PostgreSQL-only `ilike` operator** and
  aborted `db:seed` on SQLite. It now picks the operator from the driver, so a
  full seed works locally.
- **`MasterQuestionTypesSeeder` had never run.** It wrote `display_title` to
  `adventure_worlds` and `pool_count` to `question_banks`; neither is a column.
  Both are fixed, and the QA lab mission it creates now exists.
- **SQLite still carried the foreign keys from before missions existed.** When
  quizzes and lessons became missions, the fixes dropped the old constraints by
  name on MySQL and Postgres; SQLite cannot drop a constraint, so
  `mission_attempts.mission_id` still pointed at `quizzes` *and* at `missions`,
  and `child_progress.mission_id` at `lessons` *and* `missions`. Every constraint
  has to hold, so recording a valid attempt against a mission with no matching
  quizzes row failed outright. Both tables are rebuilt by
  `2026_09_08_000200_fix_sqlite_mission_foreign_keys`.

### App

- Design system built from `public/css/kid/tokens.css`: colours, the type scale,
  the three-dimensional button edge, world palettes, motion timings.
- Form factors for phone, tablet and television, including the density scale,
  the overscan margin and D-pad focus with a branded focus ring.
- Parent sign-in and registration, child profiles, adding a child with the
  passport preview and the birthday-to-level rule.
- Content catalogue, checksum-verified pack download, media fetch, downloads
  manager showing megabytes before they are spent.
- Adventure map: worlds, the mission trail, sequential unlock, subject tabs, the
  offline banner and pending-sync count.
- Local database holding whole worlds, with the question draw ported from the
  server, including the seven-day exclusion filter.
- Mission briefing: one line of story from Leo, three stat chips, one button.
- Mission player for **all thirteen question types**, with the interaction rules
  the guidelines require: a wrong answer is grey and shakes, three tries then the
  answer is revealed, hint after the first miss. Memory match and spot-and-find
  are playable for the first time; the website had logic for one and an authoring
  tool for the other, but neither ever had a screen.
- A renderer lab world (`RendererLabSeeder`): one mission per question type, so
  every renderer can be opened and played by a person checking the app.
- Outbox and sync engine: durable, idempotent, backing off, never blocking play.
- Parent PIN gate that works offline against a digest kept from the last
  successful online check.
- Screen time: minutes counted on the device, reconciled against the server, and
  a goodnight screen instead of a nag when the day's limit is reached.
- The shop and badges. A purchase is optimistic and offline: coins move on the
  device and the event goes to the outbox, while the server stays the ledger and
  the only thing that can award a badge.
- Parent dashboard: minutes really played, the daily rhythm, accuracy per
  subject, what the child can do and what is next, recent missions with the
  questions they actually got wrong, and one thing to try away from the screen.
  The whole report is cached and shown, labelled as such, when there is no
  network.
- Parent settings: the daily limit per child, the devotional and songs toggles,
  and a PIN change that also updates the offline digest.
- Television sign-in. The TV shows a six-character code and polls; a phone that
  is already signed in approves it, and the TV claims a thirty-day token exactly
  once. No credential is ever typed with a remote control. There is a browser
  page at `/tv` for a family without the phone app, and the phone entry point
  sits behind the parent PIN because approving a device is worth a token.
- Narration. Every question is read out loud: the recorded voice where the pack
  carries one (202 of 538 questions do today), the device voice everywhere else.
  Adding recordings later changes nothing above the audio director.
- Mission intro videos, skippable from the first second. A missing file, a codec
  the device will not play, an unfinished download — every one of them lands the
  child in the mission rather than stuck on a black screen.
- Speak and repeat now listens where the device can hear, and says what it
  heard. It never decides the mark: recognition mishears small children
  constantly, and a four-year-old who said the word must not be told otherwise.
  A device with no microphone runs the same screen as practice.
- The daily devotional, read aloud, shown once a day rather than once a visit.
  The whole list travels to the device and today's is picked there, so it works
  at bedtime with no connection.
- The songs hub. A song plays from the device or it is marked coming soon; the
  website's YouTube embeds are deliberately not carried over, because they
  cannot work offline and send a four-year-old somewhere nobody is supervising.
- The sticker book, drawn from the missions a child has finished. Nothing to
  award and nothing to sync, so it is right the instant a mission ends.
- Treasure chests every fifth mission finished for the first time. The rule is
  arithmetic rather than random precisely so the device and the server reach the
  same answer without asking each other, which is what lets a chest open on a
  bus with no signal. Replaying a finished mission mints nothing.
- The parent coach, answering with the child's real numbers in front of it. It
  falls back to a data-driven reply when no LLM key is configured, so it always
  answers.
- Practice reminders, scheduled on the device. They arrive whether or not the
  family has data left, and nothing about them is sent anywhere.
- M-Pesa, from inside the app. Pick a plan, confirm the number, and wait while
  Safaricom prompts that phone for its PIN. The app never sees the PIN and has
  no way to say a payment succeeded: it polls, and the server is told by
  Daraja's callback. Every price on the screen comes from the server — the app
  holds no number of its own, not even a fallback.
- A web build, so the app can be tested in Chrome with no Android device. SQLite
  runs as WebAssembly there, and the file-backed media cache is swapped for the
  browser's own caching behind one conditional import.

### Tests

| Suite | What it covers |
|---|---|
| `php artisan scoring:verify` | 39 shared fixtures against the PHP scorer |
| `mobile/flutter test` | 127 tests: the same 39 fixtures against the Dart scorer, star thresholds, form factors, the wrong-answer-is-grey rule, the profile screen in three form factors, every renderer, screen time, the shop price list, the parent report parser, the television sign-in flow, the devotional and songs models, the speech matcher, and the payment states |
| `tests/Feature/Api/*` | Sync idempotency, server scoring over a forged claim, quarantine, cross-family refusal, pack publishing and versioning |
| `tests/smoke/api-smoke.sh` | 71 checks against a running server: the whole contract end to end, including a forged three-star claim scoring what the answers earned, the parent report, the whole television sign-in handshake, the devotional and songs payload, the coach, and the whole payment path including its throttle |

Two gaps worth stating plainly rather than glossing over:

- `tests/Feature/Api` has never been executed. It needs the dev dependencies
  (`composer install` without `--no-dev`), and that install was killed by the
  operating system for lack of memory. The scoring command, the Flutter suite
  and the smoke test all run without them and all pass.
- **The APK has never been built to completion here.** Four attempts were killed
  for lack of memory, at Gradle heap sizes from 8 G down to 1 G, on a machine
  with 8 GB of RAM and an editor and browser open. `flutter analyze` is clean and
  the Dart tests pass, so the code compiles; what is unproven is the Android
  packaging step. Run `flutter build apk --debug` with those applications closed,
  or let CI do it.

## Not built yet

| Thing | Phase |
|---|---|
| Licensed song recordings, so the hub has something to play offline | 2 — decision 5 |
| Rive mascots and illustrated worlds (emoji stand in) | 2 |
| Server-sent push (Firebase). The device-token endpoint and the local reminders are built; FCM needs a Firebase project and `google-services.json` | 3 |
| TV device lab: the app has never run on a real television | 4 |
| Redis, Octane, Horizon, Sentry, the read replica | 0/5, infrastructure |
| Postgres monthly partitions on `learning_events` | 5 |
| Load testing to 100k virtual users | 5 |
| The 825-CSV bulk ingestion | 2 — still the biggest schedule risk |

## Decisions taken while building

These were needed to make progress and are all reversible in one place.

1. **Practice counts as correct.** Tracing has always been practice-only on the
   web. Speak and repeat now behaves the same way when a device has no usable
   microphone, because failing every mission on a television with no mic is not
   a defensible outcome for a three-year-old. Skipping still scores zero.
2. **Sequential unlock is on.** The first unfinished mission in a world pulses,
   the rest wait. The website disabled this for testing. It is a flag:
   `FEATURE_SEQUENTIAL_UNLOCK`.
3. **Prices come from `config/plans.php`**: KES 200 a month and 1,800 a year,
   confirmed by the owner on 8 September 2026. The old 499 / 1,200 / 3,999
   checkout list is dead. Nothing else holds a price — not the landing page,
   not the app — so a change here needs no release.
4. **sqflite rather than Drift**, and hand-written models rather than freezed, so
   the project compiles with no code generation step. Both are behind interfaces.
5. **Question caps are enforced at publish time**: Play Group missions ship with
   six questions per session, PP1 and PP2 with eight, whatever the row in the
   database says.
6. **The app id is `ke.co.kiddoquest.app`**, changed from the generated
   `ke.co.kiddoquest.kiddoquest` before anything was published.

## Getting it running

```bash
# Backend
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
php artisan db:seed --class=PlaygroupMathSeeder --force
php artisan content:build-pack --all
php artisan serve

# Give a child a plausible history so the parent dashboard has something to show
php artisan kiddoquest:demo-play 1 --days=6 --missions=2 --accuracy=78

# App
cd mobile
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

To exercise the API the way the app does:

```bash
php artisan serve &
API_BASE=http://127.0.0.1:8000/api/v1 bash tests/smoke/api-smoke.sh
```
