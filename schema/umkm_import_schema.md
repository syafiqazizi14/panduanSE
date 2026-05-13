# UMKM Import Schema

This document defines the canonical import schema for the Mojokerto UMKM workbook so it can be consumed consistently by a future Laravel 11 application.

## Workbook Sheets

### 1. `Master_GoogleMaps`
- Source role: master reference, read-only.
- Intended use: matching and verification lookup.
- Row format: one business per row.

### 2. `Master_Tokopedia`
- Source role: master reference, read-only.
- Intended use: matching and verification lookup.
- Row format: one business per row.

### 3. `Hasil_Verifikasi`
- Source role: append-only output log.
- Intended use: store verification actions from field officers.

## Canonical Columns

The canonical model keeps one shared schema across both master sheets.

| Column | Type | Required | Description |
|---|---|---:|---|
| source_tab | string | yes | Origin sheet name, for example `Master_GoogleMaps` or `Master_Tokopedia` |
| id_scraping | string | yes | Primary business row identifier |
| nama_usaha_sumber | string | yes | Original business name from the source sheet |
| slug | string | no | Tokopedia slug or equivalent identifier |
| link_sumber | string | no | Source URL |
| lokasi_ringkas | string | no | Short location label or city/region summary |
| badge | string | no | Tokopedia account badge or similar source label |
| rating | number | no | Store rating |
| jumlah_produk | integer/string | no | Number of products |
| jumlah_ulasan | integer/string | no | Number of reviews |
| bergabung_sejak | date/string | no | Join date or equivalent source age |
| deskripsi | text | no | Description text |
| kategori_jual | text | no | Tokopedia selling categories |
| kategori_sumber | string | no | Source category from Google Maps |
| subkategori_sumber | string | no | Source subcategory from Google Maps |
| source_latitude | decimal | no | Raw latitude from source |
| source_longitude | decimal | no | Raw longitude from source |
| alamat_sumber | text | no | Raw address from source |
| phone_sumber | string | no | Phone number from source |
| website_sumber | string | no | Website URL from source |
| nama_usaha_normal | string | no | Normalized business name |
| nama_usaha_pembanding | string | no | Candidate business name used for matching |
| candidate_latitude | decimal | no | Candidate latitude used for matching |
| candidate_longitude | decimal | no | Candidate longitude used for matching |
| is_matched | boolean | yes | Match status |
| match_idsbr | string | no | Matched BPS/IDSBR reference |
| match_nama_usaha | string | no | Matched canonical business name |
| match_alamat | text | no | Matched address |
| match_latitude | decimal | no | Matched latitude |
| match_longitude | decimal | no | Matched longitude |
| similarity_score | number | no | Match score |
| jarak_km | number | no | Distance in kilometers |
| keterangan | text | no | Match notes |
| geo_jalan | string | no | Street-level geo label |
| geo_desa | string | no | Geo village label |
| geo_kecamatan | string | no | Geo district label |
| geo_kabupaten_kota | string | no | Geo regency/city label |
| idsls | string | no | BPS SLS code |
| nmkec | string | no | Kecamatan name |
| nmsls | string | no | Dusun/RT/RW label |
| nmdesa | string | no | Village name |
| source_latitude_normalized | decimal | no | Normalized latitude for filtering/mapping |
| source_longitude_normalized | decimal | no | Normalized longitude for filtering/mapping |
| candidate_latitude_normalized | decimal | no | Normalized candidate latitude |
| candidate_longitude_normalized | decimal | no | Normalized candidate longitude |
| match_latitude_normalized | decimal | no | Normalized matched latitude |
| match_longitude_normalized | decimal | no | Normalized matched longitude |
| nmkec_clean | string | no | Cleaned uppercase district name |
| nmdesa_clean | string | no | Cleaned uppercase village name |
| nmsls_clean | string | no | Cleaned uppercase SLS label |

## Source Mapping

### Master_GoogleMaps source mapping
- `source_tab` <- literal `Master_GoogleMaps`
- `id_scraping` <- `id_scraping`
- `nama_usaha_sumber` <- `scraping_nama_x`
- `link_sumber` <- `link`
- `kategori_sumber` <- `industry_category`
- `subkategori_sumber` <- `category`
- `source_latitude` <- `scraping_lat_x`
- `source_longitude` <- `scraping_lon_x`
- `alamat_sumber` <- `address`
- `phone_sumber` <- `phone`
- `website_sumber` <- `website`
- `nama_usaha_normal` <- `nama_clean`
- `nama_usaha_pembanding` <- `scraping_nama_y`
- `candidate_latitude` <- `scraping_lat_y`
- `candidate_longitude` <- `scraping_lon_y`
- `is_matched` <- `is_matched`
- `match_idsbr` <- `match_idsbr`
- `match_nama_usaha` <- `match_nama_usaha`
- `match_alamat` <- `match_alamat`
- `match_latitude` <- `match_lat`
- `match_longitude` <- `match_lon`
- `similarity_score` <- `similarity_score`
- `jarak_km` <- `jarak_km`
- `keterangan` <- `keterangan`
- `geo_jalan` <- `geo_jalan`
- `geo_desa` <- `geo_desa`
- `geo_kecamatan` <- `geo_kecamatan`
- `geo_kabupaten_kota` <- `geo_kabupaten_kota`
- `idsls` <- `idsls`
- `nmkec` <- `nmkec`
- `nmsls` <- `nmsls`
- `nmdesa` <- `nmdesa`

### Master_Tokopedia source mapping
- `source_tab` <- literal `Master_Tokopedia`
- `id_scraping` <- `id_scraping`
- `nama_usaha_sumber` <- `nama_toko`
- `slug` <- `slug`
- `link_sumber` <- `link_toko`
- `lokasi_ringkas` <- `lokasi`
- `badge` <- `badge`
- `rating` <- `rating`
- `jumlah_produk` <- `jumlah_produk`
- `jumlah_ulasan` <- `jumlah_ulasan`
- `bergabung_sejak` <- `bergabung_sejak`
- `deskripsi` <- `deskripsi`
- `kategori_jual` <- `kategori_jual`
- `is_matched` <- `is_matched`
- `match_idsbr` <- `match_idsbr`
- `match_nama_usaha` <- `match_nama_usaha`
- `match_alamat` <- `match_alamat`
- `match_latitude` <- `match_latitude`
- `match_longitude` <- `match_longitude`
- `similarity_score` <- `similarity_score`
- `keterangan` <- `keterangan`
- `idsls` <- `idsls`
- `nmkec` <- `nmkec`
- `nmsls` <- `nmsls`
- `nmdesa` <- `nmdesa`

## Validation Rules

1. `source_tab` must always be present and immutable after import.
2. `id_scraping` must be unique within each source sheet.
3. `is_matched` should be normalized to `TRUE` or `FALSE`.
4. `nmkec`, `nmdesa`, and `nmsls` must be stored in cleaned uppercase form for filtering.
5. Coordinate fields must be normalized to decimal degrees before any map-based filtering.
6. `idsls` should be treated as string to preserve leading zeros if they ever appear in future source data.
7. Empty fields should remain null, not zero-filled.

## Laravel Import Intent

A future Laravel import service should:
- read each source tab separately,
- map rows into one shared canonical DTO/array shape,
- cache the normalized data for filtering,
- and write verification output only into `Hasil_Verifikasi`.

## Notes

- The workbook currently contains two master sheets and one empty verification sheet.
- The Google Maps sheet requires coordinate normalization because the raw numeric format is inconsistent.
- The Tokopedia sheet is structurally cleaner but still needs region cleaning and standardized naming.
