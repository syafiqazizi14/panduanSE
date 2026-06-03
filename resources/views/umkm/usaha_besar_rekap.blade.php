@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? 'Rekap Pencacah Usaha Besar - UMKM Mojokerto';
  $rekapRows = is_array($rekapRows ?? null) ? $rekapRows : [];
  $summary = is_array($summary ?? null) ? $summary : [];
@endphp

@section('content')
  <main id="main-content" class="mx-auto min-h-screen max-w-7xl px-3 py-6 sm:px-6 lg:px-8" tabindex="-1">
    <section class="overflow-hidden rounded-[2rem] border border-black/5 bg-white/75 shadow-soft backdrop-blur-xl">
      <div class="border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-white to-amber-50 px-4 py-5 sm:px-6 sm:py-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div class="min-w-0">
            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Rekap data</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-ink sm:text-3xl">Rekap Pencacah Usaha Besar</h1>
          </div>
          <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('panduanSE.usaha_besar') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
              Kembali ke Usaha Besar
            </a>
            <a href="{{ route('panduanSE.usaha_besar.rekap_pencacah.export') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-forest px-4 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-forest/90">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3v10m0 0 4-4m-4 4-4-4M5 17v2a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Export Excel
            </a>
          </div>
        </div>
      </div>

      <div class="grid gap-4 px-4 py-4 sm:grid-cols-2 xl:grid-cols-4 sm:px-6">
        <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Total pencacah</p>
          <p class="mt-2 text-3xl font-black text-ink">{{ number_format((int) ($summary['pencacah_count'] ?? 0)) }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Total usaha besar</p>
          <p class="mt-2 text-3xl font-black text-ink">{{ number_format((int) ($summary['total'] ?? 0)) }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Status Open</p>
          <p class="mt-2 text-3xl font-black text-amber-600">{{ number_format((int) ($summary['open'] ?? 0)) }}</p>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Status Process / Success</p>
          <div class="mt-2 flex items-baseline gap-4">
            <div>
              <p class="text-xs text-slate-500">Process</p>
              <p class="text-2xl font-black text-sky-600">{{ number_format((int) ($summary['process'] ?? 0)) }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Success</p>
              <p class="text-2xl font-black text-emerald-600">{{ number_format((int) ($summary['success'] ?? 0)) }}</p>
            </div>
          </div>
        </article>
      </div>

      <div class="px-4 pb-4 sm:px-6 sm:pb-6">
        <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-4 py-3 sm:px-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Daftar rekap per pencacah</p>
          </div>

          <div class="grid gap-4 p-4 md:hidden">
            @forelse($rekapRows as $row)
              <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Nama pencacah</p>
                    <p class="mt-1 text-base font-bold text-ink">{{ $row['nama_pencacah'] }}</p>
                  </div>
                  <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                    {{ $row['total'] }} data
                  </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                  <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500">Open</p>
                    <p class="mt-1 text-xl font-black text-amber-600">{{ $row['open'] }}</p>
                  </div>
                  <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500">Process</p>
                    <p class="mt-1 text-xl font-black text-sky-600">{{ $row['process'] }}</p>
                  </div>
                  <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                    <p class="text-[10px] uppercase tracking-[0.18em] text-slate-500">Success</p>
                    <p class="mt-1 text-xl font-black text-emerald-600">{{ $row['success'] }}</p>
                  </div>
                </div>
              </article>
            @empty
              <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-8 text-center text-slate-500">
                Belum ada data untuk direkap.
              </div>
            @endforelse
          </div>

          <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
              <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                <tr>
                  <th class="px-5 py-4">Nama Pencacah</th>
                  <th class="px-5 py-4">Total</th>
                  <th class="px-5 py-4">Open</th>
                  <th class="px-5 py-4">Process</th>
                  <th class="px-5 py-4">Success</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($rekapRows as $row)
                  <tr class="transition hover:bg-slate-50/80">
                    <td class="px-5 py-4 font-semibold text-slate-800">{{ $row['nama_pencacah'] }}</td>
                    <td class="px-5 py-4 text-slate-700">{{ $row['total'] }}</td>
                    <td class="px-5 py-4 text-amber-600">{{ $row['open'] }}</td>
                    <td class="px-5 py-4 text-sky-600">{{ $row['process'] }}</td>
                    <td class="px-5 py-4 text-emerald-600">{{ $row['success'] }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada data untuk direkap.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>
@endsection
