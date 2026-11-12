<main>
    <section class="page-inner">
        <h1 class="page-main-title">나의 후기</h1>
        <?php if (session('message')): ?><p class="insurance-in-alert"><?= esc(session('message')) ?></p><?php endif; ?>

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
            <div class="review-modal-foot-actions">
                <button type="button" class="btn btn-line review-delete-button" id="reviewDeleteButton" hidden>삭제</button>
                <a class="btn btn-line" id="reviewEditButton" href="#" hidden>수정</a>
                <button type="button" class="btn btn-line" data-popup-close>닫기</button>
            </div>
        </div>
    </div>
</div>
<style>
.review-modal-foot-actions { display: flex; gap: 10px; width: 100%; max-width: 620px; margin: 0 auto; }
.review-modal-foot-actions .btn { flex: 1; }
.review-modal-foot-actions .review-delete-button { color: #c24141; }
.review-modal-foot-actions [hidden] { display: none; }
</style>
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
        const editButton = document.getElementById('reviewEditButton');
        const deleteButton = document.getElementById('reviewDeleteButton');
        editButton.href = '<?= base_url('mypage/reviewEdit') ?>/' + encodeURIComponent(id);
        editButton.hidden = false;
        deleteButton.dataset.reviewId = id;
        deleteButton.hidden = false;

        // =========================
        // 4. 데이터 주입
        // =========================
        const wrapper = document.getElementById('reviewDetailWrapper');

        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        const article = document.createElement('article');
        article.className = 'story-detail-card';
        const title = document.createElement('h3');
        title.textContent = data.title || '';
        article.appendChild(title);

        const meta = document.createElement('div');
        meta.className = 'story-detail-meta';
        [
            `★ ${(Number(data.rating || 0)).toFixed(1)}`,
            data.fc_name || '',
            `조회수 ${(Number(data.view_count || 0)).toLocaleString()}`
        ].forEach(function (value) {
            const item = document.createElement('p');
            item.textContent = value;
            meta.appendChild(item);
        });
        const date = document.createElement('time');
        date.textContent = data.created_date || '';
        meta.appendChild(date);
        article.appendChild(meta);

        const bodyWrap = document.createElement('div');
        bodyWrap.className = 'story-detail-body';
        const body = document.createElement('p');
        String(data.body || '').split('\n').forEach(function (line, index) {
            if (index > 0) body.appendChild(document.createElement('br'));
            body.appendChild(document.createTextNode(line));
        });
        bodyWrap.appendChild(body);
        article.appendChild(bodyWrap);
        slide.appendChild(article);
        wrapper.replaceChildren(slide);

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

document.getElementById('reviewDeleteButton').addEventListener('click', async function () {
    const reviewId = this.dataset.reviewId;
    if (!reviewId || !confirm('이 후기를 삭제하시겠습니까? 삭제 후에는 상담 현황에서 다시 작성할 수 있습니다.')) {
        return;
    }

    this.disabled = true;
    try {
        const response = await fetch('<?= base_url('mypage/reviewDeleteAjax') ?>/' + encodeURIComponent(reviewId), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const result = await response.json();
        if (result.result !== 'success') {
            alert(result.message || '후기를 삭제하지 못했습니다.');
            this.disabled = false;
            return;
        }
        window.location.href = '<?= base_url('mypage/reviewList') ?>';
    } catch (error) {
        alert('서버 오류로 후기를 삭제하지 못했습니다.');
        this.disabled = false;
    }
});
</script>
