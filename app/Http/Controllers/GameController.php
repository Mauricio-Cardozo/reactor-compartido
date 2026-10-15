<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClickRequest;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(private readonly GameService $game) {}

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
     * Aplica un lote de clicks y devuelve el estado ya persistido.
     */
    public function click(ClickRequest $request): JsonResponse
    {
        return response()->json($this->game->click((int) $request->validated('clicks')));
    }

    /**
     * Estado actual: lo consultan las pantallas al entrar y al reconectarse
     * (RF-09).
     */
    public function state(): JsonResponse
    {
        return response()->json($this->game->state());
    }
}
