<?php
$statusMap = [
    'REQUEST' => [
        'badge' => 'pending',
        'badgeText' => '상담대기',
        'dateText' => '상담 요청 일자',
        'button' => [
            'text' => '상담대기',
            'disabled' => true,
        ],
    ],

    'PROGRESS' => [
        'badge' => 'pending',
        'badgeText' => '상담진행중',
        'dateText' => '상담 진행 일자',
        'button' => [
            'text' => '상담진행중',
            'disabled' => true,
        ],
    ],

    'COMPLETE' => [
        'badge' => 'done',
        'badgeText' => '상담완료',
        'dateText' => '상담 완료 일자',
        'button' => [
            'text' => 'dynamic',   // 👈 이렇게만 둠
            'disabled' => 'dynamic',
        ],
    ],

    'CANCEL' => [
        'badge' => 'reject',
        'badgeText' => '상담거부',
        'dateText' => '상담 거부 일자',
        'button' => [
            'text' => '상담거부',
            'disabled' => true,
        ],
    ],
];
?>
<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">상담 현황</h1>

        <section>
            <?php
            $status = $_GET['status'] ?? '';
            ?>

            <div class="c-tabs" role="tablist">

                <button
                    type="button"
                    class="<?= $status === '' ? 'is-active' : '' ?>"
                    onclick="location.href='?status='"
                    aria-selected="<?= $status === '' ? 'true' : 'false' ?>">
                    전체
                </button>

                <button
                    type="button"
                    class="<?= $status === 'REQUEST' ? 'is-active' : '' ?>"
                    onclick="location.href='?status=REQUEST'"
                    aria-selected="<?= $status === 'REQUEST' ? 'true' : 'false' ?>">
                    상담대기
                </button>

                <button
                    type="button"
                    class="<?= $status === 'PROGRESS' ? 'is-active' : '' ?>"
                    onclick="location.href='?status=PROGRESS'"
                    aria-selected="<?= $status === 'PROGRESS' ? 'true' : 'false' ?>">
                    상담진행중
                </button>

                <button
                    type="button"
                    class="<?= $status === 'COMPLETE' ? 'is-active' : '' ?>"
                    onclick="location.href='?status=COMPLETE'"
                    aria-selected="<?= $status === 'COMPLETE' ? 'true' : 'false' ?>">
                    상담완료
                </button>

                <button
                    type="button"
                    class="<?= $status === 'CANCEL' ? 'is-active' : '' ?>"
                    onclick="location.href='?status=CANCEL'"
                    aria-selected="<?= $status === 'CANCEL' ? 'true' : 'false' ?>">
                    상담거부
                </button>

            </div>
            <div class="recommend-list">

                <?php if (!empty($counselList)): ?>

                    <?php foreach ($counselList as $row): ?>

                        <?php

                        $companyLine = [];

                        if (!empty($row['ga'])) {
                            $companyLine[] = $row['ga'];
                        } else {
                            if (!empty($row['company'])) $companyLine[] = $row['company'];
                            if (!empty($row['company_sub'])) $companyLine[] = $row['company_sub'];
                        }

                        $profileImage = !empty($row['profile_image'])
                            ? '/uploads/profile/' . $row['profile_image']
                            : SITE_IMG_URL . 'images/temp/@profile-m.png';

                        $region = '';

                        if (!empty($row['region'])) {

                            $regions = array_map(function ($r) {
                                return fc_region_label(trim($r));
                            }, explode(',', $row['region']));

                            $region = $regions[0] ?? '';
                        }

                        $insuranceTypes = [];

                        if (!empty($row['insurance_types'])) {

                            $insuranceTypes = array_map(
                                'fc_insurance_label',
                                array_slice(
                                    array_map('trim', explode(',', $row['insurance_types'])),
                                    0,
                                    3
                                )
                            );
                        }

                        $status = $statusMap[$row['status']] ?? $statusMap['wait'];

                        ?>

                        <article class="card">

                            <div class="card-body">
                                <div class="card-link">
                                    <!-- <a href="/mypage/counsel/<?= $row['counsel_id'] ?>" class="card-link"> -->

                                    <div class="profile">

                                        <img src="<?= $profileImage ?>" class="avatar" alt="">

                                        <div>

                                            <p class="profile-name"><?= esc($row['name']) ?></p>

                                            <p class="c-rate">
                                                <span class="c-rate-star">★</span>
                                                <?= number_format($row['rating'] ?? 5.0, 1) ?>
                                                <span class="c-rate-count">(<?= number_format($row['review_count'] ?? 0) ?>)</span>
                                            </p>

                                            <p class="c-dot-line">

                                                <?php if ($companyLine): ?>
                                                    <span><?= esc(implode(' · ', array_slice($companyLine, 0, 2))) ?></span>
                                                <?php endif; ?>

                                                <?php if ($region): ?>
                                                    <span class="location"><?= esc($region) ?></span>
                                                <?php endif; ?>

                                            </p>

                                            <div class="list-tags">

                                                <?php foreach ($insuranceTypes as $type): ?>

                                                    <span><?= esc($type) ?></span>

                                                <?php endforeach; ?>

                                            </div>

                                        </div>

                                    </div>

                                    <p class="date">

                                        <?= $status['dateText'] ?> :

                                        <time><?= date('Y.m.d', strtotime($row['created_at'])) ?></time>

                                    </p>

                                    <p class="badge <?= $status['badge'] ?>">
                                        <?= $status['badgeText'] ?>
                                    </p>

                                    <?php
                                    $map = $statusMap[$row['status']] ?? null;
                                    $isReviewWrite =
                                        $row['status'] === 'COMPLETE' && empty($row['review_id']);

                                    $reviewUrl = $isReviewWrite
                                        ? "/mypage/counselReview/" . $row['counsel_uid']
                                        : '';

                                    $buttonText = '';

                                    if ($row['status'] === 'COMPLETE') {
                                        $buttonText = !empty($row['review_id'])
                                            ? '후기 작성 완료'
                                            : '후기 작성하기';
                                    }

                                    $canWriteReview = ($row['status'] === 'COMPLETE' && empty($row['review_id']));
                                    ?>

                                    <?php if (!empty($map['button'])): ?>
                                        <button
                                            type="button"
                                            class="consult-status-action"
                                            <?= $canWriteReview ? '' : 'disabled' ?>
                                            <?= $canWriteReview ? "onclick=\"event.stopPropagation(); location.href='{$reviewUrl}'\"" : '' ?>>


                                            <?= $row['status'] === 'COMPLETE'
                                                ? (!empty($row['review_id']) ? '후기 작성 완료' : '후기 작성하기')
                                                : esc($map['button']['text']) ?>

                                        </button>
                                    <?php endif; ?>

                                    <!-- </a> -->
                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-consult">

                        <img src="<?= SITE_IMG_URL ?>images/icon/empty.svg" alt="">

                        <h3>상담 내역이 없습니다.</h3>

                        <p>
                            아직 요청한 상담이 없습니다.<br>
                            관심 있는 FC에게 상담을 신청해보세요.
                        </p>

                        <a href="/fc/list" class="btn-primary">
                            FC 찾기
                        </a>

                    </div>

                <?php endif; ?>

            </div>
        </section>
    </div>
</main>
<style>
    .recommend-list:has(.empty-consult) {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 500px;
        /* 원하는 높이 */
    }

    .empty-consult {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
        padding: 40px 20px;
    }

    .empty-consult img {
        width: 72px;
        margin-bottom: 20px;
        opacity: .5;
    }

    .empty-consult h3 {
        margin: 0 0 10px;
        font-size: 22px;
        font-weight: 700;
    }

    .empty-consult p {
        margin: 0 0 24px;
        color: #777;
        line-height: 1.7;
    }
</style>