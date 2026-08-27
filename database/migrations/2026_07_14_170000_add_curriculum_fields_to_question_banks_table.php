<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            // Allow standalone banks (not tied to a single lesson)
            $table->foreignId('lesson_id')->nullable()->change();

            // Curriculum links
            $table->foreignId('subject_id')->nullable()->after('lesson_id')->constrained()->nullOnDelete();
            $table->foreignId('sub_strand_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            $table->foreignId('quiz_type_id')->nullable()->after('sub_strand_id')->constrained()->nullOnDelete();

            // Metadata
            $table->string('difficulty', 10)->default('medium')->after('description'); // easy|medium|hard
        });
    }

    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropColumn(['subject_id', 'sub_strand_id', 'quiz_type_id', 'difficulty']);
            $table->foreignId('lesson_id')->nullable(false)->change();
        });
    }
};