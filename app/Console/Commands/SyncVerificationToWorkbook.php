<?php

namespace App\Console\Commands;

use App\Models\VerificationResult;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Throwable;

class SyncVerificationToWorkbook extends Command
{
    protected $signature = 'umkm:sync-verification-sheet {--limit=500 : Max unsynced records per run}';

    protected $description = 'Append unsynced verification_results rows into workbook sheet Hasil_Verifikasi';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $records = VerificationResult::query()
            ->whereNull('synced_to_sheet_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($records->isEmpty()) {
            $this->info('No unsynced verification rows found.');
            return self::SUCCESS;
        }

        $payload = $records->map(function (VerificationResult $row): array {
            return [
                'timestamp' => optional($row->submitted_at)->toIso8601String(),
                'id_scraping' => $row->id_scraping,
                'source_tab' => $row->source_tab,
                'match_idsbr' => $row->match_idsbr,
                'match_nama_usaha' => $row->match_nama_usaha,
                'match_alamat' => $row->match_alamat,
                'verification_status' => $row->verification_status,
                'officer_name' => $row->officer_name,
                'officer_id' => $row->officer_id,
                'officer_latitude' => $row->officer_latitude,
                'officer_longitude' => $row->officer_longitude,
                'verified_latitude' => $row->verified_latitude,
                'verified_longitude' => $row->verified_longitude,
                'distance_km' => $row->distance_km,
                'notes' => $row->notes,
                'photo_url' => $row->photo_url,
                'device_id' => $row->device_id,
                'idempotency_key' => $row->idempotency_key,
            ];
        })->values()->all();

        $tmpFile = tempnam(sys_get_temp_dir(), 'umkm_sync_');
        if ($tmpFile === false) {
            $this->error('Failed to allocate temporary file for sync payload.');
            return self::FAILURE;
        }

        file_put_contents($tmpFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $pythonPath = $this->findPythonExecutable();
        $scriptPath = base_path('scripts/append_verification_to_sheet.py');
        $workbookPath = base_path('Data UMKM Online Shop Kabupaten Mojokerto.xlsx');

        if ($pythonPath === null) {
            @unlink($tmpFile);
            $this->error('Python executable not found.');
            return self::FAILURE;
        }

        if (!is_file($scriptPath)) {
            @unlink($tmpFile);
            $this->error("Sync script not found at {$scriptPath}");
            return self::FAILURE;
        }

        if (!is_file($workbookPath)) {
            @unlink($tmpFile);
            $this->error("Workbook not found at {$workbookPath}");
            return self::FAILURE;
        }

        try {
            $result = Process::run([
                $pythonPath,
                $scriptPath,
                '--input',
                $tmpFile,
                '--workbook',
                $workbookPath,
                '--sheet',
                'Hasil_Verifikasi',
            ]);
        } catch (Throwable $e) {
            @unlink($tmpFile);
            $this->error('Failed to execute Python sync: ' . $e->getMessage());
            return self::FAILURE;
        }

        @unlink($tmpFile);

        if ($result->failed()) {
            $this->error('Workbook sync failed.');
            $this->line(trim($result->errorOutput()) ?: trim($result->output()));
            return self::FAILURE;
        }

        $decoded = json_decode($result->output(), true);
        if (!is_array($decoded)) {
            $this->error('Unexpected sync output from Python script.');
            $this->line(trim($result->output()));
            return self::FAILURE;
        }

        $keysToMark = array_values(array_unique(array_merge(
            $decoded['appended_keys'] ?? [],
            $decoded['skipped_existing_keys'] ?? []
        )));

        if (!empty($keysToMark)) {
            VerificationResult::query()
                ->whereNull('synced_to_sheet_at')
                ->whereIn('idempotency_key', $keysToMark)
                ->update(['synced_to_sheet_at' => Carbon::now()]);
        }

        $this->info('Verification sheet sync complete.');
        $this->line('Appended: ' . (int) ($decoded['appended'] ?? 0));
        $this->line('Skipped existing: ' . (int) ($decoded['skipped_existing'] ?? 0));

        return self::SUCCESS;
    }

    private function findPythonExecutable(): ?string
    {
        $candidates = [
            base_path('.venv/Scripts/python.exe'),
            base_path('.venv/Scripts/python'),
            base_path('venv/Scripts/python.exe'),
            base_path('venv/bin/python3'),
            'python',
            'python3',
            'py',
        ];

        foreach ($candidates as $candidate) {
            if ($this->isExecutableCandidate($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isExecutableCandidate(string $candidate): bool
    {
        if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, '/')) {
            return is_file($candidate);
        }

        try {
            $check = Process::run([$candidate, '--version']);
            return $check->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
