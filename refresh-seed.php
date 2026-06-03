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
    private $syncScriptPath;
    private $seedPath;
    private $workbookPath;
    private $googleSpreadsheetId;
    private $googleServiceAccountJson;

    public function __construct()
    {
        $this->projectRoot = __DIR__;
        $this->scriptPath = $this->projectRoot . '/scripts/build_ui_seed.py';
        $this->syncScriptPath = $this->projectRoot . '/scripts/sync_google_sheet_to_workbook.py';
        $this->seedPath = $this->projectRoot . '/ui/ui_seed_data.json';
        $this->workbookPath = $this->projectRoot . '/Data UMKM Online Shop Kabupaten Mojokerto.xlsx';
        $this->googleSpreadsheetId = $this->readEnvValue('GOOGLE_SHEETS_SPREADSHEET_ID');
        $this->googleServiceAccountJson = $this->readEnvValue('GOOGLE_SERVICE_ACCOUNT_JSON');
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

        if ($this->shouldSyncFromGoogle()) {
            $this->syncWorkbookFromGoogleSheets();
            echo "\n";
        } else {
            echo "[i] Google Service Account sync disabled (GOOGLE_SHEETS_SPREADSHEET_ID / GOOGLE_SERVICE_ACCOUNT_JSON belum diisi).\n";
            echo "[i] Menggunakan workbook lokal yang sudah ada.\n";
        }

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
        // Allow explicit override from .env: PANDU_PYTHON_BINARY
        $envPath = $this->readEnvValue('PANDU_PYTHON_BINARY');
        if (!empty($envPath)) {
                    // If relative or not absolute, try to resolve relative to project root
                    if (!preg_match('/^[A-Za-z]:[\\\\\/]|^\\\\\\\\|^\//', $envPath)) {
                    $resolved = $this->projectRoot . DIRECTORY_SEPARATOR . ltrim($envPath, '\\/');
            } else {
                $resolved = $envPath;
            }

                // Try normalized variants to handle forward/backslash differences across environments
                $variants = [
                    $resolved,
                    str_replace('/', DIRECTORY_SEPARATOR, $resolved),
                    str_replace('\\\\', DIRECTORY_SEPARATOR, $resolved),
                    str_replace('/', '\\\\', $resolved),
                ];

                foreach ($variants as $v) {
                    if ($v && file_exists($v)) {
                        return $v;
                    }
                }
        }

        $candidates = [
            $this->projectRoot . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe',
            $this->projectRoot . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python',
            'python',
            'python3',
            'python.exe',
            'python3.exe',
            // Common absolute installation paths on Windows
            'C:\\Program Files\\Python312\\python.exe',
            'C:\\Program Files (x86)\\Python312\\python.exe',
        ];

        foreach ($candidates as $cmd) {
            if (preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\|^\//', $cmd)) {
                if (file_exists($cmd)) {
                    return $cmd;
                }
                continue;
            }

            $test = shell_exec("where {$cmd} 2>nul") ?: shell_exec("which {$cmd} 2>/dev/null");
            if ($test) {
                return trim($test);
            }
        }

        return null;
    }

    private function shouldSyncFromGoogle()
    {
        $skip = getenv('SKIP_GOOGLE_SYNC');
        if ($skip !== false && in_array(strtolower((string)$skip), ['1','true','yes'], true)) {
            return false;
        }

        return !empty($this->googleSpreadsheetId) && !empty($this->googleServiceAccountJson);
    }

    private function syncWorkbookFromGoogleSheets()
    {
        echo "[*] Sync workbook from Google Sheets (Service Account)\n";

        if (!file_exists($this->syncScriptPath)) {
            echo "[✗] ERROR: Sync script not found at:\n";
            echo "    {$this->syncScriptPath}\n";
            exit(1);
        }

        $credentialsPath = $this->googleServiceAccountJson;
                if (!preg_match('/^[A-Za-z]:[\\\\\/]|^\\\\\\\\|^\//', $credentialsPath)) {
            $credentialsPath = $this->projectRoot . DIRECTORY_SEPARATOR . ltrim($credentialsPath, '\\\\/');
        }

        if (!file_exists($credentialsPath)) {
            echo "[✗] ERROR: Service account JSON not found at:\n";
            echo "    {$credentialsPath}\n";
            exit(1);
        }

        $python = $this->findPython();
        if (!$python) {
            echo "[✗] ERROR: Python executable not found.\n";
            exit(1);
        }

        $command = "cd {$this->projectRoot} && \"{$python}\" \"{$this->syncScriptPath}\""
            . " --spreadsheet-id \"{$this->googleSpreadsheetId}\""
            . " --credentials \"{$credentialsPath}\""
            . " --output \"{$this->workbookPath}\" 2>&1";

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            echo "[✗] ERROR: Google Sheets sync failed!\n";
            echo "\nOutput:\n";
            echo implode("\n", $output) . "\n";
            exit(1);
        }

        echo "[✓] Google Sheets sync completed\n";
        foreach ($output as $line) {
            echo "    " . $line . "\n";
        }
    }

    private function readEnvValue($key)
    {
        $envPath = $this->projectRoot . '/.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }
                [$name, $raw] = explode('=', $line, 2);
                if (trim($name) !== $key) {
                    continue;
                }

                $raw = trim($raw);
                if ((str_starts_with($raw, '"') && str_ends_with($raw, '"')) || (str_starts_with($raw, "'") && str_ends_with($raw, "'"))) {
                    $raw = substr($raw, 1, -1);
                }
                if ($raw !== '') {
                    return $raw;
                }
            }
        }

        $value = getenv($key);
        if ($value !== false && trim((string)$value) !== '') {
            return trim((string)$value);
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
        echo "  Optional Sync: scripts/sync_google_sheet_to_workbook.py (Service Account)\n";
        echo "  Python Script: scripts/build_ui_seed.py\n";
        echo "  Seed Output: ui/ui_seed_data.json\n";
        echo "\n";
        echo "Optional .env keys for Google sync:\n";
        echo "  GOOGLE_SHEETS_SPREADSHEET_ID=...\n";
        echo "  GOOGLE_SERVICE_ACCOUNT_JSON=storage/app/private/google/service-account.json\n";
    }
}

// Run
$refresher = new UmkmSeedRefresher();
$refresher->run();
