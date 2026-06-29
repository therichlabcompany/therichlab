<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<div class="content-wrapper">

    <section class="content-header d-flex justify-content-between align-items-center">
        <div>
            <h1>FC 회원관리</h1>
            <!-- <small>총 회원수 : <b><?= number_format($total) ?></b></small> -->
        </div>

        <button onclick="history.back()" class="btn btn-secondary btn-sm">
            ← Back
        </button>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- =========================
            검색 박스
            ========================= -->
            <div class="card mb-3">
                <div class="card-body">

                    <form method="get" id="searchForm">

                        <div class="row">

                            <!-- 시작일 -->
                            <div class="col-md-2">
                                <label>시작일</label>
                                <input type="date" name="start_date"
                                    class="form-control"
                                    value="<?= esc($_GET['start_date'] ?? '') ?>">
                            </div>

                            <!-- 종료일 -->
                            <div class="col-md-2">
                                <label>종료일</label>
                                <input type="date" name="end_date"
                                    class="form-control"
                                    value="<?= esc($_GET['end_date'] ?? '') ?>">
                            </div>

                            <!-- 이메일 -->
                            <div class="col-md-2">
                                <label>이메일</label>
                                <input type="text" name="email"
                                    class="form-control"
                                    placeholder="email"
                                    value="<?= esc($_GET['email'] ?? '') ?>">
                            </div>

                            <!-- 이름 -->
                            <div class="col-md-2">
                                <label>이름</label>
                                <input type="text" name="name"
                                    class="form-control"
                                    placeholder="name"
                                    value="<?= esc($_GET['name'] ?? '') ?>">
                            </div>

                            <!-- 전화번호 -->
                            <div class="col-md-2">
                                <label>연락처</label>
                                <input type="text" name="phone"
                                    class="form-control"
                                    placeholder="phone"
                                    value="<?= esc($_GET['phone'] ?? '') ?>">
                            </div>

                            <!-- 심의상태 -->
                            <div class="col-md-2">
                                <label>심의상태</label>
                                <select name="approval" class="form-control">
                                    <option value="">전체</option>
                                    <option value="WAIT" <?= ($_GET['approval'] ?? '') == 'WAIT' ? 'selected' : '' ?>>승인대기</option>
                                    <option value="APPROVE" <?= ($_GET['approval'] ?? '') == 'APPROVE' ? 'selected' : '' ?>>승인완료</option>
                                    <option value="REJECT" <?= ($_GET['approval'] ?? '') == 'REJECT' ? 'selected' : '' ?>>반려</option>
                                </select>
                            </div>

                            <!-- 회원상태 -->
                            <div class="col-md-2">
                                <label>회원상태</label>
                                <select name="status" class="form-control">
                                    <option value="">전체</option>
                                    <option value="ACTIVE" <?= ($_GET['status'] ?? '') == 'ACTIVE' ? 'selected' : '' ?>>정상</option>
                                    <option value="BLOCK" <?= ($_GET['status'] ?? '') == 'BLOCK' ? 'selected' : '' ?>>차단</option>
                                    <option value="LEAVE" <?= ($_GET['status'] ?? '') == 'LEAVE' ? 'selected' : '' ?>>탈퇴</option>
                                </select>
                            </div>

                            <!-- 버튼 -->
                            <div class="col-md-2 d-flex align-items-end">

                                <button class="btn btn-primary w-100">
                                    검색
                                </button>

                            </div>

                        </div>

                    </form>

                </div>
            </div>
            <!-- =========================
            엑셀 버튼
            ========================= -->
            <div class="mb-2">
                <a href="/admin/members/export?<?= http_build_query($_GET) ?>"
                    class="btn btn-success">
                    엑셀 다운로드
                </a>
            </div>
            <div class="card">
                <div class="card-body table-responsive">

                    <table class="table table-bordered table-hover text-center align-middle">

                        <thead class="table-light">
                            <tr>

                            <th>NO</th>

                            <th>이름</th>

                            <th>이메일</th>

                            <th>연락처</th>

                            <th>가입단계</th>

                            <th>심의상태</th>

                            <th>회원상태</th>

                            <th>가입일</th>

                            <th>관리</th>

                            </tr>
                        </thead>
                        <tbody>

<?php
$startNumber = $total - (($page - 1) * $perPage);

foreach ($members as $m):
?>

<tr>

    <!-- 번호 -->
    <td><?= $startNumber-- ?></td>

    <!-- 이름 -->
    <td><?= esc($m['name']) ?></td>

    <!-- 이메일 -->
    <td class="text-start">
        <?= esc($m['email']) ?>
    </td>

    <!-- 연락처 -->
    <td><?= esc($m['phone']) ?></td>

    <!-- 가입단계 -->
    <td>
        <?= $m['fc_step'] ?> 단계
    </td>

    <!-- 심의상태 -->
    <td>

        <?php

        switch ($m['fc_review_status']) {

            case 'APPROVE':
                echo '<span class="badge bg-success">승인완료</span>';
                break;

            case 'REJECT':
                echo '<span class="badge bg-danger">반려</span>';
                break;

            default:
                echo '<span class="badge bg-warning">승인대기</span>';
                break;
        }

        ?>

    </td>

    <!-- 회원상태 -->
    <td>

        <?php [$label, $class] = member_status_label($m['status']); ?>

        <span class="badge bg-<?= $class ?>">
            <?= $label ?>
        </span>

    </td>

    <!-- 가입일 -->
    <td><?= $m['created_at'] ?></td>

    <!-- 관리 -->
    <td>

        <a href="/admin/fc-members/<?= $m['member_id'] ?>"
           class="btn btn-primary btn-sm">

            상세

        </a>

    </td>

</tr>

<?php endforeach; ?>

<?php if (empty($members)): ?>

<tr>
    <td colspan="9">
        등록된 FC 회원이 없습니다.
    </td>
</tr>

<?php endif; ?>

</tbody>

                    </table>

                </div>
            </div>

            <!-- PAGINATION -->
            <div class="mt-3 d-flex justify-content-center">
                <?= $pager->makeLinks($page, $perPage, $total, 'default_full') ?>
            </div>

    </section>
</div>

<?= $this->include('admin/layout/footer') ?>