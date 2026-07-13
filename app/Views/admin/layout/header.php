<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? '관리자') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/myfc-favicon.svg') ?>">
    <link rel="shortcut icon" type="image/svg+xml" href="<?= base_url('assets/images/myfc-favicon.svg') ?>">

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css?v=3') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary admin-shell">
<div class="app-wrapper">

    <!-- 상단 메뉴 -->
    <nav class="app-header navbar navbar-expand bg-body admin-header">
        <div class="container-fluid">
            <a class="navbar-brand admin-brand" href="<?= base_url('admin') ?>">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="MyFC" class="admin-brand-logo">
                <span>관리자</span>
            </a>
        </div>
    </nav>

    <!-- 사이드바 -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="light">
        <div class="sidebar-brand">
        
            <a href="<?= base_url('admin') ?>" class="brand-link text-decoration-none">
                <img src="<?= base_url('assets/images/logo.png') ?>" alt="MyFC" class="brand-image admin-sidebar-brand-image">
                <span class="brand-text fw-light">Admin</span>
            </a>

            <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline-secondary btn-sm">
                로그아웃
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <?php
                $adminPath = trim(service('request')->getUri()->getPath(), '/');
                $isDashboardActive = $adminPath === 'admin' || $adminPath === '';
                $isUserMenuOpen = str_starts_with($adminPath, 'admin/members')
                    || str_starts_with($adminPath, 'admin/fc-members')
                    || str_starts_with($adminPath, 'admin/inactive-members');
                $isContentMenuOpen = str_starts_with($adminPath, 'admin/contents');
                $isPopupMenuOpen = str_starts_with($adminPath, 'admin/popups')
                    || str_starts_with($adminPath, 'admin/pushes');
                $isAdMenuOpen = str_starts_with($adminPath, 'admin/ads');
                $isTermsMenuOpen = str_starts_with($adminPath, 'admin/terms');
                $isSystemMenuOpen = str_starts_with($adminPath, 'admin/accounts')
                    || str_starts_with($adminPath, 'admin/codes')
                    || str_starts_with($adminPath, 'admin/forbidden-words');
                ?>

                <ul
                    class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview"
                    role="navigation"
                    aria-label="Admin navigation"
                    data-accordion="false"
                >

                    <li class="nav-item">
                        <a href="<?= base_url('admin') ?>" class="nav-link <?= $isDashboardActive ? 'active' : '' ?>">
                            <p>대시보드</p>
                        </a>
                    </li>

                    <li class="nav-item <?= $isUserMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                사용자관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/members') ?>" class="nav-link">
                                    <p>개인 회원</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/fc-members') ?>" class="nav-link">
                                    <p>FC 회원</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/inactive-members') ?>" class="nav-link">
                                    <p>탈퇴 회원</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= $isContentMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                컨텐츠 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/contents/counsels') ?>" class="nav-link">
                                    <p>상담 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/contents/deliberations') ?>" class="nav-link">
                                    <p>심의필 신청 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/contents/reviews') ?>" class="nav-link">
                                    <p>후기 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/contents/insurance-in') ?>" class="nav-link">
                                    <p>보험IN 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/contents/securities') ?>" class="nav-link">
                                    <p>증권 관리</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= $isPopupMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                알림 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/popups') ?>" class="nav-link">
                                    <p>팝업 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/pushes') ?>" class="nav-link">
                                    <p>앱푸쉬</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= $isAdMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                광고 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/ads/normal') ?>" class="nav-link">
                                    <p>일반 광고</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/ads/top') ?>" class="nav-link">
                                    <p>상단 배너 광고</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/ads/bottom') ?>" class="nav-link">
                                    <p>하단 배너 광고</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= $isTermsMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                약관 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/terms') ?>" class="nav-link">
                                    <p>약관관리</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <p>
                                통계 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/stats') ?>" class="nav-link">
                                    <p>통계 관리 (2차 예정)</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item <?= $isSystemMenuOpen ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link">
                            <p>
                                시스템 관리
                                <span class="nav-arrow">›</span>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= base_url('admin/accounts') ?>" class="nav-link">
                                    <p>계정 관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/codes') ?>" class="nav-link">
                                    <p>코드관리</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('admin/forbidden-words') ?>" class="nav-link">
                                    <p>금칙어 설정</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- 본문 -->
    <main class="app-main">
        <div class="app-content p-3">
