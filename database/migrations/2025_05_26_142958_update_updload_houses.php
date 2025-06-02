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
            $table->string('comment')->nullable();
            $table->string('validated_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('billing_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_houses', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->dropColumn('validated_by');
            $table->dropColumn('approved_by');
            $table->dropColumn('billing_id');
        });
    }
};
