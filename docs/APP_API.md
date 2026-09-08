# KiddoQuest app API (v1)

The interface between the Flutter app and the Laravel backend. It is the
implementation of §7.1 of `KIDDOQUEST_FLUTTER_APP_PLAN.md`.

Base URL: `/api/v1`. Everything is JSON. There is no session and no CSRF token:
a parent authenticates with a bearer token, and the child being played is named
per request by a header.

## Headers

| Header | When | Why |
|---|---|---|
| `Authorization: Bearer <token>` | Every signed-in request | Sanctum personal access token, 30 days |
| `X-Device-Id` | Every request | A random UUID the app generates once. Sync ordering, rate limiting and "last synced from the TV" all hang off it. It is not a hardware id |
| `X-Child-Id` | Anything about one child | Checked against ownership on every request |
| `X-App-Version`, `X-Platform`, `X-Device-Model` | Optional | Support and the forced-update check |

## Errors

One shape, so the app has one handler:

```json
{ "error": { "code": "child_not_found", "message": "That child does not belong to this account.", "fields": {} } }
```

## Endpoints

### Public

| Method | Path | Notes |
|---|---|---|
| GET | `/config` | Prices, question caps, feature flags, minimum app version. ETag, 5 minute cache |
| POST | `/auth/parent/register` | Returns a token, the guardian and the starter PIN |
| POST | `/auth/parent/login` | Returns a token, the guardian and their children |
| POST | `/auth/device/code` | A television asks for a six-character code |
| GET | `/auth/device/poll?code=` | The television polls until a phone approves it |
| POST | `/mpesa/callback` | Safaricom Daraja. Moved here from the web routes, where the CSRF middleware would have rejected every callback |

### Signed in

| Method | Path | Notes |
|---|---|---|
| GET | `/auth/me` | The guardian and their children |
| POST | `/auth/refresh` · `/auth/logout` · `/auth/logout-all` | Token lifecycle |
| POST | `/auth/device/approve` | The phone approves a television's code |
| GET/POST | `/children` | List and create |
| GET/PATCH/DELETE | `/children/{id}` | One child, ownership enforced |
| GET | `/children/{id}/snapshot` | The authoritative state, for a second device catching up |
| PATCH | `/children/{id}/screen-time` | Daily limit in minutes, 0 means no limit |
| PATCH | `/children/{id}/focus-mission` | Tomorrow's focus mission |
| GET | `/content/catalog?level=PG` | Packs for a level. ETag |
| GET | `/content/worlds` | Worlds without pack versions, for a map before any download |
| GET | `/content/extras` | The devotional list and the songs hub, in one small payload |
| GET | `/content/packs/{packId}/manifest` | The media list, and the entitlement check for paid worlds |
| GET | `/content/packs/{packId}/v{n}/download` | The pack document. Immutable, one year cache |
| POST | `/sync` | The only hot endpoint. See below |
| POST | `/parent/pin/verify` | Returns a 30-minute parent-scope token |
| PATCH | `/parent/pin` | Requires the account password as well |
| PATCH | `/parent/settings` | Devotional and songs toggles |
| GET | `/parent/children/{id}/report?range=7d` | The parent dashboard. `range` is `7d`, `30d` or `90d` |
| POST | `/parent/coach` | `child_id`, optional `question`. Always answers, LLM key or not |
| GET | `/subscription` | Entitlement, the plans on offer, recent payments |
| POST | `/subscription/stk-push` | `phone_number`, `plan_type`. Six a minute per parent |
| GET | `/subscription/status/{checkoutRequestId}` | Poll after an STK push |
| POST | `/devices/push-token` | FCM registration |


## Parent report

```
GET /api/v1/parent/children/41/report?range=7d
{ "child": { … }, "range_days": 7,
  "overview": { "minutes_today": 10, "minutes_in_range": 51, "missions_passed": 12,
                "questions_answered": 51, "accuracy_percent": 82, "days_played": 6,
                "daily": [ { "day": "2026-09-03", "minutes": 10, "missions": 2, "stars": 6 } ] },
  "progress": { "can_do_now": [ … ], "learning_next": [ … ],
                "subjects": [ { "name": "Mathematics Activities", "code": "MATH",
                                "answered": 14, "accuracy_percent": 79 } ] },
  "history": [ { "mission_id": 11, "title": "…", "score": 5, "total": 8, "stars": 2,
                 "mistakes": [ "How many school bags do you see?" ] } ],
  "support": { "has_struggle": true, "headline": "…", "activity": "…" },
  "badges": [ … ] }
```

Read entirely from the projections, so opening it costs a handful of indexed
queries rather than a walk through the child's events. Two numbers the website
could not produce are real here: `minutes_*` comes from `child_daily_stats`
rather than a hard-coded string, and every `mistakes` entry is a question the
child actually got wrong. Subject accuracy walks Mission → AdventureWorld →
Subject rather than matching world names against a word list, so renaming a
world cannot move a child's maths score into English.

The app caches the whole response and shows it, clearly labelled, when the
request cannot be made.

## Devotional and songs

```
GET /api/v1/content/extras
{ "devotional": { "enabled": true, "items": [ { "verse_text": "…", "verse_ref": "Psalm 118:24",
                                               "teaching": "…", "prayer": "…", "emoji": "☀️" } ] },
  "songs":      { "enabled": true, "items": [ { "id": 1, "title": "The Alphabet Song",
                                                "category": "Phonics & ABCs", "emoji": "🔤",
                                                "audio": null, "link": "https://…" } ] } }
```

The whole devotional list travels rather than today's, and the app picks today
by the same day-of-year rule the website uses. A devotional is opened at
bedtime and a song in a car — the two moments a family is least likely to have
a working connection — so neither may depend on a request succeeding.

A song's `audio` is a pack media key and is null until KiddoQuest licenses its
own recordings (decision 5 in the plan). The app plays a song with `audio` and
shows the rest as coming soon; it deliberately does not follow `link` out to
YouTube, which cannot work offline and sends a four-year-old somewhere nobody
is supervising.

## Paying

```
POST /api/v1/subscription/stk-push
{ "phone_number": "0712345678", "plan_type": "monthly" }
→ { "checkout_request_id": "ws_CO_…", "amount": 200, "phone": "254712345678",
    "plan_type": "monthly", "poll_seconds": 3, "message": "…" }

GET /api/v1/subscription/status/ws_CO_…
→ { "status": "pending" | "completed" | "failed", "paid": false, "receipt": null }
```

The app cannot buy itself a subscription. It asks for a prompt and then polls;
the money is confirmed only by Daraja's callback to
`POST /api/v1/mpesa/callback`, and nothing the device sends can set a payment to
completed. `not_found` and `failed` are kept distinct on purpose — a parent
whose payment we cannot find must not be told their money is gone.

The STK push is capped at six a minute per parent, in the controller rather than
only in a route throttle: the push makes somebody's phone ask for their M-Pesa
PIN, so an account that could fire it freely is an account that could harass a
stranger. The route throttle above it is a loose backstop, set well clear of the
six so the specific limit is the one a client actually sees.

Prices come from `config/plans.php` — KES 200 a month and 1,800 a year — and
travel through `/config` and this endpoint, both of which name a plan with
`key`. The app holds no price of its own, so changing one needs no release.

## Sync

```
POST /api/v1/sync
{ "events": [
    { "id": "<uuid>", "seq": 4820, "type": "mission_completed",
      "client_ts": "2026-09-07T10:12:33+03:00",
      "payload": { "mission_id": 41, "score": 5, "total": 6, "stars": 2,
                   "time_spent": 271, "answers": [ … ] } } ] }
```

```
200 OK
{ "accepted": ["<uuid>"], "rejected": [], "cursor": "2026-09-07T07:12:34Z#8830",
  "snapshot": { "child": {…}, "progress": […], "entitlement": {…}, "content_versions": {…} } }
```

Four properties the app relies on:

- **The device owns the id.** Sending the same event twice is a no-op and is
  still reported as accepted, so an outbox can retry for as long as it needs to.
- **The server scores.** `score` and `stars` in the payload are a claim. The
  server recomputes both from `answers` against the question bank and stores its
  own result. A forged three-star claim earns what the answers earned.
- **Nothing is discarded.** An event with a wild clock, an unknown type or an
  unknown mission is stored with `status = quarantined` and a reason, and
  reported in `rejected`. The device stops retrying it; a person can still look
  at it.
- **Stars never go down.** Progress keeps the best attempt, so replaying an
  outbox or playing a mission badly a second time cannot take stars away.

Projection runs in `ProjectLearningEventsJob`, one job per child. That is the
whole scaling story for the write path: two children never touch the same rows,
so throughput grows by adding workers. With `QUEUE_CONNECTION=sync` it runs
inline and the snapshot in the response is already current; with a real queue it
runs on a worker and the snapshot may lag by a second.

Screen time is the sum of time inside missions (`mission_completed`,
`mission_abandoned`) and time outside them (`session_heartbeat`), so a client
must not send heartbeats while a mission is running. The app sends none today:
mission time is the whole of it.

Event types: `session_started`, `session_heartbeat`, `session_ended`,
`mission_started`, `question_answered`, `mission_completed`, `mission_abandoned`,
`video_watched`, `shop_purchased`, `shop_equipped`, `devotional_viewed`,
`songs_opened`, `badge_claimed`.

## Content packs

A pack is one adventure world at one version: the whole question bank plus a
media list. The app draws each session locally, applying the same balanced-bucket
draw and seven-day exclusion filter the website uses, which is what makes a
retest bring fresh questions with no network.

```bash
php artisan content:build-pack --all          # publish every world
php artisan content:build-pack whispering-forest
php artisan content:build-pack --level=PG -v  # and list unresolved media
```

Publishing writes a new version rather than overwriting, and keeps the last
three, so a device that is half way through a mission is never left pointing at
a file that has gone.

Where packs are written is `config/kiddoquest.php`: set `CONTENT_DISK` to an S3
or R2 disk and `CONTENT_CDN_URL` to the CDN in front of it, and no application
code changes.

## Scoring

`App\Services\Learning\MissionScoringService` and
`mobile/lib/core/scoring/question_scorer.dart` implement the same rules and are
both run against `fixtures/scoring/*.json`:

```bash
php artisan scoring:verify      # the server
cd mobile && flutter test       # the app
```

Two rules worth stating because they differ from the website:

- **Practice counts.** Tracing has always been practice-only on the web: doing
  it is passing it. Speak and repeat now works the same way when a device has no
  usable microphone, because a three-year-old cannot be told to buy a better
  phone. Only skipping scores zero.
- **A question counts once.** However many times it appears in `answers`, the
  first verdict stands, and answers beyond the mission's session cap are ignored.
