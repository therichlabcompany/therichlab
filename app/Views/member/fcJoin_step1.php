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

            <?php include_once (COMPONENT_PATH . '/join_default_input.php'); ?>

            <div class="form-actions">
                <button type="button" id="btnSubmit" disabled class="btn-primary">완료</button>
            </div>
            <p id="signupValidationHint" class="form-text" aria-live="polite"></p>
        </form>
    </div>
</main>

<?php if (!empty($mobileOkJsUrl) && !empty($mobileOkEnabled)): ?>
<script src="<?= esc($mobileOkJsUrl) ?>"></script>
<?php endif; ?>

<script>
let fcEmailChecked = false;
let fcPhoneChecked = false;

const mobileOkEnabled = <?= json_encode((bool) ($mobileOkEnabled ?? false)) ?>;
const mobileOkRequestUrl = <?= json_encode($mobileOkRequestUrl ?? '') ?>;
const mobileOkResultUrl = <?= json_encode($mobileOkResultUrl ?? '') ?>;
const mobileOkResultCallback = 'fcMemberPhoneAuthResult';

function digitsOnly(value) {
    return (value || '').replace(/[^0-9]/g, '');
}

function isValidSignupPassword(value) {
    const password = String(value || '');
    const letterCount = (password.match(/[A-Za-z]/g) || []).length;
    const numberCount = (password.match(/\d/g) || []).length;
    const specialCount = (password.match(/[^A-Za-z0-9\s]/g) || []).length;

    return password.length >= 8 && password.length <= 16 && !/\s/.test(password)
        && letterCount >= 3 && numberCount >= 3 && specialCount >= 3;
}

function updateSubmitState() {
    const btnSubmit = document.getElementById('btnSubmit');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password-confirm');
    const nameInput = document.getElementById('name');

    if (!btnSubmit || !emailInput || !phoneInput || !password || !passwordConfirm || !nameInput) {
        return;
    }

    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
    const passwordValid = isValidSignupPassword(password.value);
    const phoneValid = digitsOnly(phoneInput.value).length >= 10;
    const nameValid = nameInput.value.trim() !== '';

    const valid = emailValid && passwordValid && password.value === passwordConfirm.value
        && phoneValid && nameValid;

    btnSubmit.disabled = !valid || !fcEmailChecked || !fcPhoneChecked;

    const hint = document.getElementById('signupValidationHint');
    if (!hint) {
        return;
    }

    if (!emailValid || !passwordValid || !phoneValid || !nameValid || password.value !== passwordConfirm.value) {
        const missing = [];

        if (!emailValid) missing.push('올바른 이메일 입력');
        if (!passwordValid) missing.push('비밀번호 조건 충족');
        if (password.value && password.value !== passwordConfirm.value) missing.push('비밀번호 확인 일치');
        if (!phoneValid) missing.push('휴대폰 번호 입력');
        if (!nameValid) missing.push('이름 입력');

        hint.textContent = missing.join(', ') + '이 필요합니다.';
    } else if (!fcEmailChecked) {
        hint.textContent = '이메일 중복확인을 완료해주세요.';
    } else if (!fcPhoneChecked) {
        hint.textContent = '휴대폰 본인인증을 완료해주세요.';
    } else {
        hint.textContent = '';
    }
}

function setPhoneVerified(verified) {
    const phoneVerifiedInput = document.getElementById('phone_verified');
    if (phoneVerifiedInput) {
        phoneVerifiedInput.value = verified ? 'Y' : 'N';
    }
}

function setPhoneButtonLabel(state) {
    const button = document.getElementById('btnPhoneCheck');
    if (!button) {
        return;
    }

    const defaultLabel = button.dataset.defaultLabel || '변경/인증';
    const completeLabel = button.dataset.completeLabel || '인증완료';
    button.textContent = state === 'complete' ? completeLabel : defaultLabel;
}

function setPhoneAuthValues(payload) {
    const phoneInput = document.getElementById('phone');
    const nameInput = document.getElementById('name');

    const phone = digitsOnly(payload.userPhone ?? payload.phone ?? '');
    const name = (payload.userName ?? payload.name ?? '').trim();

    if (phoneInput && phone) {
        phoneInput.value = phone;
    }

    if (nameInput && name) {
        nameInput.value = name;
    }

    fcPhoneChecked = true;
    setPhoneVerified(true);
    setPhoneButtonLabel('complete');
    updateSubmitState();
}

document.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password-confirm');
    const btnEmailCheck = document.getElementById('btnEmailCheck');
    const btnPhoneCheck = document.getElementById('btnPhoneCheck');
    const btnSubmit = document.getElementById('btnSubmit');

    // 회원가입 화면에는 이전 수정 화면 값이나 브라우저 저장 비밀번호를 사용하지 않는다.
    if (passwordInput) passwordInput.value = '';
    if (passwordConfirmInput) passwordConfirmInput.value = '';

    document.querySelectorAll('#fcSignupForm input').forEach(el => {
        el.addEventListener('input', updateSubmitState);
    });

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
                alert(result.message || '처리 중 오류');
            }
        } catch (e) {
            alert('서버 통신 실패');
        }

        updateSubmitState();
    });

    emailInput.addEventListener('input', () => {
        fcEmailChecked = false;
        updateSubmitState();
    });

    window.fcMemberPhoneAuthResult = function (result) {
        let payload = result;

        if (typeof result === 'string') {
            try {
                payload = JSON.parse(result);
            } catch (error) {
                payload = { resultMsg: result };
            }
        }

        if (!payload || payload.status === 'error' || (payload.resultCode && payload.resultCode !== '2000')) {
            alert(payload?.message || payload?.resultMsg || '휴대폰 인증에 실패했습니다.');
            return;
        }

        fetch(mobileOkResultUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payload })
        })
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'success') {
                    fcPhoneChecked = false;
                    setPhoneVerified(false);
                    setPhoneButtonLabel('default');
                    updateSubmitState();
                    alert(res.message || '휴대폰 인증 처리 중 오류가 발생했습니다.');
                    return;
            }

            setPhoneAuthValues(res);
        })
            .catch(() => {
                alert('서버 통신 실패');
            });
    };

    btnPhoneCheck.addEventListener('click', () => {
        if (mobileOkEnabled && window.MOBILEOK && typeof window.MOBILEOK.process === 'function' && mobileOkRequestUrl) {
            window.MOBILEOK.process(mobileOkRequestUrl, 'WB', mobileOkResultCallback);
            return;
        }

        alert('휴대폰 본인인증 설정이 완료되지 않았습니다.');
    });

    phoneInput.addEventListener('input', function () {
        fcPhoneChecked = false;
        setPhoneVerified(false);
        setPhoneButtonLabel('default');

        let value = digitsOnly(this.value);

        if (value.length > 11) {
            value = value.substring(0, 11);
        }

        if (value.length > 7) {
            value = value.replace(/(\d{3})(\d{4})(\d+)/, '$1-$2-$3');
        } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d+)/, '$1-$2');
        }

        this.value = value;
        updateSubmitState();
    });

    btnSubmit.addEventListener('click', async () => {
        if (!fcEmailChecked) {
            alert('이메일 중복확인을 해주세요.');
            return;
        }

        if (!fcPhoneChecked) {
            alert('휴대폰 인증을 해주세요.');
            return;
        }

        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password-confirm').value;
        if (!isValidSignupPassword(password)) {
            alert('비밀번호는 영문 대소문자, 숫자, 특수문자를 각각 3개 이상 포함하여 8자~16자 내로 입력해주세요.');
            return;
        }
        if (password !== passwordConfirm) {
            alert('비밀번호가 일치하지 않습니다.');
            return;
        }

        const data = {
            member_type: 'FC',
            email: emailInput.value.trim(),
            password: password,
            password_confirm: passwordConfirm,
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
                location.href = '/member/fcComplete';
            } else {
                alert(result.message || '처리 실패');
            }
        } catch (e) {
            alert('서버 통신 실패');
        }
    });

    setPhoneButtonLabel('default');
    updateSubmitState();
});
</script>
