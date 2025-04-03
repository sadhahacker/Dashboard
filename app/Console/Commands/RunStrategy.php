<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\TradeController;
use App\Http\ScriptsPython\ScriptsRunner;
use Illuminate\Console\Command;
use App\Http\Controllers\Admin\Strategy\SupertrendStrategy;

class RunStrategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:supertrend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Supertrend trading strategy';

    public function handle(){
        ini_set('memory_limit', '512M');
        $output = (new ScriptsRunner())->runPythonScript('BNBUSDT', '1m', 1000);
        dd($output);
//        (new SupertrendStrategy())->index();
    }
}
