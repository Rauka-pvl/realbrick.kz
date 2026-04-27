@extends('layouts.app')

@section('title', 'REAL BRICK — ' . ($project['title'] ?? 'Проект'))

@section('content')
  @vite(['resources/css/main.css', 'resources/css/reset.css'])

  @php
    $projectTitle = $project['title'] ?? 'Проект';
    $description = $project['description'] ?? [];
    $characteristics = $project['characteristics'] ?? [];
    $gallery = $project['gallery'] ?? [];
  @endphp

  <div class="bg-black text-offwhite">
    <div class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8 xl:px-10">
      <nav class="pb-6 pt-2 text-xs lowercase text-offwhite/55 sm:text-sm" aria-label="Хлебные крошки">
        <a href="/" class="transition hover:text-offwhite">Главная</a>
        <span class="px-2 text-offwhite/35">/</span>
        <a href="{{ route('projects.index') }}" class="transition hover:text-offwhite">наши проекты</a>
        <span class="px-2 text-offwhite/35">/</span>
        <span class="text-offwhite/80">{{ $projectTitle }}</span>
      </nav>

      <section class="relative overflow-hidden rounded-[18px] ">
        <img
          src="{{ asset($project['hero_image']) }}"
          alt="{{ $project['hero_alt'] }}"
          class="h-[280px] w-full object-cover sm:h-[360px] md:h-[420px]"
          loading="eager"
          decoding="async"
        />
        <div class="absolute inset-0  from-black via-black/40 to-transparent"></div>
        <div class="absolute bottom-6 left-6 right-6 sm:bottom-10 sm:left-10 sm:right-10">
          <h1 class="text-3xl font-semibold leading-none tracking-tight sm:text-4xl md:text-5xl">{{ $projectTitle }}</h1>
          <p class="mt-3 text-sm text-offwhite/75 sm:text-base">{{ $project['year'] }}</p>
        </div>
      </section>

      <section class="mt-8 grid gap-6 md:grid-cols-[1fr_minmax(240px,330px)] md:gap-8 xl:mt-10 xl:gap-10">
        <div class="rounded-2xl  bg-black p-5 sm:p-7">
          @foreach($description as $paragraph)
            <p class="text-sm leading-relaxed text-offwhite/80 sm:text-base {{ $loop->first ? '' : 'mt-4' }}">
              {{ $paragraph }}
            </p>
          @endforeach

          @if(!empty($project['brick_caption']))
            <h2 class="mt-6 text-lg font-semibold text-offwhite">{{ $project['brick_caption'] }}</h2>
          @endif

          @if(!empty($project['brick_text']))
            <p class="mt-3 text-sm leading-relaxed text-offwhite/80 sm:text-base">{{ $project['brick_text'] }}</p>
          @endif
        </div>

        <aside id="project-characteristics" class="h-fit rounded-2xl border border-[#d88b38] bg-black p-5 sm:p-6">
          <h3 class="text-xl font-semibold text-gold">Характеристики</h3>
          <dl class="mt-4 space-y-3">
            @foreach($characteristics as $item)
              <div class="flex items-start justify-between gap-4 border-b border-white pb-2">
                <dt class="text-sm text-offwhite/70">{{ $item['label'] }}</dt>
                <dd class="text-sm text-offwhite">{{ $item['value'] }}</dd>
              </div>
            @endforeach
          </dl>
        </aside>
      </section>

      @if(!empty($gallery))
        <section class="mx-auto mt-8 max-w-5xl grid grid-cols-1 gap-4 sm:grid-cols-2 xl:mt-10">
          @foreach($gallery as $image)
            <figure class="aspect-[4/3] overflow-hidden rounded-[16px] border border-white/10">
              <img
                src="{{ asset($image['src']) }}"
                alt="{{ $image['alt'] }}"
                class="h-full w-full object-cover"
                loading="lazy"
                decoding="async"
              />
            </figure>
          @endforeach
        </section>
      @endif

      <section class="mt-10 rounded-2xl border border-white/10 bg-[#060606] px-5 py-7 sm:px-8 sm:py-10">
        <div class="grid gap-6 md:grid-cols-[1fr_200px] md:items-center">
          <div>
            <h2 class="text-xl font-semibold leading-snug text-offwhite sm:text-2xl md:text-3xl">
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
                href="/contacts"
                class="inline-flex min-h-[48px] items-center justify-center rounded-full border border-gold/70 px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gold transition hover:bg-gold/10 sm:min-h-0 sm:px-8"
              >
                Рассчитать материалы
              </a>
            </div>
          </div>
          <div class="mx-auto w-full max-w-[160px] md:max-w-[200px]">
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
