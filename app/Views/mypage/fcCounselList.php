<?php
$status = $_GET['status'] ?? 'ALL';
$q = $_GET['q'] ?? '';

function counsel_status_label($status)
{
    return match ($status) {
        'REQUEST'  => '상담대기',
        'PROGRESS' => '상담진행',
        'COMPLETE' => '상담완료',
        'CANCEL'   => '상담거부',
        default    => '상태없음',
    };
}

function counsel_status_class($status)
{
    return match ($status) {
        'REQUEST'  => 'pending',
        'PROGRESS' => 'progress',
        'COMPLETE' => 'done',
        'CANCEL'   => 'reject',
        default    => '',
    };
}

function counsel_button_text($status)
{
    return match ($status) {
        'REQUEST'  => '상담대기',
        'PROGRESS' => '상담진행',
        'COMPLETE' => '상담완료',
        'CANCEL'   => '상담거부',
        default    => '',
    };
}

function chunk4($list)
{
    return array_chunk($list, 4);
}
?>

<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">상담 신청 관리</h1>

        <!-- 검색 -->
        <form class="consult-search-form" method="get">
            <div class="search-field">
                <label class="search-field-label">
                    <input type="search"
                        name="q"
                        value="<?= esc($q ?? '') ?>"
                        class="search-field-input"
                        placeholder="상담신청 고객명으로 검색">
                </label>

                <input type="hidden" name="status" value="<?= esc($status ?? '') ?>">

                <button type="submit" class="search-field-submit">
                    🔍
                </button>
            </div>
        </form>

        <section>

            <!-- 탭 -->
            <div class="c-tabs" role="tablist">
                <?php
                $tabs = [
                    '' => '전체',
                    'REQUEST' => '상담대기',
                    'COMPLETE' => '상담완료',
                    'CANCEL' => '상담거부',
                ];

                $currentStatus = $status ?? '';
                ?>

                <?php foreach ($tabs as $k => $v): ?>
                    <button type="button"
                            role="tab"
                            aria-selected="<?= ($currentStatus === $k) ? 'true' : 'false' ?>"
                            onclick="location.href='?status=<?= $k ?>&q=<?= urlencode($q ?? '') ?>';">
                        <?= $v ?>
                    </button>
                <?php endforeach; ?>
            </div>
            

            <?php if (!empty($counselList)): ?>

                <?php $pages = chunk4($counselList); ?>

                <div class="recommend-list-head">
                    <div class="control-box">
                        <button type="button" class="control-btn swiper-nav-prev"></button>
                        <button type="button" class="control-btn swiper-nav-next"></button>
                    </div>
                </div>

                <div class="swiper recommend-list recommend-list-swiper">
                    <div class="swiper-wrapper">

                        <?php foreach ($pages as $page): ?>
                            <div class="swiper-slide">
                                <div class="swiper-page-grid">

                                    <?php foreach ($page as $row): ?>

                                        <?php
                                        $region = '';
                                        if (!empty($row['region'])) {
                                            $tmp = explode(',', $row['region']);
                                            $region = trim($tmp[0]);
                                        }

                                        $insurance = [];
                                        if (!empty($row['insurance_types'])) {
                                            $insurance = array_slice(
                                                array_map('trim', explode(',', $row['insurance_types'])),
                                                0,
                                                3
                                            );
                                        }

                                        $companyLine = [];
                                        if (!empty($row['ga'])) {
                                            $companyLine[] = $row['ga'];
                                        } else {
                                            if (!empty($row['company'])) $companyLine[] = $row['company'];
                                            if (!empty($row['company_sub'])) $companyLine[] = $row['company_sub'];
                                        }

                                        $statusLabel = counsel_status_label($row['status']);
                                        $statusClass = counsel_status_class($row['status']);
                                        $btnText = counsel_button_text($row['status']);
                                        ?>

                                        <article class="card">
                                            <div class="card-body">
                                                <div class="card-link">

                                                    <a href="/mypage/fccounselview/<?= $row['counsel_uid'] ?>"
                                                       class="consult-card-hit">

                                                        <div class="profile">
                                                            <div>
                                                                <p class="profile-name"><?= esc($row['member_name']) ?></p>
                                                                <p class="consult-contact"><?= esc($row['member_email']) ?></p>
                                                                <p class="consult-contact"><?= esc($row['member_phone']) ?></p>
                                                            </div>
                                                        </div>

                                                        <p class="date">
                                                            상담 요청 일자
                                                            <time><?= date('Y.m.d', strtotime($row['created_at'])) ?></time>
                                                        </p>

                                                    </a>

                                                    <p class="badge <?= $statusClass ?>">
                                                        <?= $statusLabel ?>
                                                    </p>

                                                    <button type="button"
                                                            class="consult-status-action"
                                                            <?= $row['status'] !== 'COMPLETE' ? 'disabled' : '' ?>>
                                                        <?= $btnText ?>
                                                    </button>

                                                </div>
                                            </div>
                                        </article>

                                    <?php endforeach; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>

            <?php else: ?>

                <!-- EMPTY STATE -->
                <div class="consult-empty" style="
                    display:flex;
                    flex-direction:column;
                    justify-content:center;
                    align-items:center;
                    height:60vh;
                    text-align:center;
                ">
                    <p>아직 접수된 상담 요청이 없습니다.</p>
                    <p>고객의 상담 요청이 등록되면<br>이곳에서 확인하실 수 있습니다.</p>
                </div>

            <?php endif; ?>

        </section>
    </div>
</main>

<script>
(function() {
    if (typeof MyFC === 'undefined' || typeof Swiper === 'undefined') return;

    const scope = document.querySelector('.fc-consult-mgmt section');
    const el = scope?.querySelector('.recommend-list-swiper');
    if (!el) return;

    const swiper = MyFC.initSwiper(el, scope, {
        slidesPerView: 1,
        spaceBetween: 12,
        watchOverflow: true
    });

    scope.querySelector('.swiper-nav-prev')
        ?.addEventListener('click', () => swiper.slidePrev());

    scope.querySelector('.swiper-nav-next')
        ?.addEventListener('click', () => swiper.slideNext());
})();
</script>
