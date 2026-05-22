<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $pageTitle ?? 'UMKM Mojokerto - Verifikasi Lapangan' }}</title>
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
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4 sm:gap-6">
          <img src="{{ asset('assets/Logo BPS Baru 2.png') }}" alt="Logo BPS Mojokerto" class="h-7 sm:h-9">
          <a href="/panduanSE/usaha-umkm" class="relative px-3 py-2 text-sm font-medium transition-all duration-300 rounded-lg hover:scale-105 {{ request()->path() === 'panduanSE/usaha-umkm' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">Usaha UMKM</a>
          <a href="/panduanSE/usaha-besar" class="relative px-3 py-2 text-sm font-medium transition-all duration-300 rounded-lg hover:scale-105 {{ request()->path() === 'panduanSE/usaha-besar' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">Usaha Besar</a>
          <a href="/panduanSE/kbli" class="relative px-3 py-2 text-sm font-medium transition-all duration-300 rounded-lg hover:scale-105 {{ request()->path() === 'panduanSE/kbli' ? 'text-forest bg-forest/10' : 'text-slate-700 hover:text-forest hover:bg-forest/10' }}">KBLI</a>
        </div>
        <div class="relative ml-auto w-full max-w-lg">
          <form id="globalSearchForm" method="GET" action="/panduanSE/usaha-umkm" class="w-full">
            <label for="globalSearch" class="sr-only">Cari</label>
            <div class="relative group">
              <div class="absolute left-0 top-0 bottom-0 flex items-center justify-center w-10 pointer-events-none text-slate-400 group-focus-within:text-forest transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input id="globalSearch" name="q" type="search" autocomplete="off" placeholder="Cari usaha..." class="w-full rounded-2xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm outline-none transition-all duration-300 focus:border-forest focus:ring-2 focus:ring-forest/20 hover:border-slate-400" />
              <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-semibold bg-forest text-white transition-all duration-300 hover:bg-forest/90 active:scale-95">Cari</button>
            </div>
          </form>
          <div id="globalSearchSuggestions" class="absolute left-0 right-0 top-full z-50 mt-3 hidden rounded-2xl border border-slate-200 bg-white shadow-lg"></div>
        </div>
      </div>
    </div>
  </nav>

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
  </script>
  @stack('scripts')
</body>
</html>
