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
            $table->string('dtm_comment')->nullable();
            $table->string('billing_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('upload_houses', function (Blueprint $table) {
            $table->dropColumn('dtm_comment');
            $table->dropColumn('billing_comment');
        });
    }
};
