<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->unsignedInteger('star_coins')->default(0)->after('total_stars');
            $table->json('unlocked_items')->nullable()->after('star_coins');
            $table->string('equipped_hat')->nullable()->after('unlocked_items');
            $table->unsignedInteger('streak_days')->default(1)->after('equipped_hat');
            $table->date('last_streak_date')->nullable()->after('streak_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn([
                'star_coins',
                'unlocked_items',
                'equipped_hat',
                'streak_days',
                'last_streak_date',
            ]);
        });
    }
};
