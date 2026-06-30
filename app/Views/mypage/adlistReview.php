<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">광고 신청</h1>
        <p class="page-main-lead">
            우선 노출을 희망하는 영역을 선택해 주세요.<br />
            선택한 섹션은 광고 기간 동안 노출 우선권이 적용됩니다.
        </p>

        <?php
        $menu_step = "menu4";
        include_once(COMPONENT_PATH . '/fc_ad_tab_nav.php');
        ?>

        <form class="form-box" method="post" action="#">
            <div class="form-field">
                <label class="form-label" for="ad-review-trigger">후기 선택 <b>*</b></label>
                <a href="MFC005_L01_04_04_L01.html" class="directory-select" id="ad-review-trigger" aria-label="후기 선택 화면으로 이동">
                    <span class="is-placeholder">후기를 선택해주세요.</span>
                </a>
                <input id="ad-review-value" type="hidden" name="ad_review_id" value="" />
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
<!-- is-open -->
<div id="popup-ad-review" class="c-modal lg " role="dialog" aria-modal="true">
    <button type="button" class="c-modal-backdrop" data-popup-close></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">후기 선택</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <div class="detail-page">
                <section class="detail-reviews">
                    <header>
                        <div class="section-head">
                            <div class="section-head-right">
                                <div class="control-box">
                                    <button type="button" class="control-btn swiper-nav-prev" aria-label="이전 후기"></button>
                                    <button type="button" class="control-btn swiper-nav-next" aria-label="다음 후기"></button>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="detail-reviews-panel">
                        <div class="swiper detail-reviews-swiper">
                            <div class="swiper-wrapper">

                                <?php foreach ($reviewList as $index => $row): ?>
                                    <?php if ($index % 5 === 0): ?>
                                        <div class="swiper-slide">
                                            <div>
                                            <?php endif; ?>

                                            <button
                                                type="button"
                                                class="review-card c-modal-option"
                                                data-value="<?= $row['review_id'] ?>">

                                                <span class="c-modal-option-ico"></span>

                                                <div class="review-card-meta">
                                                    <p class="c-rate">★ <?= $row['rating'] ?></p>
                                                    <p class="review-author"><?= mb_substr($row['name'], 0, 1) ?>**</p>
                                                    <time><?= date('Y.m.d', strtotime($row['created_at'])) ?></time>
                                                </div>

                                                <h4><?= htmlspecialchars($row['title']) ?></h4>
                                                <p><?= htmlspecialchars($row['body']) ?></p>

                                            </button>

                                            <?php if (($index % 5 === 4) || ($index === count($reviewList) - 1)): ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" style="max-width:unset;" data-popup-confirm>확인</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    (function() {
        'use strict';
        if (typeof MyFC === 'undefined' || typeof Swiper === 'undefined') return;

        var modal = document.getElementById('popup-ad-review');
        var scope = modal && modal.querySelector('.detail-reviews');
        var el = scope && scope.querySelector('.detail-reviews-swiper');
        var swiper = null;

        if (el && scope) {
            swiper = MyFC.initSwiper(el, scope, {
                speed: 450,
                slidesPerView: 1,
                spaceBetween: 0,
                allowTouchMove: false,
                watchOverflow: true,
                loop: true,
            });
        }

        if (modal && swiper) {
            window.requestAnimationFrame(function() {
                window.requestAnimationFrame(function() {
                    if (swiper && typeof swiper.update === 'function') swiper.update();
                });
            });
        }

        var firstOpt = modal && modal.querySelector('.c-modal-option');
        if (firstOpt) firstOpt.classList.add('is-selected');
    })();
</script>
<script>
    (function() {

        const modal = document.getElementById('popup-ad-review');
        const openBtn = document.getElementById('ad-review-trigger');
        const hidden = document.getElementById('ad-review-value');
        const trigger = document.getElementById('ad-review-trigger');
        const submitBtn = document.querySelector('.form-box .btn-primary');

        if (!modal || !openBtn) return;

        // =========================
        // 모달 열기/닫기
        // =========================
        function openModal() {
            modal.classList.add('is-open');
            document.body.classList.add('popup-open');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            document.body.classList.remove('popup-open');
        }

        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });

        modal.querySelectorAll('[data-popup-close]').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        modal.querySelector('[data-popup-confirm]')?.addEventListener('click', closeModal);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });

        // =========================
        // 리뷰 선택
        // =========================
        modal.querySelectorAll('.review-card').forEach(btn => {
            btn.addEventListener('click', function() {

                const id = this.dataset.value;
                const title = this.querySelector('h4')?.innerText;

                hidden.value = id;

                if (trigger) {
                    trigger.innerHTML = `<span>${title}</span>`;
                }

                modal.querySelectorAll('.review-card').forEach(el => {
                    el.classList.remove('is-selected');
                });

                this.classList.add('is-selected');

                checkValid();
            });
        });

        // =========================
        // submit 활성화 체크
        // =========================
        function checkValid() {
            const review = hidden.value;
            submitBtn.disabled = !review;
        }

    })();
</script>
<script>
    (function() {

        const form = document.querySelector('.form-box');
        const submitBtn = form.querySelector('.btn-primary');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const reviewId = document.getElementById('ad-review-value').value;
            const plan = document.querySelector('input[name="ad_plan"]:checked')?.value;

            if (!reviewId) {
                alert('후기를 선택해주세요.');
                return;
            }

            if (!plan) {
                alert('기간을 선택해주세요.');
                return;
            }

            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('ad_review_id', reviewId);
            formData.append('ad_plan', plan);

            fetch('/mypage/ad/review', {
                    method: 'POST',
                    body: formData
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