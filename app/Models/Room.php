<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'password',
        'player_x',
        'player_o',
        'score_x' => 0,
    'score_o' => 0,
    'turn' => 'X',
        'board',

    ];
}
