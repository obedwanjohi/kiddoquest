<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->foreignId('adventure_world_id')->nullable()->after('lesson_id')->constrained()->nullOnDelete();
            $table->string('display_title')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropForeign(['adventure_world_id']);
            $table->dropColumn(['adventure_world_id', 'display_title']);
        });
    }
};
