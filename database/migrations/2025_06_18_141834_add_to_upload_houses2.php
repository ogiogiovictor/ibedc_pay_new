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
            $table->string('nearest_bustop')->nullable();
            $table->string('lga')->nullable();
            $table->string('landmark')->nullable();
            $table->enum('type_of_premise', ['2Bedroom', '1Bedroom', '3Bedroom', '4Bedroom', 'Tenement House', 'Single Room', 'Shop(s)', 'Boys Qtrs', 'Block of flats',
            'Duplex', 'Mansion', 'Charlet'])->default("others");
            $table->enum('use_of_premise', ['Residential', 'Commercial', 'Special', 'Industrial', 'Others'])->default("Residential");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('upload_houses', function (Blueprint $table) {
            $table->dropColumn('nearest_bustop');
            $table->dropColumn('lga');
            $table->dropColumn('landmark');
            $table->dropColumn('type_of_premise');
            $table->dropColumn('use_of_premise');

        });
    }
};
