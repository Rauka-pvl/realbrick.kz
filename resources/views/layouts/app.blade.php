<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'REAL BRICK')</title>
  <meta name="description" content="REAL BRICK — премиальный кирпич ручной формовки для фасадов и интерьеров." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  {{-- <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" /> --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('/fonts/stylesheet.css') }}">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            nearblack: '#0B0B0B',
            charcoal: '#151515',
            gold: '#C9A96E',
            muted: '#A3A3A3',
            offwhite: '#F5F5F5',
          },
          fontFamily: {
            sans: ['Montserrat', 'system-ui', 'Segoe UI', 'sans-serif'],
            display: ['Cormorant Garamond', 'Georgia', 'serif'],
          },
          boxShadow: {
            gold: '0 0 40px rgba(201, 169, 110, 0.15)',
            'gold-sm': '0 0 24px rgba(201, 169, 110, 0.12)',
          },
        },
      },
    };
  </script>
  <style>
    html {
      scroll-behavior: smooth;
      width: 100%;
      max-width: 100%;
      overflow-x: hidden;
    }
    body {
      font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif;
      width: 100%;
      max-width: 100%;
      overflow-x: hidden;
    }
    .rb-global-header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 90;
      background: rgba(8, 8, 8, 0.78);
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(6px);
    }
    .rb-global-header .rb-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
    .rb-global-nav {
      display: flex;
      align-items: center;
      color: #ecdac1;
      text-transform: lowercase;
      font-size: 18px;
    }
    .rb-global-nav .nav {
      display: flex;
      gap: 22px;
    }
    .rb-global-nav .nav_link {
      position: relative;
      padding-right: 18px;
    }
    .rb-global-nav .nav_link:not(:last-child)::after {
      content: "";
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 0.5px;
      height: 20px;
      background: #d9b176;
      opacity: 0.95;
    }
    .rb-global-nav .nav_link a {
      color: inherit;
      font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif;
      font-weight: 300;
      transition: color 0.28s ease, transform 0.28s ease, opacity 0.28s ease;
    }
    .rb-global-nav .nav_link a::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: -5px;
      height: 1px;
      background: rgba(201, 169, 110, 0.9);
      transform: scaleX(0);
      transform-origin: center;
      transition: transform 0.28s ease;
      opacity: 0.9;
    }
    .rb-global-nav .nav_link a:hover {
      color: #d9b176;
      transform: translateY(-1px);
      opacity: 0.96;
    }
    .rb-global-nav .nav_link a:hover::after {
      transform: scaleX(1);
    }
    .rb-global-social { display: flex; align-items: center; gap: 8px; }
    .rb-global-social img {
      width: 36px;
      height: 36px;
      display: block;
      transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .rb-global-social a:hover img {
      transform: translateY(-1px);
      opacity: 0.95;
    }
    .rb-global-burger {
      display: none;
      width: 42px;
      height: 42px;
      align-items: center;
      justify-content: center;
      border-radius: 9999px;
      border: 1px solid rgba(201, 169, 110, 0.45);
      background: rgba(10, 10, 10, 0.5);
      color: #ecdac1;
      cursor: pointer;
    }
    .rb-global-burger-lines {
      position: relative;
      width: 18px;
      height: 12px;
      display: block;
    }
    .rb-global-burger-lines::before,
    .rb-global-burger-lines::after,
    .rb-global-burger-lines span {
      content: "";
      position: absolute;
      left: 0;
      width: 100%;
      height: 1.5px;
      background: currentColor;
      transition: transform 0.24s ease, opacity 0.24s ease;
    }
    .rb-global-burger-lines::before { top: 0; }
    .rb-global-burger-lines span { top: 5px; }
    .rb-global-burger-lines::after { bottom: 0; }
    .rb-global-burger.is-active .rb-global-burger-lines::before { transform: translateY(5px) rotate(45deg); }
    .rb-global-burger.is-active .rb-global-burger-lines span { opacity: 0; }
    .rb-global-burger.is-active .rb-global-burger-lines::after { transform: translateY(-5px) rotate(-45deg); }
    .rb-mobile-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.22s ease;
      z-index: 95;
    }
    .rb-mobile-overlay.is-open {
      opacity: 1;
      pointer-events: auto;
    }
    .rb-mobile-menu {
      position: fixed;
      top: 0;
      right: 0;
      width: 280px;
      height: 100dvh;
      padding: 80px 24px 24px;
      background: #0d0d0d;
      border-left: 1px solid rgba(255, 255, 255, 0.08);
      transform: translateX(100%);
      transition: transform 0.24s ease;
      z-index: 96;
    }
    .rb-mobile-menu.is-open { transform: translateX(0); }
    .rb-mobile-menu a {
      display: block;
      padding: 10px 0;
      color: #ecdac1;
      text-transform: lowercase;
      font-weight: 300;
    }
    .rb-mobile-social {
      margin-top: 18px;
      display: flex;
      gap: 10px;
    }
    .rb-mobile-social img {
      width: 34px;
      height: 34px;
      display: block;
    }
    @media (max-width: 980px) {
      .rb-global-nav { font-size: 15px; }
      .rb-global-nav .nav { gap: 14px; }
      .rb-global-nav .nav_link { padding-right: 12px; }
    }
    @media (max-width: 760px) {
      .rb-global-nav { display: none; }
      .rb-global-social { display: none; }
      .rb-global-burger { display: inline-flex; }
    }
    .rb-floating-cart {
      position: fixed;
      right: 24px;
      bottom: 24px;
      width: 62px;
      height: 62px;
      border-radius: 999px;
      border: 1px solid rgba(201, 169, 110, 0.5);
      background:
        radial-gradient(circle at 80% 20%, rgba(201, 169, 110, 0.25), transparent 42%),
        linear-gradient(145deg, rgba(20, 20, 20, 0.96), rgba(8, 8, 8, 0.96));
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
      z-index: 85;
      transition: transform 0.24s ease, border-color 0.24s ease, filter 0.24s ease;
    }
    .rb-floating-cart:hover {
      transform: translateY(-2px);
      border-color: rgba(201, 169, 110, 0.8);
      filter: brightness(1.08);
    }
    .rb-floating-cart__badge {
      position: absolute;
      top: -4px;
      right: -4px;
      min-width: 22px;
      height: 22px;
      border-radius: 999px;
      background: #d1ad70;
      color: #0e0e0e;
      border: 1px solid rgba(255, 255, 255, 0.3);
      font-size: 11px;
      font-weight: 700;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 6px;
    }
    @media (max-width: 760px) {
      .rb-floating-cart {
        width: 56px;
        height: 56px;
        right: 16px;
        bottom: 16px;
      }
    }
  </style>
  @stack('styles')
</head>
<body class="min-h-screen bg-nearblack text-offwhite antialiased selection:bg-gold/30 selection:text-white flex flex-col">
  <header class="rb-global-header">
    <div class="rb-wrap">
      <a href="/" class="shrink-0">
        <img src="{{ asset('storage/img/rblogo-2.png') }}" alt="Realbrick Logo" width="84" height="84">
      </a>
      <nav class="rb-global-nav">
        <ul class="nav">
          <li class="nav_link"><a href="/">главная</a></li>
          <li class="nav_link"><a href="/about">о нас</a></li>
          <li class="nav_link"><a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}">каталог</a></li>
          <li class="nav_link"><a href="/gallery">галерея</a></li>
          <li class="nav_link"><a href="/blog">блог</a></li>
          <li class="nav_link"><a href="/contacts">контакты</a></li>
        </ul>
      </nav>
      <div class="rb-global-social">
        <a href="https://www.facebook.com/61584804524037/mentions/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><img src="{{ asset('storage/img/facebook.png') }}" alt="Facebook"></a>
        <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><img src="{{ asset('storage/img/instagram.png') }}" alt="Instagram"></a>
        <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><img src="{{ asset('storage/img/vatsap.png') }}" alt="WhatsApp"></a>
      </div>
      <button class="rb-global-burger" type="button" aria-label="Открыть меню">
        <span class="rb-global-burger-lines"><span></span></span>
      </button>
    </div>
  </header>
  <div class="rb-mobile-overlay" id="rb-mobile-overlay"></div>
  <nav class="rb-mobile-menu" id="rb-mobile-menu" aria-label="Мобильная навигация">
    <a href="/">главная</a>
    <a href="/about">о нас</a>
    <a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}">каталог</a>
    <a href="/gallery">галерея</a>
    <a href="/blog">блог</a>
    <a href="/contacts">контакты</a>
    <div class="rb-mobile-social">
      <a href="https://www.facebook.com/61584804524037/mentions/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><img src="{{ asset('storage/img/facebook.png') }}" alt="Facebook"></a>
      <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><img src="{{ asset('storage/img/instagram.png') }}" alt="Instagram"></a>
      <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><img src="{{ asset('storage/img/vatsap.png') }}" alt="WhatsApp"></a>
    </div>
  </nav>

  <main class="flex-1 pt-28">
    @if(session('success'))
      <div class="mx-auto max-w-7xl px-4 lg:px-8 pt-4">
        <div class="rounded-xl border border-gold/30 bg-gold/10 px-4 py-3 text-sm text-gold">{{ session('success') }}</div>
      </div>
    @endif
    @yield('content')
  </main>

  <footer class="border-t border-white/10 bg-[linear-gradient(180deg,#0f0f0f_0%,#090909_100%)] py-10 text-offwhite md:py-12">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
      <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
        <div class="flex items-center gap-3 md:max-w-xs">
          <img src="{{ asset('storage/assets/logo-real-brick.png') }}" alt="Real Brick" class="h-12 w-12 object-contain opacity-95" width="120" height="120" decoding="async" />
          <div>
            <p class="font-bold uppercase tracking-[0.2em] text-gold">Real Brick</p>
            <p class="text-sm text-offwhite/65">Минеральный кирпич ручной формовки</p>
          </div>
        </div>
        <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm font-medium lowercase text-offwhite/85">
          <a href="/" class="transition hover:text-gold">главная</a>
          <a href="/about" class="transition hover:text-gold">о нас</a>
          <a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}" class="transition hover:text-gold">каталог</a>
          <a href="/gallery" class="transition hover:text-gold">галерея</a>
          <a href="/blog" class="transition hover:text-gold">блог</a>
          <a href="/contacts" class="transition hover:text-gold">контакты</a>
        </nav>
        <div class="text-sm text-offwhite/75">
          <p class="font-medium text-offwhite">+7 (700) 444 69 99</p>
          <p class="mt-1">info@realbrick.kz</p>
        </div>
      </div>
      <div class="mt-8 border-t border-white/10 pt-4 text-xs text-offwhite/45">
        © {{ date('Y') }} Real Brick. Все права защищены.
      </div>
    </div>
  </footer>
  @if(request()->routeIs('catalog.*'))
    @php($floatingCartCount = (int) collect((array) session('cart.items', []))->sum('qty'))
    <a href="{{ route('cart.index', ['lang' => request('lang', 'ru')]) }}" class="rb-floating-cart" aria-label="Открыть корзину">
      <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M3 4.5h2.4c.48 0 .9.32 1.03.78l.35 1.22h11.96c.7 0 1.2.68.98 1.35l-1.5 4.9a1.1 1.1 0 0 1-1.04.77H9.07a1.1 1.1 0 0 1-1.05-.79L6.1 6.9" fill="none" stroke="#d6b57b" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="10.2" cy="18.2" r="1.4" fill="#d6b57b"/>
        <circle cx="16.7" cy="18.2" r="1.4" fill="#d6b57b"/>
      </svg>
      @if($floatingCartCount > 0)
        <span class="rb-floating-cart__badge">{{ $floatingCartCount > 99 ? '99+' : $floatingCartCount }}</span>
      @endif
    </a>
  @endif
  <script>
    (() => {
      const burger = document.querySelector('.rb-global-burger');
      const overlay = document.getElementById('rb-mobile-overlay');
      const menu = document.getElementById('rb-mobile-menu');
      if (!burger || !overlay || !menu) return;

      const closeMenu = () => {
        burger.classList.remove('is-active');
        overlay.classList.remove('is-open');
        menu.classList.remove('is-open');
        document.body.style.overflow = '';
      };
      const openMenu = () => {
        burger.classList.add('is-active');
        overlay.classList.add('is-open');
        menu.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      };

      burger.addEventListener('click', () => {
        if (menu.classList.contains('is-open')) closeMenu();
        else openMenu();
      });
      overlay.addEventListener('click', closeMenu);
      menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
      });
    })();
  </script>
  @stack('scripts')
</body>
</html>

