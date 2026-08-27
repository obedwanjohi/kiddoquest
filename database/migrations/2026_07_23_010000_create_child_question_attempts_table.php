<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_question_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->onDelete('cascade');
            $table->foreignId('mission_id')->nullable()->constrained('missions')->onDelete('cascade');
            $table->foreignId('question_bank_id')->nullable()->constrained('question_banks')->onDelete('set null');
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();

            $table->index(['child_id', 'mission_id', 'attempted_at']);
            $table->index(['child_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_question_attempts');
    }
};
