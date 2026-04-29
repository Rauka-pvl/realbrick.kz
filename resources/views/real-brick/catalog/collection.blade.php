@extends('layouts.app')

@section('title', 'REAL BRICK — коллекции')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 md:py-12 lg:px-8">
  <div class="pb-6 text-xs text-muted uppercase tracking-wide">
    <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
    <span class="px-2">/</span>
    <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="text-offwhite/60 hover:text-offwhite">Каталог</a>
    @foreach(($collectionBreadcrumbs ?? []) as $crumb)
      <span class="px-2">/</span>
      @if(!empty($crumb['url']))
        <a href="{{ $crumb['url'] }}" class="text-offwhite/60 hover:text-offwhite">{{ $crumb['name'] }}</a>
      @else
        <span class="text-offwhite/80">{{ $crumb['name'] }}</span>
      @endif
    @endforeach
  </div>

  <div class="flex gap-12">
    <aside class="w-[220px] shrink-0 hidden lg:block">
      <div class="text-xs font-semibold uppercase tracking-wider text-muted">Коллекции</div>
      <ul class="mt-4 space-y-3 text-sm">
        @forelse($leftSections as $section)
          <li><a href="{{ route('catalog.collection', ['slug' => $section['slug'], 'lang' => ($lang ?? 'ru')]) }}" class="block text-offwhite/70 transition hover:text-offwhite">{{ $section['name'] }}</a></li>
        @empty
          <li class="text-offwhite/45">Нет вложенных разделов</li>
        @endforelse
      </ul>
    </aside>

    <section class="flex-1">
      <div class="flex items-start justify-between gap-4 pb-6">
        <div>
          <div class="text-sm font-medium text-offwhite/90">{{ $sectionName }}</div>
          <div class="text-xs text-muted">{{ count($childSections) }} подкатегорий</div>
        </div>
        <div class="inline-flex items-center gap-2 text-xs">
          <a href="{{ route('catalog.collection', ['slug' => request()->route('slug'), 'lang' => 'ru']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'ru' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">RU</a>
          <a href="{{ route('catalog.collection', ['slug' => request()->route('slug'), 'lang' => 'kz']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'kz' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">KZ</a>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @forelse($childSections as $section)
          <a href="{{ route('catalog.collection', ['slug' => $section['slug'], 'lang' => ($lang ?? 'ru')]) }}" class="group">
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-charcoal shadow-lg">
              <div class="aspect-[4/3] overflow-hidden">
                @if(!empty($section['cover_url']))
                  <img src="{{ $section['cover_url'] }}" alt="{{ $section['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" />
                @else
                  <div class="h-full w-full bg-charcoal/80 flex items-center justify-center text-sm font-semibold text-offwhite/70">Изображение отсутствует</div>
                @endif
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-nearblack/90 via-nearblack/40 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 px-6 py-5">
                <div class="text-sm font-semibold text-offwhite/90">{{ $section['name'] }}</div>
                <div class="mt-2 text-xs font-medium text-gold/90">подкаталог <span class="text-offwhite/70" aria-hidden="true">→</span></div>
              </div>
            </div>
          </a>
        @empty
          @forelse($products as $product)
            <div class="group relative overflow-hidden rounded-3xl border border-white/10 bg-charcoal shadow-lg">
              <a href="{{ route('catalog.product', ['slug' => $product['slug'], 'lang' => ($lang ?? 'ru')]) }}">
                <div class="aspect-[4/3] overflow-hidden">
                  @if(!empty($product['image_url']))
                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]" />
                  @else
                    <div class="h-full w-full bg-charcoal/80 flex items-center justify-center text-sm font-semibold text-offwhite/70">Изображение отсутствует</div>
                  @endif
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-nearblack/90 via-nearblack/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 px-6 py-5 pr-24">
                  <div class="text-sm font-semibold text-offwhite/90">{{ $product['name'] }}</div>
                  @if(!empty($product['price_value']))
                    <div class="mt-1 text-sm font-semibold text-gold">{{ number_format((float) $product['price_value'], 0, '.', ' ') }} тг</div>
                  @endif
                  <div class="mt-2 text-xs font-medium text-gold/90">товар <span class="text-offwhite/70" aria-hidden="true">→</span></div>
                </div>
              </a>
              <form method="POST" action="{{ route('cart.add', ['lang' => ($lang ?? 'ru')]) }}" class="absolute bottom-4 right-4 z-20">
                @csrf
                <input type="hidden" name="id" value="{{ $product['id'] }}">
                <input type="hidden" name="name" value="{{ $product['name'] }}">
                <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                <input type="hidden" name="image_url" value="{{ $product['image_url'] ?? '' }}">
                <input type="hidden" name="price_value" value="{{ $product['price_value'] ?? '' }}">
                <input type="hidden" name="price_currency" value="{{ $product['price_currency'] ?? 'KZT' }}">
                <input type="hidden" name="qty" value="1">
                <button type="submit" class="rounded-full border border-gold/70 bg-nearblack/70 px-3 py-1 text-xs font-medium text-gold hover:bg-gold/15">В корзину</button>
              </form>
            </div>
          @empty
            <div class="col-span-full rounded-2xl border border-white/10 bg-charcoal/40 p-6 text-sm text-offwhite/70">В этом разделе нет подкаталогов и товаров.</div>
          @endforelse
        @endforelse
      </div>
    </section>
  </div>
</div>
@endsection

