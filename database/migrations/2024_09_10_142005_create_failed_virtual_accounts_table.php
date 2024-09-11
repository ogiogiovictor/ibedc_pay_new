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
        Schema::create('failed_virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('fid')->nullable();
            $table->string('tx_ref')->nullable();
            $table->string('flw_ref')->nullable();
            $table->string('amount')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('status')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_virtual_accounts');
    }
};
