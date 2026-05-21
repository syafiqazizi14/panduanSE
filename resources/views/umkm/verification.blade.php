@extends('layouts.app')

@php
  $pageTitle = 'UMKM Mojokerto - Verifikasi Lapangan';
  $ui = $umkmUi ?? [];
  $authUser = $authUser ?? [];
  $meta = $ui['meta'] ?? [];
  $filters = $ui['filters'] ?? [];
@endphp

@section('content')
  <div class="min-h-screen pb-24">
    <header class="relative overflow-hidden border-b border-black/5 bg-white/60 backdrop-blur-xl">
      <div class="absolute inset-0 grain opacity-30"></div>
      <div class="relative mx-auto max-w-7xl px-3 py-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0 flex-1">
            <h1 class="mt-2 text-xl font-black tracking-tight text-ink sm:mt-3 sm:text-4xl">Verifikasi UMKM Lapangan</h1>
            <p class="mt-1 max-w-2xl text-xs leading-6 text-slate-600 sm:mt-2 sm:text-base">Filter data master dari Google Maps dan Tokopedia, lalu pilih usaha yang akan diverifikasi petugas di lapangan.</p>
          </div>
          <div class="hidden rounded-2xl bg-slate-100 px-3 py-2 text-slate-700 shadow-soft sm:block sm:flex-shrink-0">
            <form method="POST" action="{{ route('auth.logout') }}" class="flex items-center gap-3">
              @csrf
              <div class="min-w-0">
                <div class="text-xs font-semibold leading-tight">{{ $authUser['name'] ?? '-' }}</div>
                <div class="text-[11px] text-slate-600">{{ strtoupper($authUser['role'] ?? '-') }}</div>
              </div>
              <button type="submit" class="rounded-full bg-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-300">Logout</button>
            </form>
          </div>
        </div>

      </div>
    </header>

    <main id="main-content" class="mx-auto mt-4 max-w-7xl px-3 sm:mt-6 sm:px-6 lg:px-8" tabindex="-1">
      <section class="grid gap-4 rounded-2xl border border-black/5 bg-white/70 p-3 shadow-soft backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:grid-cols-[minmax(0,1fr)]">
        <section class="min-w-0">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="text-lg font-black text-ink sm:text-xl">Daftar verifikasi</h2>
              <p id="resultMeta" class="mt-1 text-xs text-slate-600 sm:text-sm" aria-live="polite">Menunggu data seed.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
              <button id="openFilterDrawer" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-black/10 shadow-sm sm:px-3 sm:py-2 sm:text-sm">
                <span aria-hidden="true">☰</span>
                <span class="hidden sm:inline">Filter</span>
              </button>
              <div class="inline-flex rounded-lg bg-white p-1 ring-1 ring-black/10 sm:rounded-2xl" role="group" aria-label="Pilih mode tampilan hasil">
                <button id="viewModeCard" type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-slate-700 sm:rounded-xl sm:px-3 sm:py-2" aria-pressed="true">Ringkas</button>
                <button id="viewModeList" type="button" class="rounded-lg px-2 py-1 text-xs font-semibold text-slate-700 sm:rounded-xl sm:px-3 sm:py-2" aria-pressed="false">Detail</button>
              </div>
            </div>
          </div>

          <div id="cardList" class="space-y-2" aria-live="polite"></div>
          <div id="paginationContainer" class="mt-4 flex flex-wrap items-center justify-center gap-1 sm:mt-6 sm:gap-2"></div>
        </section>
      </section>
    </main>
  </div>

  <div id="filterDrawerBackdrop" class="hidden fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm"></div>
  <aside id="filterDrawer" class="hidden fixed left-0 top-0 z-50 h-full w-full max-w-sm overflow-y-auto bg-white shadow-[0_0_80px_rgba(15,23,42,0.2)] ring-1 ring-black/5" aria-hidden="true" aria-labelledby="filterDrawerTitle" role="dialog">
    <div class="sticky top-0 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Filter</p>
          <h3 id="filterDrawerTitle" class="mt-1 text-xl font-black text-ink">Saring data</h3>
        </div>
        <button id="closeFilterDrawer" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Tutup</button>
      </div>
    </div>
    <div class="space-y-3 px-5 py-5">
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Source tab</label>
        <select id="sourceTab" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="ALL">Semua source</option>
          <option value="Master_GoogleMaps">Master_GoogleMaps</option>
          <option value="Master_Tokopedia">Master_Tokopedia</option>
        </select>
      </div>
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Kecamatan</label>
        <select id="kecamatan" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="">Semua kecamatan</option>
          @foreach(($filters['kecamatan'] ?? []) as $kecamatan)
            <option value="{{ $kecamatan }}">{{ $kecamatan }}</option>
          @endforeach
        </select>
      </div>
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Desa</label>
        <select id="desa" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="">Semua desa</option>
        </select>
      </div>
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</label>
        <select id="matchStatus" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="ALL">Semua status</option>
          <option value="MATCH">MATCH</option>
          <option value="NOT_MATCH">NOT_MATCH</option>
        </select>
      </div>
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Pencarian</label>
        <input id="searchQuery" type="search" placeholder="Cari nama usaha, desa, kecamatan..." class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-amber-500 focus:bg-white" />
      </div>
    </div>
  </aside>

  <div id="drawerBackdrop" class="hidden fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm"></div>
  <aside id="drawer" class="hidden fixed right-0 top-0 z-50 h-full w-full max-w-xl overflow-y-auto bg-white shadow-[0_0_80px_rgba(15,23,42,0.2)]" role="dialog" aria-modal="true" aria-labelledby="drawerTitle" aria-hidden="true">
    <div class="sticky top-0 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Detail kartu</p>
          <h3 id="drawerTitle" class="mt-1 text-2xl font-black text-ink">Rincian data</h3>
          <p id="drawerSubtitle" class="mt-1 text-sm text-slate-600"></p>
        </div>
        <button id="closeDrawer" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Tutup</button>
      </div>
    </div>
    <div class="space-y-4 px-5 py-5">
      <div class="rounded-3xl bg-slate-50 p-4 ring-1 ring-slate-200">
        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Informasi</div>
        <div id="drawerDetails" class="mt-3 space-y-2 text-sm leading-6 text-slate-700"></div>
      </div>
      <div class="rounded-3xl bg-amber-50 p-4 ring-1 ring-amber-200">
        <div class="text-xs uppercase tracking-[0.2em] text-amber-900">Form verifikasi</div>
        <div class="mt-3 space-y-3">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-amber-900">Status verifikasi</label>
            <select id="verificationStatus" class="mt-2 w-full rounded-xl border border-amber-200 bg-white px-3 py-3 text-sm outline-none focus:border-amber-500">
              <option value="MATCH">MATCH</option>
              <option value="NOT_MATCH">NOT_MATCH</option>
              <option value="NEED_REVIEW">NEED_REVIEW</option>
              <option value="DUPLICATE">DUPLICATE</option>
              <option value="OUTSIDE_AREA">OUTSIDE_AREA</option>
              <option value="NO_FINDING">NO_FINDING</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-amber-900">Catatan petugas</label>
            <textarea id="verificationNotes" rows="4" class="mt-2 w-full rounded-2xl border border-amber-200 bg-white px-3 py-3 text-sm outline-none focus:border-amber-500" placeholder="Tulis catatan lapangan..."></textarea>
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <input id="officerName" aria-label="Nama petugas" class="rounded-xl border border-amber-200 bg-slate-100 px-3 py-3 text-sm text-slate-700 outline-none" placeholder="Nama petugas" readonly />
            <input id="officerId" aria-label="ID petugas" class="rounded-xl border border-amber-200 bg-slate-100 px-3 py-3 text-sm text-slate-700 outline-none" placeholder="ID petugas" readonly />
          </div>
          <button id="saveVerificationBtn" type="button" class="w-full rounded-2xl bg-forest px-4 py-3 text-sm font-bold text-white shadow-soft">Simpan Verifikasi</button>
          <p id="saveMessage" class="hidden rounded-xl px-3 py-2 text-sm" aria-live="polite"></p>
        </div>
      </div>
    </div>
  </aside>

@endsection

@push('scripts')
  <script>
    window.__UMKM_UI__ = @json($ui);
    window.__UMKM_VERIFICATION__ = {
      saveUrl: @json(route('umkm.verification.store')),
      csrfToken: @json(csrf_token()),
      authUser: @json($authUser),
    };
  </script>
  <script>
    const ui = window.__UMKM_UI__ || {};
    const allCards = ui.cards || ui.preview_cards || [];
    const desaByKecamatan = (ui.filters && ui.filters.desa_by_kecamatan) || {};
    const kecamatanOptions = (ui.filters && ui.filters.kecamatan) || [];
    const verificationConfig = window.__UMKM_VERIFICATION__ || {};
    const authUser = verificationConfig.authUser || {};

    const els = {
      sourceTab: document.getElementById('sourceTab'),
      kecamatan: document.getElementById('kecamatan'),
      desa: document.getElementById('desa'),
      matchStatus: document.getElementById('matchStatus'),
      searchQuery: document.getElementById('searchQuery'),
      openFilterDrawer: document.getElementById('openFilterDrawer'),
      cardList: document.getElementById('cardList'),
      paginationContainer: document.getElementById('paginationContainer'),
      resultMeta: document.getElementById('resultMeta'),
      viewModeCard: document.getElementById('viewModeCard'),
      viewModeList: document.getElementById('viewModeList'),
      filterDrawer: document.getElementById('filterDrawer'),
      filterDrawerBackdrop: document.getElementById('filterDrawerBackdrop'),
      closeFilterDrawer: document.getElementById('closeFilterDrawer'),
      drawer: document.getElementById('drawer'),
      drawerBackdrop: document.getElementById('drawerBackdrop'),
      drawerTitle: document.getElementById('drawerTitle'),
      drawerSubtitle: document.getElementById('drawerSubtitle'),
      drawerDetails: document.getElementById('drawerDetails'),
      closeDrawer: document.getElementById('closeDrawer'),
      verificationStatus: document.getElementById('verificationStatus'),
      verificationNotes: document.getElementById('verificationNotes'),
      officerName: document.getElementById('officerName'),
      officerId: document.getElementById('officerId'),
      saveVerificationBtn: document.getElementById('saveVerificationBtn'),
      saveMessage: document.getElementById('saveMessage'),
    };

    const state = {
      selectedSourceTab: 'ALL',
      selectedKecamatan: '',
      selectedDesa: '',
      matchStatus: 'ALL',
      searchQuery: '',
      viewMode: 'card',
      selectedCard: null,
      isSaving: false,
      currentPage: 1,
      itemsPerPage: 10,
    };

    function escapeHtml(value) {
      return String(value ?? '').replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]));
    }

    function getFilteredCards() {
      return allCards.filter((card) => {
        if (state.selectedSourceTab !== 'ALL' && card.source_tab !== state.selectedSourceTab) return false;
        if (state.selectedKecamatan && card.nmkec !== state.selectedKecamatan) return false;
        if (state.selectedDesa && card.nmdesa !== state.selectedDesa) return false;
        if (state.matchStatus !== 'ALL' && card.match_status !== state.matchStatus) return false;
        const q = state.searchQuery.trim().toLowerCase();
        if (q) {
          const haystack = [card.id_scraping, card.nama_usaha_sumber, card.match_nama_usaha, card.nmkec, card.nmdesa, card.nmsls, card.keterangan].join(' ').toLowerCase();
          if (!haystack.includes(q)) return false;
        }
        return true;
      });
    }

    function renderDesaOptions() {
      const kec = state.selectedKecamatan;
      const desa = kec ? (desaByKecamatan[kec] || []) : [];
      els.desa.innerHTML = '<option value="">Semua desa</option>' + desa.map((item) => `<option value="${escapeHtml(item)}">${escapeHtml(item)}</option>`).join('');
      if (state.selectedDesa && !desa.includes(state.selectedDesa)) {
        state.selectedDesa = '';
        els.desa.value = '';
      }
    }

    function renderKecamatanOptions() {
      els.kecamatan.innerHTML = '<option value="">Semua kecamatan</option>' + kecamatanOptions.map((item) => `<option value="${escapeHtml(item)}">${escapeHtml(item)}</option>`).join('');
      els.kecamatan.value = state.selectedKecamatan;
    }

    function applyAuthenticatedOfficer() {
      const officerName = (authUser.name || '').trim();
      const officerId = (authUser.employee_id || '').trim();
      if (els.officerName) {
        els.officerName.value = officerName;
      }
      if (els.officerId) {
        els.officerId.value = officerId;
      }
    }

    function openDrawer(card) {
      state.selectedCard = card;
      els.drawerTitle.textContent = card.nama_usaha_sumber || card.match_nama_usaha || 'Rincian data';
      els.drawerSubtitle.textContent = `${card.source_tab} • ${card.nmkec || '-'} / ${card.nmdesa || '-'}`;
      els.verificationStatus.value = card.match_status || 'MATCH';
      els.verificationNotes.value = '';
      setSaveMessage('', 'info');
      els.drawerDetails.innerHTML = `
        <div><span class="font-semibold">ID:</span> ${escapeHtml(card.id_scraping)}</div>
        <div><span class="font-semibold">Kategori:</span> ${escapeHtml(card.kategori_sumber || card.kategori_jual || '-')}</div>
        <div><span class="font-semibold">Status:</span> ${escapeHtml(card.match_status)}</div>
        <div><span class="font-semibold">Koordinat:</span> ${card.source_latitude_normalized ?? '-'}, ${card.source_longitude_normalized ?? '-'}</div>
      `;
      els.drawer.classList.remove('hidden');
      els.drawerBackdrop.classList.remove('hidden');
      // hide main content from assistive tech
      const main = document.getElementById('main-content');
      if (main) main.setAttribute('aria-hidden', 'true');
      els.drawer.setAttribute('aria-hidden', 'false');
      // Move focus to close button
      manageFocusOnDrawerOpen();
    }

    function closeDrawer() {
      els.drawer.classList.add('hidden');
      els.drawerBackdrop.classList.add('hidden');
      setSaveMessage('', 'info');
      const main = document.getElementById('main-content');
      if (main) main.removeAttribute('aria-hidden');
      els.drawer.setAttribute('aria-hidden', 'true');
      releaseFocusFromDrawer();
    }

    function setSaveMessage(message, type) {
      if (!els.saveMessage) return;
      if (!message) {
        els.saveMessage.className = 'hidden rounded-xl px-3 py-2 text-sm';
        els.saveMessage.textContent = '';
        return;
      }

      let className = 'rounded-xl px-3 py-2 text-sm';
      if (type === 'success') className += ' bg-emerald-100 text-emerald-800';
      else if (type === 'error') className += ' bg-rose-100 text-rose-800';
      else className += ' bg-slate-100 text-slate-700';

      els.saveMessage.className = className;
      els.saveMessage.textContent = message;
    }

    function toNullableNumber(value) {
      if (value === null || value === undefined || value === '') return null;
      const n = Number(value);
      return Number.isFinite(n) ? n : null;
    }

    // Focus management for drawer accessibility
    function manageFocusOnDrawerOpen() {
      const closeBtn = els.closeDrawer;
      if (closeBtn && typeof closeBtn.focus === 'function') {
        setTimeout(() => closeBtn.focus(), 100);
      }
    }

    function releaseFocusFromDrawer() {
      // No specific focus management needed on close since we use aria-hidden
    }

    function openFilterDrawer() {
      els.filterDrawer.classList.remove('hidden');
      els.filterDrawerBackdrop.classList.remove('hidden');
    }

    function closeFilterDrawer() {
      els.filterDrawer.classList.add('hidden');
      els.filterDrawerBackdrop.classList.add('hidden');
    }


    function applyViewModeClasses() {
      els.cardList.className = 'space-y-2';
      if (state.viewMode === 'list') {
        els.viewModeCard.className = 'rounded-xl px-3 py-2 text-xs font-semibold text-slate-700';
        els.viewModeList.className = 'rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-sm';
        els.viewModeCard.setAttribute('aria-pressed', 'false');
        els.viewModeList.setAttribute('aria-pressed', 'true');
      } else {
        els.viewModeCard.className = 'rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-sm';
        els.viewModeList.className = 'rounded-xl px-3 py-2 text-xs font-semibold text-slate-700';
        els.viewModeCard.setAttribute('aria-pressed', 'true');
        els.viewModeList.setAttribute('aria-pressed', 'false');
      }
    }

    async function saveVerification() {
      if (!state.selectedCard || state.isSaving) return;
      const officerName = (authUser.name || '').trim();
      if (!officerName) {
        setSaveMessage('Sesi login petugas tidak valid. Silakan login ulang.', 'error');
        return;
      }

      const payload = {
        id_scraping: state.selectedCard.id_scraping || '',
        source_tab: state.selectedCard.source_tab || '',
        match_idsbr: state.selectedCard.match_idsbr || null,
        match_nama_usaha: state.selectedCard.match_nama_usaha || null,
        match_alamat: state.selectedCard.match_alamat || null,
        verification_status: els.verificationStatus.value,
        officer_name: officerName,
        officer_id: (authUser.employee_id || '').trim() || null,
        officer_latitude: null,
        officer_longitude: null,
        verified_latitude: toNullableNumber(state.selectedCard.source_latitude_normalized),
        verified_longitude: toNullableNumber(state.selectedCard.source_longitude_normalized),
        distance_km: toNullableNumber(state.selectedCard.jarak_km),
        notes: (els.verificationNotes.value || '').trim() || null,
        photo_url: null,
        device_id: null,
      };

      try {
        state.isSaving = true;
        els.saveVerificationBtn.disabled = true;
        els.saveVerificationBtn.textContent = 'Menyimpan...';
        setSaveMessage('Menyimpan verifikasi...', 'info');

        const response = await fetch(verificationConfig.saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': verificationConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const firstError = data && data.errors ? Object.values(data.errors)[0]?.[0] : null;
          throw new Error(firstError || data.message || 'Gagal menyimpan verifikasi.');
        }

        setSaveMessage(data.message || 'Verifikasi berhasil disimpan.', 'success');
        // Mark card as saved locally and update UI
        if (state.selectedCard) {
          state.selectedCard._saved = true;
        }
        renderCards();
        // Close drawer after short delay to give feedback
        setTimeout(() => {
          closeDrawer();
        }, 900);
      } catch (error) {
        setSaveMessage(error.message || 'Terjadi kesalahan saat menyimpan.', 'error');
      } finally {
        state.isSaving = false;
        els.saveVerificationBtn.disabled = false;
        els.saveVerificationBtn.textContent = 'Simpan Verifikasi';
      }
    }

    function renderCards() {
      const allFilteredCards = getFilteredCards();
      applyViewModeClasses();
      
      // Reset to page 1 if filter changes
      state.currentPage = 1;
      
      // Calculate pagination
      const totalItems = allFilteredCards.length;
      const totalPages = Math.ceil(totalItems / state.itemsPerPage);
      const startIdx = (state.currentPage - 1) * state.itemsPerPage;
      const endIdx = startIdx + state.itemsPerPage;
      const paginatedCards = allFilteredCards.slice(startIdx, endIdx);
      
      els.resultMeta.textContent = `Menampilkan ${startIdx + 1}-${Math.min(endIdx, totalItems)} dari ${totalItems} usaha`;
    }

    function formatCoordinates(lat, lon) {
      const n = normalizeCoords(lat, lon);
      if (!n) return '';
      return `${n.lat.toFixed(6)}, ${n.lon.toFixed(6)}`;
    }

    function getGoogleMapsUrl(lat, lon) {
      const n = normalizeCoords(lat, lon);
      if (!n) return null;
      return `https://www.google.com/maps/search/${n.lat.toFixed(6)},${n.lon.toFixed(6)}`;
    }

    els.cardList.innerHTML = paginatedCards.map((card) => {
      const coordStr = formatCoordinates(card.source_latitude, card.source_longitude);
      const mapsUrl = getGoogleMapsUrl(card.source_latitude, card.source_longitude);
      const nameHtml = mapsUrl ?
        `<a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-600 hover:text-blue-800 underline">${escapeHtml(card.nama_usaha_sumber || card.match_nama_usaha || '-')}</a>` :
        `<span class="font-semibold">${escapeHtml(card.nama_usaha_sumber || card.match_nama_usaha || '-')}</span>`;

      return `
        <article class="w-full rounded-2xl bg-white/85 px-3 py-3 shadow-sm ring-1 ring-black/5 transition hover:bg-white sm:px-4 sm:py-3" aria-label="Detail ${escapeHtml(card.nama_usaha_sumber || card.match_nama_usaha || 'usaha')}">
          <div class="space-y-1.5">
            <h3 class="truncate text-sm font-semibold text-ink sm:text-base">${nameHtml}</h3>
            <p class="text-xs text-slate-600 sm:text-sm">${escapeHtml(card.kategori_sumber || card.kategori_jual || card.keterangan || '-')}</p>
            <p class="text-xs text-slate-600 sm:text-sm">${escapeHtml(card.nmkec || '-')} / ${escapeHtml(card.nmdesa || '-')} ${card.rw ? `/ RW ${escapeHtml(card.rw)}` : ''} ${card.rt ? `/ RT ${escapeHtml(card.rt)}` : ''}</p>
            ${coordStr ? `<p class="text-xs text-slate-500 sm:text-sm">${coordStr}</p>` : ''}
          </div>
        </article>
      `}).join('');

      els.cardList.querySelectorAll('article').forEach((article, index) => {
        article.addEventListener('click', () => openDrawer(paginatedCards[index]));
        article.addEventListener('keydown', (event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openDrawer(paginatedCards[index]);
          }
        });
      });
      
      renderPagination(totalPages, totalItems);
    }

    function renderPagination(totalPages, totalItems) {
      if (totalPages <= 1) {
        els.paginationContainer.innerHTML = '';
        return;
      }
      
      let html = '';
      const isMobile = window.innerWidth < 640;
      
      // Previous button
      if (state.currentPage > 1) {
        html += `<button type="button" class="rounded px-2 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">←</button>`;
      } else {
        html += `<button type="button" disabled class="rounded px-2 py-1 text-xs font-semibold text-slate-400 bg-slate-50 cursor-not-allowed sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">←</button>`;
      }
      
      // Page numbers - simplified for mobile
      if (isMobile) {
        // Show current page and nearby pages only on mobile
        const start = Math.max(1, state.currentPage - 1);
        const end = Math.min(totalPages, state.currentPage + 1);
        for (let i = start; i <= end; i++) {
          if (i === state.currentPage) {
            html += `<button type="button" disabled class="rounded px-2 py-1 text-xs font-semibold text-white bg-slate-900 sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">${i}</button>`;
          } else {
            html += `<button type="button" class="rounded px-2 py-1 text-xs font-semibold text-slate-700 bg-white ring-1 ring-slate-200 hover:bg-slate-50 sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">${i}</button>`;
          }
        }
        if (end < totalPages) {
          html += `<span class="text-xs text-slate-400 sm:text-sm">…</span>`;
        }
      } else {
        // Show full pagination on desktop
        for (let i = 1; i <= totalPages; i++) {
          if (i === state.currentPage) {
            html += `<button type="button" disabled class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">${i}</button>`;
          } else if (i <= 3 || i > totalPages - 3 || (i >= state.currentPage - 1 && i <= state.currentPage + 1)) {
            html += `<button type="button" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">${i}</button>`;
          } else if (i === 4 || i === totalPages - 3) {
            html += `<span class="text-slate-400">…</span>`;
          }
        }
      }
      
      // Next button
      if (state.currentPage < totalPages) {
        html += `<button type="button" class="rounded px-2 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">→</button>`;
      } else {
        html += `<button type="button" disabled class="rounded px-2 py-1 text-xs font-semibold text-slate-400 bg-slate-50 cursor-not-allowed sm:rounded-lg sm:px-3 sm:py-2 sm:text-sm">→</button>`;
      }
      
      els.paginationContainer.innerHTML = html;
      
      // Attach event listeners
      els.paginationContainer.querySelectorAll('button:not([disabled])').forEach((btn) => {
        btn.addEventListener('click', () => {
          const text = btn.textContent.trim();
          if (text === '←') {
            state.currentPage = Math.max(1, state.currentPage - 1);
          } else if (text === '→') {
            state.currentPage = Math.min(totalPages, state.currentPage + 1);
          } else {
            const pageNum = parseInt(text, 10);
            if (!isNaN(pageNum)) {
              state.currentPage = pageNum;
            }
          }
          renderCards();
          // Scroll to top of list
          els.cardList.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    }

    els.sourceTab.addEventListener('change', (e) => { state.selectedSourceTab = e.target.value; renderCards(); });
    els.kecamatan.addEventListener('change', (e) => { state.selectedKecamatan = e.target.value; renderDesaOptions(); renderCards(); });
    els.desa.addEventListener('change', (e) => { state.selectedDesa = e.target.value; renderCards(); });
    els.matchStatus.addEventListener('change', (e) => { state.matchStatus = e.target.value; renderCards(); });
    els.searchQuery.addEventListener('input', (e) => { state.searchQuery = e.target.value; renderCards(); });
    els.viewModeCard.addEventListener('click', () => { state.viewMode = 'card'; renderCards(); });
    els.viewModeList.addEventListener('click', () => { state.viewMode = 'list'; renderCards(); });
    els.openFilterDrawer.addEventListener('click', openFilterDrawer);
    els.closeFilterDrawer.addEventListener('click', closeFilterDrawer);
    els.filterDrawerBackdrop.addEventListener('click', closeFilterDrawer);
    els.closeDrawer.addEventListener('click', closeDrawer);
    els.drawerBackdrop.addEventListener('click', closeDrawer);
    els.saveVerificationBtn.addEventListener('click', saveVerification);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !els.filterDrawer.classList.contains('hidden')) {
        closeFilterDrawer();
      }
      if (event.key === 'Escape' && !els.drawer.classList.contains('hidden')) {
        closeDrawer();
      }
    });

    renderKecamatanOptions();
    renderDesaOptions();
    applyAuthenticatedOfficer();
    renderCards();
  </script>
@endpush
