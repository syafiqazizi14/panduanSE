<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Exception;

class GenerateUmkmSeed extends Command
{
    protected $signature = 'umkm:generate-seed {--force : Skip confirmation and regenerate immediately}';
    protected $description = 'Generate UI seed data from workbook Excel file';

    public function handle()
    {
        $scriptPath = base_path('scripts/build_ui_seed.py');
        $pythonPath = $this->findPythonExecutable();

        if (!file_exists($scriptPath)) {
            $this->error("Script not found at: {$scriptPath}");
            return 1;
        }

        if (!$pythonPath) {
            $this->error('Python executable not found. Ensure Python is installed and in PATH.');
            return 1;
        }

        $this->info('Generating UMKM seed data from workbook...');

        try {
            $process = Process::run("{$pythonPath} {$scriptPath}");

            if ($process->failed()) {
                $this->error('Seed generation failed:');
                $this->error($process->errorOutput());
                return 1;
            }

            $this->info($process->output());
            $this->info('✓ Seed generation completed successfully.');
            return 0;
        } catch (Exception $e) {
            $this->error('Error executing seed generation: ' . $e->getMessage());
            return 1;
        }
    }

    private function findPythonExecutable()
    {
        $candidates = [
            'python',
            'python3',
            'python.exe',
            'python3.exe',
            base_path('.venv/Scripts/python.exe'),
            base_path('.venv/Scripts/python'),
            base_path('venv/Scripts/python.exe'),
            base_path('venv/bin/python'),
            base_path('venv/bin/python3'),
        ];

        foreach ($candidates as $candidate) {
            if ($this->isPythonExecutable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isPythonExecutable($path)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return file_exists($path) && is_executable($path);
        } else {
            $result = shell_exec("which {$path} 2>/dev/null");
            return !empty($result);
        }
    }
}
