<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('badge_key'); // e.g., 'forest_explorer', 'first_star'
            $table->string('name');
            $table->string('icon')->default('🏆');
            $table->timestamp('awarded_at')->useCurrent();

            $table->unique(['child_id', 'badge_key']); // one badge per child
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_badges');
    }
};