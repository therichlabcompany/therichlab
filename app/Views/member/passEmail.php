<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">비밀번호 재설정</h1>
        <p class="page-main-lead">
            등록된 이메일을 입력하면<br class="br-mo" />
            비밀번호 재설정 안내를 보내드립니다.
        </p>

        <form class="form-box" action="MFC006_01_02_01_01.html" method="post">
            <div class="form-field">
                <label class="form-label" for="reset-email">이메일</label>
                <input
                    class="form-input"
                    id="reset-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="이메일을 입력해주세요." />
            </div>
            <div class="form-actions">
                <button type="submit" disabled>계속</button>
            </div>
        </form>
    </div>
</main>