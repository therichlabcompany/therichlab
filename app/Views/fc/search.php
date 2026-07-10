<?php
$recentSearches = $recent_searches ?? [];
?>

<main>
    <div class="page-inner-narrow">
        <h1 class="search-page-title">찾으시는 정보를 검색해보세요.</h1>

        <form class="search-page-form" action="/fc/list" method="get" id="fc-search-page-form">
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
