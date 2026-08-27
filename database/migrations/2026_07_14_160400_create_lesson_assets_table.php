<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // image | video | audio | worksheet | thumbnail | etc.
            $table->string('title')->nullable();
            $table->string('caption')->nullable();
            $table->foreignId('media_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('display_order')->default(0);
            $table->string('status', 20)->default('draft'); // draft, pending, ready
            $table->timestamps();

            $table->index(['lesson_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assets');
    }
};