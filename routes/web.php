<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(GameController::class)->group(function () {
    Route::get('/', 'index')->name('game.index');
    Route::get('/emisor', 'emisor')->name('game.emisor');
    Route::get('/receptor', 'receptor')->name('game.receptor');
});

Route::controller(GameController::class)->group(function () {
    Route::get('/game/state', 'state')->name('game.state');
    Route::post('/game/click', 'click')->middleware('throttle.clicks')->name('game.click');
});
