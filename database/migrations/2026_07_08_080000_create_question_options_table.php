<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->string('content_type', 20)->default('text'); // text, image, audio
            $table->string('text_value')->nullable(); // for text options
            $table->string('image_url')->nullable(); // for image options
            $table->string('audio_url')->nullable(); // for audio options
            $table->boolean('is_correct')->default(false);
            $table->string('match_key')->nullable(); // for matching type (pair identifier)
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};