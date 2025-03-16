<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Async;
use Binance;

class WatchBalance extends Command
{
    protected $signature = 'watch:balance';
    protected $description = 'Watch account balance via WebSocket';

    public function handle()
    {
        $api = new Binance\API(env('BINANCE_API_KEY'), env('BINANCE_API_SECRET'));
        $api->httpDebug = true;
        $api->caOverride = true;

        $balances = $api->balances();
        dd($balances);

        $balance_update = function($api, $balances) {
            print_r($balances);
            echo "Balance update".PHP_EOL;
        };

    }
}
