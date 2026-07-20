<?php
$recentSearches = $recent_searches ?? [];
?>

<main>
    <div class="page-inner-narrow">
        <h1 class="search-page-title">찾으시는 정보를 검색해보세요.</h1>

        <form class="search-page-form" action="/fc/search" method="get" id="fc-search-page-form">
            <div class="search-field">
                <label class="search-field-label">
                    <input
                        type="search"
                        name="q"
                        id="fc-search-page-input"
                        class="search-field-input"
                        placeholder="검색어를 입력하세요."
                        autocomplete="off"
                        enterkeyhint="search" />
                </label>
                <button type="submit" class="search-field-submit" aria-label="검색">
                    <img src="<?= SITE_IMG_URL ?>images/ic-search.svg" alt="" />
                </button>
            </div>
        </form>

        <section class="search-recent">
            <h3 class="search-recent-label">최근 검색어</h3>

            <?php if (empty($recentSearches)): ?>
                <p class="search-recent-empty">최근 검색어가 없습니다.</p>
            <?php else: ?>
                <div class="search-recent-tags">
                    <?php foreach ($recentSearches as $item): ?>
                        <button type="button" class="search-tag" data-search-tag="<?= esc($item) ?>">
                            <?= esc($item) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($q)): ?>
            <section class="section search-results"><div class="search-results-list-wrap"><div class="search-results-head"><p class="search-results-title">“<span><?= esc($q) ?></span>”에 대한 결과</p></div><div class="list-grid">
            <?php foreach ($results as $row): ?>
                <?php $company = implode(' · ', array_filter([$row['company'] ?? '', $row['company_sub'] ?? '', $row['ga'] ?? ''])); $region = fc_region_label(trim(explode(',', (string) ($row['region'] ?? ''))[0] ?? '')); ?>
                <a href="<?= base_url('fc/view?uid=' . rawurlencode($row['member_uid'])) ?>" class="list-item"><div class="list-left"><?php $image = profile_image_url($row['profile_image'] ?? ''); ?><?php if ($image): ?><img src="<?= esc($image) ?>" alt="" class="list-avatar"><?php else: ?><span class="list-avatar is-empty"></span><?php endif; ?><div class="list-text"><p class="list-title"><?= esc($row['name']) ?></p><p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float) $row['rating'], 1) ?> <span class="c-rate-count">(<?= number_format((int) $row['rating_count']) ?>)</span></p><p class="c-dot-line"><span><?= esc($company ?: '-') ?></span><span class="location"><span><?= esc($region) ?></span></span></p><p class="list-desc"><?= esc($row['hero_line'] ?? '') ?></p></div></div></a>
            <?php endforeach; ?>
            <?php if (empty($results)): ?><p class="search-recent-empty">검색 결과가 없습니다.</p><?php endif; ?>
            </div></div></section>
        <?php endif; ?>
    </div>
</main>

<script>
    (function () {
        'use strict';

        var form = document.getElementById('fc-search-page-form');
        var input = document.getElementById('fc-search-page-input');
        var tags = document.querySelectorAll('[data-search-tag]');

        if (!form || !input) {
            return;
        }

        form.addEventListener('submit', function (e) {
            var value = String(input.value || '').trim();
            if (value === '') {
                e.preventDefault();
                alert('검색어를 입력해 주세요.');
                return;
            }
            if (value.length < 2) {
                e.preventDefault();
                alert('검색어는 2자 이상 입력해 주세요.');
                return;
            }
        });

        for (var i = 0; i < tags.length; i++) {
            tags[i].addEventListener('click', function () {
                var value = this.getAttribute('data-search-tag') || '';
                input.value = value;
                form.submit();
            });
        }
    })();
</script>
