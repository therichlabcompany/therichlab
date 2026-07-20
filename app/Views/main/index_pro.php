<?php
$adFcList = $ad_fc_list ?? [];
$mainRecommendList = $adFcList;
$productFcList = $product_fc_list ?? [];
$languageFcList = $language_fc_list ?? [];
$reviewList = $review_list ?? [];
$insuranceInList = $insurance_in_list ?? [];
$regionOptions = $region_options ?? [];
$insuranceOptions = $insurance_options ?? [];
$languageOptions = $language_options ?? [];
$topBannerAds = $top_banner_ads ?? [];
$bottomBannerAds = $bottom_banner_ads ?? [];

$mainImageUrl = static function (?string $path, string $fallback = '', string $directory = 'banner'): string {
    $path = trim((string) $path);
    if ($path === '') {
        return $fallback;
    }
    if (preg_match('#^https?://#', $path) === 1) {
        return $path;
    }
    if (strpos($path, '/') === 0) {
        return $path;
    }
    return '/uploads/' . trim($directory, '/') . '/' . $path;
};

$mainProfileImageUrl = static fn (?string $path): string => profile_image_url($path);

$mainFcRegionLabel = static function (array $row): string {
    $regions = array_filter(array_map('trim', explode(',', (string) ($row['region'] ?? ''))));
    if (!$regions) {
        return '-';
    }
    return fc_region_label((string) reset($regions));
};

$mainFcRegionLabels = static function (array $row, int $limit = 3): string {
    $regions = array_filter(array_map('trim', explode(',', (string) ($row['region'] ?? ''))));
    if (!$regions) {
        return '-';
    }
    $labels = array_map(static fn ($item) => fc_region_label($item), array_slice($regions, 0, $limit));
    return implode(', ', $labels);
};

$mainFilterCsv = static function (array $values): string {
    $values = array_values(array_unique(array_filter(array_map(static fn ($value) => trim((string) $value), $values), static fn ($value) => $value !== '')));
    return implode(',', $values);
};

$mainRegionFilterValues = static function (array $row) use ($mainFilterCsv): string {
    $values = [];
    if (!empty($row['region_code'])) {
        $regionCode = trim((string) $row['region_code']);
        return $mainFilterCsv([$regionCode, fc_region_label($regionCode)]);
    }
    foreach (array_filter(array_map('trim', explode(',', (string) ($row['region'] ?? '')))) as $item) {
        $values[] = $item;
        $values[] = fc_region_label($item);
    }
    return $mainFilterCsv($values);
};

$mainInsuranceFilterValues = static function (array $row) use ($mainFilterCsv): string {
    $values = [];
    if (!empty($row['insurance_type'])) {
        $insuranceType = trim((string) $row['insurance_type']);
        return $mainFilterCsv([$insuranceType, fc_insurance_label($insuranceType)]);
    }
    foreach (array_filter(array_map('trim', explode(',', (string) ($row['insurance_types'] ?? '')))) as $item) {
        $values[] = $item;
        $values[] = fc_insurance_label($item);
    }
    return $mainFilterCsv($values);
};

$mainLanguageFilterValues = static function (array $row) use ($mainFilterCsv): string {
    $values = [];
    // 언어별 광고의 단일 언어와 FC 프로필의 복수 상담 언어를 모두 필터에 반영한다.
    foreach ([(string) ($row['language_code'] ?? ''), (string) ($row['language'] ?? '')] as $languageValues) {
        foreach (array_filter(array_map('trim', explode(',', $languageValues))) as $item) {
            $code = fc_language_normalize($item);
            $values[] = $item;
            $values[] = $code !== '' ? $code : $item;
            $values[] = $code !== '' ? fc_language_label($code) : $item;
        }
    }
    return $mainFilterCsv($values);
};

$mainFcCompanies = static function (array $row): string {
    $items = array_filter(array_map('trim', [
        (string) ($row['company'] ?? ''),
        (string) ($row['company_sub'] ?? ''),
        (string) ($row['ga'] ?? ''),
    ]));
    $uniqueItems = [];
    foreach ($items as $item) {
        if (!in_array($item, $uniqueItems, true)) {
            $uniqueItems[] = $item;
        }
    }
    return $uniqueItems ? implode(' · ', array_slice($uniqueItems, 0, 2)) : '-';
};

$mainFcInsuranceTags = static function (array $row): array {
    $items = array_filter(array_map('trim', explode(',', (string) ($row['insurance_types'] ?? ''))));
    $items = array_values(array_unique($items));
    return array_map(static fn ($item) => fc_insurance_label($item), array_slice($items, 0, 6));
};

$mainFcHref = static function (array $row): string {
    if (!empty($row['ad_id'])) {
        return base_url('ad/click/' . (int) $row['ad_id']);
    }
    return base_url('fc/view/?uid=' . rawurlencode((string) ($row['member_uid'] ?? '')));
};

$mainMaskName = static function (?string $name): string {
    $name = trim((string) $name);
    if ($name === '') {
        return '-';
    }
    return mb_substr($name, 0, 1) . '**';
};

$mainChunks = static function (array $rows, int $size): array {
    return array_chunk($rows, $size);
};
?>
<!-- 메인 -->

<!-- 히어로 -->
<section class="hero">
    <!-- 배경 비디오: main-visual.mp4로 대체 -->
    <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?= SITE_IMG_URL ?>images/main-visual.jpg">
        <source src="<?= SITE_IMG_URL ?>images/main-visual.mp4" type="video/mp4" />
    </video>
    <div class="hero-inner">
        <div class="hero-head">
            <h1 class="hero-title">검증된 보험설계사만 있는 곳 MyFC</h1>
            <p class="hero-eyebrow">
                경험과 이력이 검증된 신뢰할 수 있는 <br class="br-mo" />보험설계사와 만나보세요. <br />
                MyFC에서 경험 많은 전문가 정보를<br class="br-mo" />
                투명하게 제공해드립니다.
            </p>
        </div>
        <div class="hero-align">
            <div class="hero-content">
                <p class="hero-desc">검증된 보험설계사를 빠르게 찾아보고, 안전하게 시작해보세요.</p>
            </div>
            <div class="hero-cta">
                <a href="/mypage/favoriteFc" class="btn btn-myfc">나의 FC 보기</a>
                <a href="/fc/list" class="btn btn-fc-list">FC 리스트 보기</a>
            </div>
        </div>
    </div>
</section>
<main>
    <div class="page-inner">
        <!-- 추천 FC -->
        <section class="section recommend-section">
            <div class="section-head">
                <h3 class="section-title">MyFC가 엄선한 보험설계사 추천 <span class="ad-mark" aria-hidden="true">AD</span></h3>
                <div class="section-head-right">
                    <a href="<?= base_url('fc/list') ?>" class="section-more">전체보기</a>
                    <div class="control-box">
                        <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                        <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                    </div>
                </div>
            </div>
            <div class="chip-scroll">
                <div class="chip-row" data-toggle-group="filter-location">
                    <?php foreach ($regionOptions as $index => $option): ?>
                        <button type="button" class="filter-btn <?= $index === 0 ? 'is-active' : '' ?>" data-toggle-item data-value="<?= esc($option['value']) ?>">
                            <?= esc($option['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="fcSwiper" class="swiper recommend-list">
                <div class="swiper-wrapper">
                    <?php if (!empty($mainRecommendList)): ?>
                        <?php foreach ($mainRecommendList as $adFc): ?>
                            <?php
                            $tags = $mainFcInsuranceTags($adFc);
                            $profileImage = $mainProfileImageUrl($adFc['profile_image'] ?? '');
                            ?>
                            <div class="swiper-slide" data-filter-values="<?= esc($mainRegionFilterValues($adFc)) ?>">
                                <article class="card">
                                    <div class="card-body">
                                        <a class="card-link" href="<?= esc($mainFcHref($adFc)) ?>">
                                            <div class="profile">
                                                <?php if ($profileImage !== ''): ?>
                                                    <img src="<?= esc($profileImage) ?>" alt="" class="avatar" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
                                                <?php else: ?>
                                                    <span class="avatar is-empty" aria-hidden="true"></span>
                                                <?php endif; ?>
                                                <div>
                                                    <p class="profile-name"><?= esc($adFc['name'] ?? '-') ?></p>
                                                    <p class="c-rate">
                                                        <span class="c-rate-star">★</span> <?= number_format((float) ($adFc['rating'] ?? 0), 1) ?>
                                                        <span class="c-rate-count">(<?= number_format((int) ($adFc['rating_count'] ?? 0)) ?>)</span>
                                                    </p>
                                                    <p class="c-dot-line">
                                                        <span><?= esc($mainFcCompanies($adFc)) ?></span><span class="location"><span><?= esc($mainFcRegionLabel($adFc)) ?></span></span>
                                                    </p>
                                                    <div class="list-tags">
                                                        <?php foreach ($tags as $tag): ?>
                                                            <span><?= esc($tag) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="main-filter-empty" data-filter-empty="filter-location" hidden aria-hidden="true">
                <p class="favorite-empty">노출 가능한 FC가 없습니다.</p>
            </div>
        </section>

        <?php if (!empty($topBannerAds)): ?>
            <!-- 상황별 프로모션 배너 -->
            <section class="section">
                <div class="situation-banner">
                    <div class="situation-banner-top">
                        <span class="ad-mark" aria-hidden="true">AD</span>
                        <div class="control-box situation-banner-controls">
                            <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                            <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                        </div>
                    </div>
                    <div id="sitAdSwiper" class="swiper situation-banner-list">
                        <div class="swiper-wrapper">
                            <?php foreach ($topBannerAds as $bannerAd): ?>
                                <?php $bannerImage = !empty($bannerAd['id']) ? base_url('ad/banner/' . (int) $bannerAd['id']) : $mainImageUrl($bannerAd['banner_image_url'] ?? '', ''); ?>
                                <div class="swiper-slide">
                                    <a href="<?= base_url('ad/click/' . (int) ($bannerAd['id'] ?? 0)) ?>" class="situation-banner-slide">
                                        <img src="<?= esc($bannerImage) ?>" alt="" class="situation-banner-img" />
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- 상황별 추천 · 2열 -->
        <section class="section">
            <div class="situation-list">
                <div class="section-head">
                    <h3 class="section-title">내 상황에 맞는 보험설계사 추천 <span class="ad-mark" aria-hidden="true">AD</span></h3>
                    <div class="section-head-right">
                        <a href="<?= base_url('fc/list') ?>" class="section-more">전체보기</a>
                        <div class="control-box">
                            <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                            <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                        </div>
                    </div>
                </div>
                <div class="chip-scroll">
                    <div class="chip-row" data-toggle-group="filter-insurance-situation">
                        <?php foreach ($insuranceOptions as $index => $option): ?>
                            <button type="button" class="filter-btn <?= $index === 0 ? 'is-active' : '' ?>" data-toggle-item data-value="<?= esc($option['value']) ?>">
                                <?= esc($option['label']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="sitListSwiper" class="swiper situation-list-carousel">
                    <div class="swiper-wrapper">
                        <?php foreach ($mainChunks($productFcList, 6) as $chunk): ?>
                            <div class="swiper-slide list-grid">
                                <?php foreach ($chunk as $row): ?>
                                    <?php
                                    $profileImage = $mainProfileImageUrl($row['profile_image'] ?? '');
                                    $desc = trim((string) ($row['hero_line'] ?? $row['intro'] ?? ''));
                                    ?>
                                    <a href="<?= esc($mainFcHref($row)) ?>" class="list-item" data-filter-values="<?= esc($mainInsuranceFilterValues($row)) ?>">
                                        <div class="list-left">
                                            <?php if ($profileImage !== ''): ?>
                                                <img src="<?= esc($profileImage) ?>" alt="" class="list-avatar" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
                                            <?php else: ?>
                                                <span class="list-avatar is-empty" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="list-text">
                                                <p class="list-title"><?= esc($row['name'] ?? '-') ?></p>
                                                <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) ($row['rating'] ?? 0), 1) ?> <span class="c-rate-count">(<?= number_format((int) ($row['rating_count'] ?? 0)) ?>)</span></p>
                                                <p class="c-dot-line">
                                                    <span><?= esc($mainFcCompanies($row)) ?></span><span class="location"><span><?= esc($mainFcRegionLabels($row)) ?></span></span>
                                                </p>
                                                <p class="list-desc"><?= esc($desc !== '' ? $desc : implode(', ', $mainFcInsuranceTags($row))) ?></p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="main-filter-empty" data-filter-empty="filter-insurance-situation" hidden aria-hidden="true">
                    <p class="favorite-empty">노출 가능한 FC가 없습니다.</p>
                </div>
            </div>
        </section>

        <!-- 고객 후기 -->
        <section class="section review-bleed">
            <div class="review-inner">
                <header>
                    <div class="review-head-top">
                        <span class="review-head-spacer"></span>
                        <h3 class="section-title">믿고 선택한 고객들의 생생한 후기 <span class="ad-mark" aria-hidden="true">AD</span></h3>
                        <div class="section-head-right">
                            <a href="<?= base_url('fc/list') ?>" class="section-more">전체보기</a>
                            <div class="control-box">
                                <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                                <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                            </div>
                        </div>
                    </div>
                    <p class="review-sub-title">MyFC를 통해 상담한 고객님들이 직접 남긴 <br class="br-mo" />신뢰의 이야기 입니다.</p>
                </header>

                <div id="reviewSwiper" class="swiper review-swiper">
                    <div class="swiper-wrapper review-track">
                        <?php $mainReviewIndex = 0; ?>
                        <?php foreach ($reviewList as $review): ?>
                            <div class="swiper-slide">
                                <a href="#" class="review-card js-main-review-open"
                                    data-review-index="<?= $mainReviewIndex++ ?>"
                                    <?= !empty($review['ad_id']) ? 'data-ad-click-url="' . esc(base_url('ad/click/' . (int) $review['ad_id'])) . '"' : '' ?>>
                                    <p class="review-author"><?= esc($mainMaskName($review['reviewer_name'] ?? '')) ?></p>
                                    <div class="review-card-meta">
                                        <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) ($review['rating'] ?? 0), 1) ?></p>
                                        <time class="review-date"><?= !empty($review['created_at']) ? esc(date('Y.m.d', strtotime($review['created_at']))) : '-' ?></time>
                                    </div>
                                    <h4><?= esc($review['title'] ?? '-') ?></h4>
                                    <p class="review-card-body"><?= esc($review['body'] ?? '') ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- 최근 등록된 보험IN -->
        <section class="section insurance-in-section">
            <div class="section-head">
                <h3 class="section-title">최근 등록된 보험IN</h3>
                <div class="section-head-right">
                    <a href="<?= base_url('insurance-in') ?>" class="section-more insurance-in-more-desktop">전체보기</a>
                    <div class="control-box">
                        <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                        <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                    </div>
                </div>
            </div>

            <div id="insuranceInSwiper" class="swiper insurance-in-swiper">
                <div class="swiper-wrapper">
                    <?php if (empty($insuranceInList)): ?>
                        <div class="swiper-slide">
                            <div class="swiper-page-grid">
                                <div class="insurance-in-card">
                                    <h4>노출 가능한 게시글이 없습니다.</h4>
                                    <p></p>
                                    <div class="insurance-in-foot">
                                        <div class="insurance-in-meta">
                                            <span>-</span>
                                            <span>-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($mainChunks($insuranceInList, 4) as $chunk): ?>
                        <div class="swiper-slide">
                            <div class="swiper-page-grid">
                                <?php foreach ($chunk as $row): ?>
                                    <a href="<?= base_url('insurance-in/' . (int) ($row['question_id'] ?? 0)) ?>" class="insurance-in-card">
                                        <h4><?= esc($row['title'] ?? '-') ?></h4>
                                        <p><?= esc($row['body'] ?? '') ?></p>
                                        <div class="insurance-in-foot">
                                            <div class="insurance-in-meta">
                                                <span>조회수 <?= number_format((int) ($row['view_count'] ?? 0)) ?></span>
                                                <span><?= !empty($row['created_at']) ? esc(date('Y.m.d', strtotime($row['created_at']))) : '-' ?></span>
                                            </div>
                                            <p class="insurance-in-author"><?= (int) ($row['answer_count'] ?? 0) > 0 ? '답변 ' . esc($row['first_fc_name'] ?? '') . ' FC · ' . (int) $row['answer_count'] . '건' : '답변을 기다리고 있어요' ?></p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="<?= base_url('insurance-in') ?>" class="section-more insurance-in-more-bottom">전체보기</a>
        </section>

        <!-- 언어별 FC 추천 -->
        <section class="section">
            <div class="language-section">
                <div class="section-head">
                    <h3 class="section-title">상담가능한 언어별 보험설계사 추천 <span class="ad-mark" aria-hidden="true">AD</span></h3>
                    <div class="section-head-right">
                        <div class="control-box">
                            <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                            <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                        </div>
                    </div>
                </div>

                <div class="chip-scroll">
                    <div class="chip-row" data-toggle-group="filter-language">
                        <?php foreach ($languageOptions as $index => $option): ?>
                            <button type="button" class="language-filter <?= $index === 0 ? 'is-active' : '' ?>" data-toggle-item data-value="<?= esc($option['value']) ?>">
                                <?php if (!empty($option['icon'])): ?>
                                    <img class="language-filter-flag <?= $option['icon'] === 'ic-flag-jp.png' ? 'language-filter-flag--ring' : '' ?>" src="<?= SITE_IMG_URL ?>images/<?= esc($option['icon']) ?>" alt="" />
                                <?php endif; ?>
                                <?= esc((string) $option['label']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="consultSwiper" class="swiper language-list">
                    <div class="swiper-wrapper">
                        <?php foreach ($mainChunks($languageFcList, 6) as $chunk): ?>
                            <div class="swiper-slide">
                                <div class="language-list-grid">
                                    <?php foreach ($chunk as $row): ?>
                                        <?php
                                        $profileImage = $mainProfileImageUrl($row['profile_image'] ?? '');
                                        $tags = array_slice($mainFcInsuranceTags($row), 0, 3);
                                        ?>
                                        <a href="<?= esc($mainFcHref($row)) ?>" class="language-card" data-filter-values="<?= esc($mainLanguageFilterValues($row)) ?>">
                                            <?php if ($profileImage !== ''): ?>
                                                <img src="<?= esc($profileImage) ?>" alt="" class="avatar" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
                                            <?php else: ?>
                                                <span class="avatar is-empty" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <div class="language-body">
                                                <p class="list-title"><?= esc($row['name'] ?? '-') ?></p>
                                                <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) ($row['rating'] ?? 0), 1) ?> <span class="c-rate-count">(<?= number_format((int) ($row['rating_count'] ?? 0)) ?>)</span></p>
                                                <p class="c-dot-line">
                                                    <span><?= esc($mainFcCompanies($row)) ?></span><span class="location"><span><?= esc($mainFcRegionLabels($row, 5)) ?></span></span>
                                                </p>
                                                <div class="language-tags">
                                                    <?php foreach ($tags as $tag): ?>
                                                        <span><?= esc($tag) ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (count($languageFcList) <= 1): ?>
                                        <a href="mailto:help@myfc.co.kr" class="language-card language-promo" aria-label="광고·고객센터 문의">
                                            <img src="<?= SITE_IMG_URL ?>images/ic-megaphone.svg" alt="" class="language-promo-ico" aria-hidden="true" />
                                            <div class="language-body">
                                                <p>언어별 상담이 가능하신 FC님!</p>
                                                <p>광고문의 주세요</p>
                                                <p>E-mail : help@myfc.co.kr</p>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="main-filter-empty" data-filter-empty="filter-language" hidden aria-hidden="true">
                    <p class="favorite-empty">노출 가능한 FC가 없습니다.</p>
                </div>
            </div>
        </section>

        <?php if (!empty($bottomBannerAds)): ?>
            <!-- 상황별 프로모션 배너 -->
            <section class="section">
                <div class="situation-banner">
                    <div class="situation-banner-top">
                        <span class="ad-mark" aria-hidden="true">AD</span>
                        <div class="control-box situation-banner-controls">
                            <button type="button" class="control-btn swiper-nav-prev" aria-label="이전"></button>
                            <button type="button" class="control-btn swiper-nav-next" aria-label="다음"></button>
                        </div>
                    </div>
                    <div id="sitAdSwiperBelowLang" class="swiper situation-banner-list">
                        <div class="swiper-wrapper">
                            <?php foreach ($bottomBannerAds as $bannerAd): ?>
                                <?php $bannerImage = !empty($bannerAd['id']) ? base_url('ad/banner/' . (int) $bannerAd['id']) : $mainImageUrl($bannerAd['banner_image_url'] ?? '', ''); ?>
                                <div class="swiper-slide">
                                    <a href="<?= base_url('ad/click/' . (int) ($bannerAd['id'] ?? 0)) ?>" class="situation-banner-slide">
                                        <img src="<?= esc($bannerImage) ?>" alt="" class="situation-banner-img" />
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php if (!empty($reviewList)): ?>
    <div class="c-modal md" id="mainReviewModal" aria-hidden="true">
        <button type="button" class="c-modal-backdrop" data-main-review-close aria-label="닫기"></button>
        <div class="c-modal-panel" role="dialog" aria-modal="true" aria-labelledby="mainReviewModalTitle">
            <div class="c-modal-head">
                <h2 class="c-modal-title" id="mainReviewModalTitle">후기 상세</h2>
                <button type="button" class="c-modal-close" data-main-review-close aria-label="닫기"></button>
            </div>
            <div class="c-modal-body">
                <div class="story-detail-wrap">
                    <button type="button" class="control-btn swiper-nav-prev" aria-label="이전 후기"></button>
                    <div class="swiper main-review-detail-swiper"><div class="swiper-wrapper">
                        <?php foreach ($reviewList as $review): ?>
                            <div class="swiper-slide"><article class="story-detail-card">
                                <h3><?= esc($review['title'] ?? '') ?></h3>
                                <div class="story-detail-meta">
                                    <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) ($review['rating'] ?? 0), 1) ?></p>
                                    <p><?= esc($mainMaskName($review['reviewer_name'] ?? '')) ?></p>
                                    <time><?= !empty($review['created_at']) ? esc(date('Y.m.d', strtotime($review['created_at']))) : '-' ?></time>
                                </div>
                                <div class="story-detail-body"><p><?= nl2br(esc($review['body'] ?? '')) ?></p></div>
                            </article></div>
                        <?php endforeach; ?>
                    </div></div>
                    <button type="button" class="control-btn swiper-nav-next" aria-label="다음 후기"></button>
                </div>
            </div>
            <div class="c-modal-foot"><button type="button" class="btn btn-line" data-main-review-close>닫기</button></div>
        </div>
    </div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<style>
    .main-filter-empty[hidden] {
        display: none !important;
    }

    .main-filter-empty {
        width: 100%;
        border: 1px solid #d6dde8;
        border-radius: 8px;
        background: #f8fafc;
        padding: 28px 16px;
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .main-filter-empty .favorite-empty {
        margin: 0;
        text-align: center;
        color: #5f6b7a;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
    }
</style>
<script>
    (function() {
        'use strict';

        var hasSwiper = typeof MyFC !== 'undefined' && typeof Swiper !== 'undefined';

        function csvHasValue(source, value) {
            if (!value || value === 'all') return true;
            return String(source || '')
                .split(',')
                .map(function(item) { return item.trim(); })
                .filter(Boolean)
                .indexOf(value) !== -1;
        }

        function updateSwiper(swiperEl) {
            if (swiperEl && swiperEl.swiper && typeof swiperEl.swiper.update === 'function') {
                swiperEl.swiper.update();
            }
        }

        function setMainVisible(node, visible) {
            if (!node) {
                return;
            }

            node.hidden = !visible;
            node.style.setProperty('display', visible ? '' : 'none', 'important');
        }

        function applyMainFilter(itemSelector, value, emptySelector, swiperSelector, showEmpty) {
            if (typeof showEmpty === 'undefined') {
                showEmpty = true;
            }
            var nodes = document.querySelectorAll(itemSelector);
            var i;
            var seen = [];
            var visibleCount = 0;

            for (i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                var visible = csvHasValue(node.getAttribute('data-filter-values'), value);
                var slide = node.closest('.swiper-slide');

                setMainVisible(node, visible);
                if (visible) {
                    visibleCount++;
                }

                if (slide) {
                    if (seen.indexOf(slide) === -1) {
                        seen.push(slide);
                    }
                    if (visible) {
                        slide.__mainFilterVisible = true;
                    } else if (typeof slide.__mainFilterVisible === 'undefined') {
                        slide.__mainFilterVisible = false;
                    }
                }
            }

            for (i = 0; i < seen.length; i++) {
                var filterSlide = seen[i];
                setMainVisible(filterSlide, !!filterSlide.__mainFilterVisible);
                delete filterSlide.__mainFilterVisible;
            }

            var swiperEl = swiperSelector ? document.querySelector(swiperSelector) : null;
            if (swiperEl) {
                swiperEl.hidden = visibleCount === 0;
                updateSwiper(swiperEl);
            }

            var emptyEl = emptySelector ? document.querySelector(emptySelector) : null;
            if (emptyEl) {
                setMainVisible(emptyEl, showEmpty && visibleCount === 0);
                emptyEl.setAttribute('aria-hidden', emptyEl.hidden ? 'true' : 'false');
            }
        }

        function bindMainFilter(groupName, itemSelector, emptySelector, swiperSelector) {
            var group = document.querySelector('[data-toggle-group="' + groupName + '"]');
            if (!group) return;

            var items = group.querySelectorAll('[data-toggle-item]');
            var active = group.querySelector('[data-toggle-item].is-active') || items[0];
            var initialized = false;
            if (active) {
                applyMainFilter(itemSelector, active.getAttribute('data-value') || 'all', emptySelector, swiperSelector, true);
                initialized = true;
            }

            group.addEventListener('click', function(e) {
                var btn = e.target.closest('[data-toggle-item]');
                if (!btn) return;
                if (btn.classList.contains('is-active')) return;
                var value = btn.getAttribute('data-value') || 'all';
                applyMainFilter(itemSelector, value, emptySelector, swiperSelector, true);
                var i;
                for (i = 0; i < items.length; i++) {
                    items[i].classList.remove('is-active');
                }
                btn.classList.add('is-active');
            });

        }

        bindMainFilter('filter-location', '#fcSwiper .swiper-slide[data-filter-values]', '.main-filter-empty[data-filter-empty="filter-location"]', '#fcSwiper');
        bindMainFilter('filter-insurance-situation', '#sitListSwiper .list-item[data-filter-values]', '.main-filter-empty[data-filter-empty="filter-insurance-situation"]', '#sitListSwiper');
        bindMainFilter('filter-language', '#consultSwiper .language-card[data-filter-values]', '.main-filter-empty[data-filter-empty="filter-language"]', '#consultSwiper');

        if (!hasSwiper) {
            return;
        }

        // 추천 FC 캐러셀
        var fcRoot = document.getElementById('fcSwiper');
        var fcSection = fcRoot && fcRoot.closest ? fcRoot.closest('section') : null;

        MyFC.initSwiper('#fcSwiper', fcSection, {
            speed: 450,
            slidesPerView: 1.3,
            spaceBetween: 12,
            grabCursor: true,
            watchOverflow: true,
            breakpointsBase: 'container',
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            loop: false,
            rewind: true,
            breakpoints: {
                481: {
                    slidesPerView: 2,
                    spaceBetween: 12
                },
                641: {
                    slidesPerView: 3,
                    spaceBetween: 12
                },
                801: {
                    slidesPerView: 4,
                    spaceBetween: 12
                },
                900: {
                    slidesPerView: 4,
                    spaceBetween: 12
                },
            },
        });

        // 상황별 AD 롤링 배너 (상단·하단)
        document.querySelectorAll('.situation-banner').forEach(function(bannerRoot) {
            var sitEl = bannerRoot.querySelector('.situation-banner-list.swiper');
            if (!sitEl) {
                return;
            }
            var slideCount = sitEl.querySelectorAll('.swiper-slide').length;
            MyFC.initSwiper(sitEl, bannerRoot, {
                speed: 450,
                slidesPerView: 1,
                spaceBetween: 0,
                loop: slideCount > 1,
                grabCursor: true,
                autoplay: {
                    delay: 4500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
            });
        });

        // 상황별 추천 · 2열 리스트
        (function initSitListSwiper() {
            var listEl = document.getElementById('sitListSwiper');
            var listScope = document.querySelector('.situation-list');
            if (!listEl || !listScope) {
                return;
            }
            MyFC.initSwiper('#sitListSwiper', listScope, {
                speed: 450,
                slidesPerView: 1,
                spaceBetween: 0,
                grabCursor: true,
                watchOverflow: true,
                loop: false,
                rewind: true,
            });
        })();

        // 최근 등록된 보험IN
        (function initInsuranceInSwiper() {
            var insuranceEl = document.getElementById('insuranceInSwiper');
            var insuranceScope = document.querySelector('.insurance-in-section');
            if (!insuranceEl || !insuranceScope) {
                return;
            }
            var swiper = MyFC.initSwiper('#insuranceInSwiper', insuranceScope, {
                navigation: false,
                speed: 450,
                slidesPerView: 1,
                spaceBetween: 12,
                autoHeight: true,
                grabCursor: true,
                watchOverflow: true,
                loop: false,
                rewind: true,
            });
            var prevBtn = insuranceScope.querySelector('.swiper-nav-prev');
            var nextBtn = insuranceScope.querySelector('.swiper-nav-next');
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

        // 언어별 FC 추천
        (function initConsultSwiper() {
            var consultEl = document.getElementById('consultSwiper');
            var consultScope = document.querySelector('.language-section');
            if (!consultEl || !consultScope) {
                return;
            }
            MyFC.initSwiper('#consultSwiper', consultScope, {
                speed: 450,
                slidesPerView: 1,
                spaceBetween: 0,
                grabCursor: true,
                watchOverflow: true,
                loop: false,
                rewind: true,
            });
        })();

        // 고객 후기
        (function initReviewSwiper() {
            var reviewEl = document.getElementById('reviewSwiper');
            var reviewScope = document.querySelector('.review-inner');
            if (!reviewEl || !reviewScope) {
                return;
            }
            var swiper = MyFC.initSwiper('#reviewSwiper', reviewScope, {
                navigation: false,
                speed: 450,
                grid: {
                    rows: 1,
                    fill: 'row',
                },
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 12,
                grabCursor: true,
                watchOverflow: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                loop: false,
                rewind: true,
                breakpoints: {
                    641: {
                        grid: {
                            rows: 2,
                            fill: 'row',
                        },
                        slidesPerView: 4,
                        slidesPerGroup: 1,
                        spaceBetween: 12,
                    },
                },
            });
            var prevBtn = reviewScope.querySelector('.swiper-nav-prev');
            var nextBtn = reviewScope.querySelector('.swiper-nav-next');
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

        (function initMainReviewModal() {
            var modal = document.getElementById('mainReviewModal');
            var triggers = document.querySelectorAll('.js-main-review-open');
            if (!modal || !triggers.length) return;

            var swiper = null;
            var scope = modal.querySelector('.story-detail-wrap');
            var element = modal.querySelector('.main-review-detail-swiper');
            triggers.forEach(function(trigger) {
                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    var adClickUrl = this.getAttribute('data-ad-click-url');
                    if (adClickUrl && window.fetch) {
                        window.fetch(adClickUrl, { credentials: 'same-origin', redirect: 'manual' }).catch(function() {});
                    }
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                    if (!swiper) {
                        swiper = MyFC.initSwiper(element, scope, { slidesPerView: 1, loop: false, autoHeight: true });
                    }
                    if (swiper) {
                        swiper.slideTo(Number(this.getAttribute('data-review-index') || 0), 0);
                        swiper.update();
                    }
                });
            });
            modal.querySelectorAll('[data-main-review-close]').forEach(function(button) {
                button.addEventListener('click', function() {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('modal-open');
                });
            });
        })();
    })();
</script>
