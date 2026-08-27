<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete()->after('created_by');
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->timestamp('archived_at')->nullable()->after('reviewed_at');
            $table->integer('version')->default(1)->after('archived_at');
            $table->text('review_notes')->nullable()->after('version');
            $table->text('rejection_reason')->nullable()->after('review_notes');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'reviewed_by',
                'submitted_at',
                'reviewed_at',
                'archived_at',
                'version',
                'review_notes',
                'rejection_reason',
            ]);
        });
    }
};