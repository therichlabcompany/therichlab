<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">회원가입</h1>

        <form id="signupForm" class="form-box" method="post" action="/member/register">
        <input type="hidden" name="member_type" value="USER">
        <input type="hidden" name="phone_verified" id="phone_verified" value="N">
            <?php include_once (COMPONENT_PATH . '/join_default_input.php');  ?>

            <div class="form-field">
                <label class="form-label" for="birth">생년월일</label>
                <input class="form-input" id="birth" name="birth" type="text" value="19910404" 
                required />
            </div>

            <div class="form-field">
                <label class="form-label" for="gender">성별</label>
                <input class="form-input" id="gender" name="gender" type="text" value="여성" 
                required
                />
            </div>

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
<script>
let emailChecked = false;
let phoneChecked = false;

document.addEventListener('DOMContentLoaded', function () {

    const emailInput = document.getElementById('email');
    const btnCheck = document.getElementById('btnEmailCheck');

    

    btnCheck.addEventListener('click', function () {

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
            body: JSON.stringify({
                email: email
            })
        })
        .then(res => res.json())
        .then(res => {
            console.log(res);
            if (res.status === 'success') {

                if (res.duplicate) {
                    alert('이미 사용 중인 이메일입니다.');
                    emailChecked = false;
                } else {
                    alert('사용 가능한 이메일입니다.');
                    emailChecked = true;
                }

            } else {
                alert('처리 중 오류가 발생했습니다.');
            }

        })
        .catch(() => {
            alert('서버 통신 실패');
        });

    });

    // 이메일 변경 시 재검증
    emailInput.addEventListener('input', function () {
        emailChecked = false;
    });

});


const phoneInput = document.getElementById('phone');
const btnPhoneCheck = document.getElementById('btnPhoneCheck');



btnPhoneCheck.addEventListener('click', function () {

    let phone = phoneInput.value.replace(/[^0-9]/g, '');

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
        body: JSON.stringify({
            phone: phone
        })
    })
    .then(res => res.json())
    .then(res => {

        console.log(res);

        if (res.status === 'success') {

            if (res.duplicate) {
                alert('이미 사용 중인 휴대폰 번호입니다.');
                phoneChecked = false;
            } else {
                alert('사용 가능한 휴대폰 번호입니다.');
                phoneChecked = true;
            }

        } else {
            alert(res.message || '처리 중 오류');
        }

    })
    .catch(() => {
        alert('서버 통신 실패');
    });

});

// 변경 시 초기화
phoneInput.addEventListener('input', function () {
    phoneChecked = false;
});

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('signupForm');
    const agreeAll = document.getElementById('agree_all');
    const submitBtn = document.getElementById('btnSubmit');
    const phoneInput = document.getElementById('phone');

    function getRequiredChecks(){
        return document.querySelectorAll('.required-agree');
    }

    function checkSubmit(){

        const requiredChecks = getRequiredChecks();

        let valid = true;

        requiredChecks.forEach(item => {
            if(!item.checked){
                valid = false;
            }
        });

        if(submitBtn){
            submitBtn.disabled = !valid;
        }
    }

    // =========================
    // 개별 체크박스
    // =========================
    function bindCheckboxEvents(){

        getRequiredChecks().forEach(item => {

            item.addEventListener('change', function () {

                checkSubmit();

                const allChecked =
                    Array.from(getRequiredChecks())
                        .every(v => v.checked);

                if(agreeAll){
                    agreeAll.checked = allChecked;
                }

            });

        });

    }

    // =========================
    // 전체 동의
    // =========================
    if(agreeAll){

        agreeAll.addEventListener('change', function(){

            document
                .querySelectorAll('.signup-agree input[type=checkbox]')
                .forEach(chk => {

                    if(chk !== agreeAll){
                        chk.checked = agreeAll.checked;
                    }

                });

            checkSubmit();
        });

    }

    // =========================
    // 초기 상태 반영 ⭐ 핵심
    // =========================
    checkSubmit();

    bindCheckboxEvents();

    // =========================
    // 휴대폰 포맷
    // =========================
    if(phoneInput){

        phoneInput.addEventListener('input', function(){

            let value = this.value.replace(/[^0-9]/g,'');

            
            if(!emailChecked){
                alert('이메일 중복확인을 해주세요.');
                e.preventDefault();
                return;
            }

            if(value.length > 11){
                value = value.substr(0,11);
            }

            if(value.length > 7){
                value = value.replace(/(\d{3})(\d{4})(\d+)/, '$1-$2-$3');
            } else if(value.length > 3){
                value = value.replace(/(\d{3})(\d+)/, '$1-$2');
            }

            this.value = value;
        });

    }

    
});

document.getElementById('btnSubmit').addEventListener('click', async function (e) {

    if (!emailChecked) {
        alert('이메일 중복확인을 해주세요.');
        return;
    }

    if (!phoneChecked) {
        alert('휴대폰 인증을 해주세요.');
        return;
    }

    const form = document.getElementById('signupForm');

    const data = {
        member_type: document.querySelector('[name="member_type"]').value,
        email: document.getElementById('email').value.trim(),
        password: document.getElementById('password').value,
        password_confirm: document.getElementById('password_confirm').value,
        phone: document.getElementById('phone').value.replace(/[^0-9]/g, ''),
        name: document.getElementById('name').value,
        birth: document.getElementById('birth').value,
        gender: document.getElementById('gender').value,
        phone_verified: document.getElementById('phone_verified').value,

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
            //alert('회원가입 완료');
            location.href = '/member/joinComplete';
        } else {
            alert(result.message || '회원가입 실패');
        }

    } catch (err) {
        alert('서버 통신 실패');
    }

});

</script>


<script>


</script>