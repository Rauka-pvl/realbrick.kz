@extends('layouts.app')

@section('title', 'REAL BRICK — премиальный кирпич ручной формовки')

@push('styles')
  <style>
    body {
      font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif;
    }
    html {
      scroll-behavior: smooth;
    }
    @media (prefers-reduced-motion: reduce) {
      html {
        scroll-behavior: auto;
      }
      .reveal {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }
    }
    .reveal {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity 0.65s ease, transform 0.65s ease;
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }
    .faq-panel {
      display: grid;
      grid-template-rows: 0fr;
      transition: grid-template-rows 0.4s ease;
    }
    .faq-item.is-open .faq-panel {
      grid-template-rows: 1fr;
    }
    .faq-panel-inner {
      overflow: hidden;
    }
    body.modal-open {
      overflow: hidden;
    }
    section[id],
    #lead-form {
      scroll-margin-top: 6rem;
    }
    @keyframes hero-brick-levitate {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    .hero-brick-levitate {
      animation: hero-brick-levitate 4.8s ease-in-out infinite;
    }
    @media (prefers-reduced-motion: reduce) {
      .hero-brick-levitate {
        animation: none;
      }
    }
    .hero-nav-sep {
      color: rgba(201, 169, 110, 0.45);
      user-select: none;
    }
    .hero-bg-photo {
      filter: saturate(0.82) brightness(0.9) contrast(1.06);
    }
    .hero-bg-house {
      filter: blur(4px) saturate(0.75) brightness(0.72);
    }
    .hero-brand-title {
      font-family: "Stem", "STEM", "Montserrat", system-ui, sans-serif;
      font-weight: 800;
      text-transform: uppercase;
      line-height: 0.86;
      letter-spacing: 0.02em;
      font-size: clamp(3.6rem, 8.8vw, 8.25rem);
      background: linear-gradient(180deg, #f0d39a 0%, #c9a96e 36%, #9f7443 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      text-shadow: 0 6px 30px rgba(187, 106, 57, 0.18);
      white-space: nowrap;
    }
    @media (min-width: 1024px) {
      .hero-desktop-canvas {
        min-height: calc(100svh - 5.5rem);
        min-height: calc(100vh - 5.5rem);
      }
    }
    .social-gold-btn {
      background: #c9a96e;
      color: #0b0b0b;
      border: 1px solid rgba(201, 169, 110, 0.95);
      box-shadow: 0 0 20px rgba(201, 169, 110, 0.25);
      transition: transform 180ms ease, box-shadow 180ms ease, opacity 180ms ease;
    }
    .social-gold-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 0 26px rgba(201, 169, 110, 0.4);
      opacity: 0.95;
    }
    .hero-left-kicker {
      max-width: 34rem;
      font-size: clamp(1.25rem, 2.35vw, 2.45rem);
      line-height: 0.96;
      letter-spacing: 0.01em;
      font-weight: 800;
      text-transform: uppercase;
      color: #f5f5f5;
    }
    .hero-left-subline {
      max-width: 22rem;
      font-size: clamp(0.95rem, 1.2vw, 1.35rem);
      line-height: 1.1;
      font-weight: 500;
      color: rgba(245, 245, 245, 0.9);
      text-transform: lowercase;
    }
    .hero-brick-float-img {
      filter: drop-shadow(0 0 42px rgba(201, 169, 110, 0.5)) drop-shadow(0 0 80px rgba(201, 169, 110, 0.22)) drop-shadow(0 28px 56px rgba(0, 0, 0, 0.75));
    }

    .usage-dot {
      width: 12px;
      height: 12px;
      border-radius: 9999px;
      border: 1px solid rgba(201, 169, 110, 0.65);
      background: transparent;
      transition: transform 200ms ease, background 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
    }

    .usage-dot.is-active {
      background: rgba(201, 169, 110, 0.95);
      box-shadow: 0 0 0 5px rgba(201, 169, 110, 0.14), 0 0 34px rgba(201, 169, 110, 0.18);
      border-color: rgba(201, 169, 110, 0.95);
      transform: scale(1.08);
    }



  </style>
@endpush

@section('content')
    @php
      $page = $page ?? 'home';
      $featured = null;
      $active = '';
      $topics = [];
      $cards = collect();
      $posts = collect();
      $post = null;
      $featuredPost = $featuredPost ?? null;
      $blogPosts = $blogPosts ?? collect();
      $blogTopics = $blogTopics ?? [];
      $activeTopic = $activeTopic ?? '';
      $blogPost = $blogPost ?? null;
      $calculatorMaterials = $calculatorMaterials ?? collect();
      $aboutStats = [
        ['value' => '15+', 'label' => 'лет на рынке'],
        ['value' => '500', 'label' => 'реализованных проектов'],
        ['value' => '100+', 'label' => 'уникальных коллекций'],
        ['value' => '100%', 'label' => 'экологичный состав'],
      ];
    @endphp

    @if($page === 'contacts')
    <section class="overflow-x-clip bg-black pt-20 pb-0 md:pt-24 md:pb-0">
      <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="pb-8 text-xs text-offwhite/70">
          <a href="/" class="hover:text-offwhite">Главная</a>
          <span class="px-2">/</span>
          <span>Контакты</span>
        </div>

        <div class="grid grid-cols-1 items-stretch justify-center gap-5 lg:grid-cols-[400px_400px]">
          <div class="mx-auto flex h-full w-full max-w-[400px] min-h-[240px] flex-col rounded-3xl border border-gold/35 bg-nearblack/75 p-6 backdrop-blur-sm sm:min-h-[252px] sm:p-7 lg:mx-0 lg:max-w-none">
            <h2 class="text-4xl font-semibold leading-tight text-offwhite">Наши <span class="text-gold">контакты</span></h2>
            <p class="mt-4 text-sm text-offwhite/75">Мы всегда на связи, чтобы помочь вам реализовать проект вашей мечты.</p>
            <div class="mt-7 grid grid-cols-2 gap-5 text-sm">
              <div>
                <div class="text-offwhite/55">Адрес</div>
                <div class="mt-1 text-offwhite">Казахстан, г. Алматы,<br>ул. Минина 14А</div>
              </div>
              <div>
                <div class="text-offwhite/55">Телефон</div>
                <a href="tel:+77004446999" class="mt-1 block text-offwhite hover:text-gold">+7 (700) 444 69 99</a>
              </div>
              <div>
                <div class="text-offwhite/55">Время работы</div>
                <div class="mt-1 text-offwhite">пн-пт 10:00-20:00</div>
              </div>
              <div>
                <div class="text-offwhite/55">Эл. почта</div>
                <a href="mailto:info@realbrick.kz" class="mt-1 block text-offwhite hover:text-gold">info@realbrick.kz</a>
              </div>
            </div>
            <div class="mt-7 flex items-center gap-2">
              <a href="https://www.facebook.com/61584804524037/mentions/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="social-gold-btn flex h-9 w-9 items-center justify-center rounded-full">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </a>
              <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="social-gold-btn flex h-9 w-9 items-center justify-center rounded-full">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              </a>
              <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="social-gold-btn flex h-9 w-9 items-center justify-center rounded-full">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              </a>
            </div>
          </div>

          <div class="relative mx-auto h-full w-full max-w-[400px] min-h-[240px] overflow-hidden rounded-3xl border border-gold/20 bg-nearblack/40 sm:min-h-[252px] lg:mx-0 lg:max-w-none">
            <iframe
              title="Карта — REAL BRICK (Казахстан, г. Алматы, ул. Минина 14А)"
              class="absolute inset-0 h-full w-full border-0"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2906.6987623670207!2d76.9259222!3d43.236775099999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38836ed114d22b95%3A0x5f13fc75a00e700e!2z0YPQuy4g0JzQuNC90LjQvdCwIDE0LCDQkNC70LzQsNGC0YsgMDUwMDAw!5e0!3m2!1sru!2skz!4v1775539053657!5m2!1sru!2skz"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
        </div>

        <div class="relative left-1/2 mt-4 w-screen max-w-none -translate-x-1/2 overflow-x-clip lg:mt-2">
          <div class="relative">
            <img
              src="{{ asset('storage/assets/contacts-bg.png') }}"
              alt=""
              width="1920"
              height="1080"
              decoding="async"
              loading="lazy"
              class="block h-auto w-full object-contain object-center"
            />
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/10 via-black/20 to-black/55 lg:from-black/0 lg:via-black/10 lg:to-black/45"></div>
            <div class="absolute inset-0 z-10 flex items-center justify-center px-4 py-10 sm:py-12 md:py-16 lg:px-8">
            <div class="pointer-events-auto mx-auto w-full max-w-2xl rounded-3xl border border-gold/35 bg-nearblack/85 p-6 shadow-[0_0_48px_rgba(0,0,0,0.55)] backdrop-blur-md sm:p-7 md:p-8">
            <h3 class="text-center text-4xl font-semibold leading-tight text-offwhite">Напишите <span class="text-gold">нам</span></h3>
            <p class="mt-2 text-center text-sm text-offwhite/70">Оставьте заявку, и наш специалист ответит вам в течение 15 минут</p>
            <form id="lead-form-el" class="mt-7 space-y-4">
              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <input name="name" type="text" required placeholder="Ваше имя" class="w-full rounded-full border border-gold/45 bg-nearblack/80 px-5 py-3 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
                <input name="phone" type="tel" required placeholder="Номер телефона" class="w-full rounded-full border border-gold/45 bg-nearblack/80 px-5 py-3 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
              </div>
              <input name="comment" type="text" placeholder="Опишите ваш проект или вопрос" class="w-full rounded-2xl border border-gold/45 bg-nearblack/80 px-5 py-4 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
              <button type="submit" class="w-full rounded-full bg-gold py-3 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:opacity-95">
                Оставить заявку
              </button>
            </form>
            <div id="form-success" class="mt-6 hidden rounded-2xl border border-gold/50 bg-charcoal/95 p-6 text-center shadow-gold" role="status">
              <p class="text-lg font-semibold text-gold">Заявка отправлена</p>
              <p class="mt-2 text-sm text-muted">Спасибо! Мы свяжемся с вами в ближайшее время.</p>
            </div>
            </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    @if($page === 'about')
    <section class="bg-black pb-14 pt-20 text-offwhite md:pb-20 md:pt-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <nav class="pb-5 text-xs text-offwhite/60">
          <a href="/" class="transition hover:text-offwhite">Главная</a>
          <span class="px-2 text-offwhite/35">/</span>
          <span class="text-offwhite/90">О нас</span>
        </nav>
      </div>

      <article class="px-4 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl overflow-hidden rounded-[14px] sm:rounded-[18px]">
          <div class="relative">
            <img
              src="{{ asset('storage/img/about/about-hero.png') }}"
              alt="Real Brick"
              class="h-[240px] w-full object-cover object-center sm:h-[310px] md:h-[370px] xl:h-[420px]"
              loading="eager"
              decoding="async"
            />
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-transparent"></div>
            <div class="absolute inset-y-0 left-0 flex items-center px-6 sm:px-10 lg:px-16">
              <div>
                <h1 class="text-4xl font-semibold tracking-tight text-gold sm:text-5xl">Real Brick</h1>
                <p class="mt-2 max-w-[430px] text-sm leading-snug text-offwhite/75 sm:text-base">
                  Более 15 лет мы создаем кирпич, который стирает<br>
                  границы между архитектурой и искусством.
                </p>
              </div>
            </div>
          </div>
        </div>
      </article>

      <div class="mx-auto mt-8 max-w-6xl px-4 sm:px-6 lg:px-8">
        <section class="mt-8 grid gap-5 lg:grid-cols-[1fr_0.92fr]">
          <div class="bg-black p-5 sm:p-7">
            <h2 class="text-2xl font-semibold text-offwhite">Искусство <span class="text-gold">быть настоящим</span></h2>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              Компания Real Brick была основана как ремесленная мастерская, где каждый кирпич производился вручную.
              Мы сохранили этот подход и в современном производстве — фактура, оттенок и характер каждого элемента
              остаются уникальными.
            </p>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              Сегодня продукция Real Brick используется в частной и коммерческой архитектуре: от уютных интерьеров
              до крупных фасадных решений. Мы объединяем традиции ручной формовки и строгие стандарты качества.
            </p>
            <blockquote class="mt-5 border-l border-gold/40 pl-4 text-[1rem] leading-snug text-offwhite/85">
              «Кирпич ручной формовки — это не просто стройматериал. Это история, застывшая в камне.»
            </blockquote>
          </div>
          <figure class="overflow-hidden rounded-2xl">
            <img
              src="{{ asset('storage/img/about/about-bookshelf.png') }}"
              alt="Коллекция кирпича Real Brick"
              class="h-full w-full object-cover"
              loading="lazy"
              decoding="async"
            />
          </figure>
        </section>
      </div>

      <section class="mt-7 bg-black px-4 sm:px-6 lg:px-8">
        <div class="mx-auto grid w-full max-w-5xl grid-cols-2 gap-4 border-y border-gold/40 px-4 py-5 sm:grid-cols-4 sm:px-6 sm:py-6">
          @foreach($aboutStats as $stat)
            <div class="text-center">
              <p class="text-3xl font-bold text-gold sm:text-4xl">{{ $stat['value'] }}</p>
              <p class="mt-2 text-xs uppercase tracking-[0.1em] text-offwhite/70">{{ $stat['label'] }}</p>
            </div>
          @endforeach
        </div>
      </section>

      <div class="mx-auto mt-8 max-w-6xl px-4 sm:px-6 lg:px-8">
        <section class="mt-8 grid gap-5 md:grid-cols-2 md:items-center">
          <figure class="overflow-hidden rounded-2xl">
            <img
              src="{{ asset('storage/img/about/about-candles.png') }}"
              alt="Техническое превосходство Real Brick"
              class="h-full w-full object-cover"
              loading="lazy"
              decoding="async"
            />
          </figure>
          <div class="rounded-2xl bg-black p-5 sm:p-7">
            <h3 class="text-2xl font-semibold text-offwhite">Технологическое <span class="text-gold">превосходство</span></h3>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              На каждом этапе производства мы контролируем геометрию, водопоглощение и прочность изделий.
              Это обеспечивает стабильное качество и долговечность в любых климатических условиях.
            </p>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              Real Brick — это ручное мастерство и современные технологии в одной системе качества.
            </p>
          </div>
        </section>

        <section class="mt-8 grid gap-5 md:grid-cols-2 md:items-center">
          <div class="order-2 rounded-2xl bg-black p-5 sm:p-7 md:order-1">
            <h3 class="text-2xl font-semibold text-offwhite">Природная <span class="text-gold">чистота</span></h3>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              Мы используем натуральные минеральные компоненты и устойчивые производственные процессы,
              чтобы материал оставался безопасным для жилых и общественных пространств.
            </p>
            <p class="mt-4 text-sm leading-relaxed text-offwhite/80">
              Каждый кирпич сохраняет природную эстетику и тактильность, которая делает архитектуру живой.
            </p>
          </div>
          <figure class="order-1 overflow-hidden rounded-2xl md:order-2">
            <img
              src="{{ asset('storage/img/about/about-natural.png') }}"
              alt="Природная чистота материалов Real Brick"
              class="h-full w-full object-cover"
              loading="lazy"
              decoding="async"
            />
          </figure>
        </section>

        <section class="mt-8 rounded-2xl border border-gold/40 bg-black px-5 py-7 sm:px-8 sm:py-8">
          <div class="grid gap-6 md:grid-cols-[1fr_auto] md:items-end">
            <div>
              <h3 class="text-2xl font-semibold text-offwhite">Сотрудничество</h3>
              <p class="mt-3 max-w-2xl text-sm leading-relaxed text-offwhite/80">
                Мы открыты к сотрудничеству с архитекторами, дизайнерами и строительными компаниями.
                Получите консультацию и подбор материалов под ваш проект.
              </p>
              <a
                href="/contacts"
                class="mt-6 inline-flex min-h-[44px] items-center justify-center rounded-full bg-gold px-7 py-2.5 text-xs font-bold uppercase tracking-wider text-nearblack transition hover:opacity-95"
              >
                получить консультацию
              </a>
            </div>
            <img
              src="{{ asset('storage/img/about/about-cooperation.png') }}"
              alt="Сотрудничество с Real Brick"
              class="mx-auto w-full max-w-[220px] object-contain md:mx-0 md:max-w-[260px]"
              loading="lazy"
              decoding="async"
            />
          </div>
        </section>
      </div>
    </section>
    @endif

    <!-- Hero: макет — крупный REAL BRICK (под кирпичом по z), кирпич по центру-правее, справа текст+карточки поверх -->
    @if($page === 'home')
    <section id="hero" class="relative flex min-h-screen flex-col overflow-hidden pt-24 pb-10 md:pt-28 md:pb-12">
      <div class="absolute inset-0 z-0">
        <img src="{{ asset('storage/assets/hero-bg-house.png') }}" alt="" class="hero-bg-house h-full min-h-full w-full scale-110 object-cover object-center opacity-[0.38]" width="1920" height="1080" decoding="async" fetchpriority="high" />
        <div class="pointer-events-none absolute inset-0 bg-nearblack/82"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-nearblack/40 via-nearblack/50 to-nearblack/95"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_90%_65%_at_58%_62%,rgba(201,169,110,0.12)_0%,transparent_55%)]"></div>
      </div>

      <!-- Мобильная колонка -->
      <div class="relative z-10 mx-auto w-full max-w-7xl flex-1 px-4 md:px-6 lg:hidden">
        <div class="reveal space-y-5 pt-2">
          <h1 class="hero-brand-title text-center">REAL BRICK</h1>
          <p class="mx-auto max-w-[300px] text-center text-sm leading-relaxed text-offwhite/90">
            Каждый кирпич формируется вручную. Поэтому фактура никогда не повторяется.
          </p>
          <p class="hero-left-kicker text-center">
            1 в мире минеральный кирпич ручной формовки
          </p>
          <p class="hero-left-subline mx-auto text-center">
            для архитектуры и интерьеров
          </p>
          <div class="relative mx-auto flex max-w-[400px] justify-center py-4">
            <div class="pointer-events-none absolute left-1/2 top-1/2 h-[min(65vw,380px)] w-[min(95vw,480px)] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold/18 blur-[88px]"></div>
            <div class="hero-brick-levitate relative z-10 w-[min(88%,380px)]">
              <img src="{{ asset('storage/assets/hero-brick-float.png') }}" alt="" class="hero-brick-float-img h-auto w-full object-contain" width="800" height="800" decoding="async" />
            </div>
          </div>
          <div class="flex flex-col items-center gap-3 pb-4">
            <a href="/catalog" data-scroll class="inline-flex h-12 w-full max-w-[275px] items-center justify-center rounded-full bg-[#C5A365] px-7 text-[0.82rem] font-bold lowercase tracking-wide text-nearblack shadow-[0_0_28px_rgba(197,163,101,0.4)] transition hover:scale-[1.02] active:scale-[0.98]">смотреть коллекцию</a>
            <a href="/catalog" class="inline-flex h-11 w-full max-w-[275px] items-center justify-center rounded-full border border-gold/70 text-[0.8rem] font-medium lowercase text-offwhite/90 transition hover:border-gold hover:text-gold">скачать каталог</a>
          </div>
          <div class="mx-auto flex w-full max-w-[280px] flex-col gap-3 pb-6">
            <div class="rounded-2xl border border-white/20 bg-black/55 px-5 py-4 backdrop-blur-sm"><p class="text-[2rem] font-bold text-white">6%</p><p class="mt-1 text-[0.82rem] lowercase text-white/90">водопоглощение</p></div>
            <div class="rounded-2xl border border-white/20 bg-black/55 px-5 py-4 backdrop-blur-sm"><p class="text-[2rem] font-bold text-white">M250</p><p class="mt-1 text-[0.82rem] lowercase text-white/90">марка прочности</p></div>
            <div class="rounded-2xl border border-white/20 bg-black/55 px-5 py-4 backdrop-blur-sm"><p class="text-[2rem] font-bold text-white">F500</p><p class="mt-1 text-[0.82rem] lowercase text-white/90">морозостойкость</p></div>
          </div>
        </div>
      </div>

      <!-- Десктоп: абсолютная композиция (isolate + z) — стабильнее сетки с row-span -->
      <div class="relative z-10 mx-auto hidden w-full max-w-7xl flex-1 px-6 lg:block xl:px-8">
        <div class="hero-desktop-canvas reveal relative isolate w-full pt-1">
          <!-- Заголовок: под кирпичом по глубине -->
          <h1 class="hero-brand-title pointer-events-none absolute left-0 top-0 z-[6] max-w-[92%] pl-[0.5%] xl:max-w-[88%]">REAL BRICK</h1>

          <!-- Подзаголовки сразу под логотипом-текстом, поверх фона и кирпича -->
          <div class="absolute left-0 top-[clamp(9.75rem,20vw,14.5rem)] z-30 w-[min(100%,28rem)] pl-[0.5%] xl:top-[clamp(11rem,18vw,15rem)]">
            <p class="hero-left-kicker !max-w-none">
              1 в мире минеральный кирпич ручной формовки
            </p>
            <p class="hero-left-subline mt-3 !max-w-none">
              для архитектуры и интерьеров
            </p>
          </div>

          <!-- Верхний абзац справа -->
          <p class="absolute right-0 top-2 z-40 max-w-[17.5rem] text-right text-[0.9375rem] leading-relaxed text-offwhite/95 xl:max-w-[18.5rem]">
            Каждый кирпич формируется вручную. Поэтому фактура никогда не повторяется.
          </p>

          <!-- Кирпич + золотое пятно: центр-правее, снизу -->
          <div class="pointer-events-none absolute inset-x-0 bottom-0 z-[14] flex justify-center pb-1 md:pb-3">
            <div class="relative w-[min(88vw,620px)] max-w-[620px] translate-x-[5%] xl:w-[min(78vw,640px)] xl:max-w-[640px]">
              <div class="absolute bottom-[12%] left-1/2 z-0 h-[min(48vh,440px)] w-[110%] max-w-[720px] -translate-x-1/2 rounded-full bg-gold/25 blur-[100px] xl:blur-[115px]"></div>
              <div class="hero-brick-levitate relative z-10 mx-auto w-[88%] max-w-[560px] xl:w-[90%] xl:max-w-[600px]">
                <img src="{{ asset('storage/assets/hero-brick-float.png') }}" alt="Облицовочный кирпич Real Brick" class="hero-brick-float-img h-auto w-full object-contain object-bottom" width="800" height="800" decoding="async" />
              </div>
            </div>
          </div>

          <!-- Кнопки: низ слева -->
          <div class="absolute bottom-[clamp(2.5rem,7vh,4.5rem)] left-0 z-40 flex w-[min(100%,17rem)] flex-col gap-2.5 pl-[0.5%]">
            <a href="/catalog" data-scroll class="inline-flex h-12 w-full items-center justify-center rounded-full bg-[#C5A365] px-6 text-[0.82rem] font-bold lowercase tracking-wide text-nearblack shadow-[0_0_28px_rgba(197,163,101,0.45)] transition hover:scale-[1.02] active:scale-[0.98]">
              смотреть коллекцию
            </a>
            <a href="/catalog" class="inline-flex h-10 w-full items-center justify-center rounded-full border border-gold/60 px-4 text-[0.78rem] font-medium lowercase text-offwhite/85 transition hover:border-gold hover:text-gold">
              скачать каталог
            </a>
          </div>

          <!-- Карточки: низ справа -->
          <div class="absolute bottom-[clamp(2.5rem,7vh,4.5rem)] right-0 z-40 flex w-[min(100%,15.5rem)] flex-col gap-2.5 xl:w-[16.5rem]">
            <div class="rounded-2xl border border-white/20 bg-black/65 px-4 py-3.5 shadow-[0_8px_28px_rgba(0,0,0,0.45)] backdrop-blur-md">
              <p class="text-[1.75rem] font-bold leading-none text-white xl:text-[2rem]">6%</p>
              <p class="mt-1.5 text-[0.8rem] font-medium lowercase text-white/90">водопоглощение</p>
            </div>
            <div class="rounded-2xl border border-white/20 bg-black/65 px-4 py-3.5 shadow-[0_8px_28px_rgba(0,0,0,0.45)] backdrop-blur-md">
              <p class="text-[1.75rem] font-bold leading-none text-white xl:text-[2rem]">M250</p>
              <p class="mt-1.5 text-[0.8rem] font-medium lowercase text-white/90">марка прочности</p>
            </div>
            <div class="rounded-2xl border border-white/20 bg-black/65 px-4 py-3.5 shadow-[0_8px_28px_rgba(0,0,0,0.45)] backdrop-blur-md">
              <p class="text-[1.75rem] font-bold leading-none text-white xl:text-[2rem]">F500</p>
              <p class="mt-1.5 text-[0.8rem] font-medium lowercase text-white/90">морозостойкость</p>
            </div>
          </div>
        </div>
      </div>

      <a href="/about" data-scroll class="absolute bottom-6 right-5 z-40 flex h-11 w-11 items-center justify-center rounded-full border border-gold/50 bg-nearblack/40 text-gold shadow-gold-sm backdrop-blur-sm transition hover:border-gold hover:bg-gold/15 md:bottom-10 md:right-8" aria-label="Вниз">
        <svg class="h-5 w-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
      </a>
    </section>
    @endif

    <!-- Usage -->
    @if($page === 'home')
    <section id="usage" class="py-10 md:py-16 bg-[rgb(3,3,3)]">
      <div class="reveal mx-auto max-w-7xl px-4 lg:px-8">
        <div class="relative mt-8 pb-12">
          <div class="grid gap-6 md:grid-cols-3">
            <article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-charcoal shadow-xl">
              <div class="aspect-[4/5] overflow-hidden">
                <img src="{{ asset('storage/assets/usage-3.png') }}" alt="Жилые дома" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04] group-hover:brightness-110" loading="lazy" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-nearblack via-nearblack/55 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-bold text-offwhite">Жилые дома</h3>
                <p class="mt-1 text-xs uppercase tracking-wide text-muted">фасады и отделка</p>
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-gold">
                  Смотреть проекты <span aria-hidden="true">→</span>
                </p>
              </div>
            </article>

            <article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-charcoal shadow-xl">
              <div class="aspect-[4/5] overflow-hidden">
                <img src="{{ asset('storage/assets/usage-2.png') }}" alt="Интерьеры" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04] group-hover:brightness-110" loading="lazy" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-nearblack via-nearblack/55 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-bold text-offwhite">Интерьеры</h3>
                <p class="mt-1 text-xs uppercase tracking-wide text-muted">стены и декор</p>
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-gold">
                  Смотреть проекты <span aria-hidden="true">→</span>
                </p>
              </div>
            </article>

            <article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-charcoal shadow-xl">
              <div class="aspect-[4/5] overflow-hidden">
                <img src="{{ asset('storage/assets/usage-1.png') }}" alt="Фасады" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04] group-hover:brightness-110" loading="lazy" />
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-nearblack via-nearblack/55 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-bold text-offwhite">Фасады</h3>
                <p class="mt-1 text-xs uppercase tracking-wide text-muted">архитектура</p>
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-gold">
                  Смотреть проекты <span aria-hidden="true">→</span>
                </p>
              </div>
            </article>
          </div>

          <!-- Визуальные точки (как индикатор карусели) -->
          <div class="pointer-events-none absolute bottom-3 left-0 right-0 flex justify-center gap-3">
            <div class="usage-dot is-active" aria-hidden="true"></div>
            <div class="usage-dot" aria-hidden="true"></div>
            <div class="usage-dot" aria-hidden="true"></div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Collections -->
    @if($page === 'home' || $page === 'catalog')
    <section id="catalog" class="bg-[rgb(3,3,3)] py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="reveal flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <h2 class="text-3xl font-bold text-gold md:text-4xl">Коллекции Real Brick</h2>
            <p class="mt-2 max-w-xl text-muted">Текстуры и оттенки для разных архитектурных задач.</p>
          </div>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Фламандский","desc":"Глубокая фактура и насыщенный тон — характер европейской классики для монументальных фасадов."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-1.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Фламандский</h3>
                <p class="mt-1 text-sm text-muted">Классическая фактура</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Баварский","desc":"Тёплые оттенки и мягкий рельеф — уют и солидность в частной застройке."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-2.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Баварский</h3>
                <p class="mt-1 text-sm text-muted">Тёплые тона</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Бельгийский","desc":"Ровная геометрия и благородный матовый поверхностный рисунок."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-3.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Бельгийский</h3>
                <p class="mt-1 text-sm text-muted">Сдержанная элегантность</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Голландский","desc":"Узнаваемый формат и чёткий ритм кладки для современных проектов."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-4.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Голландский</h3>
                <p class="mt-1 text-sm text-muted">Чёткий ритм</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Английский","desc":"Плотная керамика и благородный патинаж со временем."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-5.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Английский</h3>
                <p class="mt-1 text-sm text-muted">Плотная керамика</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Средиземноморский","desc":"Солнечные оттенки и лёгкая шероховатость для курортной архитектуры."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-6.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Средиземноморский</h3>
                <p class="mt-1 text-sm text-muted">Солнечный характер</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Скандинавский","desc":"Спокойные нейтральные тона и ровная поверхность."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-7.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Скандинавский</h3>
                <p class="mt-1 text-sm text-muted">Нейтральная палитра</p>
              </div>
            </div>
          </button>
          <button type="button" class="collection-card reveal group text-left" data-collection='{"title":"Loft Industrial","desc":"Грубая текстура и контраст для городских интерьеров и студий."}'>
            <div class="overflow-hidden rounded-2xl border border-white/10 bg-charcoal shadow-lg transition duration-300 group-hover:border-gold/50 group-hover:shadow-gold">
              <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/assets/collection-8.png') }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              </div>
              <div class="p-4">
                <h3 class="font-bold text-offwhite">Loft Industrial</h3>
                <p class="mt-1 text-sm text-muted">Городской характер</p>
              </div>
            </div>
          </button>
        </div>
      </div>
    </section>
    @endif

    <!-- Projects -->
    @if($page === 'home' || $page === 'gallery')
    <section id="projects" class="relative overflow-hidden py-16 md:py-24">
      <div class="absolute inset-0 z-0">
        <img src="{{ asset('storage/assets/projects-bg.png') }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async" />
      </div>
      <div class="relative z-10 mx-auto max-w-7xl px-4 lg:px-8">
        <div class="reveal grid gap-10 lg:grid-cols-2 lg:items-start lg:gap-16">
          <div>
            <h2 class="text-3xl font-bold leading-tight text-offwhite md:text-4xl lg:text-5xl">
              Реализованные проекты <span class="text-gold">Real Brick</span>
            </h2>

            <p class="mt-5 max-w-xl text-muted leading-relaxed">
              Квартиры, рестораны, коммерческие фасады.
              <br />
              Кирпич для проектов со вкусом и стилем
            </p>

            <div class="mt-9">
              <div class="flex items-end gap-4">
                <p class="text-6xl font-bold text-gold md:text-7xl">500+</p>
              </div>
              <p class="mt-2 text-lg text-offwhite/90">реализованных проектов</p>
            </div>

            <div class="mt-6 flex items-center gap-4">
              <img
                src="{{ asset('storage/assets/projects-kz-icon.png') }}"
                alt="Используется в проектах по всему Казахстану"
                class="h-11 w-11 rounded-full shadow-[0_0_28px_rgba(0,163,217,0.35)]"
                loading="lazy"
                decoding="async"
              />
              <p class="text-sm text-muted leading-relaxed">
                Используется в проектах
                <br />
                по всему Казахстану
              </p>
            </div>

            <a
              href="{{ route('projects.index') }}"
              class="mt-10 inline-flex items-center gap-2 text-sm font-semibold text-gold transition hover:opacity-90"
            >
              смотреть портфолио <span aria-hidden="true">→</span>
            </a>
          </div>

          <div class="rounded-3xl border border-gold/40 bg-transparent p-8 shadow-[0_0_32px_rgba(201,169,110,0.08)] md:p-10">
            <h3 class="text-2xl font-bold leading-tight text-gold md:text-3xl">
              Попробуйте
              <br />
              Real Brick в
              <br />
              вашем проекте
            </h3>
            <p class="mt-4 max-w-md text-muted leading-relaxed">
              Получите 3D-визуализацию
              <br />
              или расчет материалов для
              <br />
              проекта прямо сейчас.
            </p>

            <div class="mt-7 space-y-3">
              <a
                href="/contacts"
                data-scroll
                class="inline-flex w-full items-center justify-center rounded-xl bg-gold px-6 py-4 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:scale-[1.02] hover:shadow-[0_0_28px_rgba(201,169,110,0.45)] active:scale-[0.98] md:w-auto"
              >
                Получить 3D-визуализацию
              </a>
              <a
                href="{{ route('calculator') }}"
                class="inline-flex w-full items-center justify-center rounded-xl border border-gold/50 bg-transparent px-6 py-4 text-sm font-semibold uppercase tracking-wider text-gold transition hover:bg-gold/10 hover:border-gold active:scale-[0.98] md:w-auto"
              >
                Рассчитать материалы
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    @if($page === 'calculator')
    <section class="bg-black pb-16 pt-24 md:pb-20 md:pt-28">
      <div class="mx-auto max-w-5xl px-4 lg:px-8">
        <div class="mb-6 text-xs text-offwhite/65">
          <a href="/" class="hover:text-offwhite">Главная</a><span class="px-1.5">/</span><span>Калькулятор</span>
        </div>

        <div class="rounded-[28px] border border-gold/40 bg-[radial-gradient(circle_at_center,rgba(201,169,110,0.10)_0%,rgba(8,8,8,0.96)_58%)] p-4 shadow-[0_20px_60px_rgba(0,0,0,0.55)] md:p-8">
          <div class="grid gap-4 md:grid-cols-[1.35fr_0.95fr] md:gap-6">
            <div class="rounded-3xl border border-white/10 bg-black/45 p-5 md:p-7">
              <h1 class="text-3xl font-semibold leading-none text-gold md:text-[2.2rem]">Калькулятор</h1>
              <p class="mt-4 text-xs uppercase tracking-wide text-offwhite/70">тип помещения / конструктив</p>

              <label class="mt-2 block">
                <select id="calc-room-type" class="h-11 w-full rounded-full border border-white/10 bg-[#1d1d1f] px-4 text-sm text-offwhite outline-none transition focus:border-gold/70">
                  <option value="walls" selected>Фасады / Стены (м²)</option>
                  <option value="floor">Пол (м²)</option>
                </select>
              </label>

              <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <label class="block">
                  <span class="mb-1.5 block text-xs text-offwhite/80">Длина (м)</span>
                  <input id="calc-length" type="number" min="0.1" step="0.1" value="10" class="h-11 w-full rounded-full border border-white/10 bg-[#1d1d1f] px-4 text-sm text-offwhite outline-none transition focus:border-gold/70" />
                </label>
                <label class="block">
                  <span class="mb-1.5 block text-xs text-offwhite/80">Ширина (м)</span>
                  <input id="calc-width" type="number" min="0.1" step="0.1" value="10" class="h-11 w-full rounded-full border border-white/10 bg-[#1d1d1f] px-4 text-sm text-offwhite outline-none transition focus:border-gold/70" />
                </label>
                <label class="block">
                  <span class="mb-1.5 block text-xs text-offwhite/80">Высота (м)</span>
                  <input id="calc-height" type="number" min="0.1" step="0.1" value="3" class="h-11 w-full rounded-full border border-white/10 bg-[#1d1d1f] px-4 text-sm text-offwhite outline-none transition focus:border-gold/70" />
                </label>
              </div>

              <p class="mt-4 text-xs text-offwhite/80">Выберите материал</p>
              <div class="mt-2" id="calc-material-picker">
                <button type="button" id="calc-material-trigger" class="flex h-11 w-full items-center justify-between rounded-full border border-white/10 bg-[#1d1d1f] px-4 text-left text-sm text-offwhite outline-none transition hover:border-gold/45 focus:border-gold/70">
                  <span id="calc-material-selected-label" class="truncate">Выберите товар из каталога</span>
                  <span class="ml-3 text-offwhite/60">⌄</span>
                </button>
                <div id="calc-material-panel" class="absolute z-50 mt-2 hidden w-[min(680px,92vw)] overflow-hidden rounded-2xl border border-gold/30 bg-[#111113] shadow-[0_16px_40px_rgba(0,0,0,0.45)]">
                  <div class="border-b border-white/10 p-3">
                    <input id="calc-product-search" type="text" placeholder="Поиск товара..." class="h-10 w-full rounded-xl border border-white/10 bg-[#1d1d1f] px-3 text-sm text-offwhite placeholder:text-offwhite/40 outline-none transition focus:border-gold/70" />
                  </div>
                  <div id="calc-material-tree" class="max-h-80 overflow-y-auto p-2"></div>
                </div>
                <select id="calc-material" class="hidden"></select>
              </div>
            </div>

            <aside class="rounded-3xl border border-gold/35 bg-black/55 p-5 md:p-7">
              <p class="text-xs font-semibold uppercase tracking-[0.03em] text-offwhite">ИТОГО МАТЕРИАЛОВ</p>
              <div class="mt-1 flex items-end gap-2">
                <span id="calc-total-pieces" class="text-5xl font-bold leading-none text-gold md:text-6xl">6240</span>
                <span class="pb-1 text-sm text-offwhite/90">шт/м²</span>
              </div>
              <p class="mt-2 text-sm text-offwhite/80">Ориентировочная стоимость</p>
              <p id="calc-total-price" class="mt-1 text-2xl font-semibold text-gold">2 496 000 ₸</p>

              <div class="mt-5 space-y-2 border-t border-white/15 pt-4 text-sm">
                <div class="flex items-center justify-between gap-3"><span class="text-offwhite/85">Общая площадь</span><strong id="calc-total-area" class="text-right font-medium text-offwhite">120.0 м²</strong></div>
                <div class="flex items-center justify-between gap-3"><span class="text-offwhite/85">Площадь c запасом</span><strong id="calc-total-area-extra" class="text-right font-medium text-offwhite">126.0 м²</strong></div>
                <div class="flex items-center justify-between gap-3"><span class="text-offwhite/85">Рекомендуемый раствор</span><strong id="calc-mix" class="text-right font-medium text-offwhite">24 мест (мешков)</strong></div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </section>
    <script id="calc-tree-data" type="application/json">@json(['materials' => $calculatorMaterials ?? [], 'sections' => $calculatorSections ?? []])</script>
    @endif

    <!-- Benefits -->
    @if($page === 'home')
    <section id="benefits" class="relative overflow-hidden py-16 md:py-24">
      <div class="pointer-events-none absolute -left-28 top-1/2 h-[320px] w-[320px] -translate-y-1/2 rounded-full bg-white/18 blur-[110px]" aria-hidden="true"></div>
      <div class="pointer-events-none absolute left-1/2 top-1/2 h-[min(90vw,520px)] w-[min(90vw,520px)] -translate-x-1/2 -translate-y-1/2 rounded-full border border-gold/10 opacity-30" aria-hidden="true"></div>
      <div class="pointer-events-none absolute left-1/2 top-1/2 flex h-64 w-64 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-gold/20 text-[10rem] font-bold text-gold/5" aria-hidden="true">RB</div>
      <div class="pointer-events-none absolute -right-16 top-1/2 z-0 hidden w-[320px] -translate-y-1/2 lg:block xl:w-[420px]">
        <img src="{{ asset('storage/assets/benefits-seal.png') }}" alt="" class="h-auto w-full opacity-45 brightness-110 contrast-125" loading="lazy" decoding="async" />
      </div>
      <div class="relative mx-auto max-w-7xl px-4 lg:px-8">
        <div class="reveal grid gap-12 lg:grid-cols-2 lg:gap-16">
          <div>
            <h2 class="text-3xl font-bold text-gold md:text-4xl">Почему выбирают Real Brick</h2>
            <p class="mt-6 text-muted leading-relaxed">
              <span class="font-semibold text-gold">Real Brick</span> — кирпич ручной формовки с уникальной фактурой и характером. Мы поставляем материалы для архитектуры и интерьеров, где важны стиль, качество и долговечность.
              <br>
              <br>
              С 2007 года продукция Real Brick используется в проектах, где ценят <span class="font-semibold text-gold">натуральные материалы и выразительную фактуру</span>.
            </p>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-white/10 bg-black p-6 shadow-lg transition duration-300 hover:border-gold/40 hover:shadow-gold-sm">
              <h3 class="font-bold text-gold">Ручная формовка</h3>
              <p class="mt-2 text-sm text-muted">Живая текстура и контроль каждой партии.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black p-6 shadow-lg transition duration-300 hover:border-gold/40 hover:shadow-gold-sm">
              <h3 class="font-bold text-gold">Высокое качество</h3>
              <p class="mt-2 text-sm text-muted">Стандарты прочности и геометрии.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black p-6 shadow-lg transition duration-300 hover:border-gold/40 hover:shadow-gold-sm">
              <h3 class="font-bold text-gold">Экологичность</h3>
              <p class="mt-2 text-sm text-muted">Натуральная керамика без компромиссов.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black p-6 shadow-lg transition duration-300 hover:border-gold/40 hover:shadow-gold-sm">
              <h3 class="font-bold text-gold">Морозостойкость</h3>
              <p class="mt-2 text-sm text-muted">Стойкость к циклам замерзания и оттаивания.</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-black p-6 shadow-lg transition duration-300 hover:border-gold/40 hover:shadow-gold-sm sm:col-span-2">
              <h3 class="font-bold text-gold">Долговечность</h3>
              <p class="mt-2 text-sm text-muted">Сохранение внешнего вида и свойств на годы эксплуатации.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Blog -->
    @if($page === 'home')
    <section id="blog" class="bg-gold py-16 md:py-20">
      <div class="reveal mx-auto max-w-3xl px-4 text-center lg:px-8">
        <h2 class="text-3xl font-bold text-nearblack md:text-4xl">Блог Real Brick</h2>
        <p class="mx-auto mt-4 text-nearblack/80 leading-relaxed">
          Идеи, советы и вдохновение для архитектуры и интерьеров из кирпича ручной формовки.
        </p>
        <a href="/blog" class="mt-8 inline-flex items-center justify-center rounded-full bg-nearblack px-10 py-3.5 text-sm font-semibold lowercase tracking-wider text-gold transition hover:scale-105 hover:shadow-lg">
          Читать блог
        </a>
      </div>
    </section>
    @endif

    @if($page === 'blog')
    <section id="blog" class="bg-[rgb(6,6,6)] py-24 md:py-28">
      <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-xs text-offwhite/65">
          <a href="/" class="hover:text-offwhite">Главная</a><span class="px-1.5">/</span><span>Блог</span>
        </div>

        @php
          $featured = $featuredPost ?? null;
          $posts = $blogPosts ?? collect();
          $topics = $blogTopics ?? [];
          $active = $activeTopic ?? '';
          $cards = $featured ? $posts->where('id', '!=', $featured->id)->values() : $posts;
        @endphp

        <article class="group relative mt-6 overflow-hidden rounded-3xl border border-white/10 bg-charcoal/45">
          <div class="relative h-[290px] w-full md:h-[360px]">
            <div class="absolute inset-0 flex items-center justify-center bg-[linear-gradient(180deg,#202020_0%,#141414_100%)]">
              <span class="rounded-full border border-white/20 bg-black/35 px-5 py-2 text-sm font-medium text-offwhite/75">Изображение отсутствует</span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/45"></div>
          </div>
          <div class="absolute inset-x-0 bottom-0 p-6 md:p-8">
            <span class="inline-flex rounded-full border border-white/30 bg-white/15 px-4 py-1 text-[11px] uppercase tracking-[0.12em] text-offwhite">{{ $featured?->topic ?? 'Блог' }}</span>
            <h1 class="mt-3 max-w-2xl text-2xl font-semibold leading-tight text-offwhite md:text-4xl">{{ $featured?->title ?? 'Заголовок статьи' }}</h1>
            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-offwhite/70 md:text-sm">
              <span>{{ $featured?->published_at?->translatedFormat('j F, Y') ?? '' }}</span>
              <a href="{{ $featured ? route('blog.show', ['slug' => $featured->slug]) : '#' }}" class="inline-flex items-center gap-2 font-medium text-gold transition hover:text-gold/80">Читать статью <span aria-hidden="true">→</span></a>
            </div>
          </div>
        </article>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
          <a href="{{ route('blog.index') }}" class="{{ $active === '' ? 'border-gold/90 bg-gold text-nearblack' : 'border-white/45 text-offwhite/90 hover:border-gold/70 hover:text-gold' }} rounded-full border px-6 py-2.5 text-sm transition">Все темы</a>
          @foreach($topics as $topic)
            <a
              href="{{ route('blog.index', ['topic' => $topic]) }}"
              class="{{ $active === $topic ? 'border-gold/90 bg-gold text-nearblack' : 'border-white/45 text-offwhite/90 hover:border-gold/70 hover:text-gold' }} rounded-full border px-6 py-2.5 text-sm transition"
            >{{ $topic }}</a>
          @endforeach
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
          @forelse($cards as $post)
            <article class="group overflow-hidden rounded-3xl border border-white/10 bg-charcoal/55 transition hover:border-gold/45">
              <div class="flex h-44 items-center justify-center bg-[linear-gradient(180deg,#202020_0%,#141414_100%)]">
                <span class="rounded-full border border-white/20 bg-black/35 px-4 py-1.5 text-xs text-offwhite/75">Изображение отсутствует</span>
              </div>
              <div class="p-5">
                <h3 class="text-xl font-semibold leading-tight text-offwhite">{{ $post->title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-offwhite/65">{{ $post->excerpt }}</p>
                <div class="mt-4 flex items-center justify-between text-xs text-offwhite/35">
                  <span>{{ $post->topic }}</span>
                  <a href="{{ route('blog.show', ['slug' => $post->slug]) }}" class="inline-flex items-center gap-2 text-sm text-gold transition hover:text-gold/80">читать статью <span aria-hidden="true">→</span></a>
                </div>
              </div>
            </article>
          @empty
            <div class="col-span-full rounded-3xl border border-white/10 bg-charcoal/40 p-8 text-center text-offwhite/70">
              Пока нет опубликованных статей.
            </div>
          @endforelse
        </div>

        @if(method_exists($posts, 'lastPage') && $posts->lastPage() > 1)
          <div class="mt-10 flex items-center justify-center gap-2.5">
            @for($i = 1; $i <= $posts->lastPage(); $i++)
              <a
                href="{{ $posts->url($i) }}"
                aria-label="Страница {{ $i }}"
                class="{{ $posts->currentPage() === $i ? 'border-gold/90 bg-gold text-nearblack' : 'border-white/35 bg-transparent text-offwhite/90 hover:border-gold/60' }} flex h-8 w-8 items-center justify-center rounded-full border text-xs transition"
              >{{ $i }}</a>
            @endfor
            @if($posts->hasMorePages())
              <a href="{{ $posts->nextPageUrl() }}" class="flex h-8 w-8 items-center justify-center rounded-full border border-white/35 text-offwhite/90 transition hover:border-gold/60">→</a>
            @endif
          </div>
        @endif

        <section class="mt-12 rounded-3xl border border-white/10 bg-[linear-gradient(180deg,#101010_0%,#0a0a0a_100%)] px-6 py-10 text-center md:px-10">
          <h2 class="text-2xl font-semibold text-offwhite">Следите за новыми статьями</h2>
          <p class="mx-auto mt-3 max-w-2xl text-sm text-offwhite/65">Подпишитесь на нашу рассылку, чтобы получать уведомления о новых коллекциях, дизайнерских советах и реализованных проектах.</p>
          <form class="mx-auto mt-6 flex max-w-xl flex-col gap-3 sm:flex-row">
            <input type="email" placeholder="Ваш email" class="w-full rounded-full border border-white/15 bg-black/65 px-5 py-3 text-sm text-offwhite placeholder:text-offwhite/35 outline-none transition focus:border-gold/70" />
            <button type="submit" class="rounded-full bg-gold px-8 py-3 text-sm font-semibold text-nearblack transition hover:opacity-95">Подписаться</button>
          </form>
        </section>
      </div>
    </section>
    @endif

    @if($page === 'blog-post')
    @php($post = $blogPost ?? null)
    <section id="blog-post" class="bg-[rgb(6,6,6)] py-24 md:py-28">
      <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-xs text-offwhite/65">
          <a href="/" class="hover:text-offwhite">Главная</a><span class="px-1.5">/</span><a href="{{ route('blog.index') }}" class="hover:text-offwhite">блог</a><span class="px-1.5">/</span><span class="text-offwhite/85">{{ ($post && $post->title) ? $post->title : '' }}</span>
        </div>

        <article class="mt-6 overflow-hidden rounded-3xl border border-white/10">
          <div class="relative h-[220px] w-full bg-[linear-gradient(180deg,#262626_0%,#161616_100%)] sm:h-[280px] md:h-[340px]">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/40"></div>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="rounded-full border border-white/30 bg-white/15 px-4 py-1 text-[11px] uppercase tracking-[0.12em] text-offwhite">{{ ($post && $post->topic) ? $post->topic : 'Блог' }}</div>
            </div>
            <div class="absolute inset-x-0 bottom-0 p-5 text-center md:p-8">
              <h1 class="mx-auto max-w-3xl text-2xl font-semibold leading-tight text-offwhite md:text-4xl">{{ ($post && $post->title) ? $post->title : '' }}</h1>
              <p class="mt-2 text-sm text-offwhite/80">{{ ($post && $post->published_at) ? $post->published_at->translatedFormat('j F, Y') : '' }}</p>
            </div>
          </div>
        </article>

        <div class="mx-auto mt-10 max-w-4xl">
          <p class="text-offwhite/90">{{ ($post && $post->excerpt) ? $post->excerpt : '' }}</p>

          <h2 class="mt-10 text-3xl font-semibold text-offwhite">01. Тактильность и "Живая" Текстура</h2>
          <p class="mt-4 text-offwhite/80">Ключевым трендом остается визуальная и тактильная сложность. Кирпич ручной формовки с его неровными краями и естественными вкраплениями - это вызов современной стерильности.</p>

          <div class="mt-8 overflow-hidden rounded-3xl border border-white/10 bg-[linear-gradient(180deg,#2a2a2a_0%,#1b1b1b_100%)]">
            <div class="flex h-[240px] items-center justify-center sm:h-[320px] md:h-[380px]">
              <span class="rounded-full border border-white/20 bg-black/35 px-5 py-2 text-sm text-offwhite/75">Изображение отсутствует</span>
            </div>
          </div>
          <p class="mt-3 text-center text-xs text-offwhite/50">Текстура кирпича ручной формовки</p>

          <h2 class="mt-10 text-3xl font-semibold text-offwhite">02. Монохромный Минимализм</h2>
          <p class="mt-4 text-offwhite/80">На пике популярности - глубокие темные оттенки. Они создают эффект монолитности и структурности, особенно в сочетании с панорамным остеклением и строгими геометрическими формами.</p>

          <blockquote class="mt-8 rounded-2xl border border-white/10 bg-charcoal/45 px-6 py-7 text-lg text-offwhite/90">
            Кирпич - это не просто строительный блок, это пиксель материальной реальности.
            <div class="mt-3 text-sm text-gold/90">Александр Р., ведущий архитектор Studio Real Brick</div>
          </blockquote>

          <div class="mt-12 text-center">
            <h3 class="text-3xl font-semibold text-offwhite">Хотите узнать подробнее о применении<br class="hidden sm:block">этих трендов в вашем проекте?</h3>
            <a href="/contacts" class="mt-6 inline-flex rounded-full bg-gold px-9 py-3 text-sm font-semibold text-nearblack transition hover:opacity-95">Проконсультироваться</a>
            <div class="mt-4">
              <a href="{{ route('blog.index') }}" class="text-sm text-offwhite/80 hover:text-gold">Вернуться в блог →</a>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- FAQ -->
    @if($page === 'home')
    <section id="faq" class="relative overflow-hidden bg-[linear-gradient(90deg,rgb(10,10,10)_0%,rgb(0,0,0)_100%)] py-16 md:py-24">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-5 top-10 h-56 w-56 rounded-full bg-white/20 blur-3xl md:h-72 md:w-72"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.11] via-transparent to-transparent"></div>
      </div>
      <div class="relative z-10 mx-auto max-w-3xl px-4 lg:px-8">
        <h2 class="reveal text-center text-3xl font-bold text-gold md:text-4xl">Часто задаваемые вопросы</h2>
        <div class="reveal mt-10 space-y-3" id="faq-accordion">
          <div class="faq-item is-open rounded-2xl border border-gold/50 bg-charcoal/95 transition">
            <button type="button" class="faq-trigger flex w-full cursor-pointer items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-offwhite md:text-base" aria-expanded="true">
              <span>Что такое минеральный кирпич Real Brick и чем он отличается от обычного кирпича?</span>
              <span class="faq-icon flex h-7 w-7 items-center justify-center rounded-full border border-gold/40 text-gold text-xl leading-none">−</span>
            </button>
            <div class="faq-panel">
              <div class="faq-panel-inner">
                <div class="px-5 pb-4 text-sm leading-relaxed text-muted">
                  Real Brick — это кирпич ручной формовки из натуральных минеральных материалов. В отличие от обычного кирпича, он имеет более глубокую фактуру и выразительный внешний вид, что делает кладку визуально богаче и эстетичнее.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item rounded-2xl border border-white/10 bg-charcoal/95 transition hover:border-white/20">
            <button type="button" class="faq-trigger flex w-full cursor-pointer items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-offwhite md:text-base" aria-expanded="false">
              <span>Какой вид кирпича и плитки вы продаете?</span>
              <span class="faq-icon flex h-7 w-7 items-center justify-center rounded-full border border-gold/40 text-gold text-xl leading-none">+</span>
            </button>
            <div class="faq-panel">
              <div class="faq-panel-inner">
                <div class="px-5 pb-4 text-sm leading-relaxed text-muted">
                  Да — гарантийные обязательства закрепляются в договоре поставки. Сроки и условия зависят от объёма и типа продукции.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item rounded-2xl border border-white/10 bg-charcoal/95 transition hover:border-white/20">
            <button type="button" class="faq-trigger flex w-full cursor-pointer items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-offwhite md:text-base" aria-expanded="false">
              <span>Какая гарантия предоставляется на вашу продукцию?</span>
              <span class="faq-icon flex h-7 w-7 items-center justify-center rounded-full border border-gold/40 text-gold text-xl leading-none">+</span>
            </button>
            <div class="faq-panel">
              <div class="faq-panel-inner">
                <div class="px-5 pb-4 text-sm leading-relaxed text-muted">
                  Поставляем по всей России. Логистику и сроки согласуем индивидуально под ваш проект.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item rounded-2xl border border-white/10 bg-charcoal/95 transition hover:border-white/20">
            <button type="button" class="faq-trigger flex w-full cursor-pointer items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-offwhite md:text-base" aria-expanded="false">
              <span>Какие у вас сроки изготовления и доставки?</span>
              <span class="faq-icon flex h-7 w-7 items-center justify-center rounded-full border border-gold/40 text-gold text-xl leading-none">+</span>
            </button>
            <div class="faq-panel">
              <div class="faq-panel-inner">
                <div class="px-5 pb-4 text-sm leading-relaxed text-muted">
                  Да, наши специалисты помогут с подбором оттенка и фактуры по фото, образцам или выезду на объект.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Lead form -->
    @if($page === 'home')
    <section id="lead-form" class="relative overflow-hidden bg-black py-20 md:py-24 min-h-[980px] lg:min-h-[1040px]">
      <div class="absolute inset-0 z-0">
        <img src="{{ asset('storage/assets/faq-consult-bg.png') }}" alt="" class="pointer-events-none absolute right-0 bottom-0 h-[120%] w-auto max-w-none object-contain object-right-bottom lg:h-[128%]" />
        <div class="absolute inset-0 bg-black/25"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-nearblack via-nearblack/55 to-transparent"></div>
      </div>
      <div class="relative z-10 mx-auto max-w-7xl px-4 lg:px-8">
        <div class="reveal grid gap-12 lg:grid-cols-[minmax(320px,520px)_1fr] lg:items-end lg:gap-10">
          <div class="rounded-3xl border border-gold/40 bg-nearblack/85 p-6 shadow-[0_0_40px_rgba(0,0,0,0.45)] backdrop-blur-sm md:p-8">
            <h2 class="text-3xl font-bold leading-tight text-gold md:text-4xl">Получите консультацию по вашему проекту</h2>
            <p class="mt-3 text-sm text-offwhite/80 md:text-base">Оставьте заявку — и мы поможем подобрать кирпич для вашего проекта.</p>
            <form id="lead-form-el" class="mt-8 space-y-4">
              <div>
                <label for="lead-name" class="sr-only">Имя</label>
                <input id="lead-name" name="name" type="text" required placeholder="Ваше имя" class="w-full rounded-full border border-gold/45 bg-nearblack/80 px-5 py-3 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
              </div>
              <div>
                <label for="lead-phone" class="sr-only">Телефон</label>
                <input id="lead-phone" name="phone" type="tel" required placeholder="Ваш номер телефона" class="w-full rounded-full border border-gold/45 bg-nearblack/80 px-5 py-3 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
              </div>
              <div>
                <label for="lead-comment" class="sr-only">Комментарий</label>
                <input id="lead-comment" name="comment" type="text" placeholder="Примерный срок и объем работ" class="w-full rounded-full border border-gold/45 bg-nearblack/80 px-5 py-3 text-offwhite placeholder:text-muted/60 outline-none transition focus:border-gold focus:ring-1 focus:ring-gold" />
              </div>
              <button type="submit" class="w-full rounded-full bg-gold py-3.5 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:scale-[1.01] hover:shadow-[0_0_28px_rgba(201,169,110,0.35)] active:scale-[0.98]">
                Получить консультацию
              </button>
            </form>
            <p class="mt-3 text-xs text-muted">Нажимая кнопку, вы соглашаетесь с обработкой персональных данных</p>
            <div id="form-success" class="mt-6 hidden rounded-2xl border border-gold/50 bg-charcoal/95 p-6 text-center shadow-gold" role="status">
              <p class="text-lg font-semibold text-gold">Заявка отправлена</p>
              <p class="mt-2 text-sm text-muted">Спасибо! Мы свяжемся с вами в ближайшее время.</p>
            </div>
          </div>
          <div class="hidden min-h-[420px] lg:block"></div>
        </div>
      </div>
    </section>
    @endif

    <!-- Contacts -->
    @if($page === 'home')
    <section id="contacts" class="bg-[rgb(5,5,5)] py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <h2 class="reveal text-center text-3xl font-bold text-gold md:text-4xl">Контакты Real Brick</h2>
        <div class="reveal mt-12 grid gap-8 lg:grid-cols-2 lg:gap-12">
          <div class="space-y-4">
            <div class="rounded-2xl border border-white/10 bg-nearblack/80 p-5 transition hover:border-gold/30">
              <p class="text-xs font-semibold uppercase tracking-wider text-gold">Адрес</p>
              <p class="mt-2 text-offwhite">Казахстан, г. Алматы, ул.Минина 14А</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-nearblack/80 p-5 transition hover:border-gold/30">
              <p class="text-xs font-semibold uppercase tracking-wider text-gold">Телефон</p>
              <a href="tel:+78001234567" class="mt-2 block text-offwhite transition hover:text-gold">+7 (700) 444 69 99</a>
            </div>
            <div class="rounded-2xl border border-white/10 bg-nearblack/80 p-5 transition hover:border-gold/30">
              <p class="text-xs font-semibold uppercase tracking-wider text-gold">Время работы</p>
              <p class="mt-2 text-offwhite">пн-пт 10:00-20:00</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-nearblack/80 p-5 transition hover:border-gold/30">
              <p class="text-xs font-semibold uppercase tracking-wider text-gold">Email</p>
              <a href="mailto:hello@realbrick.demo" class="mt-2 block text-offwhite transition hover:text-gold">info@realbrick.kz</a>
            </div>
            <div class="flex gap-2 pt-2">
              <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/40 text-gold transition hover:bg-gold/10" aria-label="Instagram">IG</a>
              <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/40 text-gold transition hover:bg-gold/10" aria-label="Telegram">TG</a>
              <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-full border border-gold/40 text-gold transition hover:bg-gold/10" aria-label="WhatsApp">WA</a>
            </div>
          </div>
          <div class="overflow-hidden rounded-3xl border border-white/10 bg-muted/20 shadow-inner">
            <iframe
              title="Карта — REAL BRICK (Казахстан, г. Алматы, ул. Минина 14А)"
              class="h-[min(420px,60vh)] w-full grayscale contrast-125"
              src="https://www.openstreetmap.org/export/embed.html?bbox=76.920629%2C43.231377%2C76.929629%2C43.240377&layer=mapnik"
              loading="lazy"
            ></iframe>
            <p class="border-t border-white/10 bg-charcoal px-4 py-2 text-center text-xs text-muted">
              Казахстан, г. Алматы, ул. Минина 14А
            </p>
          </div>
        </div>
      </div>
    </section>
    @endif
@endsection

@push('scripts')
  <!-- Collection modal -->
  @if($page === 'home' || $page === 'catalog')
  <div id="collection-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" aria-hidden="true">
    <div id="collection-modal-backdrop" class="absolute inset-0 bg-black/75 backdrop-blur-sm transition-opacity"></div>
    <div id="collection-modal-panel" class="relative w-full max-w-lg scale-95 rounded-3xl border border-gold/40 bg-charcoal p-8 opacity-0 shadow-[0_0_48px_rgba(201,169,110,0.2)] transition-all duration-300" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <button type="button" id="collection-modal-close" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-offwhite transition hover:border-gold hover:text-gold" aria-label="Закрыть">
        <span class="text-xl leading-none">×</span>
      </button>
      <h3 id="modal-title" class="pr-10 text-2xl font-bold text-gold"></h3>
      <p id="modal-desc" class="mt-4 text-muted leading-relaxed"></p>
      <button type="button" id="collection-modal-ok" class="mt-8 w-full rounded-xl bg-gold py-3 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:shadow-gold">
        Понятно
      </button>
    </div>
  </div>
  @endif

  <script src="{{ asset('js/site.js') }}"></script>
@endpush
