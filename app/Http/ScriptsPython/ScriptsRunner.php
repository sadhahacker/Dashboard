<?php

namespace App\Http\ScriptsPython;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ScriptsRunner
{
    public function runPythonScript($symbol, $interval, $limit)
    {
        // Determine the correct Python executable based on OS
        $pythonExecutable = base_path('app/Http/ScriptsPython/advanced-ta/.venv/Scripts/python.exe'); // Windows
        if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin') {
            $pythonExecutable = base_path('app/Http/ScriptsPython/advanced-ta/.venv/bin/python3'); // Linux/macOS
        }

        $scriptPath = base_path('app/Http/ScriptsPython/advanced-ta/app/controller/lorentzian.py');
        $outputPath = base_path('app/Http/ScriptsPython/advanced-ta/app/controller/output/result.csv'); // Store results in a known location

        // Run Python script with parameters
        $command = [$pythonExecutable, $scriptPath, $symbol, $interval, $limit, $outputPath];
        $process = new Process($command);
        $process->run();

        // Check for Python execution errors
        if (!$process->isSuccessful()) {
            return ['error' => 'Python script execution failed!', 'details' => $process->getErrorOutput()];
        }

        // Check if the output CSV file exists
        if (!file_exists($outputPath)) {
            return ['error' => 'Result file not found!'];
        }

        // Read the last line of the CSV file
        $file = fopen($outputPath, 'r');
        $headers = fgetcsv($file); // Read header row

        $lastRow = null;
        while (($row = fgetcsv($file)) !== false) {
            $lastRow = $row; // Keep overwriting until the last row
        }
        fclose($file);

        if ($lastRow) {
            return ['success' => array_combine($headers, $lastRow)]; // Convert to JSON
        }

        return ['error' => 'No data found in CSV!'];
    }

    public function isGoodSignal(array $data): bool
    {
        $bool = fn($val) => $val === "True" || $val === true;

        // Threshold to decide if prediction supports the signal
        $prediction = (int) $data['prediction'];
        $predictionThreshold = 8;

        // Good Sell Signal
        if (
            $bool($data['isNewSellSignal']) &&
            $bool($data['isEmaDowntrend']) &&
            $data['signal'] == "-1" &&
            $prediction <= -$predictionThreshold
        ) {
            return true;
        }

        // Good Buy Signal
        if (
            $bool($data['isNewBuySignal']) &&
            $bool($data['isEmaUptrend']) &&
            $data['signal'] == "1" &&
            $prediction >= $predictionThreshold
        ) {
            return true;
        }

        return false;
    }
}
