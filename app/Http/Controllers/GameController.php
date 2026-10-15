<?php

namespace App\Http\Controllers;

use App\Events\GameStateUpdated;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GameController extends Controller
{
    /**
     * Selector de rol: punto de entrada al reactor compartido.
     */
    public function index(): View
    {
        return view('game.index');
    }

    /**
     * Pantalla del operador: clicks y sobrecarga.
     */
    public function emisor(): View
    {
        return view('game.emisor');
    }

    /**
     * Pantalla del ingeniero: producción en vivo y mejoras.
     */
    public function receptor(): View
    {
        return view('game.receptor');
    }

    /**
     * Emite un evento de prueba para verificar el canal en tiempo real.
     *
     * Temporal: solo existe mientras el Hito 1 necesita comprobar que el
     * broadcast llega a las dos pantallas. Lo reemplaza el endpoint de clicks
     * (#13) y no debe quedar habilitado fuera de desarrollo.
     */
    public function ping(): Response
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        GameStateUpdated::dispatch([
            'action' => ['type' => 'ping'],
            'updated_at' => now()->toIso8601String(),
        ]);

        return response()->noContent();
    }
}
