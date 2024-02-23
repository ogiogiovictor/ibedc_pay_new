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
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref')->default('null');
            $table->string('account_no')->default('null');
            $table->string('contract_code')->default('null');
            $table->string('account_reference')->default('null');
            $table->string('account_name')->default('null');
            $table->string('customer_email')->default('null');
            $table->string('bank_name')->default('null');
            $table->string('bank_code')->default('null');
            $table->string('account_type')->default('null');
            $table->string('status')->default('null');
            $table->string('user_id')->default('null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
