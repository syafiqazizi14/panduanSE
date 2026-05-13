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
  </style>
  @stack('styles')
</head>
<body class="text-ink antialiased">
  <a href="#main-content" class="sr-only focus:not-sr-only absolute left-4 top-4 z-50 rounded bg-white px-3 py-2 text-sm font-medium text-ink ring-1 ring-black/5">Skip to content</a>

  <nav class="w-full border-b border-black/5 bg-white/90 backdrop-blur-sm">
    <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-6">
          <a href="/" class="text-sm font-bold text-ink">Beranda</a>
          <a href="/panduanSE/usaha-umkm" class="text-sm font-medium text-ink">Usaha UMKM</a>
          <a href="/panduanSE/usaha-besar" class="text-sm text-slate-700 hover:text-ink">Usaha Besar</a>
          <a href="/panduanSE/kbli" class="text-sm text-slate-700 hover:text-ink">KBLI</a>
        </div>
        <div class="relative ml-auto w-full max-w-md">
          <form id="globalSearchForm" method="GET" action="/panduanSE/usaha-umkm" class="w-full">
            <label for="globalSearch" class="sr-only">Cari</label>
            <div class="relative">
              <input id="globalSearch" name="q" type="search" autocomplete="off" placeholder="Cari usaha, kategori, desa, RW/RT..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-amber-500 focus:bg-white" />
              <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 rounded px-3 py-1 text-sm font-semibold bg-forest text-white">Cari</button>
            </div>
          </form>
          <div id="globalSearchSuggestions" class="absolute left-0 right-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft"></div>
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
          box.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">Tidak ada saran untuk "' + escapeHtml(query) + '".</div>';
          box.classList.remove('hidden');
          return;
        }

        box.innerHTML = items.map((item) => {
          const label = escapeHtml(item.label || item.value || '');
          const secondary = escapeHtml(item.secondary || '');
          return '<button type="button" data-value="' + escapeHtml(item.value || item.label || '') + '" class="block w-full px-4 py-3 text-left hover:bg-slate-50"><div class="text-sm font-semibold text-ink">' + label + '</div>' + (secondary ? '<div class="mt-0.5 text-xs text-slate-500">' + secondary + '</div>' : '') + '</button>';
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
