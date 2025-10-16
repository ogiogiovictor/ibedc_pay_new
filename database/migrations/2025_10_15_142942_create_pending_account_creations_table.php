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
        Schema::create('pending_account_creations', function (Blueprint $table) {
            $table->id();
            $table->string('account_no');
            $table->text('account');
            $table->text('upload_houses');
            $table->unsignedBigInteger('upload_houses_id');
            //$table->string('upload_houses');
            $table->string('service_code');
            $table->string('dss');
            $table->unsignedBigInteger('user_id');
            $table->string('user_email');
            $table->text('user');
            $table->string('tracking_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_account_creations');
    }
};
