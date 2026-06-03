@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? 'KBLI - UMKM Mojokerto';
  $ui = $umkmUi ?? [];
  $kbli = is_array($ui['kbli'] ?? null) ? $ui['kbli'] : [];
@endphp

@section('content')
  <main id="main-content" class="mx-auto min-h-screen max-w-7xl px-3 py-6 sm:px-6 lg:px-8" tabindex="-1">
    <section class="rounded-[2rem] border border-black/5 bg-white/75 p-4 shadow-soft backdrop-blur-xl sm:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Menu data</p>
          <h1 class="mt-1 text-2xl font-black tracking-tight text-ink sm:text-3xl">Daftar KBLI</h1>
        </div>
        <div class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-soft sm:w-auto">
          <div class="text-xs uppercase tracking-[0.2em] text-slate-300">Total data</div>
          <div class="mt-1 text-2xl font-black">{{ count($kbli) }}</div>
        </div>
      </div>

      <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
          <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cari data</label>
          <input id="kbliSearch" type="search" placeholder="Cari kode atau deskripsi..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20" />
        </div>

        <div class="grid gap-4 p-4 md:hidden">
          @forelse($kbli as $row)
            <article data-row data-card data-search="{{ strtolower(($row['kode'] ?? '') . ' ' . ($row['deskripsi'] ?? '')) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
              <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Kode</p>
              <p class="mt-1 text-sm font-semibold text-slate-800">{{ $row['kode'] ?? '-' }}</p>
              <p class="mt-4 text-[11px] uppercase tracking-[0.18em] text-slate-500">Deskripsi</p>
              <p class="mt-1 text-sm leading-6 text-slate-700">{{ $row['deskripsi'] ?? '-' }}</p>
            </article>
          @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-slate-500">
              Belum ada data KBLI.
            </div>
          @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500">
              <tr>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Deskripsi</th>
              </tr>
            </thead>
            <tbody id="kbliTableBody" class="divide-y divide-slate-100 bg-white">
              @forelse($kbli as $row)
                <tr data-row data-search="{{ strtolower(($row['kode'] ?? '') . ' ' . ($row['deskripsi'] ?? '')) }}">
                  <td class="px-4 py-3 font-semibold text-slate-700">{{ $row['kode'] ?? '-' }}</td>
                  <td class="px-4 py-3 text-slate-700">{{ $row['deskripsi'] ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" class="px-4 py-10 text-center text-slate-500">Belum ada data KBLI.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  @push('scripts')
    <script>
      (function () {
        const input = document.getElementById('kbliSearch');
        const rows = Array.from(document.querySelectorAll('[data-row]'));
        if (!input || !rows.length) return;

        input.addEventListener('input', () => {
          const q = input.value.trim().toLowerCase();
          rows.forEach((row) => {
            const search = row.getAttribute('data-search') || '';
            row.style.display = !q || search.includes(q) ? '' : 'none';
          });
        });
      })();
    </script>
  @endpush
@endsection
