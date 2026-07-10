<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FcMember extends BaseController
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
        $sort = $request->getGet('sort') ?? 'recent_join';
        $allowedSort = ['recent_join', 'recent_login', 'view_count', 'counsel_count'];

        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'recent_join';
        }

        // =========================
        // search filters
        // =========================
        $startDate = $request->getGet('start_date') ?: '';
        $endDate   = $request->getGet('end_date') ?: '';
        $keyword = trim((string) ($request->getGet('q') ?? ''));
        $error = '';

        if ($keyword !== '' && mb_strlen($keyword) < 2) {
            $error = '검색어 입력은 최소 2자 이상 입력하셔야 됩니다.';
            $keyword = '';
        }

        // =========================
        // base builder
        // =========================
        $baseBuilder = $db->table('my_fc_member m')
            ->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'left')
            ->join('my_fc_reviewed rv', 'rv.member_uid = m.member_uid', 'left')
            ->join(
                '(SELECT fc_member_uid, COUNT(*) AS counsel_count FROM my_fc_counsel WHERE deleted_at IS NULL GROUP BY fc_member_uid) cc',
                'cc.fc_member_uid = m.member_uid',
                'left',
                false
            )
            ->where('m.deleted_at', null)
            ->where('m.member_type', 'FC');

        // =========================
        // date filter
        // =========================
        if (!empty($startDate)) {
            $baseBuilder->where('m.created_at >=', $startDate . ' 00:00:00');
        }

        if (!empty($endDate)) {
            $baseBuilder->where('m.created_at <=', $endDate . ' 23:59:59');
        }

        // =========================
        // keyword filters
        // =========================
        if ($keyword !== '') {
            $baseBuilder->groupStart()
                ->like('m.name', $keyword)
                ->orLike('m.email', $keyword)
                ->orLike('m.phone', $keyword)
                ->groupEnd();
        }

        // =========================
        // total count (clone 중요)
        // =========================
        $total = (clone $baseBuilder)->countAllResults();

        // =========================
        // list query
        // =========================
        $listBuilder = (clone $baseBuilder)
            ->select("
                m.member_id,
                m.member_uid,
                m.email,
                m.phone,
                m.name,
                m.status,
                m.fc_step,
                m.fc_review_status,
                m.created_at,
                m.last_login_at,
                p.license_no,
                p.company,
                COALESCE(p.view_count, 0) AS view_count,
                COALESCE(cc.counsel_count, 0) AS counsel_count,
                rv.status AS reviewed_status
            ");

        if ($sort === 'recent_login') {
            $listBuilder->orderBy('m.last_login_at', 'DESC')->orderBy('m.member_id', 'DESC');
        } elseif ($sort === 'view_count') {
            $listBuilder->orderBy('view_count', 'DESC')->orderBy('m.member_id', 'DESC');
        } elseif ($sort === 'counsel_count') {
            $listBuilder->orderBy('counsel_count', 'DESC')->orderBy('m.member_id', 'DESC');
        } else {
            $listBuilder->orderBy('m.created_at', 'DESC');
        }

        $members = $listBuilder
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // =========================
        // pager (CI4 standard)
        // =========================
        $pager = \Config\Services::pager();
        return view('admin/fc_member/index', [
            'members' => $members,
            'total'   => $total,
            'page'    => $page,
            'pager'    => $pager,
            'perPage' => $perPage,
            'sort'    => $sort,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'keyword' => $keyword,
            'error' => $error,
        ]);
    }

    public function export()
    {
        $db = \Config\Database::connect();
        $request = $this->request;

        $startDate = $request->getGet('start_date') ?: '';
        $endDate = $request->getGet('end_date') ?: '';
        $keyword = trim((string) ($request->getGet('q') ?? ''));

        $builder = $db->table('my_fc_member m')
            ->select("
                m.name,
                m.email,
                m.phone,
                p.license_no,
                p.company,
                COALESCE(p.view_count, 0) AS view_count,
                m.fc_review_status,
                rv.status AS reviewed_status,
                m.created_at
            ")
            ->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'left')
            ->join('my_fc_reviewed rv', 'rv.member_uid = m.member_uid', 'left')
            ->where('m.deleted_at', null)
            ->where('m.member_type', 'FC');

        if ($startDate !== '') {
            $builder->where('m.created_at >=', $startDate . ' 00:00:00');
        }

        if ($endDate !== '') {
            $builder->where('m.created_at <=', $endDate . ' 23:59:59');
        }

        if ($keyword !== '' && mb_strlen($keyword) >= 2) {
            $builder->groupStart()
                ->like('m.name', $keyword)
                ->orLike('m.email', $keyword)
                ->orLike('m.phone', $keyword)
                ->groupEnd();
        }

        $rows = $builder->orderBy('m.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $csv = "\xEF\xBB\xBF";
        $csv .= "이름,이메일주소,휴대폰번호,보험모집종사자 등록번호,소속 보험사,조회수,상태값,가입일시\n";

        foreach ($rows as $row) {
            $status = $this->fcListStatusLabel($row);
            $csv .= $this->csvLine([
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['phone'] ?? '',
                $row['license_no'] ?? '',
                $row['company'] ?? '',
                (string) ((int) ($row['view_count'] ?? 0)),
                $status,
                !empty($row['created_at']) ? date('Ymd H:i:s', strtotime($row['created_at'])) : '',
            ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="fc_members_' . date('YmdHis') . '.csv"')
            ->setBody($csv);
    }

    // =========================
    // 회원 상세
    // =========================
    public function detail($id)
    {
        helper(['region', 'insurance']);
        $db = \Config\Database::connect();

        // 회원
        $member = $db->table('my_fc_member')
            ->where('member_id', $id)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $memberUid = $member['member_uid'];

        // 프로필
        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동자료
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order','ASC')
            ->get()
            ->getResultArray();

        // 스토리
        $story = $db->table('my_fc_profile_story')
            ->where('member_uid',$memberUid)
            ->get()
            ->getRowArray();

        // 스토리 이미지
        $storyImages = $db->table('my_fc_profile_story_image')
            ->where('member_uid',$memberUid)
            ->orderBy('sort_order','ASC')
            ->get()
            ->getResultArray();

        // 심의필
        $review = $db->table('my_fc_reviewed')
            ->where('member_uid',$memberUid)
            ->get()
            ->getRowArray();

        $counselCount = $db->table('my_fc_counsel')
            ->where('fc_member_uid', $memberUid)
            ->where('deleted_at', null)
            ->countAllResults();

        $reviewCount = $db->table('my_fc_counsel_review')
            ->where('fc_member_uid', $memberUid)
            ->where('deleted_at', null)
            ->countAllResults();

        return view('admin/fc_member/detail',[
            'm'             => $member,
            'profile'       => $profile,
            'activity'      => $activity,
            'activityItems' => $activityItems,
            'story'         => $story,
            'storyImages'   => $storyImages,
            'review'        => $review,
            'counselCount'  => $counselCount,
            'reviewCount'   => $reviewCount,
        ]);
    }

    public function preview($id)
    {
        $member = $this->db->table('my_fc_member')
            ->select('member_uid')
            ->where('member_id', (int) $id)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return redirect()->to(base_url('fc/view') . '?uid=' . rawurlencode($member['member_uid']));
    }

    public function edit($id)
    {
        helper(['region', 'insurance']);

        $data = $this->getFcEditData((int) $id);

        return view('admin/fc_member/edit', $data);
    }

    public function create()
    {
        return view('admin/fc_member/create');
    }

    public function store()
    {
        helper('fileupload_helper');

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');
        $name = trim((string) $this->request->getPost('name'));
        $phone = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('phone'));
        $status = (string) ($this->request->getPost('status') ?: 'ACTIVE');
        $reviewStatus = (string) ($this->request->getPost('fc_review_status') ?: 'WAIT');

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

        if (!in_array($status, ['WAIT', 'ACTIVE', 'BLOCK', 'LEAVE'], true)) {
            return redirect()->back()->withInput()->with('error', '회원상태 값이 올바르지 않습니다.');
        }

        if (!in_array($reviewStatus, ['WAIT', 'APPROVE', 'REJECT'], true)) {
            return redirect()->back()->withInput()->with('error', '심사상태 값이 올바르지 않습니다.');
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
        $memberUid = $this->generateMemberUid();

        $this->db->transStart();

        $this->db->table('my_fc_member')->insert([
            'member_uid' => $memberUid,
            'member_type' => 'FC',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
            'phone_verified' => $this->request->getPost('phone_verified') === 'Y' ? 'Y' : 'N',
            'name' => $name,
            'status' => $status,
            'agree_age' => 1,
            'agree_terms' => 1,
            'agree_privacy' => 1,
            'agree_marketing' => $this->request->getPost('agree_marketing') ? 1 : 0,
            'join_ip' => $this->request->getIPAddress(),
            'admin_memo' => trim((string) $this->request->getPost('admin_memo')) ?: null,
            'fc_step' => 4,
            'fc_review_status' => $reviewStatus,
            'created_at' => $now,
        ]);

        $memberId = $this->db->insertID();

        $profileData = [
            'member_uid' => $memberUid,
            'company' => trim((string) $this->request->getPost('company')) ?: null,
            'company_sub' => trim((string) $this->request->getPost('company_sub')) ?: null,
            'ga' => trim((string) $this->request->getPost('ga')) ?: null,
            'position' => trim((string) $this->request->getPost('position')) ?: null,
            'license_date' => trim((string) $this->request->getPost('license_date')) ?: null,
            'license_no' => preg_replace('/[^0-9]/', '', (string) $this->request->getPost('license_no')) ?: null,
            'time_from' => $this->request->getPost('time_from') !== '' ? (int) $this->request->getPost('time_from') : null,
            'time_to' => $this->request->getPost('time_to') !== '' ? (int) $this->request->getPost('time_to') : null,
            'language' => trim((string) $this->request->getPost('language')) ?: null,
            'step' => 2,
            'created_at' => $now,
        ];

        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid() && ! $profileImage->hasMoved()) {
            $profileData['profile_image'] = upload_file($profileImage, 'uploads/profile');
        }

        $this->db->table('my_fc_profile')->insert($profileData);

        $this->db->table('my_fc_profile_activity')->insert([
            'member_uid' => $memberUid,
            'region' => trim((string) $this->request->getPost('region')) ?: null,
            'insurance_types' => trim((string) $this->request->getPost('insurance_types')) ?: null,
            'hero_line' => trim((string) $this->request->getPost('hero_line')) ?: null,
            'intro' => trim((string) $this->request->getPost('intro')) ?: null,
            'career' => trim((string) $this->request->getPost('career')) ?: null,
            'step' => 3,
            'created_at' => $now,
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'FC 회원 등록에 실패했습니다.');
        }

        return redirect()
            ->to(base_url('admin/fc-members/' . (int) $memberId))
            ->with('success', 'FC 회원이 등록되었습니다.');
    }

    public function update($id)
    {
        helper('fileupload_helper');

        $data = $this->getFcEditData((int) $id);
        $member = $data['m'];
        $memberUid = $member['member_uid'];
        $now = date('Y-m-d H:i:s');

        $name = trim((string) $this->request->getPost('name'));
        $phone = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('phone'));
        $status = (string) $this->request->getPost('status');
        $phoneVerified = $this->request->getPost('phone_verified') === 'Y' ? 'Y' : 'N';
        $reviewStatus = (string) $this->request->getPost('fc_review_status');

        if ($name === '' || $phone === '') {
            return redirect()->back()->withInput()->with('error', '이름과 휴대폰 번호를 입력해주세요.');
        }

        if (!in_array($status, ['WAIT', 'ACTIVE', 'BLOCK', 'LEAVE'], true)) {
            return redirect()->back()->withInput()->with('error', '회원상태 값이 올바르지 않습니다.');
        }

        if (!in_array($reviewStatus, ['WAIT', 'APPROVE', 'REJECT'], true)) {
            return redirect()->back()->withInput()->with('error', '심사상태 값이 올바르지 않습니다.');
        }

        $duplicatePhone = $this->db->table('my_fc_member')
            ->where('phone', $phone)
            ->where('member_id !=', $member['member_id'])
            ->where('deleted_at', null)
            ->countAllResults();

        if ($duplicatePhone > 0) {
            return redirect()->back()->withInput()->with('error', '이미 사용 중인 휴대폰 번호입니다.');
        }

        $this->db->transStart();

        $this->db->table('my_fc_member')
            ->where('member_id', $member['member_id'])
            ->where('member_type', 'FC')
            ->update([
                'name' => $name,
                'phone' => $phone,
                'status' => $status,
                'phone_verified' => $phoneVerified,
                'agree_marketing' => $this->request->getPost('agree_marketing') ? 1 : 0,
                'fc_review_status' => $reviewStatus,
                'admin_memo' => trim((string) $this->request->getPost('admin_memo')) ?: null,
                'updated_at' => $now,
            ]);

        $profileData = [
            'member_uid' => $memberUid,
            'company' => trim((string) $this->request->getPost('company')) ?: null,
            'company_sub' => trim((string) $this->request->getPost('company_sub')) ?: null,
            'ga' => trim((string) $this->request->getPost('ga')) ?: null,
            'position' => trim((string) $this->request->getPost('position')) ?: null,
            'license_date' => trim((string) $this->request->getPost('license_date')) ?: null,
            'license_no' => preg_replace('/[^0-9]/', '', (string) $this->request->getPost('license_no')) ?: null,
            'time_from' => $this->request->getPost('time_from') !== '' ? (int) $this->request->getPost('time_from') : null,
            'time_to' => $this->request->getPost('time_to') !== '' ? (int) $this->request->getPost('time_to') : null,
            'language' => trim((string) $this->request->getPost('language')) ?: null,
            'updated_at' => $now,
        ];

        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid() && ! $profileImage->hasMoved()) {
            $profileData['profile_image'] = upload_file($profileImage, 'uploads/profile');
        }

        $profile = $data['profile'];
        if ($profile) {
            $this->db->table('my_fc_profile')
                ->where('member_uid', $memberUid)
                ->update($profileData);
        } else {
            $profileData['created_at'] = $now;
            $profileData['step'] = 2;
            $this->db->table('my_fc_profile')->insert($profileData);
        }

        $activityData = [
            'member_uid' => $memberUid,
            'region' => trim((string) $this->request->getPost('region')) ?: null,
            'insurance_types' => trim((string) $this->request->getPost('insurance_types')) ?: null,
            'hero_line' => trim((string) $this->request->getPost('hero_line')) ?: null,
            'intro' => trim((string) $this->request->getPost('intro')) ?: null,
            'career' => trim((string) $this->request->getPost('career')) ?: null,
            'updated_at' => $now,
        ];

        if ($data['activity']) {
            $this->db->table('my_fc_profile_activity')
                ->where('member_uid', $memberUid)
                ->update($activityData);
        } else {
            $activityData['created_at'] = $now;
            $this->db->table('my_fc_profile_activity')->insert($activityData);
        }

        $activityItems = $this->request->getPost('activity_items');
        if (is_array($activityItems)) {
            foreach ($activityItems as $index => $item) {
                $itemId = (int) ($item['item_id'] ?? 0);
                $title = trim((string) ($item['title'] ?? ''));
                $type = (string) ($item['type'] ?? 'text');

                if (!in_array($type, ['file', 'link', 'text'], true)) {
                    $type = 'text';
                }

                if ($itemId > 0 && $title === '') {
                    $this->deleteActivityItem($memberUid, $itemId);
                    continue;
                }

                if ($title === '') {
                    continue;
                }

                $itemData = [
                    'member_uid' => $memberUid,
                    'category' => 'activity',
                    'type' => $type,
                    'title' => $title,
                    'content' => trim((string) ($item['content'] ?? '')) ?: null,
                    'url' => trim((string) ($item['url'] ?? '')) ?: null,
                    'sort_order' => (int) ($item['sort_order'] ?? $index),
                    'is_visible' => !empty($item['is_visible']) ? 1 : 0,
                ];

                if ($type === 'link' && empty($itemData['url'])) {
                    $itemData['url'] = $itemData['content'];
                    $itemData['content'] = null;
                }

                $file = $this->request->getFile("activity_items.$index.file");
                if ($file && $file->isValid() && ! $file->hasMoved()) {
                    $itemData['file_path'] = upload_file($file, 'uploads/activity');
                }

                if ($itemId > 0) {
                    $this->db->table('my_fc_profile_activity_item')
                        ->where('item_id', $itemId)
                        ->where('member_uid', $memberUid)
                        ->update($itemData);
                } else {
                    $itemData['created_at'] = $now;
                    $this->db->table('my_fc_profile_activity_item')->insert($itemData);
                }
            }
        }

        $story = $data['story'];
        $storyData = [];
        $storyVideo = $this->request->getFile('story_video');
        $storyImage = $this->request->getFile('story_image');

        if ($storyVideo && $storyVideo->isValid() && ! $storyVideo->hasMoved()) {
            $storyData['story_video'] = upload_file($storyVideo, 'uploads/story/video');
        }

        if ($storyImage && $storyImage->isValid() && ! $storyImage->hasMoved()) {
            $storyData['story_image'] = upload_file($storyImage, 'uploads/story/main');
        }

        if ($story) {
            if ($storyData) {
                $storyData['updated_at'] = $now;
                $this->db->table('my_fc_profile_story')
                    ->where('member_uid', $memberUid)
                    ->update($storyData);
            }
        } else {
            $storyData['member_uid'] = $memberUid;
            $storyData['created_at'] = $now;
            $this->db->table('my_fc_profile_story')->insert($storyData);
        }

        $keepStoryImages = $this->request->getPost('keep_story_images');
        if (!is_array($keepStoryImages)) {
            $keepStoryImages = [];
        }

        $keepStoryImages = array_map('intval', $keepStoryImages);
        $storyImages = $data['storyImages'];

        foreach ($storyImages as $image) {
            if (!in_array((int) $image['id'], $keepStoryImages, true)) {
                $this->db->table('my_fc_profile_story_image')
                    ->where('id', $image['id'])
                    ->where('member_uid', $memberUid)
                    ->delete();
                $this->deleteStoredStoryFile('uploads/story/images', (string) $image['image_path']);
            }
        }

        foreach ($keepStoryImages as $sort => $imageId) {
            $this->db->table('my_fc_profile_story_image')
                ->where('id', $imageId)
                ->where('member_uid', $memberUid)
                ->update(['sort_order' => $sort]);
        }

        $storyImageFiles = $this->request->getFiles()['story_images'] ?? [];
        if (!is_array($storyImageFiles)) {
            $storyImageFiles = [$storyImageFiles];
        }

        $sort = count($keepStoryImages);
        foreach ($storyImageFiles as $file) {
            if (! $file || ! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            $this->db->table('my_fc_profile_story_image')->insert([
                'member_uid' => $memberUid,
                'image_path' => upload_file($file, 'uploads/story/images'),
                'sort_order' => $sort++,
                'created_at' => $now,
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'FC 회원정보 저장에 실패했습니다.');
        }

        return redirect()
            ->to(base_url('admin/fc-members/' . (int) $member['member_id']))
            ->with('success', 'FC 회원정보가 수정되었습니다.');
    }

    public function saveMemo()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $memo = trim((string) $this->request->getPost('admin_memo'));

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->update([
                'admin_memo' => $memo !== '' ? $memo : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function resetPassword()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $member = $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            return $this->response->setJSON(['status' => 'fail']);
        }

        $temporaryPassword = 'MyFC!' . bin2hex(random_bytes(4));

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'password' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
                'password_reset_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'temporary_password' => $temporaryPassword,
        ]);
    }

    public function changeStatus()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $status = (string) $this->request->getPost('status');

        if (!in_array($status, ['ACTIVE', 'BLOCK', 'LEAVE'], true)) {
            return $this->response->setJSON(['status' => 'fail']);
        }

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function delete()
    {
        $memberId = (int) $this->request->getPost('member_id');

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->update([
                'status' => 'LEAVE',
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteStoryFile()
    {
        $memberId = (int) $this->request->getPost('member_id');
        $type = (string) $this->request->getPost('type');
        $imageId = (int) $this->request->getPost('image_id');

        $member = $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member || !in_array($type, ['video', 'main_image', 'image'], true)) {
            return $this->response->setJSON(['status' => 'fail']);
        }

        $memberUid = $member['member_uid'];
        $fileName = '';
        $directory = '';

        if ($type === 'image') {
            $image = $this->db->table('my_fc_profile_story_image')
                ->where('id', $imageId)
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

            if (!$image) {
                return $this->response->setJSON(['status' => 'fail']);
            }

            $fileName = (string) $image['image_path'];
            $directory = 'uploads/story/images';

            $this->db->table('my_fc_profile_story_image')
                ->where('id', $imageId)
                ->where('member_uid', $memberUid)
                ->delete();
        } else {
            $story = $this->db->table('my_fc_profile_story')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

            if (!$story) {
                return $this->response->setJSON(['status' => 'fail']);
            }

            $column = $type === 'video' ? 'story_video' : 'story_image';
            $fileName = (string) ($story[$column] ?? '');
            $directory = $type === 'video' ? 'uploads/story/video' : 'uploads/story/main';

            $this->db->table('my_fc_profile_story')
                ->where('member_uid', $memberUid)
                ->update([
                    $column => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        $this->deleteStoredStoryFile($directory, $fileName);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function reviewApprove()
    {
        $db = \Config\Database::connect();

        $memberUid = $this->request->getPost('member_uid');

        if (!$memberUid) {
            return redirect()->back()->with('error', '잘못된 요청입니다.');
        }

        $db->transStart();

        /* =========================
        * 1. 리뷰 테이블 승인 처리
        ========================= */
        $db->table('my_fc_reviewed')
            ->where('member_uid', $memberUid)
            ->update([
                'status' => 'APPROVE',
                'approve_at' => date('Y-m-d H:i:s'),
                'approve_admin_uid' => 'admin' // TODO: session admin id로 변경
            ]);

        /* =========================
        * 2. 회원 테이블 승인 상태 업데이트
        ========================= */
        $db->table('my_fc_member')
            ->where('member_uid', $memberUid)
            ->update([
                'fc_review_status' => 'APPROVE',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', '승인 처리 실패');
        }

        return redirect()->back()->with('success', '승인 처리 완료');
    }

    public function reviewReject()
    {
        $db = \Config\Database::connect();

        $memberUid = $this->request->getPost('member_uid');
        $reason    = $this->request->getPost('reject_reason');

        $db->table('my_fc_reviewed')
            ->where('member_uid', $memberUid)
            ->update([
                'status' => 'REJECT',
                'reject_reason' => $reason,
                'approve_at' => date('Y-m-d H:i:s')
            ]);

        return redirect()->back()->with('success', '반려 처리 완료');
    }

    private function fcListStatusLabel(array $member): string
    {
        $reviewedStatus = $member['reviewed_status'] ?? '';
        $reviewStatus = $member['fc_review_status'] ?? '';

        if ($reviewedStatus === '' || $reviewedStatus === null) {
            return '회원가입 완료';
        }

        if ($reviewStatus === 'APPROVE' || $reviewedStatus === 'APPROVE') {
            return '심의필 승인 완료';
        }

        if ($reviewStatus === 'REJECT' || $reviewedStatus === 'REJECT') {
            return '심의필 거부';
        }

        return '심의필 승인 요청';
    }

    private function csvLine(array $columns): string
    {
        return implode(',', array_map(static function ($value) {
            return '"' . str_replace('"', '""', (string) $value) . '"';
        }, $columns)) . "\n";
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

    private function getFcEditData(int $id): array
    {
        $member = $this->db->table('my_fc_member')
            ->where('member_id', $id)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $memberUid = $member['member_uid'];

        return [
            'm' => $member,
            'profile' => $this->db->table('my_fc_profile')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray(),
            'activity' => $this->db->table('my_fc_profile_activity')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray(),
            'activityItems' => $this->db->table('my_fc_profile_activity_item')
                ->where('member_uid', $memberUid)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('item_id', 'ASC')
                ->get()
                ->getResultArray(),
            'story' => $this->db->table('my_fc_profile_story')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray(),
            'storyImages' => $this->db->table('my_fc_profile_story_image')
                ->where('member_uid', $memberUid)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
        ];
    }

    private function deleteActivityItem(string $memberUid, int $itemId): void
    {
        $item = $this->db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->where('item_id', $itemId)
            ->get()
            ->getRowArray();

        if (!$item) {
            return;
        }

        if (!empty($item['file_path'])) {
            $this->deleteStoredStoryFile('uploads/activity', (string) $item['file_path']);
        }

        $this->db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->where('item_id', $itemId)
            ->delete();
    }

    private function deleteStoredStoryFile(string $directory, string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        $safeName = basename($fileName);
        $relativeDirectory = trim($directory, '/');

        foreach ([WRITEPATH, ROOTPATH . 'public/'] as $basePath) {
            $path = rtrim($basePath, '/') . '/' . $relativeDirectory . '/' . $safeName;

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }


}
