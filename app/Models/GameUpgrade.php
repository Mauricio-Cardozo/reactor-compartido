<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameUpgrade extends Model
{
    protected $fillable = [
        'type',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }
}
