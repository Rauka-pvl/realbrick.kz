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
    var dataEl = document.getElementById('calc-tree-data');
    if (!roomTypeEl || !lengthEl || !widthEl || !heightEl || !dataEl) return;

    var piecesEl = document.getElementById('calc-total-pieces');
    var priceEl = document.getElementById('calc-total-price');
    var priceDupEl = document.getElementById('calc-total-price-dup');
    var areaEl = document.getElementById('calc-total-area');
    var totalOpeningsEl = document.getElementById('calc-total-openings');
    var netAreaEl = document.getElementById('calc-total-net-area');
    var areaExtraEl = document.getElementById('calc-total-area-extra');
    var packCoverageEl = document.getElementById('calc-pack-coverage');
    var mixEl = document.getElementById('calc-mix');
    var verticalCornersLmEl = document.getElementById('calc-vertical-corners-lm');
    var horizontalCornersLmEl = document.getElementById('calc-horizontal-corners-lm');
    var totalCornersLmEl = document.getElementById('calc-corners-lm-total');
    var verticalCornersQtyEl = document.getElementById('calc-vertical-corners-qty');
    var horizontalCornersQtyEl = document.getElementById('calc-horizontal-corners-qty');
    var wallsPriceEl = document.getElementById('calc-walls-price');
    var cornersPriceEl = document.getElementById('calc-corners-price');
    var infoTileEl = document.getElementById('calc-info-tile');
    var totalWallPiecesEl = document.getElementById('calc-total-wall-pieces');
    var infoVCornerEl = document.getElementById('calc-info-v-corner');
    var infoPackPriceEl = document.getElementById('calc-info-pack-price');
    var infoHCornerEl = document.getElementById('calc-info-h-corner');
    var calcRunBtn = document.getElementById('calc-run');
    var calcResetBtn = document.getElementById('calc-reset');
    var quickAddOpeningBtn = document.getElementById('calc-add-opening-quick');
    var quickAddVerticalBtn = document.getElementById('calc-add-vertical-angle');
    var quickAddHorizontalBtn = document.getElementById('calc-add-horizontal-angle');
    var verticalMinusBtn = document.getElementById('calc-vertical-corners-minus');
    var verticalPlusBtn = document.getElementById('calc-vertical-corners-plus');
    var horizontalMinusBtn = document.getElementById('calc-horizontal-corners-minus');
    var horizontalPlusBtn = document.getElementById('calc-horizontal-corners-plus');

    var wallsContainerEl = document.getElementById('calc-walls');
    var inlineOpeningsEl = document.getElementById('calc-inline-openings');
    var addWallBtn = document.getElementById('calc-add-wall');
    var verticalCornersCountEl = document.getElementById('calc-vertical-corners-count');
    var verticalCornersHeightEl = document.getElementById('calc-vertical-corners-height');
    var horizontalCornersCountEl = document.getElementById('calc-horizontal-corners-count');
    var horizontalCornersLengthEl = document.getElementById('calc-horizontal-corners-length');

    if (!wallsContainerEl || !inlineOpeningsEl || !addWallBtn || !verticalCornersCountEl || !verticalCornersHeightEl || !horizontalCornersCountEl || !horizontalCornersLengthEl) return;

    var wallMaterials = [];
    var verticalCornerMaterials = [];
    var horizontalCornerMaterials = [];
    var sections = [];
    var walls = [];
    var wallsVisible = false;

    try {
      var parsed = JSON.parse(dataEl.textContent || '{}');
      wallMaterials = Array.isArray(parsed.materials) ? parsed.materials : [];
      verticalCornerMaterials = Array.isArray(parsed.verticalCornerMaterials) ? parsed.verticalCornerMaterials : [];
      horizontalCornerMaterials = Array.isArray(parsed.horizontalCornerMaterials) ? parsed.horizontalCornerMaterials : [];
      sections = Array.isArray(parsed.sections) ? parsed.sections : [];
    } catch (e) {
      wallMaterials = [];
      verticalCornerMaterials = [];
      horizontalCornerMaterials = [];
      sections = [];
    }

    function normalizePath(path) {
      return Array.isArray(path) ? path.map(function (part) { return String(part || '').trim(); }).filter(Boolean) : [];
    }

    function normalizeItem(item) {
      var dims = item.corner_dims || null;
      return {
        id: item.id,
        name: item.name || 'Материал',
        article: item.article || '',
        currency: (item.price_currency || 'USD').toUpperCase(),
        price: Math.max(parseFloat(item.price_value) || 0, 0),
        perM2: Math.max(parseFloat(item.per_m2) || 0, 0),
        packSize: Math.max(parseFloat(item.pack_size) || 0, 0),
        cornerHeightM: dims && dims.height_m ? Math.max(parseFloat(dims.height_m) || 0, 0) : 0,
        cornerWidthM: dims && dims.width_m ? Math.max(parseFloat(dims.width_m) || 0, 0) : 0,
        path: normalizePath(item.path || []),
      };
    }

    function fmt(value, digits) {
      return Number(value || 0).toLocaleString('ru-RU', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
      });
    }

    function formatCurrency(value, currency) {
      var suffix = currency === 'USD' ? ' $' : (currency === 'KZT' ? ' ₸' : (' ' + currency));
      return fmt(value, 0) + suffix;
    }

    function createPicker(config) {
      var triggerEl = document.getElementById(config.triggerId);
      var panelEl = document.getElementById(config.panelId);
      var searchEl = document.getElementById(config.searchId);
      var listEl = document.getElementById(config.treeId);
      var labelEl = document.getElementById(config.labelId);
      var selectEl = document.getElementById(config.selectId);
      var rootEl = document.getElementById(config.rootId);
      if (!triggerEl || !panelEl || !searchEl || !listEl || !labelEl || !selectEl || !rootEl) return null;

      var items = (config.items || []).map(normalizeItem);

      selectEl.innerHTML = '';
      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Выберите материал';
      placeholder.selected = true;
      selectEl.appendChild(placeholder);

      items.forEach(function (item) {
        var option = document.createElement('option');
        option.value = String(item.id || '');
        option.textContent = item.name;
        option.dataset.price = String(item.price || 0);
        option.dataset.currency = item.currency || 'USD';
        option.dataset.perM2 = String(item.perM2 || 0);
        option.dataset.packSize = String(item.packSize || 0);
        option.dataset.cornerHeightM = String(item.cornerHeightM || 0);
        option.dataset.cornerWidthM = String(item.cornerWidthM || 0);
        option.dataset.article = item.article || '';
        selectEl.appendChild(option);
      });

      function escapeHtml(value) {
        return String(value || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;');
      }

      function buildGroupedTree(list) {
        var root = { children: {}, products: [] };
        list.forEach(function (item) {
          var path = Array.isArray(item.path) ? item.path.filter(Boolean) : [];
          var node = root;
          path.forEach(function (part) {
            if (!node.children[part]) {
              node.children[part] = { children: {}, products: [] };
            }
            node = node.children[part];
          });
          node.products.push(item);
        });
        return root;
      }

      function renderGroupedNode(node, depth) {
        var html = '';
        var childKeys = Object.keys(node.children || {}).sort(function (a, b) {
          return a.localeCompare(b, 'ru');
        });

        childKeys.forEach(function (groupName) {
          var child = node.children[groupName];
          var childHtml = renderGroupedNode(child, depth + 1);
          var productsHtml = (child.products || []).map(function (item) {
            var article = item.article ? (' <span class="text-white/40">· ' + escapeHtml(item.article) + '</span>') : '';
            return '<button type="button" class="calc-list-item block w-full rounded-lg px-3 py-2 text-left text-[13px] text-white/85 transition hover:bg-white/5 hover:text-gold" data-id="' + String(item.id) + '">' + escapeHtml(item.name) + article + '</button>';
          }).join('');

          var openAttr = depth < 1 ? ' open' : '';
          html += ''
            + '<details class="' + (depth > 0 ? 'ml-3 mt-1' : 'mt-1') + '"' + openAttr + '>'
            + '<summary class="list-none cursor-pointer rounded-md px-2 py-1.5 text-[12px] font-semibold text-white/85 hover:bg-white/5">'
            + '<span class="mr-1 text-white/45">▸</span>' + escapeHtml(groupName)
            + '</summary>'
            + '<div class="' + (depth >= 0 ? 'ml-2 border-l border-white/10 pl-2' : '') + '">'
            + childHtml
            + productsHtml
            + '</div>'
            + '</details>';
        });

        if (depth === 0) {
          html += (node.products || []).map(function (item) {
            var article = item.article ? (' <span class="text-white/40">· ' + escapeHtml(item.article) + '</span>') : '';
            return '<button type="button" class="calc-list-item block w-full rounded-lg px-3 py-2 text-left text-[13px] text-white/85 transition hover:bg-white/5 hover:text-gold" data-id="' + String(item.id) + '">' + escapeHtml(item.name) + article + '</button>';
          }).join('');
        }

        return html;
      }

      function render() {
        var q = String(searchEl.value || '').trim().toLowerCase();
        var filtered = items.filter(function (item) {
          if (q === '') return true;
          var pathText = (Array.isArray(item.path) ? item.path.join(' / ') : '').toLowerCase();
          return String(item.name || '').toLowerCase().indexOf(q) !== -1
            || String(item.article || '').toLowerCase().indexOf(q) !== -1
            || pathText.indexOf(q) !== -1;
        });
        if (filtered.length === 0) {
          listEl.innerHTML = '<div class="px-2 py-4 text-sm text-offwhite/55">Ничего не найдено</div>';
          return;
        }
        var tree = buildGroupedTree(filtered);
        listEl.innerHTML = renderGroupedNode(tree, 0);

        listEl.querySelectorAll('.calc-list-item').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            var option = Array.prototype.find.call(selectEl.options, function (opt) {
              return String(opt.value) === String(id);
            });
            if (!option) return;
            option.selected = true;
            labelEl.textContent = option.textContent;
            labelEl.title = option.textContent;
            panelEl.classList.add('hidden');
            recalc();
          });
        });
      }

      triggerEl.addEventListener('click', function () {
        panelEl.classList.toggle('hidden');
        if (!panelEl.classList.contains('hidden')) searchEl.focus();
      });
      searchEl.addEventListener('input', render);
      document.addEventListener('click', function (e) {
        if (!e.target.closest('#' + config.rootId)) panelEl.classList.add('hidden');
      });

      render();
      return {
        selectEl: selectEl,
      };
    }

    var wallPicker = createPicker({
      rootId: 'calc-material-picker',
      triggerId: 'calc-material-trigger',
      panelId: 'calc-material-panel',
      searchId: 'calc-product-search',
      treeId: 'calc-material-tree',
      labelId: 'calc-material-selected-label',
      selectId: 'calc-material',
      items: wallMaterials,
    });

    var verticalPicker = createPicker({
      rootId: 'calc-vertical-picker',
      triggerId: 'calc-vertical-trigger',
      panelId: 'calc-vertical-panel',
      searchId: 'calc-vertical-search',
      treeId: 'calc-vertical-tree',
      labelId: 'calc-vertical-selected-label',
      selectId: 'calc-vertical-material',
      items: verticalCornerMaterials,
    });

    var horizontalPicker = createPicker({
      rootId: 'calc-horizontal-picker',
      triggerId: 'calc-horizontal-trigger',
      panelId: 'calc-horizontal-panel',
      searchId: 'calc-horizontal-search',
      treeId: 'calc-horizontal-tree',
      labelId: 'calc-horizontal-selected-label',
      selectId: 'calc-horizontal-material',
      items: horizontalCornerMaterials,
    });

    if (!wallPicker || !verticalPicker || !horizontalPicker) return;

    function createWall(width, height) {
      return {
        width: Math.max(parseFloat(width) || 0, 0),
        height: Math.max(parseFloat(height) || 0, 0),
        openings: [],
      };
    }

    function createOpening(defaultName) {
      return { name: defaultName || 'Проем', width: 0, height: 0 };
    }

    function renderInlineOpenings() {
      inlineOpeningsEl.innerHTML = '';
      if (!walls[0] || !Array.isArray(walls[0].openings) || walls[0].openings.length === 0) return;

      walls[0].openings.forEach(function (opening, openingIndex) {
        var row = document.createElement('div');
        row.className = 'rb-calc-inline-opening';
        row.innerHTML = ''
          + '<div class="rb-calc-inline-opening-head">'
          + '  <span>Проем ' + (openingIndex + 1) + '</span>'
          + '  <button type="button" data-inline-action="remove-opening" data-opening="' + openingIndex + '" class="rb-calc-inline-opening-remove">удалить</button>'
          + '</div>'
          + '<div class="rb-calc-inline-opening-grid">'
          + '  <input data-inline-action="opening-width" data-opening="' + openingIndex + '" type="number" min="0" step="0.01" value="' + (opening.width || 0) + '" placeholder="Ширина (м)" class="rb-calc-inline-opening-input">'
          + '  <input data-inline-action="opening-height" data-opening="' + openingIndex + '" type="number" min="0" step="0.01" value="' + (opening.height || 0) + '" placeholder="Высота (м)" class="rb-calc-inline-opening-input">'
          + '</div>';
        inlineOpeningsEl.appendChild(row);
      });
    }

    function renderWalls() {
      wallsContainerEl.innerHTML = '';
      wallsContainerEl.classList.add('hidden');
    }

    function updateWallMetricsView() {}

    function syncWallsByPerimeter() {
      var l = Math.max(parseFloat(lengthEl.value) || 0, 0);
      var w = Math.max(parseFloat(widthEl.value) || 0, 0);
      var h = Math.max(parseFloat(heightEl.value) || 0, 0);
      if (walls.length === 0) {
        walls.push(createWall(l || w, h));
      }
      walls[0].width = Math.max(l || w, 0);
      walls[0].height = h;
      renderInlineOpenings();
      renderWalls();
    }

    function recalc() {
      var wallOption = wallPicker.selectEl.options[wallPicker.selectEl.selectedIndex];
      var verticalOption = verticalPicker.selectEl.options[verticalPicker.selectEl.selectedIndex];
      var horizontalOption = horizontalPicker.selectEl.options[horizontalPicker.selectEl.selectedIndex];

      var totalWallArea = walls.reduce(function (sum, wall) {
        return sum + Math.max((wall.width || 0) * (wall.height || 0), 0);
      }, 0);
      var totalOpeningsArea = walls.reduce(function (sum, wall) {
        var openingSum = (wall.openings || []).reduce(function (s, o) {
          return s + Math.max((parseFloat(o.width) || 0) * (parseFloat(o.height) || 0), 0);
        }, 0);
        return sum + openingSum;
      }, 0);
      var netArea = Math.max(totalWallArea - totalOpeningsArea, 0);
      var areaWithReserve = netArea * 1.05;

      var packs = 0;
      var wallsPrice = 0;
      var currency = 'USD';
      var packPieces = 0;
      var piecesPerM2 = 0;
      var totalWallPieces = 0;
      if (wallOption && wallOption.value) {
        packPieces = Math.max(parseFloat(wallOption.dataset.packSize || '0') || 0, 0);
        piecesPerM2 = Math.max(parseFloat(wallOption.dataset.perM2 || '0') || 0, 0);
        var wallPackPrice = Math.max(parseFloat(wallOption.dataset.price || '0') || 0, 0);
        currency = (wallOption.dataset.currency || 'USD').toUpperCase();
        if (piecesPerM2 > 0) {
          totalWallPieces = areaWithReserve * piecesPerM2;
        }
        if (packPieces > 0 && totalWallPieces > 0) {
          packs = Math.ceil(totalWallPieces / packPieces);
          wallsPrice = packs * wallPackPrice;
        }
      }

      var verticalLm = Math.max((parseFloat(verticalCornersCountEl.value) || 0) * (parseFloat(verticalCornersHeightEl.value) || 0), 0);
      var horizontalLm = Math.max((parseFloat(horizontalCornersCountEl.value) || 0) * (parseFloat(horizontalCornersLengthEl.value) || 0), 0);

      var verticalPrice = 0;
      var verticalQty = 0;
      if (verticalOption && verticalOption.value) {
        var verticalRatePerLm = Math.max(parseFloat(verticalOption.dataset.perM2 || '0') || 0, 0);
        var verticalUnitPrice = Math.max(parseFloat(verticalOption.dataset.price || '0') || 0, 0);
        if (verticalRatePerLm > 0) {
          verticalQty = Math.ceil(verticalLm * verticalRatePerLm);
          verticalPrice = verticalQty * verticalUnitPrice;
        }
      }

      var horizontalPrice = 0;
      var horizontalQty = 0;
      if (horizontalOption && horizontalOption.value) {
        var horizontalRatePerLm = Math.max(parseFloat(horizontalOption.dataset.perM2 || '0') || 0, 0);
        var horizontalUnitPrice = Math.max(parseFloat(horizontalOption.dataset.price || '0') || 0, 0);
        if (horizontalRatePerLm > 0) {
          horizontalQty = Math.ceil(horizontalLm * horizontalRatePerLm);
          horizontalPrice = horizontalQty * horizontalUnitPrice;
        }
      }

      var cornersPrice = verticalPrice + horizontalPrice;
      var totalPrice = wallsPrice + cornersPrice;

      if (piecesEl) piecesEl.textContent = fmt(packs, 0) + ' уп';
      if (priceEl) priceEl.textContent = formatCurrency(totalPrice, currency);
      if (priceDupEl) priceDupEl.textContent = formatCurrency(totalPrice, currency);
      if (areaEl) areaEl.textContent = fmt(totalWallArea, 2) + ' м²';
      if (totalOpeningsEl) totalOpeningsEl.textContent = fmt(totalOpeningsArea, 2) + ' м²';
      if (netAreaEl) netAreaEl.textContent = fmt(netArea, 2) + ' м²';
      if (areaExtraEl) areaExtraEl.textContent = fmt(areaWithReserve, 2) + ' м²';
      if (packCoverageEl) packCoverageEl.textContent = fmt(packPieces, 0) + ' шт';
      if (mixEl) mixEl.textContent = fmt(packs, 0) + ' уп';
      if (verticalCornersLmEl) verticalCornersLmEl.textContent = fmt(verticalLm, 2) + ' п.м.';
      if (horizontalCornersLmEl) horizontalCornersLmEl.textContent = fmt(horizontalLm, 2) + ' п.м.';
      if (totalCornersLmEl) totalCornersLmEl.textContent = fmt(verticalQty + horizontalQty, 0) + ' шт';
      if (verticalCornersQtyEl) verticalCornersQtyEl.textContent = fmt(verticalQty, 0) + ' шт';
      if (horizontalCornersQtyEl) horizontalCornersQtyEl.textContent = fmt(horizontalQty, 0) + ' шт';
      if (wallsPriceEl) wallsPriceEl.textContent = formatCurrency(wallsPrice, currency);
      if (cornersPriceEl) cornersPriceEl.textContent = formatCurrency(cornersPrice, currency);
      if (infoTileEl) infoTileEl.textContent = fmt(piecesPerM2, 2) + ' шт/м²';
      if (totalWallPiecesEl) totalWallPiecesEl.textContent = fmt(totalWallPieces, 0) + ' шт';
      if (infoVCornerEl) infoVCornerEl.textContent = fmt(verticalLm, 2) + ' п.м.';
      if (infoPackPriceEl) infoPackPriceEl.textContent = formatCurrency(packs > 0 ? (wallsPrice / packs) : 0, currency);
      if (infoHCornerEl) infoHCornerEl.textContent = fmt(horizontalLm, 2) + ' п.м.';
    }

    inlineOpeningsEl.addEventListener('input', function (e) {
      var actionNode = e.target && e.target.closest ? e.target.closest('[data-inline-action]') : null;
      if (!actionNode || !walls[0] || !Array.isArray(walls[0].openings)) return;
      var action = actionNode.dataset.inlineAction || '';
      var openingIndex = parseInt(actionNode.getAttribute('data-opening'), 10);
      if (!Number.isInteger(openingIndex) || !walls[0].openings[openingIndex]) return;
      if (action === 'opening-width') walls[0].openings[openingIndex].width = Math.max(parseFloat(actionNode.value) || 0, 0);
      if (action === 'opening-height') walls[0].openings[openingIndex].height = Math.max(parseFloat(actionNode.value) || 0, 0);
      recalc();
    });

    inlineOpeningsEl.addEventListener('click', function (e) {
      var actionNode = e.target && e.target.closest ? e.target.closest('[data-inline-action]') : null;
      if (!actionNode || !walls[0] || !Array.isArray(walls[0].openings)) return;
      var action = actionNode.dataset.inlineAction || '';
      var openingIndex = parseInt(actionNode.getAttribute('data-opening'), 10);
      if (action === 'remove-opening' && Number.isInteger(openingIndex) && walls[0].openings[openingIndex]) {
        walls[0].openings.splice(openingIndex, 1);
        renderInlineOpenings();
        recalc();
      }
    });

    addWallBtn.addEventListener('click', function () {
      var baseHeight = Math.max(parseFloat(heightEl.value) || 0, 0);
      var baseWidth = Math.max(parseFloat(lengthEl.value) || 0, 0);
      wallsVisible = true;
      walls.push(createWall(baseWidth, baseHeight));
      renderWalls();
      recalc();
    });

    if (calcRunBtn) {
      calcRunBtn.addEventListener('click', function () {
        recalc();
      });
    }

    if (quickAddOpeningBtn) {
      quickAddOpeningBtn.addEventListener('click', function () {
        if (walls.length === 0) walls.push(createWall(Math.max(parseFloat(lengthEl.value) || 0, 0), Math.max(parseFloat(heightEl.value) || 0, 0)));
        wallsVisible = true;
        walls[0].openings.push(createOpening('Проем ' + (walls[0].openings.length + 1)));
        renderInlineOpenings();
        renderWalls();
        recalc();
      });
    }

    if (quickAddVerticalBtn) {
      quickAddVerticalBtn.addEventListener('click', function () {
        verticalCornersCountEl.value = String(Math.max((parseInt(verticalCornersCountEl.value, 10) || 0) + 1, 0));
        recalc();
      });
    }

    if (quickAddHorizontalBtn) {
      quickAddHorizontalBtn.addEventListener('click', function () {
        horizontalCornersCountEl.value = String(Math.max((parseInt(horizontalCornersCountEl.value, 10) || 0) + 1, 0));
        recalc();
      });
    }

    if (calcResetBtn) {
      calcResetBtn.addEventListener('click', function () {
        lengthEl.value = '10';
        widthEl.value = '10';
        heightEl.value = '3';
        verticalCornersCountEl.value = '4';
        verticalCornersHeightEl.value = '3';
        horizontalCornersCountEl.value = '2';
        horizontalCornersLengthEl.value = '10';
        wallPicker.selectEl.selectedIndex = 0;
        verticalPicker.selectEl.selectedIndex = 0;
        horizontalPicker.selectEl.selectedIndex = 0;

        var wallLabel = document.getElementById('calc-material-selected-label');
        var vLabel = document.getElementById('calc-vertical-selected-label');
        var hLabel = document.getElementById('calc-horizontal-selected-label');
        if (wallLabel) wallLabel.textContent = 'Выберите материал';
        if (vLabel) vLabel.textContent = 'Выберите материал';
        if (hLabel) hLabel.textContent = 'Выберите материал';

        syncWallsByPerimeter();
        walls.forEach(function (wall) {
          wall.openings = [];
        });
        wallsVisible = false;
        renderInlineOpenings();
        renderWalls();
        recalc();
      });
    }

    function applyStepper(inputEl, delta) {
      var next = Math.max((parseInt(inputEl.value, 10) || 0) + delta, 0);
      inputEl.value = String(next);
      recalc();
    }

    if (verticalMinusBtn) verticalMinusBtn.addEventListener('click', function () { applyStepper(verticalCornersCountEl, -1); });
    if (verticalPlusBtn) verticalPlusBtn.addEventListener('click', function () { applyStepper(verticalCornersCountEl, 1); });
    if (horizontalMinusBtn) horizontalMinusBtn.addEventListener('click', function () { applyStepper(horizontalCornersCountEl, -1); });
    if (horizontalPlusBtn) horizontalPlusBtn.addEventListener('click', function () { applyStepper(horizontalCornersCountEl, 1); });

    [roomTypeEl, lengthEl, widthEl, heightEl].forEach(function (field) {
      field.addEventListener('input', syncWallsByPerimeter);
      field.addEventListener('change', syncWallsByPerimeter);
    });

    if (widthEl && widthEl.type === 'hidden') {
      var mirrorWidth = function () {
        widthEl.value = String(Math.max(parseFloat(lengthEl.value) || 0, 0));
      };
      lengthEl.addEventListener('input', mirrorWidth);
      lengthEl.addEventListener('change', mirrorWidth);
      mirrorWidth();
    }

    [verticalCornersCountEl, verticalCornersHeightEl, horizontalCornersCountEl, horizontalCornersLengthEl, wallPicker.selectEl, verticalPicker.selectEl, horizontalPicker.selectEl].forEach(function (field) {
      field.addEventListener('input', recalc);
      field.addEventListener('change', recalc);
    });

    if (sections.length === 0) {
      // keep linter happy, sections are passed for future grouped rendering
    }

    syncWallsByPerimeter();
    renderInlineOpenings();
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
