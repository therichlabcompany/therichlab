<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>회원 상세</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-body">

                    <table class="table table-bordered">
                        <tr>
                            <th>회원ID</th>
                            <td><?= $m['member_id'] ?></td>
                        </tr>

                        <tr>
                            <th>회원고유값</th>
                            <td><?= $m['member_uid'] ?></td>
                        </tr>

                        <tr>
                            <th>이메일</th>
                            <td><?= $m['email'] ?></td>
                        </tr>

                        <tr>
                            <th>휴대폰</th>
                            <td><?= $m['phone'] ?></td>
                        </tr>

                        <tr>
                            <th>이름</th>
                            <td><?= $m['name'] ?></td>
                        </tr>

                        <tr>
                            <th>생년월일</th>
                            <td><?= $m['birth'] ?></td>
                        </tr>

                        <tr>
                            <th>성별</th>
                            <td><?= $m['gender'] ?></td>
                        </tr>

                        <tr>
                            <th>상태</th>
                            <td>
                                <?php
                               
                                [$label, $class] = member_status_label($m['status']);
                               
                                echo $label;
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th>가입일</th>
                            <td><?= $m['created_at'] ?></td>
                        </tr>

                        <tr>
                            <th>최근로그인</th>
                            <td><?= $m['last_login_at'] ?></td>
                        </tr>

                    </table>

                    <div class="mt-3">
                        <a href="/admin/members" class="btn btn-secondary">
                            목록
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>