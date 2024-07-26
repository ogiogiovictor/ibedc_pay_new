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
        Schema::create('polaris_payment_payloads', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('meter_no')->nullable();
            $table->string('account_no')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polaris_payment_payloads');
    }
};
