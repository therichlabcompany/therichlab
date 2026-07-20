<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">비밀번호 재설정</h1>
        <p class="page-main-lead"><?= !empty($guestMode) ? '등록된 이메일을 입력하면' : '현재 로그인한 이메일 주소를 확인하면' ?><br class="br-mo">비밀번호 재설정 안내를 보내드립니다.</p>
        <?php if (session('message')): ?><p class="insurance-in-alert"><?= esc(session('message')) ?></p><?php endif; ?>
        <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
        <form class="form-box" method="post" action="<?= esc($resetRequestAction ?? base_url('mypage/password-reset/request')) ?>">
            <div class="form-field"><label class="form-label" for="reset-email">이메일</label><input class="form-input" id="reset-email" name="email" type="email" autocomplete="email" value="<?= esc(old('email', '')) ?>" placeholder="이메일을 입력해주세요." required></div>
            <div class="form-actions"><button type="submit">메일 발송</button></div>
        </form>
    </div>
</main>
