<main>
    <div class="page-inner">
        <div class="ad-mgmt-head">
            <span class="ad-mgmt-head-spacer" aria-hidden="true"></span>
            <h1 class="page-main-title">광고 관리</h1>
            <span class="ad-mgmt-head-spacer" aria-hidden="true"></span>
        </div>

        <div class="ad-mgmt-toolbar">
            <p class="ad-mgmt-updated">
                <button type="button" class="ad-mgmt-updated-refresh js-ad-mgmt-refresh" aria-label="광고 현황 새로고침">
                    <span class="ad-mgmt-updated-ico" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L4.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"
                                fill="currentColor" />
                        </svg>
                    </span>
                </button>
                마지막 업데이트 <time datetime="2025-10-01T15:00">25.10.01 15:00</time>
            </p>
            <div class="ad-mgmt-head-actions">
                <a href="/mypage/adlistRegionFc" class="btn btn-sm btn-primary">광고 신청하기</a>
            </div>
        </div>

        <div class="ad-mgmt-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">구분</th>
                        <th scope="col">상태</th>
                        <th scope="col">클릭 수</th>
                        <th scope="col">광고 금액</th>
                        <th scope="col">광고 기간</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($adList)) : ?>
                        <?php foreach ($adList as $ad) : ?>

                            <tr>
                                <!-- 광고 타입 -->
                                <td>
                                    <?= esc($ad['ad_name']) ?>
                                </td>

                                <!-- 상태 -->
                                <td>
                                    <span class="ad-status <?= esc($ad['status_class']) ?>">
                                        <?= esc($ad['status_text']) ?>
                                    </span>
                                </td>

                                <!-- 클릭 -->
                                <td>
                                    <?= number_format($ad['click_count'] ?? 0) ?>
                                </td>

                                <!-- 금액 -->
                                <td>
                                    <?= number_format($ad['amount'] ?? 0) ?>
                                </td>

                                <!-- 기간 -->
                                <td>
                                    <?= esc($ad['period']) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">
                                등록된 광고가 없습니다.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="c-paging" aria-label="페이지">
            <ul>

                <!-- 이전 -->
                <li>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" rel="prev">
                            <span class="visually-hidden">이전 페이지</span>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <span class="visually-hidden">이전 페이지</span>
                        </span>
                    <?php endif; ?>
                </li>

                <!-- 페이지 번호 -->
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li>
                        <a href="?page=<?= $i ?>"
                            class="<?= ($i == $page) ? 'is-active' : '' ?>"
                            <?= ($i == $page) ? 'aria-current="page"' : '' ?>>
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- 다음 -->
                <li>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" rel="next">
                            <span class="visually-hidden">다음 페이지</span>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <span class="visually-hidden">다음 페이지</span>
                        </span>
                    <?php endif; ?>
                </li>

            </ul>

            <div>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">더보기</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</main>
<script>
    (function() {
        var btn = document.querySelector('.js-ad-mgmt-refresh');
        if (!btn) return;
        btn.addEventListener('click', function() {
            location.reload();
        });
    })();
</script>