<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('entity_type', 50);   // Lesson, Quiz, Subject, etc.
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50);         // created, updated, submitted, approved, rejected, archived, published, deleted
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // any extra context (field changes, etc.)
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_audit_logs');
    }
};