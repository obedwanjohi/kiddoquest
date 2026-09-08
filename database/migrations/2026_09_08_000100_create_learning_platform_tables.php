<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The tables the Flutter app's API needs (KIDDOQUEST_FLUTTER_APP_PLAN.md §7.5).
 *
 * learning_events is the write path: every device appends UUID-keyed events and
 * the existing mission_attempts / child_progress / child_question_attempts tables
 * become projections built from them, so the Blade parent dashboard keeps working
 * unchanged.
 *
 * On PostgreSQL, learning_events should later be converted to a monthly RANGE
 * partition on received_at; that is a production-only operation and is left out
 * here so the same migration runs on SQLite, MySQL and Postgres.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── The event log ────────────────────────────────────────────────────
        if (! Schema::hasTable('learning_events')) {
            Schema::create('learning_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
                $table->foreignId('child_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('device_id', 64)->index();
                $table->string('type', 40);
                $table->json('payload')->nullable();
                $table->timestampTz('client_ts')->nullable();
                $table->timestampTz('received_at');
                $table->unsignedBigInteger('seq')->default(0);
                $table->unsignedInteger('pack_version')->nullable();
                $table->string('status', 16)->default('accepted'); // accepted | quarantined
                $table->string('reason', 64)->nullable();
                $table->timestampTz('processed_at')->nullable();

                $table->index(['child_id', 'received_at']);
                $table->index(['guardian_id', 'received_at']);
                $table->index(['status', 'processed_at']);
                $table->index(['child_id', 'type', 'client_ts']);
            });
        }

        // ── Devices that talk to the API ─────────────────────────────────────
        if (! Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->string('id', 64)->primary();           // client-generated device_id
                $table->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete();
                $table->string('platform', 24)->nullable();    // android | android_tv | ios
                $table->string('model', 96)->nullable();
                $table->string('app_version', 24)->nullable();
                $table->string('push_token', 255)->nullable();
                $table->timestampTz('last_seen_at')->nullable();
                $table->timestamps();

                $table->index('guardian_id');
            });
        }

        // ── TV device-code login ─────────────────────────────────────────────
        if (! Schema::hasTable('device_login_codes')) {
            Schema::create('device_login_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 12)->unique();
                $table->string('device_id', 64)->nullable();
                $table->foreignId('guardian_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('platform', 24)->nullable();
                $table->timestampTz('approved_at')->nullable();
                $table->timestampTz('claimed_at')->nullable();
                $table->timestampTz('expires_at');
                $table->timestamps();

                $table->index('expires_at');
            });
        }

        // ── Daily projection powering the parent dashboard ───────────────────
        if (! Schema::hasTable('child_daily_stats')) {
            Schema::create('child_daily_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('child_id')->constrained()->cascadeOnDelete();
                $table->date('day');
                $table->unsignedInteger('seconds_played')->default(0);
                $table->unsignedInteger('missions_completed')->default(0);
                $table->unsignedInteger('missions_passed')->default(0);
                $table->unsignedInteger('questions')->default(0);
                $table->unsignedInteger('correct')->default(0);
                $table->unsignedInteger('stars_earned')->default(0);
                $table->unsignedInteger('coins_earned')->default(0);
                $table->timestamps();

                $table->unique(['child_id', 'day']);
            });
        }

        // ── Published content packs ──────────────────────────────────────────
        if (! Schema::hasTable('content_packs')) {
            Schema::create('content_packs', function (Blueprint $table) {
                $table->id();
                $table->string('pack_id', 120);
                $table->unsignedInteger('version');
                $table->foreignId('world_id')->constrained('adventure_worlds')->cascadeOnDelete();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('level_code', 12)->nullable();
                $table->string('subject_code', 24)->nullable();
                $table->string('world_slug', 120);
                $table->string('name', 160);
                $table->string('icon', 16)->nullable();
                $table->string('theme_color', 16)->nullable();
                $table->boolean('is_free')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedInteger('mission_count')->default(0);
                $table->unsignedInteger('question_count')->default(0);
                $table->unsignedInteger('media_count')->default(0);
                $table->unsignedBigInteger('bytes_core')->default(0);
                $table->unsignedBigInteger('bytes_video')->default(0);
                $table->string('sha256', 64)->nullable();
                $table->string('json_path', 255);
                $table->string('url', 512)->nullable();
                $table->json('missing_media')->nullable();
                $table->timestampTz('published_at')->nullable();
                $table->timestamps();

                $table->unique(['pack_id', 'version']);
                $table->index(['level_code', 'sort_order']);
            });
        }

        // ── Additive columns on existing tables ──────────────────────────────

        // Ties a projected attempt back to the event that produced it, so replays
        // of the same event never double-count stars.
        if (Schema::hasTable('mission_attempts') && ! Schema::hasColumn('mission_attempts', 'event_id')) {
            Schema::table('mission_attempts', function (Blueprint $table) {
                $table->uuid('event_id')->nullable()->unique()->after('id');
                $table->unsignedInteger('pack_version')->nullable()->after('event_id');
                $table->string('source', 16)->default('web')->after('pack_version'); // web | app
            });
        }

        // Plan §7.7 item 6: a real subject code instead of matching on world names.
        if (Schema::hasTable('subjects') && ! Schema::hasColumn('subjects', 'code')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->string('code', 24)->nullable()->after('slug');
            });
        }

        if (Schema::hasTable('subscriptions') && ! Schema::hasColumn('subscriptions', 'grace_days')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->unsignedSmallInteger('grace_days')->default(7)->after('expires_at');
            });
        }

        // Screen time needs the child's own limit; older installs may lack it.
        if (Schema::hasTable('children') && ! Schema::hasColumn('children', 'daily_time_limit_minutes')) {
            Schema::table('children', function (Blueprint $table) {
                $table->unsignedSmallInteger('daily_time_limit_minutes')->default(0);
            });
        }

        $this->backfillSubjectCodes();
    }

    /**
     * Give every subject a code derived from the same words the web app's
     * getSubjectCategoryAttribute() matches on, so the app's subject tabs and
     * the site agree without the string matching having to run at request time.
     */
    protected function backfillSubjectCodes(): void
    {
        if (! Schema::hasTable('subjects') || ! Schema::hasColumn('subjects', 'code')) {
            return;
        }

        foreach (DB::table('subjects')->whereNull('code')->get(['id', 'name', 'slug']) as $subject) {
            $haystack = strtolower(trim(($subject->name ?? '') . ' ' . ($subject->slug ?? '')));

            $code = match (true) {
                str_contains($haystack, 'speak'), str_contains($haystack, 'repeat'), str_contains($haystack, 'vocab') => 'SPEAK',
                str_contains($haystack, 'trac'), str_contains($haystack, 'writing') => 'TRACING',
                str_contains($haystack, 'cre'), str_contains($haystack, 'relig'), str_contains($haystack, 'value'), str_contains($haystack, 'moral') => 'CRE',
                str_contains($haystack, 'english'), str_contains($haystack, 'language'), str_contains($haystack, 'phonic'), str_contains($haystack, 'literacy') => 'ENGLISH',
                str_contains($haystack, 'math'), str_contains($haystack, 'number') => 'MATH',
                default => null,
            };

            if ($code) {
                DB::table('subjects')->where('id', $subject->id)->update(['code' => $code]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_packs');
        Schema::dropIfExists('child_daily_stats');
        Schema::dropIfExists('device_login_codes');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('learning_events');

        if (Schema::hasTable('mission_attempts') && Schema::hasColumn('mission_attempts', 'event_id')) {
            Schema::table('mission_attempts', function (Blueprint $table) {
                $table->dropColumn(['event_id', 'pack_version', 'source']);
            });
        }

        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'grace_days')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('grace_days');
            });
        }

        // subjects.code is left in place on purpose: the app reads it.
    }
};
