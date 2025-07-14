<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->index();         // e.g., 0100123456
            $table->string('pay_month');          // 1 - 12
            $table->string('pay_year');          // e.g., 2024
            $table->decimal('amount', 18, 2);                  // Commissionable payment amount
            $table->string('payment_id');          // ID from payments table
            $table->string('agency')->nullable();              // Agency name (if applicable)
            $table->string('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_logs');
    }
};
