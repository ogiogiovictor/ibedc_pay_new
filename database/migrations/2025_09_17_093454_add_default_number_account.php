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
            $table->string('default_house_no')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('account_creations', function (Blueprint $table) {
             $table->dropColumn('default_house_no');
        });
    }
};
