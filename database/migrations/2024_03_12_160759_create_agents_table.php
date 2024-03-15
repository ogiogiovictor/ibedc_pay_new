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
        Schema::create('agency', function (Blueprint $table) {
            $table->id();
            $table->string('agent_code')->default(0);
            $table->string('agent_name')->default(0);
            $table->string('agent_email')->default(0);
            $table->string('agent_official_phone')->default(0);
            $table->string('no_of_agents')->default(0);
            $table->enum('status', ['0', '1'])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};
