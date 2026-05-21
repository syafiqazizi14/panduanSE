<?php

use App\Http\Controllers\Umkm\VerificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panduanSE/usaha-umkm');

// Public list-only route under /panduanSE
Route::get('/panduanSE/usaha-umkm', [VerificationController::class, 'index'])
    ->name('panduanSE.usaha_umkm');

Route::get('/panduanSE/suggestions', [VerificationController::class, 'suggestions'])
    ->name('panduanSE.suggestions');

// Redirect base /panduanSE to the Usaha UMKM page
Route::redirect('/panduanSE', '/panduanSE/usaha-umkm');

Route::view('/panduanSE/usaha-besar', 'pages.placeholder', [
    'pageTitle' => 'Usaha Besar - UMKM Mojokerto',
    'title' => 'Usaha Besar',
    'description' => 'Halaman ini disiapkan sebagai placeholder ringan untuk menu Usaha Besar. Bisa diisi kapan saja tanpa mengubah struktur utama aplikasi.',
    'searchAction' => '/panduanSE/usaha-umkm',
])->name('usaha-besar');

Route::view('/panduanSE/kbli', 'pages.placeholder', [
    'pageTitle' => 'KBLI - UMKM Mojokerto',
    'title' => 'KBLI',
    'description' => 'Halaman ini disiapkan sebagai placeholder ringan untuk menu KBLI. Struktur sederhana ini aman untuk hosting dan mudah dikembangkan nanti.',
    'searchAction' => '/panduanSE/usaha-umkm',
])->name('kbli');
