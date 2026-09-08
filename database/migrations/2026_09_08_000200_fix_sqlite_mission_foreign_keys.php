<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SQLite kept the foreign keys from before missions existed.
 *
 * When quizzes became missions and lessons became missions, the MySQL and
 * Postgres fixes dropped the old constraints by name; SQLite cannot drop a
 * constraint at all, so those two tables still point mission_id at quizzes and
 * lessons *as well as* at missions. Every one of those constraints has to be
 * satisfied, so recording a perfectly valid attempt against a mission with no
 * matching quizzes row fails with "FOREIGN KEY constraint failed".
 *
 * SQLite is the development and small-deployment database, and this is the hot
 * write path, so it is rebuilt here the only way SQLite allows: a new table, a
 * copy, and a rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $this->rebuild(
            'mission_attempts',
            <<<'SQL'
            CREATE TABLE "mission_attempts_rebuilt" (
                "id" integer primary key autoincrement not null,
                "child_id" integer not null,
                "mission_id" integer not null,
                "score" integer not null default ('0'),
                "total" integer not null default ('0'),
                "stars" integer not null default ('0'),
                "passed" tinyint(1) not null default ('0'),
                "answers" text,
                "time_spent" integer not null default ('0'),
                "completed_at" datetime,
                "created_at" datetime,
                "updated_at" datetime,
                "event_id" varchar,
                "pack_version" integer,
                "source" varchar not null default 'web',
                foreign key("child_id") references "children"("id") on delete cascade,
                foreign key("mission_id") references "missions"("id") on delete cascade
            )
            SQL,
            ['CREATE UNIQUE INDEX "mission_attempts_event_id_unique" on "mission_attempts" ("event_id")'],
        );

        $this->rebuild(
            'child_progress',
            <<<'SQL'
            CREATE TABLE "child_progress_rebuilt" (
                "id" integer primary key autoincrement not null,
                "child_id" integer not null,
                "mission_id" integer not null,
                "status" varchar not null default ('not_started'),
                "stars_earned" integer not null default ('0'),
                "started_at" datetime,
                "completed_at" datetime,
                "created_at" datetime,
                "updated_at" datetime,
                foreign key("child_id") references "children"("id") on delete cascade,
                foreign key("mission_id") references "missions"("id") on delete cascade
            )
            SQL,
            ['CREATE UNIQUE INDEX "child_progress_child_id_mission_id_unique" on "child_progress" ("child_id", "mission_id")'],
        );
    }

    public function down(): void
    {
        // Putting the wrong constraints back would only break writes again.
    }

    /**
     * @param  array<int,string>  $indexes
     */
    protected function rebuild(string $table, string $createSql, array $indexes): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $definition = (string) DB::scalar(
            'select sql from sqlite_master where type = ? and name = ?',
            ['table', $table]
        );

        // Already rebuilt, or never carried the stale constraint.
        if (! str_contains($definition, 'references quizzes') && ! str_contains($definition, 'references lessons')) {
            return;
        }

        $columns = implode(', ', array_map(
            fn (string $column) => '"' . $column . '"',
            Schema::getColumnListing($table)
        ));

        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            DB::transaction(function () use ($table, $createSql, $indexes, $columns) {
                DB::statement("DROP TABLE IF EXISTS \"{$table}_rebuilt\"");
                DB::statement($createSql);
                DB::statement("INSERT INTO \"{$table}_rebuilt\" ({$columns}) SELECT {$columns} FROM \"{$table}\"");
                DB::statement("DROP TABLE \"{$table}\"");
                DB::statement("ALTER TABLE \"{$table}_rebuilt\" RENAME TO \"{$table}\"");

                foreach ($indexes as $index) {
                    DB::statement($index);
                }
            });
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};
