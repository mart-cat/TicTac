<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $board;
    
    public function __construct() //Че рисуем то
    {
        $this-> board = [
            ['x','',''],
            ['','0',''],
            ['','','x']
        ];
    }
    
    public function show() //рисуем
    {
        return view('game', ['board' => $this->board]);
    }

}
