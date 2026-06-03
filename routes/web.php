<?php

use App\Http\Controllers\Umkm\VerificationController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/favicon-bps.png', function () {
    $path = base_path('asset/Logo BPS.png');
    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

Route::get('/favicon.ico', function () {
    return redirect('/favicon-bps.png', 302);
});

Route::get('/asset/{filename}', function (string $filename) {
    if (urldecode($filename) !== 'Logo BPS.png') {
        abort(404);
    }

    $path = base_path('asset/Logo BPS.png');
    abort_unless(is_file($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

Route::redirect('/', '/panduanSE/usaha-umkm');

// Public list-only route under /panduanSE
Route::get('/panduanSE/usaha-umkm', [VerificationController::class, 'index'])
    ->name('panduanSE.usaha_umkm');

Route::get('/panduanSE/usaha-besar', [VerificationController::class, 'usahaBesar'])
    ->name('panduanSE.usaha_besar');

Route::get('/panduanSE/kbli', [VerificationController::class, 'kbli'])
    ->name('panduanSE.kbli');

Route::get('/panduanSE/usaha-besar/rekap-pencacah', [VerificationController::class, 'usahaBesarRekapPencacah'])
    ->name('panduanSE.usaha_besar.rekap_pencacah');

Route::get('/panduanSE/usaha-besar/rekap-pencacah/export', [VerificationController::class, 'exportUsahaBesarRekapPencacah'])
    ->name('panduanSE.usaha_besar.rekap_pencacah.export');

Route::get('/panduanSE/suggestions', [VerificationController::class, 'suggestions'])
    ->name('panduanSE.suggestions');

Route::post('/panduanSE/refresh-data', [VerificationController::class, 'refreshSeed'])
    ->name('panduanSE.refresh');

// Redirect base /panduanSE to the Usaha UMKM page
Route::redirect('/panduanSE', '/panduanSE/usaha-umkm');

// Public endpoint to update usaha besar status (no auth)
Route::post('/panduanSE/usaha-besar/{id}/status', [VerificationController::class, 'updateUsahaBesarStatus'])
    ->name('panduanSE.usaha_besar.update_status');

// Webhook receiver for Google Sheets edits (used by Apps Script)
Route::post('/panduanSE/webhook/sheet-sync', [VerificationController::class, 'webhookSheetSync'])
    ->name('panduanSE.webhook.sheet_sync');
