<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">FC 회원정보</h1>

        <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
        </p>

        <nav class="c-tabs">
            <a href="MFC005_L01_01.html" aria-current="page">기본정보 입력</a>
            <a href="MFC005_L01_01_02.html">프로필 입력</a>
            <a href="MFC005_L01_01_01.html">활동정보 입력</a>
            <a href="MFC005_L01_01_03.html">활동 스토리</a>
        </nav>

        <p class="signup-step-lead">
            고객이 확인할 프로필 정보를 입력해주세요.<br />
            정확한 정보는 신뢰도를 높입니다.
        </p>

        <form class="form-box" method="post" id="fc-member-basic-form">
            <div class="fc-profile-thumb">
                <button type="button" aria-label="프로필 이미지 등록">
                    <img src="../assets/images/temp/@profile-w.png" alt="" />
                </button>
            </div>

            <div class="form-field">
                <label class="form-label" for="fc-email">이메일</label>
                <input class="form-input" id="fc-email" name="email" type="email" value="therich@google.com" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="fc-password">비밀번호</label>
                <div class="combo">
                    <input class="form-input" id="fc-password" name="password" type="password" value="password1234!" readonly />
                    <button type="button" id="fc-password-reset">재설정</button>
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="fc-name">이름</label>
                <input class="form-input" id="fc-name" name="name" type="text" value="이민지" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="fc-phone">휴대폰 번호</label>
                <div class="combo">
                    <input class="form-input" id="fc-phone" name="phone" type="tel" value="010-1234-5678" readonly />
                    <button type="button" id="fc-phone-verify">변경/인증</button>
                </div>
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

            <div class="form-actions form-actions-split">
                <a href="MFC003_01.html" class="btn">내 프로필 페이지로 이동</a>
                <button type="submit" class="btn btn-primary">수정 완료</button>
            </div>
        </form>

        <p class="login-reset">
            <a href="MFC005_L01_01_05.html">회원탈퇴</a>
        </p>
    </div>
</main>