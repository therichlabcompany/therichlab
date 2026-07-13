<main>
    <section class="page-inner">
        <h1 class="page-main-title">나의 후기</h1>

        <section class="detail-reviews">
            <header>
                <div class="section-head">
                    <div>
                        <h2 class="section-title">작성 후기</h2>
                        <p class="review-count">
                            (<span><?= number_format(count($reviewList ?? [])) ?></span> 건)
                        </p>
                    </div>

                    <div class="section-head-right">
                        <div class="control-box">
                            <button type="button" class="control-btn swiper-nav-prev" aria-label="이전 후기"></button>
                            <button type="button" class="control-btn swiper-nav-next" aria-label="다음 후기"></button>
                        </div>
                    </div>
                </div>
            </header>

            <div class="detail-reviews-panel">

                <?php if (!empty($reviewList)): ?>

                    <?php
                    $chunkSize = 5;
                    $chunks = array_chunk($reviewList, $chunkSize);
                    $enableLoop = count($reviewList) > $chunkSize;
                    ?>

                    <div class="swiper detail-reviews-swiper">
                        <div class="swiper-wrapper">

                            <?php foreach ($chunks as $chunk): ?>
                                <div class="swiper-slide">
                                    <div>

                                        <?php foreach ($chunk as $row): ?>

                                            <?php
                                            $name = $row['fc_name'] ?? '익명';
                                            $maskedName = mb_substr($name, 0, 1) . '**';
                                            ?>

                                            <a href="javascript:void(0)"
   class="review-card js-review-open"
   data-id="<?= $row['review_id'] ?>">

                                                <div class="review-card-meta">
                                                    <p class="c-rate">
                                                        <span class="c-rate-star">★</span>
                                                        <?= number_format($row['rating'] ?? 0, 1) ?>
                                                    </p>

                                                    <p class="review-author">
                                                        <?= esc($maskedName) ?>
                                                    </p>

                                                    <time class="review-date">
                                                        <?= date('Y.m.d', strtotime($row['created_at'])) ?>
                                                    </time>
                                                </div>

                                                <h4>
                                                    <?= esc($row['title']) ?>
                                                </h4>

                                                <p class="review-card-body">
                                                    <?= esc(mb_substr($row['body'], 0, 120)) ?>...
                                                </p>

                                            </a>

                                        <?php endforeach; ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                <?php else: ?>

                    <div class="empty-review">
                        <p>작성된 후기가 없습니다.</p>
                    </div>

                <?php endif; ?>

            </div>
        </section>
    </section>
</main>

<!-- is-open -->
<div class="c-modal md" id="reviewModal">
    <button type="button" class="c-modal-backdrop" data-popup-close></button>

    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">후기 상세</h2>
            <button type="button" class="c-modal-close" data-popup-close></button>
        </div>

        <div class="c-modal-body">
            <div class="story-detail-wrap">

                <button type="button" class="control-btn swiper-nav-prev"></button>

                <div class="swiper" id="reviewDetailSwiper">
                    <div class="swiper-wrapper" id="reviewDetailWrapper">
                        <!-- AJAX inject -->
                    </div>
                </div>

                <button type="button" class="control-btn swiper-nav-next"></button>

            </div>
        </div>

        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-close>닫기</button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
(function () {
    'use strict';

    if (typeof Swiper === 'undefined') return;

    var scope = document.querySelector('.detail-reviews');
    if (!scope) return;

    var el = scope.querySelector('.detail-reviews-swiper');
    if (!el) return;

    new Swiper(el, {
        speed: 450,
        slidesPerView: 1,
        spaceBetween: 0,

        // =========================
        // UX 안정 설정
        // =========================
        allowTouchMove: true,

        // loop는 데이터 많을 때만
        loop: <?= !empty($enableLoop) ? 'true' : 'false' ?>,

        watchOverflow: true,

        // =========================
        // navigation (중요)
        // =========================
        navigation: {
            nextEl: scope.querySelector('.swiper-nav-next'),
            prevEl: scope.querySelector('.swiper-nav-prev'),
        }
    });

})();
</script>
<script>
let reviewSwiper = null;

document.addEventListener('click', async function (e) {

    // =========================
    // 1. 후기 클릭
    // =========================
    const el = e.target.closest('.js-review-open');
    if (!el) return;

    const id = el.dataset.id;

    if (!id) {
        console.error('review_id 없음');
        return;
    }

    // =========================
    // 2. 중복 클릭 방지 (선택)
    // =========================
    if (el.classList.contains('is-loading')) return;
    el.classList.add('is-loading');

    try {

        // =========================
        // 3. AJAX 요청
        // =========================
        const res = await fetch('<?= base_url('mypage/reviewDetailAjax') ?>/' + id, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const text = await res.text();

        let json;
        try {
            json = JSON.parse(text);
        } catch (err) {
            console.error('JSON 파싱 실패:', text);
            alert('서버 응답 오류');
            return;
        }

        if (!json || json.result !== 'success') {
            alert(json?.message || '데이터를 불러올 수 없습니다.');
            return;
        }

        const data = json.data;

        // =========================
        // 4. 데이터 주입
        // =========================
        const wrapper = document.getElementById('reviewDetailWrapper');

        wrapper.innerHTML = `
            <div class="swiper-slide">
                <article class="story-detail-card">
                    <h3>${data.title ?? ''}</h3>

                    <div class="story-detail-meta">
                        <p class="c-rate">★ ${(Number(data.rating || 0)).toFixed(1)}</p>
                        <p>${data.fc_name ?? ''}</p>
                        <p>조회수 ${(Number(data.view_count || 0)).toLocaleString()}</p>
                        <time>${(data.created_at ?? '').substring(0,10)}</time>
                    </div>

                    <div class="story-detail-body">
                        <p>${(data.body ?? '').replace(/\n/g, '<br>')}</p>
                    </div>
                </article>
            </div>
        `;

        // =========================
        // 5. 모달 열기
        // =========================
        const modal = document.getElementById('reviewModal');
        modal.classList.add('is-open');

        // =========================
        // 6. Swiper 초기화 (안정형)
        // =========================
        setTimeout(() => {

            if (reviewSwiper) {
                reviewSwiper.destroy(true, true);
                reviewSwiper = null;
            }

            const swiperEl = document.querySelector('#reviewDetailSwiper');

            if (!swiperEl) return;

            reviewSwiper = new Swiper(swiperEl, {
                slidesPerView: 1,
                loop: false,
                speed: 400,

                navigation: {
                    nextEl: document.querySelector('#reviewModal .swiper-nav-next'),
                    prevEl: document.querySelector('#reviewModal .swiper-nav-prev'),
                }
            });

        }, 50);

    } catch (err) {
        console.error(err);
        alert('서버 오류가 발생했습니다.');

    } finally {
        el.classList.remove('is-loading');
    }

});


// =========================
// 7. 모달 닫기
// =========================
document.addEventListener('click', function (e) {

    if (e.target.matches('[data-popup-close]')) {

        const modal = document.getElementById('reviewModal');

        modal.classList.remove('is-open');

        if (reviewSwiper) {
            reviewSwiper.destroy(true, true);
            reviewSwiper = null;
        }
    }

});
</script>
