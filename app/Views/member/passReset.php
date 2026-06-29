<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">비밀번호 재설정</h1>

        <form class="form-box" action="MFC006_01_02_01_01_01.html" method="post">
            <div class="form-field">
                <label class="form-label" for="reset-password">새 비밀번호</label>
                <input
                    class="form-input"
                    id="reset-password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="새 비밀번호를 입력해주세요." />
                <input
                    class="form-input"
                    name="password_confirm"
                    type="password"
                    autocomplete="new-password"
                    placeholder="새 비밀번호를 다시 한번 입력해주세요." />
                <p class="form-text">영문 대소문자, 숫자, 특수문자를 3개씩 이상으로 조합해 8자 이상 16자 이내로 입력해주세요.</p>
            </div>

            <!-- 계속 버튼: 비밀번호 조건 충족 시 활성화 등은 개발에서 처리. -->
            <div class="form-actions">
                <button type="submit" disabled>계속</button>
            </div>
        </form>
    </div>
</main>