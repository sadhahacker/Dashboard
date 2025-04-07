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
    public function executeTrade($symbol, $entry_price, $side, $timestamp = null)
    {
//         Skip trade if already in position (optional)
         if ($this->isOrdersAvailable($symbol)) {
             return false;
         }

        $entry_price = $this->getCorrectEntryPrice($symbol, $entry_price, $side);
        $balance = $this->tradeController->getBalance()['total']['USDT'] ?? 0;
        $this->tradeController->setLeverage($symbol, $this->leverage);

        // Calculate trade amount with leverage
        $tradeAmount = ($balance * 10) / $entry_price;
        $tradeAmount = $this->tradeController->amountToPrecision($symbol, $tradeAmount);

        // Get TP/SL prices
        $TpSl = $this->getTPSLFromCoin($entry_price, $this->takeProfitFromCoin, $this->stoplossFromCoin, $side);

        $takeProfitPrice = $this->tradeController->priceToPrecision($symbol, $TpSl['takeProfit']);
        $stopLossPrice   = $this->tradeController->priceToPrecision($symbol, $TpSl['stopLoss']);

        // Set opposite side for exit orders
        $exitSide = $side === 'buy' ? 'sell' : 'buy';

        $orders = [
            // Main entry order
            [
                'symbol' => $symbol,
                'type' => 'limit',
                'side' => $side,
                'amount' => $tradeAmount,
                'price' => $entry_price,
                'params' => [
                    'marginMode' => 'isolated',
                    'timeInForce' => 'GTC',
                ],
            ],
            // Take profit
            [
                'symbol' => $symbol,
                'type' => 'take_profit_market',
                'side' => $exitSide,
                'amount' => $tradeAmount,
                'price' => null,
                'reduceOnly' => true,
                'params' => [
                    'triggerPrice' => $takeProfitPrice,
                    'marginMode' => 'isolated',
                ],
            ],
            // Stop loss
            [
                'symbol' => $symbol,
                'type' => 'stop_market',
                'side' => $exitSide,
                'amount' => $tradeAmount,
                'price' => null,
                'reduceOnly' => true,
                'params' => [
                    'triggerPrice' => $stopLossPrice,
                    'marginMode' => 'isolated',
                ],
            ],
        ];

        // Place batch orders (requires CCXT Pro)
        $response = $this->tradeController->createOrders($orders);

        // Save signal
        Signals::updateOrCreate([
            'open_time' => $timestamp,
        ], [
            'symbol' => $symbol,
            'side' => $side,
            'status' => 0,
            'entry_price' => $entry_price,
            'take_profit' => $takeProfitPrice,
            'stop_loss' => $stopLossPrice,
            'successful' => false,
        ]);

        return $response;
    }


    protected function getTPSLFromCoin($price, $tp, $sl, $side)
    {
        $takeProfit = 0;
        $stopLoss = 0;

        if ($side === 'buy') {
            // For LONG
            $takeProfit = $price + ($price * $tp);
            $stopLoss   = $price - ($price * $sl);
        } elseif ($side === 'sell') {
            // For SHORT
            $takeProfit = $price - ($price * $tp);
            $stopLoss   = $price + ($price * $sl);
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

        if (is_null($current_price)) {
            throw new \Exception("Unable to fetch current price for {$symbol}");
        }

        // Calculate the price difference percentage
        $change_percent = abs($current_price - $entry_price) / $entry_price * 100;

        // Only update if price change is at least 0.1%
        if ($change_percent < 0.1) {
            return $entry_price; // Too small to consider updating
        }

        if ($side === 'buy') {
            // For LONG, choose lower (better buy)
            return $current_price <= $entry_price ? $current_price : $entry_price;
        } elseif ($side === 'sell') {
            // For SHORT, choose higher (better sell)
            return $current_price >= $entry_price ? $current_price : $entry_price;
        } else {
            throw new \InvalidArgumentException("Invalid side: $side. Must be 'buy' or 'sell'.");
        }
    }
}
