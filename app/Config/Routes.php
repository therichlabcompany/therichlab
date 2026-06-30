<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/fc/recommend', 'Home::recommend');


$routes->group('fc', function ($routes) {
    $routes->get('list', 'FcController::index');                // fc 리스트
    $routes->get('view', 'FcController::view');                 // fc 상세
    $routes->get('counsel', 'FcController::counsel');           // 상담신청하기
    $routes->get('counselLast', 'FcController::counselLast');   // 상담신청하기 완료
    $routes->post('counsel/save','CounselController::save');

});


$routes->group('mypage', function ($routes) {
    $routes->get('info', 'MypageController::info');         // 내정보-개인
    $routes->get('withdrawal', 'MypageController::withdrawal');  // 회원탈퇴 - 개인
    $routes->get('withdrawalLast', 'MypageController::withdrawalLast');  // 회원탈퇴 완료- 개인
    $routes->get('certificate', 'MypageController::certificate');  // 내증권과리- 개인
    $routes->get('favoriteFc', 'MypageController::favoriteFc');  // 내 관심 fc- 개인
    $routes->get('counselList', 'MypageController::counselList');  // 내 상담 리스트 - 개인
    $routes->get('counselReview/(:segment)', 'MypageController::counselReview/$1');  // 내 상담 리스트 - 개인
    $routes->get('counselReviewLast', 'MypageController::counselReviewLast');  // 내 상담 리스트 - 개인
    $routes->post('counselReviewSubmitAjax/(:segment)', 'MypageController::counselReviewSubmitAjax/$1');
    
    $routes->get('reviewWrite', 'MypageController::reviewWrite');  // 후기 작성 - 개인
    $routes->get('reviewWriteLast', 'MypageController::reviewWriteLast');  // 후기 작성 완료 - 개인
    $routes->get('reviewList', 'MypageController::reviewList');  // 후기 리스트 - 개인
    $routes->get('reviewDetailAjax/(:num)', 'MypageController::reviewDetailAjax/$1');


    $routes->get('fcinfo', 'MypageController::fcinfo');  // 후기 리스트 - 개인
    $routes->get('fcprofile', 'MypageController::fcprofile');  // 후기 리스트 - 개인
    $routes->post('updateProfileImage', 'MypageController::updateProfileImage');  // 후기 리스트 - 개인
    $routes->get('fcactivity', 'MypageController::fcactivity');  // 후기 리스트 - 개인
    $routes->get('fcstory', 'MypageController::fcstory');  // 후기 리스트 - 개인
    $routes->get('fcreviewed', 'MypageController::fcreviewed');  // 후기 리스트 - 개인
    $routes->get('fccounsel', 'MypageController::fcCounselList');  // 후기 리스트 - 개인
    $routes->get('fccounselview/(:segment)', 'MypageController::fcCounselView/$1');
    
    $routes->post('ajax_save_reviewed', 'MypageController::ajax_save_reviewed');  // 후기 리스트 - 개인
    $routes->post('fccounsel/status', 'MypageController::fcCounselStatus');
    $routes->post('withdrawAjax', 'MypageController::withdrawAjax');
    


    // 파일 업로드
    $routes->post('security/upload', 'MemberSecurityController::upload');

    // 파일 삭제
    $routes->post('security/delete', 'MemberSecurityController::delete');

    $routes->get('security/download/(:num)', 'MemberSecurityController::download/$1');

    $routes->post('updateInfo', 'MypageController::updateInfo');

    $routes->get('adlist', 'MypageController::adlist');
    $routes->get('adlistRegionFc', 'MypageController::adlistRegionFc');
    $routes->get('adlistBanner', 'MypageController::adlistBanner');
    $routes->get('adlistProductFc', 'MypageController::adlistProductFc');
    $routes->get('adlistLanguageFc', 'MypageController::adlistLanguageFc');
    $routes->get('adlistReview', 'MypageController::adlistReview');
    
    
    


    $routes->get('adLast', 'MypageController::adLast');
    $routes->post('ad/region-fc', 'MypageController::ajaxRegionFcApply');
    $routes->post('ad/banner', 'MypageController::ajaxBannerApply');
    $routes->post('ad/product-fc', 'MypageController::ajaxProductFcApply');
    $routes->post('ad/review', 'MypageController::ajaxReviewApply');
    $routes->post('ad/language-fc', 'MypageController::ajaxLanguageApply');


    
});


$routes->group('fcpage', function ($routes) {
    $routes->get('info', 'FcPageControoller::info');         // 프로필 -  fc 마이페이지 

});

$routes->group('company', function ($routes) {
    $routes->get('terms', 'CompanyController::terms');         // 프로필 -  fc 마이페이지 
    $routes->get('privacy', 'CompanyController::privacy');         // 프로필 -  fc 마이페이지 
    $routes->get('legal', 'CompanyController::legal');         // 프로필 -  fc 마이페이지 
 
});


$routes->group('member', function ($routes) {
    $routes->get('login', 'MemberController::login');         // 회원 - 로그인
    $routes->get('find', 'MemberController::find');         // 회원 - 계정찾기
    $routes->get('findResult', 'MemberController::findResult');         // 회원 - 계정찾기 결과
    $routes->get('passEmail', 'MemberController::passEmail');         // 회원 - 비밀번호 리셋 메일 보내게
    $routes->get('passreSet', 'MemberController::passReset');         // 회원 - 비밀번호 재설정하기
    $routes->get('passResult', 'MemberController::passResult');         // 회원 - 비밀번호 재설정 완료

    $routes->get('join', 'MemberController::join');         // 회원 - 개인회원가입
    $routes->get('joinComplete', 'MemberController::joinComplete');         // 회원 - 개인회원가입완료

    $routes->get('fcAgree', 'MemberController::fcAgree');         // fc 회원 - 약관동의
    $routes->get('fcJoin1', 'MemberController::fcJoin_step1');         // fc 회원 - 기본정보
    $routes->get('fcJoin2', 'MemberController::fcJoin_step2');         // fc 회원 - 프로필정보
    $routes->get('fcJoin3', 'MemberController::fcJoin_step3');         // fc 회원 - 활동정보
    $routes->get('fcJoin4', 'MemberController::fcJoin_step4');         // fc 회원 - 활동정보
    $routes->get('fcComplete', 'MemberController::fcComplete');         // 회원 - 개인회원가입완료


    $routes->post('check-email', 'MemberController::checkEmail');
    $routes->post('check-phone', 'MemberController::checkPhone');

    $routes->post('register', 'MemberController::register');

    $routes->post('loginProc', 'MemberController::loginProc');
    $routes->get('logout', 'MemberController::logout');
    $routes->post('fc/profile/update', 'MemberController::fcProfileUpdate');
    $routes->post('fc/activity/save', 'MemberController::fcActivitySave');
    $routes->post('fc/story/save', 'MemberController::fcStorySave');

    $routes->post(
        'updateBasicInfo',
        'MemberController::updateBasicInfo'
    );
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


    // =========================
    // Member Management (추가)
    // =========================
    $routes->get('members', 'Member::index');
    $routes->get('members/(:num)', 'Member::detail/$1');

    $routes->post('members/status', 'Member::changeStatus');
    $routes->post('members/delete', 'Member::delete');


        // =========================
    // FC Member (신규 추가)
    // =========================
    $routes->get('fc-members', 'FcMember::index');
    $routes->get('fc-members/(:num)', 'FcMember::detail/$1');
    $routes->post('fc-members/status', 'FcMember::changeStatus');
    $routes->post('fc-members/delete', 'FcMember::delete');

    $routes->post('fc-members/review/approve', 'FcMember::reviewApprove');
    $routes->post('fc-members/review/reject', 'FcMember::reviewReject');
});

$routes->get('push-test', 'PushTest::index');
$routes->post('fc/bookmark/toggle', 'FcBookmarkController::toggle');
$routes->get('fc/bookmark/check', 'FcBookmarkController::check');