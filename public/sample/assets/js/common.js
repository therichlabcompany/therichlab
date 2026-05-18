/**
 * common.js
 * - 전역 MyFC 네임스페이스
 * - 부트스트랩 (ui · popup 초기화)
 */
(function (window, document) {
  'use strict';

  if (!window.MyFC) window.MyFC = {};
  var MyFC = window.MyFC;

  /* 헤더 프로필 메뉴 */
  function initProfileMenu() {
    var trigger = document.querySelector('[data-profile-toggle]');
    var menu = document.querySelector('[data-profile-menu]');
    var drawer = document.querySelector('[data-profile-drawer]');
    var closeBtn = document.querySelector('[data-profile-close]');
    if (!trigger || !menu || !drawer) return;
    if (trigger.dataset.profileBound === 'true') return;
    trigger.dataset.profileBound = 'true';

    function isMobile() {
      return window.matchMedia('(max-width: 640px)').matches;
    }

    function openMenu() {
      var mobile = isMobile();

      trigger.setAttribute('aria-expanded', 'true');

      if (mobile) {
        drawer.classList.add('is-open');
        menu.classList.remove('is-open');
        document.body.classList.add('profile-open');
      } else {
        menu.classList.add('is-open');
        drawer.classList.remove('is-open');
        document.body.classList.remove('profile-open');
      }
    }

    function closeMenu() {
      trigger.setAttribute('aria-expanded', 'false');
      menu.classList.remove('is-open');
      drawer.classList.remove('is-open');
      document.body.classList.remove('profile-open');
    }

    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      var opened = trigger.getAttribute('aria-expanded') === 'true';
      if (opened) closeMenu();
      else openMenu();
    });

    document.addEventListener('click', function (e) {
      var mobile = isMobile();
      var container = mobile ? drawer : menu;
      var inside = container.contains(e.target) || trigger.contains(e.target);

      if (!inside) closeMenu();
    });

    if (closeBtn) closeBtn.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeMenu();
    });

    var prevIsMobile = isMobile();

    window.addEventListener('resize', function () {
      var current = isMobile();
      if (prevIsMobile !== current) {
        closeMenu();
      }
      prevIsMobile = current;
    });
  }

  function boot() {
    initProfileMenu();
    if (typeof MyFC.initUi === 'function') MyFC.initUi();
    if (typeof MyFC.initPopups === 'function') MyFC.initPopups();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window, document);
