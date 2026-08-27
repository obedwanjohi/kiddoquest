<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['question_bank_id', 'question_id'], 'uniq_bank_question');
            $table->index(['question_bank_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_questions');
    }
};