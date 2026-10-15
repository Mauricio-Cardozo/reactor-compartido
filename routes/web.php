<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(GameController::class)->group(function () {
    Route::get('/', 'index')->name('game.index');
    Route::get('/emisor', 'emisor')->name('game.emisor');
    Route::get('/receptor', 'receptor')->name('game.receptor');
});
