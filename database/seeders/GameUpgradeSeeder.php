<?php

namespace Database\Seeders;

use App\Models\GameUpgrade;
use Illuminate\Database\Seeder;

class GameUpgradeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['click', 'generator', 'multiplier'];

        foreach ($types as $type) {
            if (! GameUpgrade::query()->where('type', $type)->exists()) {
                GameUpgrade::query()->create([
                    'type' => $type,
                    'level' => 0,
                ]);
            }
        }
    }
}
