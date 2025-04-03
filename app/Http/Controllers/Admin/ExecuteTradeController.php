<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ExecuteTradeController extends Controller {

    protected $tradeController;

    protected $stoplossFromAccountBalance = 0.23;

    protected $takeProfitFromAccountBalance = 0.30;

    protected $stoplossFromCoin = 0.03;

    protected $takeProfitFromCoin = 0.023;

    protected $leverage = 10;

    public function __construct() {
        $this->tradeController = new TradeController();
    }
    public function executeTrade($symbol, $entry_price, $side) {
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

}
