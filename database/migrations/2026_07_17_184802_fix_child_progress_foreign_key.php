<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `child_progress` DROP FOREIGN KEY `child_progress_lesson_id_foreign`');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE `child_progress` ADD CONSTRAINT `child_progress_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE');
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `child_progress` DROP FOREIGN KEY `child_progress_mission_id_foreign`');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE `child_progress` ADD CONSTRAINT `child_progress_lesson_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE');
            } catch (\Throwable $e) {}
        }
    }
};
