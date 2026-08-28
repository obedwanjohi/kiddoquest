<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adventure_worlds', function (Blueprint $table) {
            if (!Schema::hasColumn('adventure_worlds', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('slug')->constrained('subjects')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('adventure_worlds', function (Blueprint $table) {
            if (Schema::hasColumn('adventure_worlds', 'subject_id')) {
                $table->dropConstrainedForeignId('subject_id');
            }
        });
    }
};
