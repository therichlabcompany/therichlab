<main>
    <div class="page-inner">
        <article class="fc-detail-card">
            <div class="fc-detail-head">
                <img src="<?= !empty($profile['profile_image']) ? '/uploads/profile/' . $profile['profile_image'] : SITE_IMG_URL . 'images/temp/@profile-m.png' ?>" alt="" />
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
                    <button type="button" class="fc-detail-icon-btn c-bookmark-btn" aria-label="북마크" aria-pressed="false"></button>
                    <button
                        type="button"
                        class="fc-detail-icon-btn detail-share-btn"
                        aria-label="공유"
                        data-toast="공유 링크를 준비 중입니다."></button>
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

                <!-- 전문 분야 (intro + hero_line 혼합) -->
                <?php if (!empty($activity['hero_line']) || !empty($activity['intro'])): ?>
                    <div class="fc-detail-item">
                        <h3>전문 분야</h3>
                        <p>
                            <?php if (!empty($activity['hero_line'])): ?>
                                <?= esc($activity['hero_line']) ?><br />
                            <?php endif; ?>

                            <?php if (!empty($activity['intro'])): ?>
                                <?= nl2br(esc($activity['intro'])) ?>
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
                            <?= esc($profile['language']) ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>

            <div class="fc-detail-cta-wrap">
                <a href="/fc/counsel" class="fc-detail-cta">상담 요청하기</a>
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
                                    href="/uploads/profile/<?= esc($row['file_path']) ?>"
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
                        <p class="c-rate"><span class="c-rate-star">★</span> 0.0</p>
                        <p class="review-count"><span>1,106</span> 건</p>
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
                <div class="swiper detail-reviews-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.9</p>
                                        <p class="review-author">정**</p>
                                        <time class="review-date">2025.10.01</time>
                                    </div>
                                    <h4>여러 보험 정보를 한눈에 비교할 수 있어 확실히 전문성이 느껴졌습니다</h4>
                                    <p class="review-card-body">
                                        그동안은 한 보험사 설계사만 만나 상품을 추천받는 게 당연하다고 생각했습니다. 하지만 MyFC에서는 여러 FC의 조건과
                                        이력을 한눈에 비교할 수 있어 훨씬 도움이 됐습니다. 원하던 보장은 충분하면서도 불필요한 비용은 줄이는 것에 딱 맞는
                                        결과를 얻을 수 있었고, 플랜 조율 덕분에 불필요한 지출을 줄일 수 있었습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 5.0</p>
                                        <p class="review-author">박**</p>
                                        <time class="review-date">2025.10.01</time>
                                    </div>
                                    <h4>꼼꼼한 상담과 빠른 대응으로 믿음이 갔습니다</h4>
                                    <p class="review-card-body">
                                        보험은 늘 어렵다고만 생각했는데, FC님이 제 상황을 자세히 들어주고 맞는 상품만 비교해 주셔서 안심할 수 있었습니다.
                                        특히 상담 후에도 카톡으로 궁금한 점을 빠르게 답변해 주셔서 신뢰가 갔습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.9</p>
                                        <p class="review-author">이**</p>
                                        <time class="review-date">2025.10.01</time>
                                    </div>
                                    <h4>아이 보험을 제대로 준비할 수 있었어요</h4>
                                    <p class="review-card-body">
                                        아이 보험은 항상 고민이었는데, FC님이 자녀 전용 상품들을 비교해 주셔서 불필요한 부분은 빼고 꼭 필요한 보장만
                                        선택할 수 있었습니다. 덕분에 보험료도 줄이고 마음도 놓였습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.9</p>
                                        <p class="review-author">정**</p>
                                        <time class="review-date">2025.10.01</time>
                                    </div>
                                    <h4>전문성이 돋보이는 상담, 부담 없는 설명</h4>
                                    <p class="review-card-body">
                                        과거에는 권유 위주의 상담만 받아왔는데, 이번엔 제 소득·생활 패턴을 고려해 객관적으로 설명해 주셔서 정말 신뢰가
                                        갔습니다. 단순히 판매가 아닌 전문가의 컨설팅 같았어요.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 5.0</p>
                                        <p class="review-author">김**</p>
                                        <time class="review-date">2025.10.01</time>
                                    </div>
                                    <h4>복잡한 보험 정리가 한 번에 끝났습니다</h4>
                                    <p class="review-card-body">
                                        그동안 여러 군데에서 가입한 보험이 생애에서 정리가 안 됐는데, FC님이 전부 검토하고 중복된 부분을 꼼꼼히 정리해
                                        주셨습니다. 덕분에 비용도 줄고, 필요한 보장만 남길 수 있었습니다.
                                    </p>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.8</p>
                                        <p class="review-author">최**</p>
                                        <time class="review-date">2025.09.28</time>
                                    </div>
                                    <h4>부모님 실비 보장을 같이 잡아 주셔서 든든했습니다</h4>
                                    <p class="review-card-body">
                                        연세 있으신 부모님 상품은 조건이 까다로워 막막했는데, FC님이 병력과 예산에 맞는 범위에서 현실적으로 정리해
                                        주셨습니다. 설명도 차분해서 부모님께도 이해시키기 쉬웠습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 5.0</p>
                                        <p class="review-author">한**</p>
                                        <time class="review-date">2025.09.25</time>
                                    </div>
                                    <h4>이직 준비하며 실업급여·실손 정리를 한 번에</h4>
                                    <p class="review-card-body">
                                        소득 공백이 걱정됐는데 공단 연계와 보험 중복까지 같이 봐 주셔서 불안이 많이 줄었습니다. 다음 직장 정해지면 다시 한
                                        번 봐도 된다고 해서 부담 없이 상담받았습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.9</p>
                                        <p class="review-author">윤**</p>
                                        <time class="review-date">2025.09.20</time>
                                    </div>
                                    <h4>비교표로 보니 제가 놓치던 특약이 보였어요</h4>
                                    <p class="review-card-body">
                                        약관만 혼자 읽기엔 한계가 있는데, 표로 정리해 주신 덕분에 보장 범위 차이가 한눈에 들어왔습니다. 필요 없는 특약은
                                        과감히 빼고 핵심만 남겼습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 4.7</p>
                                        <p class="review-author">조**</p>
                                        <time class="review-date">2025.09.15</time>
                                    </div>
                                    <h4>야근이 잦아도 상담 일정 맞춰 주셔서 감사했습니다</h4>
                                    <p class="review-card-body">
                                        평일 저녁이나 짧은 통화로도 진행이 가능해서 직장인에게 부담이 적었습니다. 다음 단계 진행 시에도 같은 방식으로
                                        이어가고 싶습니다.
                                    </p>
                                </a>
                                <a href="#" class="review-card">
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> 5.0</p>
                                        <p class="review-author">송**</p>
                                        <time class="review-date">2025.09.10</time>
                                    </div>
                                    <h4>주택담보대출과 보험료 밸런스를 같이 봐 주셨어요</h4>
                                    <p class="review-card-body">
                                        월 상환과 보험료가 겹쳐 총 현금 흐름이 빠듯했는데, 우선순위를 정해 단계적으로 조정할 수 있었습니다. 숫자 근거가
                                        있어 설득력이 있었습니다.
                                    </p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($story['story_video']) || !empty($story['story_image'])): ?>

            <section class="section fc-detail-story">
                <header class="section-head">
                    <h2 class="section-title">영상 스토리</h2>
                </header>

                <div>
                    <?php if (!empty($story['story_image'])): ?>
                        <img src="/uploads/story/main/<?= esc($story['story_image']) ?>" alt="스토리 이미지">
                    <?php elseif (!empty($story['story_video'])): ?>
                        <video controls style="width:100%;">
                            <source src="/uploads/story/video/<?= esc($story['story_video']) ?>" type="video/mp4">
                        </video>
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

                        <?php foreach ($storyImages as $idx => $img): ?>
                            <div class="swiper-slide">
                                <a href="#"
                                class="fc-story-trigger"
                                data-index="<?= $idx ?>"
                                aria-label="활동 이미지 상세 보기 <?= $idx + 1 ?>">
                                    <img src="/uploads/story/images/<?= esc($img['image_path']) ?>"
                                        alt="활동 이미지 <?= $idx + 1 ?>">
                                </a>
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
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function () {
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
        el.addEventListener('click', function (e) {
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
    document.addEventListener('click', function (e) {
        if (e.target.matches('[data-popup-close]')) {
            closeModal();
        }
    });

})();

(function () {
    'use strict';

    if (typeof MyFC === 'undefined' || typeof Swiper === 'undefined') return;

    var storyScope = document.querySelector('section.fc-detail-story-images');
    var storyEl = storyScope && storyScope.querySelector('.js-story-list-swiper');

    if (storyEl && storyScope) {
        MyFC.initSwiper(storyEl, storyScope, {
            speed: 450,
            slidesPerView: 3,
            spaceBetween: 8,
            grabCursor: true,
            watchOverflow: true,
            breakpoints: {
                0: {
                    slidesPerView: 2,
                    spaceBetween: 6
                },
                641: {
                    slidesPerView: 3,
                    spaceBetween: 8
                }
            }
        });
    }
})();
</script>