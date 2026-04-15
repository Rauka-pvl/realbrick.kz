@extends('layouts.app')

@section('title', 'REAL BRICK — каталог продукции')

@section('content')
      <div class="mx-auto max-w-7xl px-4 py-8 md:py-12 lg:px-8">
        <!-- Breadcrumb -->
        <div class="pb-6 text-xs text-muted uppercase tracking-wide">
          <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
          <span class="px-2">/</span>
          <span class="text-offwhite/80">Каталог</span>
        </div>

        <div class="flex gap-10">
          <!-- Sidebar -->
          <aside class="w-[220px] shrink-0 hidden lg:block">
            <div class="text-xs font-semibold uppercase tracking-wider text-muted">Категории</div>
            <ul class="mt-4 space-y-3 text-sm">
              @foreach($sections as $section)
                <li>
                  <a
                    href="{{ route('catalog.collection', ['slug' => $section['slug'], 'lang' => ($lang ?? 'ru')]) }}"
                    class="block text-offwhite/70 transition hover:text-offwhite"
                  >
                    {{ $section['name'] }}
                  </a>
                </li>
              @endforeach
            </ul>
          </aside>

          <!-- Main -->
          <section class="flex-1">
            <div class="flex items-center justify-between gap-4 pb-6">
              <h1 class="text-2xl font-bold text-gold">Каталог продукции</h1>
              <div class="flex items-center gap-4">
                <div class="text-sm text-muted">{{ count($sections) }} категорий</div>
                <div class="inline-flex items-center gap-2 text-xs">
                  <a href="{{ route('catalog.index', ['lang' => 'ru']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'ru' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">RU</a>
                  <a href="{{ route('catalog.index', ['lang' => 'kz']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'kz' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">KZ</a>
                </div>
              </div>
            </div>

            <!-- Category tiles -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
              @foreach($sections as $section)
                <div class="group relative overflow-hidden rounded-3xl bg-charcoal border border-white/10 shadow-[0_0_26px_rgba(201,169,110,0.06)]">
                  <a href="{{ route('catalog.collection', ['slug' => $section['slug'], 'lang' => ($lang ?? 'ru')]) }}">
                  @if(!empty($section['cover_url']))
                    <img src="{{ $section['cover_url'] }}" alt="{{ $section['name'] }}" class="h-[170px] w-full object-cover opacity-90 transition duration-300 group-hover:opacity-100" />
                  @else
                    <div class="h-[170px] w-full bg-charcoal/80 flex items-center justify-center text-sm font-semibold text-offwhite/70">
                      Нету Фото
                    </div>
                  @endif
                  <div class="absolute inset-0 bg-gradient-to-t from-nearblack/95 via-nearblack/40 to-transparent"></div>
                  <div class="absolute bottom-0 left-0 right-0 p-6">
                    <div class="text-sm font-semibold uppercase tracking-wide text-offwhite/80">
                      {{ $section['name'] }}
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-sm text-gold/90 group-hover:text-gold transition">
                      каталог
                      <span class="text-lg" aria-hidden="true">→</span>
                    </div>
                  </div>
                  </a>
                </div>
              @endforeach
            </div>
          </section>
        </div>
      </div>
@endsection

