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
        Schema::create('technical_evaluations', function (Blueprint $table) {
             $table->id();
             $table->string('services')->nullable();
             $table->enum('type', ['Yes', 'No'])->default('No');
             $table->string('upload_houses_id');
             $table->string('existing_meter')->nullable();
             $table->string('meter_phase');
             $table->string('extension_cables');
             $table->string('service_cable')->nullable();
             $table->string('energy_consumption');
             $table->string('meter_classification');
             $table->string('recommended_tariff')->nullable();
             $table->string('meter_recommendation');
             $table->string('comment')->nullable();
             $table->string('upload_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_evaluations');
    }
};
