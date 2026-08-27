<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename lesson_id to mission_id in child_progress
        Schema::table('child_progress', function (Blueprint $table) {
            // Drop foreign key if it exists
            if (Schema::hasColumn('child_progress', 'lesson_id')) {
                // Not strictly enforced foreign keys in some sqlite setups, but let's rename column
                $table->renameColumn('lesson_id', 'mission_id');
            }
        });

        // 2. Rename quiz_attempts table and its column
        Schema::rename('quiz_attempts', 'mission_attempts');
        
        Schema::table('mission_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('mission_attempts', 'quiz_id')) {
                $table->renameColumn('quiz_id', 'mission_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mission_attempts', function (Blueprint $table) {
            $table->renameColumn('mission_id', 'quiz_id');
        });
        
        Schema::rename('mission_attempts', 'quiz_attempts');

        Schema::table('child_progress', function (Blueprint $table) {
            $table->renameColumn('mission_id', 'lesson_id');
        });
    }
};
