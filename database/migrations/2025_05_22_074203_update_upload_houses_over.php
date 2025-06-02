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
            $table->string('account_no')->nullable();
            $table->string('status')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('upload_houses', function (Blueprint $table) {
             $table->dropColumn('account_no');
            $table->dropColumn('status');

        });
    }
};
