<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<style>
    .member-page {
        font-size: 14px;
    }

    .content-header.member-page {
        padding: 18px 8px 14px;
    }

    .content-header.member-page h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
    }

    .content-header.member-page small {
        display: block;
        margin-top: 4px;
        color: #6c757d;
        font-size: 13px;
    }

    .member-search-card .card-body {
        padding: 18px;
    }

    .member-search-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(140px, 1fr)) 108px 108px;
        gap: 12px;
        align-items: end;
    }

    .member-search-grid label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 13px;
        font-weight: 600;
    }

    .member-search-grid .form-control,
    .member-search-grid .btn {
        min-height: 38px;
        font-size: 14px;
    }

    .member-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .member-toolbar .summary {
        color: #495057;
        font-size: 13px;
    }

    .member-table-card .card-body {
        padding: 0;
    }

    .member-table {
        margin: 0;
        table-layout: fixed;
        font-size: 14px;
    }

    .member-table th {
        padding: 12px 10px;
        color: #495057;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        background: #f8f9fa;
    }

    .member-table td {
        padding: 12px 10px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .member-table .col-no {
        width: 72px;
    }

    .member-table .col-name {
        width: 130px;
    }

    .member-table .col-phone {
        width: 150px;
    }

    .member-table .col-status {
        width: 110px;
    }

    .member-table .col-counsel {
        width: 128px;
    }

    .member-table .col-date {
        width: 170px;
    }

    .member-table .col-action {
        width: 92px;
    }

    .member-table .email-cell {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-table .badge {
        min-width: 52px;
        padding: 0.45em 0.65em;
        font-size: 12px;
    }

    @media (max-width: 1200px) {
        .member-search-grid {
            grid-template-columns: repeat(3, minmax(150px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .member-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .member-search-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">

    <section class="content-header d-flex justify-content-between align-items-center member-page">
        <div>
            <h1>개인 회원 관리</h1>
            <small>총 <?= number_format($total) ?>명</small>
        </div>

        <button onclick="history.back()" class="btn btn-secondary btn-sm">
            Back
        </button>
    </section>

    <section class="content member-page">
        <div class="container-fluid">

            <div class="card member-search-card mb-3">
                <div class="card-body">
                    <form method="get" id="searchForm">
                        <div class="member-search-grid">
                            <div>
                                <label>시작일</label>
                                <input type="date" name="start_date"
                                    class="form-control"
                                    value="<?= esc($_GET['start_date'] ?? '') ?>">
                            </div>

                            <div>
                                <label>종료일</label>
                                <input type="date" name="end_date"
                                    class="form-control"
                                    value="<?= esc($_GET['end_date'] ?? '') ?>">
                            </div>

                            <div>
                                <label>이메일</label>
                                <input type="text" name="email"
                                    class="form-control"
                                    placeholder="이메일 검색"
                                    value="<?= esc($_GET['email'] ?? '') ?>">
                            </div>

                            <div>
                                <label>이름</label>
                                <input type="text" name="name"
                                    class="form-control"
                                    placeholder="이름 검색"
                                    value="<?= esc($_GET['name'] ?? '') ?>">
                            </div>

                            <div>
                                <label>연락처</label>
                                <input type="text" name="phone"
                                    class="form-control"
                                    placeholder="연락처 검색"
                                    value="<?= esc($_GET['phone'] ?? '') ?>">
                            </div>

                            <button class="btn btn-primary" type="submit">검색</button>

                            <a href="<?= base_url('admin/members') ?>" class="btn btn-outline-secondary">
                                초기화
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="member-toolbar">
                <div class="summary">
                    <?= number_format($total) ?>건 중 <?= number_format(count($members)) ?>건 표시
                </div>

                <a href="<?= base_url('admin/members/create') ?>" class="btn btn-outline-primary btn-sm">
                    신규 등록
                </a>

                <a href="/admin/members/export?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">
                    엑셀 다운로드
                </a>
            </div>

            <div class="card member-table-card">
                <div class="card-body table-responsive">

                    <table class="table table-hover text-center align-middle member-table">

                        <thead>
                            <tr>
                                <th class="col-no">NO</th>
                                <th class="col-name">이름</th>
                                <th>이메일</th>
                                <th class="col-phone">연락처</th>
                                <th>생년월일</th>
                                <th>성별</th>
                                <th class="col-counsel">상담 요청 건</th>
                                <th class="col-date">가입일</th>
                                <th class="col-action">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $startNumber = $total - (($page - 1) * $perPage);
                            
                            foreach ($members as $m): ?>
                                <tr>

                                    <!-- NO -->
                                    <td><?= $startNumber-- ?></td>

                                    <!-- NAME -->
                                    <td>
                                        <?= esc($m['name']) ?>
                                    </td>
                                    <!-- EMAIL -->
                                    <td class="text-start email-cell" title="<?= esc($m['email']) ?>">
                                        <?= esc($m['email']) ?>
                                    </td>
                                    <!-- HP -->
                                    <td>
                                        <?= esc($m['phone']) ?>
                                    </td>
                                    <td>
                                        <?= esc($m['birth'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= ($m['gender'] ?? '') === 'F' ? '여성' : (($m['gender'] ?? '') === 'M' ? '남성' : '-') ?>
                                    </td>
                                    <!-- COUNSEL COUNT -->
                                    <td>
                                        <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels') ?>" class="btn btn-sm btn-outline-primary">
                                            <?= number_format((int) ($m['counsel_count'] ?? 0)) ?>건
                                        </a>
                                    </td>
                                    <!-- CREATED -->
                                    <td>
                                        <?= !empty($m['created_at']) ? esc(date('Ymd H:i:s', strtotime($m['created_at']))) : '-' ?>
                                    </td>
                                    <!-- ACTION -->
                                    <td>
                                        <a href="/admin/members/<?= $m['member_id'] ?>" class="btn btn-sm btn-primary">
                                            상세
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="8" class="text-muted py-4">
                                        검색 결과가 없습니다.
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

        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
