<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">FC 회원가입</h1>
        <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br class="br-mo" />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
        </p>
        <?php
            $menu_step = "step2";
            include_once (COMPONENT_PATH . '/fc_tab_nav.php'); 
        ?>
        
        <p class="signup-step-lead">
            고객이 상담을 신청할 수 있도록<br class="br-mo" />
            연락 가능한 정보를 입력해주세요.
        </p>

        <form id="fcSignupForm" class="form-box" method="post">

            <input type="hidden" name="member_type" value="FC">
            <input type="hidden" name="phone_verified" id="phone_verified" value="N">

            
            <?php include_once (COMPONENT_PATH . '/join_default_input.php');  ?>
            <div class="form-actions">
                <button type="button" id="btnSubmit" disabled class="btn-primary">완료</button>
            </div>

        </form>
    </div>
</main>

<script>
    let fcEmailChecked = false;
let fcPhoneChecked = false;

document.addEventListener('DOMContentLoaded', () => {

    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');

    const btnEmailCheck = document.getElementById('btnEmailCheck');
    const btnPhoneCheck = document.getElementById('btnPhoneCheck');
    const btnSubmit = document.getElementById('btnSubmit');

    // =========================
    // 버튼 활성화
    // =========================

    function checkSubmit() {

        const email = emailInput.value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password-confirm').value;
        const phone = phoneInput.value.trim();
        const name = document.getElementById('name').value.trim();

        const valid =
            email &&
            password &&
            passwordConfirm &&
            phone &&
            name;

        btnSubmit.disabled = !valid;
    }

    document
        .querySelectorAll('#fcSignupForm input')
        .forEach(el => {
            el.addEventListener('input', checkSubmit);
        });

    // =========================
    // 이메일 중복확인
    // =========================

    btnEmailCheck.addEventListener('click', async () => {

        const email = emailInput.value.trim();

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            alert('올바른 이메일 형식이 아닙니다.');
            return;
        }

        try {

            const res = await fetch('/member/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email })
            });

            const result = await res.json();

            if (result.status === 'success') {

                if (result.duplicate) {
                    alert('이미 사용 중인 이메일입니다.');
                    fcEmailChecked = false;
                } else {
                    alert('사용 가능한 이메일입니다.');
                    fcEmailChecked = true;
                }

            } else {
                alert('처리 중 오류');
            }

        } catch (e) {
            alert('서버 통신 실패');
        }

    });

    emailInput.addEventListener('input', () => {
        fcEmailChecked = false;
    });

    // =========================
    // 휴대폰 인증
    // =========================

    btnPhoneCheck.addEventListener('click', async () => {

        let phone = phoneInput.value.replace(/[^0-9]/g, '');

        if (phone.length < 10) {
            alert('휴대폰 번호를 확인해주세요.');
            return;
        }

        try {

            const res = await fetch('/member/check-phone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ phone })
            });

            const result = await res.json();

            if (result.status === 'success') {

                if (result.duplicate) {
                    alert('이미 사용 중인 휴대폰 번호입니다.');
                    fcPhoneChecked = false;
                } else {
                    alert('사용 가능한 휴대폰 번호입니다.');
                    fcPhoneChecked = true;
                }

            } else {
                alert(result.message || '처리 중 오류');
            }

        } catch (e) {
            alert('서버 통신 실패');
        }

    });

    phoneInput.addEventListener('input', function () {

        fcPhoneChecked = false;

        let value = this.value.replace(/[^0-9]/g, '');

        if (value.length > 11) {
            value = value.substring(0, 11);
        }

        if (value.length > 7) {
            value = value.replace(/(\d{3})(\d{4})(\d+)/, '$1-$2-$3');
        } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d+)/, '$1-$2');
        }

        this.value = value;
    });

    // =========================
    // 가입
    // =========================

    btnSubmit.addEventListener('click', async () => {

        if (!fcEmailChecked) {
            alert('이메일 중복확인을 해주세요.');
            return;
        }

        if (!fcPhoneChecked) {
            alert('휴대폰 인증을 해주세요.');
            return;
        }

        const data = {
            member_type: 'FC',
            email: emailInput.value.trim(),
            password: document.getElementById('password').value,
            password_confirm: document.getElementById('password-confirm').value,
            phone: phoneInput.value.replace(/[^0-9]/g, ''),
            name: document.getElementById('name').value.trim(),
            phone_verified: 'Y'
        };

        try {

            const res = await fetch('/member/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await res.json();

            if (result.status === 'success') {

                location.href = '/member/fcJoin2';

            } else {

                alert(result.message || '처리 실패');

            }

        } catch (e) {

            alert('서버 통신 실패');

        }

    });

});
</script>