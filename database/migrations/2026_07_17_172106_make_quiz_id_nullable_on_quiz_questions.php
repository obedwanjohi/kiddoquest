<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK constraint first, then re-add as nullable
        DB::statement('ALTER TABLE `quiz_questions` DROP FOREIGN KEY `quiz_questions_quiz_id_foreign`');
        DB::statement('ALTER TABLE `quiz_questions` MODIFY COLUMN `quiz_id` BIGINT UNSIGNED NULL DEFAULT NULL');
        DB::statement('ALTER TABLE `quiz_questions` ADD CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        // Note: reversing this would require all null quiz_ids to be populated first
        DB::statement('ALTER TABLE `quiz_questions` DROP FOREIGN KEY `quiz_questions_quiz_id_foreign`');
        DB::statement('ALTER TABLE `quiz_questions` MODIFY COLUMN `quiz_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `quiz_questions` ADD CONSTRAINT `quiz_questions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE');
    }
};
