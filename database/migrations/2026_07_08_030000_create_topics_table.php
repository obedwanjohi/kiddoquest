<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If topics table doesn't exist at all (fresh install), create it
        // NOTE: foreign keys are added later in 2026_07_08_164000_add_topic_foreign_keys.php
        // because this migration runs BEFORE subjects/admins tables exist
        if (! Schema::hasTable('topics')) {
            Schema::create('topics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('subject_id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon', 10)->default('📂');
                $table->string('status', 20)->default('draft');
                $table->integer('sort_order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['subject_id', 'status', 'sort_order'], 'topics_subject_status_sort_index');
            });
            return;
        }

        // Otherwise, add only columns that don't exist yet (original fix behavior)
        Schema::table('topics', function (Blueprint $table) {
            if (! Schema::hasColumn('topics', 'subject_id')) {
                $table->foreignId('subject_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('topics', 'name')) {
                $table->string('name')->after('subject_id');
            }
            if (! Schema::hasColumn('topics', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
            if (! Schema::hasColumn('topics', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('topics', 'icon')) {
                $table->string('icon', 10)->default('📂')->after('description');
            }
            if (! Schema::hasColumn('topics', 'status')) {
                $table->string('status', 20)->default('draft')->after('icon');
            }
            if (! Schema::hasColumn('topics', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('status');
            }
            if (! Schema::hasColumn('topics', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('sort_order')->constrained('admins')->nullOnDelete();
            }
            if (! Schema::hasColumn('topics', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Add composite index if it doesn't exist
        try {
            Schema::table('topics', function (Blueprint $table) {
                $table->index(['subject_id', 'status', 'sort_order'], 'topics_subject_status_sort_index');
            });
        } catch (\Exception $e) {
            // Index may already exist — safe to ignore
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};