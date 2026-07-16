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
         Schema::table('upload_houses', function (Blueprint $table) {
            $table->string('paid_for_meter')->nullable();
            $table->string('mapID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('upload_houses', function (Blueprint $table) {
             $table->dropColumn('paid_for_meter');
             $table->dropColumn('mapID');
        });
    }
};
