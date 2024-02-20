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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index()->nullable();
            $table->string('transaction_id')->index()->nullable();
            $table->string('user_id')->index();
            $table->string('phone')->index()->nullable();
            $table->string('amount')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number')->index()->nullable();
            $table->string('meter_no')->nullable();
            $table->enum('status', ['pending', 'started', 'processing', 'success', 'failed'])->default('pending');
            $table->string('customer_name')->index()->nullable();
            $table->string('payment_source')->nullable();
            $table->string('provider')->nullable();
            $table->string('providerRef')->index()->nullable();
            $table->timestamp('date_entered')->nullable();
            $table->string('receiptno')->index()->nullable();
            $table->string('owner')->default("null");
            $table->string('BUID')->default("null");
            $table->string('Descript')->default("null");
            $table->string('response_status')->default("0");
            $table->string('latitude')->default("null");
            $table->string('longitude')->default("null");
            $table->string('source_type')->index()->default("null");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
