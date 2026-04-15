(function () {
  'use strict';

  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /** Плавный скролл по якорям (дублирует CSS для старых браузеров и учёта шапки) */
  function initSmoothScroll() {
    document.addEventListener('click', function (e) {
      var link = e.target.closest('a[href^="#"]');
      if (!link || link.getAttribute('href') === '#') return;
      var id = link.getAttribute('href').slice(1);
      var el = document.getElementById(id);
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
        block: 'start',
      });
    });
  }

  /** Модалка коллекции */
  function initCollectionModal() {
    var root = document.getElementById('collection-modal');
    if (!root) return;
    var backdrop = document.getElementById('collection-modal-backdrop');
    var panel = document.getElementById('collection-modal-panel');
    var titleEl = document.getElementById('modal-title');
    var descEl = document.getElementById('modal-desc');
    var closeBtn = document.getElementById('collection-modal-close');
    var okBtn = document.getElementById('collection-modal-ok');

    function openModal(data) {
      titleEl.textContent = data.title || '';
      descEl.textContent = data.desc || '';
      root.classList.remove('hidden');
      root.classList.add('flex');
      document.body.classList.add('modal-open');
      requestAnimationFrame(function () {
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
      });
      root.setAttribute('aria-hidden', 'false');
      closeBtn.focus();
    }

    function closeModal() {
      if (root.classList.contains('hidden')) return;
      panel.classList.add('scale-95', 'opacity-0');
      panel.classList.remove('scale-100', 'opacity-100');
      window.setTimeout(function () {
        root.classList.add('hidden');
        root.classList.remove('flex');
        document.body.classList.remove('modal-open');
        root.setAttribute('aria-hidden', 'true');
      }, 280);
    }

    document.querySelectorAll('.collection-card').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var raw = btn.getAttribute('data-collection');
        if (!raw) return;
        try {
          openModal(JSON.parse(raw));
        } catch (err) {
          openModal({ title: 'Коллекция', desc: 'Описание недоступно.' });
        }
      });
    });

    [backdrop, closeBtn, okBtn].forEach(function (node) {
      if (node) node.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !root.classList.contains('hidden')) closeModal();
    });
  }

  /** FAQ accordion: первый открыт */
  function initFaq() {
    var accordion = document.getElementById('faq-accordion');
    if (!accordion) return;

    function setOpen(item, open) {
      var trigger = item.querySelector('.faq-trigger');
      var icon = item.querySelector('.faq-icon');
      if (open) {
        item.classList.add('is-open');
        item.classList.remove('border-white/10');
        item.classList.add('border-gold/50');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
        if (icon) icon.textContent = '−';
      } else {
        item.classList.remove('is-open');
        item.classList.add('border-white/10');
        item.classList.remove('border-gold/50');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        if (icon) icon.textContent = '+';
      }
    }

    var items = accordion.querySelectorAll('.faq-item');
    items.forEach(function (item, index) {
      var trigger = item.querySelector('.faq-trigger');
      if (!trigger) return;
      setOpen(item, index === 0);

      trigger.addEventListener('click', function () {
        var isCurrentlyOpen = item.classList.contains('is-open');
        accordion.querySelectorAll('.faq-item').forEach(function (other) {
          setOpen(other, false);
        });
        if (!isCurrentlyOpen) setOpen(item, true);
      });
    });
  }

  /** Форма: отправка на сервер */
  function initLeadForm() {
    var form = document.getElementById('lead-form-el');
    var success = document.getElementById('form-success');
    if (!form) return;

    var error = document.getElementById('form-error');
    if (!error && success) {
      error = document.createElement('div');
      error.id = 'form-error';
      error.className = 'mt-6 hidden rounded-2xl border border-red-500/30 bg-charcoal/95 p-6 text-center shadow-[0_0_24px_rgba(255,0,0,0.12)]';
      error.setAttribute('role', 'status');
      error.innerHTML =
        '<p class=\"text-lg font-semibold text-offwhite\">Ошибка</p>' +
        '<p class=\"mt-2 text-sm text-muted\" data-error-message></p>';
      success.insertAdjacentElement('afterend', error);
    }

    function clearError() {
      if (error) error.classList.add('hidden');
    }

    function showError(message) {
      if (!error) return;
      var msgEl = error.querySelector('[data-error-message]');
      if (msgEl) msgEl.textContent = message || 'Не удалось отправить заявку. Попробуйте позже.';
      error.classList.remove('hidden');
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError();

      if (success) {
        success.classList.add('hidden');
        success.classList.remove('animate-pulse');
      }

      var nameInput = form.querySelector('input[name="name"]');
      var phoneInput = form.querySelector('input[name="phone"]');
      var commentInput = form.querySelector('input[name="comment"]');

      var payload = {
        name: nameInput ? nameInput.value.trim() : '',
        phone: phoneInput ? phoneInput.value.trim() : '',
        comment: commentInput ? commentInput.value.trim() : '',
      };

      var submitBtn = form.querySelector('button[type="submit"]');
      var prevText = submitBtn ? submitBtn.textContent : null;
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправляем...';
      }

      fetch('/api/leads', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      })
        .then(function (res) {
          if (res.ok) return res.json().catch(function () { return { success: true }; });
          return res.text().then(function (t) {
            throw new Error(t || 'Request failed');
          });
        })
        .then(function () {
          form.classList.add('hidden');
          if (success) {
            success.classList.remove('hidden');
            success.classList.add('animate-pulse');
            window.setTimeout(function () {
              success.classList.remove('animate-pulse');
            }, 600);
          }
        })
        .catch(function (err) {
          form.classList.remove('hidden');
          showError(err && err.message ? err.message : undefined);
          console.error(err);
        })
        .finally(function () {
          if (!submitBtn) return;
          submitBtn.disabled = false;
          if (prevText !== null) submitBtn.textContent = prevText;
        });
    });
  }

  /** Появление секций при скролле */
  function initReveal() {
    if (prefersReducedMotion) {
      document.querySelectorAll('.reveal').forEach(function (el) {
        el.classList.add('visible');
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
    );

    document.querySelectorAll('.reveal').forEach(function (el) {
      observer.observe(el);
    });
  }

  /** Год в футере */
  function initYear() {
    var y = document.getElementById('year');
    if (y) y.textContent = String(new Date().getFullYear());
  }

  /** Карусель: "Где используется Real Brick" */
  function initUsageCarousel() {
    var carousel = document.getElementById('usage-carousel');
    var prevBtn = document.getElementById('usage-prev');
    var nextBtn = document.getElementById('usage-next');
    if (!carousel) return;

    function scrollByAmount(dir) {
      var amount = Math.max(260, Math.floor(carousel.clientWidth * 0.85));
      carousel.scrollBy({
        left: amount * dir,
        top: 0,
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
      });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollByAmount(-1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollByAmount(1); });
  }

  /** Навигационные кружки для секции "Где используется Real Brick" */
  function initUsageDots() {
    var dots = document.querySelectorAll('.usage-dot');
    if (!dots || dots.length === 0) return;

    function setActive(btn) {
      dots.forEach(function (d) {
        d.classList.remove('is-active');
      });
      btn.classList.add('is-active');
    }

    dots.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setActive(btn);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSmoothScroll();
    initCollectionModal();
    initFaq();
    initLeadForm();
    initReveal();
    initYear();
  });
})();
