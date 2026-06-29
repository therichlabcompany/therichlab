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

        /* =========================
        * MODEL / DB BUILDER
        ========================= */
        $db = \Config\Database::connect();

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
            0 AS rating,
0 AS rating_count
        ');

        $builder->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'inner');

        /* ✅ activity 추가 (핵심) */
        $builder->join('my_fc_profile_activity a', 'a.member_uid = m.member_uid', 'left');

        $builder->where('m.deleted_at IS NULL', null, false);
        $builder->where('m.member_type', 'FC');
        $builder->where('m.fc_review_status', 'APPROVE');

        $builder->orderBy('m.member_id', 'DESC');

        /* =========================
        * TOTAL COUNT (pagination)
        ========================= */
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults(false);

        /* =========================
        * DATA LIST
        ========================= */
        $builder->limit($perPage, $offset);
        $list = $builder->get()->getResultArray();

        /* =========================
        * TOTAL PAGE
        ========================= */
        $totalPages = ceil($total / $perPage);

        // print_r($list);
        // exit;

        /* =========================
        * VIEW DATA
        ========================= */
        $data = [
            "header_class" => $header_class,
            "popup_page"   => $popup_page,
            "modal_page"   => $modal_page,

            "list"         => $list,

            // pagination
            "page"         => $page,
            "perPage"      => $perPage,
            "total"        => $total,
            "totalPages"   => $totalPages
        ];

        return $this->renderView('fc/list', $data);
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
    
        // echo "<br>===========================profile<br>===================<br>";
        // print_r($profile);
        // echo "<br>===========================activity<br>===================<br>";
        // print_r($activity);
        
        // echo "<br>===========================activityItems<br>===================<br>";
        // print_r($activityItems);

        // echo "<br>===========================story<br>===================<br>";
        // print_r($story);
        // echo "<br>===========================storyImages<br>===================<br>";
        // print_r($storyImages);
        // echo "<br>===========================review<br>===================<br>";
        // print_r($review);
        // exit;
        $data = [
            "header_class"   => $header_class,

            "member"         => $member,
            "profile"        => $profile,
            "activity"       => $activity,
            "activityItems"  => $activityItems,
            "story"          => $story,
            "storyImages"    => $storyImages,
            "review"         => $review   // 👈 추가
        ];

        return $this->renderView('fc/view', $data);
    }

    public function counsel(): string
    {
        //return pageView('welcome_message');
        $header_class="detail-page";

        $popup_page = [
        ];

        $modal_page = [
            "counsel_last_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/counsel', $data);
    }

    public function counselLast(): string
    {
        //return pageView('welcome_message');
        $header_class="flow-result";

        $popup_page = [
        ];

        $modal_page = [
            "counsel_last_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/counselLast', $data);
    }

    



}
