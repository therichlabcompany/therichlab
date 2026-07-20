<?php

namespace App\Controllers;

class FcController extends BaseController
{
    public function index(): string
    {
        helper(['region', 'insurance']);

        $header_class = "search-page results";

        $popup_page = [
            "popup_insurance.php",
            "popup_region.php"
        ];

        $modal_page = [];

        /* =========================
    * PAGINATION SETTING
    ========================= */
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $db = \Config\Database::connect();
        $reviewJoin = 'r.fc_member_uid = m.member_uid';
        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $reviewJoin .= " AND r.deleted_at IS NULL AND r.display_status = 'Y'";
        } else {
            $reviewJoin .= ' AND r.deleted_at IS NULL';
        }

        /* =========================
    * BASE BUILDER (LIST)
    ========================= */
        $builder = $db->table('my_fc_member m');

        $builder->select('
        m.member_id,
        m.member_uid,
        m.email,
        m.name,
        m.phone,
        m.profile_image AS member_profile_image,
        m.fc_step,
        m.fc_review_status,

        p.company,
        p.company_sub,
        p.ga,
        p.position,
        p.license_date,
        p.license_no,
        p.language,
        p.profile_image AS profile_image,

        a.region,
        a.insurance_types,
        a.hero_line,
        a.intro,
        a.career,

        IFNULL(AVG(r.rating), 0) AS rating,
        COUNT(r.review_id) AS rating_count
    ');

        $builder->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'inner');
        $builder->join('my_fc_profile_activity a', 'a.member_uid = m.member_uid', 'left');
        $builder->join('my_fc_counsel_review r', $reviewJoin, 'left', false);

        /* =========================
    * GROUP BY (필수)
    ========================= */
        $builder->groupBy('m.member_uid');

        /* =========================
    * FILTER
    ========================= */
        $insurance = $this->request->getGet('insurance');
        $region    = $this->request->getGet('region');
        $keyword   = trim((string) $this->request->getGet('q'));
        $sort      = $this->request->getGet('sort') ?? 'recommend';

        if (!empty($insurance)) {
            $insArr = array_filter(array_map('trim', explode(',', $insurance)));

            $builder->groupStart();
            foreach ($insArr as $i => $val) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $builder->$method("FIND_IN_SET(" . $db->escape($val) . ", a.insurance_types) >", 0, false);
            }
            $builder->groupEnd();
        }

        if (!empty($region)) {
            $regionArr = array_filter(array_map('trim', explode(',', $region)));

            $builder->groupStart();
            foreach ($regionArr as $i => $val) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $builder->$method("FIND_IN_SET(" . $db->escape($val) . ", a.region) >", 0, false);
            }
            $builder->groupEnd();
        }

        if ($keyword !== '' && mb_strlen($keyword) >= 2) {
            $this->rememberSearchKeyword($keyword);
            $builder->groupStart()
                ->like('m.name', $keyword)
                ->orLike('p.company', $keyword)
                ->orLike('p.company_sub', $keyword)
                ->orLike('p.ga', $keyword)
                ->orLike('p.position', $keyword)
                ->orLike('a.hero_line', $keyword)
                ->orLike('a.intro', $keyword)
                ->orLike('a.career', $keyword)
                ->orLike('p.language', $keyword);

            foreach ($this->searchRegionCodes($keyword) as $code) {
                $builder->orWhere("FIND_IN_SET(" . $db->escape($code) . ", a.region) >", 0, false);
            }

            foreach ($this->searchInsuranceCodes($keyword) as $code) {
                $builder->orWhere("FIND_IN_SET(" . $db->escape($code) . ", a.insurance_types) >", 0, false);
            }

            $builder->groupEnd();
        }

        $builder->where('m.deleted_at IS NULL', null, false);
        $builder->where('m.member_type', 'FC');
        $builder->where('m.status', 'ACTIVE');
        $builder->where('m.fc_review_status', 'APPROVE');

        /* =========================
    * SORT
    ========================= */
        switch ($sort) {
            case 'popular':
                $builder->orderBy('p.view_count', 'DESC');
                break;

            case 'rating':
                $builder->orderBy('rating', 'DESC'); // alias 사용 (OK)
                break;

            default:
                $builder->orderBy('m.member_id', 'DESC');
                break;
        }

        /* =========================
    * COUNT (안전 분리)
    ========================= */
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults(false);

        /* =========================
    * DATA
    ========================= */
        $builder->limit($perPage, $offset);
        $list = $builder->get()->getResultArray();

        $totalPages = ceil($total / $perPage);

        return $this->renderView('fc/list', [
            "header_class" => $header_class,
            "popup_page"   => $popup_page,
            "modal_page"   => $modal_page,

            "insurance" => $insurance,
            "region"    => $region,
            "q"         => $keyword,
            "sort"      => $sort,

            "list"      => $list,

            "page"      => $page,
            "perPage"   => $perPage,
            "total"     => $total,
            "totalPages" => $totalPages
        ]);
    }

    public function search(): string
    {
        helper(['region', 'insurance']);

        $header_class = "form-page search-page";
        $popup_page = [
            "popup_insurance.php",
            "popup_region.php",
        ];

        $modal_page = [];
        $recentSearches = session()->get('fc_recent_searches');

        if (!is_array($recentSearches)) {
            $recentSearches = [];
        }

        return $this->renderView('fc/search', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "recent_searches" => $recentSearches,
        ]);
    }

    public function view()
    {
        helper(['region', 'insurance']);
        $header_class = "detail-page";

        $uid = $this->request->getGet('uid');

        if (!$uid) {
            return redirect()->to('/fc/list');
        }

        $db = \Config\Database::connect();

        // =========================
        // 1. MEMBER
        // =========================
        $member = $db->table('my_fc_member')
            ->where('member_uid', $uid)
            ->where('member_type', 'FC')
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $sessionMemberUid = (string) session()->get('member_uid');
        $isOwner = session()->get('member_type') === 'FC'
            && $sessionMemberUid !== ''
            && hash_equals((string) $member['member_uid'], $sessionMemberUid);
        $isAdminPreview = (bool) session()->get('admin_logged_in');
        $isPublic = ($member['status'] ?? '') === 'ACTIVE'
            && ($member['fc_review_status'] ?? '') === 'APPROVE';

        if (!$isPublic && !$isOwner && !$isAdminPreview) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($isPublic && !$isOwner && !$isAdminPreview) {
            $db->table('my_fc_profile')
                ->where('member_uid', $uid)
                ->set('view_count', 'view_count+1', false)
                ->update();
        }

        // =========================
        // 2. PROFILE
        // =========================
        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();

        // =========================
        // 3. ACTIVITY (핵심)
        // =========================
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();



        // =========================
        // 4. ACTIVITY ITEM (자료)
        // =========================
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $uid)
            ->where('is_visible', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        // =========================
        // 5. STORY
        // =========================
        $story = $db->table('my_fc_profile_story')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();

        // =========================
        // 6. STORY IMAGES
        // =========================
        $storyImages = $db->table('my_fc_profile_story_image')
            ->where('member_uid', $uid)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $review = $db->table('my_fc_reviewed')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();



        // =========================
        // 7. REVIEW LIST (추가)
        // =========================
        $reviewListBuilder = $db->table('my_fc_counsel_review r')
            ->select('
                r.*,
                m.name AS reviewer_name
            ')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->where('c.fc_member_uid', $uid)
            ->where('r.deleted_at IS NULL', null, false)
            ->orderBy('r.created_at', 'DESC');

        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $reviewListBuilder->where('r.display_status', 'Y');
        }

        $reviewList = $reviewListBuilder
            ->limit(10)
            ->get()
            ->getResultArray();

        // =========================
        // 8. REVIEW STATS (추가)
        // =========================
        $reviewStatsBuilder = $db->table('my_fc_counsel_review r')
            ->select('
                IFNULL(AVG(r.rating),0) AS rating,
                COUNT(r.review_id) AS rating_count
            ')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->where('c.fc_member_uid', $uid);

        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $reviewStatsBuilder->where('r.display_status', 'Y');
        }

        $reviewStats = $reviewStatsBuilder->get()->getRowArray();

        $memberUid = $sessionMemberUid;

        $isBookmarked = false;

        if ($memberUid && $uid) {

            $isBookmarked = $db->table('fc_bookmarks')
                ->where('member_uid', $memberUid)
                ->where('fc_member_uid', $uid)
                ->countAllResults() > 0;
        }

        $data = [
            "header_class"   => $header_class,

            "member"         => $member,
            "profile"        => $profile,
            "activity"       => $activity,
            "activityItems"  => $activityItems,
            "story"          => $story,
            "storyImages"    => $storyImages,
            "review"         => $review   // 👈 추가
            ,
            // 🔥 추가
            "reviewList"    => $reviewList,
            "rating"        => $reviewStats['rating'] ?? 0,
            "rating_count"  => $reviewStats['rating_count'] ?? 0,
            "bookmark_status" => $isBookmarked,
            "is_owner_preview" => $isOwner && !$isPublic,

        ];

        return $this->renderView('fc/view', $data);
    }

    public function counsel()
    {
        //return pageView('welcome_message');
        helper(['region', 'insurance']);
        $header_class = "detail-page";

        $popup_page = [];

        $modal_page = [
            "counsel_last_modal.php"
        ];
        $session = session();
        // 로그인 여부
        if (!$session->get('logged_in')) {
            return redirect()->to('/member/login');
        }

        // USER 회원만 상담 가능
        if ($session->get('member_type') !== 'USER') {
            return redirect()->back()->with('error', '일반 회원만 상담 신청이 가능합니다.');
        }

        $uid = $this->request->getGet('uid');

        if (!$uid) {
            return redirect()->to('/fc/list');
        }

        $db = \Config\Database::connect();

        // =========================
        // 1. MEMBER
        // =========================
        $my_member = $db->table('my_fc_member')
            ->where('member_uid', $session->get('member_uid'))
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();


        // =========================
        // 1. MEMBER
        // =========================
        $member = $db->table('my_fc_member')
            ->where('member_uid', $uid)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$member || ($member['member_type'] ?? '') !== 'FC') {
            return redirect()->to('/fc/list')->with('error', '상담 가능한 FC 정보를 찾을 수 없습니다.');
        }


        $db->table('my_fc_profile')
            ->where('member_uid', $uid)
            ->set('view_count', 'view_count+1', false)
            ->update();

        // =========================
        // 2. PROFILE
        // =========================
        $profile = $db->table('my_fc_profile')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();

        // =========================
        // 3. ACTIVITY (핵심)
        // =========================
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();



        // =========================
        // 4. ACTIVITY ITEM (자료)
        // =========================
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $uid)
            ->where('is_visible', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        // =========================
        // 5. STORY
        // =========================
        $story = $db->table('my_fc_profile_story')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();

        // =========================
        // 6. STORY IMAGES
        // =========================
        $storyImages = $db->table('my_fc_profile_story_image')
            ->where('member_uid', $uid)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $review = $db->table('my_fc_reviewed')
            ->where('member_uid', $uid)
            ->get()
            ->getRowArray();
        // print_r($activity);
        // exit;

        // =========================
        // 증권파일 조회
        // =========================
        $securityModel = new \App\Models\MemberSecurityModel();

        $securityList = $securityModel
            ->where('member_uid', $session->get('member_uid'))
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('security_id', 'DESC')
            ->findAll();

        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "securityList" => $securityList,
            "my_member" => $my_member,

            "member"         => $member,
            "profile"        => $profile,
            "activity"       => $activity,
            "activityItems"  => $activityItems,
            "story"          => $story,
            "storyImages"    => $storyImages,
            "review"         => $review   // 👈 추가
        ];


        return $this->renderView('fc/counsel', $data);
    }

    public function counselLast(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";

        $popup_page = [];

        $modal_page = [
            "counsel_last_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('fc/counselLast', $data);
    }

    private function rememberSearchKeyword(string $keyword): void
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return;
        }

        $session = session();
        $recent = $session->get('fc_recent_searches');
        if (!is_array($recent)) {
            $recent = [];
        }

        $recent = array_values(array_filter($recent, static fn ($item) => (string) $item !== $keyword));
        array_unshift($recent, $keyword);
        $recent = array_slice($recent, 0, 8);

        $session->set('fc_recent_searches', $recent);
    }

    private function searchRegionCodes(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $map = [
            '서울' => ['seoul'],
            '경기' => ['gyeonggi'],
            '인천' => ['incheon_bucheon'],
            '부천' => ['incheon_bucheon'],
            '부산' => ['busan_ulsan_gyeongnam'],
            '울산' => ['busan_ulsan_gyeongnam'],
            '경남' => ['busan_ulsan_gyeongnam'],
            '대구' => ['daegu_gyeongbuk'],
            '경북' => ['daegu_gyeongbuk'],
            '대전' => ['daejeon_sejong_chungnam'],
            '세종' => ['daejeon_sejong_chungnam'],
            '충남' => ['daejeon_sejong_chungnam'],
            '충북' => ['cheongju_chungbuk'],
            '청주' => ['cheongju_chungbuk'],
            '광주' => ['gwangju_jeonnam'],
            '전남' => ['gwangju_jeonnam'],
            '전주' => ['jeonju_jeonbuk'],
            '전북' => ['jeonju_jeonbuk'],
            '강원' => ['chuncheon_gangwon'],
            '춘천' => ['chuncheon_gangwon'],
            '제주' => ['jeju'],
            '수도권' => ['seoul_incheon_gyeonggi'],
            '전국' => ['all'],
        ];

        $codes = [];
        foreach ($map as $label => $items) {
            if (mb_strpos($keyword, $label) !== false || mb_strpos($label, $keyword) !== false) {
                $codes = array_merge($codes, $items);
            }
        }

        if (preg_match('/^(seoul|gyeonggi|incheon_bucheon|busan_ulsan_gyeongnam|daegu_gyeongbuk|daejeon_sejong_chungnam|cheongju_chungbuk|gwangju_jeonnam|jeonju_jeonbuk|chuncheon_gangwon|jeju|seoul_incheon_gyeonggi)$/i', $keyword)) {
            $codes[] = strtolower($keyword);
        }

        return array_values(array_unique($codes));
    }

    private function searchInsuranceCodes(string $keyword): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        $map = [
            '종신' => ['whole_life'],
            '암' => ['cancer'],
            '뇌심장' => ['brain_cardio'],
            '실비' => ['indemnity'],
            '자녀' => ['child'],
            '태아' => ['child'],
            '치매' => ['dementia'],
            '간병' => ['dementia'],
            '치아' => ['dental'],
            '연금' => ['pension'],
            '변액' => ['pension'],
            '사업자' => ['business'],
            '운전자' => ['driver'],
            '자동차' => ['car'],
            '화재' => ['fire'],
        ];

        $codes = [];
        foreach ($map as $label => $items) {
            if (mb_strpos($keyword, $label) !== false || mb_strpos($label, $keyword) !== false) {
                $codes = array_merge($codes, $items);
            }
        }

        if (preg_match('/^(whole_life|cancer|brain_cardio|indemnity|child|dementia|dental|pension|business|driver|car|fire)$/i', $keyword)) {
            $codes[] = strtolower($keyword);
        }

        return array_values(array_unique($codes));
    }
}
