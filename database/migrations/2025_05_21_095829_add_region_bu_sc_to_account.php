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
            $table->string('region')->nullable();
            $table->string('house_no')->nullable();
            $table->string('full_address')->nullable();
            $table->string('business_hub')->nullable();
            $table->string('service_center')->nullable();
            $table->string('dss')->nullable();
            $table->string('lecan_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_houses', function (Blueprint $table) {
             $table->dropColumn('region');
            $table->string('house_no');
            $table->string('full_address');
             $table->dropColumn('business_hub');
             $table->dropColumn('service_center');
              $table->dropColumn('dss');
             $table->dropColumn('lecan_link');

        });
    }
};
