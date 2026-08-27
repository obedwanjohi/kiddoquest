<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add deferred foreign key constraints for tables created before their parents.
     * Topics (081231) and Lessons (081558) were created before Subjects (163559) and Admins (152219).
     */
    public function up(): void
    {
        // ── topics.subject_id -> subjects ──
        if (Schema::hasTable('topics') && Schema::hasTable('subjects')) {
            try {
                Schema::table('topics', function (Blueprint $table) {
                    $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
                });
            } catch (\Exception $e) { /* FK may already exist */ }
        }

        // ── topics.created_by -> admins ──
        if (Schema::hasTable('topics') && Schema::hasTable('admins')) {
            try {
                Schema::table('topics', function (Blueprint $table) {
                    $table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
                });
            } catch (\Exception $e) { /* FK may already exist */ }
        }

        // ── lessons.topic_id -> topics ──
        if (Schema::hasTable('lessons') && Schema::hasTable('topics')) {
            try {
                Schema::table('lessons', function (Blueprint $table) {
                    $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
                });
            } catch (\Exception $e) { /* FK may already exist */ }
        }

        // ── lessons.created_by -> admins ──
        if (Schema::hasTable('lessons') && Schema::hasTable('admins')) {
            try {
                Schema::table('lessons', function (Blueprint $table) {
                    $table->foreign('created_by')->references('id')->on('admins')->nullOnDelete();
                });
            } catch (\Exception $e) { /* FK may already exist */ }
        }
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['created_by']);
        });
    }
};