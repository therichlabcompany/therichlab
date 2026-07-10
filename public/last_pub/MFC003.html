(function (window, document) {
  'use strict';

  if (!window.MyFC) window.MyFC = {};
  var MyFC = window.MyFC;

  function each(list, fn) {
    var i;
    for (i = 0; i < list.length; i++) {
      fn(list[i], i);
    }
  }

  function extendSwiperOptions(base, extra) {
    return Object.assign({}, base || {}, extra || {});
  }

  /* =========================
   * Swiper
   * ========================= */
  MyFC.initSwiper = function (target, navScope, options) {
    if (typeof Swiper === 'undefined') return null;

    var el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return null;

    var prev = navScope ? navScope.querySelector('.swiper-nav-prev') : null;
    var next = navScope ? navScope.querySelector('.swiper-nav-next') : null;

    var nav = prev && next ? { prevEl: prev, nextEl: next } : null;

    var base = { freeMode: false };
    if (nav) base.navigation = nav;

    return new Swiper(el, extendSwiperOptions(base, options));
  };

  /* =========================
   * chip drag
   * ========================= */
  MyFC.initChipRowMouseDrag = function () {
    each(document.querySelectorAll('.chip-scroll > .chip-row'), function (row) {
      if (row.dataset.dragBound) return;
      row.dataset.dragBound = 'true';

      var isDown = false;
      var activePointerId = null;
      var startX = 0;
      var startScroll = 0;
      var dragged = false;
      var suppressClick = false;

      row.addEventListener('pointerdown', function (e) {
        if (e.pointerType === 'touch' || e.button !== 0) return;

        suppressClick = false;

        isDown = true;
        dragged = false;
        activePointerId = e.pointerId;
        startX = e.clientX;
        startScroll = row.scrollLeft;
      });

      row.addEventListener('pointermove', function (e) {
        if (!isDown || activePointerId == null || e.pointerId !== activePointerId) return;

        var gap = e.clientX - startX;
        if (Math.abs(gap) < 5) return;

        dragged = true;
        row.scrollLeft = startScroll - gap;
        row.classList.add('dragging');
        try {
          e.preventDefault();
        } catch (x) {
          /* ignore */
        }
      });

      function finishPointer(e) {
        if (!isDown) return;
        if (e && e.pointerId != null && activePointerId != null && e.pointerId !== activePointerId) return;

        var wasDragged = dragged;

        isDown = false;
        dragged = false;
        activePointerId = null;
        row.classList.remove('dragging');

        if (wasDragged) suppressClick = true;
      }

      row.addEventListener('pointerup', finishPointer);

      row.addEventListener('pointercancel', finishPointer);

      row.addEventListener('click', function (e) {
        if (!suppressClick) return;
        suppressClick = false;
        e.preventDefault();
        e.stopPropagation();
      });
    });
  };

  /* =========================
   * 후기 별점 0.5 단위 (별 .star-unit 의 data-state ↔ CSS)
   * ========================= */
  MyFC.initHalfStarRating = function () {
    each(document.querySelectorAll('.review-rate-stars.star-half'), function (group) {
      if (group.dataset.bound) return;
      group.dataset.bound = 'true';

      var stars = group.querySelectorAll('.star-unit');

      function paint(score) {
        var value = Math.max(0, parseFloat(score) || 0);
        each(stars, function (star, i) {
          var idx = i + 1;
          star.dataset.state = value >= idx ? 'full' : value >= idx - 0.5 ? 'half' : 'empty';
        });
      }

      function getValue() {
        var checked = group.querySelector('input[type="radio"]:checked');
        return checked ? checked.value : 0;
      }

      group.addEventListener('change', function () {
        paint(getValue());
      });

      each(group.querySelectorAll('.star-hit'), function (label) {
        label.addEventListener('pointerenter', function () {
          var input = label.querySelector('input[type="radio"]');
          if (input) paint(input.value);
        });
      });

      group.addEventListener('pointerleave', function () {
        paint(getValue());
      });

      paint(getValue());
    });
  };

  /* =========================
   * 상담·심의 등 공통 날짜 선택 (.consult-date)
   * 마크업: .consult-date > input[type=text][readonly] + .consult-date-picker > head strong + .consult-date-picker-days
   * ========================= */
  (function consultDatePicker() {
    function createState() {
      var today = new Date();
      return {
        today: today,
        currentMonth: new Date(today.getFullYear(), today.getMonth(), 1),
        selectedDate: null,
      };
    }

    function getElements(wrap) {
      var input = wrap.querySelector('input[type="text"]');
      var panel = wrap.querySelector('.consult-date-picker');
      if (!input || !panel) return null;
      var title = panel.querySelector('.consult-date-picker-head strong');
      var daysEl = panel.querySelector('.consult-date-picker-days');
      if (!title || !daysEl) return null;
      return { wrap: wrap, input: input, panel: panel, title: title, daysEl: daysEl };
    }

    function formatDate(d) {
      return d.getFullYear() + '년 ' + (d.getMonth() + 1) + '월 ' + d.getDate() + '일';
    }

    function isSameDate(a, b) {
      return (
        a &&
        b &&
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
      );
    }

    function buildCalendarCells(year, month) {
      var firstDay = new Date(year, month, 1).getDay();
      var lastDate = new Date(year, month + 1, 0).getDate();
      var prevLastDate = new Date(year, month, 0).getDate();
      var cells = [];
      var i;
      for (i = firstDay - 1; i >= 0; i--) {
        var padPrev = prevLastDate - i;
        cells.push({
          day: padPrev,
          offset: true,
          date: new Date(year, month - 1, padPrev),
        });
      }
      for (var d = 1; d <= lastDate; d++) {
        cells.push({
          day: d,
          offset: false,
          date: new Date(year, month, d),
        });
      }
      var nextDay = 1;
      while (cells.length % 7 !== 0) {
        cells.push({
          day: nextDay,
          offset: true,
          date: new Date(year, month + 1, nextDay),
        });
        nextDay++;
      }
      return cells;
    }

    function drawDays(cells, state, els) {
      els.daysEl.innerHTML = '';
      each(cells, function (cell) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'consult-date-picker-day';
        if (cell.offset) btn.classList.add('is-offset');
        if (isSameDate(cell.date, state.today)) btn.classList.add('is-today');
        if (state.selectedDate && isSameDate(cell.date, state.selectedDate)) btn.classList.add('is-selected');
        btn.textContent = String(cell.day);
        btn.setAttribute('data-date-value', cell.date.toISOString().slice(0, 10));
        els.daysEl.appendChild(btn);
      });
    }

    function render(state, els) {
      var year = state.currentMonth.getFullYear();
      var month = state.currentMonth.getMonth();
      els.title.textContent = year + '년 ' + (month + 1) + '월';
      drawDays(buildCalendarCells(year, month), state, els);
    }

    function changeMonth(state, nav) {
      var diff = nav === 'next' ? 1 : -1;
      state.currentMonth = new Date(
        state.currentMonth.getFullYear(),
        state.currentMonth.getMonth() + diff,
        1
      );
    }

    function selectDate(state, els, raw) {
      var picked = new Date(raw);
      state.selectedDate = new Date(picked.getFullYear(), picked.getMonth(), picked.getDate());
      state.currentMonth = new Date(
        state.selectedDate.getFullYear(),
        state.selectedDate.getMonth(),
        1
      );
      els.input.value = formatDate(state.selectedDate);
      els.panel.hidden = true;
    }

    function closeAll() {
      each(document.querySelectorAll('.consult-date-picker'), function (picker) {
        picker.hidden = true;
      });
    }

    function bindInputEvent(els, state) {
      els.input.addEventListener('click', function (e) {
        e.stopPropagation();
        closeAll();
        els.panel.hidden = false;
        render(state, els);
      });
    }

    function bindPanelEvent(els, state) {
      els.panel.addEventListener('click', function (e) {
        var nav = e.target.closest('[data-date-nav]');
        if (nav) {
          changeMonth(state, nav.getAttribute('data-date-nav'));
          render(state, els);
          return;
        }
        var dayBtn = e.target.closest('[data-date-value]');
        if (!dayBtn) return;
        selectDate(state, els, dayBtn.getAttribute('data-date-value'));
      });
    }

    function bindOutsideClick() {
      if (MyFC._consultDateOutsideBound) return;
      MyFC._consultDateOutsideBound = true;
      document.addEventListener('click', function (e) {
        if (e.target.closest('.consult-date-picker')) return;
        if (e.target.closest('.consult-date input')) return;
        closeAll();
      });
    }

    MyFC.initConsultDatePicker = function (root) {
      var scope = root || document;
      each(scope.querySelectorAll('.consult-date'), function (wrap) {
        if (wrap.dataset.consultDateBound) return;
        var els = getElements(wrap);
        if (!els) return;
        wrap.dataset.consultDateBound = 'true';
        var state = createState();
        bindInputEvent(els, state);
        bindPanelEvent(els, state);
      });
      bindOutsideClick();
    };
  })();

  /* =========================
   * init
   * ========================= */
  MyFC.initUi = function () {
    MyFC.initChipRowMouseDrag();
    MyFC.initHalfStarRating();
    MyFC.initConsultDatePicker();

    document.addEventListener('click', function (e) {
      var bookmark = e.target.closest('.c-bookmark-btn');
      if (bookmark) {
        var next = bookmark.getAttribute('aria-pressed') !== 'true';
        each(document.querySelectorAll('.c-bookmark-btn'), function (btn) {
          btn.setAttribute('aria-pressed', next);
          btn.setAttribute('aria-label', next ? '북마크 해제' : '북마크');
        });
        return;
      }

      var toggleItem = e.target.closest('[data-toggle-item]');
      if (!toggleItem) return;
      var toggleGroup = toggleItem.closest('[data-toggle-group]');
      if (!toggleGroup) return;

      each(toggleGroup.querySelectorAll('[data-toggle-item]'), function (el) {
        el.classList.remove('is-active');
      });
      toggleItem.classList.add('is-active');
    });
  };
})(window, document);
