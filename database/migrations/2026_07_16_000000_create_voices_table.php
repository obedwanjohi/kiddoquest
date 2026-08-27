<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voices', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "Leo", "Teacher Mary"
            $table->string('provider')->default('browser');  // browser, elevenlabs, polly, openai
            $table->string('voice_id')->nullable();           // provider-specific voice identifier
            $table->string('language', 10)->default('en');
            $table->string('gender', 20)->nullable();         // male, female, neutral
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');  // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voices');
    }
};
