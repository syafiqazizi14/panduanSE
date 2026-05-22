<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\VerificationResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
