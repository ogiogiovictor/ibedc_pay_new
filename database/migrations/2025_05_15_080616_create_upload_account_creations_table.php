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
        Schema::create('upload_account_creations', function (Blueprint $table) {
            $table->id();
             $table->string('customer_id')->index();
            $table->string('tracking_id')->index();
            $table->string('means_of_identification')->index();
            $table->string('identification')->index();
            $table->string('photo')->index();
            $table->string('lecan_image')->index()->nullable();
            $table->string('lecan_verified')->index()->nullable();
            $table->string('comment')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_account_creations');
    }
};
