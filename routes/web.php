<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('welcome');
});

// Маршруты для управления комнатами
Route::get('/room', [RoomController::class, 'room']);
Route::post('/create-room', [RoomController::class, 'createRoom'])->name('create.room');
Route::post('/join-room', [RoomController::class, 'joinRoom'])->name('join.room');

// Маршруты для управления игрой
Route::get('/game/{roomId}', [GameController::class, 'show'])->name('game.show');
Route::post('/move', [GameController::class, 'move']);
Route::post('/reset', [GameController::class, 'reset']);
Route::get('/game-state', [GameController::class, 'getGameState'])->name('game.state');

// Получение символа игрока
Route::get('/get-player-symbol', function () {
    return response()->json([
        'player_symbol' => Session::get('player_symbol', 'Неизвестно')
    ]);
});
