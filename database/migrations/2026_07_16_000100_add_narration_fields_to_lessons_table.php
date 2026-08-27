<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Module 4: dynamic AI narration (text + managed voice) replaces audio-file FKs.
            $table->text('learning_objective')->nullable()->after('summary');
            $table->text('intro_narration_text')->nullable()->after('learning_objective');
            $table->text('summary_narration_text')->nullable()->after('intro_narration_text');
            $table->foreignId('narration_voice_id')->nullable()->after('summary_narration_text')
                ->constrained('voices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['narration_voice_id']);
            $table->dropColumn([
                'learning_objective',
                'intro_narration_text',
                'summary_narration_text',
                'narration_voice_id',
            ]);
        });
    }
};
