<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subscriptions Table
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
                $table->string('plan_type')->default('monthly'); // monthly, termly, annual
                $table->decimal('amount_kes', 10, 2)->default(499.00);
                $table->string('status')->default('active'); // pending, active, expired, cancelled
                $table->string('mpesa_phone')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Payments Table (M-Pesa Checkout Transactions)
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
                $table->foreignId('guardian_id')->constrained('guardians')->onDelete('cascade');
                $table->string('checkout_request_id')->nullable()->unique();
                $table->string('merchant_request_id')->nullable();
                $table->string('mpesa_receipt_number')->nullable()->unique();
                $table->decimal('amount', 10, 2)->default(499.00);
                $table->string('phone_number');
                $table->string('status')->default('pending'); // pending, completed, failed
                $table->text('result_desc')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
    }
};
