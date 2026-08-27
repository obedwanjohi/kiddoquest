<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('thumbnail_media_id')->nullable()->after('summary_narration_id')->constrained('media')->nullOnDelete();
            $table->foreignId('video_media_id')->nullable()->after('thumbnail_media_id')->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['thumbnail_media_id']);
            $table->dropForeign(['video_media_id']);
            $table->dropColumn(['thumbnail_media_id', 'video_media_id']);
        });
    }
};