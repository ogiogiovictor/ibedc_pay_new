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
            $table->string('cac_slip')->nullable();
            $table->string('editable')->default(0);
            $table->string('organisational_name')->nullable();
            $table->string('cac_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('continue_account_creations', function (Blueprint $table) {
             $table->dropColumn('cac_slip');
             $table->dropColumn('editable');
             $table->dropColumn('organisational_name');
            $table->dropColumn('cac_number');
        });
    }
};
