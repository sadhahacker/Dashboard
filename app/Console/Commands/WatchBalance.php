<?php

namespace App\Console\Commands;

use \ccxt\pro;
use Illuminate\Console\Command;

class WatchBalance extends Command
{
    protected $signature = 'watch:balance';
    protected $description = 'Watch account balance via WebSocket';

    public function handle()
    {
        $exchange = new \ccxt\pro\binance([
            'apiKey' => env('BINANCE_API_KEY'),
            'secret' => env('BINANCE_API_SECRET'),
            'options' => [
                'defaultType' => 'future', // For Binance Futures
            ],
            'newUpdates' => false, // Optional: disable new updates
        ]);

        $params = []; // Define any additional parameters here

        if ($exchange->has['watchBalance']) {
            \ccxt\pro\binance::execute_and_run(function () use ($exchange, $params) {
                while (true) {
                    try {
                        $balance = yield $exchange->watch_balance($params);
                        echo date('c') . ' ' . json_encode($balance, JSON_PRETTY_PRINT) . "\n";
                    } catch (\Exception $e) {
                        echo get_class($e) . ' ' . $e->getMessage() . "\n";
                    }
                }
            });
        } else {
            echo "WebSocket support for balance watching is not available on this exchange.\n";
        }
    }
}
