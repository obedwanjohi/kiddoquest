<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the incorrect FK that points mission_id → quizzes.id
        DB::statement('ALTER TABLE `mission_attempts` DROP FOREIGN KEY `quiz_attempts_quiz_id_foreign`');

        // Add the correct FK pointing mission_id → missions.id
        DB::statement('ALTER TABLE `mission_attempts` ADD CONSTRAINT `mission_attempts_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `mission_attempts` DROP FOREIGN KEY `mission_attempts_mission_id_foreign`');
        DB::statement('ALTER TABLE `mission_attempts` ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE');
    }
};
