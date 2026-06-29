<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">FC 회원가입</h1>
        <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br class="br-mo" />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
        </p>
        <?php
            $menu_step = "step1";
            include_once (COMPONENT_PATH . '/fc_tab_nav.php'); 
        ?>
        

        <form class="form-box" method="post">
            <div class="signup-agree">
                <label class="all c-check">
                    <input type="checkbox" name="agree_all" />
                    <span>전체 동의</span>
                </label>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_age" />
                        <span>[필수] 만 19세 이상입니다.</span>
                    </label>
                </div>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_terms" />
                        <span>[필수] 이용약관 동의</span>
                    </label>
                    <a href="javascript:void(0)" class="more" aria-label="이용약관 보기" data-popup="agree_popup"></a>
                </div>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_privacy" />
                        <span>[필수] 개인정보 수집 및 이용 동의</span>
                    </label>
                    <a href="javascript:void(0)" class="more" aria-label="개인정보 수집 및 이용 동의서 보기" data-popup="privacy_popup"></a>
                </div>

                <div>
                    <label class="c-check">
                        <input type="checkbox" name="agree_marketing" />
                        <span>[선택] 마케팅 목적의 개인정보 수집 및 이용 동의</span>
                    </label>
                    <a href="javascript:void(0)" class="more" aria-label="마케팅 동의 약관 보기" data-popup="agree_marketing"></a>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" disabled>완료</button>
            </div>
        </form>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const agreeAll = document.querySelector('input[name="agree_all"]');

    const agreeAge = document.querySelector('input[name="agree_age"]');
    const agreeTerms = document.querySelector('input[name="agree_terms"]');
    const agreePrivacy = document.querySelector('input[name="agree_privacy"]');
    const agreeMarketing = document.querySelector('input[name="agree_marketing"]');

    const submitBtn = document.querySelector('.form-actions button[type="submit"]');

    const checkboxes = [
        agreeAge,
        agreeTerms,
        agreePrivacy,
        agreeMarketing
    ];

    // 버튼 활성화 처리
    function updateButton() {
        const requiredChecked =
            agreeAge.checked &&
            agreeTerms.checked &&
            agreePrivacy.checked;

        submitBtn.disabled = !requiredChecked;
    }

    // 전체동의 상태 처리
    function updateAllCheckbox() {
        agreeAll.checked = checkboxes.every(chk => chk.checked);
    }

    // 전체동의 클릭
    agreeAll.addEventListener('change', () => {
        checkboxes.forEach(chk => {
            chk.checked = agreeAll.checked;
        });

        updateButton();
    });

    // 개별 체크박스 클릭
    checkboxes.forEach(chk => {
        chk.addEventListener('change', () => {
            updateAllCheckbox();
            updateButton();
        });
    });

    // 폼 제출
    document.querySelector('.form-box').addEventListener('submit', (e) => {
        e.preventDefault();

        if (submitBtn.disabled) {
            return;
        }

        location.href = '/member/fcJoin1';
    });

    // 초기 상태
    updateButton();

});
</script>
