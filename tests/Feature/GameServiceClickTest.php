<?php

namespace Tests\Feature;

use App\Events\GameStateUpdated;
use App\Models\GameState;
use App\Services\GameService;
use Database\Seeders\GameStateSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GameServiceClickTest extends TestCase
{
    use RefreshDatabase;

    private GameService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GameStateSeeder::class);
        $this->service = app(GameService::class);
    }

    public function test_a_click_batch_adds_energy_and_credits(): void
    {
        $this->service->click(3);

        $this->assertSame('3.0000', $this->state()->energy);
        $this->assertSame('0.3000', $this->state()->credits);
    }

    public function test_ten_energy_are_exactly_one_credit(): void
    {
        $this->service->click(4);
        $this->service->click(6);

        $this->assertSame('10.0000', $this->state()->energy);
        $this->assertSame('1.0000', $this->state()->credits);
    }

    public function test_credit_fractions_survive_several_actions(): void
    {
        for ($click = 0; $click < 9; $click++) {
            $this->service->click(1);
        }

        $this->assertSame('9.0000', $this->state()->energy);
        $this->assertSame('0.9000', $this->state()->credits);
    }

    public function test_the_version_grows_with_every_update(): void
    {
        $this->assertSame(1, $this->state()->version);

        $this->service->click(1);
        $this->assertSame(2, $this->state()->version);

        $this->service->click(2);
        $this->assertSame(3, $this->state()->version);
    }

    public function test_the_permanent_multiplier_is_additive(): void
    {
        $this->state()->update(['multiplier_level' => 5]);

        $this->service->click(1);

        // Cinco niveles dan ×1,5, no ×1,61 compuesto.
        $this->assertSame('1.5000', $this->state()->energy);
    }

    public function test_the_click_power_multiplies_every_click(): void
    {
        $this->state()->update(['click_power' => 4]);

        $this->service->click(3);

        $this->assertSame('12.0000', $this->state()->energy);
        $this->assertSame('1.2000', $this->state()->credits);
    }

    public function test_it_broadcasts_the_state_it_just_persisted(): void
    {
        Event::fake([GameStateUpdated::class]);

        $this->service->click(2);

        Event::assertDispatched(GameStateUpdated::class, function (GameStateUpdated $event): bool {
            $snapshot = $event->broadcastWith();

            return $snapshot['energy'] === '2.0000'
                && $snapshot['credits'] === '0.2000'
                && $snapshot['version'] === 2
                && $snapshot['action'] === ['type' => 'click', 'clicks' => 2];
        });
    }

    public function test_it_does_not_broadcast_when_the_action_fails(): void
    {
        Event::fake([GameStateUpdated::class]);
        GameState::query()->delete();

        $this->assertThrows(fn () => $this->service->click(1), ModelNotFoundException::class);

        Event::assertNotDispatched(GameStateUpdated::class);
    }

    private function state(): GameState
    {
        return GameState::query()->firstOrFail();
    }
}
