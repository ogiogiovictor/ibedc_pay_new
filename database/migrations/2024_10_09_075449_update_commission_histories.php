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
        Schema::table('commission_histories', function (Blueprint $table) {
            $table->string('bill_year');
            $table->string('bill_month');
            $table->string('bill_amount');
            $table->enum('status_settled', ['pending', 'paid'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_histories', function (Blueprint $table) {
            $table->dropColumn('bill_year');
            $table->dropColumn('bill_month');
            $table->dropColumn('bill_amount');
            $table->dropColumn('status_settled');
        });
    }
};
