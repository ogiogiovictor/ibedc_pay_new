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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->index()->unique();
            $table->string('phone')->index()->unique();
            $table->string('pin')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('status', ['0', '1'])->default('0');
            $table->string('password');
            $table->string('user_code')->nullable();
            $table->string('meter_no_primary')->nullable();
            $table->enum('authority', ['user', 'admin', 'supervisor', 'manager', 'customer'])->default('customer');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
