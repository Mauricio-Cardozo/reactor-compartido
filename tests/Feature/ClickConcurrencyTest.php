<?php

namespace Tests\Feature;

use App\Models\GameState;
use Database\Seeders\GameStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClickConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GameStateSeeder::class);
    }

    /**
     * @group concurrency
     */
    public function test_lockForUpdate_prevents_lost_updates(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Este test requiere MySQL/MariaDB para probar lockForUpdate.');
        }

        $orig = GameState::query()->firstOrFail();
        $calls = 5;
        $totalClicks = $calls * 3;
        $expectedEnergy = bcmul((string) ($orig->click_power * $totalClicks), bcadd('1', bcmul(config('game.multiplier.bonus_per_level'), (string) $orig->multiplier_level, 4), 4), 4);
        $expectedCredits = bcmul($expectedEnergy, config('game.credits.rate'), 4);

        $pids = [];
        $tmpDir = sys_get_temp_dir();
        $script = storage_path('framework/testing/concurrency_click.php');

        file_put_contents($script, <<<'PHP'
<?php

require __DIR__.'/../../../vendor/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\GameService;
use Illuminate\Support\Facades\Log;

$service = app(GameService::class);
$service->click(3);
PHP);

        for ($i = 0; $i < $calls; $i++) {
            $pids[] = pcntl_fork();
            if ($pids[$i] === 0) {
                passthru('php '.escapeshellarg($script));
                exit(0);
            }
        }

        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertSame(0, pcntl_wexitstatus($status) ?? -1);
        }

        $final = GameState::query()->firstOrFail();
        $this->assertSame($expectedEnergy, $final->energy);
        $this->assertSame($expectedCredits, $final->credits);
        $this->assertSame(1 + $calls, $final->version);
    }
}
