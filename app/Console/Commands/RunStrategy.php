<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ExecuteTradeController;
use App\Http\Controllers\Admin\TradeController;
use App\Http\ScriptsPython\ScriptsRunner;
use App\Models\Signals;
use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Strategy\SupertrendStrategy;

class RunStrategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:strategy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the trading strategy';

    public function handle(){
        \Cache::put('isRunning', true, 60);
        ini_set('memory_limit', '512M');
        $scriptRun = new ScriptsRunner();
        $output = $scriptRun->runPythonScript('BNBUSDT', '1h', 1000);
        $output = $output['success'] ?? [];
        if ($scriptRun->isGoodSignal($output)) {
            $entry = $output['isNewBuySignal'] ? $output['startLongTrade'] : $output['startShortTrade'];
            $side = $output['isNewBuySignal'] ? 'buy' : 'sell';
            $response = (new ExecuteTradeController())->executeTrade('BNBUSDT', $entry ,$side,$output['timestamp'] );
        }
    }
}
