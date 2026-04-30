@extends('layouts.app')

@section('title', 'REAL BRICK — Наши проекты')

@push('styles')
<style>
  .projects-hero-title .gold {
    color: #c9a96e;
  }
  .projects-tab {
    border-bottom: 2px solid transparent;
    padding-bottom: 0.35rem;
    margin-bottom: -1px;
  }
  .projects-tab.is-active {
    border-bottom-color: #c9a96e;
    color: #f5f5f5;
  }
</style>
@endpush

@section('content')
  @vite(['resources/css/main.css', 'resources/css/reset.css'])

  @php
    $country = $country ?? 'all';
    $currentPage = $page ?? 1;
    $tabs = $tabs ?? config('realbrick-projects.tabs', []);
    $projects = $projects ?? [];
    $totalPages = $totalPages ?? 1;
  @endphp

  <div class="bg-black text-offwhite">
    <div class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8 xl:px-10">
      <nav class="pb-8 pt-2 text-xs lowercase text-offwhite/55 sm:text-sm" aria-label="Хлебные крошки">
        <a href="/" class="transition hover:text-offwhite">Главная</a>
        <span class="px-2 text-offwhite/35">/</span>
        <span class="text-offwhite/80">наши проекты</span>
      </nav>

      <header class="max-w-4xl">
        <h1 class="projects-hero-title text-3xl font-semibold leading-tight tracking-tight text-offwhite sm:text-4xl md:text-5xl xl:text-[3.25rem]">
          Проекты <span class="gold">Real Brick</span>
        </h1>
        <p class="mt-3 text-sm font-medium text-gold sm:text-base">
          реализованные проекты с нашего кирпича и плитки
        </p>
      </header>

      <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 border-b border-white/10 pb-3 text-sm lowercase sm:gap-x-8 sm:text-base">
        @foreach($tabs as $tab)
          <a
            href="{{ route('projects.index', array_filter(['country' => $tab['id'] === 'all' ? null : $tab['id'], 'page' => null])) }}"
            class="projects-tab pb-2 transition hover:text-offwhite {{ $country === $tab['id'] ? 'is-active' : 'text-offwhite/55' }}"
          >{{ $tab['label'] }}</a>
        @endforeach
      </div>

      <div>
        @forelse($projects as $index => $project)
          <article
            id="{{ $project['slug'] }}"
             style="border-top: 0.1px solid #ffffff"
            class="py-12 sm:py-14 md:py-16 xl:py-20">
            <div class="grid gap-8 md:grid-cols-2 md:items-center md:gap-10 xl:gap-14">
              @if($index % 2 === 1)
                <a
                  href="{{ route('projects.show', $project['slug']) }}"
                  class="relative order-2 block aspect-[16/11] min-h-[200px] md:order-1 md:min-h-[280px] xl:min-h-[340px]"
                >
                  <img
                    src="{{ asset($project['hero_image']) }}"
                    alt="{{ $project['hero_alt'] }}"
                    class="h-full w-full rounded-2xl object-cover transition duration-500 hover:opacity-95 xl:rounded-3xl"
                    loading="lazy"
                    decoding="async"
                  />
                </a>
                <div class="order-1 flex flex-col justify-center md:order-2 md:pr-4 xl:pr-8">
                  <h2 class="text-2xl font-semibold text-offwhite sm:text-3xl xl:text-[2rem]">{{ $project['title'] }}</h2>
                  <ul class="mt-5 space-y-2 text-sm sm:text-base">
                    @foreach($project['card_specs'] as $line)
                      @php
                        $colon = strpos($line, ':');
                      @endphp
                      <li class="leading-relaxed">
                        @if($colon !== false)
                          <span class="font-semibold text-offwhite">{{ trim(substr($line, 0, $colon)) }}:</span><span class="text-offwhite/65"> {{ trim(substr($line, $colon + 1)) }}</span>
                        @else
                          <span class="text-offwhite/80">{{ $line }}</span>
                        @endif
                      </li>
                    @endforeach
                  </ul>
                  <a
                    href="{{ route('projects.show', $project['slug']) }}"
                    class="mt-8 inline-flex w-fit items-center justify-center rounded-full bg-gold px-7 py-3 text-xs font-bold uppercase tracking-wider text-nearblack transition hover:opacity-95 active:scale-[0.99] sm:text-[0.7rem]"
                  >
                    посмотреть проект
                  </a>
                </div>
              @else
                <div class="order-1 flex flex-col justify-center md:order-1 md:pr-4 xl:pr-8">
                  <h2 class="text-2xl font-semibold text-offwhite sm:text-3xl xl:text-[2rem]">{{ $project['title'] }}</h2>
                  <ul class="mt-5 space-y-2 text-sm sm:text-base">
                    @foreach($project['card_specs'] as $line)
                      @php
                        $colon = strpos($line, ':');
                      @endphp
                      <li class="leading-relaxed">
                        @if($colon !== false)
                          <span class="font-semibold text-offwhite">{{ trim(substr($line, 0, $colon)) }}:</span><span class="text-offwhite/65"> {{ trim(substr($line, $colon + 1)) }}</span>
                        @else
                          <span class="text-offwhite/80">{{ $line }}</span>
                        @endif
                      </li>
                    @endforeach
                  </ul>
                  <a
                    href="{{ route('projects.show', $project['slug']) }}"
                    class="mt-8 inline-flex w-fit items-center justify-center rounded-full bg-gold px-7 py-3 text-xs font-bold uppercase tracking-wider text-nearblack transition hover:opacity-95 active:scale-[0.99] sm:text-[0.7rem]"
                  >
                    посмотреть проект
                  </a>
                </div>
                <a
                  href="{{ route('projects.show', $project['slug']) }}"
                  class="relative order-2 block aspect-[16/11] min-h-[200px] md:order-2 md:min-h-[280px] xl:min-h-[340px]"
                >
                  <img
                    src="{{ asset($project['hero_image']) }}"
                    alt="{{ $project['hero_alt'] }}"
                    class="h-full w-full rounded-2xl object-cover transition duration-500 hover:opacity-95 xl:rounded-3xl"
                    loading="lazy"
                    decoding="async"
                  />
                </a>
              @endif
            </div>
          </article>
        @empty
          <div class="border-t border-white/10 py-16 text-center text-offwhite/70">
            По выбранному фильтру проекты пока не добавлены.
          </div>
        @endforelse
      </div>

      @if($totalPages > 1)
        <nav class="flex flex-wrap items-center justify-center gap-2 border-t border-white/10 py-10 sm:py-12" aria-label="Страницы">
          @for($p = 1; $p <= $totalPages; $p++)
            <a
              href="{{ route('projects.index', array_filter(['country' => $country === 'all' ? null : $country, 'page' => $p === 1 ? null : $p])) }}"
              class="flex h-9 w-9 items-center justify-center rounded-full border text-xs font-semibold transition sm:h-10 sm:w-10 sm:text-sm {{ $currentPage === $p ? 'border-gold bg-gold text-nearblack' : 'border-white/25 text-offwhite/80 hover:border-gold/60 hover:text-gold' }}"
            >{{ $p }}</a>
          @endfor
          <a
            href="{{ route('projects.index', array_filter(['country' => $country === 'all' ? null : $country, 'page' => min($totalPages, $currentPage + 1)])) }}"
            class="ml-1 flex h-9 w-9 items-center justify-center rounded-full border border-white/25 text-offwhite/80 transition hover:border-gold/60 hover:text-gold sm:h-10 sm:w-10"
            aria-label="Следующая страница"
          >
            <span aria-hidden="true">→</span>
          </a>
        </nav>
      @endif

      <section class="py-12 sm:py-14 md:py-16 xl:py-20">
        <div class="grid gap-8 md:grid-cols-[1fr_min(220px,40%)] md:items-center md:gap-10 xl:grid-cols-[1fr_280px] xl:gap-14">
          <div>
            <h2 class="text-xl font-semibold leading-snug text-offwhite sm:text-2xl md:text-3xl xl:text-[2rem]">
              Рассчитать проект из Real Brick
            </h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-offwhite/65 sm:text-base">
              Получите 3D визуализацию или расчёт материалов для проекта прямо сейчас.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
              <a
                href="/contacts"
                class="inline-flex min-h-[48px] items-center justify-center rounded-full bg-gold px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-nearblack transition hover:opacity-95 sm:min-h-0 sm:px-8"
              >
                Получить 3D визуализацию
              </a>
              <a
                href="{{ route('calculator') }}"
                class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-gold/70 px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gold transition hover:bg-gold/10 sm:min-h-0 sm:px-8"
              >
                Рассчитать материалы
              </a>
            </div>
          </div>
          <div class="mx-auto w-full max-w-[200px] sm:max-w-[240px] md:mx-0 md:max-w-none xl:max-w-[260px]">
            <img
              src="{{ asset('storage/img/red-brick.png') }}"
              alt="Кирпич Real Brick"
              class="h-auto w-full object-contain drop-shadow-[0_12px_40px_rgba(0,0,0,0.45)]"
              width="520"
              height="520"
              loading="lazy"
              decoding="async"
            />
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection
