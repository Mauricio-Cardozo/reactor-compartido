<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tope de clicks por segundo y por jugador (RNF-03).
 *
 * El límite se cuenta en clicks, no en peticiones: agrupar clicks en el
 * cliente no puede servir para saltarse el tope. Por eso se cobra el tamaño
 * del lote. `RateLimiter::hit()` de Laravel siempre suma 1, así que la
 * ventana se lleva directamente en el cache, con la misma forma que usa el
 * framework (contador + marca de vencimiento).
 *
 * Un lote que la validación va a rechazar (no entero, fuera de rango) cuesta
 * 1: no puede producir nada, pero tampoco es gratis golpear el endpoint.
 */
class ThrottleClicks
{
    /**
     * Duración de la ventana, en segundos.
     */
    private const WINDOW = 1;

    public function handle(Request $request, Closure $next): Response
    {
        $limit = (int) config('game.limits.clicks_per_second');
        $cost = $this->cost($request, $limit);
        $key = $this->key($request);

        if ($this->used($key) + $cost > $limit) {
            return response()->json(
                ['message' => 'Demasiados clicks por segundo: esperá un instante.'],
                Response::HTTP_TOO_MANY_REQUESTS,
            )->header('Retry-After', (string) $this->retryAfter($key));
        }

        $this->charge($key, $cost);

        return $next($request);
    }

    /**
     * Clicks que se cobran por este lote.
     */
    private function cost(Request $request, int $limit): int
    {
        $max = (int) config('game.limits.clicks_per_request');
        $clicks = $request->input('clicks');

        if (! is_int($clicks) || $clicks < 1 || $clicks > $max) {
            return 1;
        }

        return min($clicks, $limit);
    }

    /**
     * El id de sesión se pseudonimiza: es la identidad del jugador en el MVP
     * (5.5) y no hace falta el valor real para contar.
     */
    private function key(Request $request): string
    {
        return 'clicks:'.hash('sha256', $request->session()->getId());
    }

    private function used(string $key): int
    {
        return (int) Cache::get($key, 0);
    }

    /**
     * Suma el lote al contador. La ventana arranca con la primera carga y no
     * se renueva, así un jugador sostenido no la estira indefinidamente.
     */
    private function charge(string $key, int $cost): void
    {
        Cache::add($key.':until', now()->addSeconds(self::WINDOW)->getTimestamp(), self::WINDOW);
        Cache::add($key, 0, self::WINDOW);
        Cache::increment($key, $cost);
    }

    private function retryAfter(string $key): int
    {
        $until = (int) Cache::get($key.':until', 0);

        return max(1, $until - now()->getTimestamp());
    }
}
