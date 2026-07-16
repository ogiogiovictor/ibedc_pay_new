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
        Schema::create('meter_installations', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->nullable();
            $table->string('meter_number');
            $table->string('mapid');
            $table->string('tracking_id')->nullable();
            $table->date('installed_at');
            $table->string('installed_by');
            $table->string('install_status');
            $table->decimal('longitude', 10, 8);
            $table->decimal('latitude', 10, 8);
            $table->integer('amount_paid');
            $table->string('payment_status');
            $table->date('payment_date');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_installations');
    }
};
