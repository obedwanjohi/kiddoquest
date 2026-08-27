<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adventure_worlds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('theme_color')->default('#22C55E'); // green default
            $table->string('icon')->default('🌳');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_locked')->default(false); // world unlock state
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adventure_worlds');
    }
};