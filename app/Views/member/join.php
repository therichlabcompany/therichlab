<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">회원가입</h1>

        <form id="signupForm" class="form-box" method="post" action="/member/register">
            <input type="hidden" name="member_type" value="USER">
            <input type="hidden" name="phone_verified" id="phone_verified" value="N">
            <?php include_once (COMPONENT_PATH . '/join_user_input.php'); ?>

            <div class="signup-agree">
                <label class="all c-check">
                    <input type="checkbox" name="agree_all" id = "agree_all" />
                    <span>전체 동의</span>
                </label>
                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_age"class="required-agree" />
                        <span>[필수] 만 19세 이상입니다.</span>
                    </label>
                </div>
                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_terms" class="required-agree" />
                        <span>[필수] 이용약관 동의</span>
                    </label>
                    <a href="javascript:void(0)"
                    class="more"
                    data-popup="agree_popup"
                    aria-label="이용약관 보기"></a>
                </div>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_privacy" class="required-agree" />
                        <span>[필수] 개인정보 수집 및 이용 동의</span>
                    </label>
                    <a href="javascript:void(0)"
                    class="more"
                    data-popup="privacy_popup"
                    aria-label="개인정보 보기"></a>
                </div>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_marketing" />
                        <span>[선택] 마케팅 목적의 개인정보 수집 및 이용 동의</span>
                    </label>
                    <a href="javascript:void(0)"
                    class="more"
                    data-popup="marketing_popup"
                    aria-label="마케팅 보기"></a>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="btnSubmit" disabled>가입하기</button>
            </div>
        </form>
    </div>
</main>

<?php if (!empty($mobileOkJsUrl) && !empty($mobileOkEnabled)): ?>
<script src="<?= esc($mobileOkJsUrl) ?>"></script>
<?php endif; ?>

<script>
const mobileOkEnabled = <?= json_encode((bool) ($mobileOkEnabled ?? false)) ?>;
const mobileOkRequestUrl = <?= json_encode($mobileOkRequestUrl ?? '') ?>;
const mobileOkResultUrl = <?= json_encode($mobileOkResultUrl ?? '') ?>;
const mobileOkResultCallback = 'memberPhoneAuthResult';
let emailChecked = false;
let phoneChecked = false;

function digitsOnly(value) {
    return (value || '').replace(/[^0-9]/g, '');
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
    const birthInput = document.getElementById('birth');
    const genderInput = document.getElementById('gender');
    const genderDisplay = document.getElementById('gender-display');

    const phone = digitsOnly(payload.userPhone ?? payload.phone ?? '');
    const name = (payload.userName ?? payload.name ?? '').trim();
    const birth = digitsOnly(payload.userBirthday ?? payload.birth ?? '');
    const genderRaw = String(payload.userGender ?? payload.gender ?? '').trim();

    let gender = '';
    if (genderRaw === '1' || genderRaw.toUpperCase() === 'M') {
        gender = 'M';
    } else if (genderRaw === '2' || genderRaw.toUpperCase() === 'F') {
        gender = 'F';
    }

    if (phoneInput && phone) {
        phoneInput.value = phone;
    }

    if (nameInput && name) {
        nameInput.value = name;
    }

    if (birthInput && birth) {
        birthInput.value = birth;
    }

    if (genderInput) {
        genderInput.value = gender;
    }

    if (genderDisplay) {
        genderDisplay.value = gender === 'M' ? '남성' : (gender === 'F' ? '여성' : '');
    }

    phoneChecked = true;
    setPhoneVerified(true);
    setPhoneButtonLabel('complete');
    updateSubmitState();
}

function updateSubmitState() {
    const submitBtn = document.getElementById('btnSubmit');
    const requiredChecks = document.querySelectorAll('.required-agree');
    const agreeReady = Array.from(requiredChecks).every(item => item.checked);

    if (submitBtn) {
        submitBtn.disabled = !(emailChecked && phoneChecked && agreeReady);
    }
}

function checkEmailDuplicate() {
    const emailInput = document.getElementById('email');
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        alert('올바른 이메일 형식이 아닙니다.');
        return;
    }

    fetch('/member/check-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email })
    })
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                alert('처리 중 오류가 발생했습니다.');
                return;
            }

            if (res.duplicate) {
                alert('이미 사용 중인 이메일입니다.');
                emailChecked = false;
            } else {
                alert('사용 가능한 이메일입니다.');
                emailChecked = true;
            }
            updateSubmitState();
        })
        .catch(() => {
            alert('서버 통신 실패');
        });
}

function checkPhoneDuplicate() {
    const phoneInput = document.getElementById('phone');
    const phone = digitsOnly(phoneInput.value);

    if (phone.length < 10) {
        alert('휴대폰 번호를 확인해주세요.');
        return;
    }

    fetch('/member/check-phone', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ phone })
    })
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                alert(res.message || '처리 중 오류');
                return;
            }

            if (res.duplicate) {
                alert('이미 사용 중인 휴대폰 번호입니다.');
                phoneChecked = false;
                setPhoneVerified(false);
                setPhoneButtonLabel('default');
                updateSubmitState();
                return;
            }

            alert('사용 가능한 휴대폰 번호입니다.');
            phoneChecked = true;
            setPhoneVerified(true);
            setPhoneButtonLabel('complete');
            updateSubmitState();
        })
        .catch(() => {
            alert('서버 통신 실패');
        });
}

window.memberPhoneAuthResult = function (result) {
    let payload = result;

    if (typeof result === 'string') {
        try {
            payload = JSON.parse(result);
        } catch (error) {
            payload = { resultMsg: result };
        }
    }

    if (!payload || (payload.resultCode && payload.resultCode !== '2000')) {
        alert(payload?.resultMsg || '휴대폰 인증에 실패했습니다.');
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
                phoneChecked = false;
                setPhoneVerified(false);
                setPhoneButtonLabel('default');
                updateSubmitState();
                alert(res.message || '휴대폰 인증 처리 중 오류가 발생했습니다.');
                return;
            }

            setPhoneAuthValues(res);
            setPhoneButtonLabel('complete');
            alert('휴대폰 인증이 완료되었습니다.');
        })
        .catch(() => {
            alert('서버 통신 실패');
        });
};

document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const btnEmailCheck = document.getElementById('btnEmailCheck');
    const btnPhoneCheck = document.getElementById('btnPhoneCheck');
    const agreeAll = document.getElementById('agree_all');
    const submitBtn = document.getElementById('btnSubmit');

    if (btnEmailCheck) {
        btnEmailCheck.addEventListener('click', checkEmailDuplicate);
    }

    if (emailInput) {
        emailInput.addEventListener('input', function () {
            emailChecked = false;
        });
    }

    if (btnPhoneCheck) {
        btnPhoneCheck.addEventListener('click', function () {
            if (mobileOkEnabled && window.MOBILEOK && typeof window.MOBILEOK.process === 'function' && mobileOkRequestUrl) {
                window.MOBILEOK.process(mobileOkRequestUrl, 'WB', mobileOkResultCallback);
                return;
            }

            alert('휴대폰 본인인증 설정이 완료되지 않았습니다.');
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            const value = digitsOnly(this.value).slice(0, 11);
            this.value = value;
            phoneChecked = false;
            setPhoneVerified(false);
            setPhoneButtonLabel('default');
            updateSubmitState();
        });
    }

    function checkSubmit() {
        updateSubmitState();
    }

    function bindCheckboxEvents() {
        document.querySelectorAll('.required-agree').forEach(item => {
            item.addEventListener('change', function () {
                checkSubmit();

                const allChecked = Array.from(document.querySelectorAll('.required-agree')).every(v => v.checked);
                if (agreeAll) {
                    agreeAll.checked = allChecked;
                }
            });
        });
    }

    if (agreeAll) {
        agreeAll.addEventListener('change', function () {
            document.querySelectorAll('.signup-agree input[type=checkbox]').forEach(chk => {
                if (chk !== agreeAll) {
                    chk.checked = agreeAll.checked;
                }
            });

            checkSubmit();
        });
    }

    checkSubmit();
    bindCheckboxEvents();

    if (submitBtn) {
        submitBtn.addEventListener('click', async function () {
            if (!emailChecked) {
                alert('이메일 중복확인을 해주세요.');
                return;
            }

            if (!phoneChecked) {
                alert('휴대폰 인증을 해주세요.');
                return;
            }

            const password = document.getElementById('password')?.value ?? '';
            const passwordConfirm = document.getElementById('password_confirm')?.value ?? '';
            const nameInput = document.getElementById('name');
            const birthInput = document.getElementById('birth');
            const genderInput = document.getElementById('gender');

            const name = (nameInput?.value ?? '').trim();
            const birth = digitsOnly(birthInput?.value ?? '');
            const gender = genderInput
                ? (genderInput.value || document.querySelector('input[name="gender"]:checked')?.value || '').trim()
                : (document.querySelector('input[name="gender"]:checked')?.value || '').trim();

            if (!password || !passwordConfirm || !name || !birth) {
                alert('필수 정보를 입력해주세요.');
                return;
            }

            if (password !== passwordConfirm) {
                alert('비밀번호가 일치하지 않습니다.');
                return;
            }

            if (!/^\d{8}$/.test(birth)) {
                alert('생년월일은 8자리 숫자로 입력해주세요.');
                return;
            }

            if (!gender || !['M', 'F'].includes(gender)) {
                alert('성별을 선택해주세요.');
                return;
            }

            const data = {
                member_type: document.querySelector('[name="member_type"]').value,
                email: document.getElementById('email').value.trim(),
                password: password,
                password_confirm: passwordConfirm,
                phone: digitsOnly(document.getElementById('phone').value),
                name: name,
                birth: birth,
                gender: gender,
                phone_verified: 'Y',
                agree_age: document.querySelector('[name="agree_age"]').checked,
                agree_terms: document.querySelector('[name="agree_terms"]').checked,
                agree_privacy: document.querySelector('[name="agree_privacy"]').checked,
                agree_marketing: document.querySelector('[name="agree_marketing"]').checked
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
                    location.href = '/member/joinComplete';
                } else {
                    alert(result.message || '회원가입 실패');
                }
            } catch (err) {
                alert('서버 통신 실패');
            }
        });
    }
});
</script>
