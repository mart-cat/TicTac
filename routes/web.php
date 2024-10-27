<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/game', [GameController::class, 'show']);
Route::post('/move', [GameController::class, 'move']);
Route::get('/reset', [GameController::class, 'reset']);
   
