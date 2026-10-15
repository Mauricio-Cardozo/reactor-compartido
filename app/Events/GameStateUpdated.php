<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Actualización del reactor que ya fue persistida.
 *
 * Representa solo estado confirmado: no se usa para intentos, rechazos ni
 * límites. Como el canal `reactor` es público, el snapshot se filtra a
 * PUBLIC_KEYS: la IP y el id de sesión de quien disparó la acción viajan
 * únicamente a los logs y a `game_actions`, nunca al navegador.
 */
class GameStateUpdated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Claves que pueden mostrarse a cualquiera que escuche el canal público.
     *
     * @var list<string>
     */
    public const PUBLIC_KEYS = [
        'version',
        'energy',
        'credits',
        'click_power',
        'multiplier_level',
        'generator_level',
        'overload',
        'action',
        'updated_at',
        'correlation_id',
    ];

    /**
     * @var array<string, mixed>
     */
    public readonly array $snapshot;

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function __construct(array $snapshot)
    {
        $this->snapshot = array_intersect_key($snapshot, array_flip(self::PUBLIC_KEYS));
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('reactor')];
    }

    public function broadcastAs(): string
    {
        return 'GameStateUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->snapshot;
    }
}
