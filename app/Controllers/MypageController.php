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
        $mobileOk = service('mobileOk');
        $data['mobileOkEnabled'] = $mobileOk->isConfigured();
        $data['mobileOkJsUrl'] = $mobileOk->requestJsUrl();
        $data['mobileOkRequestUrl'] = base_url('member/phone-auth/request');
        $data['mobileOkResultUrl'] = $mobileOk->returnUrl();
        $data['phoneAuthApplyUrl'] = base_url('mypage/apply-phone-auth-info');
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
        $hasViewCount = $db->fieldExists('view_count', 'my_fc_counsel_review');

        if ($hasViewCount) {
            $db->table('my_fc_counsel_review')
                ->where('review_id', $reviewId)
                ->where('member_uid', $member_uid)
                ->set('view_count', 'view_count + 1', false)
                ->update();
        }

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
        $mobileOk = service('mobileOk');
        $data['mobileOkEnabled'] = $mobileOk->isConfigured();
        $data['mobileOkJsUrl'] = $mobileOk->requestJsUrl();
        $data['mobileOkRequestUrl'] = base_url('member/phone-auth/request');
        $data['mobileOkResultUrl'] = $mobileOk->returnUrl();
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
        $birth   = $this->request->getPost('birth');
        $agreeMk = $this->request->getPost('agree_marketing');
        $gender = $this->request->getPost('gender');

        $currentMember = $memberModel
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->first();

        if (!$currentMember) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '회원 정보를 찾을 수 없습니다.'
            ]);
        }

        $phone = preg_replace('/[^0-9]/', '', (string) $phone);
        $currentPhone = preg_replace('/[^0-9]/', '', (string) ($currentMember['phone'] ?? ''));

        if ($phone === '' || strlen($phone) < 10 || strlen($phone) > 11) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 확인해주세요.'
            ]);
        }

        if ($phone !== $currentPhone) {
            $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
            if (!(bool) $session->get('phone_auth_verified') || $authPhone !== $phone) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => '변경할 휴대폰 번호를 본인인증해주세요.'
                ]);
            }

            $duplicate = $memberModel
                ->where('phone', $phone)
                ->where('member_uid !=', $memberUid)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($duplicate > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => '이미 사용 중인 휴대폰 번호입니다.'
                ]);
            }
        }

        // 본인인증을 완료한 번호라면 인증기관에서 받은 인적 정보를 우선 반영한다.
        // 클라이언트에서 값을 변경해 보내도 인증 결과와 다른 정보로 저장되지 않도록 한다.
        $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
        if ((bool) $session->get('phone_auth_verified') && $authPhone === $phone) {
            $authName = trim((string) $session->get('phone_auth_name'));
            $authBirth = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_birth'));
            $authGender = strtoupper(trim((string) $session->get('phone_auth_gender')));

            if ($authName !== '') {
                $name = $authName;
            }
            if ($authBirth !== '') {
                $birth = $authBirth;
            }
            if (in_array($authGender, ['M', 'F'], true)) {
                $gender = $authGender;
            }
        }

        // =========================
        // 2. 업데이트 데이터
        // =========================
        $data = [
            'phone'            => $phone,
            'name'             => $name,
            'birth'            => $birth,
            'gender'           => $gender,   // ⭐ 추가
            'phone_verified'   => $phone !== $currentPhone ? 'Y' : ($currentMember['phone_verified'] ?? 'N'),
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

    /**
     * 본인인증 직후 인증기관에서 확인한 정보를 회원 정보에 즉시 반영한다.
     */
    public function applyPhoneAuthInfo()
    {
        $session = session();
        $memberUid = (string) $session->get('member_uid');

        if (!$session->get('logged_in') || $memberUid === '') {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.',
            ]);
        }

        if (!(bool) $session->get('phone_auth_verified')) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => '본인인증을 먼저 완료해주세요.',
            ]);
        }

        $phone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => '인증된 휴대폰 번호를 확인할 수 없습니다.',
            ]);
        }

        $memberModel = new \App\Models\MemberModel();
        $member = $memberModel
            ->where('member_uid', $memberUid)
            ->where('deleted_at', null)
            ->first();

        if (!$member) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => '회원 정보를 찾을 수 없습니다.',
            ]);
        }

        $currentPhone = preg_replace('/[^0-9]/', '', (string) ($member['phone'] ?? ''));
        $phoneChanged = $phone !== $currentPhone;

        if ($phoneChanged) {
            $duplicate = $memberModel
                ->where('phone', $phone)
                ->where('member_uid !=', $memberUid)
                ->where('deleted_at', null)
                ->countAllResults();

            if ($duplicate > 0) {
                return $this->response->setStatusCode(409)->setJSON([
                    'status' => 'error',
                    'message' => '이미 사용 중인 휴대폰 번호입니다.',
                ]);
            }
        }

        $data = [
            'phone' => $phone,
            'phone_verified' => 'Y',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $name = trim((string) $session->get('phone_auth_name'));
        $birth = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_birth'));
        $gender = strtoupper(trim((string) $session->get('phone_auth_gender')));
        if ($name !== '') {
            $data['name'] = $name;
        }
        if ($birth !== '') {
            $data['birth'] = $birth;
        }
        if (in_array($gender, ['M', 'F'], true)) {
            $data['gender'] = $gender;
        }

        if (!$memberModel->where('member_uid', $memberUid)->set($data)->update()) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '인증 정보를 반영하지 못했습니다.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'phoneChanged' => $phoneChanged,
            'message' => $phoneChanged ? '휴대폰 번호가 변경되었습니다.' : '본인인증 정보가 반영되었습니다.',
        ]);
    }

    public function adlist(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page ad-mgmt-page";

        $fc_member_id = session()->get('member_uid');

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $adModel = new \App\Models\AdMasterModel();

        $result = $adModel->getAdListByMemberPaging($fc_member_id, $page, $perPage);

        $adList = $result['list'];
        $total  = $result['total'];

        $totalPages = ceil($total / $perPage);

        // =========================
        // 상태 가공 (핵심 로직 통합)
        // =========================
        $today = date('Y-m-d');

        foreach ($adList as &$ad) {

            $status = $ad['status'];

            // 기본값
            $ad['status_text'] = '';
            $ad['status_class'] = '';

            if ($status === 'approved') {

                // 기간 체크
                if (!empty($ad['start_date']) && !empty($ad['end_date'])) {

                    if ($today < $ad['start_date']) {
                        $ad['status_text'] = '대기';
                        $ad['status_class'] = 'wait';
                    } elseif ($today > $ad['end_date']) {
                        $ad['status_text'] = '종료';
                        $ad['status_class'] = 'end';
                    } else {
                        $ad['status_text'] = '광고 중';
                        $ad['status_class'] = 'on';
                    }
                } else {
                    $ad['status_text'] = '광고 중';
                    $ad['status_class'] = 'on';
                }
            } else {

                $map = [
                    'apply'    => ['신청', 'wait'],
                    'pending'  => ['대기', 'wait'],
                    'rejected' => ['거절', 'end'],
                    'end'      => ['종료', 'end'],
                ];

                $ad['status_text']  = $map[$status][0] ?? $status;
                $ad['status_class'] = $map[$status][1] ?? '';
            }

            // 광고 타입 한글 매핑
            $adTypeMap = [
                'region_fc'   => '지역별 FC',
                'banner'      => '배너광고',
                'product_fc'  => '상품별 FC',
                'review'      => '리뷰광고',
                'language_fc' => '언어별 FC',
            ];

            $ad['ad_name'] = $adTypeMap[$ad['ad_type']] ?? $ad['ad_type'];

            // 기간 포맷
            if (!empty($ad['start_date']) && !empty($ad['end_date'])) {
                $ad['period'] =
                    date('y.m.d', strtotime($ad['start_date'])) .
                    ' – ' .
                    date('y.m.d', strtotime($ad['end_date']));
            } else {
                $ad['period'] = '-';
            }
        }

        return $this->renderView('mypage/adList', [
            "header_class" => $header_class,
            "adList" => $adList,
            "page" => $page,
            "totalPages" => $totalPages,
            "perPage" => $perPage
        ]);
    }

    public function adlistRegionFc(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page fc-ad-apply-page";

        $popup_page = [];
        $modal_page = [];

        return $this->renderView('mypage/adlistRegionFc', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,

        ]);
    }

    public function adlistBanner(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page fc-ad-apply-page";

        $popup_page = [];
        $modal_page = [];

        return $this->renderView('mypage/adlistBanner', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,

        ]);
    }

    public function adlistProductFc(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page fc-ad-apply-page";

        $popup_page = [];
        $modal_page = [];

        return $this->renderView('mypage/adlistProductFc', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,

        ]);
    }


    public function adlistReview(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page fc-ad-apply-page";

        $popup_page = [];
        $modal_page = [];

        $db = \Config\Database::connect();

        // ===========================
        // 내 리뷰 리스트 + FC 정보 JOIN
        // ===========================
        $memberId = session()->get('member_uid');
        $reviewList = $db->table('my_fc_counsel_review r')
            ->select("
            r.*,

            c.counsel_uid,
            c.status AS counsel_status,
            c.created_at AS counsel_created_at,

            m.name AS name,

            p.profile_image,
            p.ga,
            p.company,
            p.company_sub,

            a.region,
            a.insurance_types
        ")
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = r.member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = r.member_uid', 'left')
            ->where('r.fc_member_uid', $memberId)
            ->where('r.deleted_at IS NULL', null, false)
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->renderView('mypage/adlistReview', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "reviewList" => $reviewList,

        ]);
    }


    public function adlistLanguageFc(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page fc-ad-apply-page";

        $popup_page = [];
        $modal_page = [];

        return $this->renderView('mypage/adlistLanguageFc', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,

        ]);
    }

    public function ajaxRegionFcApply()
    {
        $memberId = session()->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $region = $this->request->getPost('ad_region');
        $plan   = $this->request->getPost('ad_plan');

        if (!$region || !$plan) {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '필수값 누락'
            ]);
        }

        $priceMap = [
            '1m' => 500000
        ];

        $model = new \App\Models\AdMasterModel();

        $model->insert([
            'fc_member_id' => $memberId,
            'ad_type' => 'region_fc',
            'status' => 'apply',

            'region_code' => $region,

            'amount' => $priceMap[$plan] ?? 0,
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '신청 완료'
        ]);
    }

    public function ajaxBannerApply()
    {
        helper('fileupload_helper');

        $session = session();
        $memberId = $session->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $plan = $this->request->getPost('ad_plan');
        $needDesign = $this->request->getPost('banner_need_design');

        if (!$plan) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '광고 기간이 없습니다.'
            ]);
        }

        $file = $this->request->getFile('banner_file');

        // =========================
        // 파일 조건 처리
        // =========================
        if (!$needDesign) {

            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'result' => 'error',
                    'msg' => '파일이 없습니다.'
                ]);
            }
        }

        // =========================
        // 1. 업로드 (헬퍼 방식)
        // =========================
        $filePath = "";
        if (!$needDesign) {
            try {
                $fileName = upload_file($file, 'uploads/banner', ['jpg', 'jpeg', 'png', 'gif']);
            } catch (\Throwable $e) {
                return $this->response->setJSON([
                    'result' => 'error',
                    'msg' => '배너 이미지는 jpg, png, gif 파일만 등록할 수 있습니다.'
                ]);
            }

            if (!$fileName) {
                return $this->response->setJSON([
                    'result' => 'error',
                    'msg' => '업로드 실패'
                ]);
            }

            $filePath = '/uploads/banner/' . $fileName;
        }

        // =========================
        // 2. 가격 정책
        // =========================
        $priceMap = [
            '1m' => 500000
        ];

        $amount = $priceMap[$plan] ?? 0;

        if (!$amount) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '잘못된 상품입니다.'
            ]);
        }

        // =========================
        // 3. DB 저장
        // =========================
        $model = new \App\Models\AdMasterModel();

        $model->insert([
            'fc_member_id' => $memberId,
            'ad_type' => 'banner',
            'status' => 'apply',

            'amount' => $amount,

            'banner_image_url' => $filePath,
            'banner_need_design' => $needDesign ? 1 : 0,
            'banner_position' => 'top',
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '신청 완료'
        ]);
    }


    public function ajaxProductFcApply()
    {
        $memberId = session()->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $plan = $this->request->getPost('ad_plan');
        $insurance = $this->request->getPost('ad_insurance_type');

        if (!$plan || !$insurance) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '필수값 누락'
            ]);
        }

        $priceMap = [
            '1m' => 500000
        ];

        $model = new \App\Models\AdMasterModel();

        $model->insert([
            'fc_member_id' => $memberId,
            'ad_type' => 'product_fc',
            'status' => 'apply',

            'insurance_type' => $insurance,

            'amount' => $priceMap[$plan] ?? 0,
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '신청 완료'
        ]);
    }


    public function ajaxReviewApply()
    {
        $memberId = session()->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $reviewId = $this->request->getPost('ad_review_id');
        $plan     = $this->request->getPost('ad_plan');

        if (!$reviewId || !$plan) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '필수값 누락'
            ]);
        }

        $ownedReview = \Config\Database::connect()->table('my_fc_counsel_review')
            ->where('review_id', (int) $reviewId)
            ->where('fc_member_uid', $memberId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$ownedReview) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '본인에게 등록된 후기만 광고로 신청할 수 있습니다.'
            ]);
        }

        $priceMap = [
            '1m' => 500000
        ];

        $model = new \App\Models\AdMasterModel();

        $model->insert([
            'fc_member_id' => $memberId,
            'ad_type' => 'review',
            'status' => 'apply',

            'review_id' => $reviewId,
            'amount' => $priceMap[$plan] ?? 0,
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '신청 완료'
        ]);
    }

    public function ajaxLanguageApply()
    {
        $memberId = session()->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $plan = $this->request->getPost('ad_plan');
        $language = $this->request->getPost('ad_language');

        if (!$plan || !$language) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '필수값 누락'
            ]);
        }

        $priceMap = [
            '1m' => 500000
        ];

        $model = new \App\Models\AdMasterModel();

        $model->insert([
            'fc_member_id' => $memberId,
            'ad_type' => 'language_fc',
            'status' => 'apply',

            'language_code' => $language,

            'amount' => $priceMap[$plan] ?? 0,
        ]);

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '신청 완료'
        ]);
    }

    public function adLast(): string
    {
        helper(['region', 'insurance']);

        $header_class = "flow-result";

        $popup_page = [];
        $modal_page = [];

        return $this->renderView('mypage/adLast', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,

        ]);
    }



    //http://therichlab.local/sample/html/MFC005_L01_04_01_01.html
}
