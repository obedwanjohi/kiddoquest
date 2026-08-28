<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── 1. Fix mission_attempts Foreign Key Constraint ──
        try {
            DB::statement('ALTER TABLE mission_attempts DROP CONSTRAINT IF EXISTS quiz_attempts_quiz_id_foreign');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE mission_attempts DROP CONSTRAINT IF EXISTS mission_attempts_quiz_id_foreign');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE mission_attempts DROP CONSTRAINT IF EXISTS mission_attempts_mission_id_foreign');
        } catch (\Throwable $e) {}

        Schema::table('mission_attempts', function (Blueprint $table) {
            $table->foreign('mission_id', 'mission_attempts_mission_id_foreign')
                ->references('id')
                ->on('missions')
                ->onDelete('cascade');
        });

        // ── 2. Fix child_progress Foreign Key Constraint ──
        try {
            DB::statement('ALTER TABLE child_progress DROP CONSTRAINT IF EXISTS child_progress_lesson_id_foreign');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE child_progress DROP CONSTRAINT IF EXISTS child_progress_mission_id_foreign');
        } catch (\Throwable $e) {}

        Schema::table('child_progress', function (Blueprint $table) {
            $table->foreign('mission_id', 'child_progress_mission_id_foreign')
                ->references('id')
                ->on('missions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_attempts', function (Blueprint $table) {
            try {
                $table->dropForeign('mission_attempts_mission_id_foreign');
            } catch (\Throwable $e) {}
        });

        Schema::table('child_progress', function (Blueprint $table) {
            try {
                $table->dropForeign('child_progress_mission_id_foreign');
            } catch (\Throwable $e) {}
        });
    }
};
