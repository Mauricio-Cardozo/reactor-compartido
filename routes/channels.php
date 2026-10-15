<?php

use Illuminate\Support\Facades\Broadcast;

// El canal `reactor` es público: se declara en App\Events\GameStateUpdated y
// no necesita autorización porque nada privado viaja por él.

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
