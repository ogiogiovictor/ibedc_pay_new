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
        Schema::create('continue_account_creations', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->index();
            $table->string('tracking_id')->index();
            $table->string('nin_number')->index()->nullable();
            $table->string('landlord_surname')->nullable();
            $table->string('landlord_othernames')->nullable();
            $table->string('landlord_dob')->nullable();
            $table->string('landlord_telephone')->nullable();
            $table->string('landlord_email')->nullable();
            $table->string('name_address_of_previous_employer')->nullable();
            $table->string('previous_customer_address')->nullable();
            $table->string('previous_account_number')->nullable();
            $table->string('previous_meter_number')->nullable();
            $table->enum('landlord_personal_identification', ['International Passport', 'Drivers License', 'PVC', 'Voters Card', 'BVN', 'NIN', 'Others'])->nullable();
            $table->string('landloard_picture')->nullable();
            $table->enum('prefered_method_of_recieving_bill', ['Sent to the House', 'Bill Sent By SMS', 'Bill Sent By Email'])->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->string('comments')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('continue_account_creations');
    }
};
