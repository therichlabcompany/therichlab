<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class Member extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // =========================
    // 회원 리스트
    // =========================
    public function index()
    {
        $db = \Config\Database::connect();
        $request = $this->request;

        // =========================
        // paging
        // =========================
        $page    = max(1, (int) ($request->getGet('page') ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        // =========================
        // sorting
        // =========================
        $sortField = $request->getGet('sort') ?? 'member_id';
        $sortOrder = strtoupper($request->getGet('order') ?? 'DESC');

        $allowedSort = [
            'member_id',
            'email',
            'name',
            'member_type',
            'status',
            'created_at'
        ];

        if (!in_array($sortField, $allowedSort)) {
            $sortField = 'member_id';
        }

        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        // =========================
        // search filters
        // =========================
        $startDate = $request->getGet('start_date');
        $endDate   = $request->getGet('end_date');

        $email = trim($request->getGet('email') ?? '');
        $name  = trim($request->getGet('name') ?? '');
        $phone = trim($request->getGet('phone') ?? '');

        // =========================
        // base builder
        // =========================
        $baseBuilder = $db->table('my_fc_member')
            ->where('member_type', 'USER')
            ->where('deleted_at', null);

        // =========================
        // date filter
        // =========================
        if (!empty($startDate)) {
            $baseBuilder->where('created_at >=', $startDate . ' 00:00:00');
        }

        if (!empty($endDate)) {
            $baseBuilder->where('created_at <=', $endDate . ' 23:59:59');
        }

        // =========================
        // keyword filters
        // =========================
        if (!empty($email)) {
            $baseBuilder->like('email', $email);
        }

        if (!empty($name)) {
            $baseBuilder->like('name', $name);
        }

        if (!empty($phone)) {
            $baseBuilder->like('phone', $phone);
        }

        // =========================
        // total count (clone 중요)
        // =========================
        $total = (clone $baseBuilder)->countAllResults();

        // =========================
        // list query
        // =========================
        $members = (clone $baseBuilder)
            ->select('my_fc_member.*')
            ->select('(
                SELECT COUNT(*)
                FROM my_fc_counsel c
                WHERE c.member_uid = my_fc_member.member_uid
                AND c.deleted_at IS NULL
            ) AS counsel_count', false)
            ->orderBy($sortField, $sortOrder)
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // =========================
        // pager (CI4 standard)
        // =========================
        $pager = \Config\Services::pager();
        return view('admin/member/index', [
            'members' => $members,
            'total'   => $total,
            'page'    => $page,
            'pager'    => $pager,
            'perPage' => $perPage,
            'sort'    => $sortField,
            'order'   => $sortOrder,
        ]);
    }

    public function export()
    {
        $request = $this->request;
        $builder = $this->db->table('my_fc_member')
            ->select('name, email, phone, birth, gender, created_at, member_uid')
            ->where('member_type', 'USER')
            ->where('deleted_at', null);

        if ($request->getGet('start_date')) {
            $builder->where('created_at >=', $request->getGet('start_date') . ' 00:00:00');
        }

        if ($request->getGet('end_date')) {
            $builder->where('created_at <=', $request->getGet('end_date') . ' 23:59:59');
        }

        foreach (['email', 'name', 'phone'] as $field) {
            $value = trim((string) ($request->getGet($field) ?? ''));
            if ($value !== '') {
                $builder->like($field, $value);
            }
        }

        $rows = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
        $csv = "\xEF\xBB\xBF";
        $csv .= "이름,이메일주소,휴대폰번호,생년월일,성별,상담요청건수,가입일시\n";

        foreach ($rows as $row) {
            $counselCount = $this->db->table('my_fc_counsel')
                ->where('member_uid', $row['member_uid'])
                ->where('deleted_at', null)
                ->countAllResults();

            $csv .= $this->csvLine([
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['phone'] ?? '',
                $row['birth'] ?? '',
                ($row['gender'] ?? '') === 'F' ? '여성' : (($row['gender'] ?? '') === 'M' ? '남성' : ''),
                (string) $counselCount,
                !empty($row['created_at']) ? date('Ymd H:i:s', strtotime($row['created_at'])) : '',
            ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="members_' . date('YmdHis') . '.csv"')
            ->setBody($csv);
    }

    // =========================
    // 회원 상세
    // =========================
    public function detail($id)
    {
        $db = \Config\Database::connect();

        $member = $db->table('my_fc_member')
            ->where('member_id', $id)
            ->where('member_type', 'USER')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $memberUid = $member['member_uid'];

        $counselCount = $db->table('my_fc_counsel')
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->countAllResults();

        $reviewCount = $db->table('my_fc_counsel_review')
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->countAllResults();

        $reviews = $db->table('my_fc_counsel_review r')
            ->select("
                r.*,
                fc.name AS fc_name,
                fp.profile_image,
                fp.company,
                fpa.region,
                fpa.intro,
                COALESCE(rs.avg_rating, 0) AS avg_rating
            ")
            ->join('my_fc_member fc', 'fc.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile_activity fpa', 'fpa.member_uid = r.fc_member_uid', 'left')
            ->join(
                '(SELECT fc_member_uid, AVG(rating) AS avg_rating FROM my_fc_counsel_review WHERE deleted_at IS NULL GROUP BY fc_member_uid) rs',
                'rs.fc_member_uid = r.fc_member_uid',
                'left',
                false
            )
            ->where('r.member_uid', $memberUid)
            ->where('r.deleted_at', null)
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $securityFiles = $db->table('my_fc_member_security')
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('security_id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/member/detail', [
            'm' => $member,
            'counselCount' => $counselCount,
            'reviewCount' => $reviewCount,
            'reviews' => $reviews,
            'securityFiles' => $securityFiles,
        ]);
    }

    public function counsels($id)
    {
        $member = $this->getMember((int) $id);
        $status = strtoupper((string) ($this->request->getGet('status') ?? 'ALL'));
        $selectedUid = (string) ($this->request->getGet('counsel_uid') ?? '');
        $allowedStatus = ['ALL', 'REQUEST', 'COMPLETE'];

        if (!in_array($status, $allowedStatus, true)) {
            $status = 'ALL';
        }

        $builder = $this->db->table('my_fc_counsel c')
            ->select("
                c.*,
                fc.name AS fc_name,
                fp.profile_image,
                fp.company,
                fp.company_sub,
                fpa.region,
                fpa.intro,
                COALESCE(rs.avg_rating, 0) AS avg_rating
            ")
            ->join('my_fc_member fc', 'fc.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile_activity fpa', 'fpa.member_uid = c.fc_member_uid', 'left')
            ->join(
                '(SELECT fc_member_uid, AVG(rating) AS avg_rating FROM my_fc_counsel_review WHERE deleted_at IS NULL GROUP BY fc_member_uid) rs',
                'rs.fc_member_uid = c.fc_member_uid',
                'left',
                false
            )
            ->where('c.member_uid', $member['member_uid'])
            ->where('c.deleted_at', null);

        if ($status !== 'ALL') {
            $builder->where('c.status', $status);
        }

        $counsels = $builder
            ->orderBy('c.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if ($selectedUid === '' && !empty($counsels)) {
            $selectedUid = $counsels[0]['counsel_uid'];
        }

        $selectedCounsel = null;
        foreach ($counsels as $counsel) {
            if ($counsel['counsel_uid'] === $selectedUid) {
                $selectedCounsel = $counsel;
                break;
            }
        }

        $files = [];
        if ($selectedCounsel) {
            $files = $this->db->table('my_fc_counsel_file')
                ->where('counsel_uid', $selectedCounsel['counsel_uid'])
                ->orderBy('file_id', 'ASC')
                ->get()
                ->getResultArray();
        }

        $statusCounts = $this->db->table('my_fc_counsel')
            ->select("
                COUNT(*) AS all_count,
                SUM(CASE WHEN status = 'REQUEST' THEN 1 ELSE 0 END) AS request_count,
                SUM(CASE WHEN status = 'COMPLETE' THEN 1 ELSE 0 END) AS complete_count
            ", false)
            ->where('member_uid', $member['member_uid'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        return view('admin/member/counsels', [
            'm' => $member,
            'counsels' => $counsels,
            'selectedCounsel' => $selectedCounsel,
            'files' => $files,
            'status' => $status,
            'statusCounts' => $statusCounts ?: [],
        ]);
    }

    public function reviews($id)
    {
        $member = $this->getMember((int) $id);

        $reviews = $this->db->table('my_fc_counsel_review r')
            ->select("
                r.*,
                c.created_at AS counsel_created_at,
                fc.name AS fc_name,
                fp.profile_image,
                fp.company,
                fpa.region
            ")
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member fc', 'fc.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile_activity fpa', 'fpa.member_uid = r.fc_member_uid', 'left')
            ->where('r.member_uid', $member['member_uid'])
            ->where('r.deleted_at', null)
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/member/reviews', [
            'm' => $member,
            'reviews' => $reviews,
        ]);
    }

    public function reviewDetail($id, $reviewId)
    {
        $member = $this->getMember((int) $id);

        $review = $this->db->table('my_fc_counsel_review r')
            ->select("
                r.*,
                c.created_at AS counsel_created_at,
                c.content AS counsel_content,
                fc.name AS fc_name,
                fp.company,
                fpa.region
            ")
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member fc', 'fc.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile_activity fpa', 'fpa.member_uid = r.fc_member_uid', 'left')
            ->where('r.review_id', (int) $reviewId)
            ->where('r.member_uid', $member['member_uid'])
            ->where('r.deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$review) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/member/review_detail', [
            'm' => $member,
            'review' => $review,
        ]);
    }

    public function edit($id)
    {
        $member = $this->getMember((int) $id);

        return view('admin/member/edit', [
            'm' => $member,
        ]);
    }

    public function create()
    {
        return view('admin/member/create');
    }

    public function store()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');
        $name = trim((string) $this->request->getPost('name'));
        $phone = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('phone'));
        $birth = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('birth'));
        $gender = $this->request->getPost('gender') ?: null;
        $status = (string) ($this->request->getPost('status') ?: 'ACTIVE');

        if ($email === '' || $password === '' || $name === '' || $phone === '') {
            return redirect()->back()->withInput()->with('error', '이메일, 비밀번호, 이름, 휴대폰 번호를 입력해주세요.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', '이메일 형식이 올바르지 않습니다.');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('error', '비밀번호 확인이 일치하지 않습니다.');
        }

        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return redirect()->back()->withInput()->with('error', '휴대폰 번호를 확인해주세요.');
        }

        if ($birth !== '' && !preg_match('/^\d{8}$/', $birth)) {
            return redirect()->back()->withInput()->with('error', '생년월일은 YYYYMMDD 형식으로 입력해주세요.');
        }

        if (!in_array($gender, ['M', 'F', null], true)) {
            return redirect()->back()->withInput()->with('error', '성별 값이 올바르지 않습니다.');
        }

        if (!in_array($status, ['WAIT', 'ACTIVE', 'BLOCK', 'LEAVE'], true)) {
            return redirect()->back()->withInput()->with('error', '회원상태 값이 올바르지 않습니다.');
        }

        $exists = $this->db->table('my_fc_member')
            ->groupStart()
            ->where('email', $email)
            ->orWhere('phone', $phone)
            ->groupEnd()
            ->where('deleted_at', null)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->back()->withInput()->with('error', '이미 사용 중인 이메일 또는 휴대폰 번호입니다.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('my_fc_member')->insert([
            'member_uid' => $this->generateMemberUid(),
            'member_type' => 'USER',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
            'phone_verified' => $this->request->getPost('phone_verified') === 'Y' ? 'Y' : 'N',
            'name' => $name,
            'birth' => $birth ?: null,
            'gender' => $gender,
            'nickname' => trim((string) $this->request->getPost('nickname')) ?: null,
            'status' => $status,
            'agree_age' => 1,
            'agree_terms' => 1,
            'agree_privacy' => 1,
            'agree_marketing' => $this->request->getPost('agree_marketing') ? 1 : 0,
            'join_ip' => $this->request->getIPAddress(),
            'admin_memo' => trim((string) $this->request->getPost('admin_memo')) ?: null,
            'fc_step' => 0,
            'created_at' => $now,
        ]);

        return redirect()
            ->to(base_url('admin/members/' . $this->db->insertID()))
            ->with('message', '개인회원이 등록되었습니다.');
    }

    public function update($id)
    {
        $member = $this->getMember((int) $id);

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'birth' => trim((string) $this->request->getPost('birth')) ?: null,
            'gender' => $this->request->getPost('gender') ?: null,
            'nickname' => trim((string) $this->request->getPost('nickname')) ?: null,
            'status' => $this->request->getPost('status') ?: $member['status'],
            'phone_verified' => $this->request->getPost('phone_verified') === 'Y' ? 'Y' : 'N',
            'agree_marketing' => $this->request->getPost('agree_marketing') ? 1 : 0,
            'admin_memo' => trim((string) $this->request->getPost('admin_memo')) ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($data['name'] === '' || $data['phone'] === '') {
            return redirect()->back()->withInput()->with('error', '이름과 휴대폰 번호를 입력해주세요.');
        }

        if (!in_array($data['status'], ['WAIT', 'ACTIVE', 'BLOCK', 'LEAVE'], true)) {
            return redirect()->back()->withInput()->with('error', '회원상태 값이 올바르지 않습니다.');
        }

        if (!in_array($data['gender'], ['M', 'F', null], true)) {
            return redirect()->back()->withInput()->with('error', '성별 값이 올바르지 않습니다.');
        }

        $duplicatePhone = $this->db->table('my_fc_member')
            ->where('phone', $data['phone'])
            ->where('member_id !=', $member['member_id'])
            ->where('deleted_at', null)
            ->countAllResults();

        if ($duplicatePhone > 0) {
            return redirect()->back()->withInput()->with('error', '이미 사용 중인 휴대폰 번호입니다.');
        }

        $this->db->table('my_fc_member')
            ->where('member_id', $member['member_id'])
            ->update($data);

        return redirect()
            ->to(base_url('admin/members/' . $member['member_id']))
            ->with('message', '회원정보가 수정되었습니다.');
    }


    // =========================
    // 상태 변경
    // =========================
    public function changeStatus()
    {
        $memberId = $this->request->getPost('member_id');
        $status = $this->request->getPost('status');

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    // =========================
    // soft delete
    // =========================
    public function delete()
    {
        $memberId = $this->request->getPost('member_id');

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    public function saveMemo()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $memo = trim((string) $this->request->getPost('admin_memo'));

        $this->getMember($memberId);

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'admin_memo' => $memo !== '' ? $memo : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    public function resetPassword()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $member = $this->getMember($memberId);
        $temporaryPassword = $this->makeTemporaryPassword();

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'password' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
                'password_reset_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $sent = $this->sendTemporaryPasswordMail($member, $temporaryPassword);

        return $this->response->setJSON([
            'status' => 'success',
            'mail_sent' => $sent,
            'temporary_password' => $sent ? null : $temporaryPassword,
        ]);
    }

    public function uploadFile($id)
    {
        $member = $this->getMember((int) $id);
        $files = $this->request->getFiles();

        if (empty($files['admin_files'])) {
            return redirect()->back()->with('error', '업로드할 파일을 선택해주세요.');
        }

        $uploaded = 0;
        foreach ($files['admin_files'] as $file) {
            if (!$file->isValid() || $file->hasMoved()) {
                continue;
            }

            $extension = strtolower($file->getClientExtension());
            if (!$this->isAllowedUploadExtension($extension)) {
                return redirect()->back()->with('error', '허용되지 않은 파일 형식이 포함되어 있습니다.');
            }

            $targetPath = WRITEPATH . 'uploads/security';
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            $savedName = $file->getRandomName();
            $file->move($targetPath, $savedName);

            $this->db->table('my_fc_member_security')->insert([
                'member_uid' => $member['member_uid'],
                'original_name' => $file->getClientName(),
                'saved_name' => $savedName,
                'file_path' => 'uploads/security/' . $savedName,
                'file_ext' => $extension,
                'file_size' => $file->getSize(),
                'sort_order' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $uploaded++;
        }

        if ($uploaded < 1) {
            return redirect()->back()->with('error', '업로드된 파일이 없습니다.');
        }

        return redirect()
            ->to(base_url('admin/members/' . $member['member_id']))
            ->with('message', '파일이 추가되었습니다.');
    }

    public function downloadFile($securityId)
    {
        $file = $this->getSecurityFile((int) $securityId);
        $path = WRITEPATH . $file['file_path'];

        if (!is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null)->setFileName($file['original_name']);
    }

    public function downloadFiles($id)
    {
        $member = $this->getMember((int) $id);
        $files = $this->db->table('my_fc_member_security')
            ->where('member_uid', $member['member_uid'])
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('security_id', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($files)) {
            return redirect()->back()->with('error', '다운로드할 파일이 없습니다.');
        }

        $zipPath = WRITEPATH . 'cache/member_' . $member['member_id'] . '_files_' . date('YmdHis') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', '압축 파일을 생성하지 못했습니다.');
        }

        $added = 0;
        foreach ($files as $index => $file) {
            $path = WRITEPATH . $file['file_path'];
            if (!is_file($path)) {
                continue;
            }

            $zip->addFile($path, sprintf('%02d_%s', $index + 1, $file['original_name']));
            $added++;
        }

        $zip->close();

        if ($added < 1) {
            @unlink($zipPath);
            return redirect()->back()->with('error', '다운로드할 실제 파일이 없습니다.');
        }

        register_shutdown_function(static function () use ($zipPath) {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        });

        return $this->response
            ->download($zipPath, null)
            ->setFileName('member_' . $member['member_id'] . '_files.zip');
    }

    public function deleteFile()
    {
        $securityId = (int) $this->request->getPost('security_id');
        $file = $this->getSecurityFile($securityId);
        $path = WRITEPATH . $file['file_path'];

        if (is_file($path)) {
            @unlink($path);
        }

        $this->db->table('my_fc_member_security')
            ->where('security_id', $securityId)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    public function downloadCounselFile($fileId)
    {
        $file = $this->getCounselFile((int) $fileId);
        $path = $this->resolveUploadPath($file['file_path']);

        if (!$path || !is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null)->setFileName($file['original_name']);
    }

    public function deleteCounselFile()
    {
        $fileId = (int) $this->request->getPost('file_id');
        $file = $this->getCounselFile($fileId);
        $path = $this->resolveUploadPath($file['file_path']);

        if ($path && is_file($path)) {
            @unlink($path);
        }

        $this->db->table('my_fc_counsel_file')
            ->where('file_id', $fileId)
            ->delete();

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    public function deleteReview()
    {
        $reviewId = (int) $this->request->getPost('review_id');

        $this->db->table('my_fc_counsel_review')
            ->where('review_id', $reviewId)
            ->where('deleted_at', null)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status' => 'success',
        ]);
    }

    private function getMember(int $id): array
    {
        $member = $this->db->table('my_fc_member')
            ->where('member_id', $id)
            ->where('member_type', 'USER')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $member;
    }

    private function generateMemberUid(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz';

        do {
            $uid = '';
            for ($i = 0; $i < 20; $i++) {
                $uid .= $chars[random_int(0, strlen($chars) - 1)];
            }

            $exists = $this->db->table('my_fc_member')
                ->where('member_uid', $uid)
                ->countAllResults();
        } while ($exists > 0);

        return $uid;
    }

    private function getSecurityFile(int $securityId): array
    {
        $file = $this->db->table('my_fc_member_security')
            ->where('security_id', $securityId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$file) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $file;
    }

    private function getCounselFile(int $fileId): array
    {
        $file = $this->db->table('my_fc_counsel_file')
            ->where('file_id', $fileId)
            ->get()
            ->getRowArray();

        if (!$file) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $file;
    }

    private function resolveUploadPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $writePath = WRITEPATH . ltrim($path, '/');
        if (is_file($writePath)) {
            return $writePath;
        }

        $publicPath = FCPATH . ltrim($path, '/');
        if (is_file($publicPath)) {
            return $publicPath;
        }

        return $writePath;
    }

    private function makeTemporaryPassword(): string
    {
        return 'MyFC!' . bin2hex(random_bytes(4));
    }

    private function sendTemporaryPasswordMail(array $member, string $temporaryPassword): bool
    {
        $config = config('Email');
        if (empty($config->fromEmail)) {
            return false;
        }

        $email = \Config\Services::email();
        $email->setFrom($config->fromEmail, $config->fromName ?: 'MyFC');
        $email->setTo($member['email']);
        $email->setSubject('[MyFC] 임시 비밀번호 안내');
        $email->setMessage(
            "안녕하세요.\n\n관리자 요청으로 임시 비밀번호가 발급되었습니다.\n"
            . "임시 비밀번호: {$temporaryPassword}\n\n로그인 후 비밀번호를 변경해주세요."
        );

        return $email->send(false);
    }

    private function csvLine(array $columns): string
    {
        return implode(',', array_map(static function ($value) {
            return '"' . str_replace('"', '""', (string) $value) . '"';
        }, $columns)) . "\n";
    }

    private function isAllowedUploadExtension(string $extension): bool
    {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'hwp', 'txt', 'zip'];
        $blocked = ['php', 'phtml', 'html', 'js', 'sh', 'exe', 'bat'];

        return $extension !== '' && in_array($extension, $allowed, true) && !in_array($extension, $blocked, true);
    }
}
