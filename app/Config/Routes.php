<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');



$routes->group('fc', function($routes) {
    $routes->get('list', 'FcController::index');                // fc 리스트
    $routes->get('view', 'FcController::view');                 // fc 상세
    $routes->get('counsel', 'FcController::counsel');           // 상담신청하기
    $routes->get('counselLast', 'FcController::counselLast');   // 상담신청하기 완료
    
});


$routes->group('mypage', function($routes) {
    $routes->get('info', 'MypageController::info');         // 내정보-개인
    $routes->get('withdrawal', 'MypageController::withdrawal');  // 회원탈퇴 - 개인
    $routes->get('withdrawalLast', 'MypageController::withdrawalLast');  // 회원탈퇴 완료- 개인
    $routes->get('certificate', 'MypageController::certificate');  // 내증권과리- 개인
    $routes->get('favoriteFc', 'MypageController::favoriteFc');  // 내 관심 fc- 개인
    $routes->get('counselList', 'MypageController::counselList');  // 내 상담 리스트 - 개인
    $routes->get('reviewWrite', 'MypageController::reviewWrite');  // 후기 작성 - 개인
    $routes->get('reviewWriteLast', 'MypageController::reviewWriteLast');  // 후기 작성 완료 - 개인
    $routes->get('reviewList', 'MypageController::reviewList');  // 후기 리스트 - 개인
});


$routes->group('fcpage', function($routes) {
    $routes->get('info', 'FcPageControoller::info');         // 프로필 -  fc 마이페이지 
    
});

/* 관리자 */

// 로그인/로그아웃 (필터 없이 접근 가능)
$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin'
], function ($routes) {
    // 관리자 로그인
    $routes->match(['get', 'post'], 'login', 'Auth::login');

    // 관리자 로그아웃
    $routes->get('logout', 'Auth::logout');
});

// 인증이 필요한 관리자 영역
$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => 'adminAuth',
], function ($routes) {
    // /admin
    $routes->get('', 'Dashboard::index');

    // /admin/
    $routes->get('/', 'Dashboard::index');
});