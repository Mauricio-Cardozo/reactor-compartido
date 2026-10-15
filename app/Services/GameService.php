<?php

namespace App\Services;

use App\Events\GameStateUpdated;
use App\Models\GameState;
use Illuminate\Support\Facades\DB;

/**
 * Toda la lógica del juego.
 *
 * Es la única autoridad sobre los recursos: el navegador solo pide acciones
 * y dibuja el snapshot que se le devuelve (RNF-01). Los cálculos económicos
 * se hacen con BCMath y se redondean acá, en un solo lugar (RNF-08).
 */
class GameService
{
    /**
     * Aplica un lote de clicks y publica el estado ya persistido.
     *
     * @param  int  $clicks  cantidad de clicks del lote (>= 1, ya validada por el Form Request)
     */
    public function click(int $clicks): GameState
    {
        $state = DB::transaction(function () use ($clicks): GameState {
            // El bloqueo es lo que evita que dos jugadores acrediten el mismo
            // intervalo a la vez (RNF-02). Todas las acciones pasan por acá.
            $state = $this->lockState();

            $gained = $this->clicksEnergy($state, $clicks);

            $state->energy = $this->add($state->energy, $gained);
            $state->credits = $this->add($state->credits, $this->toCredits($gained));
            $state->version++;
            $state->save();

            return $state;
        });

        // Fuera de la transacción: ningún cliente puede ver un estado que
        // después se revierta (5.1).
        GameStateUpdated::dispatch($this->snapshot($state, [
            'type' => 'click',
            'clicks' => $clicks,
        ]));

        return $state;
    }

    /**
     * Snapshot público del reactor: es lo que viaja por el canal y lo que
     * devuelven los endpoints de estado.
     *
     * @param  array<string, mixed>  $action
     * @return array<string, mixed>
     */
    public function snapshot(GameState $state, array $action): array
    {
        return [
            'version' => $state->version,
            'energy' => $state->energy,
            'credits' => $state->credits,
            'click_power' => $state->click_power,
            'multiplier_level' => $state->multiplier_level,
            'action' => $action,
            'updated_at' => $state->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Bloquea la fila única de estado hasta el commit de la transacción.
     */
    private function lockState(): GameState
    {
        return GameState::query()->lockForUpdate()->firstOrFail();
    }

    /**
     * Energía de un lote: click_power × clicks × multiplicador permanente.
     */
    private function clicksEnergy(GameState $state, int $clicks): string
    {
        $perClick = bcmul((string) $state->click_power, $this->permanentMultiplier($state), $this->scale());

        return bcmul($perClick, (string) $clicks, $this->scale());
    }

    /**
     * El multiplicador permanente se deriva del nivel: no se guarda mutable.
     */
    private function permanentMultiplier(GameState $state): string
    {
        $bonus = bcmul((string) config('game.multiplier.bonus_per_level'), (string) $state->multiplier_level, $this->scale());

        return bcadd('1', $bonus, $this->scale());
    }

    /**
     * Créditos ganados por la energía producida (RF-03).
     */
    private function toCredits(string $energy): string
    {
        return bcmul($energy, (string) config('game.credits.rate'), $this->scale());
    }

    private function add(string $current, string $delta): string
    {
        return bcadd($current, $delta, $this->scale());
    }

    private function scale(): int
    {
        return (int) config('game.precision.scale');
    }
}
