<nav class="c-tabs fc-info-tabs" aria-label="FC 회원정보 메뉴">
    <a href="/mypage/fcinfo" <?php if($menu_step == "step1") echo 'aria-current="page"'; ?>>기본정보 입력</a>
    <a href="/mypage/fcprofile" <?php if($menu_step == "step2") echo 'aria-current="page"'; ?>>프로필 입력</a>
    <a href="/mypage/fcactivity" <?php if($menu_step == "step3") echo 'aria-current="page"'; ?>>활동정보 입력</a>
    <a href="/mypage/fcstory" <?php if($menu_step == "step4") echo 'aria-current="page"'; ?>>활동 스토리</a>
</nav>
