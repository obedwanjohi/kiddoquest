<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            \Illuminate\Support\Facades\DB::statement('DROP INDEX IF EXISTS question_banks_lesson_id_status_index');
        } catch (\Throwable $e) {}

        Schema::table('question_banks', function (Blueprint $table) {
            try {
                $table->dropIndex(['lesson_id', 'status']);
            } catch (\Throwable $e) {}
            try {
                $table->dropForeign(['lesson_id']);
            } catch (\Throwable $e) {}
            $table->dropColumn(['lesson_id', 'pool_size', 'pass_threshold', 'max_attempts', 'shuffle']);
        });
    }

    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('pool_size')->default(5);
            $table->integer('pass_threshold')->default(70);
            $table->integer('max_attempts')->default(3);
            $table->boolean('shuffle')->default(true);
        });
    }
};
