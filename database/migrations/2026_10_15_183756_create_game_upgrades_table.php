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
        Schema::create('game_upgrades', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->unsignedInteger('level')->default(0);
            $table->timestamps();

            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_upgrades');
    }
};
