<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Estado único del reactor compartido.
 *
 * El multiplicador permanente y la producción por segundo se derivan de los
 * niveles: no se guardan como valores mutables que puedan desincronizarse.
 */
class GameState extends Model
{
    protected $fillable = [
        'energy',
        'credits',
        'click_power',
        'multiplier_level',
        'overload_started_at',
        'overload_until',
        'overload_ready_at',
        'last_tick_at',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'energy' => 'decimal:4',
            'credits' => 'decimal:4',
            'click_power' => 'integer',
            'multiplier_level' => 'integer',
            'version' => 'integer',
            'overload_started_at' => 'datetime',
            'overload_until' => 'datetime',
            'overload_ready_at' => 'datetime',
            'last_tick_at' => 'datetime',
        ];
    }
}
