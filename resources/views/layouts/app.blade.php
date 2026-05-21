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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggle = document.getElementById('searchToggle');
      const wrap = document.getElementById('globalSearchWrap');
      const input = document.getElementById('globalSearch');
      const form = document.getElementById('globalSearchForm');
      if (!toggle || !wrap) return;

      function showSearch() {
        wrap.classList.remove('hidden');
        toggle.classList.add('hidden');
        setTimeout(() => input && input.focus(), 50);
      }

      function hideSearch() {
        wrap.classList.add('hidden');
        toggle.classList.remove('hidden');
      }

      toggle.addEventListener('click', () => {
        if (wrap.classList.contains('hidden')) {
          showSearch();
          return;
        }

        // shouldn't be reachable because toggle is hidden when open,
        // but keep fallback: submit if has query, otherwise focus
        const q = input && input.value ? input.value.trim() : '';
        if (q) {
          form && form.submit();
        } else {
          input && input.focus();
        }
      });

      // Click outside the form hides the search input and restores the toggle
      document.addEventListener('click', (e) => {
        if (wrap.classList.contains('hidden')) return;
        const target = e.target;
        if (!form.contains(target) && !toggle.contains(target)) {
          hideSearch();
        }
      });

      // Escape to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !wrap.classList.contains('hidden')) {
          hideSearch();
        }
      });
    });
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
    <div class="mx-auto max-w-7xl px-3 py-3 sm:px-6 lg:px-8">
      <div class="flex flex-wrap items-center gap-3 lg:flex-nowrap lg:items-center lg:justify-start lg:gap-3">
        <img src="/assets/Logo BPS Baru 2.png" alt="BPS" class="order-1 h-10 w-auto shrink-0 sm:h-12 lg:h-12" />
        <div class="relative order-2 ml-auto w-full max-w-[220px] sm:max-w-md lg:order-3 lg:ml-auto lg:w-auto lg:max-w-md">
          <form id="globalSearchForm" method="GET" action="/panduanSE/usaha-umkm" class="w-full">
            <label for="globalSearch" class="sr-only">Cari</label>
            <div class="flex items-center justify-end gap-2">
              <div id="globalSearchWrap" class="hidden flex-1 min-w-0 items-center gap-2">
                <input id="globalSearch" name="q" type="search" autocomplete="off" placeholder="Cari usaha, kategori, desa..." class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs outline-none focus:border-amber-500 focus:bg-white sm:text-sm" />
                <button type="submit" class="shrink-0 rounded-xl bg-forest px-3 py-2 text-xs font-semibold text-white sm:px-4 sm:text-sm">Cari</button>
              </div>
              <button id="searchToggle" type="button" class="inline-flex shrink-0 items-center justify-center rounded-full bg-white p-1.5 ring-1 ring-slate-200 z-10 sm:p-2">
                <img src="/assets/search.png" alt="Cari" class="h-4 w-4 sm:h-5 sm:w-5" />
              </button>
            </div>
          </form>
          <div id="globalSearchSuggestions" class="absolute left-0 right-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft"></div>
        </div>
        <div id="navLinks" class="order-3 relative flex w-full basis-full items-center gap-2 overflow-x-auto pb-1 lg:order-2 lg:ml-4 lg:w-auto lg:basis-auto lg:gap-6 lg:overflow-visible lg:pb-0">
          <div id="navIndicator" class="pointer-events-none absolute rounded-xl border border-forest/10 bg-gradient-to-b from-forest/10 via-forest/6 to-forest/5 shadow-[inset_0_-2px_0_rgba(20,83,45,0.28)] opacity-0 transition-[left,top,width,height,opacity] duration-300 ease-out" style="left:0; top:0; width:0; height:0;"></div>
          <a href="/panduanSE/usaha-umkm" class="nav-link relative z-10 rounded-lg px-2 py-1 text-sm font-medium text-ink transition-[color,transform,background-color] duration-200 hover:bg-forest/5 hover:text-ink sm:px-0">Usaha UMKM</a>
          <a href="/panduanSE/usaha-besar" class="nav-link relative z-10 rounded-lg px-2 py-1 text-sm font-medium text-slate-700 transition-[color,transform,background-color] duration-200 hover:bg-forest/5 hover:text-ink sm:px-0">Usaha Besar</a>
          <a href="/panduanSE/kbli" class="nav-link relative z-10 rounded-lg px-2 py-1 text-sm font-medium text-slate-700 transition-[color,transform,background-color] duration-200 hover:bg-forest/5 hover:text-ink sm:px-0">KBLI</a>
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
  <script>
    (function () {
      function initNavIndicator() {
        const container = document.getElementById('navLinks');
        const indicator = document.getElementById('navIndicator');
        if (!container || !indicator) return;

        const links = Array.from(container.querySelectorAll('a.nav-link'));
        if (!links.length) return;

        function placeIndicator(el, animate = true) {
          const cRect = container.getBoundingClientRect();
          const r = el.getBoundingClientRect();
          const left = Math.round(r.left - cRect.left) - 8;
          const top = Math.round(r.top - cRect.top) - 6;
          const width = Math.round(r.width) + 16;
          const height = Math.round(r.height) + 12;

          indicator.style.transition = animate ? '' : 'none';
          indicator.style.left = left + 'px';
          indicator.style.top = top + 'px';
          indicator.style.width = width + 'px';
          indicator.style.height = height + 'px';
          indicator.style.opacity = '1';
          if (!animate) {
            requestAnimationFrame(() => {
              indicator.style.transition = '';
            });
          }
        }

        function setActiveLink(activeLink) {
          links.forEach((link) => {
            link.classList.remove('font-bold', 'text-ink');
            link.classList.add('font-medium', 'text-slate-700');
          });

          if (activeLink) {
            activeLink.classList.add('font-bold', 'text-ink');
            activeLink.classList.remove('font-medium', 'text-slate-700');
          }
        }

        function resolveActiveLink() {
          const currentPath = window.location.pathname.replace(/\/+$/, '');
          const stored = localStorage.getItem('navActiveHref');

          if (stored) {
            const storedMatch = links.find((a) => {
              try {
                return new URL(a.href, window.location.origin).pathname.replace(/\/+$/, '') === new URL(stored, window.location.origin).pathname.replace(/\/+$/, '');
              } catch (e) {
                return false;
              }
            });

            if (storedMatch) {
              return storedMatch;
            }
          }

          const pathMatch = links.find((a) => {
            try {
              return new URL(a.href, window.location.origin).pathname.replace(/\/+$/, '') === currentPath;
            } catch (e) {
              return false;
            }
          });

          return pathMatch || links[0];
        }

        const initialActiveLink = resolveActiveLink();
        if (initialActiveLink) {
          placeIndicator(initialActiveLink, false);
          setActiveLink(initialActiveLink);
        }

        // show indicator only when a link is clicked, and persist the choice
        links.forEach((a) => {

          a.addEventListener('click', (e) => {
            const href = a.getAttribute('href');
            try {
              const url = new URL(href, window.location.href);
              if (url.origin !== window.location.origin) return;
            } catch (err) {
              return;
            }

            e.preventDefault();
            // persist which link was clicked so destination shows the indicator
            try { localStorage.setItem('navActiveHref', new URL(href, window.location.href).href); } catch (err) {}
            placeIndicator(a, true);
            setActiveLink(a);
            window.location.href = href;
          });
        });

        let resizeTimer = null;
        window.addEventListener('resize', () => {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(() => {
            // only reposition if indicator is visible (user has clicked before)
            if (indicator.style.opacity === '1') {
              const storedHref = localStorage.getItem('navActiveHref');
              if (storedHref) {
                const match = links.find((a) => {
                  try { return new URL(a.href, window.location.origin).pathname.replace(/\/+$/, '') === new URL(storedHref, window.location.origin).pathname.replace(/\/+$/, ''); } catch (e) { return false; }
                }) || resolveActiveLink();
                if (match) {
                  placeIndicator(match, false);
                  setActiveLink(match);
                }
              }
            }
          }, 120);
        });
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavIndicator);
      } else {
        initNavIndicator();
      }
    })();
  </script>
</body>
</html>
