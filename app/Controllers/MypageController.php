<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class MypageController extends BaseController
{
    public function index(): string
    {
        //return pageView('welcome_message');
        $header_class = "search-page results";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('mypage/index', $data);
    }

    public function info(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [];


        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => [],
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
        ];
        $data['mode'] = 'edit';


        return $this->renderView('mypage/info', $data);
    }

    public function withdrawal(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page flow-result";
        $popup_page = [];

        $modal_page = [];
        $session = session();

        $memberUid = $session->get('member_uid');

        $memberModel = new \App\Models\MemberModel();

        // =========================
        // 1. 회원 정보 조회
        // =========================
        $member = $memberModel
            ->where('member_uid', $memberUid)
            ->first();

        // =========================
        // 2. 상담 건수 조회 (예시 테이블)
        // =========================
        $db = \Config\Database::connect();

        $consultCount = $db->table('my_fc_counsel') // ← 실제 테이블명 맞게 수정
            ->where('member_uid', $memberUid)
            ->countAllResults();



        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            // 👉 추가 데이터
            "email" => $member['email'] ?? '',
            "consult_count" => $consultCount
        ];


        return $this->renderView('mypage/withdrawal', $data);
    }

    public function withdrawalLast(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('mypage/withdrawalLast', $data);
    }

    public function certificate()
    {
        $session = session();

        // =========================
        // 로그인 체크
        // =========================
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $memberUid = $session->get('member_uid');

        // =========================
        // 증권파일 조회
        // =========================
        $securityModel = new \App\Models\MemberSecurityModel();

        $securityList = $securityModel
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('security_id', 'DESC')
            ->findAll();

        // =========================
        // View Data
        // =========================
        $header_class = "form-page form-page securities-page";

        $popup_page = [];

        $modal_page = [];

        $data = [
            "header_class" => $header_class,
            "popup_page"   => $popup_page,
            "modal_page"   => $modal_page,
            "securityList" => $securityList,
            "member_uid"   => $memberUid,
        ];

        return $this->renderView('mypage/certificate', $data);
    }

    public function favoriteFc(): string
    {
        helper(['region', 'insurance']);
        $header_class = "form-page favorite-page";

        $session = session();
        $memberUid = $session->get('member_uid');

        $popup_page = [];
        $modal_page = [];

        $db = \Config\Database::connect();

        $favoriteList = [];

        if ($memberUid) {

            $favoriteList = $db->table('fc_bookmarks b')
                ->select('
                    b.id,
                    b.fc_member_uid,
                    b.created_at,

                    m.name,
                    m.member_uid,

                    p.profile_image,
                    p.company,
                    p.company_sub,
                    p.ga,
                    p.view_count,

                    a.region,
                    a.insurance_types,

                    IFNULL(r.avg_rating, 0) AS rating,
                    IFNULL(r.review_count, 0) AS review_count
                ')
                ->join('my_fc_member m', 'm.member_uid = b.fc_member_uid', 'left')
                ->join('my_fc_profile p', 'p.member_uid = b.fc_member_uid', 'left')
                ->join('my_fc_profile_activity a', 'a.member_uid = b.fc_member_uid', 'left')

                ->join('(
                    SELECT 
                        c.fc_member_uid,
                        AVG(r.rating) AS avg_rating,
                        COUNT(r.review_id) AS review_count
                    FROM my_fc_counsel_review r
                    JOIN my_fc_counsel c ON c.counsel_uid = r.counsel_uid
                    GROUP BY c.fc_member_uid
                ) r', 'r.fc_member_uid = b.fc_member_uid', 'left')

                ->where('b.member_uid', $memberUid)
                ->orderBy('b.created_at', 'DESC')
                ->get()
                ->getResultArray();
        }

        // =========================
        // 2. 데이터 가공 (뷰용 정리)
        // =========================
        foreach ($favoriteList as &$row) {

            // 회사 라인 (GA > 보험사)
            $companyLine = [];

            if (!empty($row['ga'])) {
                $companyLine[] = $row['ga'];
            } else {
                if (!empty($row['company'])) $companyLine[] = $row['company'];
                if (!empty($row['company_sub'])) $companyLine[] = $row['company_sub'];
            }

            $row['company_line'] = array_slice($companyLine, 0, 2);

            // 프로필 이미지
            $row['profile_image'] = !empty($row['profile_image'])
                ? '/uploads/profile/' . $row['profile_image']
                : SITE_IMG_URL . 'images/temp/@profile-m.png';

            // 지역
            $row['region_label'] = '';
            if (!empty($row['region'])) {
                $regions = array_map('fc_region_label', array_map('trim', explode(',', $row['region'])));
                $row['region_label'] = $regions[0] ?? '';
            }

            // 보험
            $row['insurance_labels'] = [];
            if (!empty($row['insurance_types'])) {
                $row['insurance_labels'] = array_slice(
                    array_map('fc_insurance_label', array_map('trim', explode(',', $row['insurance_types']))),
                    0,
                    3
                );
            }
        }

        // =========================
        // 3. Swiper 4개 chunk
        // =========================
        $favoriteChunks = array_chunk($favoriteList, 4);

        $data = [
            "header_class"  => $header_class,
            "popup_page"    => $popup_page,
            "modal_page"    => $modal_page,

            // 🔥 추가
            "favoriteList"  => $favoriteList,
            "favoriteChunks"  => $favoriteChunks,
            "favoriteCount"  => count($favoriteList),
        ];

        return $this->renderView('mypage/favoriteFc', $data);
    }

    public function counselList(): string
    {
        // ===========================
        // 로그인 체크
        // ===========================
        helper(['region', 'insurance']);
        $member_uid = session()->get('member_uid');

        if (empty($member_uid)) {
            return redirect()->to('/member/login');
        }

        // ===========================
        // 상담 목록 조회
        // ===========================
        $db = \Config\Database::connect();

        $status = $this->request->getGet('status');

        $status = $this->request->getGet('status');

        $query = $db->table('my_fc_counsel c')
            ->select("
        c.*,

        m.name AS fc_name,

        p.profile_image,
        p.ga,
        p.company,
        p.company_sub,

        a.region,
        a.insurance_types,

        r.avg_rating,
        r.review_count,
        r.review_id
    ")
            ->join('my_fc_member m', 'm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = c.fc_member_uid', 'left')

            // ===========================
            // 리뷰 집계 서브쿼리
            // ===========================
            ->join("
        (
            SELECT
                counsel_uid,
                AVG(rating) AS avg_rating,
                COUNT(*) AS review_count,
                MAX(review_id) AS review_id
            FROM my_fc_counsel_review
            WHERE deleted_at IS NULL
            GROUP BY counsel_uid
        ) r
    ", 'r.counsel_uid = c.counsel_uid', 'left')

            ->where('c.member_uid', $member_uid)
            ->where('c.deleted_at IS NULL', null, false);

        if (!empty($status)) {
            $query->where('c.status', $status);
        }

        $counselList = $query
            ->orderBy('c.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $header_class = "consult-status-page";

        $data = [
            "header_class" => $header_class,
            "popup_page"   => [],
            "modal_page"   => [],
            "counselList"  => $counselList,
        ];

        return $this->renderView('mypage/counselList', $data);
    }

    public function reviewWrite(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWrite', $data);
    }

    public function reviewWriteLast(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWriteLast', $data);
    }

    public function reviewList(): string
    {
        $member_uid = session()->get('member_uid');

        if (empty($member_uid)) {
            return redirect()->to('/member/login');
        }

        $db = \Config\Database::connect();

        // ===========================
        // 내 리뷰 리스트 + FC 정보 JOIN
        // ===========================
        $reviewList = $db->table('my_fc_counsel_review r')
            ->select("
            r.*,

            c.counsel_uid,
            c.status AS counsel_status,
            c.created_at AS counsel_created_at,

            m.name AS fc_name,

            p.profile_image,
            p.ga,
            p.company,
            p.company_sub,

            a.region,
            a.insurance_types
        ")
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member m', 'm.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = r.fc_member_uid', 'left')
            ->where('r.member_uid', $member_uid)
            ->where('r.deleted_at IS NULL', null, false)
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // ===========================
        // view data
        // ===========================
        $header_class = "consult-status-page detail-page";

        $data = [
            "header_class" => $header_class,
            "popup_page"   => [],
            "modal_page"   => ["hugi_modal.php"],
            "reviewList"   => $reviewList
        ];

        return $this->renderView('mypage/reviewList', $data);
    }

    public function reviewDetailAjax($reviewId)
    {
        $member_uid = session()->get('member_uid');

        if (empty($member_uid)) {
            return $this->response->setJSON([
                'result' => 'fail',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $row = $db->table('my_fc_counsel_review r')
            ->select('
            r.*,
            m.name AS fc_name,
            p.profile_image
        ')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member m', 'm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = c.fc_member_uid', 'left')
            ->where('r.review_id', $reviewId)
            ->where('r.member_uid', $member_uid)
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->response->setJSON([
                'result' => 'fail',
                'message' => '데이터 없음'
            ]);
        }

        return $this->response->setJSON([
            'result' => 'success',
            'data' => $row
        ]);
    }


    public function fcinfo(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => [],
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcinfo', $data);
    }

    public function updateProfileImage()
    {
        $this->response->setContentType('application/json');

        helper('fileupload_helper');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $file = $this->request->getFile('profile_image');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '파일이 없습니다.'
            ]);
        }

        // =========================
        // 1. 업로드 (writable 기준)
        // =========================
        $fileName = upload_file($file, 'uploads/profile');

        if (!$fileName) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '업로드 실패'
            ]);
        }

        $profileTable = $db->table('my_fc_profile');

        $profile = $profileTable
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // =========================
        // 2. DB 저장용 값 (파일명만)
        // =========================
        $saveFile = $fileName;

        // =========================
        // 3. 기존 파일 삭제 (public 기준)
        // =========================
        if ($profile && !empty($profile['profile_image'])) {

            $oldPath = WRITEPATH . 'uploads/profile/' . $profile['profile_image'];

            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // =========================
        // 4. UPSERT
        // =========================
        if ($profile) {

            $profileTable->where('member_uid', $memberUid)
                ->update([
                    'profile_image' => $saveFile,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {

            $profileTable->insert([
                'member_uid' => $memberUid,
                'profile_image' => $saveFile,
                'step' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '프로필 이미지 업데이트 완료',
            'data' => [
                'profile_image' => $saveFile,
                'url' => base_url('uploads/profile/' . $saveFile)
            ]
        ]);
    }

    public function fcprofile(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        $modal_page = [
            "fc_time_modal.php",
            "fc_lang_modal.php"
        ];

        // print_r($profile);
        // exit;

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcprofile', $data);
    }

    public function fcactivity(): string
    {
        $session = session();
        helper(['region', 'insurance']);
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();



        // 활동 정보
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동 아이템 목록
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('item_id', 'ASC')
            ->get()
            ->getResultArray();

        $modal_page = [
            "region_modal.php",
            "insurance_modal.php"
        ];

        // print_r($profile);
        // exit;
        // echo "<br>=========================activity<br>=========================<br>";
        // print_r($activity);
        // echo "<br>=========================activityItems<br>=========================<br>";
        // print_r($activityItems);
        // exit;

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
            "activity"     => $activity,
            "activityItems" => $activityItems,
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcactivity', $data);
    }

    public function fcstory(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();



        // 활동 정보
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동 아이템 목록
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('item_id', 'ASC')
            ->get()
            ->getResultArray();

        // ==========================
        // Story 정보
        // ==========================
        $story = $db->table('my_fc_profile_story')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // ==========================
        // Story 이미지
        // ==========================
        $storyImages = $db->table('my_fc_profile_story_image')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $modal_page = [];

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
            "activity"     => $activity,
            "activityItems" => $activityItems,
            // 추가
            'story'        => $story,
            'storyImages'  => $storyImages,
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcstory', $data);
    }

    public function fcreviewed(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');


        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $review = $db->table('my_fc_reviewed')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        $modal_page = [];

        $data = [
            "header_class" => "form-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            'review' => $review

        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcreviewed', $data);
    }

    public function ajax_save_reviewed()
    {
        $this->response->setContentType('application/json');

        helper('fileupload_helper');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $table = $db->table('my_fc_reviewed');

        // 기존 데이터
        $review = $table
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // ============================
        // 상태 정책
        // ============================
        $currentStatus = $review['status'] ?? null;

        // WAIT이면 수정 불가 (보안 레벨)
        if ($currentStatus === 'WAIT') {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '승인 대기중에는 수정할 수 없습니다.'
            ]);
        }

        // 입력값
        $saveData = [
            'deliberation_no'      => trim($this->request->getPost('deliberation_no')),
            'approval_start'       => $this->request->getPost('approval_start'),
            'approval_end'         => $this->request->getPost('approval_end'),
            'deliberation_opinion' => trim($this->request->getPost('deliberation_opinion')),
        ];

        // ============================
        // 파일 업로드
        // ============================
        $file = $this->request->getFile('deliberation_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {

            $fileName = upload_file($file, 'uploads/review');

            if (!$fileName) {

                return $this->response->setJSON([
                    'result' => 'fail',
                    'msg' => '파일 업로드에 실패했습니다.'
                ]);
            }

            // 기존 파일 삭제
            if ($review && !empty($review['deliberation_file'])) {

                $oldPath = WRITEPATH . 'uploads/review/' . $review['deliberation_file'];

                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $saveData['deliberation_file'] = $fileName;
        }

        // ============================
        // UPDATE
        // ============================
        if ($review) {

            $saveData['updated_at'] = date('Y-m-d H:i:s');

            // =========================
            // 상태 리셋 규칙
            // =========================
            if (in_array($review['status'], ['APPROVE', 'REJECT'])) {
                $saveData['status'] = 'WAIT';
            }

            $table
                ->where('member_uid', $memberUid)
                ->update($saveData);
        }
        // ============================
        // INSERT
        // ============================
        else {

            $saveData['member_uid'] = $memberUid;
            $saveData['status'] = 'WAIT';
            $saveData['created_at'] = date('Y-m-d H:i:s');

            $table->insert($saveData);
        }

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '심의필 정보가 저장되었습니다.'
        ]);
    }


    public function fcCounselList(): string
    {
        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $q = $this->request->getGet('q');
        $status = $this->request->getGet('status'); // REQUEST / COMPLETE / CANCEL / ALL

        $builder = $db->table('my_fc_counsel c')
            ->select("
                c.*,
                m.name AS member_name,
                m.email AS member_email,
                m.phone AS member_phone
            ")
            ->join('my_fc_member m', 'm.member_uid = c.member_uid', 'left')
            ->where('c.fc_member_uid', $memberUid)
            ->where('c.deleted_at IS NULL', null, false);

        // 검색 (고객명)
        if (!empty($q)) {
            $builder->like('c.name', $q);
        }

        // 상태 필터
        if (!empty($status) && $status !== 'ALL') {
            $builder->where('c.status', $status);
        }

        $counselList = $builder
            ->orderBy('c.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'header_class' => 'consult-status-page fc-consult-mgmt',
            'counselList'  => $counselList,
            'q'            => $q,
            'status'       => $status ?? ''
        ];

        return $this->renderView('mypage/fcCounselList', $data);
    }

    public function fcCounselView($counselUid): string
    {
        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        // 상담 상세
        $counsel = $db->table('my_fc_counsel c')
            ->select("
                c.*,

                m.name AS member_name,
                m.email AS member_email,
                m.phone AS member_phone,
                m.birth,
                m.gender,

                p.profile_image,
                p.ga,
                p.company,
                p.company_sub,

                a.region
            ")
            ->join('my_fc_member m', 'm.member_uid = c.member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = c.member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = c.member_uid', 'left')
            ->where('c.counsel_uid', $counselUid)
            ->where('c.deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$counsel) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 파일 목록
        $files = $db->table('my_fc_counsel_file')
            ->where('counsel_uid', $counselUid)
            ->orderBy('file_id', 'ASC')
            ->get()
            ->getResultArray();

        // 보험 라벨 변환 (최대 3개)
        $insuranceTypes = [];
        if (!empty($counsel['insurance_types'])) {
            $insuranceTypes = array_slice(
                array_map('trim', explode(',', $counsel['insurance_types'])),
                0,
                3
            );
        }

        $data = [
            "header_class" => "detail-page consult-detail-page",
            "popup_page"   => [],
            "modal_page"   => [],
            'counsel' => $counsel,
            'files' => $files,
            'insuranceTypes' => $insuranceTypes,
        ];

        return $this->renderView('mypage/fcCounselView', $data);
    }

    public function fcCounselStatus()
    {
        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result' => false,
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $counselUid = $this->request->getPost('counsel_uid');
        $status     = $this->request->getPost('status'); // COMPLETE or CANCEL

        if (!$counselUid || !$status) {
            return $this->response->setJSON([
                'result' => false,
                'msg' => '잘못된 요청입니다.'
            ]);
        }

        if (!in_array($status, ['COMPLETE', 'CANCEL'])) {
            return $this->response->setJSON([
                'result' => false,
                'msg' => '허용되지 않은 상태값입니다.'
            ]);
        }

        $db = \Config\Database::connect();

        // 현재 상태 확인
        $counsel = $db->table('my_fc_counsel')
            ->where('counsel_uid', $counselUid)
            ->where('fc_member_uid', $memberUid)
            ->get()
            ->getRowArray();

        if (!$counsel) {
            return $this->response->setJSON([
                'result' => false,
                'msg' => '데이터가 없습니다.'
            ]);
        }

        // 이미 완료된 건 변경 불가
        if ($counsel['status'] !== 'REQUEST') {
            return $this->response->setJSON([
                'result' => false,
                'msg' => '이미 처리된 상담입니다.'
            ]);
        }

        // 업데이트
        $db->table('my_fc_counsel')
            ->where('counsel_uid', $counselUid)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'result' => true,
            'msg' => '처리되었습니다.',
            'status' => $status
        ]);
    }

    public function counselReview($counselUid)
    {
        helper(['region', 'insurance']);

        // ===========================
        // 로그인 체크
        // ===========================
        $member_uid = session()->get('member_uid');

        if (empty($member_uid)) {
            return redirect()->to('/member/login');
        }

        // ===========================
        // DB 연결
        // ===========================
        $db = \Config\Database::connect();

        // ===========================
        // 상담 상세 조회 (단일)
        // ===========================
        $counsel = $db->table('my_fc_counsel c')
            ->select("
                c.*,

                m.name AS fc_name,

                p.profile_image,
                p.ga,
                p.company,
                p.company_sub,

                a.region,
                a.insurance_types,
                a.hero_line
            ")
            ->join('my_fc_member m', 'm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = c.fc_member_uid', 'left')
            ->where('c.member_uid', $member_uid)
            ->where('c.counsel_uid', $counselUid)
            ->where('c.deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        // ===========================
        // 데이터 없을 경우 404 처리
        // ===========================
        if (!$counsel) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // print_r($counsel);
        // exit;

        // ===========================
        // View 데이터
        // ===========================
        $data = [
            "header_class" => "form-page",
            "popup_page"   => [],
            "modal_page"   => [],
            "counsel"      => $counsel,
        ];

        return $this->renderView('mypage/counselReview', $data);
    }

    public function counselReviewSubmitAjax($counselUid)
    {
        $member_uid = session()->get('member_uid');

        if (empty($member_uid)) {
            return $this->response->setJSON([
                'result' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $rating = $this->request->getPost('rating');
        $title  = trim($this->request->getPost('title'));
        $body   = trim($this->request->getPost('body'));

        // ===========================
        // validation
        // ===========================
        if (empty($rating) || empty($title) || empty($body)) {
            return $this->response->setJSON([
                'result' => 'error',
                'message' => '필수값을 입력해주세요.'
            ]);
        }

        if ($rating < 0.5 || $rating > 5) {
            return $this->response->setJSON([
                'result' => 'error',
                'message' => '잘못된 평점입니다.'
            ]);
        }

        // ===========================
        // 상담 존재 확인
        // ===========================
        $counsel = $db->table('my_fc_counsel')
            ->where('counsel_uid', $counselUid)
            ->where('member_uid', $member_uid)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$counsel) {
            return $this->response->setJSON([
                'result' => 'error',
                'message' => '상담 정보를 찾을 수 없습니다.'
            ]);
        }

        // ===========================
        // 중복 체크
        // ===========================
        $exists = $db->table('my_fc_counsel_review')
            ->where('counsel_uid', $counselUid)
            ->where('member_uid', $member_uid)
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON([
                'result' => 'error',
                'message' => '이미 후기를 작성했습니다.'
            ]);
        }

        // ===========================
        // insert
        // ===========================
        $db->table('my_fc_counsel_review')->insert([
            'counsel_uid'   => $counselUid,
            'fc_member_uid' => $counsel['fc_member_uid'],
            'member_uid'    => $member_uid,
            'rating'        => $rating,
            'title'         => $title,
            'body'          => $body,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'message' => '후기가 등록되었습니다.'
        ]);
    }

    public function counselReviewLast(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('mypage/counselReviewLast', $data);
    }

    public function withdrawAjax(): ResponseInterface
    {
        $session = session();

        // =========================
        // 1. 로그인 체크
        // =========================
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $memberUid = $session->get('member_uid');

        // =========================
        // 2. POST 체크 (동의 체크)
        // =========================
        $agree = $this->request->getPost('agree');

        if ($agree !== 'Y') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '탈퇴 동의가 필요합니다.'
            ]);
        }

        // =========================
        // 3. 회원 조회
        // =========================
        $memberModel = new \App\Models\MemberModel();

        $member = $memberModel
            ->where('member_uid', $memberUid)
            ->first();

        if (!$member) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '회원 정보를 찾을 수 없습니다.'
            ]);
        }

        // =========================
        // 4. 탈퇴 처리 (soft delete)
        // =========================
        $update = $memberModel->where('member_uid', $memberUid)->set([
            'status'     => 'LEAVE',
            'deleted_at' => date('Y-m-d H:i:s')
        ])->update();

        if (!$update) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '탈퇴 처리 실패'
            ]);
        }

        // =========================
        // 5. 세션 제거 (로그아웃)
        // =========================
        $session->destroy();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '회원 탈퇴가 완료되었습니다.'
        ]);
    }

    public function updateInfo()
{
    $session = session();

    if (!$session->get('logged_in')) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '로그인이 필요합니다.'
        ]);
    }

    $memberUid = $session->get('member_uid');

    $memberModel = new \App\Models\MemberModel();

    // =========================
    // 1. 데이터 수집
    // =========================
    $email   = $this->request->getPost('email');
    $phone   = $this->request->getPost('phone');
    $name    = $this->request->getPost('name');
    $agreeMk = $this->request->getPost('agree_marketing');
    $gender = $this->request->getPost('gender');

    // =========================
    // 2. 업데이트 데이터
    // =========================
    $data = [
        'phone'            => $phone,
        'name'             => $name,
        'gender'           => $gender,   // ⭐ 추가
        'agree_marketing'  => $agreeMk,
        'updated_at'       => date('Y-m-d H:i:s'),
    ];

    // 이메일은 수정 불가 조건 아니면 허용
    if ($email) {
        $data['email'] = $email;
    }

    // =========================
    // 3. 업데이트 실행
    // =========================
    $update = $memberModel
        ->where('member_uid', $memberUid)
        ->set($data)
        ->update();

    if (!$update) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '수정 실패'
        ]);
    }

    return $this->response->setJSON([
        'status' => 'success',
        'message' => '회원 정보가 수정되었습니다.'
    ]);
}
}
