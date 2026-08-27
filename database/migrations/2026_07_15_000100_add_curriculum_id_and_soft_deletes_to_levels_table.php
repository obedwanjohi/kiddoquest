<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->foreignId('curriculum_id')->nullable()->after('id')->constrained('curricula')->nullOnDelete();
            $table->softDeletes();
        });

        // Ensure a default CBC curriculum exists and back-fill existing levels onto it.
        $cbcId = DB::table('curricula')->where('code', 'CBC')->value('id');

        if (! $cbcId) {
            $now = now();
            $cbcId = DB::table('curricula')->insertGetId([
                'name' => 'CBC Curriculum',
                'slug' => 'cbc-curriculum',
                'code' => 'CBC',
                'description' => 'Kenya Competency-Based Curriculum (CBC).',
                'color' => '#4F46E5',
                'icon' => '🇰🇪',
                'sort_order' => 0,
                'status' => 'published',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Attach every existing level to CBC.
        DB::table('levels')->whereNull('curriculum_id')->update(['curriculum_id' => $cbcId]);

        // Normalize the legacy 'active' status to the standard 'published'.
        DB::table('levels')->where('status', 'active')->update(['status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropForeign(['curriculum_id']);
            $table->dropColumn('curriculum_id');
            $table->dropSoftDeletes();
        });
    }
};
