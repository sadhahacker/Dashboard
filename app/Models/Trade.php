<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'symbol', 'order_id', 'trade_id', 'side',
        'price', 'amount', 'cost', 'realized_pnl',
        'fee', 'fee_currency', 'trade_time', 'maker'
    ];
}
