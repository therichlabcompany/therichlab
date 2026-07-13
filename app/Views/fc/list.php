<?php
$keyword = trim((string) ($q ?? ''));
$regionText = "";
foreach (explode(',', $region) as $r):
    if ($regionText) $regionText .= ",";
    $regionText .= fc_region_label(trim($r));
endforeach;

if (empty($regionText)) $regionText = "전체";


$insuranceText = "";

foreach (explode(',', $insurance) as $item):
    if ($insuranceText) $insuranceText .= ",";
    $insuranceText .= fc_insurance_label(trim($item));
endforeach;

if (empty($insuranceText)) $insuranceText = "전체";
?>
<link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=3') ?>" />
<main>
    <div class="page-inner">
        <h1 class="visually-hidden">보험설계사 목록</h1>
        <section class="fc-directory">
            <form id="fc-search-form" method="get" action="/fc/list">
                <input type="hidden" name="sort" id="fc-sort-value" value="<?= $sort ?>">
                <input type="hidden" name="q" id="fc-keyword-value" value="<?= esc($keyword) ?>">
                <div class="directory-toolbar">
                    <div class="directory-filters">
                        <div class="select-field">
                            <span class="visually-hidden">보험 종류</span>
                            <button type="button" class="directory-select" data-popup-target="#popup-insurance"
                                data-popup-sync="#fc-filter-insurance-value">
                                <span><?= $insuranceText ?></span>
                            </button>
                            <input type="hidden" id="fc-filter-insurance-value" name="insurance" value="<?= $insurance ?>" />
                        </div>
                        <div class="select-field">
                            <span class="visually-hidden">지역</span>
                            <div class="fc-region-inline">
                                <button type="button" class="directory-select fc-region-select"
                                    data-popup-target="#popup-region" data-popup-sync="#fc-filter-region-value">
                                    <span><?= $regionText ?></span>
                                </button>
                            </div>
                            <input type="hidden" id="fc-filter-region-value" name="region" value="<?= $region ?>" />
                        </div>
                    </div>
                    <div class="fc-directory-sort">
                        <button type="button" class="fc-sort-btn<?php if($sort == "recommend") echo" is-active";?>">추천순</button>
                        <button type="button" class="fc-sort-btn<?php if($sort == "popular") echo" is-active";?>">인기순</button>
                        <button type="button" class="fc-sort-btn<?php if($sort == "rating") echo" is-active";?>">평점순</button>
                    </div>
                </div>
            </form>

            <?php if ($keyword !== ''): ?>
                <div class="search-results-head">
                    <p class="search-results-title">“<span><?= esc($keyword) ?></span>”에 대한 결과</p>
                    <p class="search-results-count">검색결과 <?= number_format((int) $total) ?>개</p>
                </div>
            <?php endif; ?>

            <div class="fc-profile-grid">

                <?php if (!empty($list)): ?>

                    <?php foreach ($list as $row): ?>

                        <article class="card">
                            <div class="card-body">
                                <a class="card-link" href="/fc/view/?uid=<?= esc($row['member_uid']) ?>">
                                    <div class="profile">

                                        <!-- profile image -->
                                        <?php $profileImageUrl = profile_image_url($row['profile_image'] ?? ''); ?>
                                        <?php if ($profileImageUrl !== ''): ?>
                                            <img src="<?= esc($profileImageUrl) ?>" alt="" class="avatar" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
                                        <?php else: ?>
                                            <span class="avatar is-empty" aria-hidden="true"></span>
                                        <?php endif; ?>

                                        <div>

                                            <!-- name -->
                                            <p class="profile-name">
                                                <?= esc($row['name']) ?>
                                            </p>

                                            <!-- rating -->
                                            <p class="c-rate">
                                                <span class="c-rate-star">★</span>
                                                <?= number_format($row['rating'], 1) ?>
                                                <span class="c-rate-count">
                                                    (<?= number_format($row['rating_count']) ?>)
                                                </span>
                                            </p>

                                            <!-- company + region -->
                                            <p class="c-dot-line">
                                                <span><?= esc($row['company']) ?></span>

                                                <span class="location">
                                                    <?php
                                                    $regions = explode(',', $row['region'] ?? '');
                                                    $region = trim($regions[0] ?? '');
                                                    ?>
                                                    <span><?= fc_region_label($region) ?></span>
                                                </span>
                                            </p>

                                            <!-- tags -->
                                            <div class="list-tags">
                                                <?php
                                                $items = array_slice(
                                                    explode(',', $row['insurance_types'] ?? ''),
                                                    0,
                                                    4
                                                );
                                                ?>

                                                <?php foreach ($items as $item): ?>
                                                    <span><?= fc_insurance_label(trim($item)) ?></span>
                                                <?php endforeach; ?>
                                            </div>

                                        </div>
                                    </div>
                                </a>
                            </div>
                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- EMPTY STATE -->
                    <div class="fc-empty-state">
                        <div class="fc-empty-icon">🔍</div>
                        <p class="fc-empty-title">검색 결과가 없습니다</p>
                        <p class="fc-empty-desc">조건을 변경해서 다시 검색해 주세요</p>
                    </div>

                <?php endif; ?>

            </div>
        </section>
        <nav class="c-paging">
            <?php
            $query = $_GET;
            ?>

            <ul>
                <!-- 이전 -->
                <li>
                    <?php
                    $query['page'] = max(1, $page - 1);
                    ?>
                    <a href="?<?= http_build_query($query) ?>"
                        rel="prev"
                        class="<?= ($page <= 1) ? 'disabled' : '' ?>">
                        <span class="visually-hidden">이전 페이지</span>
                    </a>
                </li>

                <!-- 페이지 번호 -->
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li>
                        <?php
                        $query['page'] = $i;
                        ?>
                        <a href="?<?= http_build_query($query) ?>"
                            <?= ($i == $page) ? 'aria-current="page"' : '' ?>>
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- 다음 -->
                <li>
                    <?php
                    $query['page'] = min($totalPages, $page + 1);
                    ?>
                    <a href="?<?= http_build_query($query) ?>"
                        rel="next"
                        class="<?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <span class="visually-hidden">다음 페이지</span>
                    </a>
                </li>
            </ul>
            <div>
                <a href="#">더보기</a>
            </div>
        </nav>
    </div>
</main>
<style>
    .search-results-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-end;
        margin: 18px 0 16px;
    }

    .search-results-title {
        margin: 0;
        color: #172033;
        font-size: 18px;
        font-weight: 800;
    }

    .search-results-count {
        margin: 0;
        color: #6b7480;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .fc-empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #888;
    }

    .fc-empty-icon {
        font-size: 40px;
        margin-bottom: 12px;
    }

    .fc-empty-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }

    .fc-empty-desc {
        font-size: 14px;
        color: #999;
    }
</style>
<script>
    (function() {
        const form = document.getElementById('fc-search-form');

        const insuranceInput = document.getElementById('fc-filter-insurance-value');
        const regionInput = document.getElementById('fc-filter-region-value');
        const sortInput = document.getElementById('fc-sort-value');
        const keywordInput = document.getElementById('fc-keyword-value');

        // =========================
        // 정렬
        // =========================
        document.querySelectorAll('.fc-sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.fc-sort-btn').forEach(b => b.classList.remove('is-active'));
                this.classList.add('is-active');

                const text = this.innerText;

                if (text === '추천순') sortInput.value = 'recommend';
                if (text === '인기순') sortInput.value = 'popular';
                if (text === '평점순') sortInput.value = 'rating';

                form.submit();
            });
        });

        if (keywordInput) {
            keywordInput.addEventListener('change', function() {
                const value = String(keywordInput.value || '').trim();
                if (value !== '' && value.length < 2) {
                    alert('검색어는 2자 이상 입력해 주세요.');
                    keywordInput.value = '';
                    return;
                }
                form.submit();
            });
        }

        // =========================
        // insurance / region 변경 감지 (popup에서 값 sync 후 submit)
        // =========================
        const observer = new MutationObserver(() => {
            form.submit();
        });

        if (insuranceInput) {
            observer.observe(insuranceInput, {
                attributes: true,
                attributeFilter: ['value']
            });
        }

        if (regionInput) {
            observer.observe(regionInput, {
                attributes: true,
                attributeFilter: ['value']
            });
        }
    })();
</script>
