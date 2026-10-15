<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_states', function (Blueprint $table) {
            $table->id();

            // Energía y créditos con escala 4: el redondeo se decide en el
            // servicio, nunca en la columna (RNF-08).
            $table->decimal('energy', 20, 4)->default(0);
            $table->decimal('credits', 20, 4)->default(0);

            $table->unsignedInteger('click_power')->default(1);
            $table->unsignedInteger('multiplier_level')->default(0);

            // Ventana de sobrecarga: inicio, fin y próxima disponibilidad.
            $table->timestamp('overload_started_at')->nullable();
            $table->timestamp('overload_until')->nullable();
            $table->timestamp('overload_ready_at')->nullable();

            // Frontera del último tick idle acreditado (Hito 4).
            $table->timestamp('last_tick_at')->nullable();

            // Se incrementa en cada actualización del estado (RNF-09).
            $table->unsignedBigInteger('version')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_states');
    }
};
