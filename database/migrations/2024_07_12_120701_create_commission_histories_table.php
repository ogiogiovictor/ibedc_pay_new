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
        Schema::create('commission_histories', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('commission_amount')->default(0.00);
            $table->string('commision_percent');
            $table->string('amount_paid');
            $table->string('acount_type');
            $table->string('account_id');
            $table->string('agency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_histories');
    }
};
