<main>
    <div class="signup-done-inner">
        <h1>계정 찾기</h1>
        <p>이 휴대폰 번호로 가입한 계정을 알려드려요.</p>
        <div class="flow-result-card">
            <p class="flow-result-sub">가입 이메일</p>
            <p class="flow-result-main"><?= esc($email) ?></p>
        </div>
        <a href="<?= base_url('member/login') ?>">로그인</a>
    </div>
</main>
