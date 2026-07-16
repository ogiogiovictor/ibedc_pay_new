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
         Schema::table('wallet_logs', function (Blueprint $table) {
            $table->float('current_balance')->default(0);
            $table->float('previous_balance')->default(0);
            $table->string('customer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('wallet_logs', function (Blueprint $table) {
             $table->dropColumn('current_balance');
             $table->dropColumn('previous_balance');
             $table->dropColumn('customer_id');
        });
    }
};
