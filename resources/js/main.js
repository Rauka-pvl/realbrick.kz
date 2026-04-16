/* ── USAGE SLIDER ── */
const track = document.querySelector('.usage_track');
const dots = document.querySelectorAll('.usage_dot');
const cards = document.querySelectorAll('.usage_card');
let current = 0;

function goTo(index) {
  current = index;
  const cardWidth = cards[0].offsetWidth + 24;
  track.style.transform = `translateX(-${cardWidth * index}px)`;
  dots.forEach(d => d.classList.remove('active'));
  dots[index].classList.add('active');
}

dots.forEach(dot => {
  dot.addEventListener('click', () => goTo(+dot.dataset.index));
});

/* drag support — mouse */
let startX = 0, isDragging = false;
track.addEventListener('mousedown', e => { startX = e.clientX; isDragging = true; });
track.addEventListener('mousemove', e => { if (!isDragging) return; });
track.addEventListener('mouseup', e => {
  if (!isDragging) return;
  isDragging = false;
  const diff = startX - e.clientX;
  if (diff > 60 && current < cards.length - 1) goTo(current + 1);
  if (diff < -60 && current > 0) goTo(current - 1);
});
track.addEventListener('mouseleave', () => { isDragging = false; });

/* drag support — touch */
track.addEventListener('touchstart', e => {
  startX = e.touches[0].clientX;
  isDragging = true;
}, { passive: true });
track.addEventListener('touchend', e => {
  if (!isDragging) return;
  isDragging = false;
  const diff = startX - e.changedTouches[0].clientX;
  if (diff > 60 && current < cards.length - 1) goTo(current + 1);
  if (diff < -60 && current > 0) goTo(current - 1);
});

/* ── FAQ ACCORDION ── */
document.querySelectorAll('.faq_question').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.faq_item');
    const isActive = item.classList.contains('active');

    document.querySelectorAll('.faq_item').forEach(el => {
      el.classList.remove('active');
      el.querySelector('.faq_icon').textContent = '+';
    });

    if (!isActive) {
      item.classList.add('active');
      item.querySelector('.faq_icon').textContent = '✕';
    }
  });
});

/* ── ANIMATIONS ── */

document.head.insertAdjacentHTML('beforeend', `<style>
  /* Hero — появление при загрузке */
  .header_body-top h1 {
    animation: rbFadeDown 0.9s cubic-bezier(0.22,1,0.36,1) both;
  }
  .heaeder_body-text {
    animation: rbFadeDown 0.9s 0.18s cubic-bezier(0.22,1,0.36,1) both;
  }
  .header_footer-left {
    animation: rbFadeUp 0.9s 0.32s cubic-bezier(0.22,1,0.36,1) both;
  }
  .header_footer-right {
    animation: rbFadeUp 0.9s 0.44s cubic-bezier(0.22,1,0.36,1) both;
  }
  .header_bottom-text {
    animation: rbFadeUp 0.8s 0.6s cubic-bezier(0.22,1,0.36,1) both;
  }
  .header_inner img:first-child {
    animation: rbFadeDown 0.7s cubic-bezier(0.22,1,0.36,1) both;
  }
  .header_social {
    animation: rbFadeDown 0.7s 0.1s cubic-bezier(0.22,1,0.36,1) both;
  }

  @keyframes rbFadeDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: none; }
  }
  @keyframes rbFadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: none; }
  }

  /* Кирпич — покачивание */
  .header_footer-decor {
    animation: rbFloat 5s ease-in-out infinite;
  }
  @keyframes rbFloat {
    0%, 100% { transform: translate(-50%, -50%) rotate(-1deg); }
    50%       { transform: translate(-50%, -54%) rotate(1deg); }
  }

  /* Scroll-reveal */
  .rb-hidden {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.55s ease, transform 0.55s ease;
  }
  .rb-visible {
    opacity: 1 !important;
    transform: none !important;
  }

  /* Nav hover-линия */
  .nav_link a { position: relative; }
  .nav_link a::after {
    content: '';
    position: absolute;
    left: 0; bottom: -3px;
    width: 0; height: 1px;
    background: #d9b176;
    transition: width 0.3s ease;
  }
  .nav_link a:hover::after { width: 100%; }

  /* Кнопки */
  .btn, .usage_cta, .blog-promo_btn,
  .projects_btn-gold, .projects_btn-outline {
    transition: transform 0.2s ease, background-color 0.3s ease, border-color 0.3s ease;
  }
  .btn:hover, .usage_cta:hover, .blog-promo_btn:hover,
  .projects_btn-gold:hover, .projects_btn-outline:hover {
    transform: scale(1.03);
  }
  .consult_submit {
    transition: transform 0.2s ease, background-color 0.3s ease;
  }
  .consult_submit:hover { transform: scale(1.02); }

  /* Карточки коллекций */
  .collections_card {
    transition: box-shadow 0.35s ease, transform 0.35s ease;
  }
  .collections_card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 36px rgba(217,177,118,0.14);
  }

  /* Why-карточки */
  .why_card {
    transition: border-color 0.35s ease, transform 0.35s ease;
  }
  .why_card:hover {
    border-color: rgba(217,177,118,0.65);
    transform: translateY(-4px);
  }

  /* Contacts items */
  .contacts_item {
    transition: background 0.3s ease, transform 0.3s ease;
  }
  .contacts_item:hover {
    background: #1e1e1e;
    transform: translateX(4px);
  }

  /* Stat counter */
  .projects_stat-num {
    display: inline-block;
    transition: transform 0.3s ease;
  }
  .projects_stat-num:hover { transform: scale(1.08); }
</style>`);

/* Scroll-reveal */
const revealTargets = document.querySelectorAll(
  '.usage_card, .collections_card, .why_card, .faq_item, .contacts_item, .projects_cta-card'
);

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('rb-visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

revealTargets.forEach((el, i) => {
  el.classList.add('rb-hidden');
  el.style.transitionDelay = `${i * 0.06}s`;
  revealObserver.observe(el);
});

/* Счётчик «500+» */
function animateCounter(el, target, suffix) {
  const duration = 1800;
  const start = performance.now();
  const update = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.floor(ease * target) + suffix;
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

const statNum = document.querySelector('.projects_stat-num');
if (statNum) {
  const counterObserver = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) {
      animateCounter(statNum, 500, '+');
      counterObserver.disconnect();
    }
  }, { threshold: 0.5 });
  counterObserver.observe(statNum);
}

/* Параллакс для декоративного кирпича */
const brickDecor = document.querySelector('.header_footer-decor');
if (brickDecor) {
  window.addEventListener('scroll', () => {
    const offset = window.scrollY * 0.12;
    brickDecor.style.transform = `translate(-50%, calc(-50% + ${offset}px))`;
  }, { passive: true });
}




// ── BURGER MENU ──
const burger = document.querySelector('.nav_burger');
const nav = document.querySelector('.nav');

// Создаём оверлей
const overlay = document.createElement('div');
overlay.classList.add('nav_overlay');
document.body.appendChild(overlay);

// Создаём мобильное меню
const mobileNav = document.createElement('nav');
mobileNav.classList.add('nav_mobile');
mobileNav.innerHTML = `
  <button class="nav_mobile-close" aria-label="Закрыть меню">✕</button>
  <ul class="nav_mobile-list">
    <li><a href="#">главная</a></li>
    <li><a href="#">о нас</a></li>
    <li><a href="#">каталог</a></li>
    <li><a href="#">галерея</a></li>
    <li><a href="#">блог</a></li>
    <li><a href="#">контакты</a></li>
  </ul>
  <div class="nav_mobile-social">
    <a href="https://www.facebook.com/61584804524037/mentions/" target="_blank" rel="noopener noreferrer"><img src="/storage/img/facebook.png" alt="Facebook" width="36"></a>
    <a href="https://www.instagram.com/realbrickasia/" target="_blank" rel="noopener noreferrer"><img src="/storage/img/instagram.png" alt="Instagram" width="36"></a>
    <a href="https://wa.me/77004446999" target="_blank" rel="noopener noreferrer"><img src="/storage/img/vatsap.png" alt="WhatsApp" width="36"></a>
  </div>
`;
document.body.appendChild(mobileNav);

function openMenu() {
    mobileNav.classList.add('is-open');
    overlay.classList.add('is-visible');
    burger.classList.add('is-active');
    document.body.style.overflow = 'hidden';
}

function closeMenu() {
    mobileNav.classList.remove('is-open');
    overlay.classList.remove('is-visible');
    burger.classList.remove('is-active');
    document.body.style.overflow = '';
}

burger.addEventListener('click', openMenu);
overlay.addEventListener('click', closeMenu);
mobileNav.querySelector('.nav_mobile-close').addEventListener('click', closeMenu);

// Закрытие по Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
});

// Закрытие при клике на ссылку
mobileNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
});