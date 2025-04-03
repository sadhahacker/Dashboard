<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\TradeController;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncToExchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        $trades = (new TradeController())->getTrades('BNBUSDT');
        foreach ($trades as $trade) {
            Trade::updateOrCreate(
                ['trade_id' => $trade['id']], // Unique key to prevent duplicates
                [
                    'symbol'        => $trade['symbol'],
                    'order_id'      => $trade['order'] ?? null,
                    'side'          => $trade['side'],
                    'price'         => $trade['price'],
                    'amount'        => $trade['amount'],
                    'cost'          => $trade['cost'],
                    'realized_pnl'  => $trade['info']['realizedPnl'] ?? 0,
                    'fee'           => $trade['fee']['cost'] ?? 0,
                    'fee_currency'  => $trade['fee']['currency'] ?? 'USDT',
                    'trade_time'    => Carbon::createFromTimestampMs($trade['timestamp'])->toDateTimeString(),
                    'maker'         => $trade['takerOrMaker'] === 'maker',
                ]
            );
        }
    }
}
