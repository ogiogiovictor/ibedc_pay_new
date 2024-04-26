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
        Schema::create('main_menu', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name')->nullable();
            $table->string('menu_url')->nullable();
            $table->string('menu_status')->nullable();
            $table->string('menu_icon')->nullable();

            $table->index('menu_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_menu');
    }
};
