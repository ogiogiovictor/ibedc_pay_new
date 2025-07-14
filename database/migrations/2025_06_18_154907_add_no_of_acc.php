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
                $table->string('no_of_account_apply_for')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('continue_account_creations', function (Blueprint $table) {
            $table->dropColumn('no_of_account_apply_for');

        });
    }
};
