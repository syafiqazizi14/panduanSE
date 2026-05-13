# Verification Append Payload

This document defines the append-only payload structure for writing verification results into `Hasil_Verifikasi`.

## Purpose

- Store field verification actions from officers.
- Keep each verification submission immutable once appended.
- Support idempotent writes to avoid duplicate rows.

## Recommended Append Order

Use one row per verification event with a fixed column order.

| Column | Type | Required | Description |
|---|---|---:|---|
| timestamp | datetime | yes | Server-side append time |
| id_scraping | string | yes | Source business row ID |
| source_tab | string | yes | `Master_GoogleMaps` or `Master_Tokopedia` |
| match_idsbr | string | no | Matched official ID/reference |
| match_nama_usaha | string | no | Matched official business name |
| match_alamat | string | no | Matched official address |
| verification_status | string | yes | Final status, for example `MATCH`, `NOT_MATCH`, `DUPLICATE`, `NEED_REVIEW` |
| officer_name | string | yes | Verifying officer name |
| officer_id | string | no | Officer ID or NIP |
| officer_latitude | decimal | no | Officer GPS latitude |
| officer_longitude | decimal | no | Officer GPS longitude |
| verified_latitude | decimal | no | Business point latitude verified in the field |
| verified_longitude | decimal | no | Business point longitude verified in the field |
| distance_km | number | no | Distance between reference and field point |
| notes | text | no | Officer notes |
| photo_url | string | no | Optional evidence URL |
| device_id | string | no | Optional device identifier |
| idempotency_key | string | yes | Unique key to prevent duplicate append |

## Canonical Payload Shape

```json
{
  "timestamp": "2026-05-07T10:30:00+07:00",
  "id_scraping": "gmaps_1",
  "source_tab": "Master_GoogleMaps",
  "match_idsbr": "33268085",
  "match_nama_usaha": "ACOSYS PROGRAM KASIR DAN AKUNTANSI",
  "match_alamat": "Griya Wonosari Indah No.B-01, Wonosari, Kec. Ngoro, Kabupaten Mojokerto, Jawa Timur 61385",
  "verification_status": "MATCH",
  "officer_name": "Budi Santoso",
  "officer_id": "BPS-MJK-001",
  "officer_latitude": -7.5632,
  "officer_longitude": 112.6337,
  "verified_latitude": -7.5631,
  "verified_longitude": 112.6336,
  "distance_km": 0.12,
  "notes": "Lokasi sesuai papan nama dan koordinat",
  "photo_url": "https://...",
  "device_id": "android-abc123",
  "idempotency_key": "gmaps_1-20260507-103000-bps-mjk-001"
}
```

## Field Rules

### Required
- `timestamp`
- `id_scraping`
- `source_tab`
- `verification_status`
- `officer_name`
- `idempotency_key`

### Validation Notes
- `source_tab` must match one of the master sheet names.
- `verification_status` should use a fixed enum.
- Latitude and longitude fields should be normalized to decimal degrees.
- Empty optional fields should be stored as null.
- `idempotency_key` must be unique per submission.

## Suggested Verification Status Enum

- `MATCH`
- `NOT_MATCH`
- `DUPLICATE`
- `NEED_REVIEW`
- `OUTSIDE_AREA`
- `NO_FINDING`

## Append Behavior

1. Validate the payload.
2. Reject if `idempotency_key` already exists.
3. Build the row using the fixed column order.
4. Append the row to `Hasil_Verifikasi`.
5. Return the appended timestamp and row reference.

## Laravel Intent

A future Laravel controller should:
- validate the payload using a Form Request,
- normalize coordinates and region values,
- append the row through a Sheets service,
- and store the idempotency key in cache or a lightweight persistence layer.
