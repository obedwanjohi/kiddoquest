<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();

            $table->string('name');
            $table->string('disk', 20)->default('public');
            $table->string('file_path');                    // relative path on disk
            $table->string('file_name');                    // original uploaded file name
            $table->string('mime_type', 100);
            $table->string('extension', 10);
            $table->string('type', 20);                     // image | video | audio | document
            $table->unsignedBigInteger('size_bytes')->default(0);

            // Media-specific metadata
            $table->string('thumbnail_path')->nullable();   // for videos/images
            $table->unsignedInteger('duration_seconds')->nullable(); // for video/audio
            $table->unsignedInteger('width')->nullable();   // for image/video
            $table->unsignedInteger('height')->nullable();  // for image/video

            // Organizational metadata
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();               // flexible tagging
            $table->text('alt_text')->nullable();           // accessibility
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['type', 'subject_id']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};