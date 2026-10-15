<?php

namespace Tests\Feature;

use App\Events\GameStateUpdated;
use App\Models\GameState;
use Database\Seeders\GameStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClickEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // El cliente de tests no reenvía cookies en peticiones JSON, así que
        // sin esto cada request sería un jugador nuevo y el throttle nunca
        // se dispararía.
        $this->withCredentials = true;

        $this->seed(GameStateSeeder::class);
    }

    public function test_a_click_batch_returns_the_persisted_state(): void
    {
        $response = $this->postJson(route('game.click'), ['clicks' => 3]);

        $response->assertOk()
            ->assertJsonPath('energy', '3.0000')
            ->assertJsonPath('credits', '0.3000')
            ->assertJsonPath('version', 2)
            ->assertJsonPath('action.type', 'click')
            ->assertJsonPath('action.clicks', 3);

        $this->assertSame('3.0000', GameState::query()->firstOrFail()->energy);
    }

    public function test_it_ignores_resources_sent_by_the_client(): void
    {
        $response = $this->postJson(route('game.click'), [
            'clicks' => 1,
            'energy' => '999.0000',
            'credits' => '999.0000',
            'version' => 500,
        ]);

        $response->assertOk()
            ->assertJsonPath('energy', '1.0000')
            ->assertJsonPath('credits', '0.1000')
            ->assertJsonPath('version', 2);
    }

    public function test_it_broadcasts_the_state_after_persisting_it(): void
    {
        Event::fake([GameStateUpdated::class]);

        $this->postJson(route('game.click'), ['clicks' => 2])->assertOk();

        Event::assertDispatched(GameStateUpdated::class, function (GameStateUpdated $event): bool {
            return $event->broadcastWith()['energy'] === '2.0000'
                && $event->broadcastWith()['version'] === 2;
        });
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'sin clicks' => [['clicks' => null]],
            'cero clicks' => [['clicks' => 0]],
            'clicks negativos' => [['clicks' => -3]],
            'clicks no enteros' => [['clicks' => 2.5]],
            'lote demasiado grande' => [['clicks' => 16]],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('invalidPayloads')]
    public function test_it_rejects_invalid_batches_without_broadcasting(array $payload): void
    {
        Event::fake([GameStateUpdated::class]);

        $this->postJson(route('game.click'), $payload)->assertStatus(422);

        Event::assertNotDispatched(GameStateUpdated::class);
        $this->assertSame('0.0000', GameState::query()->firstOrFail()->energy);
    }

    public function test_it_throttles_clicks_per_second_per_player(): void
    {
        $this->keepSession($this->postJson(route('game.click'), ['clicks' => 15])->assertOk());

        $this->postJson(route('game.click'), ['clicks' => 1])
            ->assertStatus(429)
            ->assertHeader('Retry-After');

        // El estado no cambió por el lote rechazado.
        $this->assertSame('15.0000', GameState::query()->firstOrFail()->energy);
    }

    public function test_the_throttle_counts_clicks_not_requests(): void
    {
        // Cinco lotes de tres clicks son quince clicks: un límite por petición
        // habría dejado pasar quince.
        for ($batch = 0; $batch < 5; $batch++) {
            $this->keepSession($this->postJson(route('game.click'), ['clicks' => 3])->assertOk());
        }

        $this->postJson(route('game.click'), ['clicks' => 1])->assertStatus(429);
    }

    public function test_an_invalid_batch_does_not_consume_the_whole_window(): void
    {
        $this->keepSession($this->postJson(route('game.click'), ['clicks' => 99])->assertStatus(422));

        $this->postJson(route('game.click'), ['clicks' => 1])->assertOk();
    }

    public function test_the_throttle_releases_the_player_after_a_second(): void
    {
        $this->keepSession($this->postJson(route('game.click'), ['clicks' => 15])->assertOk());
        $this->postJson(route('game.click'), ['clicks' => 1])->assertStatus(429);

        $this->travel(1)->seconds();

        $this->postJson(route('game.click'), ['clicks' => 1])->assertOk();
    }

    public function test_the_state_endpoint_returns_the_public_snapshot(): void
    {
        $response = $this->getJson(route('game.state'));

        $response->assertOk()
            ->assertJsonPath('energy', '0.0000')
            ->assertJsonPath('credits', '0.0000')
            ->assertJsonPath('click_power', 1)
            ->assertJsonPath('multiplier_level', 0)
            ->assertJsonPath('action.type', 'sync');

        $snapshot = $response->json();

        $this->assertArrayNotHasKey('ip', $snapshot);
        $this->assertArrayNotHasKey('triggeredByIp', $snapshot);
        $this->assertArrayNotHasKey('triggeredById', $snapshot);
    }

    public function test_the_temporary_ping_endpoint_is_gone(): void
    {
        $this->post('/game/ping')->assertNotFound();
    }

    /**
     * Copia las cookies de la respuesta al siguiente request para mantener la
     * misma sesión (y por lo tanto el mismo jugador).
     */
    private function keepSession(TestResponse $response): void
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
        }
    }
}
