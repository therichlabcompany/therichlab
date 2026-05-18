<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">내 정보</h1>

        <form class="form-box" method="post">
            <div class="form-field">
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
            </div>

            <div class="form-field">
                <label class="form-label" for="my-birth">생년월일</label>
                <input class="form-input" id="my-birth" name="birth" type="text" value="910404" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="my-gender">성별</label>
                <input class="form-input" id="my-gender" name="gender" type="text" value="여성" readonly />
            </div>

            <div class="form-field form-field--label-inline">
                <span class="form-label">마케팅 목적의 개인정보 수집 및 이용 동의</span>
                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="yes" checked />
                    <span>예</span>
                </label>
                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="no" />
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