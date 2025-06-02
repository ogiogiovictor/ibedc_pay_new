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
         Schema::table('users', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('business_hub')->nullable();
             $table->string('sc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sc');
             $table->dropColumn('region');
              $table->dropColumn('business_hub');
        });
    }
};
