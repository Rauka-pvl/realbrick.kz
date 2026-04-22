/* ── USAGE SLIDER ── */
const track = document.querySelector('.usage_track');
const dots = document.querySelectorAll('.usage_dot');
let current = 0;

if (track) {
  const originalCards = Array.from(track.querySelectorAll('.usage_card'));
  const total = originalCards.length;

  if (total > 1) {
    // Build 3 logical blocks: [clone all] [original] [clone all]
    // This prevents "empty area" glitches on rapid ±2 navigation.
    const leftBlock = originalCards.map((card) => {
      const clone = card.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      return clone;
    });
    const rightBlock = originalCards.map((card) => {
      const clone = card.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      return clone;
    });

    track.innerHTML = '';
    leftBlock.forEach((n) => track.appendChild(n));
    originalCards.forEach((n) => track.appendChild(n));
    rightBlock.forEach((n) => track.appendChild(n));

    const allCards = Array.from(track.querySelectorAll('.usage_card'));
    let currentTrackIndex = total; // first slide in the middle block
    let startX = 0;
    let isDragging = false;

    const getGap = () => (window.matchMedia('(max-width: 768px)').matches ? 16 : 24);
    const getCardStep = () => allCards[0].offsetWidth + getGap();

    const updateDots = () => {
      const logicalIndex = (currentTrackIndex - 1 + total) % total;
      current = logicalIndex;
      // Navigation dots work as relative controls: -2, -1, 0, +1, +2
      // so the center dot always stays "active".
      if (dots.length >= 5) {
        dots.forEach(d => d.classList.remove('active'));
        dots[2].classList.add('active');
      } else {
        dots.forEach(d => d.classList.remove('active'));
        if (dots[logicalIndex]) dots[logicalIndex].classList.add('active');
      }
    };

    const setPosition = (index, animated = true) => {
      currentTrackIndex = index;
      track.style.transition = animated ? 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
      track.style.transform = `translateX(-${getCardStep() * currentTrackIndex}px)`;
      updateDots();
    };

    const goNext = () => setPosition(currentTrackIndex + 1, true);
    const goPrev = () => setPosition(currentTrackIndex - 1, true);

    // Start from first real card in the center block.
    setPosition(total, false);

    if (dots.length >= 5) {
      const relativeSteps = [-2, -1, 0, 1, 2];
      dots.forEach((dot, index) => {
        dot.dataset.step = String(relativeSteps[index] ?? 0);
        dot.setAttribute('aria-label', relativeSteps[index] === 0
          ? 'Текущий слайд'
          : `Перейти на ${Math.abs(relativeSteps[index])} ${Math.abs(relativeSteps[index]) === 1 ? 'слайд' : 'слайда'} ${relativeSteps[index] > 0 ? 'вперед' : 'назад'}`);
        dot.addEventListener('click', () => {
          const step = Number(dot.dataset.step || '0');
          if (step === 0) return;
          setPosition(currentTrackIndex + step, true);
          restartAutoplay();
        });
      });
    } else {
      dots.forEach(dot => {
        dot.addEventListener('click', () => {
          const logicalIndex = +dot.dataset.index;
          setPosition(total + logicalIndex, true);
          restartAutoplay();
        });
      });
    }

    track.addEventListener('transitionend', () => {
      // Keep cursor in the center block for seamless infinite loop.
      if (currentTrackIndex < total) {
        setPosition(currentTrackIndex + total, false);
      } else if (currentTrackIndex >= total * 2) {
        setPosition(currentTrackIndex - total, false);
      }
    });

    window.addEventListener('resize', () => setPosition(currentTrackIndex, false));

    /* drag support — mouse */
    track.addEventListener('mousedown', e => { startX = e.clientX; isDragging = true; });
    track.addEventListener('mousemove', () => { if (!isDragging) return; });
    track.addEventListener('mouseup', e => {
      if (!isDragging) return;
      isDragging = false;
      const diff = startX - e.clientX;
      if (diff > 60) goNext();
      if (diff < -60) goPrev();
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
      if (diff > 60) goNext();
      if (diff < -60) goPrev();
      if (Math.abs(diff) > 60) restartAutoplay();
    });

    // Infinite auto-loop
    const slider = track.closest('.usage_slider');
    const AUTOPLAY_DELAY = 3500;
    let autoplayId = null;

    function stopAutoplay() {
      if (!autoplayId) return;
      clearInterval(autoplayId);
      autoplayId = null;
    }

    function startAutoplay() {
      stopAutoplay();
      autoplayId = setInterval(() => {
        goNext();
      }, AUTOPLAY_DELAY);
    }

    function restartAutoplay() {
      startAutoplay();
    }

    if (slider) {
      slider.addEventListener('mouseenter', stopAutoplay);
      slider.addEventListener('mouseleave', startAutoplay);
      slider.addEventListener('touchstart', stopAutoplay, { passive: true });
      slider.addEventListener('touchend', startAutoplay);
    }

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) stopAutoplay();
      else startAutoplay();
    });

    startAutoplay();
  }
}

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
if (burger) {
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
}

/* ── CONSULT FORM -> BITRIX ── */
async function sendToBitrix(data) {
  var BITRIX_URL = 'https://realbrick.bitrix24.kz/rest/168/cu47wrs4cd1tbzck/crm.lead.add.json';

  var params = {
    fields: {
      TITLE: 'InnovateX.SITE',
      NAME: data.name,
      PHONE: [{ VALUE: data.phone, VALUE_TYPE: 'WORK' }],
      COMMENTS: data.message || ''
    }
  };

  if (data.file) {
    if (data.file.size > 5 * 1024 * 1024) {
      throw new Error('FILE_TOO_LARGE');
    }

    var base64 = await new Promise((resolve, reject) => {
      var reader = new FileReader();
      reader.onload = () => resolve(reader.result.split(',')[1]);
      reader.onerror = reject;
      reader.readAsDataURL(data.file);
    });

    params.fields.UF_CRM_1775545282 = { fileData: [data.file.name, base64] };
  }

  var response = await fetch(BITRIX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(params)
  });

  var result = await response.json();
  console.log('Bitrix response:', result);

  if (!result.result) {
    console.error('Bitrix error:', result);
    throw new Error('BITRIX_ERROR');
  }
}

function showToast(message, type = 'success') {
  const oldToast = document.getElementById('rb-toast');
  if (oldToast) oldToast.remove();

  const toast = document.createElement('div');
  toast.id = 'rb-toast';
  toast.textContent = message;
  toast.style.position = 'fixed';
  toast.style.left = '50%';
  toast.style.top = '50%';
  toast.style.zIndex = '9999';
  toast.style.padding = '12px 16px';
  toast.style.borderRadius = '12px';
  toast.style.fontSize = '14px';
  toast.style.fontWeight = '600';
  toast.style.backdropFilter = 'blur(6px)';
  toast.style.boxShadow = '0 12px 30px rgba(0,0,0,0.28)';
  toast.style.transition = 'opacity 220ms ease, transform 220ms ease';
  toast.style.opacity = '0';
  toast.style.transform = 'translate(-50%, calc(-50% + 8px))';
  toast.style.color = type === 'success' ? '#111' : '#fff';
  toast.style.background = type === 'success'
    ? 'linear-gradient(135deg, #d9b176, #f0d39a)'
    : 'linear-gradient(135deg, #8d1f1f, #c94343)';

  document.body.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translate(-50%, -50%)';
  });

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translate(-50%, calc(-50% + 8px))';
    setTimeout(() => toast.remove(), 260);
  }, 2600);
}

const consultForm = document.getElementById('consult-form');
if (consultForm) {
  const submitBtn = consultForm.querySelector('.consult_submit');
  const noteEl = consultForm.querySelector('.consult_note');
  const phoneInput = consultForm.querySelector('input[name="phone"]');

  function formatKzPhone(rawValue) {
    const digits = String(rawValue || '').replace(/\D/g, '');
    let local = digits;
    if (local.startsWith('8')) local = `7${local.slice(1)}`;
    if (!local.startsWith('7')) local = `7${local}`;
    local = local.slice(0, 11);

    const d = local.slice(1);
    const p1 = d.slice(0, 3);
    const p2 = d.slice(3, 6);
    const p3 = d.slice(6, 8);
    const p4 = d.slice(8, 10);

    let out = '+7';
    if (p1) out += ` (${p1}`;
    if (p1.length === 3) out += ')';
    if (p2) out += ` ${p2}`;
    if (p3) out += `-${p3}`;
    if (p4) out += `-${p4}`;

    return {
      formatted: out,
      normalized: local.length === 11 ? `+${local}` : '',
      isComplete: local.length === 11,
    };
  }

  if (phoneInput) {
    phoneInput.setAttribute('inputmode', 'tel');
    phoneInput.setAttribute('autocomplete', 'tel');
    phoneInput.setAttribute('placeholder', '+7 (___) ___-__-__');

    const applyMask = () => {
      const masked = formatKzPhone(phoneInput.value);
      phoneInput.value = masked.formatted;
    };

    phoneInput.addEventListener('input', applyMask);
    phoneInput.addEventListener('focus', () => {
      if (!phoneInput.value.trim()) phoneInput.value = '+7';
    });
    phoneInput.addEventListener('blur', () => {
      const masked = formatKzPhone(phoneInput.value);
      if (!masked.isComplete) phoneInput.value = '';
    });
  }

  consultForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(consultForm);
    const name = String(formData.get('name') || '').trim();
    const phoneMasked = formatKzPhone(String(formData.get('phone') || '').trim());
    const phone = phoneMasked.normalized;
    const message = String(formData.get('message') || '').trim();

    if (!name || !phoneMasked.isComplete) {
      if (noteEl) noteEl.textContent = 'Введите номер в формате +7 (___) ___-__-__.';
      return;
    }

    const originalBtnText = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправка...';
    }

    try {
      await sendToBitrix({
        name,
        phone,
        message,
      });

      consultForm.reset();
      if (noteEl) noteEl.textContent = 'Ответим в течение 15 минут';
      showToast('Успешно! Заявка отправлена.', 'success');
    } catch (err) {
      if (noteEl) noteEl.textContent = 'Ошибка отправки. Попробуйте еще раз.';
      showToast(err && err.message ? err.message : 'Ошибка отправки. Попробуйте еще раз.', 'error');
      console.error(err);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
      }
    }
  });
}