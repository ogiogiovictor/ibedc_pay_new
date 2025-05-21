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
        Schema::create('account_creations', function (Blueprint $table) {
            //$table->id();
            $table->bigIncrements('id');
            $table->string('tracking_id')->index();
            $table->string('phone')->index();
            $table->string('email')->index();
            $table->string('account_no')->index()->nullable();
            $table->string('title')->nullable();
            $table->string('surname')->index();
            $table->string('firstname')->index();
            $table->string('other_name')->nullable();
            $table->string('house_no')->nullable();
            $table->string('nearest_bustop');
            $table->string('landmark')->nullable();
            $table->string('lga');
            $table->string('address'); # Address at which supply is required
            $table->enum('type_of_premise', ['2Bedroom', '1Bedroom', '3Bedroom', '4Bedroom', 'Tenement House', 'Single Room', 'Shop(s)', 'Boys Qtrs', 'Block of flats',
            'Duplex', 'Mansion', 'Charlet'])->default("others");
            $table->string('others_in_type_of_premise')->nullable();  // dropdown
            $table->enum('use_of_premise', ['Residential', 'Commercial', 'Special', 'Industrial', 'Others'])->default("Residential");

            $table->string('ip_address')->index()->nullable();
            $table->unique(['tracking_id', 'phone', 'email']);
            $table->enum('status', ['incompleted', 'started', 'processing', 'with-dtm', 'lecan-completed', 'with-billing', 'with-bhm', 'with-regional_head', 'rejected', 
            'cancelled', 'approved', 'completed'])->default("started");
            $table->string('status_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_creations');
    }
};
