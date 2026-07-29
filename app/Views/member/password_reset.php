<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">비밀번호 재설정</h1>
        <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
        <form class="form-box" id="password-reset-form" method="post" action="<?= base_url('member/password-reset') ?>">
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <div class="form-field">
                <label class="form-label" for="reset-password">새 비밀번호</label>
                <input class="form-input" id="reset-password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="20" placeholder="새 비밀번호를 입력해주세요." required>
                <input class="form-input" id="reset-password-confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="8" maxlength="20" placeholder="새 비밀번호를 다시 한번 입력해주세요." required>
                <p id="password-reset-hint" class="form-text" aria-live="polite">8자~20자 내로 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해주세요.</p>
            </div>
            <div class="form-actions"><button id="password-reset-submit" type="submit">계속</button></div>
        </form>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('password-reset-form');
    const password = document.getElementById('reset-password');
    const confirm = document.getElementById('reset-password-confirm');
    const submit = document.getElementById('password-reset-submit');
    const hint = document.getElementById('password-reset-hint');

    function passwordIssue(value) {
        const input = String(value || '');
        if (input.length < 8 || input.length > 20) return '비밀번호는 8자 이상 20자 이하로 입력해주세요.';
        if (/\s/.test(input)) return '비밀번호에는 공백을 사용할 수 없습니다.';
        if (!/[A-Z]/.test(input)) return '비밀번호에 영문 대문자를 1자 이상 포함해주세요.';
        if (!/[a-z]/.test(input)) return '비밀번호에 영문 소문자를 1자 이상 포함해주세요.';
        if (!/\d/.test(input)) return '비밀번호에 숫자를 1자 이상 포함해주세요.';
        if (!/[^A-Za-z0-9\s]/.test(input)) return '비밀번호에 특수문자를 1자 이상 포함해주세요.';
        return '';
    }

    function updateState() {
        const issue = passwordIssue(password.value);
        const matches = password.value !== '' && password.value === confirm.value;
        password.setCustomValidity(issue);
        confirm.setCustomValidity(confirm.value !== '' && !matches ? '비밀번호 확인이 일치하지 않습니다.' : '');
        submit.setAttribute('aria-disabled', Boolean(issue) || !matches ? 'true' : 'false');

        if (issue) {
            hint.textContent = issue;
        } else if (confirm.value !== '' && !matches) {
            hint.textContent = '비밀번호 확인이 일치하지 않습니다.';
        } else {
            hint.textContent = '8자~20자 내로 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해주세요.';
        }
    }

    password.addEventListener('input', updateState);
    confirm.addEventListener('input', updateState);
    form.addEventListener('submit', function (event) {
        updateState();
        const issue = passwordIssue(password.value);
        if (issue) {
            event.preventDefault();
            alert(issue);
            password.focus();
            return;
        }
        if (password.value !== confirm.value) {
            event.preventDefault();
            alert('비밀번호 확인이 일치하지 않습니다.');
            confirm.focus();
        }
    });
    updateState();
});
</script>
