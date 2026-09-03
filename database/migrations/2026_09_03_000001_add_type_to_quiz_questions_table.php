<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The engine (kids/mission/engine.blade.php) prefers the denormalized
     * quiz_questions.type column and falls back to the quizType relation slug.
     * The column existed on some environments (added manually) but was never
     * created by a migration, and QuizQuestion::$fillable did not include it,
     * so seeder writes to `type` were silently discarded. This migration makes
     * the column real everywhere and backfills it from quiz_types.slug.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('quiz_questions', 'type')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->string('type', 50)->nullable()->after('quiz_type_id');
            });
        }

        // Backfill: derive type from the quiz_types FK where it is still NULL.
        // Driver-agnostic (works on both PostgreSQL and MariaDB).
        \App\Models\QuizQuestion::query()
            ->whereNull('type')
            ->with('quizType')
            ->chunkById(200, function ($questions) {
                foreach ($questions as $question) {
                    if ($question->quizType) {
                        $question->type = $question->quizType->slug;
                        $question->save();
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('quiz_questions', 'type')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
