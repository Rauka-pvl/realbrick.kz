@extends('layouts.app')

@section('title', 'REAL BRICK — товар')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 md:py-12 lg:px-8">
  <div class="pb-6 text-xs text-muted uppercase tracking-wide">
    <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
    <span class="px-2">/</span>
    <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="text-offwhite/60 hover:text-offwhite">Каталог</a>
    @foreach(($productBreadcrumbs ?? []) as $crumb)
      <span class="px-2">/</span>
      @if(!empty($crumb['url']))
        <a href="{{ $crumb['url'] }}" class="text-offwhite/60 hover:text-offwhite">{{ $crumb['name'] }}</a>
      @else
        <span class="text-offwhite/80">{{ $crumb['name'] }}</span>
      @endif
    @endforeach
  </div>

  <div class="flex gap-12">
    <aside class="w-[260px] shrink-0 hidden lg:block">
      <div class="text-xs font-semibold uppercase tracking-wider text-muted">Товары раздела</div>
      <ul class="mt-4 space-y-2 text-sm">
        @foreach($relatedProducts as $item)
          <li><a href="{{ route('catalog.product', ['slug' => $item['slug'], 'lang' => ($lang ?? 'ru')]) }}" class="block text-offwhite/70 transition hover:text-offwhite">{{ $item['name'] }}</a></li>
        @endforeach
      </ul>
    </aside>

    <section class="flex-1">
      <div class="flex items-start justify-between gap-6 pb-6">
        <div class="text-sm text-muted">{{ $productName }}</div>
        <div class="flex items-center gap-4">
          <div class="text-sm text-muted">{{ count($relatedProducts) }} товаров в разделе</div>
          <div class="inline-flex items-center gap-2 text-xs">
            <a href="{{ route('catalog.product', ['slug' => request()->route('slug'), 'lang' => 'ru']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'ru' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">RU</a>
            <a href="{{ route('catalog.product', ['slug' => request()->route('slug'), 'lang' => 'kz']) }}" class="rounded border px-2 py-1 {{ ($lang ?? 'ru') === 'kz' ? 'border-gold text-gold' : 'border-white/20 text-offwhite/70' }}">KZ</a>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
        <div>
          <div class="rounded-2xl border border-white/10 bg-charcoal/30 overflow-hidden">
            <img src="{{ $productImage ?: asset('storage/assets/collection-1.png') }}" alt="{{ $productName }}" class="w-full h-[360px] object-contain bg-charcoal/60" />
          </div>
          @php($thumbs = collect($productImages ?? [])->filter()->unique()->reject(function ($u) use ($productImage) { return $u === $productImage; })->values()->take(4))
          @if($thumbs->isNotEmpty())
          <div class="mt-4 flex gap-3">
            @foreach($thumbs as $thumb)
              <div class="h-20 w-24 rounded-xl overflow-hidden border border-white/10 bg-charcoal/30">
                <img src="{{ $thumb }}" alt="{{ $productName }}" class="h-full w-full object-contain bg-charcoal/60" />
              </div>
            @endforeach
          </div>
          @endif
        </div>

        <div class="pt-1">
          @php($crumbNames = collect($productBreadcrumbs ?? [])->pluck('name')->values())
          @php($categoryName = $crumbNames->get(0))
          @php($subcategoryName = $crumbNames->get(1))

          <div class="flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-offwhite/90">{{ $productName }}</h1>
            <span class="rounded-full bg-green-600/15 border border-green-500/30 text-green-300 px-4 py-2 text-xs font-semibold">В наличии</span>
          </div>

          <div class="mt-5 grid grid-cols-1 gap-2 text-sm text-offwhite/75">
            <div><span class="text-muted">Категория:</span> <span class="ml-1 text-offwhite/90">{{ $categoryName ?: '—' }}</span></div>
            <div><span class="text-muted">Подкатегория:</span> <span class="ml-1 text-offwhite/90">{{ $subcategoryName ?: '—' }}</span></div>
            <div><span class="text-muted">Цена:</span> <span class="ml-1 text-gold">{{ !empty($productPriceValue) ? number_format((float) $productPriceValue, 0, '.', ' ') . ' тг' : 'по запросу' }}</span></div>
          </div>

          <div class="mt-6">
            <div class="text-sm font-medium text-offwhite/80">Размеры товара</div>
            <div class="mt-3 flex flex-wrap gap-2">
              @foreach(['250x120x65','250x120x65','250x120x65','250x120x65'] as $chip)
                <span class="rounded-full border border-white/15 bg-charcoal/40 px-3 py-1 text-[12px] text-offwhite/70">{{ $chip }}</span>
              @endforeach
            </div>
          </div>

          <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
            <form method="POST" action="{{ route('cart.add', ['lang' => ($lang ?? 'ru')]) }}">
              @csrf
              <input type="hidden" name="id" value="{{ $productBitrixId }}">
              <input type="hidden" name="name" value="{{ $productName }}">
              <input type="hidden" name="slug" value="{{ request()->route('slug') }}">
              <input type="hidden" name="image_url" value="{{ $productImage ?? '' }}">
              <input type="hidden" name="price_value" value="{{ $productPriceValue ?? '' }}">
              <input type="hidden" name="price_currency" value="{{ $productPriceCurrency ?? 'KZT' }}">
              <input type="hidden" name="qty" value="1">
              <button type="submit" class="rounded-full bg-gold px-7 py-3 text-sm font-bold uppercase tracking-wider text-nearblack hover:opacity-90 transition">Добавить в корзину</button>
            </form>
            <a href="{{ route('cart.index', ['lang' => ($lang ?? 'ru')]) }}" class="rounded-full border border-gold/60 bg-transparent px-7 py-3 text-sm font-semibold uppercase tracking-wider text-gold hover:bg-gold/10 transition">Открыть корзину</a>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection

