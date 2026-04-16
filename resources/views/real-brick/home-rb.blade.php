@extends('layouts.app')

@section('title', 'REAL BRICK — главная')

@push('styles')
@vite('resources/rb/main.css')
<style>body{overflow-x:hidden;}</style>
@endpush

@section('content')


  <section class="header">
    <div class="container">
      <div class="header_body">
        <div class="header_body-top">
          <h1>REAL BRICK</h1>
          <div class="heaeder_body-text">
            <p>Каждый кирпич формируется <br> вручную. Поэтому фактура<br> никогда не повторяется</p>
          </div>
        </div>
      </div>
      <div class="header_footer">
        <div class="header_footer-left">
          <h1>1 В МИРЕ МИНЕРАЛЬНЫЙ КИРПИЧ <br> РУЧНОЙ ФОРМОВКИ </h1>
          <p>для архитектуры <br> и интерьеров </p>
          <div class="header_footer-buttons">
            <a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}" class="btn btn_collection">смотреть коллекцию</a>
            <a href="{{ route('catalog.index', ['lang' => request('lang', 'ru')]) }}" class="btn btn_catalog">скачать каталог</a>
          </div>
        </div>
        <img src="{{ asset('rb/img/Кирпич.png') }}" alt="" class="header_footer-decor" aria-hidden="true" width="200px">
        <div class="header_footer-right">
          <div class="header_footer-card">
            <h1>6%</h1>
            <p>- водопоглощение</p>
          </div>
          <div class="header_footer-card">
            <h1>M250</h1>
            <p>- марка прочности</p>
          </div>
          <div class="header_footer-card">
            <h1>F500</h1>
            <p>- морозостойкость</p>
          </div>
        </div>
      </div>

      <div class="header_footer-bottom">
        <div class="header_bottom-text">Где используется <span>Real Brick</span></div>
      </div>
    </div>
  </section>

  <section class="usage">
        <div class="usage_title">
          Где используется <span>Real Brick</span>
        </div>
  <div class="usage_slider">
    <div class="usage_track">

      <div class="usage_card">
        <img src="{{ asset('rb/img/2 блок карточки/1.png') }}" alt="Жилые дома">
        <div class="usage_card-content">
          <h2>Жилые дома</h2>
          <p>Фасады из кирпича и плитки ручной формовки</p>
          <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
        </div>
      </div>

      <div class="usage_card">
        <img src="{{ asset('rb/img/2 блок карточки/2.png') }}" alt="Интерьеры">
        <div class="usage_card-content">
          <h2>Интерьеры</h2>
          <p>Живая фактура кирпича для выразительных интерьеров.</p>
          <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
        </div>
      </div>

      <div class="usage_card">
        <img src="{{ asset('rb/img/2 блок карточки/бани и зоны барбекю.png') }}" alt="Фасады">
        <div class="usage_card-content">
          <h2>Фасады</h2>
          <p>Современные решения для внешней отделки зданий.</p>
          <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
        </div>
      </div>

      <div class="usage_card">
        <img src="{{ asset('rb/img/2 блок карточки/коммерческие объекты и офисы.png') }}" alt="Коммерческие объекты">
        <div class="usage_card-content">
          <h2>Коммерческие объекты</h2>
          <p>Офисы, рестораны и общественные пространства.</p>
          <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
        </div>
      </div>

      <div class="usage_card">
        <img src="{{ asset('rb/img/2 блок карточки/технические помещения.png') }}" alt="Ландшафт">
        <div class="usage_card-content">
          <h2>Ландшафт</h2>
          <p>Дорожки, подпорные стены и малые архитектурные формы.</p>
          <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
        </div>
      </div>

    </div>
  </div>

  <div class="usage_dots">
    <button class="usage_dot active" data-index="0"></button>
    <button class="usage_dot" data-index="1"></button>
    <button class="usage_dot" data-index="2"></button>
    <button class="usage_dot" data-index="3"></button>
    <button class="usage_dot" data-index="4"></button>
  </div>

  <div class="usage_bottom">
    <p>Кирпич ручной формовки создаёт фактуру,<br>которая делает архитектуру выразительной.</p>
    <a href="#" class="btn btn_collection usage_cta">подобрать кирпич</a>
  </div>
</section>

<section class="collections">
  <div class="container">

    <div class="collections_header">
      <h2 class="collections_title">Коллекции <span>Real Brick</span></h2>
      <a href="#" class="collections_all">смотреть весь каталог <span>→</span></a>
    </div>

    <div class="collections_grid">

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 18.png') }}" alt="Кирпич ручной формовки">
        <div class="collections_card-content">
          <h3>Кирпич ручной формовки</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 17.png') }}" alt="Плитка ручной формовки">
        <div class="collections_card-content">
          <h3>Плитка ручной формовки</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 19.png') }}" alt="Декоративные элементы">
        <div class="collections_card-content">
          <h3>Декоративные элементы</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 15.png') }}" alt="Напольное покрытие">
        <div class="collections_card-content">
          <h3>Напольное покрытие</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 19.png') }}" alt="Лимитированные серии">
        <div class="collections_card-content">
          <h3>Лимитированные серии</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 27.png') }}" alt="Черепица">
        <div class="collections_card-content">
          <h3>Черепица</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

      <div class="collections_card">
        <img src="{{ asset('rb/img/Слой 28.png') }}" alt="Сопутствующие материалы">
        <div class="collections_card-content">
          <h3>Сопутствующие материалы</h3>
          <a href="#" class="collections_link">каталог <span>→</span></a>
        </div>
      </div>

    </div>
  </div>
</section>

<section class="projects">
  <div class="projects_inner">

    <!-- фоновый текст REAL BRICK -->
    <img src="{{ asset('rb/img/REAL BRICK копия.png') }}" alt="" class="projects_bg-img">

    <div class="container">
      <div class="projects_layout">

        <!-- левая колонка -->
        <div class="projects_left">
          <h2 class="projects_title">Реализованные<br> проекты <span>Real Brick</span></h2>
          <p class="projects_desc">Квартиры, рестораны, коммерческие фасады.<br>Кирпич для проектов со вкусом и стилем</p>

          <div class="projects_stat">
            <span class="projects_stat-num">500+</span>
            <span class="projects_stat-label">реализованных проектов</span>
          </div>

          <div class="projects_geo">
            <img src="{{ asset('rb/img/kazakhstan-flag-national-europe-emblem-icon-illustration-abstract-design-element-free-vector копия.png') }}" alt="Казахстан" width="36" height="36">
            <span>Используется в проектах<br>по всему Казахстану</span>
          </div>

          <a href="#" class="projects_portfolio">смотреть портфолио <span>→</span></a>
        </div>

        <!-- правая колонка — карточка CTA -->
        <div class="projects_cta-card">
          <h3>Попробуйте<br>Real Brick в<br>вашем проекте</h3>
          <p>Получите 3D-визуализацию<br>или расчёт материалов для<br>проекта прямо сейчас.</p>
          <a href="#" class="btn btn_collection projects_btn-gold">Получить 3D-визуализацию</a>
          <a href="#" class="btn btn_catalog projects_btn-outline">Рассчитать материалы</a>
        </div>

      </div>
    </div>
  </div>
</section>


<section class="why">
  <div class="container">
    <h2 class="why_title">Почему выбирают <span>Real Brick</span></h2>

    <div class="why_layout">

      <!-- левая колонка -->
      <div class="why_left">
        <p class="why_text">
          <span class="why_accent">Real Brick</span> — кирпич ручной формовки
          с уникальной фактурой и характером.
          Мы поставляем материалы для
          архитектуры и интерьеров, где важны
          стиль, качество и долговечность.
        </p>
        <p class="why_text">
          С 2007 года продукция Real Brick
          используется в проектах, где ценят
          <span class="why_accent">натуральные материалы и<br>
          выразительную фактуру.</span>
        </p>
      </div>

      <!-- правая колонка — карточки -->
      <div class="why_cards">

        <div class="why_card">
          <h3>Ручная формовка</h3>
          <p>Каждый кирпич создаётся вручную, поэтому фактура всегда уникальна</p>
        </div>

        <div class="why_card">
          <h3>Высокое качество</h3>
          <p>Проверенные материалы обеспечивают прочность и долговечность</p>
        </div>

        <div class="why_card">
          <h3>Экологичность</h3>
          <p>Продукция производится из натуральных компонентов</p>
        </div>

        <div class="why_card">
          <h3>Морозостойкость</h3>
          <p>Материал подходит для любого климата, включая суровые зимы</p>
        </div>

      </div>

  <img src="{{ asset('rb/img/dc6527e3-2201-4883-9.png') }}" alt="" class="why_bg-img" aria-hidden="true">
      

    </div>
    
  </div>
</section>

<!-- BLOG SECTION -->

<section class="blog-promo">
  <div class="blog-promo_inner">
    <h2 class="blog-promo_title">Блог <span>Real Brick</span></h2>
    <p class="blog-promo_desc">Идеи, советы и вдохновение для архитектуры<br>и интерьеров из кирпича ручной формовки.</p>
    <a href="#" class="blog-promo_btn">Читать блог</a>
  </div>
</section>

<section class="faq">
  <div class="container">
    <h2 class="faq_title">Часто задаваемые вопросы</h2>

    <div class="faq_list">

      <div class="faq_item active">
        <button class="faq_question">
          <span>Что такое минеральный кирпич Real Brick и чем он отличается от обычного кирпича?</span>
          <span class="faq_icon">✕</span>
        </button>
        <div class="faq_answer">
          <p>Real Brick — это кирпич ручной формовки из натуральных минеральных материалов.</p>
          <p>Главное отличие от обычного кирпича — живая фактура и индивидуальный характер каждого элемента. Кирпичи создаются вручную, поэтому поверхность всегда уникальна и выглядит более естественно в архитектуре и интерьере.</p>
        </div>
      </div>

      <div class="faq_item">
        <button class="faq_question">
          <span>Какой вид кирпича и плитки вы продаете?</span>
          <span class="faq_icon">+</span>
        </button>
        <div class="faq_answer">
          <p>Мы предлагаем широкий ассортимент кирпича и плитки ручной формовки различных форматов, цветов и фактур для фасадов и интерьеров.</p>
        </div>
      </div>

      <div class="faq_item">
        <button class="faq_question">
          <span>Какая гарантия предоставляется на вашу продукцию?</span>
          <span class="faq_icon">+</span>
        </button>
        <div class="faq_answer">
          <p>На всю продукцию Real Brick предоставляется гарантия качества. Материалы соответствуют стандартам прочности и морозостойкости.</p>
        </div>
      </div>

      <div class="faq_item">
        <button class="faq_question">
          <span>Какие у вас сроки изготовления и доставки?</span>
          <span class="faq_icon">+</span>
        </button>
        <div class="faq_answer">
          <p>Сроки изготовления зависят от объёма заказа. Доставка осуществляется по всему Казахстану. Уточнить детали можно у менеджера.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<section class="consult">
  <div class="consult_inner">

    <div class="consult_left">
      <h2 class="consult_title">Получите консультацию<br>по вашему проекту</h2>
      <p class="consult_desc">Оставьте заявку — и мы поможем подобрать<br>кирпич для вашего проекта</p>

      <div class="consult_form">
        <input class="consult_input" type="text" placeholder="Ваше имя*">
        <input class="consult_input" type="tel" placeholder="Номер телефона*">
        <textarea class="consult_input consult_textarea" placeholder="Комментарий к проекту"></textarea>
        <button class="consult_submit">Получить консультацию</button>
        <p class="consult_note">Ответим в течение 15 минут</p>
      </div>
    </div>

    <!-- картинку вставишь сам -->
    <div class="consult_right">
      <img src="{{ asset('rb/img/Слой 23.png') }}" alt="" class="consult_img">
    </div>

  </div>
</section>

<section class="contacts">
  <div class="container">
    <div class="contacts_layout">

      <!-- левая колонка -->
      <div class="contacts_left">
        <h2 class="contacts_title">Контакты <span>Real Brick</span></h2>

        <div class="contacts_list">

          <div class="contacts_item">
            <span class="contacts_label">Адрес</span>
            <span class="contacts_value">Казахстан, г. Алматы, ул.Минина 14А</span>
          </div>

          <div class="contacts_item">
            <span class="contacts_label">Телефон</span>
            <a href="tel:+77004446999" class="contacts_value">+7 (700) 444 69 99</a>
          </div>

          <div class="contacts_item">
            <span class="contacts_label">Время работы</span>
            <span class="contacts_value">пн-пт 10:00-20:00</span>
          </div>

          <div class="contacts_item">
            <span class="contacts_label">Эл. почта</span>
            <a href="mailto:info@realbrick.kz" class="contacts_value">info@realbrick.kz</a>
          </div>

        </div>

        <div class="contacts_social">
          <a href="#">
            <img src="{{ asset('rb/img/фейсбук.png') }}" alt="Facebook" width="36">
          </a>
          <a href="#">
            <img src="{{ asset('rb/img/Инстаграм.png') }}" alt="Instagram" width="36">
          </a>
          <a href="#">
            <img src="{{ asset('rb/img/Ватсап.png') }}" alt="WhatsApp" width="36">
          </a>
        </div>
      </div>

      <!-- правая колонка — карта -->
      <div class="contacts_map">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2906.4!2d76.9285!3d43.2567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zReal+Brick+Asia!5e0!3m2!1sru!2skz!4v1"
          width="100%"
          height="100%"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>

    </div>
  </div>
</section>


@endsection

@push('scripts')
@vite('resources/rb/main.js')
@endpush
