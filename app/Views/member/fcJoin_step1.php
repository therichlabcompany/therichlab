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
                <button type="button" id="btnSubmit" aria-disabled="true" class="btn-primary is-pending">완료</button>
            </div>
            <p id="signupValidationHint" class="form-text" aria-live="polite">이메일 중복확인, 비밀번호 조건, 휴대폰 본인인증이 필요합니다.</p>
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
const mobileOkUseRedirect = <?= json_encode((bool) ($mobileOkUseRedirect ?? false)) ?>;
const mobileOkAuthResult = <?= json_encode($mobileOkAuthResult ?? null) ?>;

function digitsOnly(value) {
    return (value || '').replace(/[^0-9]/g, '');
}

function isValidSignupPassword(value) {
    const password = String(value || '');
    return password.length >= 8 && password.length <= 20 && !/\s/.test(password)
        && /[A-Z]/.test(password)
        && /[a-z]/.test(password)
        && /\d/.test(password)
        && /[^A-Za-z0-9\s]/.test(password);
}

function signupPasswordIssue(value) {
    const password = String(value || '');
    if (password.length < 8 || password.length > 20) return '비밀번호는 8자 이상 20자 이하로 입력해주세요.';
    if (/\s/.test(password)) return '비밀번호에는 공백을 사용할 수 없습니다.';
    if (!/[A-Z]/.test(password)) return '비밀번호에 영문 대문자를 1자 이상 포함해주세요.';
    if (!/[a-z]/.test(password)) return '비밀번호에 영문 소문자를 1자 이상 포함해주세요.';
    if (!/\d/.test(password)) return '비밀번호에 숫자를 1자 이상 포함해주세요.';
    if (!/[^A-Za-z0-9\s]/.test(password)) return '비밀번호에 특수문자를 1자 이상 포함해주세요.';
    return '';
}

function updateSubmitState() {
    const btnSubmit = document.getElementById('btnSubmit');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password-confirm');
    const nameInput = document.getElementById('name');
    const birthInput = document.getElementById('birth');
    const genderInput = document.getElementById('gender');

    if (!btnSubmit || !emailInput || !phoneInput || !password || !passwordConfirm || !nameInput || !birthInput || !genderInput) {
        return;
    }

    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
    const passwordValid = isValidSignupPassword(password.value);
    const phoneValid = digitsOnly(phoneInput.value).length >= 10;
    const nameValid = nameInput.value.trim() !== '';
    const birthValid = /^\d{8}$/.test(digitsOnly(birthInput.value));
    const genderValid = ['M', 'F'].includes(genderInput.value);

    const valid = emailValid && passwordValid && password.value === passwordConfirm.value
        && phoneValid && nameValid && birthValid && genderValid;

    const ready = valid && fcEmailChecked && fcPhoneChecked;
    // 미완료 상태에서도 눌러 부족한 항목과 입력 위치를 즉시 안내한다.
    btnSubmit.disabled = false;
    btnSubmit.classList.toggle('is-pending', !ready);
    btnSubmit.setAttribute('aria-disabled', ready ? 'false' : 'true');

    const hint = document.getElementById('signupValidationHint');
    if (!hint) {
        return;
    }

    if (!emailValid || !passwordValid || !phoneValid || !nameValid || !birthValid || !genderValid || password.value !== passwordConfirm.value) {
        const missing = [];

        if (!emailValid) missing.push('올바른 이메일 입력');
        if (!passwordValid) missing.push(signupPasswordIssue(password.value).replace('비밀번호에 ', '').replace('해주세요.', ''));
        if (password.value && password.value !== passwordConfirm.value) missing.push('비밀번호 확인 일치');
        if (!phoneValid) missing.push('휴대폰 번호 입력');
        if (!nameValid || !birthValid || !genderValid) missing.push('휴대폰 본인인증');

        hint.textContent = missing.join(', ') + '이 필요합니다.';
    } else if (!fcEmailChecked) {
        hint.textContent = '이메일 중복확인을 완료해주세요.';
    } else if (!fcPhoneChecked) {
        hint.textContent = '휴대폰 본인인증을 완료해주세요.';
    } else {
        hint.textContent = '';
    }
}

function focusSignupIssue() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password-confirm');
    const phoneButton = document.getElementById('btnPhoneCheck');
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email?.value.trim() ?? '');
    const passwordIssue = signupPasswordIssue(password?.value ?? '');

    if (!emailValid) return { message: '올바른 이메일을 입력해주세요.', target: email };
    if (!fcEmailChecked) return { message: '이메일 중복확인을 완료해주세요.', target: document.getElementById('btnEmailCheck') };
    if (passwordIssue) return { message: passwordIssue, target: password };
    if ((password?.value ?? '') !== (passwordConfirm?.value ?? '')) return { message: '비밀번호 확인이 일치하지 않습니다.', target: passwordConfirm };
    const birth = digitsOnly(document.getElementById('birth')?.value ?? '');
    const gender = document.getElementById('gender')?.value ?? '';
    const name = document.getElementById('name')?.value.trim() ?? '';
    if (!fcPhoneChecked || name === '' || !/^\d{8}$/.test(birth) || !['M', 'F'].includes(gender)) {
        return { message: '휴대폰 본인인증을 완료해주세요.', target: phoneButton };
    }
    return null;
}

function setPhoneVerified(verified) {
    const phoneVerifiedInput = document.getElementById('phone_verified');
    if (phoneVerifiedInput) {
        phoneVerifiedInput.value = verified ? 'Y' : 'N';
    }
}

function setPhoneInputLocked(locked) {
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.readOnly = locked;
    }
}

function setPhoneButtonLabel(state) {
    const button = document.getElementById('btnPhoneCheck');
    if (!button) {
        return;
    }

    const defaultLabel = button.dataset.defaultLabel || '변경/인증';
    const completeLabel = button.dataset.completeLabel || '다시 인증';
    button.textContent = state === 'complete' ? completeLabel : defaultLabel;
}

function setPhoneAuthValues(payload) {
    const phoneInput = document.getElementById('phone');
    const nameInput = document.getElementById('name');
    const birthInput = document.getElementById('birth');
    const genderInput = document.getElementById('gender');
    const genderDisplay = document.getElementById('gender-display');

    const phone = digitsOnly(payload.userPhone ?? payload.phone ?? '');
    const name = (payload.userName ?? payload.name ?? '').trim();
    const birth = digitsOnly(payload.userBirthday ?? payload.birth ?? '');
    const genderRaw = String(payload.userGender ?? payload.gender ?? '').trim().toUpperCase();
    const gender = (genderRaw === '1' || genderRaw === 'M')
        ? 'M'
        : ((genderRaw === '2' || genderRaw === 'F') ? 'F' : '');

    if (phoneInput && phone) {
        phoneInput.value = phone;
    }

    if (nameInput && name) {
        nameInput.value = name;
    }
    if (birthInput && birth) birthInput.value = birth;
    if (genderInput && ['M', 'F'].includes(gender)) genderInput.value = gender;
    if (genderDisplay) genderDisplay.value = gender === 'M' ? '남성' : (gender === 'F' ? '여성' : '');

    fcPhoneChecked = true;
    setPhoneVerified(true);
    setPhoneInputLocked(true);
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
                    alert(result.message || '이미 사용 중인 이메일입니다.');
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
                    setPhoneInputLocked(false);
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
        if (fcPhoneChecked) {
            fcPhoneChecked = false;
            setPhoneVerified(false);
            setPhoneButtonLabel('default');
            updateSubmitState();
        }

        if (mobileOkEnabled && window.MOBILEOK && typeof window.MOBILEOK.process === 'function' && mobileOkRequestUrl) {
            window.MOBILEOK.process(
                mobileOkRequestUrl,
                mobileOkUseRedirect ? 'MB' : 'WB',
                mobileOkUseRedirect ? '' : mobileOkResultCallback
            );
            return;
        }

        alert('휴대폰 본인인증 설정이 완료되지 않았습니다.');
    });

    phoneInput.addEventListener('input', function () {
        fcPhoneChecked = false;
        setPhoneVerified(false);
        setPhoneInputLocked(false);
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
        const signupIssue = focusSignupIssue();
        if (signupIssue) {
            alert(signupIssue.message);
            signupIssue.target?.focus();
            return;
        }

        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password-confirm').value;
        if (!isValidSignupPassword(password)) {
            alert('비밀번호는 8자~20자이며 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해야 합니다.');
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
            birth: document.getElementById('birth').value.trim(),
            gender: document.getElementById('gender').value,
            phone_verified: 'Y'
        };

        btnSubmit.disabled = true;

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
        } finally {
            updateSubmitState();
        }
    });

    setPhoneButtonLabel('default');
    if (mobileOkAuthResult) {
        setPhoneAuthValues(mobileOkAuthResult);
    }
    updateSubmitState();
});
</script>
