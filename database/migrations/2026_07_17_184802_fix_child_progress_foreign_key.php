<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old FK that incorrectly points mission_id → lessons.id
        DB::statement('ALTER TABLE `child_progress` DROP FOREIGN KEY `child_progress_lesson_id_foreign`');

        // Add correct FK pointing mission_id → missions.id
        DB::statement('ALTER TABLE `child_progress` ADD CONSTRAINT `child_progress_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `child_progress` DROP FOREIGN KEY `child_progress_mission_id_foreign`');
        DB::statement('ALTER TABLE `child_progress` ADD CONSTRAINT `child_progress_lesson_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE');
    }
};
