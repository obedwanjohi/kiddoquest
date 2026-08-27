<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // QT-01, QT-02, etc.
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 10)->default('❓');
            $table->string('interaction_mode', 30)->default('tap'); // tap, drag, voice, draw, write
            $table->boolean('has_options')->default(true); // MC, True/False, etc.
            $table->boolean('has_media_prompt')->default(false); // audio/image prompt
            $table->boolean('is_scoring_type')->default(true); // some types are non-scoring (speak & repeat)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_types');
    }
};