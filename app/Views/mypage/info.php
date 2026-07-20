<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">내 정보</h1>

        <form class="form-box" method="post">

            <?php include_once (COMPONENT_PATH . '/join_default_input.php');  ?>
            <!-- <div class="form-field">
                <label class="form-label" for="my-email">이메일</label>
                <input class="form-input" id="my-email" name="email" type="email" value="therich@google.com" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="my-password">비밀번호</label>
                <div class="combo">
                    <input class="form-input" id="my-password" name="password" type="password" value="password1234!" readonly />
                    <button type="button">재설정</button>
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="my-name">이름</label>
                <input class="form-input" id="my-name" name="name" type="text" value="이민지" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="my-phone">휴대폰 번호</label>
                <div class="combo">
                    <input class="form-input" id="my-phone" name="phone" type="tel" value="010-1234-5678" readonly />
                    <button type="button">변경/인증</button>
                </div>
            </div> -->

            <div class="form-field">
                <label class="form-label" for="my-birth">생년월일</label>
                <input class="form-input" id="my-birth" name="birth" type="text"
                    value="<?= esc($user['birth'] ?? '') ?>"  />
            </div>

            <div class="form-field">
                <label class="form-label">성별</label>

                <label class="c-radio">
                    <input type="radio" name="gender" value="M"
                        <?= ($user['gender'] ?? '') === 'M' ? 'checked' : '' ?> />
                    <span>남성</span>
                </label>

                <label class="c-radio">
                    <input type="radio" name="gender" value="F"
                        <?= ($user['gender'] ?? '') === 'F' ? 'checked' : '' ?> />
                    <span>여성</span>
                </label>
            </div>

            <div class="form-field form-field--label-inline">
                <span class="form-label">마케팅 목적의 개인정보 수집 및 이용 동의</span>

                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="1"
                        <?= !empty($user['agree_marketing']) ? 'checked' : '' ?> />
                    <span>예</span>
                </label>

                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="0"
                        <?= empty($user['agree_marketing']) ? 'checked' : '' ?> />
                    <span>아니오</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit">수정 완료</button>
            </div>
        </form>

        <p class="login-reset">
            <a href="/mypage/withdrawal">회원탈퇴</a>
        </p>
    </div>
</main>
<?php include_once (COMPONENT_PATH . '/mobileok_phone_edit.php'); ?>
<script>
document.querySelector('.form-box').addEventListener('submit', function(e){
    e.preventDefault();

    const form = this;

    const email = form.email.value.trim();
    const phone = form.phone.value.trim();
    const name  = form.name.value.trim();
    const agreeMarketing = form.querySelector('input[name="agree_marketing"]:checked');

    // =========================
    // 1. 필수값 체크
    // =========================
    if (!email) {
        alert('이메일을 입력해주세요.');
        form.email.focus();
        return;
    }

    if (!phone) {
        alert('휴대폰 번호를 입력해주세요.');
        form.phone.focus();
        return;
    }

    if (!name) {
        alert('이름을 입력해주세요.');
        form.name.focus();
        return;
    }

    const gender = form.querySelector('input[name="gender"]:checked');

    if (!gender) {
        alert('성별을 선택해주세요.');
        return;
    }

    if (!agreeMarketing) {
        alert('마케팅 동의 여부를 선택해주세요.');
        return;
    }

    // =========================
    // 2. 이메일 형식 체크 (간단 버전)
    // =========================
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('이메일 형식이 올바르지 않습니다.');
        form.email.focus();
        return;
    }

    // =========================
    // 3. AJAX 요청
    // =========================
    fetch('/mypage/updateInfo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(new FormData(form))
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '수정 실패');
        }

    })
    .catch(() => {
        alert('오류가 발생했습니다.');
    });

});
</script>
