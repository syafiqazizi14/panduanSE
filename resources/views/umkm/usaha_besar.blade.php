@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? 'Usaha Besar - UMKM Mojokerto';
  $ui = $umkmUi ?? [];
  $usahaBesar = is_array($ui['usaha_besar'] ?? null) ? $ui['usaha_besar'] : [];
  $totalUsahaBesar = count($usahaBesar);
  $statusOptions = ['Open', 'Process', 'Success'];
  $pencacahOptions = collect($usahaBesar)
      ->map(static fn ($row) => trim((string) ($row['nama_pencacah'] ?? '')))
      ->filter()
      ->unique()
      ->sort()
      ->values();
@endphp

@section('content')
  <main id="main-content" class="mx-auto min-h-screen max-w-7xl px-3 py-6 sm:px-6 lg:px-8" tabindex="-1">
    <section class="rounded-[2rem] border border-black/5 bg-white/75 p-4 shadow-soft backdrop-blur-xl sm:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Menu data</p>
          <h1 class="mt-1 text-2xl font-black tracking-tight text-ink sm:text-3xl">Data Usaha Besar</h1>
        </div>
        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-stretch">
          <button id="usahaBesarSyncSpreadsheet" type="button" class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:border-emerald-300 hover:bg-emerald-700 sm:w-auto">
            <svg id="usahaBesarSyncSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 100 20 10 10 0 000-20z"></path>
            </svg>
            <span id="usahaBesarSyncLabel">Sync Spreadsheet</span>
          </button>
          <button id="usahaBesarSaveChanges" type="button" class="inline-flex w-full items-center justify-center rounded-2xl border border-sky-200 bg-sky-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:border-sky-300 hover:bg-sky-700 sm:w-auto">
            <svg id="usahaBesarSaveSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 100 20 10 10 0 000-20z"></path>
            </svg>
            <span id="usahaBesarSaveLabel">Simpan Perubahan</span>
          </button>
          <a href="{{ route('panduanSE.usaha_besar.rekap_pencacah') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
            Rekap Pencacah
          </a>
          <div class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-soft sm:w-auto">
            <div class="text-xs uppercase tracking-[0.2em] text-slate-300">Total data</div>
            <div class="mt-1 text-2xl font-black">{{ $totalUsahaBesar }}</div>
          </div>
        </div>
      </div>

      <div id="usahaBesarDirtyNotice" class="mt-4 hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Belum ada perubahan.
      </div>

      <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
          <div class="grid gap-3 md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
              <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cari data</label>
              <input id="usahaBesarSearch" type="search" placeholder="Cari nama usaha, pencacah, atau status..." class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20" />
            </div>

            <div class="md:col-span-3">
              <label for="usahaBesarPencacahFilter" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Filter pencacah</label>
              <select id="usahaBesarPencacahFilter" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20">
                <option value="">Semua pencacah</option>
                @foreach($pencacahOptions as $pencacah)
                  <option value="{{ strtolower($pencacah) }}">{{ $pencacah }}</option>
                @endforeach
              </select>
            </div>

            <div class="md:col-span-2">
              <label for="usahaBesarStatusFilter" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Filter status</label>
              <select id="usahaBesarStatusFilter" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20">
                <option value="">Semua status</option>
                <option value="open">Open</option>
                <option value="process">Process</option>
                <option value="success">Success</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <button id="usahaBesarResetFilter" type="button" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Reset filter</button>
            </div>
          </div>
        </div>

        <div class="grid gap-4 p-4 md:hidden">
          @forelse($usahaBesar as $row)
            @php
              $status = strtolower(trim((string) ($row['status'] ?? '')));
              $pencacah = strtolower(trim((string) ($row['nama_pencacah'] ?? '')));
            @endphp
            <article data-row data-card data-usaha-id="{{ $row['id_usaha_besar'] ?? '' }}" data-original-status="{{ $status }}" data-pencacah="{{ $pencacah }}" data-status="{{ $status }}" data-search="{{ strtolower(($row['id_usaha_besar'] ?? '') . ' ' . ($row['nama_usaha'] ?? '') . ' ' . ($row['nama_pencacah'] ?? '') . ' ' . ($row['status'] ?? '')) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">ID</p>
                  <p class="mt-1 text-sm font-semibold text-slate-800">{{ $row['id_usaha_besar'] ?? '-' }}</p>
                </div>
                <select data-usaha-id="{{ $row['id_usaha_besar'] ?? '' }}" class="usaha-status-select min-w-[120px] rounded-full border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                  @foreach($statusOptions as $opt)
                    <option value="{{ strtolower($opt) }}" {{ strtolower($row['status'] ?? '') === strtolower($opt) ? 'selected' : '' }}>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mt-4 space-y-3">
                <div>
                  <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Nama Usaha</p>
                  <p class="mt-1 text-sm font-medium text-slate-800">{{ $row['nama_usaha'] ?? '-' }}</p>
                </div>
                <div>
                  <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Nama Pencacah</p>
                  <p class="mt-1 text-sm text-slate-700">{{ $row['nama_pencacah'] ?? '-' }}</p>
                </div>
              </div>
            </article>
          @empty
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-slate-500">
              Belum ada data usaha besar.
            </div>
          @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500">
              <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Nama Usaha</th>
                <th class="px-4 py-3">Nama Pencacah</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody id="usahaBesarTableBody" class="divide-y divide-slate-100 bg-white">
              @forelse($usahaBesar as $row)
                @php
                  $status = strtolower(trim((string) ($row['status'] ?? '')));
                  $pencacah = strtolower(trim((string) ($row['nama_pencacah'] ?? '')));
                @endphp
                    <tr data-row data-usaha-id="{{ $row['id_usaha_besar'] ?? '' }}" data-original-status="{{ $status }}" data-pencacah="{{ $pencacah }}" data-status="{{ $status }}" data-search="{{ strtolower(($row['id_usaha_besar'] ?? '') . ' ' . ($row['nama_usaha'] ?? '') . ' ' . ($row['nama_pencacah'] ?? '') . ' ' . ($row['status'] ?? '')) }}">
                  <td class="px-4 py-3 font-semibold text-slate-700">{{ $row['id_usaha_besar'] ?? '-' }}</td>
                  <td class="px-4 py-3 text-slate-700">{{ $row['nama_usaha'] ?? '-' }}</td>
                  <td class="px-4 py-3 text-slate-700">{{ $row['nama_pencacah'] ?? '-' }}</td>
                  <td class="px-4 py-3">
                    <select data-usaha-id="{{ $row['id_usaha_besar'] ?? '' }}" class="usaha-status-select rounded-md border px-3 py-1 text-sm">
                      @foreach($statusOptions as $opt)
                        <option value="{{ strtolower($opt) }}" {{ strtolower($row['status'] ?? '') === strtolower($opt) ? 'selected' : '' }}>{{ $opt }}</option>
                      @endforeach
                    </select>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada data usaha besar.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div id="usahaBesarEmptyState" class="hidden border-t border-slate-200 px-4 py-10 text-center text-slate-500">
          Hasil filter tidak ditemukan.
        </div>
      </div>
    </section>
  </main>

  <div id="usahaBesarToastHost" aria-live="polite" aria-atomic="true" class="pointer-events-none fixed right-4 top-4 z-50 flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:top-6"></div>

  <div id="usahaBesarPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="usahaBesarPasswordTitle">
    <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-5 shadow-2xl sm:p-6">
      <div class="flex items-start gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 shadow-inner">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4m-1 0h10a2 2 0 012 2v5a2 2 0 01-2 2H7a2 2 0 01-2-2v-5a2 2 0 012-2z" />
          </svg>
        </div>
        <div class="min-w-0 flex-1">
          <h2 id="usahaBesarPasswordTitle" class="text-lg font-black tracking-tight text-slate-900">Masukkan password sync</h2>
          <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan password refresh untuk menyinkronkan data spreadsheet dan memperbarui seed.</p>
        </div>
      </div>

      <div class="mt-5 space-y-3">
        <label for="usahaBesarPasswordInput" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Password sync</label>
        <input id="usahaBesarPasswordInput" type="password" autocomplete="current-password" placeholder="Masukkan password refresh" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20" />
        <p id="usahaBesarPasswordError" class="hidden text-sm font-medium text-rose-600"></p>
      </div>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <button id="usahaBesarPasswordCancel" type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">Batal</button>
        <button id="usahaBesarPasswordSubmit" type="button" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-emerald-700">Lanjut Sync</button>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      (function () {
        const input = document.getElementById('usahaBesarSearch');
        const pencacahFilter = document.getElementById('usahaBesarPencacahFilter');
        const statusFilter = document.getElementById('usahaBesarStatusFilter');
        const resetButton = document.getElementById('usahaBesarResetFilter');
        const rows = Array.from(document.querySelectorAll('[data-row]'));
        const emptyState = document.getElementById('usahaBesarEmptyState');

        if (!rows.length) return;

        const applyFilters = () => {
          const q = (input?.value || '').trim().toLowerCase();
          const pencacah = (pencacahFilter?.value || '').trim().toLowerCase();
          const status = (statusFilter?.value || '').trim().toLowerCase();

          rows.forEach((row) => {
            const search = row.getAttribute('data-search') || '';
            const rowPencacah = (row.getAttribute('data-pencacah') || '').trim().toLowerCase();
            const rowStatus = (row.getAttribute('data-status') || '').trim().toLowerCase();
            const matchesSearch = !q || search.includes(q);
            const matchesPencacah = !pencacah || rowPencacah === pencacah;
            const matchesStatus = !status || rowStatus === status;

            row.style.display = matchesSearch && matchesPencacah && matchesStatus ? '' : 'none';
          });

          if (emptyState) {
            const visibleCount = rows.filter((row) => row.style.display !== 'none').length;
            emptyState.classList.toggle('hidden', visibleCount > 0);
          }
        };

        input?.addEventListener('input', applyFilters);
        pencacahFilter?.addEventListener('change', applyFilters);
        statusFilter?.addEventListener('change', applyFilters);
        resetButton?.addEventListener('click', () => {
          if (input) input.value = '';
          if (pencacahFilter) pencacahFilter.value = '';
          if (statusFilter) statusFilter.value = '';
          applyFilters();
          input?.focus();
        });

        applyFilters();
      })();
    </script>
    <script>
      (function () {
        const selects = Array.from(document.querySelectorAll('.usaha-status-select'));
        const rows = Array.from(document.querySelectorAll('[data-row]'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const saveButton = document.getElementById('usahaBesarSaveChanges');
        const syncButton = document.getElementById('usahaBesarSyncSpreadsheet');
        const saveSpinner = document.getElementById('usahaBesarSaveSpinner');
        const syncSpinner = document.getElementById('usahaBesarSyncSpinner');
        const saveLabel = document.getElementById('usahaBesarSaveLabel');
        const syncLabel = document.getElementById('usahaBesarSyncLabel');
        const dirtyNotice = document.getElementById('usahaBesarDirtyNotice');
        const toastHost = document.getElementById('usahaBesarToastHost');
        const passwordModal = document.getElementById('usahaBesarPasswordModal');
        const passwordInput = document.getElementById('usahaBesarPasswordInput');
        const passwordError = document.getElementById('usahaBesarPasswordError');
        const passwordCancel = document.getElementById('usahaBesarPasswordCancel');
        const passwordSubmit = document.getElementById('usahaBesarPasswordSubmit');
        const dirtyMap = new Map();
        let passwordResolve = null;
        let passwordReject = null;

        if (!selects.length) return;

        const getRowsById = (id) => rows.filter((row) => row.getAttribute('data-usaha-id') === id);
        const getSelectsById = (id) => selects.filter((select) => select.getAttribute('data-usaha-id') === id);

        const setBusyState = (busy, type) => {
          if (saveButton) saveButton.disabled = busy;
          if (syncButton) syncButton.disabled = busy;
          if (saveSpinner) saveSpinner.classList.toggle('hidden', type !== 'save' || !busy);
          if (syncSpinner) syncSpinner.classList.toggle('hidden', type !== 'sync' || !busy);
        };

        const updateDirtyNotice = () => {
          if (!dirtyNotice) return;
          if (dirtyMap.size === 0) {
            dirtyNotice.textContent = 'Belum ada perubahan.';
            dirtyNotice.classList.add('hidden');
            return;
          }

          dirtyNotice.textContent = dirtyMap.size + ' perubahan belum disimpan. Klik "Simpan Perubahan" untuk mengirim ke spreadsheet.';
          dirtyNotice.classList.remove('hidden');
        };

        const updateSaveLabel = () => {
          if (!saveLabel) return;
          saveLabel.textContent = dirtyMap.size > 0 ? 'Simpan Perubahan (' + dirtyMap.size + ')' : 'Simpan Perubahan';
        };

        const showToast = (message, type = 'info') => {
          if (!toastHost) {
            return;
          }

          const palette = {
            success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
            error: 'border-rose-200 bg-rose-50 text-rose-900',
            info: 'border-slate-200 bg-white text-slate-800',
          };

          const iconMap = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />',
            error: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
            info: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1m-2 0h2" />',
          };

          const toast = document.createElement('div');
          toast.className = 'pointer-events-auto flex items-start gap-3 rounded-2xl border p-4 shadow-xl backdrop-blur ' + (palette[type] || palette.info) + ' animate-[toastIn_.2s_ease-out]';
          toast.innerHTML = `
            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/80 shadow-sm">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">${iconMap[type] || iconMap.info}</svg>
            </div>
            <div class="min-w-0 flex-1 pr-2">
              <div class="text-sm font-semibold">${message}</div>
            </div>
            <button type="button" class="rounded-full p-1 text-current/60 transition hover:bg-black/5 hover:text-current" aria-label="Tutup notifikasi">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
          `;

          const removeToast = () => {
            toast.classList.add('opacity-0', 'translate-y-[-4px]');
            window.setTimeout(() => toast.remove(), 180);
          };

          toast.querySelector('button')?.addEventListener('click', removeToast);
          toastHost.appendChild(toast);
          window.setTimeout(removeToast, 3200);
        };

        const openPasswordModal = () => new Promise((resolve, reject) => {
          passwordResolve = resolve;
          passwordReject = reject;
          if (passwordError) {
            passwordError.classList.add('hidden');
            passwordError.textContent = '';
          }
          if (passwordInput) {
            passwordInput.value = '';
          }
          passwordModal?.classList.remove('hidden');
          passwordModal?.classList.add('flex');
          window.setTimeout(() => passwordInput?.focus(), 30);
        });

        const closePasswordModal = () => {
          passwordModal?.classList.add('hidden');
          passwordModal?.classList.remove('flex');
          if (passwordReject) {
            passwordReject(new Error('Dibatalkan'));
          }
          passwordResolve = null;
          passwordReject = null;
        };

        const submitPasswordModal = () => {
          const value = (passwordInput?.value || '').trim();
          if (!value) {
            if (passwordError) {
              passwordError.textContent = 'Password belum diisi.';
              passwordError.classList.remove('hidden');
            }
            passwordInput?.focus();
            return;
          }

          if (passwordError) {
            passwordError.classList.add('hidden');
            passwordError.textContent = '';
          }

          passwordModal?.classList.add('hidden');
          passwordModal?.classList.remove('flex');
          if (passwordResolve) {
            passwordResolve(value);
          }
          passwordResolve = null;
          passwordReject = null;
        };

        passwordCancel?.addEventListener('click', closePasswordModal);
        passwordSubmit?.addEventListener('click', submitPasswordModal);
        passwordInput?.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
            submitPasswordModal();
          }
          if (event.key === 'Escape') {
            event.preventDefault();
            closePasswordModal();
          }
        });
        passwordModal?.addEventListener('click', (event) => {
          if (event.target === passwordModal) {
            closePasswordModal();
          }
        });

        const markDirty = (id, isDirty) => {
          getRowsById(id).forEach((row) => {
            row.classList.toggle('ring-2', isDirty);
            row.classList.toggle('ring-amber-300', isDirty);
            row.classList.toggle('bg-amber-50/60', isDirty);
          });

          getSelectsById(id).forEach((select) => {
            select.classList.toggle('border-amber-400', isDirty);
            select.classList.toggle('bg-amber-50', isDirty);
          });
        };

        const syncSelectValues = (id, value) => {
          getSelectsById(id).forEach((select) => {
            select.value = value;
          });
        };

        selects.forEach((sel) => {
          sel.addEventListener('change', (ev) => {
            const select = ev.target;
            const id = select.getAttribute('data-usaha-id');
            if (!id) return;

            const currentValue = (select.value || '').toLowerCase();
            const originalRow = getRowsById(id)[0];
            const originalValue = (originalRow?.getAttribute('data-original-status') || '').toLowerCase();

            syncSelectValues(id, currentValue);

            if (currentValue === originalValue) {
              dirtyMap.delete(id);
              markDirty(id, false);
            } else {
              dirtyMap.set(id, currentValue);
              markDirty(id, true);
            }

            updateDirtyNotice();
            updateSaveLabel();
          });
        });

        saveButton?.addEventListener('click', async () => {
          if (!dirtyMap.size) {
            updateDirtyNotice();
            return;
          }

          setBusyState(true, 'save');
          try {
            for (const [id, status] of dirtyMap.entries()) {
              const response = await fetch('/panduanSE/usaha-besar/' + encodeURIComponent(id) + '/status', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf,
                  'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ status }),
              });

              const data = await response.json().catch(() => ({}));
              if (!response.ok || !data.ok) {
                throw new Error(data.output || data.message || 'Gagal menyimpan status');
              }

              getRowsById(id).forEach((row) => {
                row.setAttribute('data-original-status', status);
                row.setAttribute('data-status', status);
              });
              markDirty(id, false);
              dirtyMap.delete(id);
            }

            updateDirtyNotice();
            updateSaveLabel();
            showToast('Perubahan berhasil disimpan ke spreadsheet.', 'success');
          } catch (err) {
            showToast(err?.message || 'Gagal menyimpan perubahan', 'error');
          } finally {
            setBusyState(false, 'save');
          }
        });

        syncButton?.addEventListener('click', async () => {
          if (dirtyMap.size > 0 && !confirm('Masih ada perubahan yang belum disimpan. Lanjut sync spreadsheet dan muat ulang data?')) {
            return;
          }

          let password = '';
          try {
            password = await openPasswordModal();
          } catch (err) {
            return;
          }

          setBusyState(true, 'sync');
          try {
            const response = await fetch('/panduanSE/refresh-data', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: JSON.stringify({ password, sync_google: true }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.ok) {
              throw new Error(data.output || data.message || 'Gagal sinkron spreadsheet');
            }

            showToast('Spreadsheet berhasil disinkronkan dan data dimuat ulang.', 'success');
            window.location.reload();
          } catch (err) {
            showToast(err?.message || 'Gagal sinkron spreadsheet', 'error');
          } finally {
            setBusyState(false, 'sync');
          }
        });

        updateDirtyNotice();
        updateSaveLabel();
      })();
    </script>
  @endpush
@endsection
