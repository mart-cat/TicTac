<?php

namespace App\Http\Controllers;

use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Room;
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
            session(['turn' => 'X']);
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
    
            // Декодируем текущее состояние доски
            $board = json_decode($room->board, true);
            $row = $request->input('row');
            $col = $request->input('col');
    
            // Проверка, что полученные данные корректны
            if (!isset($board[$row]) || !isset($board[$row][$col])) {
                return response()->json(['error' => 'Invalid board coordinates'], 400);
            }
    
            // Проверяем, что ход делается в пустую ячейку и по очереди
            if ($board[$row][$col] !== '' || $room->turn !== session('player_symbol')) {
                return response()->json(['error' => 'Invalid move or not your turn'], 400);
            }
    
            // Выполняем ход
            $board[$row][$col] = $room->turn;
    
            // Меняем очередь хода
            $room->turn = $room->turn === 'X' ? 'O' : 'X';
    
            // Проверяем победителя
            $winner = $this->check($board);
    
            // Сохраняем изменения в БД внутри транзакции
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
    
    
    public function show() {
    $roomId = session('room'); // Получаем ID комнаты из сессии
    $room = Room::find($roomId);

    // Декодируем поле board из JSON в массив
    $board = json_decode($room->board, true);
    $turn = $room->turn;
    $winner = $room->winner;
    $playerSymbol = session('player_symbol'); // Получаем символ игрока из сессии
    
    return view('game', [
        'board' => $board,
        'turn' => $turn,
        'winner' => $winner,
        'room' => $room, // Передаем объект комнаты в представление
        'playerSymbol' => $playerSymbol, // Передаем символ игрока в представление
    ]);
}

    

    public function room() {
        return view('room');
    }
    
    public function reset() {    
        session()->forget(['board', 'whoPlay', 'winner']);
        return redirect()->route('game.show');
    }

    public function createRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:rooms',  // Убедитесь, что имя комнаты уникально
            'password' => 'required',
        ]);
    
        $sessionId = session()->getId(); // Получаем ID сессии игрока
    
        $room = Room::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'board' => json_encode([['', '', ''], ['', '', ''], ['', '', '']]),
            'player_x' => $sessionId, // Создатель комнаты становится игроком "X"
            'turn' => 'X', // Первый ход всегда за "X"
        ]);
    
        // Сохраняем ID комнаты в сессии
        Session::put('room', $room->id);
        Session::put('player_symbol', 'X'); // Создатель играет за "X"
        session()->save(); // Принудительно сохраняем сессию
    
        // Перенаправляем по URL с ID комнаты
        return response()->json([
            'success' => true,
            'room' => $room,
            'player_symbol' => 'X', // Отправляем на фронт, что этот игрок — "X"
            'redirect' => route('game.show', ['roomId' => $room->id]) // Передаем ID комнаты в URL
        ]);
    }

    public function joinRoom(Request $request)
{
    $request->validate([
        'name' => 'required',
        'password' => 'required',
    ]);

    // Ищем комнату по имени
    $room = Room::where('name', $request->name)->first();

    // Если комната не найдена или пароль неверен
    if (!$room || !Hash::check($request->password, $room->password)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    $playerSymbol = null;

    // Присваиваем игроку символ, если в комнате ещё нет игрока X или O
    if (!$room->player_x) {
        $room->player_x = session()->getId(); // Сохраняем ID сессии для X
        $playerSymbol = 'X';
    } elseif (!$room->player_o) {
        $room->player_o = session()->getId(); // Сохраняем ID сессии для O
        $playerSymbol = 'O';
    } else {
        return response()->json(['error' => 'Room is full'], 403); // Если комната уже полная
    }

    // Сохраняем изменения в комнате
    $room->save();

    // Сохраняем ID комнаты и символ игрока в сессии
    Session::put('room', $room->id);
    Session::put('player_symbol', $playerSymbol); // Сохраняем символ игрока в сессии
    session()->save(); // Принудительно сохраняем сессию

    // Перенаправляем по URL с ID комнаты
    return response()->json([
        'success' => true,
        'room' => $room,
        'player_symbol' => $playerSymbol, // Отправляем на фронт, кем играет игрок
        'redirect' => route('game.show', ['roomId' => $room->id]) // Передаем ID комнаты в URL
    ]);
}



}