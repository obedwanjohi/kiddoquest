<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Sub-strand link (nullable for backward compat — old lessons keep topic_id)
            $table->foreignId('sub_strand_id')->nullable()->after('topic_id')->constrained()->nullOnDelete();

            // Media / narration links
            $table->string('video_path')->nullable()->after('video_url');
            $table->integer('video_duration_seconds')->nullable()->after('video_path');
            $table->foreignId('intro_narration_id')->nullable()->after('video_duration_seconds')->constrained('narrations')->nullOnDelete();
            $table->foreignId('summary_narration_id')->nullable()->after('intro_narration_id')->constrained('narrations')->nullOnDelete();

            // CBC + planning
            $table->string('cbc_outcome_code')->nullable()->after('summary_narration_id');
            $table->integer('estimated_minutes')->default(8)->after('cbc_outcome_code');
        });
    }

    public function down(): void
    {
        // Drop foreign keys first, then columns
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['sub_strand_id']);
            $table->dropForeign(['intro_narration_id']);
            $table->dropForeign(['summary_narration_id']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'sub_strand_id',
                'video_path',
                'video_duration_seconds',
                'intro_narration_id',
                'summary_narration_id',
                'cbc_outcome_code',
                'estimated_minutes',
            ]);
        });
    }
};