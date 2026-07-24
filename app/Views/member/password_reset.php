<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">비밀번호 재설정</h1>
        <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
        <form class="form-box" method="post" action="<?= base_url('member/password-reset') ?>">
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <div class="form-field"><label class="form-label" for="reset-password">새 비밀번호</label><input class="form-input" id="reset-password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="20" placeholder="새 비밀번호를 입력해주세요." required><input class="form-input" name="password_confirm" type="password" autocomplete="new-password" minlength="8" maxlength="20" placeholder="새 비밀번호를 다시 한번 입력해주세요." required><p class="form-text">8자~20자 내로 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해주세요.</p></div>
            <div class="form-actions"><button type="submit">계속</button></div>
        </form>
    </div>
</main>
