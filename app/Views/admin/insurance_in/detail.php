<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$question = $question ?? [];
$answers = $answers ?? [];
$files = $files ?? [];
$profileImage = static fn(array $row): string => !empty($row['profile_image']) ? base_url('uploads/profile/' . $row['profile_image']) : base_url('assets/images/temp/@profile-m.png');
?>
<style>
.insurance-admin-detail { padding:18px 8px 36px; color:#172033; }
.insurance-admin-detail h1 { margin:0; font-size:24px; font-weight:800; }
.insurance-admin-detail .breadcrumb-text { margin-top:6px; color:#6b7280; font-size:13px; }
.insurance-question-box,.insurance-answer-section { margin-top:16px; border:1px solid #dfe5ec; border-radius:10px; background:#fff; overflow:hidden; }
.insurance-question-head { padding:20px; background:#f8fafc; }
.insurance-question-label { color:#64748b; font-size:13px; font-weight:800; }
.insurance-question-title { margin:8px 0; font-size:20px; font-weight:800; }
.insurance-question-body,.insurance-answer-body { line-height:1.7; white-space:normal; }
.insurance-meta { display:flex; flex-wrap:wrap; gap:8px 20px; margin-top:14px; color:#64748b; font-size:13px; }
.insurance-question-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:12px 20px; border-top:1px solid #dfe5ec; }
.insurance-answer-section { padding:18px; }
.insurance-answer-section-title { margin:0 0 14px; padding-bottom:12px; border-bottom:1px solid #dfe5ec; font-size:18px; font-weight:800; }
.insurance-answer { padding:18px 0; }
.insurance-answer + .insurance-answer { border-top:1px dashed #cbd5e1; }
.insurance-answer-toolbar { display:flex; justify-content:flex-end; margin-bottom:8px; }
.insurance-fc-card { display:flex; justify-content:space-between; gap:16px; padding:16px; border:1px solid #dfe5ec; border-radius:10px; }
.insurance-fc-profile { display:flex; gap:14px; min-width:0; }
.insurance-fc-profile img { width:70px; height:70px; flex:0 0 70px; border:1px solid #dfe5ec; border-radius:50%; object-fit:cover; }
.insurance-fc-name { margin:0 0 3px; font-size:16px; font-weight:800; }
.insurance-fc-meta { margin:2px 0; color:#64748b; font-size:13px; }
.insurance-tags { display:flex; flex-wrap:wrap; gap:5px; margin-top:7px; }
.insurance-tags span { padding:2px 7px; border:1px solid #dfe5ec; border-radius:5px; color:#64748b; font-size:12px; }
.insurance-answer-content { margin-top:14px; padding-top:14px; border-top:1px solid #dfe5ec; }
.insurance-files { margin:14px 0 0; padding:10px 14px; border-radius:6px; background:#fff; }
@media(max-width:768px){.insurance-question-foot,.insurance-fc-card{align-items:stretch;flex-direction:column}.insurance-fc-card .btn{align-self:flex-start}}
</style>
<div class="content-wrapper"><section class="content-header insurance-admin-detail"><div class="container-fluid">
    <h1>보험IN 상세</h1><div class="breadcrumb-text"><?= esc($breadcrumb ?? '') ?></div>
    <?php if (session('success') || session('error')): ?><div class="alert <?= session('error') ? 'alert-danger' : 'alert-success' ?> mt-3"><?= esc(session('error') ?: session('success')) ?></div><?php endif; ?>
    <div class="d-flex gap-2 mt-3"><a class="btn btn-outline-secondary btn-sm" href="<?= base_url('admin/contents/insurance-in') ?>">&lt;- 리스트로 돌아가기</a><form method="post" action="<?= base_url('admin/contents/insurance-in/' . (int) $question['question_id'] . '/delete') ?>" onsubmit="return confirm('보험IN 게시물을 삭제하시겠습니까?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm">게시물 삭제</button></form></div>
    <article class="insurance-question-box"><div class="insurance-question-head"><div class="insurance-question-label">질문</div><h2 class="insurance-question-title"><?= esc($question['title'] ?? '-') ?></h2><div class="insurance-question-body"><?= nl2br(esc((string)($question['body'] ?? '-'))) ?></div>
        <?php if ($files): ?><ul class="insurance-files"><?php foreach ($files as $file): ?><li><?= esc($file['original_name'] ?? '-') ?> (<?= number_format(((int)($file['file_size'] ?? 0))/1024, 1) ?> KB)</li><?php endforeach; ?></ul><?php endif; ?>
        <div class="insurance-meta"><span>작성일 <?= esc($question['created_at'] ?? '-') ?></span><span>조회수 <?= number_format((int)($question['view_count'] ?? 0)) ?></span><span>작성자 <a href="<?= base_url('admin/members/' . (int)($question['member_id'] ?? 0)) ?>"><?= esc(($question['writer_name'] ?? '-') . ' (' . ($question['writer_email'] ?? '-') . ')') ?></a></span></div></div>
        <div class="insurance-question-foot"><strong>FC 답변 <?= count($answers) ?>건 등록</strong></div>
    </article>
    <section class="insurance-answer-section"><h2 class="insurance-answer-section-title">FC 답변 <span class="text-primary"><?= count($answers) ?>건</span></h2>
        <?php foreach ($answers as $answer): ?><?php $tags=array_filter(array_map('trim',explode(',',(string)($answer['insurance_types']??'')))); ?>
        <article class="insurance-answer"><div class="insurance-answer-toolbar"><form method="post" action="<?= base_url('admin/contents/insurance-in/' . (int)$question['question_id'] . '/answers/' . (int)$answer['answer_id'] . '/delete') ?>" onsubmit="return confirm('이 FC 답변을 삭제하시겠습니까?')"><?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">답변 삭제</button></form></div>
            <div class="insurance-fc-card"><div class="insurance-fc-profile"><img src="<?= esc($profileImage($answer)) ?>" alt=""><div><p class="insurance-fc-name"><?= esc($answer['name'] ?? '-') ?></p><p class="insurance-fc-meta">★ <?= number_format((float)($answer['rating']??0),1) ?> (<?= number_format((int)($answer['rating_count']??0)) ?>)</p><p class="insurance-fc-meta"><?= esc($answer['company'] ?: $answer['ga'] ?: '소속 정보 없음') ?> · <?= esc($answer['region'] ?? '지역 정보 없음') ?></p><?php if($tags): ?><div class="insurance-tags"><?php foreach(array_slice($tags,0,6) as $tag): ?><span><?= esc(fc_insurance_label($tag)) ?></span><?php endforeach; ?></div><?php endif; ?></div></div><a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/fc-members/' . (int)($answer['member_id']??0)) ?>">FC 상세</a></div>
            <div class="insurance-answer-content"><div class="insurance-meta">답변 작성일 <?= esc($answer['created_at'] ?? '-') ?></div><div class="insurance-answer-body mt-2"><?= nl2br(esc((string)($answer['body']??'-'))) ?></div></div>
        </article><?php endforeach; ?>
        <?php if(!$answers): ?><div class="text-center text-muted py-5">등록된 FC 답변이 없습니다.</div><?php endif; ?>
    </section>
</div></section></div>
<?= $this->include('admin/layout/footer') ?>
