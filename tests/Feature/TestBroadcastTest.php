<?php

namespace Tests\Feature;

use App\Events\GameStateUpdated;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TestBroadcastTest extends TestCase
{
    public function test_the_test_endpoint_broadcasts_a_game_state_updated_event(): void
    {
        Event::fake([GameStateUpdated::class]);

        $this->post(route('game.ping'))->assertNoContent();

        Event::assertDispatched(GameStateUpdated::class, function (GameStateUpdated $event): bool {
            return $event->broadcastWith()['action']['type'] === 'ping';
        });
    }

    public function test_the_test_endpoint_is_hidden_outside_local_and_testing(): void
    {
        // Fuera de testing se activa el middleware de CSRF: lo desactivamos
        // para poder comprobar el 404 del guard en sí.
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->app['env'] = 'production';

        $this->post(route('game.ping'))->assertNotFound();
    }
}
