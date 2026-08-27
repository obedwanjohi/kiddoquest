<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->string('favorite_color')->nullable()->after('avatar');
            $table->string('recommended_level')->nullable()->after('favorite_color');
            $table->timestamp('last_played_at')->nullable()->after('total_stars');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn(['favorite_color', 'recommended_level', 'last_played_at']);
        });
    }
};