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
        Schema::create('store_maf_meters', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->index();
            $table->string('meter_no')->index();
            $table->string('customer_name')->nullable();
            $table->longText('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('date_installed')->nullable();
            $table->string('region')->nullable();
            $table->string('business_hub')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_maf_meters');
    }
};
