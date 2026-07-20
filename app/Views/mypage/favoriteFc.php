<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">내 관심 FC</h1>
        <section>
            <div class="bookmark-box">
                <span><img src="<?= SITE_IMG_URL ?>images/ic-detail-bookmark-on.svg" alt="" />북마크</span>
                <strong><strong><?= number_format($favoriteCount) ?></strong></strong>
            </div>
            <p class="favorite-empty">
                관심 있는 보험 전문가를 등록해두면<br />
                언제든 빠르게 다시 찾아볼 수 있습니다.<br />
                지금 바로 나에게 맞는 FC를 찾아보세요.
            </p>
            <div class="recommend-list-head">
                <div class="control-box">
                    <button type="button" class="control-btn swiper-nav-prev" aria-label="이전 FC"></button>
                    <button type="button" class="control-btn swiper-nav-next" aria-label="다음 FC"></button>
                </div>
            </div>
            <div class="swiper recommend-list recommend-list-swiper">
    <div class="swiper-wrapper">

        <?php foreach ($favoriteChunks as $chunk): ?>
            <div class="swiper-slide">
                <div class="swiper-page-grid">

                    <?php foreach ($chunk as $row): ?>

                        <article class="card">

                            <div class="card-body">

                                <a class="card-link" href="/fc/view?uid=<?= esc($row['fc_member_uid']) ?>">

                                    <div class="profile">
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <img src="<?= esc($row['profile_image']) ?>" class="avatar" alt="" onerror="this.removeAttribute('src'); this.classList.add('is-empty');">
                                        <?php else: ?>
                                            <span class="avatar is-empty" aria-hidden="true"></span>
                                        <?php endif; ?>

                                        <div>

                                            <p class="profile-name">
                                                <?= esc($row['name']) ?>
                                            </p>

                                            <p class="c-rate">
                                                <span class="c-rate-star">★</span>
                                                <?= number_format($row['rating'], 1) ?>
                                                <span class="c-rate-count">
                                                    (<?= number_format($row['review_count']) ?>)
                                                </span>
                                            </p>

                                            <p class="c-dot-line">

                                                <?php if (!empty($row['company_line'])): ?>
                                                    <span>
                                                        <?= esc(implode(' · ', $row['company_line'])) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($row['region_label'])): ?>
                                                    <span class="location">
                                                        <?= esc($row['region_label']) ?>
                                                    </span>
                                                <?php endif; ?>

                                            </p>

                                            <div class="list-tags">

                                                <?php foreach ($row['insurance_labels'] as $tag): ?>
                                                    <span><?= esc($tag) ?></span>
                                                <?php endforeach; ?>

                                            </div>

                                        </div>
                                    </div>

                                </a>

                            </div>
                        </article>

                    <?php endforeach; ?>

                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>
        </section>
    </div>
</main>

<script>
    (function() {
        'use strict';
        if (typeof MyFC === 'undefined' || typeof Swiper === 'undefined') return;

        var scope = document.querySelector('.favorite-page section');
        var el = scope && scope.querySelector('.recommend-list-swiper');
        if (!el || !scope) return;

        var swiper = MyFC.initSwiper(el, scope, {
            navigation: false,
            speed: 400,
            slidesPerView: 1,
            spaceBetween: 12,
            watchOverflow: true,
        });

        var prevBtn = scope.querySelector('.swiper-nav-prev');
        var nextBtn = scope.querySelector('.swiper-nav-next');
        if (swiper && prevBtn && nextBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                swiper.slidePrev();
            });
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                swiper.slideNext();
            });
        }
    })();
</script>
