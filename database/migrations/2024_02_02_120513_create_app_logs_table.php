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
        Schema::create('app_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index()->nullable();
            $table->string('ip_address')->index()->nullable();
            $table->string('ajax')->index()->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('payload')->nullable();
            $table->integer('status_code')->index()->nullable();
            $table->longText('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_logs');
    }
};
