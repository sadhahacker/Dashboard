<?php

namespace App\Console\Commands;

use ccxt\pro\binance;
use Illuminate\Console\Command;
use React\Async;
use React\EventLoop\Loop;

class WatchBalance extends Command
{
    protected $signature = 'watch:balance';
    protected $description = 'Watch account balance via WebSocket';

    public function handle()
    {
        $exchange = new binance([
            'apiKey' => env('BINANCE_API_KEY'),
            'secret' => env('BINANCE_API_SECRET'),
            'options' => [
                'defaultType' => 'future', // For Binance Futures
            ],
            'newUpdates' => false, // Optional: disable new updates
        ]);

        $params = []; // Define any additional parameters here

        Async\async(function () use ($exchange, $params) {
            while (true) {
                try {
                    $balance = yield $exchange->watch_balance($params);
                    echo date('c'), ' ', json_encode($balance), "\n";
                } catch (\Exception $e) {
                    echo get_class($e), ' ', $e->getMessage(), "\n";
                    sleep(5); // Prevent excessive retries
                }
            }
        })();

        Loop::run();
    }
}
