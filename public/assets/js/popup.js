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

  var alertQueue = Promise.resolve();

  function showAlertModal(message) {
    return new Promise(function (resolve) {
      var previousFocus = document.activeElement;
      var modal = document.createElement('div');
      var backdrop = document.createElement('button');
      var panel = document.createElement('div');
      var head = document.createElement('div');
      var title = document.createElement('h2');
      var body = document.createElement('div');
      var text = document.createElement('p');
      var foot = document.createElement('div');
      var confirmButton = document.createElement('button');
      var settled = false;

      modal.className = 'c-modal notice-link';
      modal.setAttribute('role', 'alertdialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-labelledby', 'myfc-alert-title');
      modal.setAttribute('aria-describedby', 'myfc-alert-message');

      backdrop.type = 'button';
      backdrop.className = 'c-modal-backdrop';
      backdrop.setAttribute('aria-label', '닫기');
      panel.className = 'c-modal-panel';
      head.className = 'c-modal-head';
      title.className = 'c-modal-title';
      title.id = 'myfc-alert-title';
      title.textContent = '알림';
      body.className = 'c-modal-body';
      text.className = 'modal-text';
      text.id = 'myfc-alert-message';
      text.textContent = message == null ? '' : String(message);
      foot.className = 'c-modal-foot';
      confirmButton.type = 'button';
      confirmButton.className = 'btn btn-primary';
      confirmButton.textContent = '확인';

      head.appendChild(title);
      body.appendChild(text);
      foot.appendChild(confirmButton);
      panel.appendChild(head);
      panel.appendChild(body);
      panel.appendChild(foot);
      modal.appendChild(backdrop);
      modal.appendChild(panel);
      document.body.appendChild(modal);

      function close() {
        if (settled) return;
        settled = true;
        document.removeEventListener('keydown', onKeydown, true);
        modal.remove();
        if (!document.querySelector('.c-modal.is-open')) {
          document.body.classList.remove('popup-open');
        }
        if (previousFocus && typeof previousFocus.focus === 'function') {
          previousFocus.focus();
        }
        resolve();
      }

      function onKeydown(event) {
        if (event.key !== 'Escape') return;
        event.preventDefault();
        event.stopPropagation();
        close();
      }

      backdrop.addEventListener('click', close);
      confirmButton.addEventListener('click', close);
      document.addEventListener('keydown', onKeydown, true);
      modal.classList.add('is-open');
      document.body.classList.add('popup-open');
      confirmButton.focus();
    });
  }

  // 브라우저 alert와 달리 Promise를 반환한다. 호출부는 await로 다음 동작을 대기한다.
  MyFC.alert = function (message) {
    var next = alertQueue.then(function () {
      return showAlertModal(message);
    });
    alertQueue = next.catch(function () {});
    return next;
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
      if (!node._popupConfirmed) restorePopupSelection(node);
      node.classList.remove('is-open');
      node._popupConfirmed = false;
      node._popupBeforeAll = null;
      node._popupInitialAll = false;
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
    popup._popupConfirmed = false;
    popup._popupBeforeAll = null;
    popup._popupInitialAll = false;
    popup.classList.add('is-open');
    toggleBodyLock();
    syncState(popup, btn);
    popup._popupInitialAll = !!popup.querySelector('.c-modal-option.is-selected[data-value="' + getAllValue(popup) + '"]');
    popup._popupInitialSelection = getSelectedOptionValues(popup);
    if (popup._popupInitialAll) {
      popup._popupBeforeAll = [];
    } else {
      popup._popupBeforeAll = popup._popupInitialSelection.slice();
    }
  }

  function getAllValue(popup) {
    var value = popup.getAttribute('data-popup-all-value');
    return value == null ? '' : String(value);
  }

  function getSelectedOptionValues(popup) {
    var values = [];
    each(popup.querySelectorAll('.c-modal-option.is-selected'), function (option) {
      var value = getOptionValue(option);
      if (value !== '' && value !== getAllValue(popup)) values.push(value);
    });
    return values;
  }

  function restorePopupSelection(popup) {
    var values = popup._popupInitialSelection || [];
    var selectedMap = {};
    each(values, function (value) { selectedMap[value] = true; });
    each(popup.querySelectorAll('.c-modal-option'), function (option) {
      var value = getOptionValue(option);
      option.classList.toggle('is-selected', popup._popupInitialAll || value !== getAllValue(popup) && value !== '' && !!selectedMap[value]);
    });
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
    var allValue;
    var allSelected;

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
      allValue = getAllValue(popup);
      allSelected = (allValue === '' && values.length === 0) || values.indexOf(allValue) !== -1;
      for (i = 0; i < values.length; i++) {
        selectedMap[values[i]] = true;
      }

      for (i = 0; i < options.length; i++) {
        optionValue = options[i].getAttribute('data-value');
        optionValue = optionValue == null ? '' : String(optionValue);

        if (allSelected) {
          options[i].classList.add('is-selected');
        } else {
          options[i].classList.toggle('is-selected', optionValue !== allValue && optionValue !== '' && !!selectedMap[optionValue]);
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
    var allToggle;
    var allValue;

    if (!popup || !popup.classList.contains('is-open')) return;

    if (!isMulti) {
      for (i = 0; i < options.length; i++) {
        options[i].classList.remove('is-selected');
      }
      optionBtn.classList.add('is-selected');
      return;
    }

    value = getOptionValue(optionBtn);

    allValue = getAllValue(popup);
    allToggle = popup.hasAttribute('data-popup-all-toggle') && value === allValue;
    if (allToggle) {
      if (optionBtn.classList.contains('is-selected')) {
        var previousValues = popup._popupBeforeAll || [];
        var previousMap = {};
        each(previousValues, function (previousValue) { previousMap[previousValue] = true; });
        each(options, function (option) {
          var optionValue = getOptionValue(option);
          option.classList.toggle('is-selected', optionValue !== allValue && !!previousMap[optionValue]);
        });
      } else {
        popup._popupBeforeAll = getSelectedOptionValues(popup);
        each(options, function (option) { option.classList.add('is-selected'); });
      }
      return;
    }

    if (value === '') {
      for (i = 0; i < options.length; i++) {
        options[i].classList.remove('is-selected');
      }
      optionBtn.classList.add('is-selected');
      return;
    }

    for (i = 0; i < options.length; i++) {
      if (getOptionValue(options[i]) === allValue) {
        options[i].classList.remove('is-selected');
      }
    }

    optionBtn.classList.toggle('is-selected');

    /* 비어 있지 않은 값이 하나도 선택되지 않으면 data-value=""(전체)만 선택 */
    hasSelected = popup.querySelector('.c-modal-option.is-selected:not([data-value="' + allValue + '"])');
    emptyOpt = popup.querySelector('.c-modal-option[data-value="' + allValue + '"]');
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

      popup._popupConfirmed = true;
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

    popup._popupConfirmed = true;
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
