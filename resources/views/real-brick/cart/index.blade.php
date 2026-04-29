@extends('layouts.app')

@section('title', 'REAL BRICK — корзина')

@push('styles')
<style>
  .rb-cart-shell {
    max-width: 1180px;
    margin: 0 auto;
    padding: 20px 20px 70px;
  }
  .rb-cart-title {
    font-size: clamp(38px, 5.6vw, 52px);
    line-height: 1;
    font-weight: 600;
    letter-spacing: -0.02em;
    color: #fff;
  }
  .rb-cart-title span {
    color: #c9a96e;
  }
  .rb-cart-grid {
    margin-top: 28px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 32px;
    align-items: start;
  }
  .rb-cart-card,
  .rb-cart-summary {
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 24px;
    background:
      radial-gradient(circle at 100% 100%, rgba(201, 169, 110, 0.09), transparent 38%),
      linear-gradient(130deg, rgba(27, 27, 27, 0.95), rgba(10, 10, 10, 0.94));
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02);
  }
  .rb-cart-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  .rb-cart-card {
    padding: 12px 14px;
    display: grid;
    grid-template-columns: 128px minmax(0, 1fr) auto;
    gap: 14px;
    align-items: center;
  }
  .rb-cart-image {
    width: 128px;
    height: 72px;
    border-radius: 9px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.35);
  }
  .rb-cart-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .rb-cart-info h3 {
    font-size: 14px;
    line-height: 1.35;
    font-weight: 500;
    color: #f1f1f1;
  }
  .rb-cart-meta {
    margin-top: 4px;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.58);
  }
  .rb-cart-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
  }
  .rb-cart-actions input {
    width: 64px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.92);
    background: rgba(255, 255, 255, 0.05);
  }
  .rb-cart-step {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid rgba(201, 169, 110, 0.5);
    color: #dcbf86;
    font-size: 16px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s ease;
  }
  .rb-cart-step:hover {
    background: rgba(201, 169, 110, 0.12);
  }
  .rb-cart-actions .rb-cart-qty-input {
    width: 48px;
    text-align: center;
    pointer-events: auto;
    appearance: textfield;
    -moz-appearance: textfield;
  }
  .rb-cart-actions .rb-cart-qty-input::-webkit-outer-spin-button,
  .rb-cart-actions .rb-cart-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  .rb-cart-btn {
    height: 28px;
    padding: 0 11px;
    border-radius: 999px;
    border: 1px solid rgba(201, 169, 110, 0.5);
    color: #dcbf86;
    font-size: 11px;
    font-weight: 500;
    transition: 0.22s ease;
  }
  .rb-cart-btn:hover {
    background: rgba(201, 169, 110, 0.1);
  }
  .rb-cart-remove {
    border-color: rgba(255, 255, 255, 0.24);
    color: rgba(255, 255, 255, 0.76);
  }
  .rb-cart-remove:hover {
    border-color: rgba(255, 255, 255, 0.36);
    background: rgba(255, 255, 255, 0.06);
  }
  .rb-cart-qty {
    text-align: right;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
  }
  .rb-cart-qty strong {
    display: block;
    font-size: 24px;
    line-height: 1;
    font-weight: 600;
    color: #d6b57b;
    margin-bottom: 4px;
  }
  .rb-cart-summary {
    padding: 24px;
  }
  .rb-cart-summary h2 {
    font-size: 39px;
    line-height: 1;
    font-weight: 600;
    color: #fff;
    margin-bottom: 20px;
  }
  .rb-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.86);
    margin-bottom: 10px;
  }

  .rb-summary-total {
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    color: #fff;
    font-size: 35px;
    font-weight: 600;
  }
  .rb-summary-total span:last-child {
    color: #d6b57b;
  }
  .rb-checkout-title {
    margin: 18px 0 12px;
    font-size: 29px;
    line-height: 1;
    font-weight: 500;
    color: #fff;
  }
  .rb-checkout-field {
    width: 100%;
    height: 48px;
    border-radius: 999px;
    border: 1px solid rgba(201, 169, 110, 0.55);
    padding: 0 18px;
    font-size: 15px;
    color: rgba(255, 255, 255, 0.92);
    background-color: rgba(10, 10, 10, 0.86);
    margin-bottom: 10px;
    pointer-events: auto;
    cursor: text;
  }
  .rb-checkout-field::placeholder {
    color: rgba(255, 255, 255, 0.42);
    opacity: 1;
  }
  .rb-checkout-field:focus {
    outline: none;
    border-color: #d2b173;
    box-shadow: 0 0 0 1px rgba(201, 169, 110, 0.25);
  }
  .rb-checkout-field:-webkit-autofill,
  .rb-checkout-field:-webkit-autofill:hover,
  .rb-checkout-field:-webkit-autofill:focus {
    -webkit-text-fill-color: rgba(255, 255, 255, 0.92);
    -webkit-box-shadow: 0 0 0 1000px rgba(10, 10, 10, 0.86) inset;
    box-shadow: 0 0 0 1000px rgba(10, 10, 10, 0.86) inset;
    transition: background-color 9999s ease-in-out 0s;
  }
  .rb-order-submit {
    margin-top: 6px;
    width: 100%;
    height: 50px;
    border-radius: 999px;
    background: #d3b073;
    color: #161616;
    font-size: 18px;
    font-weight: 500;
    transition: filter 0.2s ease;
  }
  .rb-order-submit:hover {
    filter: brightness(1.06);
  }
  .rb-empty {
    margin-top: 24px;
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 40px 24px;
    text-align: center;
    color: rgba(255, 255, 255, 0.78);
  }
  @media (max-width: 1080px) {
    .rb-cart-grid {
      grid-template-columns: minmax(0, 1fr);
    }
    .rb-cart-summary {
      max-width: 540px;
    }
  }
  @media (max-width: 700px) {
    .rb-cart-shell {
      padding: 16px 14px 50px;
    }
    .rb-cart-card {
      grid-template-columns: 1fr;
    }
    .rb-cart-image {
      width: 100%;
      height: 150px;
    }
    .rb-cart-qty {
      text-align: left;
    }
    .rb-cart-summary h2 {
      font-size: 33px;
    }
    .rb-checkout-title {
      font-size: 24px;
    }
  }
</style>
@endpush

@section('content')
<section class="rb-cart-shell">
  <div class="pb-7 text-xs text-muted uppercase tracking-wide">
    <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
    <span class="px-2">/</span>
    <span class="text-offwhite/80">Корзина</span>
  </div>

  <h1 class="rb-cart-title">Ваша <span>корзина</span></h1>

  @if($items->isEmpty())
    <div class="rb-empty">
      Корзина пока пустая. Добавьте товары из каталога.
      <div class="mt-4">
        <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="inline-flex rounded-full border border-gold/70 px-5 py-2 text-sm text-gold hover:bg-gold/10">Перейти в каталог</a>
      </div>
    </div>
  @else
    <div class="rb-cart-grid">
      <div class="rb-cart-list">
        @foreach($items as $item)
          <article class="rb-cart-card" data-cart-item-id="{{ $item['id'] }}">
            <div class="rb-cart-image">
              @if(!empty($item['image_url']))
                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" />
              @else
                <div class="flex h-full w-full items-center justify-center text-[10px] text-offwhite/60">Изображение отсутствует</div>
              @endif
            </div>
            <div class="rb-cart-info">
              <h3>{{ $item['name'] }}</h3>
              <div class="rb-cart-meta">Код товара: {{ $item['id'] }}</div>
              @if(!empty($item['slug']))
                <a href="{{ route('catalog.product', ['slug' => $item['slug'], 'lang' => ($lang ?? 'ru')]) }}" class="text-xs text-gold/90 hover:text-gold">Открыть товар</a>
              @endif
              <div class="rb-cart-actions">
                <button type="button" class="rb-cart-step" data-cart-step="-1" aria-label="Уменьшить количество">-</button>
                <input
                  type="number"
                  min="0"
                  max="999"
                  value="{{ (int) $item['qty'] }}"
                  class="rb-cart-qty-input"
                  data-cart-qty-input
                  aria-label="Количество"
                >
                <button type="button" class="rb-cart-step" data-cart-step="1" aria-label="Увеличить количество">+</button>
                <form method="POST" action="{{ route('cart.remove', ['lang' => ($lang ?? 'ru')]) }}">
                  @csrf
                  <input type="hidden" name="id" value="{{ $item['id'] }}">
                  <button type="submit" class="rb-cart-btn rb-cart-remove">Удалить</button>
                </form>
              </div>
            </div>
            <div class="rb-cart-qty">
              <strong data-cart-item-amount>{{ !empty($item['price_value']) ? number_format((float) $item['price_value'] * (int) $item['qty'], 0, '.', ' ') : 0 }}</strong>
              тг
            </div>
          </article>
        @endforeach
      </div>

      <aside class="rb-cart-summary">
        <h2>Итого</h2>
        <div class="rb-summary-row">
          <span>Товары ({{ $items->count() }})</span>
          <span data-cart-total>{{ number_format((float) ($totalAmount ?? 0), 0, '.', ' ') }} тг</span>
        </div>
        <div class="rb-summary-row">
          <span>Доставка</span>
          <span class="text-green-500">Бесплатно</span>
        </div>
        <div class="rb-summary-total">
          <span>К оплате</span>
          <span data-cart-total>{{ number_format((float) ($totalAmount ?? 0), 0, '.', ' ') }} тг</span>
        </div>

        <h3 class="rb-checkout-title">Оформление заказа</h3>
        <form method="POST" action="{{ route('cart.submit', ['lang' => ($lang ?? 'ru')]) }}">
          @csrf
          <input class="rb-checkout-field" name="name" type="text" required maxlength="255" value="{{ old('name') }}" placeholder="Ваше имя*" />
          @error('name')<p class="mb-2 text-xs text-red-300">{{ $message }}</p>@enderror
          <input class="rb-checkout-field" name="phone" type="text" required maxlength="50" value="{{ old('phone') }}" placeholder="Номер телефона*" />
          @error('phone')<p class="mb-2 text-xs text-red-300">{{ $message }}</p>@enderror
          <input class="rb-checkout-field" name="email" type="email" maxlength="190" value="{{ old('email') }}" placeholder="email*" />
          <input class="rb-checkout-field" name="comment" type="text" maxlength="1200" value="{{ old('comment') }}" placeholder="Адрес доставки*" />
          @error('comment')<p class="mb-2 text-xs text-red-300">{{ $message }}</p>@enderror
          <button type="submit" class="rb-order-submit">Оформить заказ</button>
        </form>

        <div class="mt-3 flex items-center justify-between gap-2">
          <form method="POST" action="{{ route('cart.clear', ['lang' => ($lang ?? 'ru')]) }}">
            @csrf
            <button type="submit" class="text-xs text-offwhite/65 hover:text-offwhite">Очистить корзину</button>
          </form>
          <a href="{{ route('catalog.index', ['lang' => ($lang ?? 'ru')]) }}" class="text-xs text-gold/95 hover:text-gold">Вернуться в каталог</a>
        </div>
      </aside>
    </div>
  @endif
</section>
@endsection

@push('scripts')
<script>
  (() => {
    const updateUrl = @json(route('cart.update', ['lang' => ($lang ?? 'ru')]));
    const csrf = @json(csrf_token());
    const totals = Array.from(document.querySelectorAll('[data-cart-total]'));

    const formatAmount = (value) => {
      const num = Number(value || 0);
      return `${Math.round(num).toLocaleString('ru-RU')} тг`;
    };

    const setTotals = (amount) => {
      totals.forEach((el) => {
        el.textContent = formatAmount(amount);
      });
    };

    const updateQty = async (itemCard, qty) => {
      const itemId = itemCard.dataset.cartItemId;
      if (!itemId) return;

      const normalizedQty = Math.max(0, Math.min(999, Number.isNaN(Number(qty)) ? 0 : Number(qty)));
      const qtyInput = itemCard.querySelector('[data-cart-qty-input]');
      if (qtyInput) qtyInput.value = normalizedQty;

      try {
        const response = await fetch(updateUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ id: itemId, qty: normalizedQty }),
        });

        if (!response.ok) {
          throw new Error('update-failed');
        }

        const data = await response.json();
        if (data.removed) {
          itemCard.remove();
          if (!document.querySelector('[data-cart-item-id]')) {
            window.location.reload();
            return;
          }
        } else {
          if (qtyInput) qtyInput.value = data.item_qty;
          const amountEl = itemCard.querySelector('[data-cart-item-amount]');
          if (amountEl) amountEl.textContent = Math.round(data.item_amount || 0).toLocaleString('ru-RU');
        }
        setTotals(data.total_amount || 0);
      } catch (e) {
        window.location.reload();
      }
    };

    document.querySelectorAll('[data-cart-item-id]').forEach((itemCard) => {
      const qtyInput = itemCard.querySelector('[data-cart-qty-input]');
      const minus = itemCard.querySelector('[data-cart-step="-1"]');
      const plus = itemCard.querySelector('[data-cart-step="1"]');
      let timer = null;

      if (minus && qtyInput) {
        minus.addEventListener('click', () => {
          const current = Number(qtyInput.value || 0);
          updateQty(itemCard, current - 1);
        });
      }

      if (plus && qtyInput) {
        plus.addEventListener('click', () => {
          const current = Number(qtyInput.value || 0);
          updateQty(itemCard, current + 1);
        });
      }

      if (qtyInput) {
        qtyInput.addEventListener('input', () => {
          if (timer) clearTimeout(timer);
          timer = setTimeout(() => updateQty(itemCard, qtyInput.value), 450);
        });
        qtyInput.addEventListener('blur', () => updateQty(itemCard, qtyInput.value));
        qtyInput.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            updateQty(itemCard, qtyInput.value);
          }
        });
      }
    });
  })();
</script>
@endpush

