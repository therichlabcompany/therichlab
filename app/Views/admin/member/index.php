<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<div class="content-wrapper">

    <section class="content-header d-flex justify-content-between align-items-center">
        <div>
            <h1>회원관리</h1>
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
                                <th>상태</th>
                                <th>가입일</th>
                                <th>관리</th>
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
                                    <td class="text-start">
                                        <?= esc($m['email']) ?>
                                    </td>
                                    <!-- HP -->
                                    <td>
                                        <?= esc($m['phone']) ?>
                                    </td>
                                    <!-- STATUS -->
                                    <?php [$label, $class] = member_status_label($m['status']);?>
                                    <td>
                                        <span class="badge bg-<?= $class ?>">
                                            <?= $label ?>
                                        </span>
                                    </td>
                                    <!-- CREATED -->
                                    <td>
                                        <?= $m['created_at'] ?>
                                    </td>
                                    <!-- ACTION -->
                                    <td>
                                        <a href="/admin/members/<?= $m['member_id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            상세
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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