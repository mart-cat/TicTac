<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Room;

class GameController extends Controller
{
    public function __construct()
    {
        if (!session()->has('board')) {
            session([
                'board' => [
                    ['', '', ''],
                    ['', '', ''],
                    ['', '', '']
                ]
            ]);
            session(['turn' => 'X']);
        }
    }

    public function check($board)
    {
        for ($i = 0; $i < 3; $i++) {
            if ($board[$i][0] === $board[$i][1] && $board[$i][1] === $board[$i][2] && $board[$i][0] !== '') {
                return $board[$i][0];
            }
        }
        for ($i = 0; $i < 3; $i++) {
            if ($board[0][$i] === $board[1][$i] && $board[1][$i] === $board[2][$i] && $board[0][$i] !== '') {
                return $board[0][$i];
            }
        }
        if ($board[0][0] === $board[1][1] && $board[1][1] === $board[2][2] && $board[0][0] !== '') {
            return $board[0][0];
        }
        if ($board[0][2] === $board[1][1] && $board[1][1] === $board[2][0] && $board[0][2] !== '') {
            return $board[0][2];
        }
        return null;
    }

    public function move(Request $request)
    {
        try {
            if (!Session::has('room')) {
                return response()->json(['error' => 'Session room ID not set'], 400);
            }

            $room = Room::find(Session::get('room'));
            if (!$room) {
                return response()->json(['error' => 'Room not found'], 404);
            }

            $board = json_decode($room->board, true);
            $row = $request->input('row');
            $col = $request->input('col');

            if (!isset($board[$row]) || !isset($board[$row][$col])) {
                return response()->json(['error' => 'Invalid board coordinates'], 400);
            }

            if ($board[$row][$col] !== '' || $room->turn !== session('player_symbol')) {
                return response()->json(['error' => 'Invalid move or not your turn'], 400);
            }

            $board[$row][$col] = $room->turn;
            $room->turn = $room->turn === 'X' ? 'O' : 'X';

            $winner = $this->check($board);

            DB::transaction(function () use ($room, $board, $winner) {
                $room->board = json_encode($board);
                $room->winner = $winner;
                $room->save();
            });

            return response()->json([
                'board' => $board,
                'turn' => $room->turn,
                'winner' => $winner
            ]);
        } catch (Exception $e) {
            Log::error('Ошибка при обработке хода: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function show()
    {
        $roomId = session('room');
        $room = Room::find($roomId);

        return view('game', [
            'board' => json_decode($room->board, true),
            'turn' => $room->turn,
            'winner' => $room->winner,
            'room' => $room,
            'playerSymbol' => session('player_symbol'),
        ]);
    }

    public function reset(Request $request)
    {
        try {
            $roomId = session('room');
            $room = Room::find($roomId);

            if (!$room) {
                return response()->json(['error' => 'Комната не найдена'], 404);
            }

            $room->board = json_encode([['', '', ''], ['', '', ''], ['', '', '']]);
            $room->turn = 'X';
            $room->winner = null;
            $room->save();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Ошибка при сбросе игры: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка сервера'], 500);
        }
    }

    public function getGameState()
    {
        $roomId = Session::get('room');
        $room = Room::find($roomId);

        if (!$room) {
            return response()->json(['error' => 'Комната не найдена'], 404);
        }

        return response()->json([
            'board' => json_decode($room->board, true),
            'turn' => $room->turn,
            'winner' => $room->winner ?? null,
        ]);
    }
}
