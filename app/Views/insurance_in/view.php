<?php
$uid = (string) session('member_uid');
$type = (string) session('member_type');
$owner = $uid !== '' && $uid === $question['member_uid'];
$profileImage = static fn($row) => profile_image_url($row['profile_image'] ?? '');
?>
<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">보험IN</h1>
        <p class="page-main-lead">보험에 대한 궁금한 문의사항을 등록하시면 <br class="br-mo">FC님들이 실시간 답변 해드려요.</p>
        <?php if (session('error') || session('message')): ?><p class="insurance-in-alert <?= session('error') ? 'warn' : '' ?>"><?= esc(session('error') ?: session('message')) ?></p><?php endif; ?>
        <div class="insurance-in-detail-box">
            <article class="insurance-in-question">
                <div class="insurance-in-question-head">
                    <div class="insurance-in-question-bar">
                        <p class="insurance-in-question-label">질문</p><?php if ($owner && !(int) $question['answer_count']): ?><div class="insurance-in-question-actions"><a href="<?= base_url('insurance-in/' . $question['question_id'] . '/edit') ?>">수정</a>
                                <form method="post" action="<?= base_url('insurance-in/' . $question['question_id'] . '/delete') ?>" onsubmit="return confirm('질문을 삭제하시겠습니까?')"><?= csrf_field() ?><button>삭제</button></form>
                            </div><?php endif; ?>
                    </div>
                    <h2><?= esc($question['title']) ?></h2>
                    <p><?= nl2br(esc($question['body'])) ?></p>
                    <?php if ($files): ?><ul class="insurance-in-files"><?php foreach ($files as $file): ?><li><a href="<?= base_url('insurance-in/file/' . $file['file_id']) ?>">첨부파일: <?= esc($file['original_name']) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
                    <p class="insurance-in-meta"><span>작성일(<?= date('Y-m-d', strtotime($question['created_at'])) ?>)</span><span>조회수 <?= number_format($question['view_count']) ?></span></p>
                </div>
                <div class="insurance-in-foot">
                    <p class="insurance-in-author"><?= (int) $question['answer_count'] ? 'FC 답변 <span>' . (int) $question['answer_count'] . '건 등록</span>' : '답변을 기다리고 있어요' ?></p><button type="button" class="insurance-in-share" onclick="navigator.clipboard?.writeText(location.href);alert('주소를 복사했습니다.')"><img src="<?= SITE_IMG_URL ?>images/ic-detail-share.svg" alt="">공유하기</button>
                </div>
            </article>
            <section class="insurance-in-answers">
                <h2 class="insurance-in-answers-title">FC 답변 <span><?= count($answers) ?>건</span></h2>
                <?php foreach ($answers as $answer): ?><article class="insurance-in-answer">
                        <?php if ($uid === $answer['fc_member_uid']): ?><div class="insurance-in-answer-actions"><a href="<?= base_url('insurance-in/' . $question['question_id'] . '/answer/' . $answer['answer_id']) ?>">수정</a>
                                <form method="post" action="<?= base_url('insurance-in/' . $question['question_id'] . '/answer/' . $answer['answer_id'] . '/delete') ?>" onsubmit="return confirm('답변을 삭제하시겠습니까?')"><?= csrf_field() ?><button>삭제</button></form>
                            </div><?php endif; ?>
                        <article class="card">
                            <div class="card-body">
                                <div class="profile"><?php if ($profileImage($answer) !== ''): ?><img src="<?= esc($profileImage($answer)) ?>" alt="" class="avatar" onerror="this.removeAttribute('src'); this.classList.add('is-empty');"><?php else: ?><span class="avatar is-empty" aria-hidden="true"></span><?php endif; ?>
                                    <div>
                                        <p class="profile-name"><?= esc($answer['name']) ?></p>
                                        <p class="c-rate"><span class="c-rate-star">★</span> <?= number_format((float)$answer['rating'], 1) ?> <span class="c-rate-count">(<?= number_format($answer['rating_count']) ?>)</span></p>
                                        <p class="c-dot-line"><span><?= esc($answer['company'] ?: $answer['ga'] ?: 'FC') ?></span><span><?= esc($answer['region'] ?? '') ?></span></p>
                                    </div>
                                </div>
                                <div class="insurance-in-fc-actions"><a href="<?= base_url('fc/view?uid=' . rawurlencode($answer['fc_member_uid'])) ?>" class="btn btn-line">프로필 상세보기</a><a href="<?= base_url('fc/counsel?uid=' . rawurlencode($answer['fc_member_uid'])) ?>" class="btn btn-line">상담 신청하기</a></div>
                            </div>
                        </article>
                        <div class="insurance-in-answer-text">
                            <p class="insurance-in-answer-date">답변 작성일(<?= date('Y-m-d', strtotime($answer['created_at'])) ?>)</p>
                            <p><?= nl2br(esc($answer['body'])) ?></p>
                        </div>
                    </article><?php endforeach; ?>
                <?php if ($type === 'FC' && !array_filter($answers, static fn($a) => $a['fc_member_uid'] === $uid)): ?><div class="form-actions"><a class="btn btn-primary" href="<?= base_url('insurance-in/' . $question['question_id'] . '/answer') ?>">답변 등록</a></div><?php endif; ?>
            </section>
        </div>
    </div>
</main>
