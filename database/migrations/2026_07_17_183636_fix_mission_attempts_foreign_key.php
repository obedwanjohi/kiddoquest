<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `mission_attempts` DROP FOREIGN KEY `quiz_attempts_quiz_id_foreign`');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE `mission_attempts` ADD CONSTRAINT `mission_attempts_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE');
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE `mission_attempts` DROP FOREIGN KEY `mission_attempts_mission_id_foreign`');
            } catch (\Throwable $e) {}
            try {
                DB::statement('ALTER TABLE `mission_attempts` ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE');
            } catch (\Throwable $e) {}
        }
    }
};
