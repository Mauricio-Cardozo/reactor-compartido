<?php

namespace App\Http\Controllers;

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
}
