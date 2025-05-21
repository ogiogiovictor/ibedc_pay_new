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
         Schema::table('account_creations', function (Blueprint $table) {
            $table->string('region')->nullable();
            $table->string('business_hub')->nullable();
            $table->string('service_center')->nullable();
            $table->string('dss')->nullable();
            $table->string('comment')->nullable();
            $table->string('meter_no')->nullable();
            $table->string('meter_book')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('account_creations', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('business_hub');
            $table->dropColumn('service_center');
            $table->dropColumn('dss');
            $table->dropColumn('comment');
            $table->dropColumn('meter_no');
            $table->dropColumn('meter_book');
        });
    }
};
