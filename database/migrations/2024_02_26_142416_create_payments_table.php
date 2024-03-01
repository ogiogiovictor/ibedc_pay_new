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
        Schema::create('polaris_payments', function (Blueprint $table) {
            $table->id();
            $table->string('request_ref')->nullable();
            $table->string('request_type')->nullable()->index();
            $table->string('requester')->nullable();
            $table->string('transaction_type')->nullable()->index();
            $table->string('amount')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('provider')->nullable();
            $table->string('transaction_ref')->nullable()->index();
            $table->string('VirtualAccount')->nullable();
            $table->string('VirtualAccountName')->nullable();
            $table->string('Narration')->nullable()->index();
            $table->string('SenderAccountNumber')->nullable()->index();
            $table->string('SenderAccountName')->nullable();
            $table->string('SenderBankName')->nullable();
            $table->string('account_number')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('customer_ref')->nullable();
            $table->string('customer_firstname')->nullable();
            $table->string('customer_surname')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile_no')->nullable();
            $table->string('Hash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
