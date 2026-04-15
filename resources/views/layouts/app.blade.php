<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'REAL BRICK')</title>
  <meta name="description" content="REAL BRICK — премиальный кирпич ручной формовки для фасадов и интерьеров." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('fonts/stylesheet.css') }}">
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
    body { font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif; }
    html { scroll-behavior: smooth; }
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
      gap: 18px;
      color: #ecdac1;
      text-transform: lowercase;
      font-size: 18px;
    }
    .rb-global-nav a {
      position: relative;
      color: inherit;
      font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif;
      font-weight: 300;
      transition: color 0.28s ease, transform 0.28s ease, opacity 0.28s ease;
    }
    .rb-global-nav a::after {
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
    .rb-global-nav a:hover {
      color: #d9b176;
      transform: translateY(-1px);
      opacity: 0.96;
    }
    .rb-global-nav a:hover::after {
      transform: scaleX(1);
    }
    .rb-nav-sep {
      color: rgba(201, 169, 110, 0.55);
      transition: color 0.28s ease, opacity 0.28s ease;
    }
    .rb-global-nav a:hover + .rb-nav-sep {
      color: rgba(217, 177, 118, 0.85);
      opacity: 1;
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
    @media (max-width: 980px) {
      .rb-global-nav { font-size: 15px; gap: 10px; }
    }
    @media (max-width: 760px) {
      .rb-global-nav { display: none; }
    }
  </style>
  @stack('styles')
</head>
<body class="min-h-screen bg-nearblack text-offwhite antialiased selection:bg-gold/30 selection:text-white flex flex-col">
  <header class="rb-global-header">
    <div class="rb-wrap">
      <a href="/" class="shrink-0">
        <img src="{{ asset('rb/img/RBlogo.png') }}" alt="Realbrick Logo" width="84" height="84">
      </a>
      <nav class="rb-global-nav">
        <a href="/">главная</a><span class="rb-nav-sep">|</span>
        <a href="/about">о нас</a><span class="rb-nav-sep">|</span>
        <a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}">каталог</a><span class="rb-nav-sep">|</span>
        <a href="/gallery">галерея</a><span class="rb-nav-sep">|</span>
        <a href="/blog">блог</a><span class="rb-nav-sep">|</span>
        <a href="/contacts">контакты</a>
      </nav>
      <div class="rb-global-social">
        <a href="#" aria-label="Facebook"><img src="{{ asset('rb/img/фейсбук.png') }}" alt="Facebook"></a>
        <a href="#" aria-label="Instagram"><img src="{{ asset('rb/img/Инстаграм.png') }}" alt="Instagram"></a>
        <a href="#" aria-label="WhatsApp"><img src="{{ asset('rb/img/Ватсап.png') }}" alt="WhatsApp"></a>
      </div>
    </div>
  </header>

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
  @stack('scripts')
</body>
</html>

