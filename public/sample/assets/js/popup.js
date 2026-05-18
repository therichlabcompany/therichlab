/**
 * popup.js — 중앙 모달(.c-modal) + 토스트
 *
 * 마크업 훅 예:
 * - 열기: [data-popup-target="#팝업id"] + 트리거에 [data-popup-sync="#hidden입력"] (선택)
 * - 닫기: [data-popup-close], 확인: [data-popup-confirm]
 * - 옵션: .c-modal-option + data-value, 복수 선택 시 팝업에 data-popup-multiselect
 */
(function (window, document) {
  'use strict';

  if (!window.MyFC) window.MyFC = {};
  var MyFC = window.MyFC;

  function getOptionValue(el) {
    var v = el.getAttribute('data-value');
    return v == null ? '' : String(v);
  }

  /* toast (기본 + error) */
  var TOAST_TYPE_WHITELIST = { error: 1 };
  var DEFAULT_TOAST_MS = 2800;
  var TOAST_OUT_MS = 0.22 * 1000 + 80;

  function normalizeToastDurationMs(raw) {
    var n = typeof raw === 'number' ? raw : parseInt(raw, 10);
    if (!isFinite(n) || n < 0) return DEFAULT_TOAST_MS;
    if (n > 60000) return 60000;
    return n;
  }

  function sanitizeToastType(raw) {
    if (raw == null || raw === '') return '';
    var s = String(raw).toLowerCase().replace(/[^a-z]/g, '');
    return TOAST_TYPE_WHITELIST[s] ? s : '';
  }

  function getToastHost() {
    var host = document.querySelector('#app-toast-host');
    if (!host) {
      host = document.createElement('div');
      host.id = 'app-toast-host';
      host.className = 'app-toast-host';
      document.body.appendChild(host);
    }
    return host;
  }

  function removeToastAfterHide(toast) {
    var removed = false;
    function removeNow() {
      if (removed) return;
      removed = true;
      toast.remove();
    }

    var fallback = window.setTimeout(removeNow, TOAST_OUT_MS);

    function onTransitionEnd(ev) {
      if (ev.target !== toast) return;
      if (ev.propertyName !== 'opacity' && ev.propertyName !== 'transform') return;
      toast.removeEventListener('transitionend', onTransitionEnd);
      window.clearTimeout(fallback);
      removeNow();
    }

    toast.addEventListener('transitionend', onTransitionEnd);
  }

  MyFC.showToast = function (message, options) {
    var msg = message == null ? '' : String(message);
    if (!msg.trim()) return;

    var opts = options || {};
    var duration = normalizeToastDurationMs(opts.durationMs);
    var typeKey = sanitizeToastType(opts.type);
    var typeClass = typeKey ? ' ' + typeKey : '';

    var toast = document.createElement('div');
    toast.className = 'app-toast' + typeClass;
    toast.textContent = msg;

    getToastHost().appendChild(toast);

    window.requestAnimationFrame(function () {
      toast.classList.add('is-shown');
    });

    window.setTimeout(function () {
      toast.classList.remove('is-shown');
      removeToastAfterHide(toast);
    }, duration);
  };

  function handleToastButton(btn) {
    var msg = btn.dataset.toast;
    if (!msg) return;
    MyFC.showToast(msg, {
      type: btn.dataset.toastType,
      durationMs: parseInt(btn.dataset.toastMs, 10),
    });
  }

  /* 열린 .c-modal이 있을 때만 body.popup-open (스크롤 잠금) */
  function toggleBodyLock() {
    document.body.classList.toggle('popup-open', !!document.querySelector('.c-modal.is-open'));
  }

  function closeAll() {
    each(document.querySelectorAll('.c-modal.is-open'), function (node) {
      node.classList.remove('is-open');
    });
    toggleBodyLock();
  }

  function each(list, fn) {
    var i;
    for (i = 0; i < list.length; i++) fn(list[i], i);
  }

  /* .c-modal만 열림; 잘못된 셀렉터는 무시 */
  function open(btn) {
    var target = btn.getAttribute('data-popup-target');
    var popup = target ? document.querySelector(target) : null;

    if (!popup || !popup.classList.contains('c-modal')) return;

    closeAll();
    popup.classList.add('is-open');
    toggleBodyLock();
    syncState(popup, btn);
  }

  /* 열 때: hidden 값 기준으로 옵션 is-selected 동기화 (단일 / 복수) */
  function syncState(popup, btn) {
    var syncSelector = btn.getAttribute('data-popup-sync');
    var input = syncSelector ? document.querySelector(syncSelector) : null;
    var value = input && 'value' in input ? String(input.value) : '';
    var options = popup.querySelectorAll('.c-modal-option');
    var i;
    var optionValue;
    var found = false;
    var values;
    var selectedMap;

    if (popup.hasAttribute('data-popup-multiselect')) {
      values = value
        .split(',')
        .map(function (item) {
          return item.trim();
        })
        .filter(function (item) {
          return !!item;
        });

      selectedMap = {};
      for (i = 0; i < values.length; i++) {
        selectedMap[values[i]] = true;
      }

      for (i = 0; i < options.length; i++) {
        optionValue = options[i].getAttribute('data-value');
        optionValue = optionValue == null ? '' : String(optionValue);

        if (values.length === 0) {
          options[i].classList.toggle('is-selected', optionValue === '');
        } else {
          options[i].classList.toggle('is-selected', optionValue !== '' && !!selectedMap[optionValue]);
        }
      }

      return;
    }

    for (i = 0; i < options.length; i++) {
      optionValue = options[i].getAttribute('data-value');
      optionValue = optionValue == null ? '' : String(optionValue);

      if (optionValue === value) {
        options[i].classList.add('is-selected');
        found = true;
      } else {
        options[i].classList.remove('is-selected');
      }
    }

    if (!found && options.length > 0) {
      options[0].classList.add('is-selected');
    }
  }

  /* is-selected만 변경 (확인 전까지 input/라벨 미반영). 열린 모달에서만 동작 */
  function selectOption(popup, optionBtn) {
    var options = popup.querySelectorAll('.c-modal-option');
    var i;
    var isMulti = popup.hasAttribute('data-popup-multiselect');
    var value;
    var hasSelected;
    var emptyOpt;

    if (!popup || !popup.classList.contains('is-open')) return;

    if (!isMulti) {
      for (i = 0; i < options.length; i++) {
        options[i].classList.remove('is-selected');
      }
      optionBtn.classList.add('is-selected');
      return;
    }

    value = getOptionValue(optionBtn);

    if (value === '') {
      for (i = 0; i < options.length; i++) {
        options[i].classList.remove('is-selected');
      }
      optionBtn.classList.add('is-selected');
      return;
    }

    for (i = 0; i < options.length; i++) {
      if (getOptionValue(options[i]) === '') {
        options[i].classList.remove('is-selected');
      }
    }

    optionBtn.classList.toggle('is-selected');

    /* 비어 있지 않은 값이 하나도 선택되지 않으면 data-value=""(전체)만 선택 */
    hasSelected = popup.querySelector('.c-modal-option.is-selected:not([data-value=""])');
    emptyOpt = popup.querySelector('.c-modal-option[data-value=""]');
    if (emptyOpt) {
      emptyOpt.classList.toggle('is-selected', !hasSelected);
    }
  }

  function getPopupOptionLabel(option) {
    var labelNode = option.querySelector('.c-modal-option-label');
    if (labelNode) return labelNode.textContent.trim();
    return (option.textContent || '').trim();
  }

  /* 확인 시 hidden + 트리거 첫 span 라벨 반영 */
  function confirm(popup, trigger) {
    var syncSelector;
    var input;
    var labelEl;
    var selectedOptions;
    var selected;
    var labels = [];
    var values = [];
    var i;
    var value;
    var label;

    if (!popup || !trigger) {
      closeAll();
      return;
    }

    syncSelector = trigger.getAttribute('data-popup-sync');
    input = syncSelector ? document.querySelector(syncSelector) : null;
    labelEl = trigger.querySelector(':scope > span');

    if (popup.hasAttribute('data-popup-multiselect')) {
      selectedOptions = popup.querySelectorAll('.c-modal-option.is-selected');

      for (i = 0; i < selectedOptions.length; i++) {
        value = selectedOptions[i].getAttribute('data-value');
        value = value == null ? '' : String(value);

        if (value === '') continue;

        values.push(value);
        labels.push(getPopupOptionLabel(selectedOptions[i]));
      }

      if (input && 'value' in input) {
        input.value = values.join(',');
      }

      if (labelEl) {
        if (labels.length > 0) {
          labelEl.textContent = labels.join(', ');
        } else {
          selected = popup.querySelector('.c-modal-option[data-value=""]');
          if (selected) {
            labelEl.textContent = getPopupOptionLabel(selected);
          }
        }
        labelEl.classList.remove('is-placeholder');
      }

      closeAll();
      return;
    }

    selected = popup.querySelector('.c-modal-option.is-selected');

    if (selected) {
      value = selected.getAttribute('data-value');
      value = value == null ? '' : String(value);
      label = getPopupOptionLabel(selected);

      if (input && 'value' in input) {
        input.value = value;
      }

      if (labelEl) {
        labelEl.textContent = label;
        labelEl.classList.remove('is-placeholder');
      }
    }

    closeAll();
  }

  /* common.js DOMContentLoaded에서 호출 */
  MyFC.initPopups = function () {
    var activeTrigger = null;

    document.addEventListener('click', function (e) {
      var openBtn = e.target.closest('[data-popup-target]');
      var closeBtn = e.target.closest('[data-popup-close]');
      var confirmBtn = e.target.closest('[data-popup-confirm]');
      var optionBtn = e.target.closest('.c-modal-option');
      var toastBtn = e.target.closest('[data-toast]');
      var popupNode;

      if (openBtn) {
        e.preventDefault();
        activeTrigger = openBtn;
        open(openBtn);
        return;
      }

      if (closeBtn) {
        e.preventDefault();
        activeTrigger = null;
        closeAll();
        return;
      }

      if (optionBtn) {
        popupNode = optionBtn.closest('.c-modal');
        if (!popupNode || !popupNode.classList.contains('is-open')) return;
        e.preventDefault();
        selectOption(popupNode, optionBtn);
        return;
      }

      if (confirmBtn) {
        e.preventDefault();
        confirm(confirmBtn.closest('.c-modal'), activeTrigger);
        activeTrigger = null;
        return;
      }

      if (toastBtn) {
        handleToastButton(toastBtn);
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Escape') return;
      if (document.querySelector('.c-modal.is-open')) {
        closeAll();
      }
    });
  };
})(window, document);
