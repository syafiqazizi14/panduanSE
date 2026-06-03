<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $pageTitle ?? 'UMKM Mojokerto - Verifikasi Lapangan' }}</title>
  <link rel="icon" type="image/png" href="/favicon-bps.png?v=3">
  <link rel="apple-touch-icon" href="/favicon-bps.png?v=3">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            ink: '#0f172a',
            paper: '#f6f1e8',
            sand: '#efe4d0',
            rust: '#b45309',
            forest: '#14532d',
            gold: '#f59e0b'
          },
          boxShadow: {
            soft: '0 18px 45px rgba(15, 23, 42, 0.12)'
          }
        }
      }
    }
  </script>
  <style>
    :root { color-scheme: light; }
    body {
      background:
        radial-gradient(circle at top left, rgba(245, 158, 11, 0.14), transparent 34%),
        radial-gradient(circle at top right, rgba(20, 83, 45, 0.12), transparent 28%),
        linear-gradient(180deg, #fbf7f0 0%, #f6f1e8 48%, #efe6d7 100%);
    }
    .grain {
      background-image: linear-gradient(rgba(255,255,255,0.12), rgba(255,255,255,0.12)), radial-gradient(rgba(0,0,0,0.06) 1px, transparent 1px);
      background-size: auto, 18px 18px;
      background-position: 0 0, 0 0;
    }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    #globalSearchSuggestions {
      max-height: 400px;
      overflow-y: auto;
    }
    #globalSearchSuggestions::-webkit-scrollbar {
      width: 6px;
    }
    #globalSearchSuggestions::-webkit-scrollbar-track {
      background: transparent;
    }
    #globalSearchSuggestions::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }
    #globalSearchSuggestions::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>
  @stack('styles')
</head>
<body class="text-ink antialiased">
  <a href="#main-content" class="sr-only focus:not-sr-only absolute left-4 top-4 z-50 rounded bg-white px-3 py-2 text-sm font-medium text-ink ring-1 ring-black/5">Skip to content</a>

  <nav class="w-full border-b border-black/5 bg-white/90 backdrop-blur-sm">
    <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
      <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-wrap items-center gap-2 sm:gap-4 sm:gap-y-2">
          <img src="{{ asset('assets/Logo BPS Baru 2.png') }}" alt="Logo BPS Mojokerto" class="h-7 sm:h-9">
          <a href="/panduanSE/usaha-umkm" class="relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-300 hover:scale-105 {{ request()->path() === 'panduanSE/usaha-umkm' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">Usaha UMKM</a>
          <a href="/panduanSE/usaha-besar" class="relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-300 hover:scale-105 {{ request()->path() === 'panduanSE/usaha-besar' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">Usaha Besar</a>
          <a href="/panduanSE/kbli" class="relative rounded-lg px-3 py-2 text-sm font-medium transition-all duration-300 hover:scale-105 {{ request()->path() === 'panduanSE/kbli' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">KBLI</a>
        </div>
        <div class="relative flex w-full flex-col gap-3 xl:ml-auto xl:max-w-2xl xl:flex-row xl:items-center">
          <form id="globalSearchForm" method="GET" action="/panduanSE/usaha-umkm" class="w-full">
            <label for="globalSearch" class="sr-only">Cari</label>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
              <div class="relative group flex-1">
                <div class="absolute left-0 top-0 bottom-0 flex items-center justify-center w-10 pointer-events-none text-slate-400 group-focus-within:text-forest transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input id="globalSearch" name="q" type="search" autocomplete="off" placeholder="Cari usaha..." class="w-full rounded-2xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm outline-none transition-all duration-300 focus:border-forest focus:ring-2 focus:ring-forest/20 hover:border-slate-400" />
              </div>
              <button type="submit" class="w-full rounded-2xl bg-forest px-4 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:bg-forest/90 active:scale-95 sm:w-auto sm:px-3 sm:py-1.5 sm:text-xs">Cari</button>
            </div>
          </form>
          <button id="openRefreshModal" type="button" class="w-full rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-slate-800 active:scale-[0.99] xl:w-auto">Refresh Data</button>
          <div id="globalSearchSuggestions" class="absolute left-0 right-0 top-full z-50 mt-3 hidden rounded-2xl border border-slate-200 bg-white shadow-lg"></div>
        </div>
      </div>
    </div>
  </nav>

  <div id="refreshModalBackdrop" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm"></div>
  <div id="refreshModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-[2rem] bg-white p-4 shadow-[0_30px_90px_rgba(15,23,42,0.25)] ring-1 ring-black/5 sm:p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500 sm:text-xs">Refresh data</p>
          <h2 class="mt-1 text-xl font-black text-ink sm:text-2xl">Masukkan password</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">Tombol ini akan merefresh semua data sekaligus, termasuk seed utama, Usaha Besar, dan KBLI.</p>
        </div>
        <button id="closeRefreshModal" type="button" class="rounded-full bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">Tutup</button>
      </div>
      <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <div class="flex items-start gap-3">
          <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-amber-500"></div>
          <p>Pastikan password refresh hanya dipegang petugas yang berwenang. Setelah dijalankan, sistem akan memuat ulang semua seed dari spreadsheet.</p>
        </div>
      </div>
      <div class="mt-5 space-y-4">
        <div>
          <label for="refreshPassword" class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Password</label>
          <input id="refreshPassword" type="password" autocomplete="current-password" placeholder="Masukkan password refresh" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-forest focus:bg-white focus:ring-2 focus:ring-forest/20">
        </div>
        <div class="flex items-center gap-3">
          <button id="submitRefreshModal" type="button" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-forest px-4 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-forest/90">
            <svg id="refreshSpinner" class="hidden h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a10 10 0 100 20 10 10 0 000-20z"></path>
            </svg>
            <span id="submitRefreshLabel">Jalankan Refresh</span>
          </button>
          <button id="cancelRefreshModal" type="button" class="inline-flex rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-700">Batal</button>
        </div>
        <p id="refreshModalMessage" class="hidden rounded-2xl px-4 py-3 text-sm" aria-live="polite"></p>
      </div>
    </div>
  </div>

  @yield('content')
  <script>
    (function () {
      const input = document.getElementById('globalSearch');
      const form = document.getElementById('globalSearchForm');
      const box = document.getElementById('globalSearchSuggestions');
      if (!input || !form || !box) {
        return;
      }

      let timer = null;
      let controller = null;

      function hideBox() {
        box.classList.add('hidden');
        box.innerHTML = '';
      }

      function renderItems(items, query) {
        if (!items.length) {
          box.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Tidak ada hasil untuk "' + escapeHtml(query) + '"</div>';
          box.classList.remove('hidden');
          return;
        }

        box.innerHTML = items.map((item) => {
          const label = escapeHtml(item.label || item.value || '');
          const kategori = escapeHtml(item.kategori || '');
          const lokasi = escapeHtml(item.lokasi || '');
          const detail = kategori + (kategori && lokasi ? ' • ' : '') + lokasi;
          return '<button type="button" data-value="' + escapeHtml(item.value || item.label || '') + '" class="block w-full px-4 py-3 text-left border-b border-slate-100 hover:bg-forest/5 transition-colors"><div class="text-sm font-semibold text-ink">' + label + '</div>' + (detail ? '<div class="mt-1 text-xs text-slate-600 truncate">' + detail + '</div>' : '') + '</button>';
        }).join('');
        box.classList.remove('hidden');

        box.querySelectorAll('button[data-value]').forEach((btn) => {
          btn.addEventListener('click', () => {
            input.value = btn.getAttribute('data-value') || '';
            hideBox();
            form.submit();
          });
        });
      }

      function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
      }

      async function fetchSuggestions(query) {
        if (controller) {
          controller.abort();
        }
        controller = new AbortController();

        const url = '/panduanSE/suggestions?q=' + encodeURIComponent(query);
        const response = await fetch(url, { signal: controller.signal, headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
          throw new Error('Request failed');
        }
        return await response.json();
      }

      input.addEventListener('input', () => {
        const query = input.value.trim();
        if (timer) {
          clearTimeout(timer);
        }
        if (query.length < 2) {
          hideBox();
          return;
        }

        timer = setTimeout(async () => {
          try {
            const data = await fetchSuggestions(query);
            renderItems(Array.isArray(data.items) ? data.items : [], query);
          } catch (error) {
            hideBox();
          }
        }, 220);
      });

      input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && box.innerHTML.trim() !== '') {
          box.classList.remove('hidden');
        }
      });

      document.addEventListener('click', (event) => {
        if (!form.contains(event.target) && !box.contains(event.target)) {
          hideBox();
        }
      });

      form.addEventListener('submit', hideBox);
    })();

    (function () {
      const openBtn = document.getElementById('openRefreshModal');
      const modal = document.getElementById('refreshModal');
      const backdrop = document.getElementById('refreshModalBackdrop');
      const closeBtn = document.getElementById('closeRefreshModal');
      const cancelBtn = document.getElementById('cancelRefreshModal');
      const submitBtn = document.getElementById('submitRefreshModal');
      const submitLabel = document.getElementById('submitRefreshLabel');
      const spinner = document.getElementById('refreshSpinner');
      const passwordInput = document.getElementById('refreshPassword');
      const message = document.getElementById('refreshModalMessage');
      if (!openBtn || !modal || !backdrop || !closeBtn || !cancelBtn || !submitBtn || !submitLabel || !spinner || !passwordInput || !message) {
        return;
      }

      const endpoint = @json(route('panduanSE.refresh'));
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

      function openModal() {
        modal.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        passwordInput.value = '';
        message.className = 'hidden rounded-2xl px-4 py-3 text-sm';
        message.textContent = '';
        setTimeout(() => passwordInput.focus(), 50);
      }

      function closeModal() {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
      }

      function showMessage(text, type) {
        if (!text) {
          message.className = 'hidden rounded-2xl px-4 py-3 text-sm';
          message.textContent = '';
          return;
        }

        const classes = ['rounded-2xl px-4 py-3 text-sm'];
        if (type === 'success') classes.push('bg-emerald-100 text-emerald-800');
        else if (type === 'error') classes.push('bg-rose-100 text-rose-800');
        else if (type === 'info') classes.push('bg-slate-100 text-slate-700');
        else classes.push('bg-slate-100 text-slate-700');
        message.className = classes.join(' ');
        message.textContent = text;
      }

      function reloadWithFreshUrl() {
        const url = new URL(window.location.href);
        url.searchParams.set('_refresh', Date.now().toString());
        window.location.replace(url.toString());
      }

      function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        passwordInput.disabled = isLoading;
        spinner.classList.toggle('hidden', !isLoading);
        submitLabel.textContent = isLoading ? 'Memproses...' : 'Jalankan Refresh';
        submitBtn.classList.toggle('opacity-80', isLoading);
        submitBtn.classList.toggle('cursor-not-allowed', isLoading);
      }

      async function submitRefresh() {
        const password = passwordInput.value.trim();
        if (!password) {
          showMessage('Password wajib diisi.', 'error');
          return;
        }

        setLoading(true);
        showMessage('Sedang menjalankan refresh semua data...', 'info');

        try {
          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ password }),
          });

          const data = await response.json().catch(() => ({}));
          if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Refresh gagal dijalankan.');
          }

          showMessage(data.message || 'Refresh selesai.', 'success');
          setLoading(false);
          setTimeout(() => {
            closeModal();
            reloadWithFreshUrl();
          }, 900);
        } catch (error) {
          showMessage(error.message || 'Gagal menjalankan refresh.', 'error');
        } finally {
          setLoading(false);
        }
      }

      openBtn.addEventListener('click', openModal);
      closeBtn.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);
      backdrop.addEventListener('click', closeModal);
      submitBtn.addEventListener('click', submitRefresh);
      passwordInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          submitRefresh();
        }
      });
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
          closeModal();
        }
      });
    })();
  </script>
  @stack('scripts')
</body>
</html>
