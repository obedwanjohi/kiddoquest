<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                if (!Schema::hasColumn('children', 'assigned_mission_id')) {
                    $table->foreignId('assigned_mission_id')->nullable()->after('equipped_hat')->constrained('missions')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                $table->dropForeign(['assigned_mission_id']);
                $table->dropColumn('assigned_mission_id');
            });
        }
    }
};
