<?php

namespace App\Controllers;
use Config\Database;

class Home extends BaseController
{
    public function __construct()
    {
        // default 그룹으로 DB 연결
        $this->db = Database::connect('default');

        // 공용 함수(db_conn())를 사용하고 싶다면 아래처럼 사용 가능
        // $this->db = db_conn();
    }

    public function index(): string
    {
        helper(['region', 'insurance']);

        $header_class = "main-page";

        $popup_page = [];
        $modal_page = [];

        $regionOptions = $this->modalOptions('region_modal.php', true);
        $insuranceOptions = $this->modalOptions('insurance_modal.php', true);
        $languageOptions = $this->languageOptions();

        $adFcList = $this->mainRecommendFcList();
        $productFcList = $this->activeProductFcList();
        $languageFcList = $this->activeLanguageFcList();
        $reviewList = $this->activeReviewList();
        $insuranceInList = $this->activeInsuranceInList();
        $topBannerAds = $this->activeBannerAds('top');
        $bottomBannerAds = $this->activeBannerAds('bottom');

        return $this->renderView('main/index_pro', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "ad_fc_list" => $adFcList,
            "product_fc_list" => $productFcList,
            "language_fc_list" => $languageFcList,
            "review_list" => $reviewList,
            "insurance_in_list" => $insuranceInList,
            "region_options" => $regionOptions,
            "insurance_options" => $insuranceOptions,
            "language_options" => $languageOptions,
            "top_banner_ads" => $topBannerAds,
            "bottom_banner_ads" => $bottomBannerAds,
        ]);
    }

    public function index_pro(): string
    {
        helper(['region', 'insurance']);

        $header_class = "main-page";

        $popup_page = [];
        $modal_page = [];

        $regionOptions = $this->modalOptions('region_modal.php', true);
        $insuranceOptions = $this->modalOptions('insurance_modal.php', true);
        $languageOptions = $this->languageOptions();

        $adFcList = $this->mainRecommendFcList();
        $productFcList = $this->activeProductFcList();
        $languageFcList = $this->activeLanguageFcList();
        $reviewList = $this->activeReviewList();
        $insuranceInList = $this->activeInsuranceInList();
        $topBannerAds = $this->activeBannerAds('top');
        $bottomBannerAds = $this->activeBannerAds('bottom');

        return $this->renderView('main/index_pro', [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "ad_fc_list" => $adFcList,
            "product_fc_list" => $productFcList,
            "language_fc_list" => $languageFcList,
            "review_list" => $reviewList,
            "insurance_in_list" => $insuranceInList,
            "region_options" => $regionOptions,
            "insurance_options" => $insuranceOptions,
            "language_options" => $languageOptions,
            "top_banner_ads" => $topBannerAds,
            "bottom_banner_ads" => $bottomBannerAds,
        ]);
    }

    private function fcList(int $limit = 20): array
    {
        return $this->fcRows(null, $limit);
    }

    private function activeAdFcList(): array
    {
        return $this->fcRows(['region_fc'], 20);
    }

    private function mainRecommendFcList(): array
    {
        $adRows = $this->activeAdFcList();
        $generalRows = $this->fcRows(null, 20);

        if (empty($adRows)) {
            return $generalRows;
        }

        $merged = [];
        $seen = [];

        foreach ($adRows as $row) {
            $memberUid = (string) ($row['member_uid'] ?? '');
            if ($memberUid === '') {
                continue;
            }
            $seen[$memberUid] = true;
            $merged[] = $row;
        }

        foreach ($generalRows as $row) {
            $memberUid = (string) ($row['member_uid'] ?? '');
            if ($memberUid === '' || isset($seen[$memberUid])) {
                continue;
            }
            $merged[] = $row;
        }

        return $merged;
    }

    private function activeProductFcList(): array
    {
        $rows = $this->fcRows(['product_fc'], 12);
        if (!empty($rows)) {
            return $rows;
        }

        return $this->fcRows(null, 12);
    }

    private function activeLanguageFcList(): array
    {
        $rows = $this->fcRows(['language_fc'], 12);
        if (!empty($rows)) {
            return $rows;
        }

        return $this->fcRows(null, 12);
    }

    private function fcRows(?array $adTypes, int $limit): array
    {
        $db = $this->db;
        $today = date('Y-m-d');
        $reviewAggregate = $this->reviewAggregateSql($db);

        if ($adTypes !== null) {
            $builder = $db->table('ad_master ad')
                ->select('
                    ad.id AS ad_id,
                    ad.ad_type,
                    ad.region_code,
                    ad.insurance_type,
                    ad.language_code,
                    ad.start_date,
                    ad.end_date,
                    m.member_id,
                    m.member_uid,
                    m.name,
                    COALESCE(NULLIF(p.profile_image, ""), NULLIF(m.profile_image, "")) AS profile_image,
                    p.company,
                    p.company_sub,
                    p.ga,
                    p.language,
                    a.region,
                    a.insurance_types,
                    a.intro,
                    a.hero_line,
                    IFNULL(rv.rating, 0) AS rating,
                    IFNULL(rv.rating_count, 0) AS rating_count
                ')
                ->join('my_fc_member m', '(m.member_uid COLLATE utf8mb4_unicode_ci = ad.fc_member_id COLLATE utf8mb4_unicode_ci OR CAST(m.member_id AS CHAR) COLLATE utf8mb4_unicode_ci = ad.fc_member_id COLLATE utf8mb4_unicode_ci)', 'inner', false)
                ->whereIn('ad.ad_type', $adTypes)
                ->where('ad.status', 'approved')
                ->where('ad.start_date <=', $today)
                ->where('ad.end_date >=', $today)
                ->orderBy('RAND()', '', false);
        } else {
            $builder = $db->table('my_fc_member m')
                ->select('
                    NULL AS ad_id,
                    NULL AS ad_type,
                    NULL AS region_code,
                    NULL AS insurance_type,
                    NULL AS language_code,
                    NULL AS start_date,
                    NULL AS end_date,
                    m.member_id,
                    m.member_uid,
                    m.name,
                    COALESCE(NULLIF(p.profile_image, ""), NULLIF(m.profile_image, "")) AS profile_image,
                    p.company,
                    p.company_sub,
                    p.ga,
                    p.language,
                    a.region,
                    a.insurance_types,
                    a.intro,
                    a.hero_line,
                    IFNULL(rv.rating, 0) AS rating,
                    IFNULL(rv.rating_count, 0) AS rating_count
                ', false)
                ->orderBy('rating', 'DESC');
        }

        return $builder
            ->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'inner')
            ->join('my_fc_profile_activity a', 'a.member_uid = m.member_uid', 'left')
            ->join('(' . $reviewAggregate . ') rv', 'rv.fc_member_uid = m.member_uid', 'left', false)
            ->where('m.deleted_at IS NULL', null, false)
            ->where('m.member_type', 'FC')
            ->where('m.status', 'ACTIVE')
            ->where('m.fc_review_status', 'APPROVE')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    private function activeReviewList(): array
    {
        $rows = $this->reviewAdList();
        if (!empty($rows)) {
            return $rows;
        }

        return $this->recentReviewList(16);
    }

    private function reviewAdList(): array
    {
        $db = $this->db;
        $today = date('Y-m-d');

        $builder = $db->table('my_fc_counsel_review r')
            ->select('
                r.review_id,
                r.title,
                r.body,
                r.rating,
                r.created_at,
                m.name AS reviewer_name
            ')
            ->join('ad_master ad', 'ad.review_id = r.review_id', 'inner')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->where('ad.ad_type', 'review')
            ->where('ad.status', 'approved')
            ->where('ad.start_date <=', $today)
            ->where('ad.end_date >=', $today)
            ->where('r.deleted_at IS NULL', null, false)
            ->orderBy('RAND()', '', false)
            ->limit(16);

        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $builder->where('r.display_status', 'Y');
        }

        return $builder->get()->getResultArray();
    }

    private function recentReviewList(int $limit = 16): array
    {
        $db = $this->db;

        $builder = $db->table('my_fc_counsel_review r')
            ->select('
                r.review_id,
                r.title,
                r.body,
                r.rating,
                r.created_at,
                m.name AS reviewer_name
            ')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->where('r.deleted_at IS NULL', null, false)
            ->orderBy('r.review_id', 'DESC')
            ->limit($limit);

        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $builder->where('r.display_status', 'Y');
        }

        return $builder->get()->getResultArray();
    }

    private function activeInsuranceInList(): array
    {
        if (!$this->db->tableExists('my_fc_insurance_in_question')) {
            return [];
        }

        return $this->db->table('my_fc_insurance_in_question q')
            ->select('q.question_id, q.title, q.body, q.view_count, q.created_at, COUNT(a.answer_id) answer_count,
                SUBSTRING_INDEX(GROUP_CONCAT(m.name ORDER BY a.created_at ASC), ",", 1) first_fc_name', false)
            ->join('my_fc_insurance_in_answer a', "a.question_id = q.question_id AND a.status = 'DISPLAY' AND a.deleted_at IS NULL", 'left', false)
            ->join('my_fc_member m', 'm.member_uid = a.fc_member_uid', 'left')
            ->where('q.status', 'OPEN')->where('q.deleted_at', null)
            ->groupBy('q.question_id')->orderBy('q.created_at', 'DESC')->limit(8)
            ->get()->getResultArray();
    }

    private function activeBannerAds(string $position): array
    {
        $position = $position === 'bottom' ? 'bottom' : 'top';
        $today = date('Y-m-d');

        return $this->db
            ->table('ad_master')
            ->select('id, banner_image_url, banner_link_url, start_date, end_date, banner_position')
            ->where('ad_type', 'banner')
            ->where('status', 'approved')
            ->where('banner_position', $position)
            ->where('start_date <=', $today)
            ->where('end_date >=', $today)
            ->where('banner_image_url IS NOT NULL', null, false)
            ->where('banner_image_url !=', '')
            ->orderBy('RAND()', '', false)
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    private function reviewAggregateSql($db): string
    {
        $reviewBuilder = $db->table('my_fc_counsel_review')
            ->select('fc_member_uid, IFNULL(AVG(rating), 0) AS rating, COUNT(review_id) AS rating_count')
            ->where('deleted_at IS NULL', null, false)
            ->groupBy('fc_member_uid');

        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $reviewBuilder->where('display_status', 'Y');
        }

        return $reviewBuilder->getCompiledSelect();
    }

    private function modalOptions(string $fileName, bool $includeEmpty = false): array
    {
        $path = APPPATH . 'Views/modal/' . $fileName;
        if (!is_file($path)) {
            return [];
        }

        $html = (string) file_get_contents($path);
        preg_match_all(
            '/<button[^>]*class="[^"]*c-modal-option[^"]*"[^>]*data-value="([^"]*)"[^>]*>.*?<span[^>]*class="[^"]*c-modal-option-label[^"]*"[^>]*>(.*?)<\/span>/is',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        $options = [];
        foreach ($matches as $match) {
            $value = html_entity_decode(trim(strip_tags($match[1])), ENT_QUOTES, 'UTF-8');
            $label = html_entity_decode(trim(strip_tags($match[2])), ENT_QUOTES, 'UTF-8');
            if (!$includeEmpty && ($value === '' || $value === 'all')) {
                continue;
            }
            if ($value === '') {
                $value = 'all';
            }
            $options[$value] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return array_values($options);
    }

    private function languageOptions(): array
    {
        $options = array_merge(
            [[
                'value' => 'all',
                'label' => '전체',
                'icon' => '',
            ]],
            $this->modalOptions('fc_lang_modal.php', false)
        );
        $iconMap = [
            '영어' => 'ic-flag-us.png',
            '중국어' => 'ic-flag-cn.png',
            '베트남어' => 'ic-flag-vn.png',
            '태국어' => 'ic-flag-th.png',
            '필리핀어' => 'ic-flag-ph.png',
            '일본어' => 'ic-flag-jp.png',
            '수어' => 'ic-hand.png',
        ];

        foreach ($options as &$option) {
            $option['icon'] = $iconMap[$option['value']] ?? '';
        }
        unset($option);

        return $options;
    }

    public function recommend()
    {
        helper(['region', 'insurance']);

        $db = \Config\Database::connect();
        $reviewJoin = 'r.fc_member_uid = m.member_uid';
        if ($db->fieldExists('display_status', 'my_fc_counsel_review')) {
            $reviewJoin .= " AND r.deleted_at IS NULL AND r.display_status = 'Y'";
        } else {
            $reviewJoin .= ' AND r.deleted_at IS NULL';
        }

        $region = $this->request->getGet('region'); // ex: "서울,경기"

        $builder = $db->table('my_fc_member m');

        $builder->select('
            m.member_uid,
            m.name,
            COALESCE(NULLIF(p.profile_image, ""), NULLIF(m.profile_image, "")) AS profile_image,

            p.company,
            a.region,
            a.insurance_types,

            IFNULL(AVG(r.rating), 0) AS rating,
            COUNT(r.review_id) AS rating_count
        ');

        $builder->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'inner');
        $builder->join('my_fc_profile_activity a', 'a.member_uid = m.member_uid', 'left');
        $builder->join('my_fc_counsel_review r', $reviewJoin, 'left', false);

        $builder->groupBy('m.member_uid');

        $builder->where('m.member_type', 'FC');
        $builder->where('m.status', 'ACTIVE');
        $builder->where('m.fc_review_status', 'APPROVE');
        $builder->where('m.deleted_at IS NULL', null, false);

        // ⭐ 지역 필터
        if (!empty($region)) {
            $regions = explode(',', $region);

            $builder->groupStart();
            foreach ($regions as $i => $r) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $builder->$method(
                    "FIND_IN_SET(" . $db->escape(trim($r)) . ", a.region) > 0",
                    null,
                    false
                );
            }
            $builder->groupEnd();
        }

        // ⭐ 추천 정렬
        $builder->orderBy('RAND()', '', false);
        $builder->limit(20);

        $list = $builder->get()->getResultArray();

        // HTML로 내려줌 (swiper용)
        $html = '';

        foreach ($list as $row) {

            $regions = explode(',', $row['region'] ?? '');
            $region_label = fc_region_label(trim($regions[0] ?? ''));

            $items = array_slice(explode(',', $row['insurance_types'] ?? ''), 0, 6);

            $tags = '';
            foreach ($items as $item) {
                $item = trim($item);
                if (!$item) continue;
                $tags .= '<span>' . fc_insurance_label($item) . '</span>';
            }

            $img = profile_image_url($row['profile_image'] ?? '');
            $profileMedia = $img !== ''
                ? '<img src="' . esc($img) . '" class="avatar" alt="" onerror="this.removeAttribute(\'src\'); this.classList.add(\'is-empty\');" />'
                : '<span class="avatar is-empty" aria-hidden="true"></span>';

            $html .= '
            <div class="swiper-slide">
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view/?uid=' . esc($row['member_uid']) . '">

                            <div class="profile">

                                ' . $profileMedia . '

                                <div>

                                    <p class="profile-name">' . esc($row['name']) . '</p>

                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span>
                                        ' . number_format($row['rating'], 1) . '
                                        <span class="c-rate-count">
                                            (' . number_format($row['rating_count']) . ')
                                        </span>
                                    </p>

                                    <p class="c-dot-line">
                                        <span>' . esc($row['company']) . '</span>
                                        <span class="location"><span>' . esc($region_label) . '</span></span>
                                    </p>

                                    <div class="list-tags">
                                        ' . $tags . '
                                    </div>

                                </div>
                            </div>

                        </a>
                    </div>
                </article>
            </div>';
        }

        return $this->response->setJSON([
            'html' => $html
        ]);
    }
}
