<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">광고 신청</h1>
        <p class="page-main-lead">
            우선 노출을 희망하는 영역을 선택해 주세요.<br />
            선택한 섹션은 광고 기간 동안 노출 우선권이 적용됩니다.
        </p>

        <?php
              $menu_step = "menu3";
              include_once (COMPONENT_PATH . '/fc_ad_tab_nav.php'); 
          ?>

        <form class="form-box" method="post" action="#">
            <div class="form-field">
                <label class="form-label" for="ad-insurance-trigger">보험 항목 <b>*</b></label>
                <button
                    type="button"
                    class="directory-select"
                    id="ad-insurance-trigger"
                    data-popup-target="#popup-ad-insurance"
                    data-popup-sync="#ad-insurance-value">
                    <span class="is-placeholder">보험 항목을 선택해주세요.</span>
                </button>
                <input id="ad-insurance-value" type="hidden" name="ad_insurance_type" value="" />
            </div>

            <div class="form-field">
                <span class="form-label">광고 기간 및 금액 선택 <b>*</b></span>
                <div class="ad-apply-plan-row">
                    <label class="c-radio">
                        <input type="radio" name="ad_plan" value="1m" checked />
                        <span>1개월</span>
                    </label>
                    <span class="ad-apply-price">500,000 원</span>
                </div>
            </div>

            <section class="gray-box">
                <ul class="dash-list">
                    <li>
                        광고를 신청하시면 담당자 확인 후
                        <strong class="warn">이메일/카카오톡 메시지로 안내장을 발송</strong>
                        해드립니다.
                    </li>
                    <li>섹션별 <span class="uline">노출 순서</span>는 <strong class="warn">랜덤</strong>으로 노출됩니다.</li>
                    <li>광고 노출 기간 종료 시 자동으로 비활성화 됩니다.</li>
                    <li>결제 완료 후에는 광고 <strong class="warn">취소 및 환불이 불가</strong>합니다.</li>
                </ul>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" disabled>신청하기</button>
            </div>
        </form>
    </div>
</main>
<div id="popup-ad-insurance" class="c-modal">
    <button type="button" class="c-modal-backdrop" data-popup-close></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">보험 항목</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <ul class="c-modal-list">
                <li>
                    <button type="button" class="c-modal-option" data-value="whole_life">
                        <span class="c-modal-option-label">종신보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="cancer">
                        <span class="c-modal-option-label">암보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="brain_cardio">
                        <span class="c-modal-option-label">뇌심장보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="indemnity">
                        <span class="c-modal-option-label">실비보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="child">
                        <span class="c-modal-option-label">자녀/태아보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="dementia">
                        <span class="c-modal-option-label">치매/간병보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="dental">
                        <span class="c-modal-option-label">치아보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="pension">
                        <span class="c-modal-option-label">연금/변액보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="business">
                        <span class="c-modal-option-label">사업자보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="driver">
                        <span class="c-modal-option-label">운전자보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="car">
                        <span class="c-modal-option-label">자동차보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="fire">
                        <span class="c-modal-option-label">화재보험</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-confirm>확인</button>
        </div>
    </div>
</div>


<script>
(function () {

    const form = document.querySelector('.form-box');
    const submitBtn = form.querySelector('.btn-primary');

    const hiddenInsurance = document.getElementById('ad-insurance-value');
    const trigger = document.getElementById('ad-insurance-trigger');

    function getPlan() {
        return document.querySelector('input[name="ad_plan"]:checked')?.value;
    }

    function getInsurance() {
        return hiddenInsurance.value;
    }

    function checkValid() {
        const plan = getPlan();
        const insurance = getInsurance();

        submitBtn.disabled = !(plan && insurance);
    }

    // =========================
    // 보험 선택 (modal)
    // =========================
    document.querySelectorAll('#popup-ad-insurance .c-modal-option')
        .forEach(btn => {
            btn.addEventListener('click', function () {

                const value = this.dataset.value;
                const label = this.querySelector('.c-modal-option-label')?.innerText;

                hiddenInsurance.value = value;

                trigger.innerHTML = `<span>${label}</span>`;

                checkValid();
            });
        });

    document.addEventListener('change', checkValid);

    // =========================
    // AJAX submit
    // =========================
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const ad_plan = getPlan();
        const insurance = getInsurance();

        if (!ad_plan) {
            alert('광고 기간을 선택해주세요.');
            return;
        }

        if (!insurance) {
            alert('보험 항목을 선택해주세요.');
            return;
        }

        submitBtn.disabled = true;

        fetch('/mypage/ad/product-fc', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                ad_plan,
                ad_insurance_type: insurance
            })
        })
        .then(res => res.json())
        .then(res => {

            if (res.result === 'success') {
                location.href = '/mypage/adLast';
                return;
            }

            alert(res.msg || '실패');
            submitBtn.disabled = false;
        })
        .catch(() => {
            alert('서버 오류');
            submitBtn.disabled = false;
        });

    });

})();
</script>