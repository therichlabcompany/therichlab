<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">보험IN</h1>
        <p class="page-main-lead">보험에 대한 궁금한 문의사항을 등록하시면 <br class="br-mo">FC님들이 실시간 답변 해드려요.</p>

        <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
        <form action="<?= base_url('insurance-in') ?>" method="get">
            <input type="hidden" name="sort" value="<?= esc($sort) ?>">
            <div class="search-field">
                <label class="search-field-label"><span class="visually-hidden">내용검색</span>
                    <input type="search" name="q" value="<?= esc($keyword) ?>" class="search-field-input" placeholder="내용검색" autocomplete="off">
                </label>
                <button type="submit" class="search-field-submit"><img src="<?= SITE_IMG_URL ?>images/ic-search.svg" alt="검색"></button>
            </div>
        </form>
        <div class="insurance-in-toolbar">
            <div class="c-tabs insurance-in-sort" aria-label="정렬">
                <?php foreach (['answers' => '최근 답변순', 'questions' => '최근 질문순', 'views' => '조회순'] as $key => $label): ?>
                    <a href="<?= base_url('insurance-in') . '?sort=' . $key . '&q=' . rawurlencode($keyword) ?>" aria-current="<?= $sort === $key ? 'page' : 'false' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?= base_url('insurance-in/write') ?>" class="btn btn-line" id="insurance-in-write">질문 등록</a>
        </div>
        <ul class="insurance-in-list">
            <?php if (!$questions): ?><li class="insurance-in-empty">등록된 질문이 없습니다.</li><?php endif; ?>
            <?php foreach ($questions as $row): ?>
                <li><a href="<?= base_url('insurance-in/' . (int) $row['question_id']) ?>" class="insurance-in-card">
                    <h2><?= esc($row['title']) ?></h2><p><?= esc($row['body']) ?></p>
                    <div class="insurance-in-foot">
                        <p class="insurance-in-meta"><span>작성일(<?= esc(date('Y-m-d', strtotime($row['created_at']))) ?>)</span><span>조회수 <?= number_format((int) $row['view_count']) ?></span></p>
                        <p class="insurance-in-author">
                            <?php if ((int) $row['answer_count']): ?>답변 <?= esc($row['first_fc_name']) ?> FC<?= (int) $row['answer_count'] > 1 ? ' 외 ' : ' ' ?><span><?= (int) $row['answer_count'] ?>건 등록</span><?php else: ?>답변을 기다리고 있어요<?php endif; ?>
                        </p>
                    </div>
                </a></li>
            <?php endforeach; ?>
        </ul>
        <?php if ($total_pages > 1): ?>
            <?php $pageUrl = static fn (int $number): string => base_url('insurance-in') . '?page=' . $number . '&sort=' . rawurlencode($sort) . '&q=' . rawurlencode($keyword); ?>
            <nav class="c-paging" aria-label="페이지"><ul>
                <li><?= $page > 1 ? '<a href="' . esc($pageUrl($page - 1)) . '" rel="prev"><span class="visually-hidden">이전 페이지</span></a>' : '<span class="disabled"><span class="visually-hidden">이전 페이지</span></span>' ?></li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?><li><a href="<?= esc($pageUrl($i)) ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a></li><?php endfor; ?>
                <li><?= $page < $total_pages ? '<a href="' . esc($pageUrl($page + 1)) . '" rel="next"><span class="visually-hidden">다음 페이지</span></a>' : '<span class="disabled"><span class="visually-hidden">다음 페이지</span></span>' ?></li>
            </ul>
            <?php if ($page < $total_pages): ?><div><a href="<?= esc($pageUrl($page + 1)) ?>">다음 질문 더보기</a></div><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</main>
<?php if (session('member_type') === 'FC'): ?>
<script>
document.getElementById('insurance-in-write')?.addEventListener('click', function (event) {
    event.preventDefault();
    alert('개인회원만 글작성이 가능합니다.');
});
</script>
<?php endif; ?>
