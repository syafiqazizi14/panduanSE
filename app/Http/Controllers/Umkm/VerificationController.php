<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VerificationController extends Controller
{
    private function loadUiData(): array
    {
        $seedPath = base_path('ui/ui_seed_data.json');
        $ui = [
            'meta' => [],
            'filters' => [
                'kecamatan' => [],
                'desa_by_kecamatan' => [],
                'rw_rt_by_desa' => [],
            ],
            'preview_cards' => [],
            'cards' => [],
        ];

        if (is_file($seedPath)) {
            $decoded = json_decode((string) file_get_contents($seedPath), true);

            if (is_array($decoded)) {
                $ui = array_replace_recursive($ui, $decoded);
            }
        }

        return $ui;
    }

    public function index(Request $request): View
    {
        $ui = $this->loadUiData();

        return view('umkm.verification_simple', [
            'umkmUi' => $ui,
            'authUser' => $this->getAuthUser($request),
            'pageTitle' => 'UMKM Mojokerto - Daftar Usaha',
        ]);
    }

    public function usahaBesar(Request $request): View
    {
        $ui = $this->loadUiData();

        return view('umkm.usaha_besar', [
            'umkmUi' => $ui,
            'pageTitle' => 'Usaha Besar - UMKM Mojokerto',
        ]);
    }

    public function kbli(Request $request): View
    {
        $ui = $this->loadUiData();

        return view('umkm.kbli', [
            'umkmUi' => $ui,
            'pageTitle' => 'KBLI - UMKM Mojokerto',
        ]);
    }

    public function usahaBesarRekapPencacah(Request $request): View
    {
        $rekapRows = $this->buildUsahaBesarRekapRows();

        return view('umkm.usaha_besar_rekap', [
            'pageTitle' => 'Rekap Pencacah Usaha Besar - UMKM Mojokerto',
            'rekapRows' => $rekapRows,
            'summary' => $this->buildUsahaBesarRekapSummary($rekapRows),
        ]);
    }

    public function exportUsahaBesarRekapPencacah()
    {
        $rekapRows = $this->buildUsahaBesarRekapRows();
        $filename = 'rekap-pencacah-usaha-besar-' . now()->format('Ymd_His') . '.xls';

        return response()->streamDownload(function () use ($rekapRows): void {
            echo '<!DOCTYPE html>';
            echo '<html lang="id">';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
            echo '<title>Rekap Pencacah Usaha Besar</title>';
            echo '<style>';
            echo '@page{size:A4 landscape;margin:10mm;}';
            echo 'body{font-family:Arial,Helvetica,sans-serif;background:#ffffff;color:#0f172a;margin:0;padding:12px;}';
            echo '.sheet{max-width:1280px;margin:0 auto;background:#fff;border:1px solid #dbe4ee;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.06);}';
            echo '.header-table,.data-table{width:100%;border-collapse:collapse;table-layout:fixed;}';
            echo '.header-table td{border:0;padding:6px 16px;text-align:center;}';
            echo '.title{font-size:24px;line-height:1.15;font-weight:800;padding-top:14px;color:#0f172a;}';
            echo '.subtitle{color:#64748b;font-size:13px;padding-bottom:4px;}';
            echo '.summary-row td{padding:6px 16px 12px;}';
            echo '.summary-wrap{font-size:0;line-height:0;}';
            echo '.summary-box{display:inline-block;margin:0 5px 6px;padding:6px 12px;background:#f8fafc;border:1px solid #dbe4ee;border-radius:999px;color:#475569;font-size:12px;line-height:1.2;}';
            echo '.data-table{border-top:1px solid #dbe4ee;}';
            echo '.data-table thead th{background:#0f172a;color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:.08em;padding:10px 8px;text-align:center;border:1px solid #0b1220;}';
            echo '.data-table tbody td{padding:9px 8px;border:1px solid #e2e8f0;font-size:13px;vertical-align:middle;}';
            echo '.data-table tbody tr:nth-child(even){background:#f8fafc;}';
            echo '.num{text-align:center;font-weight:700;}';
            echo '.name{font-weight:700;color:#0f172a;}';
            echo '.open{color:#b45309;}';
            echo '.process{color:#0369a1;}';
            echo '.success{color:#15803d;}';
            echo '.empty{padding:26px;text-align:center;color:#64748b;}';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<div class="sheet">';
            echo '<table class="header-table">';
            echo '<tr><td class="title" colspan="6">Rekap Pencacah Usaha Besar</td></tr>';
            echo '<tr><td class="subtitle" colspan="6">Ringkasan beban kerja pencacah berdasarkan status data usaha besar</td></tr>';
            echo '<tr class="summary-row"><td colspan="6"><div class="summary-wrap">';
            echo '<span class="summary-box">Dicetak: ' . e(now()->format('d/m/Y H:i:s')) . '</span>';
            echo '<span class="summary-box">Total Pencacah: ' . count($rekapRows) . '</span>';
            echo '<span class="summary-box">Total Usaha Besar: ' . array_sum(array_map(static fn (array $row): int => (int) ($row['total'] ?? 0), $rekapRows)) . '</span>';
            echo '</div></td></tr>';
            echo '<tr><td colspan="6" style="padding:0 18px 18px;"><div style="height:1px;background:#e2e8f0;"></div></td></tr>';
            echo '</table>';
            echo '<table class="data-table">';
            echo '<thead><tr><th style="width:48px;">No</th><th>Nama Pencacah</th><th style="width:120px;">Total</th><th style="width:120px;">Open</th><th style="width:120px;">Process</th><th style="width:120px;">Success</th></tr></thead>';
            echo '<tbody>';

            if (empty($rekapRows)) {
                echo '<tr><td class="empty" colspan="6">Belum ada data untuk direkap.</td></tr>';
            } else {
                foreach ($rekapRows as $index => $row) {
                    echo '<tr>';
                    echo '<td class="num">' . e((string) ($index + 1)) . '</td>';
                    echo '<td class="name">' . e((string) ($row['nama_pencacah'] ?? '-')) . '</td>';
                    echo '<td class="num">' . e((string) ((int) ($row['total'] ?? 0))) . '</td>';
                    echo '<td class="num open">' . e((string) ((int) ($row['open'] ?? 0))) . '</td>';
                    echo '<td class="num process">' . e((string) ((int) ($row['process'] ?? 0))) . '</td>';
                    echo '<td class="num success">' . e((string) ((int) ($row['success'] ?? 0))) . '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</body>';
            echo '</html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function updateUsahaBesarStatus(Request $request, string $id): JsonResponse
    {
        $allowed = ['open', 'process', 'success'];
        $statusRaw = (string) $request->input('status', '');
        $status = strtolower(trim($statusRaw));

        if (!in_array($status, $allowed, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Status tidak valid.',
            ], 422);
        }

        $credentials = $this->resolveCredentialsPath((string) env('GOOGLE_SERVICE_ACCOUNT_JSON', ''));
        $spreadsheetId = (string) env('GOOGLE_SHEETS_SPREADSHEET_ID', '');

        if ($credentials === null || $spreadsheetId === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Konfigurasi spreadsheet belum lengkap.',
            ], 500);
        }

        try {
            Log::debug('updateUsahaBesarStatus running', [
                'mode' => 'php-http',
                'credentials' => $credentials,
                'spreadsheetId' => $spreadsheetId,
                'id' => $id,
                'status' => strtoupper($status),
            ]);

            $serviceAccount = $this->loadServiceAccountCredentials($credentials);
            $accessToken = $this->createGoogleAccessToken($serviceAccount, ['https://www.googleapis.com/auth/spreadsheets']);
            $sheetValue = $this->statusToSheetValue(strtoupper($status));

            $headerRows = $this->getSheetValues($spreadsheetId, $accessToken, "'Data_Usaha_Besar'!A1:Z1");
            if (empty($headerRows[0])) {
                throw new \RuntimeException('Header row not found in Data_Usaha_Besar');
            }

            $headers = array_map(static fn ($value) => trim((string) $value), $headerRows[0]);
            $headerIndex = array_flip(array_filter($headers, static fn ($value) => $value !== ''));

            $idCol = $headerIndex['id_usaha_besar'] ?? null;
            $statusCol = $headerIndex['status'] ?? null;
            if ($idCol === null || $statusCol === null) {
                throw new \RuntimeException('Required columns id_usaha_besar/status not found');
            }

            $rows = $this->getSheetValues($spreadsheetId, $accessToken, "'Data_Usaha_Besar'!A2:Z");
            if (empty($rows)) {
                throw new \RuntimeException('No data rows found in Data_Usaha_Besar');
            }

            $targetRowNumber = null;
            foreach ($rows as $offset => $row) {
                $rowValues = array_values($row);
                $currentId = $rowValues[$idCol] ?? '';
                if (trim((string) $currentId) === trim((string) $id)) {
                    $targetRowNumber = $offset + 2;
                    break;
                }
            }

            if ($targetRowNumber === null) {
                throw new \RuntimeException('id_usaha_besar not found: ' . $id);
            }

            $columnLetter = $this->columnLetter((int) $statusCol + 1);
            $updateRange = "'Data_Usaha_Besar'!{$columnLetter}{$targetRowNumber}";
            $this->updateSheetValue($spreadsheetId, $accessToken, $updateRange, $sheetValue);

            Log::debug('updateUsahaBesarStatus success', [
                'sheet' => 'Data_Usaha_Besar',
                'row' => $targetRowNumber,
                'range' => $updateRange,
            ]);
            // Update local UI seed so status change reflects immediately without running Python
            $localUpdated = false;
            try {
                $seedPath = base_path('ui/ui_seed_data.json');
                if (is_file($seedPath) && is_readable($seedPath)) {
                    $contents = file_get_contents($seedPath);
                    $decoded = json_decode((string) $contents, true);
                    if (is_array($decoded) && isset($decoded['usaha_besar']) && is_array($decoded['usaha_besar'])) {
                        foreach ($decoded['usaha_besar'] as &$entry) {
                            if (isset($entry['id_usaha_besar']) && trim((string)$entry['id_usaha_besar']) === trim((string)$id)) {
                                $entry['status'] = $sheetValue;
                                $localUpdated = true;
                                break;
                            }
                        }
                        unset($entry);

                        if ($localUpdated) {
                            file_put_contents($seedPath, json_encode($decoded, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), LOCK_EX);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('updateUsahaBesarStatus local seed update failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Status berhasil disimpan ke spreadsheet.',
                'data' => [
                    'id_usaha_besar' => $id,
                    'status' => strtoupper($status),
                    'local_seed_updated' => $localUpdated,
                ],
                'output' => json_encode([
                    'sheet' => 'Data_Usaha_Besar',
                    'row' => $targetRowNumber,
                    'range' => $updateRange,
                    'sheet_value' => $sheetValue,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            Log::debug('updateUsahaBesarStatus failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['ok' => false, 'message' => 'Gagal menyimpan status.'], 500);
        }
    }

    public function webhookSheetSync(Request $request): JsonResponse
    {
        $secret = (string) $request->header('X-SYNC-SECRET', '');
        $expected = (string) env('SYNC_WEBHOOK_SECRET', '');
        if ($expected === '' || !hash_equals($expected, $secret)) {
            return response()->json(['ok' => false, 'message' => 'Invalid secret'], 403);
        }

        $lock = Cache::lock('sheet_sync_lock', 10);
        if (!$lock->get()) {
            return response()->json(['ok' => true, 'message' => 'Sync already in progress'], 202);
        }

        try {
            $result = $this->refreshSeedFromGoogleSheets();

            $lock->release();
            return response()->json([
                'ok' => true,
                'message' => 'Sync completed',
                'counts' => [
                    'cards' => count($result['cards'] ?? []),
                    'usaha_besar' => count($result['usaha_besar'] ?? []),
                    'kbli' => count($result['kbli'] ?? []),
                ],
            ]);
        } catch (\Throwable $e) {
            $lock->release();
            Log::error('webhookSheetSync failed: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Sync failed', 'error' => $e->getMessage()], 500);
        }
    }

    private function loadServiceAccountCredentials(string $credentialsPath): array
    {
        $contents = file_get_contents($credentialsPath);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read credentials file.');
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid credentials JSON.');
        }

        if (empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw new \RuntimeException('Service account JSON missing required fields.');
        }

        return $decoded;
    }

    private function buildUsahaBesarRekapRows(): array
    {
        $ui = $this->loadUiData();
        $usahaBesar = is_array($ui['usaha_besar'] ?? null) ? $ui['usaha_besar'] : [];
        $grouped = [];

        foreach ($usahaBesar as $row) {
            if (!is_array($row)) {
                continue;
            }

            $namaPencacah = trim((string) ($row['nama_pencacah'] ?? ''));
            if ($namaPencacah === '') {
                $namaPencacah = 'Tanpa Pencacah';
            }

            if (!isset($grouped[$namaPencacah])) {
                $grouped[$namaPencacah] = [
                    'nama_pencacah' => $namaPencacah,
                    'total' => 0,
                    'open' => 0,
                    'process' => 0,
                    'success' => 0,
                ];
            }

            $grouped[$namaPencacah]['total']++;

            $status = strtolower(trim((string) ($row['status'] ?? '')));
            if ($status === 'open') {
                $grouped[$namaPencacah]['open']++;
            } elseif ($status === 'process') {
                $grouped[$namaPencacah]['process']++;
            } elseif ($status === 'success') {
                $grouped[$namaPencacah]['success']++;
            }
        }

        $rows = array_values($grouped);
        usort($rows, static function (array $left, array $right): int {
              $totalComparison = (int) ($right['total'] ?? 0) <=> (int) ($left['total'] ?? 0);
              if ($totalComparison !== 0) {
                 return $totalComparison;
              }

              return strcmp((string) ($left['nama_pencacah'] ?? ''), (string) ($right['nama_pencacah'] ?? ''));
        });

        return $rows;
    }

    private function buildUsahaBesarRekapSummary(array $rekapRows): array
    {
        $summary = [
            'pencacah_count' => 0,
            'total' => 0,
            'open' => 0,
            'process' => 0,
            'success' => 0,
        ];

        foreach ($rekapRows as $row) {
            $summary['pencacah_count']++;
            $summary['total'] += (int) ($row['total'] ?? 0);
            $summary['open'] += (int) ($row['open'] ?? 0);
            $summary['process'] += (int) ($row['process'] ?? 0);
            $summary['success'] += (int) ($row['success'] ?? 0);
        }

        return $summary;
    }

    private function normalizePrivateKey(string $privateKey): string
    {
        $text = str_replace(["\r\n", '\\n'], "\n", trim($privateKey));

        if (str_starts_with($text, '"') && str_ends_with($text, '"')) {
            $text = trim($text, '"');
        }

        if (str_starts_with($text, "'") && str_ends_with($text, "'")) {
            $text = trim($text, "'");
        }

        $beginMarker = '-----BEGIN PRIVATE KEY-----';
        $endMarker = '-----END PRIVATE KEY-----';
        $beginIndex = strpos($text, $beginMarker);
        $endIndex = strpos($text, $endMarker);

        if ($beginIndex !== false && $endIndex !== false) {
            $text = substr($text, $beginIndex, $endIndex - $beginIndex + strlen($endMarker));
        }

        if (!str_starts_with($text, $beginMarker) || !str_ends_with($text, $endMarker)) {
            throw new \RuntimeException('Invalid private key format.');
        }

        return $text . "\n";
    }

    private function createGoogleAccessToken(array $credentials, array $scopes): string
    {
        $privateKey = $this->normalizePrivateKey((string) $credentials['private_key']);
        $keyResource = openssl_pkey_get_private($privateKey);
        if ($keyResource === false) {
            throw new \RuntimeException('Unable to load private key.');
        }

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $claims = [
            'iss' => (string) $credentials['client_email'],
            'scope' => implode(' ', $scopes),
            'aud' => (string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token'),
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $headerSegment = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $claimsSegment = $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));
        $signingInput = $headerSegment . '.' . $claimsSegment;

        $signature = '';
        if (!openssl_sign($signingInput, $signature, $keyResource, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Failed to sign JWT assertion.');
        }

        $assertion = $signingInput . '.' . $this->base64UrlEncode($signature);

        $response = Http::withoutVerifying()
            ->asForm()
            ->timeout(60)
            ->post((string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token'), [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Token request failed: ' . $response->status() . ' ' . $response->body());
        }

        $accessToken = (string) $response->json('access_token');
        if ($accessToken === '') {
            throw new \RuntimeException('Access token missing from token response.');
        }

        return $accessToken;
    }

    private function getSheetValues(string $spreadsheetId, string $accessToken, string $range): array
    {
        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->acceptJson()
            ->timeout(60)
            ->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . rawurlencode($range), [
                'majorDimension' => 'ROWS',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Sheet read failed: ' . $response->status() . ' ' . $response->body());
        }

        $values = $response->json('values');
        return is_array($values) ? $values : [];
    }

    private function updateSheetValue(string $spreadsheetId, string $accessToken, string $range, string $value): void
    {
        $response = Http::withoutVerifying()
            ->withToken($accessToken)
            ->acceptJson()
            ->timeout(60)
            ->put("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . rawurlencode($range) . '?valueInputOption=RAW', [
                'values' => [[$value]],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Sheet update failed: ' . $response->status() . ' ' . $response->body());
        }
    }

    private function statusToSheetValue(string $status): string
    {
        return match ($status) {
            'OPEN' => 'Open',
            'PROCESS' => 'Process',
            'SUCCESS' => 'Success',
            default => throw new \RuntimeException('Status tidak valid.'),
        };
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function columnLetter(int $columnNumber): string
    {
        $result = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $result = chr(65 + ($columnNumber % 26)) . $result;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $result;
    }

    private function resolveCredentialsPath(string $credentials): ?string
    {
        $credentials = trim($credentials);
        if ($credentials === '') {
            return null;
        }

        if (
            str_starts_with($credentials, '/') ||
            str_starts_with($credentials, '\\') ||
            preg_match('/^[A-Za-z]:\\\\/', $credentials) === 1
        ) {
            return $credentials;
        }

        return base_path($credentials);
    }

    public function refreshSeed(Request $request): JsonResponse
    {
        $password = (string) $request->input('password', '');
        $expected = (string) config('app.pandu_refresh_password', '');
        $allowGoogleSync = $request->boolean('sync_google', false);

        if ($expected === '' || !hash_equals($expected, $password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Password refresh tidak valid.',
            ], 403);
        }

        $phpBinary = (string) config('app.pandu_php_binary', 'php');
        $pythonBinary = (string) config('app.pandu_python_binary', '');
        if ($pythonBinary !== '') {
            putenv('PANDU_PYTHON_BINARY=' . $pythonBinary);
            Log::debug('exported PANDU_PYTHON_BINARY', ['value' => $pythonBinary]);
        }
        if ($allowGoogleSync) {
            try {
                $result = $this->refreshSeedFromGoogleSheets();

                return response()->json([
                    'ok' => true,
                    'message' => 'Spreadsheet berhasil disinkronkan dan seed diperbarui.',
                    'counts' => [
                        'cards' => count($result['cards'] ?? []),
                        'usaha_besar' => count($result['usaha_besar'] ?? []),
                        'kbli' => count($result['kbli'] ?? []),
                    ],
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Gagal sinkron spreadsheet: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'ok' => false,
            'message' => 'Refresh tanpa sync_google tidak didukung di server ini.',
        ], 400);
    }

    private function refreshSeedFromGoogleSheets(): array
    {
        $credentials = $this->resolveCredentialsPath((string) env('GOOGLE_SERVICE_ACCOUNT_JSON', ''));
        $spreadsheetId = (string) env('GOOGLE_SHEETS_SPREADSHEET_ID', '');

        if ($credentials === null || $spreadsheetId === '') {
            throw new \RuntimeException('Google Sheets not configured');
        }

        $serviceAccount = $this->loadServiceAccountCredentials($credentials);
        $accessToken = $this->createGoogleAccessToken($serviceAccount, ['https://www.googleapis.com/auth/spreadsheets.readonly']);

        $gmapsRows = $this->buildNormalizedRowsFromSheet($spreadsheetId, $accessToken, 'Master_GoogleMaps', 'Master_GoogleMaps');
        $tokpedRows = $this->buildNormalizedRowsFromSheet($spreadsheetId, $accessToken, 'Master_Tokopedia', 'Master_Tokopedia');

        $kecamatanSet = [];
        $desaSet = [];
        $desaByKecamatan = [];
        $rwRtByDesa = [];
        $matchedTrue = 0;
        $matchedFalse = 0;

        $trackDistricts = static function (array $row) use (&$kecamatanSet, &$desaSet, &$desaByKecamatan, &$rwRtByDesa, &$matchedTrue, &$matchedFalse): void {
            $kecamatan = trim((string) ($row['nmkec'] ?? ''));
            $desa = trim((string) ($row['nmdesa'] ?? ''));
            $rw = trim((string) ($row['rw'] ?? ''));
            $rt = trim((string) ($row['rt'] ?? ''));

            if ($kecamatan !== '') {
                $kecamatanSet[$kecamatan] = true;
                if ($desa !== '') {
                    $desaSet[$desa] = true;
                    $desaByKecamatan[$kecamatan][$desa] = true;
                    if (!isset($rwRtByDesa[$desa])) {
                        $rwRtByDesa[$desa] = ['RW' => [], 'RT' => []];
                    }
                    if ($rw !== '') {
                        $rwRtByDesa[$desa]['RW'][$rw] = true;
                    }
                    if ($rt !== '') {
                        $rwRtByDesa[$desa]['RT'][$rt] = true;
                    }
                }
            }

            if (!empty($row['is_matched'])) {
                $matchedTrue++;
            } else {
                $matchedFalse++;
            }
        };

        foreach ($gmapsRows as $row) {
            $trackDistricts($row);
        }
        foreach ($tokpedRows as $row) {
            $trackDistricts($row);
        }

        $usahaBesarRows = $this->buildRowsFromSimpleSheet($spreadsheetId, $accessToken, 'Data_Usaha_Besar', [
            'id_usaha_besar' => 'id_usaha_besar',
            'nama_usaha' => 'nama_usaha',
            'nama_pencacah' => 'nama_pencacah',
            'status' => 'status',
        ]);

        $kbliRows = $this->buildKbliRowsFromSheet($spreadsheetId, $accessToken, 'Daftar_KBLI');

        $seed = [
            'meta' => [
                'source_file' => 'Google Sheets',
                'generated_at' => now()->toIso8601String(),
                'ui_ready' => true,
                'sheets' => ['Master_GoogleMaps', 'Master_Tokopedia', 'Data_Usaha_Besar', 'Daftar_KBLI', 'Hasil_Verifikasi'],
                'total_rows' => count($gmapsRows) + count($tokpedRows),
                'googlemaps_rows' => count($gmapsRows),
                'tokopedia_rows' => count($tokpedRows),
                'kecamatan_count' => count($kecamatanSet),
                'desa_count_total' => count($desaSet),
                'matched_true' => $matchedTrue,
                'matched_false' => $matchedFalse,
                'usaha_besar_rows' => count($usahaBesarRows),
                'kbli_rows' => count($kbliRows),
            ],
            'filters' => [
                'kecamatan' => array_keys($kecamatanSet),
                'desa_by_kecamatan' => array_map(static fn (array $values): array => array_keys($values), $desaByKecamatan),
                'rw_rt_by_desa' => array_map(static function (array $values): array {
                    return [
                        'RW' => array_keys($values['RW'] ?? []),
                        'RT' => array_keys($values['RT'] ?? []),
                    ];
                }, $rwRtByDesa),
            ],
            'ui_state_shape' => [
                'selected_source_tab' => 'Master_GoogleMaps | Master_Tokopedia | ALL',
                'selected_kecamatan' => 'string | null',
                'selected_desa' => 'string | null',
                'selected_rw' => 'string | null',
                'selected_rt' => 'string | null',
                'search_query' => 'string | null',
                'match_status' => 'MATCH | NOT_MATCH | ALL',
                'cards' => 'array of business card objects',
            ],
            'cards' => array_merge($gmapsRows, $tokpedRows),
            'preview_cards' => array_merge(array_slice($gmapsRows, 0, 5), array_slice($tokpedRows, 0, 5)),
            'usaha_besar' => $usahaBesarRows,
            'kbli' => $kbliRows,
        ];

        $seedPath = base_path('ui/ui_seed_data.json');
        file_put_contents($seedPath, json_encode($seed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

        return $seed;
    }

    private function buildNormalizedRowsFromSheet(string $spreadsheetId, string $accessToken, string $sheetName, string $sourceTab): array
    {
        $rows = $this->getSheetValues($spreadsheetId, $accessToken, "'{$sheetName}'!A1:Z");
        if (empty($rows) || !isset($rows[0]) || !is_array($rows[0])) {
            return [];
        }

        $headers = array_map(static fn ($value) => trim((string) $value), $rows[0]);
        $index = array_flip(array_filter($headers, static fn ($value) => $value !== ''));
        $items = [];

        foreach (array_slice($rows, 1) as $row) {
            $row = array_values(is_array($row) ? $row : []);
            $item = $this->normalizeCardRow($row, $index, $sourceTab);
            $items[] = $item;
        }

        return $items;
    }

    private function normalizeCardRow(array $row, array $index, string $sourceTab): array
    {
        $get = static function (string $name) use ($row, $index) {
            $position = $index[$name] ?? null;
            return $position !== null && array_key_exists($position, $row) ? $row[$position] : null;
        };

        $firstAvailable = static function (string ...$names) use ($get) {
            foreach ($names as $name) {
                $value = $get($name);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $kecamatan = $get('nmkec');
        $desa = $get('nmdesa');
        $nmsls = $get('nmsls');
        [$rt, $rw] = $this->parseRwRt($nmsls !== null ? (string) $nmsls : null);
        $matchStatus = strtoupper((string) $get('is_matched')) === 'TRUE' ? 'MATCH' : 'NOT_MATCH';
        $latRaw = $this->asFloat($firstAvailable(
            'source_latitude_normalized',
            'source_latitude',
            'scraping_lat_x',
            'match_latitude',
            'candidate_latitude'
        ));
        $lonRaw = $this->asFloat($firstAvailable(
            'source_longitude_normalized',
            'source_longitude',
            'scraping_lon_x',
            'match_longitude',
            'candidate_longitude'
        ));
        [$latFinal, $lonFinal] = $this->normalizeCoords($latRaw, $lonRaw);

        return [
            'source_tab' => $sourceTab,
            'id_scraping' => $get('id_scraping'),
            'nama_usaha_sumber' => $get('nama_usaha_sumber'),
            'kategori_sumber' => $get('kategori_sumber'),
            'subkategori_sumber' => $get('subkategori_sumber'),
            'nmkec' => $kecamatan,
            'nmdesa' => $desa,
            'nmsls' => $nmsls,
            'rt' => $rt,
            'rw' => $rw,
            'is_matched' => strtoupper((string) $get('is_matched')) === 'TRUE',
            'match_status' => $matchStatus,
            'source_latitude_normalized' => $latRaw,
            'source_longitude_normalized' => $lonRaw,
            'source_latitude' => $latFinal,
            'source_longitude' => $lonFinal,
            'match_idsbr' => $get('match_idsbr'),
            'match_nama_usaha' => $get('match_nama_usaha'),
            'similarity_score' => $get('similarity_score'),
            'jarak_km' => $get('jarak_km'),
            'keterangan' => $get('keterangan'),
        ];
    }

    private function buildRowsFromSimpleSheet(string $spreadsheetId, string $accessToken, string $sheetName, array $columns): array
    {
        $rows = $this->getSheetValues($spreadsheetId, $accessToken, "'{$sheetName}'!A1:Z");
        if (empty($rows) || !isset($rows[0]) || !is_array($rows[0])) {
            return [];
        }

        $headers = array_map(static fn ($value) => trim((string) $value), $rows[0]);
        $index = array_flip(array_filter($headers, static fn ($value) => $value !== ''));
        $items = [];

        foreach (array_slice($rows, 1) as $row) {
            $row = array_values(is_array($row) ? $row : []);
            if (!array_filter($row, static fn ($value) => $value !== null && $value !== '')) {
                continue;
            }

            $item = [];
            foreach ($columns as $source => $target) {
                $position = $index[$source] ?? null;
                $item[$target] = $position !== null && array_key_exists($position, $row) ? $row[$position] : null;
            }

            $items[] = $item;
        }

        return $items;
    }

    private function buildKbliRowsFromSheet(string $spreadsheetId, string $accessToken, string $sheetName): array
    {
        $rows = $this->getSheetValues($spreadsheetId, $accessToken, "'{$sheetName}'!A1:Z");
        if (empty($rows) || !isset($rows[0]) || !is_array($rows[0])) {
            return [];
        }

        $headers = array_map(static fn ($value) => trim((string) $value), $rows[0]);
        $index = array_flip(array_filter($headers, static fn ($value) => $value !== ''));
        $items = [];

        foreach (array_slice($rows, 1) as $row) {
            $row = array_values(is_array($row) ? $row : []);
            if (!array_filter($row, static fn ($value) => $value !== null && $value !== '')) {
                continue;
            }

            $kodePos = $index['Kode'] ?? $index['KBLI'] ?? null;
            $deskripsiPos = $index['Deskripsi'] ?? $index['Judul'] ?? null;
            $kode = $kodePos !== null && array_key_exists($kodePos, $row) ? $row[$kodePos] : null;
            if ($kode === null || trim((string) $kode) === '') {
                continue;
            }

            $items[] = [
                'kode' => $kode,
                'deskripsi' => $deskripsiPos !== null && array_key_exists($deskripsiPos, $row) ? $row[$deskripsiPos] : null,
            ];
        }

        return $items;
    }

    private function parseRwRt(?string $nmsls): array
    {
        if ($nmsls === null || trim($nmsls) === '') {
            return [null, null];
        }

        preg_match('/RW\s+(\d+)/i', $nmsls, $rwMatch);
        preg_match('/RT\s+(\d+)/i', $nmsls, $rtMatch);

        $rw = $rwMatch[1] ?? null;
        $rt = $rtMatch[1] ?? null;

        return [$rt, $rw];
    }

    private function asFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = str_replace(['.', ','], ['', '.'], trim((string) $value));
        return is_numeric($text) ? (float) $text : null;
    }

    private function normalizeCoords(?float $lat, ?float $lon): array
    {
        if ($lat === null || $lon === null) {
            return [null, null];
        }

        $isValid = static fn (float $a, float $b): bool => abs($a) <= 90 && abs($b) <= 180;
        if ($isValid($lat, $lon)) {
            return [$lat, $lon];
        }

        $factors = [1000000, 10000000, 100000, 10000, 1000, 100, 10];
        foreach ($factors as $factor) {
            $la = $lat / $factor;
            $lo = $lon / $factor;
            if ($isValid($la, $lo)) {
                return [$la, $lo];
            }
        }

        foreach ($factors as $factor) {
            $la = $lon / $factor;
            $lo = $lat / $factor;
            if ($isValid($la, $lo)) {
                return [$la, $lo];
            }
        }

        $la = $lat;
        $lo = $lon;
        for ($i = 0; $i < 7; $i++) {
            $la /= 10;
            $lo /= 10;
            if ($isValid($la, $lo)) {
                return [$la, $lo];
            }
        }

        return [null, null];
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json(['ok' => true, 'items' => []]);
        }

        $needle = mb_strtolower($query);
        $ui = $this->loadUiData();
        $cards = is_array($ui['cards'] ?? null) ? $ui['cards'] : [];
        $seen = [];
        $items = [];

        foreach ($cards as $card) {
            if (!is_array($card)) {
                continue;
            }

            $name = (string) ($card['nama_usaha_sumber'] ?? $card['match_nama_usaha'] ?? '');
            $kategori = (string) ($card['kategori_sumber'] ?? '');
            $desa = (string) ($card['nmdesa'] ?? '');
            $kec = (string) ($card['nmkec'] ?? '');
            $rw = (string) ($card['rw'] ?? '');
            $rt = (string) ($card['rt'] ?? '');

            $haystack = mb_strtolower($name . ' ' . $kategori . ' ' . $desa . ' ' . $kec . ' rw ' . $rw . ' rt ' . $rt);
            if (mb_strpos($haystack, $needle) === false) {
                continue;
            }

            $key = mb_strtolower($name);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $lokasi = trim(($kec !== '' ? $kec : '') . ($desa !== '' ? ' / ' . $desa : '') . ($rw !== '' ? ' / RW ' . $rw : '') . ($rt !== '' ? ' / RT ' . $rt : ''));
            
            $items[] = [
                'label' => $name,
                'value' => $name,
                'kategori' => $kategori,
                'lokasi' => $lokasi,
            ];

            if (count($items) >= 20) {
                break;
            }
        }

        return response()->json(['ok' => true, 'items' => $items]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $authUser = $this->getAuthUser($request);
        if ($authUser === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Sesi login tidak ditemukan. Silakan login ulang.',
                ], 401);
            }

            return redirect()->route('auth.login.form')->with('auth_error', 'Sesi login tidak ditemukan.');
        }

        $validated = $request->validate([
            'id_scraping' => ['required', 'string', 'max:255'],
            'source_tab' => ['required', Rule::in(['Master_GoogleMaps', 'Master_Tokopedia'])],
            'match_idsbr' => ['nullable', 'string', 'max:255'],
            'match_nama_usaha' => ['nullable', 'string', 'max:255'],
            'match_alamat' => ['nullable', 'string', 'max:1000'],
            'verification_status' => ['required', Rule::in(['MATCH', 'NOT_MATCH', 'DUPLICATE', 'NEED_REVIEW', 'OUTSIDE_AREA', 'NO_FINDING'])],
            'officer_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'officer_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'verified_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'verified_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo_url' => ['nullable', 'url', 'max:1000'],
            'device_id' => ['nullable', 'string', 'max:191'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $idempotencyPayload = $validated;
        $idempotencyPayload['officer_name'] = (string) ($authUser['name'] ?? 'petugas');

        $idempotencyKey = $validated['idempotency_key'] ?? $this->generateIdempotencyKey($idempotencyPayload);

        while (VerificationResult::where('idempotency_key', $idempotencyKey)->exists()) {
            $idempotencyKey = $this->generateIdempotencyKey($idempotencyPayload);
        }

        $record = VerificationResult::create([
            'submitted_at' => now(),
            'id_scraping' => $validated['id_scraping'],
            'source_tab' => $validated['source_tab'],
            'match_idsbr' => $validated['match_idsbr'] ?? null,
            'match_nama_usaha' => $validated['match_nama_usaha'] ?? null,
            'match_alamat' => $validated['match_alamat'] ?? null,
            'verification_status' => $validated['verification_status'],
            'officer_name' => (string) ($authUser['name'] ?? 'Petugas'),
            'officer_id' => isset($authUser['employee_id']) ? (string) $authUser['employee_id'] : null,
            'officer_latitude' => $validated['officer_latitude'] ?? null,
            'officer_longitude' => $validated['officer_longitude'] ?? null,
            'verified_latitude' => $validated['verified_latitude'] ?? null,
            'verified_longitude' => $validated['verified_longitude'] ?? null,
            'distance_km' => $validated['distance_km'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'photo_url' => $validated['photo_url'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            'idempotency_key' => $idempotencyKey,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Verifikasi berhasil disimpan.',
                'data' => [
                    'id' => $record->id,
                    'idempotency_key' => $record->idempotency_key,
                ],
            ]);
        }

        return back()->with('status', 'Verifikasi berhasil disimpan.');
    }

    private function getAuthUser(Request $request): ?array
    {
        $user = $request->user();
        if ($user === null) {
            return null;
        }

        return [
            'username' => (string) ($user->username ?? ''),
            'name' => (string) ($user->name ?? ''),
            'employee_id' => (string) ($user->employee_id ?? ''),
            'role' => (string) ($user->role ?? ''),
        ];
    }

    private function generateIdempotencyKey(array $payload): string
    {
        $officer = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string) ($payload['officer_name'] ?? 'petugas')) ?? 'petugas');
        $officer = trim($officer, '-');
        if ($officer === '') {
            $officer = 'petugas';
        }

        return sprintf(
            '%s-%s-%s-%s',
            $payload['id_scraping'] ?? 'row',
            now()->format('YmdHis'),
            substr($officer, 0, 24),
            substr(sha1(uniqid((string) mt_rand(), true)), 0, 10)
        );
    }
}
