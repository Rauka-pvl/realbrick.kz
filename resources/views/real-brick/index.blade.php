@extends('layouts.app')

@section('title', 'REAL BRICK — главная')

@push('styles')
<style>
  body{overflow-x:hidden;}
  /* Force image URLs from Laravel app, not Vite host */
  .header { background-image: url("{{ asset('storage/img/fon.png') }}") !important; }
  .projects::before { background-image: url("{{ asset('storage/img/123123.png') }}") !important; }
  .projects::after { background-image: url("{{ asset('storage/img/123.png') }}") !important; }
  /* .consult { background-image: url("{{ asset('storage/img/pryamougolnik-2-kopiya-28.png') }}") !important; } */
</style>
@endpush

@section('content')
    @vite(['resources/css/main.css', 'resources/css/reset.css'])

    <section class="header pb-10 lg:pb-0 max-[1023px]:pb-4">
    <div class="container 2xl:!max-w-none 2xl:!mx-0 2xl:!px-24 2xl:!w-full max-[1023px]:!max-w-full max-[1023px]:!px-4">
        <div class="header_body">
            <div class="header_body-top flex-col gap-4 text-center lg:flex-row lg:items-center lg:justify-between lg:text-left max-[1023px]:!items-start max-[1023px]:!gap-2 max-[1023px]:!text-left">
              <h1 class="text-[40px] leading-none sm:text-[52px] md:text-[72px] xl:text-[96px] 2xl:text-[130px] max-[1023px]:!text-[64px] max-[420px]:!text-[56px] max-[1023px]:!leading-[0.94]">REAL BRICK</h1>
              <div class="heaeder_body-text max-w-full text-center lg:max-w-[36%] lg:text-left max-[1023px]:hidden">
                <p class="text-[15px] leading-[1.35] sm:text-base lg:text-xl 2xl:text-[23px]">Каждый кирпич формируется <br class="hidden lg:block">  вручную. Поэтому фактура<br class="hidden lg:block"> никогда не повторяется</p>
              </div>
            </div>

          </div>
          <div class="header_footer flex-col items-center gap-6 text-center lg:flex-row lg:items-stretch lg:justify-between lg:text-left max-[1023px]:!items-start max-[1023px]:!gap-3 max-[1023px]:!text-left">
            <div class="header_footer-left">
              <h1 class="text-[20px] sm:text-2xl lg:text-3xl 2xl:text-4xl max-[1023px]:!max-w-[320px] max-[1023px]:!text-[20px] max-[1023px]:!leading-[1.15]">1 В МИРЕ МИНЕРАЛЬНЫЙ КИРПИЧ <br class="hidden lg:block"> РУЧНОЙ ФОРМОВКИ </h1>
              <p class="text-base sm:text-lg lg:text-2xl 2xl:text-3xl max-[1023px]:!text-[16px] max-[1023px]:!leading-[1.15]">для архитектуры <br class="hidden lg:block"> и интерьеров </p>
              <div class="header_footer-buttons w-full items-center lg:w-auto lg:items-start max-[1023px]:!mt-3 max-[1023px]:!w-auto max-[1023px]:!items-start max-[1023px]:!gap-2.5">
                <a href="#" class="btn btn_collection w-full sm:w-auto max-[1023px]:!w-auto max-[1023px]:!min-w-[190px] max-[1023px]:!px-6 max-[1023px]:!py-3 max-[1023px]:!text-[14px]">смотреть коллекцию</a>
                <a href="#" class="btn btn_catalog w-full sm:w-auto max-[1023px]:!w-auto max-[1023px]:!min-w-[190px] max-[1023px]:!px-6 max-[1023px]:!py-3 max-[1023px]:!text-[14px]">скачать каталог</a>
              </div>
            </div>
            <!-- decorative image between left and right footer columns -->
            <img src="{{ asset('storage/img/kirpich.png') }}" alt="" class="header_footer-decor static my-2 w-full max-w-[460px] translate-x-0 translate-y-0 lg:absolute lg:left-[53%] lg:top-[52%] lg:w-[650px] lg:max-w-[650px] lg:-translate-x-1/2 lg:-translate-y-1/2 max-[1023px]:!relative max-[1023px]:!left-auto max-[1023px]:!top-auto max-[1023px]:!mx-auto max-[1023px]:!mt-2 max-[1023px]:!w-[94vw] max-[1023px]:!max-w-[340px] max-[1023px]:!translate-x-0 max-[1023px]:!translate-y-0" aria-hidden="true" width="200px">
            <div class="header_footer-right max-[1023px]:hidden">
              <div class="header_footer-card w-full max-w-[320px] lg:w-auto lg:max-w-none">
                <h1>6%</h1>
                <p>- водопоглощение</p>
              </div>
              <div class="header_footer-card w-full max-w-[320px] lg:w-auto lg:max-w-none">
                <h1>M250</h1>
                <p>- водопоглощение</p>
              </div>
              <div class="header_footer-card w-full max-w-[320px] lg:w-auto lg:max-w-none">
                <h1>F500</h1>
                <p>- водопоглощение</p>
              </div>
            </div>
          </div>

          <div class="relative -mt-20 flex min-h-[220px] items-end justify-center pb-4 lg:hidden">
            <img
              src="{{ asset('storage/img/kamen.png') }}"
              alt=""
              aria-hidden="true"
              class="pointer-events-none absolute bottom-0 left-1/2 z-0 w-[360px] max-w-none -translate-x-1/2"
            >
            <p class="relative z-10 max-w-[270px] text-left text-[15px] leading-[1.35] text-white">
              Каждый кирпич формируется вручную. Поэтому фактура никогда не повторяется
            </p>
          </div>

          <div class="rb-hero-bottom hidden pt-6 text-center lg:absolute lg:bottom-0 lg:left-0 lg:right-0 lg:flex lg:items-end lg:justify-center lg:pt-10 lg:pb-[60px]">
            <img
              src="{{ asset('storage/img/kamen.png') }}"
              alt=""
              aria-hidden="true"
              class="pointer-events-none absolute bottom-0 left-1/2 z-0 w-[900px] max-w-none -translate-x-1/2"
            >
          
            </div>
          </div>
        </div>
      </div>
    </section>

   <section class="usage">
        <div class="usage_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px] mb-5">
          <p>Где используется  <span class="ml-1"> Real Brick</span></p>
        </div>
        <div class="usage_slider">
            <div class="usage_track">

              <div class="usage_card">
                <img src="{{ asset('storage/img/2-blok-kartochki/1.png') }}" alt="Жилые дома">
                <div class="usage_card-content">
                  <h2>Жилые дома</h2>
                  <p>Фасады из кирпича и плитки ручной формовки</p>
                  <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
                </div>
              </div>

              <div class="usage_card">
                <img src="{{ asset('storage/img/2-blok-kartochki/2.png') }}" alt="Интерьеры">
                <div class="usage_card-content">
                  <h2>Интерьеры</h2>
                  <p>Живая фактура кирпича для выразительных интерьеров.</p>
                  <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
                </div>
              </div>

              <div class="usage_card">
                <img src="{{ asset('storage/img/2-blok-kartochki/bani-i-zony-barbekyu.png') }}" alt="Фасады">
                <div class="usage_card-content">
                  <h2>Фасады</h2>
                  <p>Современные решения для внешней отделки зданий.</p>
                  <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
                </div>
              </div>

              <div class="usage_card">
                <img src="{{ asset('storage/img/2-blok-kartochki/kommercheskie-obekty-i-ofisy.png') }}" alt="Коммерческие объекты">
                <div class="usage_card-content">
                  <h2>Коммерческие объекты</h2>
                  <p>Офисы, рестораны и общественные пространства.</p>
                  <a href="#" class="usage_link">смотреть проекты <span>→</span></a>
                </div>
              </div>

              <div class="usage_card">
                    <img src="{{ asset('storage/img/2-blok-kartochki/tehnicheskie-pomescheniya.png') }}" alt="Ландшафт">
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
        <h2 class="collections_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Коллекции <span>Real Brick</span></h2>
        <a href="#" class="collections_all">смотреть весь каталог <span>→</span></a>
      </div>

      <div class="collections_grid">

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-18.png')}}" alt="Кирпич ручной формовки">
          <div class="collections_card-content">
            <h3>Кирпич ручной формовки</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-17.png') }}" alt="Плитка ручной формовки">
          <div class="collections_card-content">
            <h3>Плитка ручной формовки</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-19.png') }}" alt="Декоративные элементы">
          <div class="collections_card-content">
            <h3>Декоративные элементы</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-15.png') }}" alt="Напольное покрытие">
          <div class="collections_card-content">
            <h3>Напольное покрытие</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-19.png') }}" alt="Лимитированные серии">
          <div class="collections_card-content">
            <h3>Лимитированные серии</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-27.png') }}" alt="Черепица">
          <div class="collections_card-content">
            <h3>Черепица</h3>
            <a href="#" class="collections_link">каталог <span>→</span></a>
          </div>
        </div>

        <div class="collections_card">
          <img src="{{ asset('storage/img/sloi-28.png') }}" alt="Сопутствующие материалы">
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
      <img src="{{ asset('storage/img/real-brick-kopiya.png') }}" alt="" class="projects_bg-img">

      <div class="container">
        <div class="projects_layout grid grid-cols-1 gap-6 lg:flex lg:justify-between lg:gap-0">

          <!-- левая колонка -->
          <div class="projects_left">
            <h2 class="projects_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Реализованные<br> проекты <span>Real Brick</span></h2>
            <p class="projects_desc text-[15px] leading-[1.45] lg:text-base">Квартиры, рестораны, коммерческие фасады.<br>Кирпич для проектов со вкусом и стилем</p>

            <div class="projects_stat">
              <span class="projects_stat-num">500+</span>
              <span class="projects_stat-label">реализованных проектов</span>
            </div>

            <div class="projects_geo">
              <img src="{{ asset('storage/img/kazakhstan-flag-national-europe-emblem-icon-illustration-abstract-design-element-free-vector-kopiya.png') }}" alt="Казахстан" width="36" height="36">
              <span>Используется в проектах<br>по всему Казахстану</span>
            </div>

            <a href="{{ route('projects.index') }}" class="projects_portfolio">смотреть портфолио <span>→</span></a>
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
      <h2 class="why_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Почему выбирают <span>Real Brick</span></h2>

      <div class="why_layout grid grid-cols-1 gap-6 lg:flex lg:gap-0">

        <!-- левая колонка -->
        <div class="why_left">
          <p class="why_text text-[15px] leading-[1.45] lg:text-base">
            <span class="why_accent">Real Brick</span> — кирпич ручной формовки
            с уникальной фактурой и характером.
            Мы поставляем материалы для
            архитектуры и интерьеров, где важны
            стиль, качество и долговечность.
          </p>
          <p class="why_text text-[15px] leading-[1.45] lg:text-base">
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

    <img src="{{ asset('storage/img/dc6527e3-2201-4883-9.png') }}" alt="" class="why_bg-img" aria-hidden="true">


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
      <h2 class="faq_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Часто задаваемые вопросы</h2>

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
    <div class="consult_inner grid grid-cols-1 gap-6 lg:flex lg:justify-between lg:gap-0">

      <div class="consult_left">
        <h2 class="consult_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Получите консультацию<br>по вашему проекту</h2>
        <p class="consult_desc text-[15px] leading-[1.45] lg:text-base">Оставьте заявку — и мы поможем подобрать<br>кирпич для вашего проекта</p>

        <form class="consult_form" id="consult-form">
          <input class="consult_input" name="name" type="text" placeholder="Ваше имя*" required>
          <input class="consult_input" name="phone" type="tel" placeholder="Номер телефона*" required>
          <textarea class="consult_input consult_textarea" name="message" placeholder="Комментарий к проекту"></textarea>
          <button class="consult_submit" type="submit">Получить консультацию</button>
          <p class="consult_note">Ответим в течение 15 минут</p>
        </form>
      </div>

      <!-- картинку вставишь сам -->
      <div class="consult_right">
        <img src="{{ asset('storage/img/sloi-23.png') }}" alt="" class="consult_img">
      </div>

    </div>
  </section>

  <section class="contacts">
    <div class="container">
      <div class="contacts_layout grid grid-cols-1 gap-6 lg:flex lg:justify-between lg:gap-0">

        <!-- левая колонка -->
        <div class="contacts_left">
          <h2 class="contacts_title text-[28px] leading-tight sm:text-[32px] lg:text-[36px]">Контакты <span>Real Brick</span></h2>

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
            <a href="https://www.facebook.com/61584804524037/mentions/" target="_blank" rel="noopener noreferrer">
                <img src="{{ asset('storage/img/facebook.png') }}" alt="Facebook" width="36">
            </a>
            <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer">
                <img src="{{ asset('storage/img/instagram.png') }}" alt="Instagram" width="36">
            </a>
            <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer">
              <img src="{{ asset('storage/img/vatsap.png') }}" alt="WhatsApp" width="36">
            </a>
          </div>
        </div>

        <!-- правая колонка — карта -->
        <div class="contacts_map min-h-[320px] lg:min-h-0">
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
@vite('resources/js/main.js')
@endsection
