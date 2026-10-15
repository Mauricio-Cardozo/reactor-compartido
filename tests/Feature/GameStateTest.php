<?php

namespace Tests\Feature;

use App\Models\GameState;
use Database\Seeders\GameStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_balance_lives_in_configuration(): void
    {
        $this->assertSame(1, config('game.click.power'));
        $this->assertSame('0.1', config('game.credits.rate'));
        $this->assertSame(15, config('game.limits.clicks_per_second'));
    }

    public function test_it_starts_empty_and_with_a_first_version(): void
    {
        $state = GameState::query()->create([])->refresh();

        $this->assertSame('0.0000', $state->energy);
        $this->assertSame('0.0000', $state->credits);
        $this->assertSame(1, $state->click_power);
        $this->assertSame(0, $state->multiplier_level);
        $this->assertSame(1, $state->version);
        $this->assertNull($state->overload_started_at);
        $this->assertNull($state->overload_until);
        $this->assertNull($state->overload_ready_at);
    }

    public function test_the_decimal_cast_keeps_four_places_as_strings(): void
    {
        $state = GameState::query()->create([
            'energy' => '12.345678',
            'credits' => '0.1',
        ])->refresh();

        $this->assertSame('12.3457', $state->energy);
        $this->assertSame('0.1000', $state->credits);
    }

    public function test_the_seeder_creates_exactly_one_state(): void
    {
        $this->seed(GameStateSeeder::class);
        $this->seed(GameStateSeeder::class);

        $this->assertSame(1, GameState::query()->count());

        $state = GameState::query()->firstOrFail();

        $this->assertSame(config('game.click.power'), $state->click_power);
        $this->assertNotNull($state->last_tick_at);
    }
}
