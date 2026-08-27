<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 20)->unique();           // e.g. CBC, CAMB, MONT
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#4F46E5');
            $table->string('icon', 10)->default('🎓');
            $table->integer('sort_order')->default(0);
            $table->string('status', 20)->default('draft');  // draft | published | archived
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curricula');
    }
};
