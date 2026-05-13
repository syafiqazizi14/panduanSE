<?php
/**
 * UMKM Seed Refresh Script
 * 
 * Usage:
 *   php refresh-seed.php              (regenerate seed)
 *   php refresh-seed.php --check      (show current seed status)
 *   php refresh-seed.php --help       (show this help)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class UmkmSeedRefresher
{
    private $projectRoot;
    private $scriptPath;
    private $seedPath;
    private $workbookPath;

    public function __construct()
    {
        $this->projectRoot = __DIR__;
        $this->scriptPath = $this->projectRoot . '/scripts/build_ui_seed.py';
        $this->seedPath = $this->projectRoot . '/ui/ui_seed_data.json';
        $this->workbookPath = $this->projectRoot . '/Data UMKM Online Shop Kabupaten Mojokerto.xlsx';
    }

    public function run()
    {
        $command = isset($GLOBALS['argv'][1]) ? $GLOBALS['argv'][1] : 'refresh';

        switch ($command) {
            case '--help':
            case '-h':
                $this->showHelp();
                break;
            case '--check':
            case '-c':
                $this->checkStatus();
                break;
            case 'refresh':
            default:
                $this->refreshSeed();
                break;
        }
    }

    private function refreshSeed()
    {
        echo "[*] UMKM Seed Generation\n";
        echo "─────────────────────────────────────\n\n";

        // Check workbook exists
        if (!file_exists($this->workbookPath)) {
            echo "[✗] ERROR: Workbook not found at:\n";
            echo "    {$this->workbookPath}\n";
            exit(1);
        }
        echo "[✓] Workbook found: {$this->workbookPath}\n";

        // Check Python script exists
        if (!file_exists($this->scriptPath)) {
            echo "[✗] ERROR: Python script not found at:\n";
            echo "    {$this->scriptPath}\n";
            exit(1);
        }
        echo "[✓] Python script found: {$this->scriptPath}\n";

        // Find Python executable
        $python = $this->findPython();
        if (!$python) {
            echo "[✗] ERROR: Python executable not found.\n";
            echo "    Install Python 3.10+ and ensure it's in PATH.\n";
            exit(1);
        }
        echo "[✓] Python found: {$python}\n";

        // Run generator
        echo "\n[*] Generating seed from workbook...\n";
        $command = "cd {$this->projectRoot} && \"{$python}\" \"{$this->scriptPath}\" 2>&1";
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            echo "[✗] ERROR: Seed generation failed!\n";
            echo "\nOutput:\n";
            echo implode("\n", $output) . "\n";
            exit(1);
        }

        // Show output
        foreach ($output as $line) {
            echo "    " . $line . "\n";
        }

        // Verify seed was created
        if (!file_exists($this->seedPath)) {
            echo "\n[✗] ERROR: Seed file was not created at:\n";
            echo "    {$this->seedPath}\n";
            exit(1);
        }

        echo "\n[✓] Seed generation completed successfully!\n";
        $this->showSeedStatus();
    }

    private function checkStatus()
    {
        echo "[*] UMKM Seed Status\n";
        echo "─────────────────────────────────────\n\n";

        if (!file_exists($this->seedPath)) {
            echo "[✗] Seed not generated yet.\n";
            echo "    Run: php refresh-seed.php\n";
            return;
        }

        $this->showSeedStatus();
    }

    private function showSeedStatus()
    {
        $seed = json_decode(file_get_contents($this->seedPath), true);

        if (!$seed) {
            echo "[✗] ERROR: Could not read seed JSON\n";
            return;
        }

        echo "[✓] Seed Status:\n";
        echo "    Generated: {$seed['meta']['generated_at']}\n";
        echo "    Total Rows: {$seed['meta']['total_rows']}\n";
        echo "    GoogleMaps: {$seed['meta']['googlemaps_rows']}\n";
        echo "    Tokopedia: {$seed['meta']['tokopedia_rows']}\n";
        echo "    Kecamatan: {$seed['meta']['kecamatan_count']}\n";
        echo "    Desa: {$seed['meta']['desa_count_total']}\n";
        echo "    Matched: {$seed['meta']['matched_true']}\n";
        echo "    Unmatched: {$seed['meta']['matched_false']}\n";
    }

    private function findPython()
    {
        $candidates = [
            'python',
            'python3',
            'python.exe',
            'python3.exe',
        ];

        foreach ($candidates as $cmd) {
            $test = shell_exec("where {$cmd} 2>nul") ?: shell_exec("which {$cmd} 2>/dev/null");
            if ($test) {
                return trim($test);
            }
        }

        return null;
    }

    private function showHelp()
    {
        echo "UMKM Seed Refresh - Regenerate UI seed data from workbook\n";
        echo "\n";
        echo "Usage:\n";
        echo "  php refresh-seed.php              Regenerate seed from workbook\n";
        echo "  php refresh-seed.php --check      Show current seed status\n";
        echo "  php refresh-seed.php --help       Show this help message\n";
        echo "\n";
        echo "Examples:\n";
        echo "  php refresh-seed.php              # Refresh seed now\n";
        echo "  php refresh-seed.php -c           # Check seed status\n";
        echo "\n";
        echo "Files:\n";
        echo "  Workbook: Data UMKM Online Shop Kabupaten Mojokerto.xlsx\n";
        echo "  Python Script: scripts/build_ui_seed.py\n";
        echo "  Seed Output: ui/ui_seed_data.json\n";
    }
}

// Run
$refresher = new UmkmSeedRefresher();
$refresher->run();
