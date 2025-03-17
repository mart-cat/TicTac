<?php

namespace App\Http\Controllers;

use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Room;

class RoomController extends Controller
{
    public function createRoom(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:rooms',
            'password' => 'required',
        ]);

        $sessionId = session()->getId();

        $room = Room::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'board' => json_encode([['', '', ''], ['', '', ''], ['', '', '']]),
            'player_x' => $sessionId,
            'turn' => 'X',
        ]);

        Session::put('room', $room->id);
        Session::put('player_symbol', 'X');
        session()->save();

        return response()->json([
            'success' => true,
            'room' => $room,
            'player_symbol' => 'X',
            'redirect' => route('game.show', ['roomId' => $room->id])
        ]);
    }

    public function joinRoom(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $room = Room::where('name', $request->name)->first();

        if (!$room || !Hash::check($request->password, $room->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $playerSymbol = null;

        if (!$room->player_x) {
            $room->player_x = session()->getId();
            $playerSymbol = 'X';
        } elseif (!$room->player_o) {
            $room->player_o = session()->getId();
            $playerSymbol = 'O';
        } else {
            return response()->json(['error' => 'Room is full'], 403);
        }

        $room->save();

        Session::put('room', $room->id);
        Session::put('player_symbol', $playerSymbol);
        session()->save();

        return response()->json([
            'success' => true,
            'room' => $room,
            'player_symbol' => $playerSymbol,
            'redirect' => route('game.show', ['roomId' => $room->id])
        ]);
    }

    public function room()
    {
        return view('room');
    }
}
