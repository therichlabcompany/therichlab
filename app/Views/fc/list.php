<link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=3') ?>" />
<main>
    <div class="page-inner">
        <h1 class="visually-hidden">보험설계사 목록</h1>
        <section class="fc-directory">
            <div class="directory-toolbar">
                <div class="directory-filters">
                    <div class="select-field">
                        <span class="visually-hidden">보험 종류</span>
                        <button type="button" class="directory-select" data-popup-target="#popup-insurance"
                            data-popup-sync="#fc-filter-insurance-value">
                            <span>전체</span>
                        </button>
                        <input type="hidden" id="fc-filter-insurance-value" name="insurance" value="" />
                    </div>
                    <div class="select-field">
                        <span class="visually-hidden">지역</span>
                        <div class="fc-region-inline">
                            <button type="button" class="directory-select fc-region-select"
                                data-popup-target="#popup-region" data-popup-sync="#fc-filter-region-value">
                                <span>전체</span>
                            </button>
                        </div>
                        <input type="hidden" id="fc-filter-region-value" name="region" value="" />
                    </div>
                </div>
                <div class="fc-directory-sort">
                    <button type="button" class="fc-sort-btn is-active">추천순</button>
                    <button type="button" class="fc-sort-btn">인기순</button>
                    <button type="button" class="fc-sort-btn">평점순</button>
                </div>
            </div>

            <div class="fc-profile-grid">

                <?php foreach ($list as $row): ?>

                    <article class="card">
                        <div class="card-body">
                            <a class="card-link" href="/fc/view/?uid=<?= esc($row['member_uid']) ?>">
                                <div class="profile">

                                    <!-- profile image -->
                                    <img
                                        src="<?= !empty($row['profile_image']) ? '/uploads/profile/'.$row['profile_image'] : SITE_IMG_URL . 'images/temp/@profile-m.png' ?>"
                                        alt=""
                                        class="avatar" />

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
                                                <span>
                                                    <?= fc_region_label($region) ?>
                                                </span>
                                            </span>
                                        </p>

                                        <!-- tags (최대 4개) -->
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
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">이서연</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(2,018)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>삼성화재</span><span class="location"><span>서울·경기</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>종신보험</span>
                                        <span>암보험</span>
                                        <span>실비보험</span>
                                        <span>운전자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>

            </div>
        </section>
        <nav class="c-paging">
            <ul>
                <!-- 이전 -->
                <li>
                    <a href="?page=<?= max(1, $page - 1) ?>"
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
                        <a href="?page=<?= $i ?>"
                            <?= ($i == $page) ? 'aria-current="page"' : '' ?>>
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- 다음 -->
                <li>
                    <a href="?page=<?= min($totalPages, $page + 1) ?>"
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