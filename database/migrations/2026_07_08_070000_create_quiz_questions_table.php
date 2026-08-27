<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_type_id')->constrained()->restrictOnDelete();
            $table->text('prompt'); // the question text
            $table->string('prompt_image_url')->nullable(); // optional image prompt
            $table->string('prompt_audio_url')->nullable(); // optional audio prompt
            $table->integer('points')->default(1);
            $table->integer('sort_order')->default(0);
            $table->text('hint')->nullable(); // optional hint for child
            $table->text('explanation')->nullable(); // shown after answering
            $table->json('scoring_config')->nullable(); // type-specific scoring rules (e.g., correct_value for count)
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};