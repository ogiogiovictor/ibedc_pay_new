<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.  payment_transactions
     */
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('minimumPurchase')->nullable();
            $table->string('tariffcode')->nullable();
            $table->string('customerArrears')->nullable();
            $table->string('tariff')->nullable();
            $table->string('serviceBand')->nullable();
            $table->string('feederName')->nullable();
            $table->string('dssName')->nullable();
            $table->string('udertaking')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('minimumPurchase');
            $table->dropColumn('tariffcode');
            $table->dropColumn('customerArrears');
            $table->dropColumn('tariff');
            $table->dropColumn('serviceBand');
            $table->dropColumn('feederName');
            $table->dropColumn('dssName');
            $table->dropColumn('udertaking');
        });
    }
};
