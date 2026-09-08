import 'package:flutter/foundation.dart' show debugPrint, kIsWeb;
import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';
import 'package:sqflite_common_ffi_web/sqflite_ffi_web.dart';

/// What a media key in a pack points at: the file if it has been downloaded,
/// and the URL to fall back on if it has not.
class ResolvedMedia {
  const ResolvedMedia({required this.url, this.localPath});

  final String url;
  final String? localPath;

  bool get hasFile => localPath != null && localPath!.isNotEmpty;
}

/// The on-device database.
///
/// This is what makes the app work with no network: the whole of a downloaded
/// world lives here, along with the child's progress and the outbox of things
/// waiting to be told to the server.
///
/// The plan names Drift for this. Plain sqflite with hand-written SQL is used
/// instead so the project builds with no code generation step, which matters a
/// great deal when the toolchain is being set up over a slow connection. The
/// queries are behind the DAOs below, so swapping the engine later touches only
/// this directory.
class LocalDatabase {
  LocalDatabase._();

  static final LocalDatabase instance = LocalDatabase._();

  static const int schemaVersion = 1;

  Database? _db;

  Future<Database> get database async => _db ??= await _open();

  Future<Database> _open() async {
    // On the web the same schema runs against SQLite compiled to WebAssembly, so
    // a browser build behaves like a device build rather than needing a second
    // storage layer. The shared worker keeps the database off the UI thread;
    // where a browser will not start one, fall back to running it in the page,
    // which is slower but works.
    if (kIsWeb) {
      databaseFactory = databaseFactoryFfiWeb;

      try {
        await databaseFactory.openDatabase(':memory:').then((db) => db.close());
      } catch (error) {
        debugPrint('Shared worker SQLite unavailable ($error); using the in-page database.');
        databaseFactory = databaseFactoryFfiWebNoWebWorker;
      }
    }

    final path = kIsWeb ? 'kiddoquest.db' : p.join(await getDatabasesPath(), 'kiddoquest.db');

    return openDatabase(
      path,
      version: schemaVersion,
      onConfigure: (db) async => db.execute('PRAGMA foreign_keys = ON'),
      onCreate: (db, version) async => _createSchema(db),
      onUpgrade: (db, from, to) async {
        // Content is re-downloadable and progress lives on the server, so an
        // early-version upgrade rebuilds rather than migrating.
        if (from < schemaVersion) {
          await _dropSchema(db);
          await _createSchema(db);
        }
      },
    );
  }

  /// For tests: point the database at an in-memory instance.
  // ignore: use_setters_to_change_properties
  void useDatabase(Database database) => _db = database;

  Future<void> close() async {
    await _db?.close();
    _db = null;
  }

  static Future<void> _dropSchema(Database db) async {
    for (final table in _tables.reversed) {
      await db.execute('DROP TABLE IF EXISTS $table');
    }
  }

  static const List<String> _tables = [
    'packs',
    'worlds',
    'missions',
    'questions',
    'options',
    'media_files',
    'children',
    'child_progress',
    'local_attempts',
    'question_attempt_log',
    'screen_time_sessions',
    'outbox_events',
    'sync_state',
    'settings',
  ];

  static Future<void> createSchemaFor(Database db) => _createSchema(db);

  static Future<void> _createSchema(Database db) async {
    final batch = db.batch();

    // ── Content ──────────────────────────────────────────────────────────────
    batch.execute('''
      CREATE TABLE packs (
        pack_id TEXT PRIMARY KEY,
        version INTEGER NOT NULL,
        level TEXT,
        subject_code TEXT,
        world_slug TEXT,
        name TEXT,
        icon TEXT,
        theme_color TEXT,
        is_free INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0,
        status TEXT NOT NULL DEFAULT 'ready',
        bytes INTEGER NOT NULL DEFAULT 0,
        downloaded_at TEXT
      )
    ''');

    batch.execute('''
      CREATE TABLE worlds (
        id INTEGER PRIMARY KEY,
        pack_id TEXT NOT NULL,
        slug TEXT,
        name TEXT NOT NULL,
        icon TEXT,
        description TEXT,
        theme_color TEXT,
        subject_code TEXT,
        category TEXT,
        level TEXT,
        is_free INTEGER NOT NULL DEFAULT 0,
        sort_order INTEGER NOT NULL DEFAULT 0
      )
    ''');

    batch.execute('''
      CREATE TABLE missions (
        id INTEGER PRIMARY KEY,
        world_id INTEGER NOT NULL,
        pack_id TEXT NOT NULL,
        slug TEXT,
        title TEXT NOT NULL,
        display_title TEXT,
        description TEXT,
        intro_text TEXT,
        intro_audio TEXT,
        outro_text TEXT,
        video_path TEXT,
        questions_per_session INTEGER NOT NULL DEFAULT 6,
        pass_threshold INTEGER NOT NULL DEFAULT 60,
        stars_reward INTEGER NOT NULL DEFAULT 3,
        estimated_minutes INTEGER NOT NULL DEFAULT 5,
        sort_order INTEGER NOT NULL DEFAULT 0
      )
    ''');
    batch.execute('CREATE INDEX idx_missions_world ON missions(world_id, sort_order)');

    batch.execute('''
      CREATE TABLE questions (
        id INTEGER PRIMARY KEY,
        mission_id INTEGER NOT NULL,
        bank_id INTEGER,
        type TEXT NOT NULL,
        prompt TEXT,
        narration_text TEXT,
        narration_audio TEXT,
        image TEXT,
        audio TEXT,
        hint TEXT,
        explanation TEXT,
        points INTEGER NOT NULL DEFAULT 1,
        difficulty TEXT,
        scoring_config TEXT,
        metadata TEXT,
        sort_order INTEGER NOT NULL DEFAULT 0
      )
    ''');
    batch.execute('CREATE INDEX idx_questions_mission ON questions(mission_id)');

    batch.execute('''
      CREATE TABLE options (
        id INTEGER PRIMARY KEY,
        question_id INTEGER NOT NULL,
        text TEXT,
        image TEXT,
        audio TEXT,
        is_correct INTEGER NOT NULL DEFAULT 0,
        content_type TEXT,
        match_key TEXT,
        sort_order INTEGER NOT NULL DEFAULT 0
      )
    ''');
    batch.execute('CREATE INDEX idx_options_question ON options(question_id)');

    batch.execute('''
      CREATE TABLE media_files (
        path TEXT PRIMARY KEY,
        pack_id TEXT,
        url TEXT NOT NULL,
        sha256 TEXT,
        bytes INTEGER NOT NULL DEFAULT 0,
        kind TEXT NOT NULL DEFAULT 'image',
        required INTEGER NOT NULL DEFAULT 1,
        local_path TEXT,
        status TEXT NOT NULL DEFAULT 'pending'
      )
    ''');

    // ── The family ───────────────────────────────────────────────────────────
    batch.execute('''
      CREATE TABLE children (
        id INTEGER PRIMARY KEY,
        guardian_id INTEGER,
        payload TEXT NOT NULL,
        snapshot_at TEXT
      )
    ''');

    batch.execute('''
      CREATE TABLE child_progress (
        child_id INTEGER NOT NULL,
        mission_id INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'in_progress',
        stars_earned INTEGER NOT NULL DEFAULT 0,
        completed_at TEXT,
        PRIMARY KEY (child_id, mission_id)
      )
    ''');

    batch.execute('''
      CREATE TABLE local_attempts (
        id TEXT PRIMARY KEY,
        child_id INTEGER NOT NULL,
        mission_id INTEGER NOT NULL,
        pack_version INTEGER,
        score INTEGER NOT NULL DEFAULT 0,
        total INTEGER NOT NULL DEFAULT 0,
        stars INTEGER NOT NULL DEFAULT 0,
        passed INTEGER NOT NULL DEFAULT 0,
        time_spent INTEGER NOT NULL DEFAULT 0,
        answers TEXT,
        completed_at TEXT NOT NULL,
        synced INTEGER NOT NULL DEFAULT 0
      )
    ''');

    // Feeds the seven-day exclusion filter while offline, so a retest brings
    // fresh questions exactly as it does on the website.
    batch.execute('''
      CREATE TABLE question_attempt_log (
        child_id INTEGER NOT NULL,
        mission_id INTEGER NOT NULL,
        question_id INTEGER NOT NULL,
        attempted_at TEXT NOT NULL,
        is_correct INTEGER NOT NULL DEFAULT 0
      )
    ''');
    batch.execute('CREATE INDEX idx_qlog ON question_attempt_log(child_id, mission_id, attempted_at)');

    batch.execute('''
      CREATE TABLE screen_time_sessions (
        id TEXT PRIMARY KEY,
        child_id INTEGER NOT NULL,
        started_at TEXT NOT NULL,
        ended_at TEXT,
        seconds INTEGER NOT NULL DEFAULT 0,
        day_local TEXT NOT NULL
      )
    ''');
    batch.execute('CREATE INDEX idx_screen_time ON screen_time_sessions(child_id, day_local)');

    // ── Sync ─────────────────────────────────────────────────────────────────
    batch.execute('''
      CREATE TABLE outbox_events (
        id TEXT PRIMARY KEY,
        child_id INTEGER,
        type TEXT NOT NULL,
        payload TEXT NOT NULL,
        client_ts TEXT NOT NULL,
        seq INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        attempts INTEGER NOT NULL DEFAULT 0,
        last_error TEXT
      )
    ''');
    batch.execute('CREATE INDEX idx_outbox_pending ON outbox_events(status, seq)');

    batch.execute('''
      CREATE TABLE sync_state (
        child_id INTEGER PRIMARY KEY,
        cursor TEXT,
        last_success_at TEXT,
        pending_count INTEGER NOT NULL DEFAULT 0
      )
    ''');

    batch.execute('''
      CREATE TABLE settings (
        key TEXT PRIMARY KEY,
        value TEXT
      )
    ''');

    await batch.commit(noResult: true);
  }
}
