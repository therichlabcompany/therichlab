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
    <link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=3') ?>" />

    <!-- 스크립트 · Swiper -->
    <script src="<?= base_url('assets/js/ui.js?v=8') ?>"></script>
    <script src="<?= base_url('assets/js/popup.js?v=8') ?>"></script>
    <script src="<?= base_url('assets/js/common.js?v=8') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


</head>

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
?>    
    <div class="layout-wrapper <?= $header_class ?>">
        <!-- 헤더 · 로그인 후: .site-header.member 로 클래스만 교체 -->
        <header class="site-header guest">
            <div class="header-inner">
                <div class="logo">
                    <a href="/">
                        <img src="<?= SITE_IMG_URL ?>images/logo.png" alt="보험사 연계 서비스" class="logo-img" />
                    </a>
                </div>
                <nav class="gnb">
                    <ul>
                        <li>
                            <a href="MFC002.html" class="gnb-search">
                                <img src="<?= SITE_IMG_URL ?>images/ic-search.svg" alt="" class="gnb-ico" />
                            </a>
                        </li>
                        <li class="guest">
                            <a href="MFC007.html" class="gnb-btn-line">회원가입</a>
                        </li>
                        <li class="guest">
                            <a href="MFC006_01.html" class="gnb-btn-line">로그인</a>
                        </li>
                        <li class="member">
                            <button type="button" class="gnb-avatar" data-profile-toggle aria-expanded="false" aria-label="마이페이지 메뉴 열기">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="gnb-avatar-img" />
                            </button>
                        </li>
                    </ul>
                </nav>
                <div class="profile-menu" data-profile-menu>
                    <div class="profile-menu-summary">
                        <strong>김노아</strong>
                        <p>username@gmail.com</p>
                    </div>
                    <ul>
                        <li><a href="/mypage/info">내 정보</a></li>
                        <li><a href="/mypage/certificate">내 증권 관리</a></li>
                        <li><a href="/mypage/favoriteFc">내 관심 FC</a></li>
                        <li><a href="/mypage/counselList">상담현황</a></li>
                        <li><a href="/mypage/reviewList">나의 후기</a></li>
                    </ul>
                    <a href="MFC004_L01_06.html" class="profile-menu-logout">로그아웃</a>
                </div>
            </div>
        </header>
        