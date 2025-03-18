<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\Async;
use ccxt\binance;

class WatchBalance extends Command
{
    protected $signature = 'watch:balance';
    protected $description = 'Watch account balance via WebSocket';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        $exchange = new binance([
            'apiKey' => env('BINANCE_API_KEY'),
            'secret' => env('BINANCE_API_SECRET'),
            'options' => [
                'defaultType' => 'future', // For Binance Futures
            ],
            'newUpdates' => false, // Optional: disable new updates
        ]);

        while (true) {
            $data = $this->formatBalanceDetails($exchange->fetch_balance());
            broadcast(new \App\Events\Binance($data));
            sleep(5);
        }
    }
    protected function formatBalanceDetails($data) {
        if (!isset($data['info']['assets'])) {
            return [];
        }

        return array_values(array_filter($data['info']['assets'], function ($asset) {
            $walletBalance = (float) $asset['walletBalance'];
            $unrealizedProfit = (float) $asset['unrealizedProfit'];
            $marginBalance = (float) $asset['marginBalance'];

            // Filter out assets with zero balances
            return $walletBalance > 0 || $unrealizedProfit > 0 || $marginBalance > 0;
        }));
    }
}
