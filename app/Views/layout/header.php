<!doctype html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>MyFC</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="<?= base_url('assets/css/reset.css?v=3') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css?v=3') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css?v=3') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css?v=3') ?>" />
    <link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=7') ?>" />

    <!-- 스크립트 · Swiper -->
    <script src="<?= base_url('assets/js/ui.js?v=9') ?>"></script>
    <script src="<?= base_url('assets/js/popup.js?v=8') ?>"></script>
    <script src="<?= base_url('assets/js/common.js?v=8') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


</head>

<?php if($isApp && !empty($appToken)): ?>

<script>
// ─────────────────────────────────────────────────────────────
// 진단 + 안전한 최종본
//
// 증상: appBridgeReady 이벤트를 dispatch 했는데 웹 리스너가 안 깨어남.
// 원인 후보:
//   (A) 이벤트 리스너/디스패치의 window 컨텍스트 불일치
//   (B) 이벤트 등록과 발생 사이 미세한 경합
//   (C) 앱이 이벤트를 실제로는 못 쏨(주입 스크립트 예외 등)
//
// 아래는 "이벤트"에만 의존하지 않고, 짧은 폴링으로도 함수를 잡아
// 어떤 경우든 확실히 호출되게 만든 버전.
// ─────────────────────────────────────────────────────────────
(function () {
  window.__myfcBridgeVersion = 'app-bridge-20260709-3';
  console.log('[BRIDGE]', window.__myfcBridgeVersion);

  const appToken = <?= json_encode($appToken) ?>;

  let sent = false;
  function fire() {
    if (sent) return;
    if (typeof window.requestAppTokenFromWeb !== 'function') return;
    sent = true;
    console.log('requestAppTokenFromWeb web request start');
    window.requestAppTokenFromWeb(appToken);
  }

  function sendAppToken() {
    console.log('sendAppToken step 2');

    if (typeof window.requestAppTokenFromWeb === 'function') {
      fire();
      return;
    }

    console.log('requestAppTokenFromWeb not ready → 대기 시작');

    // 1) 이벤트 방식 (앱이 준비되면 즉시)
    window.addEventListener('appBridgeReady', function () {
      console.log('appBridgeReady 수신');
      fire();
    }, { once: true });

    // 2) 폴링 방식 (이벤트를 못 받는 경우 대비, 최대 5초)
    let tries = 0;
    const timer = setInterval(function () {
      tries++;
      if (typeof window.requestAppTokenFromWeb === 'function') {
        console.log('폴링으로 함수 발견 (' + tries + '회차)');
        clearInterval(timer);
        fire();
      } else if (tries >= 50) { // 100ms * 50 = 5초
        console.log('5초 내 함수 미발견, 폴링 중단');
        clearInterval(timer);
      }
    }, 100);
  }

  if (document.readyState === 'loading') {
    console.log('Flutter Bridge loading');
    document.addEventListener('DOMContentLoaded', sendAppToken);
  } else {
    console.log('sendAppToken step 1');
    sendAppToken();
  }
})();
</script>

<script>

(function(){


    /**
     * Flutter → Web
     * 앱 로그인 토큰 전달
     */
    window.onAppTokenRequested = function(
        loginToken,
        pushToken
    ){


        console.log('[BRIDGE] onAppTokenRequested enter', window.__myfcBridgeVersion);
        console.log(
            "APP TOKEN:",
            loginToken
        );


        console.log(
            "PUSH TOKEN:",
            pushToken
        );


        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/member/appLogin", true);
        xhr.setRequestHeader(
            "Content-Type",
            "application/x-www-form-urlencoded; charset=UTF-8"
        );
        xhr.setRequestHeader("Accept", "application/json");

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }

            console.log('[AJAX] complete');
            console.log('[AJAX] response status :', xhr.status);

            if (xhr.status < 200 || xhr.status >= 300) {
                console.error(xhr.responseText);
                return;
            }

            try {
                const res = JSON.parse(xhr.responseText);

                console.log(
                    "APP LOGIN RESULT",
                    res
                );

                if(res.result){


                    // 자동 로그인 성공

                    //location.reload();


                }else{


                    console.log(
                        res.message
                    );

                }
            } catch (error) {
                console.error(error);
                console.error(xhr.responseText);
            }

        };

        xhr.onerror = function () {
            console.error('[AJAX] network error');
        };

        xhr.send(
            "app_token=" +
            encodeURIComponent(loginToken ?? "") +
            "&fcm_token=" +
            encodeURIComponent(pushToken ?? "")
        );


    };


})();

</script>

<?php endif; ?>
 <!-- class="popup-open" -->
<body>

<?php
// 팝업 파일들을 동적으로 include
if (!empty($modal_page) && is_array($modal_page)) {
    foreach ($modal_page as $modal) {
        $file = MODAL_PATH . '/' . $modal;

        // 파일이 존재할 때만 include
        if (is_file($file)) {
            include_once $file;
        }
    }
}

// 항상 출력할 모달
$fixed_modal = MODAL_PATH . '/join_select.php';
if (is_file($fixed_modal)) {
    include_once $fixed_modal;
}


$isLogin = session()->get('logged_in');

$memberName = session()->get('name');
$memberEmail = session()->get('email');

?>    

<script>

document.addEventListener('DOMContentLoaded', function () {

    const body = document.body;

    // =========================
    // 팝업 열기
    // =========================
    document.querySelectorAll('[data-popup]').forEach(btn => {

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('data-popup');
            const popup = document.getElementById(targetId);

            if (popup) {
                popup.classList.add('is-open');
                body.classList.add('popup-open');
            }

        });

    });

    // =========================
    // 팝업 닫기 (X + backdrop)
    // =========================
    document.querySelectorAll('[data-popup-close]').forEach(el => {

        el.addEventListener('click', function () {

            const modal = this.closest('.c-modal');

            if (modal) {
                modal.classList.remove('is-open');
            }

            // ⭐ 핵심: 닫을 때 무조건 제거
            body.classList.remove('popup-open');

        });

    });

    // =========================
    // ESC 닫기
    // =========================
    document.addEventListener('keydown', function (e) {

        if (e.key === 'Escape') {

            document.querySelectorAll('.c-modal.is-open')
                .forEach(modal => {
                    modal.classList.remove('is-open');
                });

            body.classList.remove('popup-open');
        }

    });

});

</script>
    <div class="layout-wrapper <?= $header_class ?>">
        <!-- 헤더 · 로그인 후: .site-header.member 로 클래스만 교체 -->
        <header class="site-header <?= $isLogin ? 'member' : 'guest' ?>">
            <div class="header-inner">
                <div class="logo">
                    <a href="/">
                        <img src="<?= SITE_IMG_URL ?>images/logo.png" alt="보험사 연계 서비스" class="logo-img" />
                    </a>
                </div>
                <nav class="gnb">
                    <ul>
                        <li>
                            <a href="<?= base_url('fc/search') ?>" class="gnb-search">
                                <img src="<?= SITE_IMG_URL ?>images/ic-search.svg" alt="" class="gnb-ico" />
                            </a>
                        </li>
                        <?php if(!$isLogin): ?>
                        <li class="guest">
                            <a href="javascript:void(0);" data-popup="join_select" class="gnb-btn-line">회원가입</a>
                        </li>
                        <li class="guest">
                            <a href="/member/login" class="gnb-btn-line">로그인</a>
                        </li>
                        <?php else: ?>
                        <li class="member">
                            <button type="button" class="gnb-avatar" data-profile-toggle aria-expanded="false" aria-label="마이페이지 메뉴 열기">
                                <img src="<?= !empty($memberProfile['profile_image'])
        ? '/uploads/profile/'.$memberProfile['profile_image']
        : SITE_IMG_URL . 'images/temp/@profile-m.png' ?>" alt="" class="gnb-avatar-img" />
                                
                            </button>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php if($isLogin): ?>
                <div class="profile-menu" data-profile-menu>
                    <div class="profile-menu-summary">
                        <strong><?= esc($memberName) ?></strong>
                        <p><?= esc($memberEmail) ?></p>
                    </div>
                    <?php $memberType = session()->get('member_type'); ?>
                    <?php if ($memberType === 'FC'): ?>
                        
                    <ul>
                        <li><a href="/mypage/fcinfo">내 정보</a></li>
                        <li><a href="/mypage/fcprofile">프로필 관리</a></li>
                        <li><a href="/mypage/fcreviewed">심의필 정보 관리</a></li>
                        <li><a href="/mypage/fccounsel">상담 신청 관리</a></li>
                        <li><a href="/mypage/adlist">광고 관리</a></li>
                    </ul>
                    <?php else: ?>
                    <ul>
                        
                        <li><a href="/mypage/info">내 정보</a></li>
                        <li><a href="/mypage/certificate">내 증권 관리</a></li>
                        <li><a href="/mypage/favoriteFc">내 관심 FC</a></li>
                        <li><a href="/mypage/counselList">상담현황</a></li>
                        <li><a href="/mypage/reviewList">나의 후기</a></li>
                    </ul>
                    <?php endif; ?>
                    <!-- <a href="MFC004_L01_06.html" class="profile-menu-logout">로그아웃</a> -->
                     <a href="/member/logout" class="profile-menu-logout">로그아웃</a>
                </div>
                <?php endif; ?>
                
            </div>
        </header>
        <?php if($isLogin): ?>
        <aside class="profile-menu-drawer" data-profile-drawer>
            <button type="button" class="profile-menu-drawer-close" data-profile-close aria-label="닫기"></button>
            <div class="profile-menu-summary">
                <strong><?= esc($memberName) ?></strong>
                <p><?= esc($memberEmail) ?></p>
            </div>
            <?php if ($memberType === 'FC'): ?>
            <ul>
                <li><a href="/mypage/fcinfo">내 정보</a></li>
                <li><a href="/mypage/fcprofile">프로필 관리</a></li>
                <li><a href="/mypage/fcreviewed">심의필 정보 관리</a></li>
                <li><a href="/mypage/fccounsel">상담 신청 관리</a></li>
                <li><a href="/mypage/adlist">광고 관리</a></li>
            </ul>
            <?php else: ?>
            <ul>
                <li><a href="/mypage/info">내 정보</a></li>
                <li><a href="/mypage/certificate">내 증권 관리</a></li>
                <li><a href="/mypage/favoriteFc">내 관심 FC</a></li>
                <li><a href="/mypage/counselList">상담현황</a></li>
                <li><a href="/mypage/reviewList">나의 후기</a></li>
            </ul>
            <?php endif; ?>
            <a href="/member/logout" class="profile-menu-logout">로그아웃</a>
        </aside>
        <?php endif; ?>

        
