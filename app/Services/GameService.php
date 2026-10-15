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
     * @return array<string, mixed> snapshot público que también se emite por el canal
     */
    public function click(int $clicks): array
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

        return $this->publish($state, [
            'type' => 'click',
            'clicks' => $clicks,
        ]);
    }

    /**
     * Estado actual del reactor, listo para mostrar.
     *
     * @return array<string, mixed>
     */
    public function state(): array
    {
        return $this->snapshot($this->currentState(), ['type' => 'sync']);
    }

    /**
     * Snapshot público del reactor: lo devuelven los endpoints y es lo que
     * viaja por el canal.
     *
     * @param  array<string, mixed>  $action
     * @return array<string, mixed>
     */
    private function snapshot(GameState $state, array $action): array
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
     * Emite el estado ya persistido, nunca desde dentro de la transacción:
     * si fallara después del broadcast, los clientes verían un estado que no
     * se guardó (5.1).
     *
     * @param  array<string, mixed>  $action
     * @return array<string, mixed>
     */
    private function publish(GameState $state, array $action): array
    {
        $snapshot = $this->snapshot($state, $action);

        GameStateUpdated::dispatch($snapshot);

        return $snapshot;
    }

    private function currentState(): GameState
    {
        return GameState::query()->firstOrFail();
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
