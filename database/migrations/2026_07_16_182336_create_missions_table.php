<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_bank_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('video_media_id')->nullable()->constrained('media')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Intro narration (TTS)
            $table->text('intro_narration_text')->nullable();
            $table->string('intro_voice_profile')->nullable();

            // Teaching video
            $table->string('video_url')->nullable();
            $table->boolean('allow_replay')->default(true);

            // Outro narration (TTS)
            $table->text('outro_narration_text')->nullable();
            $table->string('outro_voice_profile')->nullable();

            // Assessment config
            $table->unsignedTinyInteger('pass_threshold_percent')->default(60);
            $table->unsignedTinyInteger('stars_reward')->default(3);
            $table->unsignedTinyInteger('questions_per_session')->default(10);

            // Metadata
            $table->unsignedSmallInteger('estimated_minutes')->default(5);
            $table->string('status')->default('draft'); // draft, in_review, published
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['lesson_id', 'sort_order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};