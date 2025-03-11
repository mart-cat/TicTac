<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Session;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/game/{roomId}', [GameController::class, 'show'])->name('game.show');

Route::post('/move', [GameController::class, 'move']);
Route::get('/reset', [GameController::class, 'reset']);
Route::get('/room', [GameController::class, 'room']);
Route::post('/create-room', [GameController::class, 'createRoom'])->name('create.room');
Route::post('/join-room', [GameController::class, 'joinRoom'])->name('join.room');
Route::get('/get-player-symbol', function () {
    return response()->json([
        'player_symbol' => Session::get('player_symbol', 'Неизвестно')
    ]);
});