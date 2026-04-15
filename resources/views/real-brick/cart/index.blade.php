@extends('layouts.app')

@section('title', 'REAL BRICK — корзина')

@push('styles')
<style>
  body.modal-open {
    overflow: hidden;
  }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-5xl px-4 lg:px-8">
  <div class="pb-6 text-xs text-muted uppercase tracking-wide">
    <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
    <span class="px-2">/</span>
    <span class="text-offwhite/80">Корзина</span>
  </div>

  <div class="flex items-center justify-between gap-4 pb-6">
    <h1 class="text-2xl font-bold text-gold">Корзина</h1>
    <div class="text-sm text-offwhite/70">Товаров: {{ $totalQty }}</div>
  </div>

  @if($items->isEmpty())
    <div class="rounded-2xl border border-white/10 bg-charcoal/50 p-8 text-center text-offwhite/75">
      Корзина пока пустая. Добавьте товары из каталога.
      <div class="mt-4">
        <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="inline-flex rounded-full border border-gold/70 px-5 py-2 text-sm text-gold hover:bg-gold/10">Перейти в каталог</a>
      </div>
    </div>
  @else
    <div class="space-y-3">
      @foreach($items as $item)
        <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-charcoal/40 p-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-4">
            <div class="h-16 w-20 overflow-hidden rounded-lg border border-white/10 bg-charcoal/50">
              @if(!empty($item['image_url']))
                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover" />
              @else
                <div class="flex h-full w-full items-center justify-center text-[10px] text-offwhite/60">Нету Фото</div>
              @endif
            </div>
            <div>
              <div class="text-sm font-semibold text-offwhite">{{ $item['name'] }}</div>
              @if(!empty($item['slug']))
                <a href="{{ route('catalog.product', ['slug' => $item['slug'], 'lang' => ($lang ?? 'ru')]) }}" class="text-xs text-gold/90 hover:text-gold">Открыть товар</a>
              @endif
            </div>
          </div>
          <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('cart.update', ['lang' => ($lang ?? 'ru')]) }}" class="flex items-center gap-2">
              @csrf
              <input type="hidden" name="id" value="{{ $item['id'] }}">
              <input type="number" name="qty" min="0" max="999" value="{{ $item['qty'] }}" class="w-20 rounded-lg border border-white/20 bg-nearblack/60 px-3 py-2 text-sm text-offwhite focus:border-gold focus:outline-none" />
              <button type="submit" class="rounded-lg border border-gold/70 px-3 py-2 text-xs text-gold hover:bg-gold/10">Обновить</button>
            </form>
            <form method="POST" action="{{ route('cart.remove', ['lang' => ($lang ?? 'ru')]) }}">
              @csrf
              <input type="hidden" name="id" value="{{ $item['id'] }}">
              <button type="submit" class="rounded-lg border border-red-400/50 px-3 py-2 text-xs text-red-300 hover:bg-red-500/10">Удалить</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
      <form method="POST" action="{{ route('cart.clear', ['lang' => ($lang ?? 'ru')]) }}">
        @csrf
        <button type="submit" class="rounded-full border border-red-400/50 px-5 py-2 text-sm text-red-300 hover:bg-red-500/10">Очистить корзину</button>
      </form>
      <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="rounded-full border border-gold/70 px-5 py-2 text-sm text-gold hover:bg-gold/10">Продолжить выбор</a>
    </div>

    <div class="mt-8 rounded-2xl border border-white/10 bg-charcoal/50 p-5 md:p-6">
      <h2 class="text-lg font-semibold text-offwhite">Оформить заявку</h2>
      <p class="mt-1 text-sm text-offwhite/70">Форма откроется в модальном окне вместе со списком товаров.</p>
      <button type="button" id="open-cart-modal" class="mt-4 rounded-full bg-gold px-6 py-3 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:opacity-90">
        Открыть форму
      </button>
    </div>

    <div id="cart-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4" aria-hidden="true">
      <div id="cart-modal-backdrop" class="absolute inset-0 bg-black/75 backdrop-blur-sm"></div>
      <div class="relative z-10 w-full max-w-2xl rounded-3xl border border-gold/30 bg-charcoal p-5 shadow-[0_0_48px_rgba(0,0,0,0.55)] md:p-6">
        <div class="mb-4 flex items-start justify-between gap-4">
          <div>
            <h3 class="text-xl font-semibold text-offwhite">Отправить заявку</h3>
            <p class="mt-1 text-sm text-offwhite/70">Имя, телефон, комментарий и товары из корзины</p>
          </div>
          <button type="button" id="close-cart-modal" class="rounded-full border border-white/20 px-3 py-1 text-offwhite/80 hover:border-gold hover:text-gold">×</button>
        </div>

        <div class="mb-4 max-h-44 space-y-2 overflow-auto rounded-xl border border-white/10 bg-nearblack/40 p-3">
          @foreach($items as $item)
            <div class="flex items-center justify-between gap-3 text-sm">
              <div class="truncate text-offwhite/85">{{ $item['name'] }}</div>
              <div class="shrink-0 text-gold">x {{ $item['qty'] }}</div>
            </div>
          @endforeach
        </div>

        <form method="POST" action="{{ route('cart.submit', ['lang' => ($lang ?? 'ru')]) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
          @csrf
          <div>
            <label for="cart_name" class="mb-1 block text-sm text-offwhite/80">Имя</label>
            <input id="cart_name" name="name" type="text" required maxlength="255" value="{{ old('name') }}" class="w-full rounded-xl border border-white/15 bg-nearblack/60 px-4 py-3 text-sm text-offwhite placeholder:text-offwhite/35 focus:border-gold focus:outline-none" placeholder="Ваше имя" />
            @error('name')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
          </div>

          <div>
            <label for="cart_phone" class="mb-1 block text-sm text-offwhite/80">Телефон</label>
            <input id="cart_phone" name="phone" type="text" required maxlength="50" value="{{ old('phone') }}" class="w-full rounded-xl border border-white/15 bg-nearblack/60 px-4 py-3 text-sm text-offwhite placeholder:text-offwhite/35 focus:border-gold focus:outline-none" placeholder="+7 (___) ___ __ __" />
            @error('phone')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2">
            <label for="cart_comment" class="mb-1 block text-sm text-offwhite/80">Комментарии</label>
            <textarea id="cart_comment" name="comment" rows="4" maxlength="1200" class="w-full rounded-xl border border-white/15 bg-nearblack/60 px-4 py-3 text-sm text-offwhite placeholder:text-offwhite/35 focus:border-gold focus:outline-none" placeholder="Комментарий к заявке">{{ old('comment') }}</textarea>
            @error('comment')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
          </div>

          <div class="md:col-span-2 flex items-center gap-3">
            <button type="submit" class="rounded-full bg-gold px-6 py-3 text-sm font-bold uppercase tracking-wider text-nearblack transition hover:opacity-90">
              Отправить заявку
            </button>
            <button type="button" id="cancel-cart-modal" class="rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-offwhite/80 hover:border-gold hover:text-gold">
              Отмена
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const modal = document.getElementById('cart-modal');
    const openBtn = document.getElementById('open-cart-modal');
    const closeBtn = document.getElementById('close-cart-modal');
    const cancelBtn = document.getElementById('cancel-cart-modal');
    const backdrop = document.getElementById('cart-modal-backdrop');
    if (!modal || !openBtn) return;

    const openModal = () => {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.classList.add('modal-open');
    };
    const closeModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.classList.remove('modal-open');
    };

    openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    @if($errors->has('name') || $errors->has('phone') || $errors->has('comment'))
    openModal();
    @endif
  })();
</script>
@endpush

