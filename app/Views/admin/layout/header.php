<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? '관리자') ?></title>

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- 상단 메뉴 -->
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('admin') ?>">관리자</a>
        </div>
    </nav>

    <!-- 사이드바 -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
           
            <a href="<?= base_url('admin') ?>" class="brand-link text-decoration-none">
                <span class="brand-text fw-light">Admin</span>
            </a>
            <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline-secondary btn-sm">
                로그아웃
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column">
                    <li class="nav-item">
                        <a href="<?= base_url('admin') ?>" class="nav-link">
                            <span>대시보드</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- 본문 -->
    <main class="app-main">
        <div class="app-content p-3">