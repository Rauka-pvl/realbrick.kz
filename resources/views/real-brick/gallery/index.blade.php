@extends('layouts.app')

@section('title', 'REAL BRICK — фотогалерея')

@push('styles')
<style>
  .rb-gallery-wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 24px 20px 64px;
  }
  .rb-gallery-title {
    margin-top: 12px;
    text-align: center;
    font-size: clamp(42px, 7vw, 72px);
    line-height: 0.95;
    font-weight: 600;
    color: #d2b075;
    letter-spacing: -0.02em;
  }
  .rb-gallery-subtitle {
    margin-top: 10px;
    text-align: center;
    font-size: 22px;
    line-height: 1.35;
    color: rgba(255, 255, 255, 0.78);
  }
  .rb-filter-row {
    margin-top: 22px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px 12px;
  }
  .rb-filter-label {
    font-size: 35px;
    font-weight: 200;
    color: #f3f3f3;
    margin-right: 8px;
  }
  
  .rb-pill {
    height: 40px;
    padding: 0 20px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    display: inline-flex;
    align-items: center;
    font-size: 20px;
    font-weight: 300;
    color: rgba(255, 255, 255, 0.85);
    transition: 0.2s ease;
  }

  .rb-pill:hover {
    border-color: rgba(201, 169, 110, 0.8);
    color: #d8b77f;
  }

  .rb-pill.is-active {
    border-color: rgba(201, 169, 110, 0.95);
    
    color: #e3c68e;
  }
  .rb-gallery-grid {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 14px;
  }
  .rb-photo-card {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    background: #111;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  .rb-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  .rb-photo-featured {
    grid-column: span 8;
    height: 286px;
  }
  .rb-photo-featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 30%, rgba(0, 0, 0, 0.85));
    display: flex;
    align-items: flex-end;
    padding: 18px 22px;
  }
  .rb-photo-featured-text {
    display: block;
    width: 100%;
  }
  .rb-photo-featured h3 {
    font-size: 32px;
    line-height: 1;
    color: #f7f7f7;
    font-weight: 500;
  }
  .rb-photo-featured p {
    margin-top: 4px;
    font-size: 16px;
    color: rgba(255, 255, 255, 0.9);
  }
  .rb-photo-col-4 { grid-column: span 4; height: 286px; }
  .rb-photo-col-4-short { grid-column: span 4; height: 232px; }
  .rb-photo-col-8-wide { grid-column: span 8; height: 286px; }

  .rb-gallery-pagination {
    margin-top: 22px;
    display: flex;
    justify-content: center;
    gap: 9px;
  }
  .rb-page-btn {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: rgba(255, 255, 255, 0.82);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }
  .rb-page-btn.is-active {
    border-color: #c9a96e;
    background: #c9a96e;
    color: #141414;
    font-weight: 600;
  }
  @media (max-width: 1024px) {
    .rb-filter-label { font-size: 26px; }
    .rb-pill { font-size: 16px; height: 36px; }
    .rb-photo-featured { height: 250px; }
    .rb-photo-col-4, .rb-photo-col-4-short, .rb-photo-col-8-wide { height: 220px; }
  }
  @media (max-width: 760px) {
    .rb-gallery-wrap { padding: 14px 14px 42px; }
    .rb-gallery-subtitle { font-size: 16px; }
    .rb-filter-label { width: 100%; margin-right: 0; margin-bottom: 2px; font-size: 22px; }
    .rb-pill { font-size: 14px; padding: 0 14px; height: 34px; }
    .rb-gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .rb-photo-featured,
    .rb-photo-col-8-wide,
    .rb-photo-col-4,
    .rb-photo-col-4-short { grid-column: span 2; height: 190px; }
    .rb-photo-featured h3 { font-size: 20px; }
    .rb-photo-featured p { font-size: 12px; }
  }
</style>
@endpush

@section('content')
<section class="rb-gallery-wrap">
  <div class="pb-4 text-xs text-muted uppercase tracking-wide">
    <a href="/" class="text-offwhite/60 hover:text-offwhite">Главная</a>
    <span class="px-2">/</span>
    <span class="text-offwhite/80">Галерея</span>
  </div>

  <h1 class="rb-gallery-title">Фотогалерея</h1>
  <p class="rb-gallery-subtitle">Живые примеры использования продукции<br>Real Brick в архитектуре</p>

  <div class="rb-filter-row">
    <div class="rb-filter-label">Коллекция:</div>
    <a href="{{ route('gallery.index', ['color' => $activeColor ?: null]) }}" class="rb-pill {{ $activeCollection === '' ? 'is-active' : '' }}">Все</a>
    @foreach($collections as $collection)
      <a href="{{ route('gallery.index', ['collection' => $collection, 'color' => $activeColor ?: null]) }}" class="rb-pill {{ $activeCollection === $collection ? 'is-active' : '' }}">{{ $collection }}</a>
    @endforeach
  </div>

  <div class="rb-filter-row">
    <div class="rb-filter-label">По цвету:</div>
    <a href="{{ route('gallery.index', ['collection' => $activeCollection ?: null]) }}" class="rb-pill {{ $activeColor === '' ? 'is-active' : '' }}">Все цвета</a>
    @foreach($colors as $color)
      <a href="{{ route('gallery.index', ['collection' => $activeCollection ?: null, 'color' => $color]) }}" class="rb-pill {{ $activeColor === $color ? 'is-active' : '' }}">{{ $color }}</a>
    @endforeach
  </div>

  @if($featuredPhoto)
    @php($cards = $galleryCards->values())
    <div class="rb-gallery-grid">
      <article class="rb-photo-card rb-photo-featured">
        <img src="{{ asset('storage/' . $featuredPhoto->image_path) }}" alt="{{ $featuredPhoto->title ?? 'Фото проекта' }}">
        <div class="rb-photo-featured-overlay">
          <div class="rb-photo-featured-text">
            <h3>{{ $featuredPhoto->title ?? 'Коллекция Real Brick' }}</h3>
            <p>{{ $featuredPhoto->subtitle ?? 'Реальный объект' }}</p>
          </div>
        </div>
      </article>

      @foreach($cards as $index => $photo)
        @php($class = ($index >= 1 && $index <= 3) ? 'rb-photo-col-4-short' : (($index === 5) ? 'rb-photo-col-8-wide' : 'rb-photo-col-4'))
        <article class="rb-photo-card {{ $class }}">
          <img src="{{ asset('storage/' . $photo->image_path) }}" alt="Фото {{ $photo->collection_type }} {{ $photo->color }}">
        </article>
      @endforeach
    </div>

    @if($galleryCards->lastPage() > 1)
      <div class="rb-gallery-pagination">
        @for($page = 1; $page <= $galleryCards->lastPage(); $page++)
          <a href="{{ $galleryCards->url($page) }}" class="rb-page-btn {{ $galleryCards->currentPage() === $page ? 'is-active' : '' }}">{{ $page }}</a>
        @endfor
        @if($galleryCards->hasMorePages())
          <a href="{{ $galleryCards->nextPageUrl() }}" class="rb-page-btn">→</a>
        @endif
      </div>
    @endif
  @else
    <div class="mt-8 rounded-2xl border border-white/10 bg-charcoal/40 p-8 text-center text-offwhite/70">
      По выбранным фильтрам пока нет фотографий.
    </div>
  @endif
</section>
@endsection

