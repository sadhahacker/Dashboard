<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signals;
use Carbon\Carbon;

class ExecuteTradeController extends Controller {

    protected $tradeController;

    protected $stoplossFromAccountBalance = 0.23;

    protected $takeProfitFromAccountBalance = 0.30;

    protected $stoplossFromCoin = 0.03;

    protected $takeProfitFromCoin = 0.023;

    protected $leverage = 15;

    public function __construct() {
        $this->tradeController = new TradeController();
    }
    public function executeTrade($symbol, $entry_price, $side, $timestamp = null) {
        if($this->isOrdersAvailable($symbol) || Carbon::now()->addHour()->lt($timestamp)) {
            return false; // Orders already exist, do not execute new trade
        }

        $entry_price = $this->getCorrectEntryPrice($symbol, $entry_price, $side);
        $balance = $this->tradeController->getBalance();
        $this->tradeController->setLeverage($this->leverage);

        // Calculate trade amount based on leverage
        $tradeAmount = ($balance * $this->leverage) / $entry_price;
        $tradeAmount = $this->tradeController->amount_to_precision($symbol, $tradeAmount); // Ensure precision

        // Determine Take Profit & Stop Loss prices
        $TpSl = $this->getTPSLFromCoin($entry_price, $this->takeProfitFromCoin, $this->stoplossFromCoin, $side);

        // Ensure price precision
        $takeProfitPrice = $this->tradeController->price_to_precision($symbol, $TpSl['takeProfit']);
        $stopLossPrice = $this->tradeController->price_to_precision($symbol, $TpSl['stopLoss']);

        // Order parameters
        $params = [
            'stopLoss' => [
                'triggerPrice' => $stopLossPrice,
            ],
            'takeProfit' => [
                'triggerPrice' => $takeProfitPrice,
            ],
            'marginMode' => 'isolated'
        ];

        // Create order (for LIMIT entry)
        $order = $this->tradeController->create_order($symbol, 'limit', $side, $tradeAmount, $entry_price, $params);

        Signals::updateOrCreate([
            'open_time' => $timestamp,
        ], [
            'side' => $side,
            'entry_price' => $entry_price,
            'take_profit' => $takeProfitPrice,
            'stop_loss' => $stopLossPrice,
        ]);

        return $order;
    }


    protected function getTPSLFromCoin($price, $tp, $sl, $side)
    {
        $takeProfit = 0;
        $stopLoss = 0;

        if ($side === 'buy') {
            $takeProfit = $price * (1 + ($tp / 100)); // TP is above entry
            $stopLoss = $price * (1 - ($sl / 100)); // SL is below entry
        } elseif ($side === 'sell') {
            $takeProfit = $price * (1 - ($tp / 100)); // TP is below entry
            $stopLoss = $price * (1 + ($sl / 100)); // SL is above entry
        }

        return [
            'takeProfit' => round($takeProfit, 2),
            'stopLoss' => round($stopLoss, 2)
        ];
    }

    public function isOrdersAvailable($symbol = 'BNBUSDT')
    {
        $positions = $this->tradeController->getPositions($symbol);
        $orders = $this->tradeController->getOpenOrders($symbol);
        return !empty($orders) || !empty($positions);
    }

    public function getCorrectEntryPrice($symbol, $entry_price, $side)
    {
        $ticker = $this->tradeController->getTicker($symbol);
        $current_price = isset($ticker['last']) ? $ticker['last'] : null;

        if ($side === 'buy') {
            // For LONG, choose lower (better buy)
            return $current_price <= $entry_price ? $current_price : $entry_price;
        } elseif ($side === 'sell') {
            // For SHORT, choose higher (better sell)
            return $current_price >= $entry_price ? $current_price : $entry_price;
        } else {
            throw new \InvalidArgumentException("Invalid side: $side. Must be 'long' or 'short'.");
        }
    }
}
