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

  /** Калькулятор материалов */
  function initMaterialCalculator() {
    var roomTypeEl = document.getElementById('calc-room-type');
    var lengthEl = document.getElementById('calc-length');
    var widthEl = document.getElementById('calc-width');
    var heightEl = document.getElementById('calc-height');
    var pickerEl = document.getElementById('calc-material-picker');
    var triggerEl = document.getElementById('calc-material-trigger');
    var panelEl = document.getElementById('calc-material-panel');
    var selectedLabelEl = document.getElementById('calc-material-selected-label');
    var searchEl = document.getElementById('calc-product-search');
    var treeEl = document.getElementById('calc-material-tree');
    var dataEl = document.getElementById('calc-tree-data');
    var materialEl = document.getElementById('calc-material');
    if (!roomTypeEl || !lengthEl || !widthEl || !heightEl || !materialEl || !pickerEl || !triggerEl || !panelEl || !selectedLabelEl || !searchEl || !treeEl || !dataEl) return;

    var piecesEl = document.getElementById('calc-total-pieces');
    var priceEl = document.getElementById('calc-total-price');
    var areaEl = document.getElementById('calc-total-area');
    var areaExtraEl = document.getElementById('calc-total-area-extra');
    var mixEl = document.getElementById('calc-mix');
    var materials = [];
    var sections = [];
    var flattenedProducts = [];
    var tree = {};
    var topOrder = [
      'Кирпичи',
      'Напольная программа',
      'Отливы и Колпаки-парапеты',
      'Подсистема ППС',
      'Плитки',
      'Фуга и Клей',
      'Черепица',
    ];

    try {
      var parsed = JSON.parse(dataEl.textContent || '{}');
      materials = Array.isArray(parsed.materials) ? parsed.materials : [];
      sections = Array.isArray(parsed.sections) ? parsed.sections : [];
    } catch (e) {
      materials = [];
      sections = [];
    }

    function esc(v) {
      return String(v || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
      });
    }

    function normalizePath(path) {
      if (!Array.isArray(path) || path.length === 0) return [];
      return path.map(function (part) { return String(part || '').trim(); }).filter(Boolean);
    }

    function ensurePath(path) {
      if (!Array.isArray(path) || path.length === 0) return;
      if (String(path[0] || '').trim().toLowerCase() === 'материалы') return;
      var node = tree;
      path.forEach(function (part) {
        if (!node[part]) node[part] = { children: {}, products: [] };
        node = node[part].children;
      });
    }

    function insertIntoTree(product) {
      ensurePath(product.path);
      if (!Array.isArray(product.path) || product.path.length === 0) return;
      if (String(product.path[0] || '').trim().toLowerCase() === 'материалы') return;

      var leafNode = tree;
      product.path.forEach(function (part) {
        if (!leafNode[part]) leafNode[part] = { children: {}, products: [] };
        if (part === product.path[product.path.length - 1]) {
          leafNode[part].products.push(product);
        } else {
          leafNode = leafNode[part].children;
        }
      });
    }

    function buildTree() {
      tree = {};
      sections.forEach(function (rawPath) {
        var path = normalizePath(rawPath);
        ensurePath(path);
      });
      flattenedProducts = materials.map(function (item) {
        return {
          id: item.id,
          name: item.name || 'Материал',
          price: item.price_value || 0,
          currency: item.price_currency || 'KZT',
          perM2: item.per_m2 || 52,
          path: normalizePath(item.path),
        };
      });
      flattenedProducts.forEach(insertIntoTree);
    }

    function fillHiddenSelect() {
      materialEl.innerHTML = '';
      flattenedProducts.forEach(function (product, idx) {
        var option = document.createElement('option');
        option.value = String(product.id || '');
        option.textContent = String(product.name || 'Материал');
        option.dataset.price = String(product.price || 0);
        option.dataset.perM2 = String(product.perM2 || 52);
        option.dataset.currency = String(product.currency || 'KZT');
        if (idx === 0) option.selected = true;
        materialEl.appendChild(option);
      });
    }

    function selectProduct(productId) {
      var option = Array.prototype.find.call(materialEl.options, function (opt) {
        return String(opt.value) === String(productId);
      });
      if (!option) return;
      option.selected = true;
      selectedLabelEl.textContent = option.textContent;
      recalc();
      panelEl.classList.add('hidden');
    }

    function sortedKeys(node, depth) {
      var keys = Object.keys(node || {}).filter(function (key) {
        return String(key || '').trim().toLowerCase() !== 'материалы';
      });
      if (depth === 0) {
        return keys.sort(function (a, b) {
          var ai = topOrder.indexOf(a); if (ai < 0) ai = 999;
          var bi = topOrder.indexOf(b); if (bi < 0) bi = 999;
          if (ai !== bi) return ai - bi;
          return a.localeCompare(b, 'ru');
        });
      }
      return keys.sort(function (a, b) { return a.localeCompare(b, 'ru'); });
    }

    function renderNode(node, depth, query) {
      var html = '';
      sortedKeys(node, depth).forEach(function (name) {
        var item = node[name];
        var childHtml = renderNode(item.children || {}, depth + 1, query);
        var products = (item.products || []).filter(function (p) {
          return query === '' || String(p.name || '').toLowerCase().indexOf(query) !== -1;
        });

        var productsHtml = products.map(function (p) {
          return '<button type="button" class="calc-tree-product block w-full rounded-lg px-3 py-1.5 text-left text-[13px] text-offwhite/80 transition hover:bg-white/5 hover:text-gold" data-product-id="' + esc(p.id) + '">' + esc(p.name) + '</button>';
        }).join('');

        var emptyHtml = (childHtml === '' && productsHtml === '')
          ? '<div class="ml-4 mt-1 border-l border-white/10 pl-2"><div class="px-3 py-1.5 text-[12px] text-offwhite/45">Пусто</div></div>'
          : '';

        html += ''
          + '<details class="' + (depth > 0 ? 'ml-4 mt-1' : 'mb-1') + '" open>'
          + '<summary class="list-none cursor-pointer rounded-md px-2 py-1.5 ' + (depth === 0 ? 'text-sm font-semibold text-offwhite' : 'text-[13px] font-medium text-offwhite/90') + ' hover:bg-white/5">'
          + '<span class="mr-1 text-offwhite/45">▾</span>' + esc(name)
          + '</summary>'
          + '<div class="' + (depth > 0 ? 'ml-3 border-l border-white/10 pl-2' : '') + '">'
          + childHtml
          + (productsHtml ? '<div class="ml-4 mt-1 border-l border-white/10 pl-2">' + productsHtml + '</div>' : '')
          + emptyHtml
          + '</div>'
          + '</details>';
      });
      return html;
    }

    function renderTree() {
      var query = String(searchEl.value || '').trim().toLowerCase();
      var html = renderNode(tree, 0, query);
      treeEl.innerHTML = html || '<div class="px-2 py-4 text-sm text-offwhite/55">Ничего не найдено</div>';
      treeEl.querySelectorAll('.calc-tree-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
          selectProduct(btn.getAttribute('data-product-id'));
        });
      });
    }

    triggerEl.addEventListener('click', function () {
      panelEl.classList.toggle('hidden');
      if (!panelEl.classList.contains('hidden')) searchEl.focus();
    });
    document.addEventListener('click', function (e) {
      if (!e.target.closest('#calc-material-picker')) panelEl.classList.add('hidden');
    });
    searchEl.addEventListener('input', renderTree);

    function fmt(value, digits) {
      return Number(value || 0).toLocaleString('ru-RU', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
      });
    }

    function recalc() {
      var roomType = roomTypeEl.value;
      var length = Math.max(parseFloat(lengthEl.value) || 0, 0);
      var width = Math.max(parseFloat(widthEl.value) || 0, 0);
      var height = Math.max(parseFloat(heightEl.value) || 0, 0);
      var selectedOption = materialEl.options[materialEl.selectedIndex];
      var perM2 = Math.max(parseFloat(selectedOption && selectedOption.dataset ? selectedOption.dataset.perM2 : '') || 52, 1);
      var price = Math.max(parseFloat(selectedOption && selectedOption.dataset ? selectedOption.dataset.price : '') || 0, 0);
      var currency = (selectedOption && selectedOption.dataset ? selectedOption.dataset.currency : '') || 'KZT';

      var area = roomType === 'floor' ? (length * width) : (2 * (length + width) * height);
      var areaWithReserve = area * 1.05;
      var pieces = Math.ceil(areaWithReserve * perM2);
      var totalPrice = Math.round(pieces * price);
      var mixBags = Math.max(Math.ceil(areaWithReserve * 0.19), 1);

      heightEl.disabled = roomType === 'floor';
      if (heightEl.parentElement) {
        heightEl.parentElement.style.opacity = roomType === 'floor' ? '0.45' : '1';
      }

      if (piecesEl) piecesEl.textContent = fmt(pieces, 0);
      if (priceEl) priceEl.textContent = fmt(totalPrice, 0) + (currency === 'KZT' ? ' ₸' : (' ' + currency));
      if (areaEl) areaEl.textContent = fmt(area, 1) + ' м²';
      if (areaExtraEl) areaExtraEl.textContent = fmt(areaWithReserve, 1) + ' м²';
      if (mixEl) mixEl.textContent = fmt(mixBags, 0) + ' мест (мешков)';
    }

    [roomTypeEl, lengthEl, widthEl, heightEl, materialEl].forEach(function (field) {
      field.addEventListener('input', recalc);
      field.addEventListener('change', recalc);
    });

    buildTree();
    fillHiddenSelect();
    if (materialEl.options.length > 0) {
      selectedLabelEl.textContent = materialEl.options[0].textContent;
    }
    renderTree();
    recalc();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSmoothScroll();
    initCollectionModal();
    initFaq();
    initLeadForm();
    initReveal();
    initYear();
    initMaterialCalculator();
  });
})();
