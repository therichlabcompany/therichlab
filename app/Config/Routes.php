<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/index_pro', 'Home::index_pro');

$routes->get('/fc/recommend', 'Home::recommend');

$routes->get('ad/click/(:num)', 'AdController::click/$1');

$routes->group('insurance-in', function ($routes) {
    $routes->get('', 'InsuranceInController::index');
    $routes->get('write', 'InsuranceInController::write');
    $routes->post('write', 'InsuranceInController::saveQuestion');
    $routes->get('(:num)', 'InsuranceInController::view/$1');
    $routes->get('(:num)/edit', 'InsuranceInController::write/$1');
    $routes->post('(:num)/edit', 'InsuranceInController::saveQuestion/$1');
    $routes->post('(:num)/delete', 'InsuranceInController::deleteQuestion/$1');
    $routes->get('(:num)/answer', 'InsuranceInController::answer/$1');
    $routes->post('(:num)/answer', 'InsuranceInController::saveAnswer/$1');
    $routes->get('(:num)/answer/(:num)', 'InsuranceInController::answer/$1/$2');
    $routes->post('(:num)/answer/(:num)', 'InsuranceInController::saveAnswer/$1/$2');
    $routes->post('(:num)/answer/(:num)/delete', 'InsuranceInController::deleteAnswer/$1/$2');
    $routes->get('file/(:num)', 'InsuranceInController::download/$1');
});


$routes->group('fc', function ($routes) {
    $routes->get('search', 'FcController::search');            // fc 검색
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
    $routes->post('removeProfileImage', 'MypageController::deleteProfileImage');
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
    $routes->post('apply-phone-auth-info', 'MypageController::applyPhoneAuthInfo');

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
    // MobileOK 표준창 스크립트는 거래 요청 URL을 POST로 호출한다.
    $routes->post('phone-auth/request', 'MemberController::phoneAuthRequest');
    $routes->post('phone-auth/result', 'MemberController::phoneAuthResult');

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

    $routes->post(
        'appLogin',
        'MemberController::appLogin'
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
    $routes->get('dashboard/export', 'Dashboard::export');

    // /admin/
    $routes->get('/', 'Dashboard::index');


    // =========================
    // Member Management (추가)
    // =========================
    $routes->get('members', 'Member::index');
    $routes->get('members/export', 'Member::export');
    $routes->get('members/(:num)', 'Member::detail/$1');
    $routes->get('members/(:num)/counsels', 'Member::counsels/$1');
    $routes->get('members/(:num)/reviews', 'Member::reviews/$1');
    $routes->get('members/(:num)/reviews/(:num)', 'Member::reviewDetail/$1/$2');
    $routes->get('members/(:num)/edit', 'Member::edit/$1');
    $routes->post('members/(:num)/update', 'Member::update/$1');
    $routes->post('members/(:num)/files/upload', 'Member::uploadFile/$1');
    $routes->get('members/(:num)/files/download-all', 'Member::downloadFiles/$1');
    $routes->get('members/files/(:num)/download', 'Member::downloadFile/$1');
    $routes->get('members/counsel-files/(:num)/download', 'Member::downloadCounselFile/$1');

    $routes->post('members/status', 'Member::changeStatus');
    $routes->post('members/delete', 'Member::delete');
    $routes->post('members/memo-save', 'Member::saveMemo');
    $routes->post('members/password-reset', 'Member::resetPassword');
    $routes->post('members/files/delete', 'Member::deleteFile');
    $routes->post('members/counsel-files/delete', 'Member::deleteCounselFile');
    $routes->post('members/reviews/delete', 'Member::deleteReview');
    $routes->get('members/create', 'Member::create');
    $routes->post('members/create', 'Member::store');


        // =========================
    // FC Member (신규 추가)
    // =========================
    $routes->get('fc-members', 'FcMember::index');
    $routes->get('fc-members/export', 'FcMember::export');
    $routes->get('fc-members/(:num)', 'FcMember::detail/$1');
    $routes->post('fc-members/memo-save', 'FcMember::saveMemo');
    $routes->post('fc-members/password-reset', 'FcMember::resetPassword');
    $routes->post('fc-members/status', 'FcMember::changeStatus');
    $routes->post('fc-members/approve', 'FcMember::approve');
    $routes->post('fc-members/delete', 'FcMember::delete');
    $routes->post('fc-members/story/delete', 'FcMember::deleteStoryFile');

    $routes->post('fc-members/review/approve', 'FcMember::reviewApprove');
    $routes->post('fc-members/review/reject', 'FcMember::reviewReject');

    $routes->get('fc-members/create', 'FcMember::create');
    $routes->post('fc-members/create', 'FcMember::store');
    $routes->get('fc-members/(:num)/preview', 'FcMember::preview/$1');
    $routes->get('fc-members/(:num)/edit', 'FcMember::edit/$1');
    $routes->post('fc-members/(:num)/update', 'FcMember::update/$1');

    $routes->get('inactive-members', 'Management::inactiveMembers');
    $routes->get('contents/counsels', 'Management::counsels');
    $routes->get('contents/counsels/export', 'Management::counselsExport');
    $routes->get('contents/counsels/(:num)', 'Management::counselDetail/$1');
    $routes->get('contents/deliberations', 'Management::deliberations');
    $routes->get('contents/deliberations/export', 'Management::deliberationsExport');
    $routes->get('contents/deliberations/(:num)', 'Management::deliberationDetail/$1');
    $routes->get('contents/deliberations/(:num)/download', 'Management::deliberationDownload/$1');
    $routes->post('contents/deliberations/(:num)/decision', 'Management::deliberationDecision/$1');
    $routes->get('contents/reviews', 'Management::reviews');
    $routes->get('contents/reviews/export', 'Management::reviewsExport');
    $routes->get('contents/reviews/(:num)', 'Management::reviewDetail/$1');
    $routes->post('contents/reviews/(:num)/display', 'Management::reviewDisplayUpdate/$1');
    $routes->get('contents/insurance-in', 'Management::insuranceIn');
    $routes->get('contents/insurance-in/(:num)', 'Management::insuranceInDetail/$1');
    $routes->post('contents/insurance-in/(:num)/delete', 'Management::insuranceInDelete/$1');
    $routes->post('contents/insurance-in/(:num)/answers/(:num)/delete', 'Management::insuranceInAnswerDelete/$1/$2');
    $routes->get('contents/securities', 'Management::securities');
    $routes->get('contents/securities/(:segment)', 'Management::securityDetail/$1');

    $routes->get('popups', 'Management::popups');
    $routes->get('popups/create', 'Management::popupCreate');
    $routes->post('popups/create', 'Management::popupStore');
    $routes->get('popups/(:num)', 'Management::popupEdit/$1');
    $routes->get('popups/(:num)/edit', 'Management::popupEdit/$1');
    $routes->post('popups/(:num)/update', 'Management::popupUpdate/$1');
    $routes->post('popups/(:num)/delete', 'Management::popupDelete/$1');
    $routes->get('pushes', 'Management::pushes');
    $routes->get('pushes/create', 'Management::pushCreate');
    $routes->post('pushes/create', 'Management::pushStore');
    $routes->get('pushes/(:num)', 'Management::pushDetail/$1');
    $routes->post('pushes/(:num)/cancel', 'Management::pushCancel/$1');

    $routes->get('ads', 'Management::ads/normal');
    $routes->get('ads/normal', 'Management::ads/normal');
    $routes->get('ads/normal/create', 'Management::adCreate/normal');
    $routes->post('ads/normal/create', 'Management::adStore/normal');
    $routes->get('ads/normal/clicks', 'Management::adClicks/normal');
    $routes->get('ads/normal/status', 'Management::adStatus/normal');
    $routes->get('ads/normal/export', 'Management::adsExport/normal');
    $routes->post('ads/normal/bulk-end', 'Management::adsBulkEnd/normal');
    $routes->post('ads/normal/(:num)/decision', 'Management::adDecision/normal/$1');
    $routes->get('ads/top', 'Management::ads/top');
    $routes->get('ads/top/create', 'Management::adCreate/top');
    $routes->post('ads/top/create', 'Management::adStore/top');
    $routes->get('ads/top/clicks', 'Management::adClicks/top');
    $routes->get('ads/top/status', 'Management::adStatus/top');
    $routes->get('ads/top/export', 'Management::adsExport/top');
    $routes->post('ads/top/bulk-end', 'Management::adsBulkEnd/top');
    $routes->post('ads/top/(:num)/decision', 'Management::adDecision/top/$1');
    $routes->get('ads/bottom', 'Management::ads/bottom');
    $routes->get('ads/bottom/create', 'Management::adCreate/bottom');
    $routes->post('ads/bottom/create', 'Management::adStore/bottom');
    $routes->get('ads/bottom/clicks', 'Management::adClicks/bottom');
    $routes->get('ads/bottom/status', 'Management::adStatus/bottom');
    $routes->get('ads/bottom/export', 'Management::adsExport/bottom');
    $routes->post('ads/bottom/bulk-end', 'Management::adsBulkEnd/bottom');
    $routes->post('ads/bottom/(:num)/decision', 'Management::adDecision/bottom/$1');

    $routes->get('terms', 'Management::terms');
    $routes->get('terms/create', 'Management::termCreate');
    $routes->post('terms/create', 'Management::termStore');
    $routes->get('terms/(:num)', 'Management::termEdit/$1');
    $routes->get('terms/(:num)/edit', 'Management::termEdit/$1');
    $routes->post('terms/(:num)/update', 'Management::termUpdate/$1');
    $routes->post('terms/(:num)/delete', 'Management::termDelete/$1');
    $routes->get('accounts', 'Management::accounts');
    $routes->get('accounts/create', 'Management::accountCreate');
    $routes->post('accounts/create', 'Management::accountStore');
    $routes->get('accounts/(:num)/edit', 'Management::accountEdit/$1');
    $routes->post('accounts/(:num)/update', 'Management::accountUpdate/$1');
    $routes->post('accounts/(:num)/status', 'Management::accountStatus/$1');
    $routes->get('codes', 'Management::codes');
    $routes->get('codes/create', 'Management::codeCreate');
    $routes->post('codes/create', 'Management::codeStore');
    $routes->get('codes/(:num)/edit', 'Management::codeEdit/$1');
    $routes->post('codes/(:num)/update', 'Management::codeUpdate/$1');
    $routes->post('codes/(:num)/delete', 'Management::codeDelete/$1');
    $routes->get('codes/(:segment)', 'Management::codes/$1');
    $routes->get('forbidden-words', 'Management::forbiddenWords');
    $routes->get('forbidden-words/create', 'Management::forbiddenWordCreate');
    $routes->post('forbidden-words/create', 'Management::forbiddenWordStore');
    $routes->get('forbidden-words/(:num)/edit', 'Management::forbiddenWordEdit/$1');
    $routes->post('forbidden-words/(:num)/update', 'Management::forbiddenWordUpdate/$1');
    $routes->post('forbidden-words/(:num)/delete', 'Management::forbiddenWordDelete/$1');
    $routes->get('stats', 'Management::placeholderPage/stats');
});

$routes->get('push-test', 'PushTest::index');
$routes->post('fc/bookmark/toggle', 'FcBookmarkController::toggle');
$routes->get('fc/bookmark/check', 'FcBookmarkController::check');
