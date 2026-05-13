# UMKM Seed Generation Automation

## Overview

Setiap kali mengubah data di workbook Excel, seed JSON untuk UI perlu di-refresh. Proses ini sekarang sudah terotomasi dengan script PHP yang mudah dijalankan.

## Manual Trigger

Jalankan command ini kapan saja untuk refresh seed:

```bash
php refresh-seed.php
```

## Sync Verifikasi ke Sheet Hasil_Verifikasi

Jika verifikasi sudah tersimpan di database Laravel, jalankan command ini untuk append data yang belum tersinkron ke sheet `Hasil_Verifikasi`:

```bash
php artisan umkm:sync-verification-sheet
```

Batasi jumlah data per proses (opsional):

```bash
php artisan umkm:sync-verification-sheet --limit=500
```

Command akan:
- mengambil row verifikasi yang `synced_to_sheet_at` masih null,
- append ke workbook melalui script Python,
- menghindari duplikasi berdasarkan `idempotency_key`,
- lalu menandai row sebagai sudah tersinkron.

**Output contoh:**
```
[*] UMKM Seed Generation
─────────────────────────────────────

[✓] Workbook found: ...
[✓] Python script found: ...
[✓] Python found: C:\Program Files\Python312\python.exe

[*] Generating seed from workbook...
    Wrote C:\xampp\htdocs\UMKM\ui\ui_seed_data.json

[✓] Seed generation completed successfully!
[✓] Seed Status:
    Generated: 2026-05-11T08:33:28
    Total Rows: 3458
    GoogleMaps: 143
    Tokopedia: 3315
    Kecamatan: 18
    Desa: 287
    Matched: 678
    Unmatched: 2780
```

## Check Seed Status

Lihat kapan seed terakhir di-refresh:

```bash
php refresh-seed.php --check
```

## Automatic Scheduling (Optional)

Untuk menjalankan seed refresh otomatis setiap hari pukul 02:00 AM, gunakan Task Scheduler (Windows) atau cron (Linux/Mac).

### Setup untuk Windows (XAMPP)

1. Buka **Task Scheduler** (Ketik "Task Scheduler" di Windows Search)
2. Klik **Create Basic Task**
3. Nama: `UMKM Seed Refresh`
4. Trigger: **Daily**, Time: **02:00**
5. Action: **Start a program**
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `refresh-seed.php`
   - Start in: `C:\xampp\htdocs\UMKM`
6. Finish dan Enable

Scheduler akan otomatis jalankan `php refresh-seed.php` setiap hari jam 2 pagi.

Untuk Laravel scheduler (jika project Laravel sudah bootstrap penuh), command berikut sudah terdaftar:
- `umkm:generate-seed` (daily 02:00)
- `umkm:sync-verification-sheet --limit=500` (setiap 10 menit)

### Setup untuk Linux/Mac

Edit crontab:
```bash
crontab -e
```

Tambahkan baris berikut:
```
0 2 * * * cd /path/to/UMKM && php refresh-seed.php >> /tmp/umkm-seed.log 2>&1
```

Ini akan jalankan refresh setiap hari jam 2 AM dan log output-nya ke `/tmp/umkm-seed.log`.

## Files Involved

- `scripts/build_ui_seed.py` — Python script yang baca workbook
- `refresh-seed.php` — PHP wrapper untuk easy trigger
- `ui/ui_seed_data.json` — Output seed data (auto-updated)
- `app/Http/Controllers/Umkm/VerificationController.php` — Controller yang baca seed
- `app/Console/Commands/SyncVerificationToWorkbook.php` — Command sinkronisasi verifikasi ke sheet
- `scripts/append_verification_to_sheet.py` — Python append ke `Hasil_Verifikasi`

## Workflow

1. **User mengubah data** di workbook Excel
2. **Jalankan**: `php refresh-seed.php` (atau tunggu scheduler jam 2 AM)
3. **Script baca workbook** dan update `ui_seed_data.json`
4. **Controller membaca** seed yang sudah baru saat request `/umkm/verifikasi`
5. **Browser refresh** menampilkan data terbaru

## Troubleshooting

### "Python executable not found"
Pastikan Python 3.10+ terinstall dan tersedia di PATH.

**Check Python:**
```bash
python --version
python3 --version
```

**Add to PATH** (Windows):
1. System Properties → Environment Variables
2. Edit PATH dan tambahkan: `C:\Program Files\Python312`
3. Restart command prompt

**Install Python:**
- Download dari https://www.python.org/downloads/
- Pastikan check "Add Python to PATH" saat install

### "openpyxl not found"
Install dependency:
```bash
pip install openpyxl
```

Atau dengan Python 3 explicit:
```bash
pip3 install openpyxl
```

### Seed JSON not updating
1. Cek workbook file exists: `Data UMKM Online Shop Kabupaten Mojokerto.xlsx` di folder root UMKM
2. Jalankan manual: `php refresh-seed.php`
3. Lihat error message untuk debugging
4. Cek permissions folder `ui/` bisa ditulis

### Script error message tidak jelas
Enable verbose output:
```bash
php -d display_errors=1 refresh-seed.php
```

## Testing

Test di command line:
```bash
# Refresh seed
php refresh-seed.php

# Check status
php refresh-seed.php --check

# Show help
php refresh-seed.php --help
```

Semua command akan show output yang jelas tentang status dan timestamp seed terbaru.

## Real-time Verification

Check seed status di aplikasi:

1. Buka browser: http://localhost:8000/umkm/verifikasi (atau port Laravel kamu)
2. Inspect element → Network tab → check JSON data loaded
3. Seed timestamp harus match dengan output `php refresh-seed.php --check`

