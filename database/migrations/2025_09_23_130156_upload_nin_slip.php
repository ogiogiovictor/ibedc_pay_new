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
         Schema::table('continue_account_creations', function (Blueprint $table) {
            $table->string('nin_slip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('continue_account_creations', function (Blueprint $table) {
             $table->dropColumn('nin_slip');
        });
    }
};
