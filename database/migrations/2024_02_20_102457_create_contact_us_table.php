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
        Schema::create('contact_us', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('null');
            $table->string('message')->default('null');
            $table->string('email')->default('null');
            $table->string('account_type')->default('null');
            $table->string('unique_code')->default('null');
            $table->string('subject')->default('null');
            $table->string('status')->default('null');
            $table->string('phone')->default('null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us');
    }
};
