# KiddoQuest app

The Flutter client for KiddoQuest: one codebase for Android phones, tablets and
Android TV, built to keep working when the network does not.

It is the app half of `KIDDOQUEST_FLUTTER_APP_PLAN.md`. The Laravel app in the
directory above is the content studio, the parent web portal and the API.

## Running it

```bash
cd mobile
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

`10.0.2.2` is how an Android emulator reaches the machine it runs on. For a real
phone on the same wifi, use your computer's LAN address; for a deployed backend,
use `https://www.kiddoquest.co.ke/api/v1`. Start the API with `php artisan serve`
in the directory above.

Set `APP_URL` in the backend's `.env` to the same host. Media URLs inside a
content pack are built from it, so a pack published while `APP_URL` says
`localhost` will point every picture at the phone itself.

Before a device sees any worlds, publish at least one:

```bash
cd ..
php artisan content:build-pack --all
```

## What is built

| Area | State |
|---|---|
| Design system (tokens, theme, buttons, answer cards, mascot, counters, world cards, focus ring) | Done, from `public/css/kid/tokens.css` |
| Form factors: phone, tablet, television | Done, including D-pad focus and overscan |
| Parent sign-in and registration | Done |
| Child profiles, add a child | Done |
| Content catalogue, pack download with checksum, downloads manager | Done |
| Adventure map with worlds, mission trail, sequential unlock | Done |
| Local database, question draw with the seven-day exclusion filter | Done |
| Mission player: all thirteen question types | Done. Speech recognition is not wired up, so speak and repeat runs as practice, which the shared scorer counts |
| Outbox, sync engine, server-authoritative snapshot merge | Done |
| Parent PIN gate, offline-capable | Done |
| Parent dashboard, M-Pesa, notifications | Phase 3 |
| Narration audio, Rive mascots, songs, devotional | Phase 2 |

## How it is put together

```
lib/
  app/           router, providers, session
  core/
    auth/        token storage, sign-in, the offline PIN digest
    content/     catalogue, pack download, media cache
    db/          sqflite schema and the data access objects
    models/      plain Dart models, hand written, no code generation
    network/     dio client, one error shape
    platform/    form factor, television detection, device identity
    scoring/     the scorer, mirrored from the server
    sync/        outbox and sync engine
  design/        tokens, theme, components
  features/      auth, profiles, map, mission, downloads, parent
test/            scoring against the shared fixtures, form factors, feedback rules
```

Two deliberate departures from the plan:

- **sqflite with hand-written SQL instead of Drift.** Drift needs a code
  generation step before the project will compile, which is a poor trade on a
  slow connection for a schema this size. All the SQL is behind the data access
  objects in `core/db`, so swapping the engine later touches one directory.
- **Plain models instead of freezed and json_serializable**, for the same
  reason. The models are small and hand-written `fromJson` is easy to read.

## Tests

```bash
flutter test
```

`test/scoring_test.dart` runs `../fixtures/scoring/*.json`, the same files the
Laravel suite runs. That is what stops the app and the server ever disagreeing
about whether a child got something right, which would otherwise show up as
stars vanishing after a sync.

## Fonts

The design calls for Baloo 2 and Nunito. The font files are not in the
repository, so the app currently falls back to the platform font while keeping
the full type scale. To add them, drop the TTFs in `assets/fonts/` and declare
them in `pubspec.yaml` as families named `Baloo2` and `Nunito`; nothing else has
to change.

## Testing it in Chrome

The web platform is enabled so the app can be exercised in a browser without an
Android device. The database runs as SQLite compiled to WebAssembly, so storage,
the outbox and the offline question draw all behave as they do on a phone.

The reliable way is to serve the app from the API's own origin, which means no
cross-origin rules and pack images that load exactly as they do on a device:

```bash
cd mobile
flutter build web --base-href "/app/" --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
cp -r build/web/* ../public/app/
cd .. && php artisan serve
```

Then open <http://127.0.0.1:8000/app/>. In Git Bash, prefix the build with
`MSYS_NO_PATHCONV=1` or it rewrites `/app/` into a Windows path.

For hot reload while working on the app, run it against its own dev server
instead. The API needs CORS for that, which `config/cors.php` already allows:

```bash
flutter run -d chrome --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
```

Two things behave differently in a browser than on a device, both by design:
pack media is streamed from its URL rather than cached to disk, since a browser
has no application directory; and the secure token store falls back to the
browser's own encrypted storage.

## Building the APK

```bash
flutter build apk --debug
```

The app analyses clean and its tests pass, but the APK has not been built on the
machine this was written on: four attempts were killed by the operating system.
That machine has 8 GB of memory with an editor holding 1.7 GB and a browser 1.3
GB. Close them and the build fits, and the CI workflow builds it on every push.

## Android SDK notes

The build pins `compileSdk = 36` and `flutter_secure_storage` below version 10.
That is not arbitrary: version 10 and above require compileSdk 37, and on the
machine this was built on the Android SDK cannot install platform 37 correctly.
The command-line tools there understand SDK XML version 3 while the repository
now serves version 4, so the platform lands in `platforms/android-37.0` with
`AndroidVersion.ApiLevel=37.0` in its `source.properties`, which Gradle cannot
resolve to `android-37`.

The real fix is to update the Android command-line tools
(`sdkmanager --install "cmdline-tools;latest"`, or update them in Android
Studio) and then reinstall platform 37. Once that is done, both pins can be
lifted. Until then, pinning keeps the build reproducible instead of chasing an
SDK that is not there.

## Android TV

The same build serves televisions. `android/app/src/main/AndroidManifest.xml`
declares leanback, marks the touchscreen as not required, and adds the leanback
launcher intent. Every interactive widget goes through `KidFocusable`, so the
D-pad works everywhere and the focus ring is a brand element rather than a
default outline.

The banner at `res/drawable/tv_banner.xml` is a placeholder. Google Play needs a
real 320x180 image before the TV track can be submitted.
