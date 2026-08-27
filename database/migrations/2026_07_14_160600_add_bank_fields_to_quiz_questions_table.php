<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Question Bank link (nullable — old questions stay on quiz_id)
            $table->foreignId('question_bank_id')->nullable()->after('quiz_id')->constrained()->nullOnDelete();

            // Narration link (replaces standalone prompt_audio_url long-term)
            $table->foreignId('narration_id')->nullable()->after('prompt_audio_url')->constrained()->nullOnDelete();

            // Curriculum + difficulty
            $table->string('cbc_outcome_code')->nullable()->after('scoring_config');
            $table->string('difficulty', 10)->default('easy')->after('cbc_outcome_code'); // easy | medium | hard
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['question_bank_id']);
            $table->dropForeign(['narration_id']);
            $table->dropColumn(['question_bank_id', 'narration_id', 'cbc_outcome_code', 'difficulty']);
        });
    }
};