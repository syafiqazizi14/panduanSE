# Laravel Import Cache Flow

This blueprint describes the recommended import and cache flow for the Mojokerto UMKM workbook after the canonical schema has been established.

## Goal

- Read both master sheets from the workbook or Google Sheets source.
- Normalize rows into one canonical shape.
- Cache the normalized result for fast in-memory filtering.
- Keep `Hasil_Verifikasi` as an append-only output log.

## Recommended Laravel Structure

### Services
- `App\Services\Sheets\SheetsClient`
  - Low-level Google Sheets API wrapper.
  - Reads ranges and appends rows.
- `App\Services\Sheets\WorkbookImportService`
  - Imports workbook tabs.
  - Maps raw rows into canonical records.
- `App\Services\Sheets\WorkbookCacheService`
  - Stores and retrieves normalized data from cache.
- `App\Services\Sheets\VerificationAppendService`
  - Appends verification rows to `Hasil_Verifikasi`.

### Actions / Jobs
- `ImportMasterSheetsJob`
  - Refreshes cached master data.
- `AppendVerificationJob`
  - Writes verification data asynchronously when needed.

### Controllers
- `DashboardController`
  - Reads cached data only.
- `VerificationController`
  - Validates submission and triggers append.

## Cache Design

### Cache Keys
- `umkm:master:googlemaps:v1`
- `umkm:master:tokopedia:v1`
- `umkm:master:combined:v1`
- `umkm:filters:kecamatan:v1`
- `umkm:filters:desa:{nmkec}:v1`

### Recommended Cache Shape

Store normalized rows as arrays or DTO-like arrays:

```php
[
    'source_tab' => 'Master_GoogleMaps',
    'id_scraping' => 'gmaps_1',
    'nama_usaha_sumber' => 'Acosys Program Kasir dan Akuntansi',
    'link_sumber' => 'https://...',
    'kategori_sumber' => 'Software Development',
    'subkategori_sumber' => 'Perusahaan Software',
    'source_latitude_normalized' => -7.5631009,
    'source_longitude_normalized' => 112.6336362,
    'is_matched' => true,
    'nmkec_clean' => 'NGORO',
    'nmdesa_clean' => 'WONOSARI',
]
```

### Cache TTL
- Suggested default: 6 to 24 hours.
- Use manual refresh for admin actions.
- Prefer explicit invalidation after workbook sync.

## Import Flow

### 1. Fetch raw sheet rows
- Read `Master_GoogleMaps` and `Master_Tokopedia` separately.
- Do not query row by row.
- Pull in batches if using API.

### 2. Map to canonical structure
- Use the canonical schema defined in `schema/umkm_import_schema.md`.
- Add `source_tab`.
- Normalize region labels to uppercase.
- Normalize coordinates to decimal degrees.

### 3. Build derived indexes
- Unique kecamatan list.
- Desa list grouped by kecamatan.
- Optional SLS grouping if needed.
- Store these in cache alongside raw normalized rows.

### 4. Save cache
- Save master maps, tokopedia, and combined collections.
- Save filter indexes separately for fast lookup.

### 5. Serve frontend from cache
- Dashboard uses cached master rows.
- Filter dropdowns read from cached kecamatan/desa indexes.
- Card list queries only the cached collection.

### 6. Append verification
- When user submits verification:
  - validate payload,
  - normalize values,
  - append a new row to `Hasil_Verifikasi`,
  - optionally invalidate related cache if the verification affects local state.

## Filter Flow

### Kecamatan -> Desa Cascade
1. Frontend loads `nmkec` list from cache-backed endpoint.
2. User selects kecamatan.
3. Backend returns desa list for that kecamatan from cache.
4. Frontend updates desa dropdown without reload.
5. Card list filters in memory or via cached endpoint.

### Card Selection
- Each card should include:
  - source name,
  - source tab,
  - cleaned region labels,
  - match status,
  - normalized coordinates,
  - short notes.

## Verification Append Flow

1. User selects a business card.
2. User fills verification status and optional notes.
3. Backend validates and maps the payload.
4. Backend appends a row to `Hasil_Verifikasi`.
5. Backend returns success and optionally the appended row index or timestamp.

## Practical Validation Rules

- `id_scraping` must exist.
- `source_tab` must be one of the known master sheets.
- `nmkec_clean` and `nmdesa_clean` should be derived before filtering.
- Coordinates should be normalized or left null if invalid.
- Duplicate append submissions should be guarded with an idempotency key.

## Suggested Next File Set

If you continue this implementation, the next useful artifacts are:
- a service class skeleton for workbook import,
- a cache repository or cache service skeleton,
- and the append-verification payload schema.
