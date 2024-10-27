<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{protected $board;
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
    public function check($board) {
        // Проверка строк
        for ($i = 0; $i < 3; $i++) {
            if ($board[$i][0] === $board[$i][1] && $board[$i][1] === $board[$i][2] && $board[$i][0] !== '') {
                return $board[$i][0];
            }
        }
        // Проверка столбцов
        for ($i = 0; $i < 3; $i++) {
            if ($board[0][$i] === $board[1][$i] && $board[1][$i] === $board[2][$i] && $board[0][$i] !== '') {
                return $board[0][$i];
            }
        }
        // Проверка диагоналей
        if ($board[0][0] === $board[1][1] && $board[1][1] === $board[2][2] && $board[0][0] !== '') {
            return $board[0][0];
        }
        if ($board[0][2] === $board[1][1] && $board[1][1] === $board[2][0] && $board[0][2] !== '') {
            return $board[0][2];
        }
        // Если нет победителя
        return null;
    }
    
    public function move(Request $req) {    
        try {
            $row = $req->input('row');
            $col = $req->input('col');
            
            $board = session('board');
            $whoPlay = session('whoPlay');
            $winner = session('winner');

            if ($winner){
                return response()->json([
                    'board' => $board,
                    'whoPlay' => $whoPlay,
                    'winner' => $winner
                ]);
            }
    
            // Проверяем пуста ли ячейка
            if ($board[$row][$col] === '') {
                $board[$row][$col] = $whoPlay; // Устанавливаем
                $whoPlay = ($whoPlay === 'X') ? 'O' : 'X'; // Меняем игрока
    
                // Проверяем победителя
                $winner = $this->check($board);
                
                // Сохраняем 
                session(['board' => $board, 'whoPlay' => $whoPlay]);
                if ($winner) {
                    session(['winner' => $winner]);
                }
            }
            
            return response()->json([
                'board' => $board,
                'whoPlay' => $whoPlay,
                'winner' => session('winner', null),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in move: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
    public function show() {
        $board = session('board');
        $whoPlay = session('whoPlay');
        $winner = session('winner', null);
        
        return view('game', ['board' => $board, 'whoPlay' => $whoPlay, 'winner' => $winner]);
    }
    
    public function reset() {    
        session()->forget(['board', 'whoPlay', 'winner']);
        return redirect()->route('game.show');
    }
}