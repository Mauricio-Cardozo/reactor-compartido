<?php

namespace Database\Seeders;

use App\Models\GameState;
use Illuminate\Database\Seeder;

/**
 * El MVP juega con una única fila de estado compartido.
 */
class GameStateSeeder extends Seeder
{
    public function run(): void
    {
        if (GameState::query()->exists()) {
            return;
        }

        GameState::query()->create([
            'click_power' => config('game.click.power'),
            'last_tick_at' => now(),
        ]);
    }
}
