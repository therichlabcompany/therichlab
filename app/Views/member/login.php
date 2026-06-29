<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">로그인</h1>

        <form class="form-box" method="post">
            <div class="form-field">
                <label class="form-label" for="login-email">이메일</label>
                <input
                    class="form-input"
                    id="login-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="이메일을 입력해주세요." />
            </div>

            <div class="form-field">
                <label class="form-label" for="login-password">비밀번호</label>
                <input
                    class="form-input"
                    id="login-password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="비밀번호를 입력해주세요." />
            </div>

            <div class="login-remember">
                <label class="c-check">
                    <input type="checkbox" name="remember" />
                    <span>로그인 유지</span>
                </label>
            </div>

            <!-- 로그인 버튼: 이메일 비밀번호 입력 후 활성화 > disabled 제거 -->
            <div class="form-actions">
                <button type="submit" disabled>로그인</button>
            </div>

            <div class="login-or" role="presentation">
                <span>또는</span>
            </div>
            <div class="form-actions">
                <button type="button" class="login-secondary">계정찾기</button>
            </div>

            <p class="login-reset">
                <a href="MFC006_01_02.html">비밀번호 재설정</a>
            </p>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('.form-box');
    const email = document.getElementById('login-email');
    const password = document.getElementById('login-password');
    const submitBtn = document.querySelector('.form-actions button[type="submit"]');

    // =========================
    // 버튼 활성화
    // =========================
    function checkValid() {
        if (email.value.trim() && password.value.trim()) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    email.addEventListener('input', checkValid);
    password.addEventListener('input', checkValid);

    // =========================
    // 로그인 처리 (AJAX)
    // =========================
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        fetch('/member/loginProc', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                email: email.value.trim(),
                password: password.value,
                remember: document.querySelector('input[name="remember"]').checked ? 1 : 0
            })
        })
        .then(res => res.json())
        .then(res => {

            if (res.status === 'success') {
                alert('로그인 성공');
                location.href = '/'; // or /mypage
            } else {
                alert(res.message || '로그인 실패');
            }

        })
        .catch(() => {
            alert('서버 오류');
        });

    });

});
</script>