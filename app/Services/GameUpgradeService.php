<?php

namespace App\Services;

use App\Events\GameStateUpdated;
use App\Models\GameState;
use App\Models\GameUpgrade;
use Illuminate\Support\Facades\DB;

class GameUpgradeService
{
    public function upgrade(string $type): array
    {
        return DB::transaction(function () use ($type): array {
            $state = GameState::query()->lockForUpdate()->firstOrFail();
            $upgrade = GameUpgrade::query()->where('type', $type)->lockForUpdate()->first();

            if (! $upgrade) {
                $upgrade = GameUpgrade::query()->create(['type' => $type, 'level' => 0]);
            }

            $cost = $this->cost($type, $upgrade->level);
            if (bccomp($state->credits, $cost, 4) < 0) {
                throw new \DomainException('Créditos insuficientes');
            }

            $state->credits = bcsub($state->credits, $cost, 4);

            if ($type === 'click') {
                $state->click_power += (int) config('game.upgrades.click.power_per_level');
            } elseif ($type === 'multiplier') {
                $state->multiplier_level += 1;
            } elseif ($type === 'generator') {
                $upgrade->level += 1;
                $upgrade->save();
            }

            if ($type !== 'generator') {
                $upgrade->level += 1;
                $upgrade->save();
            }

            $state->version++;
            $state->save();

            $snapshot = app(GameService::class)->snapshot($state, [
                'type' => 'upgrade',
                'upgrade' => $type,
                'level' => $upgrade->level,
            ]);

            GameStateUpdated::dispatch($snapshot);

            return $snapshot;
        });
    }

    private function cost(string $type, int $level): string
    {
        $base = (string) config('game.upgrades.'.$type.'.base_cost');
        $factor = (string) config('game.upgrades.'.$type.'.cost_factor');
        $scale = (int) config('game.precision.scale');
        $raw = bcmul($base, bcpow($factor, (string) $level, 10), 10);

        return bcadd($raw, '0.9999', 0);
    }
}
