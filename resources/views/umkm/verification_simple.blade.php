@extends('layouts.app')

@php
  $pageTitle = 'UMKM Mojokerto - Daftar Usaha';
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
            <h1 class="text-xl font-black tracking-tight text-ink sm:text-4xl">Daftar Usaha UMKM</h1>
            <p class="mt-1 max-w-2xl text-xs leading-6 text-slate-600 sm:mt-2 sm:text-base">Daftar lengkap usaha mikro, kecil, dan menengah di Kabupaten Mojokerto dari data Google Maps dan Tokopedia.</p>
          </div>
        </div>
      </div>
    </header>

    <main id="main-content" class="mx-auto mt-4 max-w-7xl px-3 sm:mt-6 sm:px-6 lg:px-8" tabindex="-1">
      <section class="grid gap-4 rounded-2xl border border-black/5 bg-white/70 p-3 shadow-soft backdrop-blur-xl sm:rounded-[2rem] sm:p-4 lg:grid-cols-[minmax(0,1fr)]">
        <section class="min-w-0">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="text-lg font-black text-ink sm:text-xl">Daftar Usaha</h2>
              <p id="resultMeta" class="mt-1 text-xs text-slate-600 sm:text-sm" aria-live="polite">Menunggu data seed.</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
              <label for="itemsPerPage" class="text-xs sm:text-sm font-semibold text-slate-700">Tampilkan:</label>
              <select id="itemsPerPage" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs sm:text-sm font-medium text-ink outline-none focus:border-forest focus:ring-2 focus:ring-forest/20 transition-all">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="text-xs sm:text-sm text-slate-600">per halaman</span>
            </div>
            <button id="openFilterDrawer" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-2 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-black/10 shadow-sm sm:px-3 sm:py-2 sm:text-sm">
              <span aria-hidden="true">☰</span>
              <span class="hidden sm:inline">Filter</span>
            </button>
          </div>

          <div id="cardList" class="border border-slate-200 rounded-xl overflow-hidden" aria-live="polite"></div>
          <div id="paginationContainer" class="mt-4 flex flex-wrap items-center justify-center gap-1 sm:mt-6 sm:gap-2"></div>
        </section>
      </section>
    </main>
  </div>

  <!-- Filter Drawer -->
  <div id="filterDrawerBackdrop" class="hidden fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm"></div>
  <aside id="filterDrawer" class="hidden fixed left-0 top-0 z-50 h-full w-full max-w-sm overflow-y-auto bg-white shadow-[0_0_80px_rgba(15,23,42,0.2)] ring-1 ring-black/5" aria-hidden="true" aria-labelledby="filterDrawerTitle" role="dialog">
    <div class="sticky top-0 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Saring</p>
          <h3 id="filterDrawerTitle" class="mt-1 text-xl font-black text-ink">Saring data</h3>
        </div>
        <button id="closeFilterDrawer" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Tutup</button>
      </div>
    </div>
    <div class="space-y-3 px-5 py-5">
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sumber Data</label>
        <select id="sourceTab" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="ALL">Semua sumber</option>
          <option value="Master_GoogleMaps">Google Maps</option>
          <option value="Master_Tokopedia">Tokopedia</option>
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
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">RW (Rukun Warga)</label>
        <select id="rw" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="">Semua RW</option>
        </select>
      </div>
      <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-black/5">
        <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">RT (Rukun Tetangga)</label>
        <select id="rt" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium outline-none transition focus:border-amber-500 focus:bg-white">
          <option value="">Semua RT</option>
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

@endsection

@push('scripts')
  <script>
    window.__UMKM_UI__ = @json($ui);
  </script>
  <script>
    const ui = window.__UMKM_UI__ || {};
    const allCards = ui.cards || ui.preview_cards || [];
    const desaByKecamatan = (ui.filters && ui.filters.desa_by_kecamatan) || {};
    const rwRtByDesa = (ui.filters && ui.filters.rw_rt_by_desa) || {};
    const kecamatanOptions = (ui.filters && ui.filters.kecamatan) || [];

    const els = {
      sourceTab: document.getElementById('sourceTab'),
      kecamatan: document.getElementById('kecamatan'),
      desa: document.getElementById('desa'),
      rw: document.getElementById('rw'),
      rt: document.getElementById('rt'),
      matchStatus: document.getElementById('matchStatus'),
      searchQuery: document.getElementById('searchQuery'),
      openFilterDrawer: document.getElementById('openFilterDrawer'),
      cardList: document.getElementById('cardList'),
      paginationContainer: document.getElementById('paginationContainer'),
      resultMeta: document.getElementById('resultMeta'),
      filterDrawer: document.getElementById('filterDrawer'),
      filterDrawerBackdrop: document.getElementById('filterDrawerBackdrop'),
      closeFilterDrawer: document.getElementById('closeFilterDrawer'),
    };

    const state = {
      selectedSourceTab: 'ALL',
      selectedKecamatan: null,
      selectedDesa: null,
      selectedRw: null,
      selectedRt: null,
      matchStatus: 'ALL',
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
    };

    function escapeHtml(text) {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function openFilterDrawer() {
      els.filterDrawer.classList.remove('hidden');
      els.filterDrawerBackdrop.classList.remove('hidden');
    }

    function closeFilterDrawer() {
      els.filterDrawer.classList.add('hidden');
      els.filterDrawerBackdrop.classList.add('hidden');
    }

    function renderDesaOptions() {
      const selectedKec = state.selectedKecamatan;
      const desaList = selectedKec ? (desaByKecamatan[selectedKec] || []) : [];
      
      els.desa.innerHTML = '<option value="">Semua desa</option>' + 
        desaList.map(d => `<option value="${escapeHtml(d)}">${escapeHtml(d)}</option>`).join('');
      
      // Reset RW/RT when desa changes
      renderRwRtOptions();
    }

    function renderRwRtOptions() {
      const selectedDesa = state.selectedDesa;
      const rwRtData = selectedDesa ? (rwRtByDesa[selectedDesa] || { RW: [], RT: [] }) : { RW: [], RT: [] };
      
      els.rw.innerHTML = '<option value="">Semua RW</option>' + 
        rwRtData.RW.map(r => `<option value="${escapeHtml(r)}">RW ${escapeHtml(r)}</option>`).join('');
      
      els.rt.innerHTML = '<option value="">Semua RT</option>' + 
        rwRtData.RT.map(r => `<option value="${escapeHtml(r)}">RT ${escapeHtml(r)}</option>`).join('');
    }

    function getFilteredCards() {
      return allCards.filter(card => {
        const sourceMatch = state.selectedSourceTab === 'ALL' || card.source_tab === state.selectedSourceTab;
        const kecMatch = !state.selectedKecamatan || card.nmkec === state.selectedKecamatan;
        const desaMatch = !state.selectedDesa || card.nmdesa === state.selectedDesa;
        const rwMatch = !state.selectedRw || card.rw === state.selectedRw;
        const rtMatch = !state.selectedRt || card.rt === state.selectedRt;
        const statusMatch = state.matchStatus === 'ALL' || card.match_status === state.matchStatus;
        
        const searchLower = state.searchQuery.toLowerCase();
        const searchMatch = !searchLower || 
          (card.nama_usaha_sumber && card.nama_usaha_sumber.toLowerCase().includes(searchLower)) ||
          (card.kategori_sumber && card.kategori_sumber.toLowerCase().includes(searchLower)) ||
          (card.nmdesa && card.nmdesa.toLowerCase().includes(searchLower)) ||
          (card.nmkec && card.nmkec.toLowerCase().includes(searchLower));
        
        return sourceMatch && kecMatch && desaMatch && rwMatch && rtMatch && statusMatch && searchMatch;
      });
    }

    function normalizeCoords(lat, lon) {
      if (lat == null || lon == null) return null;
      let la = Number(lat);
      let lo = Number(lon);
      if (!isFinite(la) || !isFinite(lo)) return null;

      function valid(a, b) { return Math.abs(a) <= 90 && Math.abs(b) <= 180; }
      if (valid(la, lo)) return { lat: la, lon: lo };

      const factors = [1e6, 1e7, 1e5, 1e4, 1e3, 100, 10];
      for (const f of factors) {
        const la2 = la / f;
        const lo2 = lo / f;
        if (valid(la2, lo2)) return { lat: la2, lon: lo2 };
      }

      // try swapping
      for (const f of factors) {
        const la2 = lo / f;
        const lo2 = la / f;
        if (valid(la2, lo2)) return { lat: la2, lon: lo2 };
      }

      // try progressively dividing by 10
      let la3 = la, lo3 = lo;
      for (let i = 0; i < 7; i++) {
        la3 /= 10; lo3 /= 10;
        if (valid(la3, lo3)) return { lat: la3, lon: lo3 };
      }

      return null;
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

    function renderCards() {
      const allFilteredCards = getFilteredCards();
      const totalItems = allFilteredCards.length;
      const totalPages = Math.ceil(totalItems / state.itemsPerPage);
      const safeTotalPages = Math.max(1, totalPages);
      if (state.currentPage > safeTotalPages) {
        state.currentPage = safeTotalPages;
      }

      const startIdx = (state.currentPage - 1) * state.itemsPerPage;
      const endIdx = startIdx + state.itemsPerPage;
      const paginatedCards = allFilteredCards.slice(startIdx, endIdx);
      
      els.resultMeta.textContent = totalItems > 0
        ? `Menampilkan ${startIdx + 1}-${Math.min(endIdx, totalItems)} dari ${totalItems} usaha`
        : 'Tidak ada data yang cocok dengan filter saat ini';
      
      els.cardList.innerHTML = paginatedCards.map((card) => {
        const mapsUrl = getGoogleMapsUrl(card.source_latitude, card.source_longitude);
        const nama = escapeHtml(card.nama_usaha_sumber || card.match_nama_usaha || '-');
        const kategori = escapeHtml(card.kategori_sumber || card.kategori_jual || '');
        const alamat = `${escapeHtml(card.nmkec || '')} / ${escapeHtml(card.nmdesa || '')} ${card.rw ? `/ RW ${escapeHtml(card.rw)}` : ''} ${card.rt ? `/ RT ${escapeHtml(card.rt)}` : ''}`.trim();
        
        const nameHtml = mapsUrl ? 
          `<a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">${nama}</a>` :
          nama;
        
        return `
        <div class="border-b border-slate-200 px-3 py-2.5 sm:px-4 sm:py-3 hover:bg-slate-50 transition-colors">
          <div class="text-sm sm:text-base font-medium">${nameHtml}</div>
          <div class="text-xs text-slate-600 mt-1">${kategori}${kategori && alamat ? ' • ' : ''}${alamat}</div>
        </div>
      `;
      }).join('');
      
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
          els.cardList.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    }

    els.sourceTab.addEventListener('change', (e) => { state.selectedSourceTab = e.target.value; renderCards(); });
    els.kecamatan.addEventListener('change', (e) => { state.selectedKecamatan = e.target.value; renderDesaOptions(); renderCards(); });
    els.desa.addEventListener('change', (e) => { state.selectedDesa = e.target.value; renderRwRtOptions(); renderCards(); });
    els.rw.addEventListener('change', (e) => { state.selectedRw = e.target.value; renderCards(); });
    els.rt.addEventListener('change', (e) => { state.selectedRt = e.target.value; renderCards(); });
    els.matchStatus.addEventListener('change', (e) => { state.matchStatus = e.target.value; renderCards(); });
    els.searchQuery.addEventListener('input', (e) => { state.searchQuery = e.target.value; renderCards(); });
    
    // Items per page selector
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    if (itemsPerPageSelect) {
      itemsPerPageSelect.addEventListener('change', (e) => {
        state.itemsPerPage = parseInt(e.target.value, 10);
        state.currentPage = 1;
        renderCards();
      });
    }
    
    els.openFilterDrawer.addEventListener('click', openFilterDrawer);
    els.closeFilterDrawer.addEventListener('click', closeFilterDrawer);
    els.filterDrawerBackdrop.addEventListener('click', closeFilterDrawer);
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeFilterDrawer(); });

    // Prefill search from query param (global navbar search)
    (function() {
      const params = new URLSearchParams(window.location.search || '');
      const q = params.get('q') || '';
      if (q) {
        state.searchQuery = q;
        if (els.searchQuery) els.searchQuery.value = q;
        const gs = document.getElementById('globalSearch');
        if (gs) gs.value = q;
      }
    })();

    if (allCards.length > 0) {
      renderCards();
    } else {
      els.resultMeta.textContent = 'Tidak ada data tersedia';
    }
  </script>
@endpush
