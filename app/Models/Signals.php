<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signals extends Model
{
    protected $fillable = [
        'symbol',
        'side',
        'open_time',
        'entry_price',
        'take_profit',
        'stop_loss',
        'status',
        'successful',
    ];
}
