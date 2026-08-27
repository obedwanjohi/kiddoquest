<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guardians')) {
            Schema::table('guardians', function (Blueprint $table) {
                if (!Schema::hasColumn('guardians', 'parent_pin')) {
                    $table->string('parent_pin')->default('1234')->after('password');
                }
            });
        }

        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                if (!Schema::hasColumn('children', 'daily_time_limit_minutes')) {
                    $table->integer('daily_time_limit_minutes')->default(0)->after('star_coins'); // 0 = unlimited
                }
                if (!Schema::hasColumn('children', 'today_usage_minutes')) {
                    $table->integer('today_usage_minutes')->default(0)->after('daily_time_limit_minutes');
                }
                if (!Schema::hasColumn('children', 'last_usage_reset_at')) {
                    $table->timestamp('last_usage_reset_at')->nullable()->after('today_usage_minutes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('guardians')) {
            Schema::table('guardians', function (Blueprint $table) {
                $table->dropColumn('parent_pin');
            });
        }

        if (Schema::hasTable('children')) {
            Schema::table('children', function (Blueprint $table) {
                $table->dropColumn(['daily_time_limit_minutes', 'today_usage_minutes', 'last_usage_reset_at']);
            });
        }
    }
};
