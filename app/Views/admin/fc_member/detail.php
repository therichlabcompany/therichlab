<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<style>
    .fc-wrap {
        padding: 15px;
    }

    .fc-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .fc-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .fc-card h5 {
        margin: 0;
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 700;
        background: #f5f5f5;
        border-bottom: 1px solid #eee;
    }

    .fc-table {
        width: 100%;
        font-size: 13px;
    }

    .fc-table th {
        width: 140px;
        background: #fafafa;
        padding: 8px;
        text-align: left;
        color: #666;
    }

    .fc-table td {
        padding: 8px;
    }

    .badge-box {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
    }

    .fc-img-thumb {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: 0.2s;
    }

    .fc-img-thumb:hover {
        transform: scale(1.03);
    }
</style>

<div class="content-wrapper">

    <section class="content-header d-flex justify-content-between align-items-center">
        <div>
            <h1>FC 회원 상세</h1>
            <small><?= esc($m['member_uid']) ?></small>
        </div>

        <a href="/admin/fc-members" class="btn btn-secondary btn-sm">← 목록</a>
    </section>

    <section class="content">
        <div class="fc-wrap">

            <!-- =========================
                1. 핵심 요약 (가장 중요)
            ========================= -->
            <div class="fc-card">
                <h5>회원 요약</h5>
                <table class="fc-table">
                    <tr>
                        <th>이름</th>
                        <td><b><?= esc($m['name']) ?></b></td>

                        <th>상태</th>
                        <td>
                            <?php [$label, $class] = member_status_label($m['status']); ?>
                            <span class="badge bg-<?= $class ?>"><?= $label ?></span>
                        </td>

                        <th>단계</th>
                        <td><?= $m['fc_step'] ?></td>
                    </tr>

                    <tr>
                        <th>이메일</th>
                        <td><?= esc($m['email']) ?></td>

                        <th>연락처</th>
                        <td><?= esc($m['phone']) ?></td>

                        <th>심의</th>
                        <td><?= esc($m['fc_review_status']) ?></td>
                    </tr>

                    <tr>
                        <th>가입일</th>
                        <td><?= $m['created_at'] ?></td>

                        <th>최근 로그인</th>
                        <td><?= $m['last_login_at'] ?? '-' ?></td>

                        <th></th>
                        <td></td>
                    </tr>
                </table>
            </div>

            <!-- =========================
    2. 프로필
========================= -->
            <div class="fc-card">
                <h5>프로필</h5>

                <?php if ($profile): ?>
                    <table class="fc-table">

                        <tr>
                            <th>회사</th>
                            <td><?= esc($profile['company']) ?></td>

                            <th>GA</th>
                            <td><?= esc($profile['ga']) ?></td>
                        </tr>

                        <tr>
                            <th>직책</th>
                            <td><?= esc($profile['position']) ?></td>

                            <th>자격증</th>
                            <td><?= esc($profile['license_no']) ?></td>
                        </tr>

                        <tr>
                            <th>업무시간</th>
                            <td><?= $profile['time_from'] ?> ~ <?= $profile['time_to'] ?></td>

                            <th>언어</th>
                            <td><?= esc($profile['language']) ?></td>
                        </tr>

                    </table>
                <?php else: ?>
                    <div style="padding:10px;">프로필 없음</div>
                <?php endif; ?>
            </div>

            <!-- =========================
    3. 활동 정보
========================= -->
            <div class="fc-card">
                <h5>활동 정보</h5>

                <?php if ($activity): ?>
                    <table class="fc-table">

                        <tr>
                            <th>지역</th>
                            <td colspan="3">
                                <?php foreach (explode(',', $activity['region']) as $r): ?>
                                    <span class="badge bg-secondary">
                                        <?= fc_region_label(trim($r)) ?>
                                    </span>
                                <?php endforeach; ?>

                            </td>
                        </tr>

                        <tr>
                            <th>보험</th>
                            <td colspan="3"><?php foreach (explode(',', $activity['insurance_types']) as $item): ?>
                                    <span class="badge bg-light text-dark border">
                                        <?= fc_insurance_label(trim($item)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>한줄</th>
                            <td colspan="3"><?= esc($activity['hero_line']) ?></td>
                        </tr>

                        <tr>
                            <th>소개</th>
                            <td colspan="3"><?= nl2br(esc($activity['intro'])) ?></td>
                        </tr>

                        <tr>
                            <th>경력</th>
                            <td colspan="3"><?= nl2br(esc($activity['career'])) ?></td>
                        </tr>

                    </table>
                <?php else: ?>
                    <div style="padding:10px;">없음</div>
                <?php endif; ?>
            </div>

            <!-- =========================
    4. 활동 자료
========================= -->
            <div class="fc-card">
                <h5>활동 자료</h5>

                <div style="padding:10px;">
                    <?php foreach ($activityItems as $item): ?>
                        <div style="margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <b><?= esc($item['title']) ?></b>
                            <span style="color:#999;font-size:12px;">(<?= esc($item['type']) ?>)</span>

                            <?php if ($item['file_path']): ?>
                                <div>
                                    <img
                                        src="/uploads/activity/<?= esc($item['file_path']) ?>"
                                        class="fc-img-thumb js-img-view"
                                        data-src="/uploads/activity/<?= esc($item['file_path']) ?>">
                                </div>
                            <?php endif; ?>

                            <?php if ($item['url']): ?>
                                <div>
                                    <a href="<?= esc($item['url']) ?>" target="_blank">링크</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- =========================
    5. 스토리
========================= -->
            <!-- =========================
    5. 스토리 (FULL REFACTOR)
========================= -->

            <!-- 🔥 1. 스토리 영상 -->
            <div class="fc-card">
                <h5>스토리 영상</h5>

                <div style="padding:10px;">
                    <?php if ($story && $story['story_video']): ?>
                        <video controls style="width:100%; max-height:420px; border-radius:6px; background:#000;">
                            <source src="/uploads/story/video/<?= esc($story['story_video']) ?>">
                        </video>
                    <?php else: ?>
                        <div style="color:#999;">등록된 영상이 없습니다.</div>
                    <?php endif; ?>
                </div>
            </div>


            <!-- 🔥 2. 스토리 대표 이미지 -->
            <div class="fc-card">
                <h5>스토리 대표 이미지</h5>

                <div style="padding:10px;">
                    <?php if ($story && $story['story_image']): ?>
                        <img
                            src="/uploads/story/main/<?= esc($story['story_image']) ?>"
                            class="fc-img-thumb js-img-view"
                            data-src="/uploads/story/main/<?= esc($story['story_image']) ?>"
                            style="width:200px; height:200px;">
                    <?php else: ?>
                        <div style="color:#999;">대표 이미지 없음</div>
                    <?php endif; ?>
                </div>
            </div>


            <!-- 🔥 3. 스토리 이미지 갤러리 -->
            <div class="fc-card">
                <h5>스토리 이미지 갤러리</h5>

                <div style="padding:10px; display:flex; flex-wrap:wrap; gap:10px;">

                    <?php if (!empty($storyImages)): ?>
                        <?php foreach ($storyImages as $img): ?>
                            <img
                                src="/uploads/story/images/<?= esc($img['image_path']) ?>"
                                class="fc-img-thumb js-img-view"
                                data-src="/uploads/story/images/<?= esc($img['image_path']) ?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="color:#999;">등록된 이미지가 없습니다.</div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- =========================
    6. 심의
========================= -->
            <!-- =========================
    6. 심의
========================= -->
            <!-- =========================
    6. 심의 정보 (REFACTORED UI)
========================= -->
            <div class="fc-card">
                <h5>심의 정보</h5>

                <?php if ($review): ?>

                    <div style="padding:15px;">

                        <!-- 기본 정보 -->
                        <table class="fc-table">
                            <tr>
                                <th>심의번호</th>
                                <td><?= esc($review['deliberation_no']) ?></td>

                                <th>상태</th>
                                <td>
                                    <?php if ($review['status'] === 'APPROVE'): ?>
                                        <span class="badge bg-success">승인완료</span>
                                    <?php elseif ($review['status'] === 'REJECT'): ?>
                                        <span class="badge bg-danger">반려</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">대기</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <th>기간</th>
                                <td colspan="3">
                                    <?= $review['approval_start'] ?> ~ <?= $review['approval_end'] ?>
                                </td>
                            </tr>

                            <tr>
                                <th>심의 의견</th>
                                <td colspan="3">
                                    <?= nl2br(esc($review['deliberation_opinion'])) ?>
                                </td>
                            </tr>

                            <?php if (!empty($review['reject_reason'])): ?>
                                <tr>
                                    <th style="color:#d33;">반려 사유</th>
                                    <td colspan="3" style="color:#d33;">
                                        <?= nl2br(esc($review['reject_reason'])) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <!-- =========================
                버튼 영역
            ========================= -->
                        <?php if ($review['status'] === 'WAIT'): ?>
                            <div style="
                    margin-top:20px;
                    display:flex;
                    justify-content:center;
                    gap:10px;
                ">

                                <!-- 승인 -->
                                <form method="post" action="/admin/fc-members/review/approve">
                                    <input type="hidden" name="member_uid" value="<?= esc($m['member_uid']) ?>">
                                    <button type="submit"
                                        class="btn btn-success btn-sm"
                                        style="min-width:120px;">
                                        ✔ 승인 완료
                                    </button>
                                </form>

                                <!-- 반려 -->
                                <button type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    style="min-width:120px;"
                                    onclick="openRejectModal('<?= esc($m['member_uid']) ?>')">
                                    ✖ 반려 처리
                                </button>

                            </div>
                        <?php endif; ?>

                    </div>

                <?php else: ?>
                    <div style="padding:15px; color:#999;">
                        심의 정보 없음
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>
<div id="img-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
    <img id="img-modal-src" style="max-width:90%; max-height:90%; border-radius:8px;">
</div>
<!-- =========================
    반려 모달
========================= -->
<div id="rejectModal"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.6);
        z-index:99999;
        justify-content:center;
        align-items:center;
     ">

    <div style="
        width:420px;
        background:#fff;
        border-radius:10px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(0,0,0,0.3);
    ">

        <!-- 헤더 -->
        <div style="
            padding:12px 15px;
            background:#dc3545;
            color:#fff;
            font-weight:700;
        ">
            반려 처리
        </div>

        <!-- 바디 -->
        <form method="post" action="/admin/fc-members/review/reject">
            <input type="hidden" name="member_uid" id="reject_member_uid">

            <div style="padding:15px;">

                <label style="font-size:13px; font-weight:600;">
                    반려 사유
                </label>

                <textarea name="reject_reason"
                    required
                    style="
                            width:100%;
                            height:120px;
                            margin-top:8px;
                            padding:10px;
                            border:1px solid #ddd;
                            border-radius:6px;
                            resize:none;
                          "
                    placeholder="예: 서류 미비 / 정보 불일치 / 승인 기준 미충족 등"></textarea>

            </div>

            <!-- 버튼 -->
            <div style="
                padding:12px 15px;
                display:flex;
                justify-content:flex-end;
                gap:8px;
                border-top:1px solid #eee;
                background:#fafafa;
            ">
                <button type="button"
                    onclick="closeRejectModal()"
                    class="btn btn-light btn-sm">
                    취소
                </button>

                <button type="submit"
                    class="btn btn-danger btn-sm">
                    반려 확정
                </button>
            </div>

        </form>

    </div>
</div>
<?= $this->include('admin/layout/footer') ?>

<script>
    document.addEventListener('click', function(e) {
        const img = e.target.closest('.js-img-view');
        if (!img) return;

        const modal = document.getElementById('img-modal');
        const modalImg = document.getElementById('img-modal-src');

        modalImg.src = img.dataset.src;
        modal.style.display = 'flex';
    });

    // 닫기
    document.getElementById('img-modal').addEventListener('click', function() {
        this.style.display = 'none';
    });
</script>

<script>
    function openRejectModal(uid) {
        document.getElementById('reject_member_uid').value = uid;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
</script>