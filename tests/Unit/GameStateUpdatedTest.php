<?php

namespace Tests\Unit;

use App\Events\GameStateUpdated;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Tests\TestCase;

class GameStateUpdatedTest extends TestCase
{
    public function test_it_broadcasts_now_on_the_reactor_channel(): void
    {
        $event = new GameStateUpdated([]);

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertSame('GameStateUpdated', $event->broadcastAs());

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
        $this->assertSame('reactor', $channels[0]->name);
    }

    public function test_the_public_payload_keeps_only_whitelisted_keys(): void
    {
        $event = new GameStateUpdated([
            'version' => 7,
            'energy' => '12.5000',
            'credits' => '1.2500',
            'action' => ['type' => 'click', 'player' => 'Ana'],
            'updated_at' => '2026-10-15T17:00:00+00:00',
            'correlation_id' => 'abc-123',
            // Datos privados: no deben viajar por el canal público.
            'triggeredById' => 'session-1',
            'triggeredByIp' => '10.0.0.1',
            'ip' => '10.0.0.1',
            'player_name' => 'Ana',
        ]);

        $this->assertSame([
            'version' => 7,
            'energy' => '12.5000',
            'credits' => '1.2500',
            'action' => ['type' => 'click', 'player' => 'Ana'],
            'updated_at' => '2026-10-15T17:00:00+00:00',
            'correlation_id' => 'abc-123',
        ], $event->broadcastWith());

        $this->assertArrayNotHasKey('triggeredById', $event->broadcastWith());
        $this->assertArrayNotHasKey('triggeredByIp', $event->broadcastWith());
    }

    public function test_it_waits_for_the_database_transaction_to_commit(): void
    {
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, new GameStateUpdated([]));
    }
}
