<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adventure_world_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('story_title')->nullable(); // adventure reframe, e.g., "Help Eli Count Apples"
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['adventure_world_id', 'lesson_id']); // one lesson per world (but reusable)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('world_lessons');
    }
};