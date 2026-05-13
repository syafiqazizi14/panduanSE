# UI Frontend Contract

This document defines the frontend-facing data shape for the UMKM verification UI.

## Purpose

The frontend should treat the backend as a cached data source that returns:
- filter options,
- business cards,
- and verification submission payload definitions.

## Core State

### Global State
- `selectedSourceTab`: `ALL | Master_GoogleMaps | Master_Tokopedia`
- `selectedKecamatan`: `string | null`
- `selectedDesa`: `string | null`
- `searchQuery`: `string | null`
- `matchStatus`: `ALL | MATCH | NOT_MATCH`
- `cards`: array of business card objects
- `loading`: boolean
- `error`: string | null

### Card Shape

```json
{
  "source_tab": "Master_GoogleMaps",
  "id_scraping": "gmaps_1",
  "nama_usaha_sumber": "Acosys Program Kasir dan Akuntansi",
  "kategori_sumber": "Software Development",
  "subkategori_sumber": "Perusahaan Software",
  "nmkec": "NGORO",
  "nmdesa": "WONOSARI",
  "nmsls": "RT 001 RW 003 DUSUN WONOSARI",
  "is_matched": true,
  "match_status": "MATCH",
  "source_latitude_normalized": -7.5631009,
  "source_longitude_normalized": 112.6336362,
  "match_idsbr": "33268085",
  "match_nama_usaha": "ACOSYS PROGRAM KASIR DAN AKUNTANSI",
  "similarity_score": 100,
  "jarak_km": 68,
  "keterangan": "Match nama + lokasi"
}
```

## UI Sections

### Filter Bar
- Kecamatan dropdown.
- Desa dropdown that depends on selected kecamatan.
- Source tab selector.
- Search input.
- Match status selector.

### Result Cards
- Title: `nama_usaha_sumber`
- Subtitle: `kategori_sumber` or `lokasi_ringkas`
- Region label: `nmkec`, `nmdesa`, `nmsls`
- Badge: `MATCH` or `NOT_MATCH`
- Small meta row: score, distance, source tab.
- Tap action: open verification drawer or detail panel.

### Verification Drawer
- Status selector.
- Notes textarea.
- Officer identity fields.
- GPS fields if available.
- Submit button to append to `Hasil_Verifikasi`.

## Recommended Frontend Behavior

1. Load filter options before cards.
2. Apply all filtering from cached data.
3. Never query Google Sheets directly from the browser.
4. Use normalized coordinates for any map or distance display.
5. Treat `Hasil_Verifikasi` as write-only from the UI.

## UI Readiness Criteria

The data is considered UI-ready when:
- both master sheets have one shared schema,
- region fields are cleaned,
- normalized coordinates exist,
- filter indexes are available,
- and verification payload columns are defined.

All of those conditions are now documented in the `schema/` folder and the seed package in `ui/ui_seed_data.json`.