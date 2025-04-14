<?php

use App\Http\Controllers\Admin\TradeController;
use App\Http\Middleware\IncreaseMemoryLimit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('api/exchange')->middleware(IncreaseMemoryLimit::class)->group(function () {
    // Public endpoints
    Route::get('/markets', [TradeController::class, 'getMarketsRequest']);
    Route::get('/ticker', [TradeController::class, 'getTickerRequest']);
    Route::get('/tickers', [TradeController::class, 'getTickersRequest']);
    Route::get('/orderbook', [TradeController::class, 'getOrderBookRequest']);
    Route::get('/trades', [TradeController::class, 'getTradesRequest']);
    Route::get('/ohlcv', [TradeController::class, 'getOHLCVRequest']);

    // Private endpoints (should be protected with authentication middleware)
    Route::middleware([])->group(function () {
        Route::get('/balance', [TradeController::class, 'getBalanceRequest']);
        Route::get('/mytrades', [TradeController::class, 'getMyTradesRequest']);
        Route::post('/order', [TradeController::class, 'createOrderRequest']);
        Route::delete('/order', [TradeController::class, 'cancelOrderRequest']);
        Route::get('/order', [TradeController::class, 'getOrderRequest']);
        Route::get('/openorders', [TradeController::class, 'getOpenOrdersRequest']);
        Route::get('/closedorders', [TradeController::class, 'getClosedOrdersRequest']);
        Route::get('/depositaddress', [TradeController::class, 'getDepositAddressRequest']);
        Route::get('/deposits', [TradeController::class, 'getDepositsRequest']);
        Route::get('/withdrawals', [TradeController::class, 'getWithdrawalsRequest']);
        Route::post('/transfer', [TradeController::class, 'transferRequest']);
        Route::post('/withdraw', [TradeController::class, 'withdrawRequest']);
    });
});

Route::get('isBotRunning', function (){
    $isRunning = Cache::has('isRunning') ? Cache::get('isRunning') : false;
    return response()->json([
        'isRunning' => $isRunning,
    ]);
});


Route::get('mytrade', function () {
    return view('admin/trade');
});



Route::get('test', [TradeController::class, 'calculateTrade'])->middleware(IncreaseMemoryLimit::class);
