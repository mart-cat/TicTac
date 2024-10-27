<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function __construct()
    {
        if (!session()->has('board')) {
            session(['board' => [
                ['', '', ''],
                ['', '', ''],
                ['', '', '']
            ]]);
            session(['whoPlay' => 'X']);
        }
    }

    public function show()
    {
        // Получаем текущее состояние игры из сессии
        $board = session('board');
        $whoPlay = session('whoPlay');

        return view('game', ['board' => $board, 'whoPlay' => $whoPlay]);
    }

    public function move(Request $req)
    {    
        try {
            $row = $req->input('row');
            $col = $req->input('col');
            
            $board = session('board');
            $whoPlay = session('whoPlay');

            // Проверяем пуста ли ячейка
            if ($board[$row][$col] === '') {
                $board[$row][$col] = $whoPlay; // Устанавливаем
                $whoPlay = ($whoPlay === 'X') ? 'O' : 'X'; // Меняем игрока

                // Сохраняем 
                session(['board' => $board, 'whoPlay' => $whoPlay]);
            }

            // Возвращаем 
            return response()->json([
                'board' => $board,
                'whoPlay' => $whoPlay
            ]);
        } catch (\Exception $e) {
            Log::error('Error in move: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function reset()
    {    
        session() -> forget(['board','whoPlay']);
        return redirect()-> route ('game.show');

    }
}