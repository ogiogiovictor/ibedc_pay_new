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
        Schema::create('wallet_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index()->nullable();
            $table->string('user_email')->nullable();
            $table->string('customer_email')->nullable();
            $table->float('wallet_amount')->default(0);
            $table->string('log_by')->index()->nullable();
            $table->string('response')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_refunds');
    }
};
