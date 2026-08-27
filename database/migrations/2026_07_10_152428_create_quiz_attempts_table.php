<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0); // correct answers
            $table->integer('total')->default(0); // total questions
            $table->integer('stars')->default(0); // 0-3 stars based on performance
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable(); // detailed answer log for AI analysis
            $table->integer('time_spent')->default(0); // seconds
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};