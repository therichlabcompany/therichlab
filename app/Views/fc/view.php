<main>
    <div class="page-inner">
        <?php if (!empty($is_owner_preview)): ?>
            <div class="fc-owner-preview-notice">
                현재 관리자 승인 전입니다. 등록한 FC 정보는 본인에게만 미리보기로 표시됩니다.
            </div>
        <?php endif; ?>
        <article class="fc-detail-card">
            <div class="fc-detail-head">
                <?php $fcDetailProfileImage = profile_image_url($profile['profile_image'] ?? ''); ?>
                <?php if ($fcDetailProfileImage !== ''): ?>
                    <img src="<?= esc($fcDetailProfileImage) ?>" alt="" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
                <?php else: ?>
                    <span class="fc-detail-profile-empty" aria-hidden="true"></span>
                <?php endif; ?>
                <div class="fc-detail-head-main">
                    <!-- 소속: 보험사 최대 2곳 또는 GA 최대 1곳(데이터에 맞게 노출) -->
                    <?php
                    $companyLine = [];

                    if (!empty($profile['ga'])) {
                        $companyLine[] = $profile['ga'];
                    } else {
                        if (!empty($profile['company'])) $companyLine[] = $profile['company'];
                        if (!empty($profile['company_sub'])) $companyLine[] = $profile['company_sub'];
                    }
                    ?>

                    <?php if (!empty($companyLine)): ?>
                        <p><?= esc(implode(' · ', array_slice($companyLine, 0, 2))) ?></p>
                    <?php endif; ?>
                </div>
                <div class="fc-detail-head-actions">
                    <button type="button" class="detail-capture-btn">화면캡쳐</button>
                    <!-- <button type="button" class="fc-detail-icon-btn c-bookmark-btn" aria-label="북마크" aria-pressed="false"></button> -->
                    <button
                        type="button"
                        class="fc-detail-icon-btn c-bookmark-btn <?= $bookmark_status ? 'is-active' : '' ?>"
                        aria-label="북마크"
                        aria-pressed="<?= $bookmark_status ? 'true' : 'false' ?>"
                        data-fc-member-uid="<?= esc($member['member_uid']) ?>">
                    </button>
                    <!-- <button
                        type="button"
                        class="fc-detail-icon-btn detail-share-btn"
                        aria-label="공유"
                        data-toast="공유 링크를 준비 중입니다."></button> -->

                    <button
                        type="button"
                        class="fc-detail-icon-btn detail-share-btn"
                        aria-label="공유"></button>
                </div>
            </div>

            <div class="fc-detail">

                <!-- 활동 지역 (전체) -->
                <div class="fc-detail-item">
                    <h3>활동 지역</h3>
                    <p>
                        <?php if (!empty($activity['region'])): ?>
                            <?= implode(' ', array_map(function ($r) {
                                return fc_region_label(trim($r));
                            }, explode(',', $activity['region']))) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- 보험 항목 (전체) -->
                <div class="fc-detail-item">
                    <h3>운영 가능 보험 항목</h3>
                    <p>
                        <?php if (!empty($activity['insurance_types'])): ?>
                            <?= implode(' ', array_map(function ($i) {
                                return fc_insurance_label(trim($i));
                            }, explode(',', $activity['insurance_types']))) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- 전문 분야 (한 줄 히어로 + 전문 분야) -->
                <?php if (!empty($activity['hero_line']) || !empty($activity['specialty'])): ?>
                    <div class="fc-detail-item">
                        <h3>전문 분야</h3>
                        <p>
                            <?php if (!empty($activity['hero_line'])): ?>
                                <?= esc($activity['hero_line']) ?><br />
                            <?php endif; ?>

                            <?php if (!empty($activity['specialty'])): ?>
                                <?= nl2br(esc($activity['specialty'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- 심의필 -->
                <?php if (!empty($review)): ?>
                    <div class="fc-detail-item">
                        <h3>심의필 번호</h3>
                        <p>
                            <?= esc($review['deliberation_no']) ?>
                            <?php if (!empty($review['approval_start']) || !empty($review['approval_end'])): ?>
                                (<?= esc($review['approval_start']) ?> ~ <?= esc($review['approval_end']) ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- 상담 가능 시간 -->
                <?php if (!empty($profile['time_from']) || !empty($profile['time_to'])): ?>
                    <div class="fc-detail-item">
                        <h3>상담 가능 시간</h3>
                        <p>
                            <?= sprintf(
                                "%02d:00 ~ %02d:00",
                                (int)$profile['time_from'],
                                (int)$profile['time_to']
                            ) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- 상담 가능 언어 -->
                <?php if (!empty($profile['language'])): ?>
                    <div class="fc-detail-item">
                        <h3>상담 가능한 언어</h3>
                        <p>
                            <?= esc(fc_language_labels($profile['language'])) ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>

            <div class="fc-detail-cta-wrap">
                <a href="javascript:void(0);" class="fc-detail-cta" onclick="goCounsel('<?= esc($member['member_uid']) ?>')">상담 요청하기</a>
            </div>
        </article>

        <section class="section fc-detail-bio">
            <h2 class="section-title">경력사항</h2>
            <ul>
                <?php if (!empty($activity['career'])): ?>
                    <?php foreach (preg_split('/\r\n|\r|\n/', trim($activity['career'])) as $line): ?>
                        <?php if (trim($line) !== ''): ?>
                            <li><?= esc(trim($line)) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>

        <section class="section fc-detail-certs-section">
            <div class="fc-detail-certs-block">
                <h2 class="section-title">이력 및 인증</h2>

                <ul class="fc-detail-certs">

                    <?php foreach ($activityItems as $row): ?>

                        <?php if (empty($row['title'])) continue; ?>

                        <li>

                            <!-- 공통 제목 -->
                            <span>- <?= esc($row['title']) ?></span>

                            <!-- FILE -->
                            <?php if ($row['type'] === 'file' && !empty($row['file_path'])): ?>
                                <a
                                    href="<?= esc(base_url('uploads/activity/' . rawurlencode(basename((string) $row['file_path'])))) ?>"
                                    download
                                    class="fc-detail-cert-dl"
                                    aria-label="<?= esc($row['title']) ?> 파일 다운로드">
                                </a>
                            <?php endif; ?>

                            <!-- LINK -->
                            <?php if ($row['type'] === 'link' && !empty($row['url'])): ?>
                                <a
                                    href="<?= esc($row['url']) ?>"
                                    target="_blank"
                                    class="fc-detail-cert-dl fc-detail-cert-link"
                                    aria-label="<?= esc($row['title']) ?> 링크 이동">
                                </a>
                            <?php endif; ?>

                            <!-- TEXT -->
                            <?php if ($row['type'] === 'text' && !empty($row['content'])): ?>
                                <span class="fc-cert-text">
                                    <?= esc($row['content']) ?>
                                </span>
                            <?php endif; ?>

                        </li>

                    <?php endforeach; ?>

                </ul>
            </div>
        </section>

        <?php if (!empty($activity['intro'])): ?>
            <section class="section fc-detail-about">
                <h2 class="section-title">자기소개</h2>
                <p>
                    <?= nl2br(esc($activity['intro'])) ?>
                </p>
            </section>
        <?php endif; ?>

        <section class="section detail-reviews">
            <header>
                <div class="section-head">
                    <div>
                        <h2 class="section-title">최근 등록 후기</h2>
                        <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format($rating, 1) ?></p>
                        <p class="review-count"><span><?= number_format($rating_count) ?></span> 건</p>
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
                    $reviewIndex = 0;
                    ?>

                    <div class="swiper detail-reviews-swiper">
                        <div class="swiper-wrapper">

                            <?php foreach ($chunks as $chunk): ?>
                                <div class="swiper-slide">
                                    <div>

                                        <?php foreach ($chunk as $row): ?>

                                            <?php
                                            $name = $row['reviewer_name'] ?? '익명';
                                            $maskedName = mb_substr($name, 0, 1) . '**';

                                            $rating = $row['rating'] ?? 0;
                                            $title  = $row['title'] ?? '';
                                            $body   = $row['body'] ?? '';

                                            $date = !empty($row['created_at'])
                                                ? date('Y.m.d', strtotime($row['created_at']))
                                                : '';
                                            ?>

                                            <a href="#" class="review-card js-fc-review-open" data-review-index="<?= $reviewIndex++ ?>">

                                                <div class="review-card-meta">
                                                    <p class="c-rate">
                                                        <span class="c-rate-star">★</span>
                                                        <?= number_format($rating, 1) ?>
                                                    </p>

                                                    <p class="review-author">
                                                        <?= esc($maskedName) ?>
                                                    </p>

                                                    <time class="review-date">
                                                        <?= esc($date) ?>
                                                    </time>
                                                </div>

                                                <h4>
                                                    <?= esc($title) ?>
                                                </h4>

                                                <p class="review-card-body">
                                                    <?= esc(mb_substr($body, 0, 120)) ?>...
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
                        <div class="empty-review-inner">
                            <div class="empty-icon">💬</div>

                            <p class="empty-title">작성된 후기가 없습니다</p>
                            <p class="empty-sub">상담을 완료하면 후기를 작성할 수 있어요</p>

                            <a href="<?= !empty($is_owner) ? '/mypage/fccounsel' : '/mypage/counselList' ?>" class="empty-btn">
                                상담 내역 보기
                            </a>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </section>

        <?php if (!empty($story['story_video']) || !empty($story['story_image'])): ?>

            <section class="section fc-detail-story">
                <header class="section-head">
                    <h2 class="section-title">영상 스토리</h2>
                </header>

                <div>
                    <?php if (!empty($story['story_video'])): ?>
                        <video controls preload="metadata" playsinline style="width:100%;" <?= !empty($story['story_image']) ? 'poster="' . esc(base_url('uploads/story/main/' . rawurlencode(basename((string) $story['story_image'])))) . '"' : '' ?>>
                            <source src="<?= esc(base_url('fc/story/video/' . rawurlencode(basename((string) $story['story_video'])))) ?>">
                        </video>
                    <?php elseif (!empty($story['story_image'])): ?>
                        <img src="/uploads/story/main/<?= esc($story['story_image']) ?>" alt="스토리 이미지">
                    <?php endif; ?>
                </div>
            </section>

        <?php endif; ?>

        <?php if (!empty($storyImages)): ?>

            <section class="section fc-detail-story fc-detail-story-images">
                <header class="section-head">
                    <h2 class="section-title">활동 이미지</h2>

                    <div class="section-head-right">
                        <div class="control-box">
                            <button type="button" class="control-btn swiper-nav-prev"></button>
                            <button type="button" class="control-btn swiper-nav-next"></button>
                        </div>
                    </div>
                </header>

                <div class="swiper fc-detail-story-swiper js-story-list-swiper">
                    <div class="swiper-wrapper">

                        <?php foreach (array_chunk($storyImages, 9, true) as $imageChunk): ?>
                            <div class="swiper-slide">
                                <div class="fc-detail-story-grid">
                                    <?php foreach ($imageChunk as $idx => $img): ?>
                                        <a href="#"
                                            class="fc-story-trigger"
                                            data-index="<?= $idx ?>"
                                            aria-label="활동 이미지 상세 보기 <?= $idx + 1 ?>">
                                            <img src="/uploads/story/images/<?= esc($img['image_path']) ?>"
                                                alt="활동 이미지 <?= $idx + 1 ?>">
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </section>

        <?php endif; ?>
        <aside class="detail-notice-box">
            <h3 class="detail-notice-title">주의사항</h3>
            <p>
                MyFC는 보험설계사와 개인회원을 중개하는 중개플랫폼 역할을 하며, 등록된 FC 정보가 사실과 다를 경우 등에 따른 모든 법적
                책임(민원, 분쟁, 준법 위반 등)은 전적으로 FC 본인에게 있습니다.
            </p>
        </aside>
    </div>
</main>
<?php if (!empty($reviewList)): ?>
    <div class="c-modal md" id="fcReviewModal" aria-hidden="true">
        <button type="button" class="c-modal-backdrop" data-fc-review-close aria-label="닫기"></button>
        <div class="c-modal-panel" role="dialog" aria-modal="true" aria-labelledby="fcReviewModalTitle">
            <div class="c-modal-head">
                <h2 class="c-modal-title" id="fcReviewModalTitle">후기 상세</h2>
                <button type="button" class="c-modal-close" data-fc-review-close aria-label="닫기"></button>
            </div>
            <div class="c-modal-body">
                <div class="story-detail-wrap">
                    <button type="button" class="control-btn swiper-nav-prev" aria-label="이전 후기"></button>
                    <div class="swiper fc-review-detail-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($reviewList as $reviewRow): ?>
                                <?php
                                $reviewerName = $reviewRow['reviewer_name'] ?? '익명';
                                $reviewerMaskedName = mb_substr($reviewerName, 0, 1) . '**';
                                $reviewDate = !empty($reviewRow['created_at'])
                                    ? date('Y.m.d', strtotime($reviewRow['created_at']))
                                    : '';
                                ?>
                                <div class="swiper-slide">
                                    <article class="story-detail-card">
                                        <h3><?= esc($reviewRow['title'] ?? '') ?></h3>
                                        <div class="story-detail-meta">
                                            <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) ($reviewRow['rating'] ?? 0), 1) ?></p>
                                            <p><?= esc($reviewerMaskedName) ?></p>
                                            <time><?= esc($reviewDate) ?></time>
                                        </div>
                                        <div class="story-detail-body"><p><?= nl2br(esc($reviewRow['body'] ?? '')) ?></p></div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="control-btn swiper-nav-next" aria-label="다음 후기"></button>
                </div>
            </div>
            <div class="c-modal-foot"><button type="button" class="btn btn-line" data-fc-review-close>닫기</button></div>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($storyImages)): ?>

    <div class="c-modal md" id="storyModal">
        <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>

        <div class="c-modal-panel">
            <div class="c-modal-head">
                <h2 class="c-modal-title">활동 스토리</h2>
                <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
            </div>

            <div class="c-modal-body">
                <div class="story-detail-wrap">

                    <button type="button"
                        class="control-btn swiper-nav-prev"
                        aria-label="이전 이미지"></button>

                    <div class="swiper story-detail-swiper">
                        <div class="swiper-wrapper">

                            <?php foreach ($storyImages as $idx => $img): ?>
                                <div class="swiper-slide">
                                    <article class="story-detail-card">
                                        <div class="body">
                                            <img
                                                src="/uploads/story/images/<?= esc($img['image_path']) ?>"
                                                alt="활동 이미지 <?= $idx + 1 ?> 상세" />
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <button type="button"
                        class="control-btn swiper-nav-next"
                        aria-label="다음 이미지"></button>

                </div>
            </div>

            <div class="c-modal-foot">
                <button type="button" class="btn btn-line" data-popup-close>닫기</button>
            </div>
        </div>
    </div>

<?php endif; ?>
<style>
    .empty-review {
        width: 100%;
        min-height: 400px;

        display: flex;
        justify-content: center;
        align-items: center;

        background: #fff;
        /* ✅ 전체 흰 배경 */
    }

    .empty-review-inner {
        text-align: center;

        padding: 50px 24px;
        border-radius: 18px;

        background: #fff;
        /* 카드도 흰색 */
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 14px;
    }

    .empty-title {
        font-size: 18px;
        font-weight: 600;
        color: #111;
        margin-bottom: 6px;
    }

    .empty-sub {
        font-size: 14px;
        color: #999;
        margin-bottom: 20px;
    }

    .empty-btn {
        display: inline-block;
        padding: 11px 18px;
        border-radius: 10px;

        background: #111;
        color: #fff;
        font-size: 14px;
        text-decoration: none;

        transition: 0.2s;
    }

    .empty-btn:hover {
        opacity: 0.85;
    }

    .fc-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);

        background: rgba(0, 0, 0, 0.85);
        color: #fff;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;

        z-index: 999999;

        opacity: 0;
        visibility: hidden;
        transition: 0.25s;
    }

    .fc-toast.show {
        opacity: 1;
        visibility: visible;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    (function() {
        'use strict';

        if (typeof MyFC === 'undefined' || typeof Swiper === 'undefined') return;

        let storySwiper = null;

        const modal = document.getElementById('storyModal');
        const triggerList = document.querySelectorAll('.fc-story-trigger');

        const openModal = () => {
            modal.classList.add('is-open');
            document.body.classList.add('modal-open');
        };

        const closeModal = () => {
            modal.classList.remove('is-open');
            document.body.classList.remove('modal-open');
        };

        // =========================
        // 1. 리스트 클릭 이벤트
        // =========================
        triggerList.forEach(el => {
            el.addEventListener('click', function(e) {
                e.preventDefault();

                const index = parseInt(this.dataset.index || 0, 10);

                openModal();

                // Swiper lazy init (1회만)
                const wrap = modal.querySelector('.story-detail-swiper');
                const scope = modal.querySelector('.story-detail-wrap');

                if (!storySwiper) {
                    storySwiper = MyFC.initSwiper(wrap, scope, {
                        speed: 380,
                        slidesPerView: 1,
                        spaceBetween: 0,
                        loop: false,
                        autoHeight: true,
                        observer: true,
                        observeParents: true,
                        navigation: {
                            nextEl: scope.querySelector('.swiper-nav-next'),
                            prevEl: scope.querySelector('.swiper-nav-prev'),
                        }
                    });
                }

                // index 이동
                requestAnimationFrame(() => {
                    storySwiper.slideTo(index, 0);
                    storySwiper.update();
                });
            });
        });

        // =========================
        // 2. 모달 닫기
        // =========================
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-popup-close]')) {
                closeModal();
            }
        });

    })();

    (function() {
        'use strict';

        const reviewScope = document.querySelector('section.detail-reviews');
        const reviewList = reviewScope && reviewScope.querySelector('.detail-reviews-swiper');
        if (!reviewScope || !reviewList || typeof Swiper === 'undefined') return;

        new Swiper(reviewList, {
            slidesPerView: 1,
            loop: false,
            speed: 450,
            watchOverflow: true,
            navigation: {
                nextEl: reviewScope.querySelector('.swiper-nav-next'),
                prevEl: reviewScope.querySelector('.swiper-nav-prev'),
            },
        });
    })();

    (function() {
        'use strict';

        const storyScope = document.querySelector('section.fc-detail-story-images');
        const storyElement = storyScope && storyScope.querySelector('.js-story-list-swiper');
        if (!storyScope || !storyElement || typeof Swiper === 'undefined') return;

        new Swiper(storyElement, {
            slidesPerView: 1,
            spaceBetween: 0,
            speed: 450,
            watchOverflow: true,
            navigation: {
                nextEl: storyScope.querySelector('.swiper-nav-next'),
                prevEl: storyScope.querySelector('.swiper-nav-prev'),
            },
        });
    })();

    (function() {
        'use strict';

        const modal = document.getElementById('fcReviewModal');
        const triggers = document.querySelectorAll('.js-fc-review-open');
        if (!modal || !triggers.length || typeof Swiper === 'undefined') return;

        const swiperElement = modal.querySelector('.fc-review-detail-swiper');
        const detailScope = modal.querySelector('.story-detail-wrap');
        let reviewSwiper = null;

        const initReviewSwiper = function() {
            if (reviewSwiper) return;

            reviewSwiper = new Swiper(swiperElement, {
                slidesPerView: 1,
                loop: false,
                autoHeight: true,
            });

            detailScope.querySelector('.swiper-nav-prev').addEventListener('click', function(event) {
                event.preventDefault();
                reviewSwiper.slidePrev();
            });
            detailScope.querySelector('.swiper-nav-next').addEventListener('click', function(event) {
                event.preventDefault();
                reviewSwiper.slideNext();
            });
        };

        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                const index = Number(this.dataset.reviewIndex || 0);
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                requestAnimationFrame(function() {
                    initReviewSwiper();
                    reviewSwiper.slideTo(index, 0);
                    reviewSwiper.update();
                });
            });
        });

        modal.querySelectorAll('[data-fc-review-close]').forEach(function(button) {
            button.addEventListener('click', function() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            });
        });
    })();
</script>


<script>
    function goCounsel(memberUid) {

        <?php if (!session()->get('logged_in')): ?>
            alert('로그인 후 이용 가능합니다.');
            location.href = '<?= esc(base_url('member/login')) ?>';
            return;
        <?php endif; ?>

        <?php if (session()->get('member_type') !== 'USER'): ?>
            alert('일반 회원만 상담 신청이 가능합니다.');
            return;
        <?php endif; ?>

        location.href = '/fc/counsel?uid=' + encodeURIComponent(memberUid);
    }
</script>
<!-- =========================
     1. HTML2CANVAS CDN
========================= -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


<script>
    (function() {
        'use strict';


        /* =========================
         * TOAST SYSTEM
         * ========================= */
        function showToast(message) {

            let toast = document.querySelector('.fc-toast');

            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'fc-toast';
                document.body.appendChild(toast);
            }

            toast.textContent = message;

            toast.classList.remove('show');
            void toast.offsetWidth; // reflow

            toast.classList.add('show');

            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }


        /* =========================
         * READY
         * ========================= */
        document.addEventListener('DOMContentLoaded', function() {


            /* =========================
             * 1. 화면 캡쳐 (고품질)
             * ========================= */
            const captureBtn = document.querySelector('.detail-capture-btn');

            if (captureBtn) {
                captureBtn.addEventListener('click', async function() {

                    try {

                        if (typeof html2canvas === 'undefined') {
                            alert('html2canvas 라이브러리를 불러오지 못했습니다.');
                            return;
                        }

                        const target = document.body; // 👉 전체 페이지 캡쳐

                        showToast('전체 화면 캡쳐 준비 중...');

                        // 스크롤 안정화 (중요)
                        const originalScroll = {
                            x: window.scrollX,
                            y: window.scrollY
                        };

                        window.scrollTo(0, 0);
                        await new Promise(r => setTimeout(r, 300));

                        const canvas = await html2canvas(target, {
                            backgroundColor: '#ffffff',
                            scale: window.devicePixelRatio || 2,
                            useCORS: true,
                            allowTaint: false,
                            logging: false,

                            // 🔥 핵심: 전체 문서 캡쳐
                            scrollX: 0,
                            scrollY: 0,

                            windowWidth: document.documentElement.scrollWidth,
                            windowHeight: document.documentElement.scrollHeight
                        });

                        // 원래 위치 복원
                        window.scrollTo(originalScroll.x, originalScroll.y);

                        const image = canvas.toDataURL('image/png', 1.0);

                        const link = document.createElement('a');
                        link.href = image;
                        link.download = 'full-page-capture-' + Date.now() + '.png';

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        showToast('전체 페이지 캡쳐 완료');

                    } catch (err) {
                        console.error(err);
                        showToast('캡쳐 실패');
                    }

                });
            }


            /* =========================
             * 2. 북마크
             * ========================= */
            const bookmarkBtn = document.querySelector('.c-bookmark-btn');

            if (bookmarkBtn) {

                const fcMemberUid = bookmarkBtn.dataset.fcMemberUid;

                // 초기 상태 체크
                fetch(`/fc/bookmark/check?fc_member_uid=${encodeURIComponent(fcMemberUid)}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.bookmarked) {
                            bookmarkBtn.classList.add('is-active');
                            bookmarkBtn.setAttribute('aria-pressed', 'true');
                        }
                    });

                bookmarkBtn.addEventListener('click', function() {

                    fetch('/fc/bookmark/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'fc_member_uid=' + encodeURIComponent(fcMemberUid)
                        })
                        .then(res => res.json())
                        .then(res => {

                            if (res.result === 'added') {
                                this.classList.add('is-active');
                                this.setAttribute('aria-pressed', 'true');
                            }

                            if (res.result === 'removed') {
                                this.classList.remove('is-active');
                                this.setAttribute('aria-pressed', 'false');
                            }

                            showToast(res.msg);

                        })
                        .catch(() => {
                            showToast('처리 중 오류 발생');
                        });

                });
            }


            /* =========================
             * 3. 공유 (클립보드 복사)
             * ========================= */
            document.addEventListener('click', function(e) {

                const btn = e.target.closest('.detail-share-btn');

                if (!btn) return;

                const url = window.location.href;

                if (navigator.clipboard && window.isSecureContext) {

                    navigator.clipboard.writeText(url)
                        .then(() => showToast('링크가 복사되었습니다.'))
                        .catch(() => fallbackCopy(url));

                } else {
                    fallbackCopy(url);
                }

            });


            function fallbackCopy(text) {

                const input = document.createElement('input');
                input.value = text;

                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);

                showToast('링크가 복사되었습니다.');
            }

        });

    })();
</script>
