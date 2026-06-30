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
        $builder->join('my_fc_counsel_review r', 'r.fc_member_uid = m.member_uid', 'left');

        /* =========================
    * GROUP BY (필수)
    ========================= */
        $builder->groupBy('m.member_uid');

        /* =========================
    * FILTER
    ========================= */
        $insurance = $this->request->getGet('insurance');
        $region    = $this->request->getGet('region');
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

        $builder->where('m.deleted_at IS NULL', null, false);
        $builder->where('m.member_type', 'FC');
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
            "sort"      => $sort,

            "list"      => $list,

            "page"      => $page,
            "perPage"   => $perPage,
            "total"     => $total,
            "totalPages" => $totalPages
        ]);
    }

    public function view(): string
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
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray();


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



        // =========================
        // 7. REVIEW LIST (추가)
        // =========================
        $reviewList = $db->table('my_fc_counsel_review r')
            ->select('
                r.*,
                m.name AS reviewer_name
            ')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->where('c.fc_member_uid', $uid)
            ->orderBy('r.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // =========================
        // 8. REVIEW STATS (추가)
        // =========================
        $reviewStats = $db->table('my_fc_counsel_review r')
            ->select('
                IFNULL(AVG(r.rating),0) AS rating,
                COUNT(r.review_id) AS rating_count
            ')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->where('c.fc_member_uid', $uid)
            ->get()
            ->getRowArray();

        $memberUid = session()->get('member_uid');

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

        ];

        return $this->renderView('fc/view', $data);
    }

    public function counsel(): string
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
            return redirect()->to('/login');
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
}
