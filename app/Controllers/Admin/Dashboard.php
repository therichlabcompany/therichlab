<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminExcelExporter;
use Config\Database;

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        // default 그룹으로 DB 연결
        $this->db = Database::connect('default');

        // 공용 함수(db_conn())를 사용하고 싶다면 아래처럼 사용 가능
        // $this->db = db_conn();
    }


    public function index()
    {
        $range = $this->dashboardRange();
        $counts = $this->dashboardCounts($range);

        return $this->renderAdminView('admin/main/main', [
            'title' => '관리자 대시보드',
            'startDate' => $range['start_date'],
            'endDate' => $range['end_date'],
            'prevStartDate' => $this->shiftDate($range['start_date'], '-1 month'),
            'prevEndDate' => $this->shiftDate($range['end_date'], '-1 month'),
            'nextStartDate' => $this->shiftDate($range['start_date'], '+1 month'),
            'nextEndDate' => $this->shiftDate($range['end_date'], '+1 month'),
            'counts' => $counts,
            'members' => $this->recentMembers($range),
            'counsels' => $this->recentCounsels($range),
            'reviews' => $this->recentReviews($range),
            'ads' => $this->recentAds($range),
            'exportUrl' => base_url('admin/dashboard/export?' . http_build_query([
                'start_date' => $range['start_date'],
                'end_date' => $range['end_date'],
            ])),
        ]);
    }

    public function export()
    {
        $range = $this->dashboardRange();
        $counts = $this->dashboardCounts($range);
        $summaryText = $this->dashboardSummaryText($counts);

        $memberRows = $this->exportMembers($range);
        $counselRows = $this->exportCounsels($range);
        $adRows = $this->exportAds($range);
        $reviewRows = $this->exportReviews($range);

        $sheets = [
            [
                'name' => '가입회원',
                'meta' => [
                    ['대시보드 기간', $range['start_date'] . ' ~ ' . $range['end_date']],
                    ['대시보드 집계', $summaryText],
                    ['총 건수', number_format(count($memberRows))],
                ],
                'headers' => $this->memberExportHeaders(),
                'rows' => array_map([$this, 'memberExportRow'], $memberRows),
            ],
            [
                'name' => '상담요청(대기)',
                'meta' => [
                    ['대시보드 기간', $range['start_date'] . ' ~ ' . $range['end_date']],
                    ['대시보드 집계', $summaryText],
                    ['총 건수', number_format(count($counselRows))],
                ],
                'headers' => $this->counselExportHeaders(),
                'rows' => array_map([$this, 'counselExportRow'], $counselRows),
            ],
            [
                'name' => '광고신청',
                'meta' => [
                    ['대시보드 기간', $range['start_date'] . ' ~ ' . $range['end_date']],
                    ['대시보드 집계', $summaryText],
                    ['총 건수', number_format(count($adRows))],
                ],
                'headers' => $this->adExportHeaders(),
                'rows' => array_map([$this, 'adExportRow'], $adRows),
            ],
            [
                'name' => '신규등록후기',
                'meta' => [
                    ['대시보드 기간', $range['start_date'] . ' ~ ' . $range['end_date']],
                    ['대시보드 집계', $summaryText],
                    ['총 건수', number_format(count($reviewRows))],
                ],
                'headers' => $this->reviewExportHeaders(),
                'rows' => array_map([$this, 'reviewExportRow'], $reviewRows),
            ],
        ];

        $xlsx = $this->buildXlsx($sheets);
        $fileName = 'dashboard-' . $range['start_date'] . '-' . $range['end_date'] . '.xlsx';

        return $this->response
            ->download($fileName, $xlsx)
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function dashboardRange(): array
    {
        $today = date('Y-m-d');
        $defaultStart = date('Y-m-d', strtotime('-1 month'));
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = $defaultStart;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = $today;
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start' => $startDate . ' 00:00:00',
            'end' => $endDate . ' 23:59:59',
        ];
    }

    private function shiftDate(string $date, string $interval): string
    {
        return date('Y-m-d', strtotime($date . ' ' . $interval));
    }

    private function dashboardCounts(array $range): array
    {
        return [
            'newUsers' => $this->memberCount('USER', $range),
            'newFcMembers' => $this->memberCount('FC', $range),
            'counselRequests' => $this->tableDateCount('my_fc_counsel', 'created_at', $range, ['status' => 'REQUEST']),
            'newReviews' => $this->tableDateCount('my_fc_counsel_review', 'created_at', $range),
            'newAds' => $this->tableDateCount('ad_master', 'created_at', $range),
            'reviewedRequests' => $this->tableDateCount('my_fc_reviewed', 'created_at', $range, ['status' => 'WAIT']),
            'normalAds' => $this->adCount($range, false),
            'bannerAds' => $this->adCount($range, true),
        ];
    }

    private function dashboardSummaryText(array $counts): string
    {
        return implode(' / ', [
            '신규 개인 회원 ' . number_format((int) ($counts['newUsers'] ?? 0)),
            '신규 FC 회원 ' . number_format((int) ($counts['newFcMembers'] ?? 0)),
            '상담요청(대기) ' . number_format((int) ($counts['counselRequests'] ?? 0)),
            '신규 등록 후기 ' . number_format((int) ($counts['newReviews'] ?? 0)),
            '신규 광고 요청 ' . number_format((int) ($counts['newAds'] ?? 0)),
            '심의필 승인 요청 ' . number_format((int) ($counts['reviewedRequests'] ?? 0)),
        ]);
    }

    private function memberCount(string $type, array $range): int
    {
        return (int) $this->db->table('my_fc_member')
            ->where('member_type', $type)
            ->where('deleted_at', null)
            ->where('created_at >=', $range['start'])
            ->where('created_at <=', $range['end'])
            ->countAllResults();
    }

    private function tableDateCount(string $table, string $dateColumn, array $range, array $where = []): int
    {
        if (! $this->db->tableExists($table)) {
            return 0;
        }

        $builder = $this->db->table($table)
            ->where($dateColumn . ' >=', $range['start'])
            ->where($dateColumn . ' <=', $range['end']);

        if ($this->db->fieldExists('deleted_at', $table)) {
            $builder->where('deleted_at', null);
        }

        foreach ($where as $column => $value) {
            $builder->where($column, $value);
        }

        return (int) $builder->countAllResults();
    }

    private function adCount(array $range, bool $banner): int
    {
        $builder = $this->db->table('ad_master')
            ->where('created_at >=', $range['start'])
            ->where('created_at <=', $range['end']);

        if ($banner) {
            $builder->where('ad_type', 'banner');
        } else {
            $builder->whereIn('ad_type', ['region_fc', 'product_fc', 'review', 'language_fc']);
        }

        return (int) $builder->countAllResults();
    }

    private function recentMembers(array $range, int $limit = 8): array
    {
        return $this->memberRows($range, $limit, false);
    }

    private function memberRows(array $range, int $limit = 0, bool $exportMode = false): array
    {
        $builder = $this->db->table('my_fc_member m')
            ->select(
                'm.member_id, m.member_uid, m.member_type, m.email, m.phone, m.phone_verified, m.name, m.birth, m.gender, m.nickname, m.profile_image, m.status, m.login_fail_count, m.password_reset_at, m.last_login_at, m.agree_age, m.agree_terms, m.agree_privacy, m.agree_marketing, m.join_ip, m.admin_memo, m.created_at, m.updated_at, m.deleted_at, m.app_platform, m.app_token_expire_at, m.app_token_updated_at, m.fc_step, m.fc_review_status,'
                . ' p.company, p.company_sub, p.ga, p.position, p.license_date, p.license_no, p.time_from, p.time_to, p.language, p.profile_image AS fc_profile_image, p.step AS profile_step, p.view_count AS profile_view_count,'
                . ' a.region, a.insurance_types, a.hero_line, a.intro, a.career,'
                . ' s.story_video, s.story_image,'
                . ' r.deliberation_no, r.approval_start, r.approval_end, r.deliberation_opinion, r.deliberation_file, r.status AS reviewed_status, r.reject_reason AS reviewed_reject_reason, r.approve_admin_uid, r.approve_at, r.created_at AS reviewed_created_at, r.updated_at AS reviewed_updated_at,'
                . ' (SELECT COUNT(*) FROM my_fc_counsel c WHERE c.member_uid = m.member_uid AND c.deleted_at IS NULL) AS counsel_count,'
                . ' (SELECT COUNT(*) FROM my_fc_counsel_review rv WHERE rv.fc_member_uid = m.member_uid AND rv.deleted_at IS NULL) AS review_count,'
                . ' (SELECT COUNT(*) FROM my_fc_member_security ms WHERE ms.member_uid = m.member_uid AND ms.deleted_at IS NULL) AS security_count,'
                . ' (SELECT COUNT(*) FROM my_fc_profile_activity_item ai WHERE ai.member_uid = m.member_uid) AS activity_item_count,'
                . ' (SELECT COUNT(*) FROM my_fc_profile_story_image si WHERE si.member_uid = m.member_uid) AS story_image_count'
            )
            ->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'left')
            ->join('my_fc_profile_activity a', 'a.member_uid = m.member_uid', 'left')
            ->join('my_fc_profile_story s', 's.member_uid = m.member_uid', 'left')
            ->join('my_fc_reviewed r', 'r.member_uid = m.member_uid', 'left')
            ->where('m.deleted_at', null)
            ->where('m.created_at >=', $range['start'])
            ->where('m.created_at <=', $range['end'])
            ->orderBy('m.member_id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['member_type_label'] = ($row['member_type'] ?? '') === 'FC' ? 'FC' : '개인';
            $row['detail_url'] = base_url(($row['member_type'] ?? '') === 'FC' ? 'admin/fc-members/' . (int) $row['member_id'] : 'admin/members/' . (int) $row['member_id']);
            $row['image_path'] = ($row['member_type'] ?? '') === 'FC' ? ($row['fc_profile_image'] ?? $row['profile_image'] ?? '') : '';
        }

        return $rows;
    }

    private function recentCounsels(array $range, int $limit = 8): array
    {
        return $this->counselRows($range, $limit, false);
    }

    private function counselRows(array $range, int $limit = 0, bool $exportMode = false): array
    {
        $builder = $this->db->table('my_fc_counsel c')
            ->select(
                'c.counsel_id, c.counsel_uid, c.fc_member_uid, c.member_uid, c.name AS user_name, c.email AS user_email, c.phone AS user_phone, c.reserve_datetime, c.content, c.status, c.reject_reason, c.created_at, c.updated_at, c.deleted_at,'
                . ' um.member_id AS user_member_id, um.member_type AS user_member_type, um.status AS user_status,'
                . ' fm.member_id AS fc_member_id, fm.member_uid AS fc_member_uid_real, fm.name AS fc_name, fm.email AS fc_email, fm.phone AS fc_phone, fm.status AS fc_status, fm.fc_review_status AS fc_review_status,'
                . ' fp.company, fp.company_sub, fp.ga, fp.position, fp.license_no, fp.language, fp.profile_image AS fc_profile_image,'
                . ' (SELECT COUNT(*) FROM my_fc_counsel_file f WHERE f.counsel_uid = c.counsel_uid) AS file_count,'
                . ' (SELECT GROUP_CONCAT(CONCAT(f.original_name, \' [\', f.file_type, \'/\', IFNULL(f.file_ext, \'-\'), \'/\', IFNULL(f.file_size, 0), \'B]\') ORDER BY f.file_id SEPARATOR \' | \') FROM my_fc_counsel_file f WHERE f.counsel_uid = c.counsel_uid) AS file_list'
            )
            ->join('my_fc_member um', 'um.member_uid = c.member_uid', 'left')
            ->join('my_fc_member fm', 'fm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = c.fc_member_uid', 'left')
            ->where('c.deleted_at', null)
            ->where('c.created_at >=', $range['start'])
            ->where('c.created_at <=', $range['end'])
            ->orderBy('c.counsel_id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    private function recentReviews(array $range, int $limit = 8): array
    {
        return $this->reviewRows($range, $limit, false);
    }

    private function reviewRows(array $range, int $limit = 0, bool $exportMode = false): array
    {
        $builder = $this->db->table('my_fc_counsel_review r')
            ->select(
                'r.review_id, r.counsel_uid, r.fc_member_uid, r.member_uid, r.rating, r.title, r.body, r.created_at, r.updated_at, r.deleted_at, r.view_count, r.display_status,'
                . ' c.reserve_datetime AS counsel_reserve_datetime, c.content AS counsel_content, c.status AS counsel_status, c.reject_reason AS counsel_reject_reason, c.created_at AS counsel_created_at, c.updated_at AS counsel_updated_at,'
                . ' u.member_id AS user_member_id, u.member_type AS user_member_type, u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.status AS user_status,'
                . ' f.member_id AS fc_member_id, f.member_type AS fc_member_type, f.name AS fc_name, f.email AS fc_email, f.phone AS fc_phone, f.status AS fc_status, f.fc_review_status AS fc_review_status,'
                . ' p.profile_image AS fc_profile_image'
            )
            ->join('my_fc_member u', 'u.member_uid = r.member_uid', 'left')
            ->join('my_fc_member f', 'f.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = r.fc_member_uid', 'left')
            ->join('my_fc_counsel c', 'c.counsel_uid = r.counsel_uid', 'left')
            ->where('r.deleted_at', null)
            ->where('r.created_at >=', $range['start'])
            ->where('r.created_at <=', $range['end'])
            ->orderBy('r.review_id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    private function recentAds(array $range, int $limit = 8): array
    {
        return $this->adRows($range, $limit, false);
    }

    private function adRows(array $range, int $limit = 0, bool $exportMode = false): array
    {
        $builder = $this->db->table('ad_master a')
            ->select(
                'a.id, a.fc_member_id, a.ad_type, a.status, a.amount, a.start_date, a.end_date, a.approved_at, a.approved_by, a.click_count, a.created_at, a.updated_at,'
                . ' a.region_code, a.banner_image_url, a.banner_link_url, a.banner_need_design, a.insurance_type, a.review_id, a.language_code, a.banner_position,'
                . ' m.member_id, m.member_uid, m.member_type, m.name, m.email, m.phone, m.status AS member_status, m.created_at AS member_created_at'
            )
            ->join('my_fc_member m', '(m.member_uid COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci OR CAST(m.member_id AS CHAR) COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci)', 'left', false)
            ->where('a.created_at >=', $range['start'])
            ->where('a.created_at <=', $range['end'])
            ->orderBy('a.id', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    private function adTypeLabel(string $type, string $position = ''): string
    {
        if ($type === 'banner') {
            return [
                'top' => '상단 배너 광고',
                'bottom' => '하단 배너 광고',
            ][$position] ?? '배너 광고';
        }

        return [
            'region_fc' => '지역별 광고',
            'product_fc' => '상담가능 상품별 광고',
            'review' => '후기 광고',
            'language_fc' => '언어별 광고',
        ][$type] ?? $type;
    }

    private function exportMembers(array $range): array
    {
        return $this->memberRows($range, 0, true);
    }

    private function exportCounsels(array $range): array
    {
        return $this->counselRows($range, 0, true);
    }

    private function exportReviews(array $range): array
    {
        return $this->reviewRows($range, 0, true);
    }

    private function exportAds(array $range): array
    {
        return $this->adRows($range, 0, true);
    }

    private function memberExportHeaders(): array
    {
        return [
            '회원번호',
            '회원UID',
            '회원구분',
            '이름',
            '이메일',
            '휴대폰',
            '휴대폰 인증',
            '생년월일',
            '성별',
            '닉네임',
            '회원상태',
            '로그인 실패 횟수',
            '비밀번호 재설정일',
            '최종 로그인',
            '약관 동의',
            '개인정보 동의',
            '마케팅 동의',
            '가입 IP',
            '관리자 메모',
            '가입일',
            '수정일',
            '앱 플랫폼',
            'FC 단계',
            '심의필 상태',
            '원수사',
            '추가 보험사',
            'GA',
            '직책',
            '자격 취득일',
            '등록번호',
            '상담 시작',
            '상담 종료',
            '상담 언어',
            'FC 프로필 이미지',
            '프로필 단계',
            '프로필 조회수',
            '활동지역',
            '운영 보험항목',
            '전문 분야',
            '자기소개',
            '경력사항',
            '활동 영상',
            '활동 대표 이미지',
            '심의필 번호',
            '심의필 승인 시작',
            '심의필 승인 종료',
            '심의필 의견',
            '심의필 파일',
            '심의필 결과',
            '심의필 거부사유',
            '심의필 승인관리자',
            '심의필 승인일',
            '상담건수',
            '후기건수',
            '증권파일건수',
            '활동 항목수',
            '활동 이미지수',
        ];
    }

    private function memberExportRow(array $row): array
    {
        return [
            $row['member_id'] ?? '',
            $row['member_uid'] ?? '',
            $this->memberTypeLabel((string) ($row['member_type'] ?? '')),
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['phone'] ?? '',
            $this->ynLabel($row['phone_verified'] ?? ''),
            $this->formatBirthday($row['birth'] ?? ''),
            $this->genderLabel($row['gender'] ?? ''),
            $row['nickname'] ?? '',
            $row['status'] ?? '',
            $row['login_fail_count'] ?? 0,
            $row['password_reset_at'] ?? '',
            $row['last_login_at'] ?? '',
            $this->boolLabel($row['agree_terms'] ?? 0),
            $this->boolLabel($row['agree_privacy'] ?? 0),
            $this->boolLabel($row['agree_marketing'] ?? 0),
            $row['join_ip'] ?? '',
            $row['admin_memo'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
            $row['app_platform'] ?? '',
            $row['fc_step'] ?? '',
            $row['fc_review_status'] ?? '',
            $row['company'] ?? '',
            $row['company_sub'] ?? '',
            $row['ga'] ?? '',
            $row['position'] ?? '',
            $row['license_date'] ?? '',
            $row['license_no'] ?? '',
            $this->timeLabel($row['time_from'] ?? ''),
            $this->timeLabel($row['time_to'] ?? ''),
            $row['language'] ?? '',
            $row['fc_profile_image'] ?? ($row['profile_image'] ?? ''),
            $row['profile_step'] ?? '',
            $row['profile_view_count'] ?? 0,
            $row['region'] ?? '',
            $row['insurance_types'] ?? '',
            $row['hero_line'] ?? '',
            $row['intro'] ?? '',
            $row['career'] ?? '',
            $row['story_video'] ?? '',
            $row['story_image'] ?? '',
            $row['deliberation_no'] ?? '',
            $row['approval_start'] ?? '',
            $row['approval_end'] ?? '',
            $row['deliberation_opinion'] ?? '',
            $row['deliberation_file'] ?? '',
            $row['reviewed_status'] ?? '',
            $row['reviewed_reject_reason'] ?? '',
            $row['approve_admin_uid'] ?? '',
            $row['approve_at'] ?? '',
            $row['counsel_count'] ?? 0,
            $row['review_count'] ?? 0,
            $row['security_count'] ?? 0,
            $row['activity_item_count'] ?? 0,
            $row['story_image_count'] ?? 0,
        ];
    }

    private function counselExportHeaders(): array
    {
        return [
            '상담ID',
            '상담UID',
            '상담상태',
            '신청회원명',
            '신청회원이메일',
            '신청회원휴대폰',
            '신청회원UID',
            'FC회원명',
            'FC회원이메일',
            'FC회원휴대폰',
            'FC회원UID',
            'FC상태',
            'FC심의필상태',
            'FC 원수사',
            'FC GA',
            'FC 직책',
            'FC 등록번호',
            'FC 상담언어',
            'FC 프로필 이미지',
            '희망 상담일시',
            '상담내용',
            '거부사유',
            '첨부파일 수',
            '첨부파일 상세',
            '생성일',
            '수정일',
        ];
    }

    private function counselExportRow(array $row): array
    {
        return [
            $row['counsel_id'] ?? '',
            $row['counsel_uid'] ?? '',
            $row['status'] ?? '',
            $row['user_name'] ?? '',
            $row['user_email'] ?? '',
            $row['user_phone'] ?? '',
            $row['member_uid'] ?? '',
            $row['fc_name'] ?? '',
            $row['fc_email'] ?? '',
            $row['fc_phone'] ?? '',
            $row['fc_member_uid'] ?? '',
            $row['fc_status'] ?? '',
            $row['fc_review_status'] ?? '',
            $row['company'] ?? '',
            $row['ga'] ?? '',
            $row['position'] ?? '',
            $row['license_no'] ?? '',
            $row['language'] ?? '',
            $row['fc_profile_image'] ?? '',
            $row['reserve_datetime'] ?? '',
            $row['content'] ?? '',
            $row['reject_reason'] ?? '',
            $row['file_count'] ?? 0,
            $row['file_list'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
        ];
    }

    private function adExportHeaders(): array
    {
        return [
            '광고ID',
            '광고주 UID',
            '광고유형',
            '광고상태',
            '광고금액',
            '시작일',
            '종료일',
            '승인일시',
            '승인관리자',
            '클릭수',
            '생성일',
            '수정일',
            '지역코드',
            '배너이미지URL',
            '배너링크URL',
            '배너제작요청',
            '보험상품',
            '후기ID',
            '언어코드',
            '배너위치',
            '신청회원명',
            '신청회원이메일',
            '신청회원휴대폰',
            '신청회원UID',
            '신청회원상태',
        ];
    }

    private function adExportRow(array $row): array
    {
        return [
            $row['id'] ?? '',
            $row['fc_member_id'] ?? '',
            $this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? '')),
            $row['status'] ?? '',
            $row['amount'] ?? 0,
            $row['start_date'] ?? '',
            $row['end_date'] ?? '',
            $row['approved_at'] ?? '',
            $row['approved_by'] ?? '',
            $row['click_count'] ?? 0,
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
            $row['region_code'] ?? '',
            $row['banner_image_url'] ?? '',
            $row['banner_link_url'] ?? '',
            $this->boolLabel($row['banner_need_design'] ?? 0),
            $row['insurance_type'] ?? '',
            $row['review_id'] ?? '',
            $row['language_code'] ?? '',
            $row['banner_position'] ?? '',
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['phone'] ?? '',
            $row['member_uid'] ?? '',
            $row['member_status'] ?? '',
        ];
    }

    private function reviewExportHeaders(): array
    {
        return [
            '후기ID',
            '상담UID',
            '평점',
            '제목',
            '내용',
            '노출상태',
            '조회수',
            '생성일',
            '수정일',
            '삭제일',
            '신청회원명',
            '신청회원이메일',
            '신청회원휴대폰',
            '신청회원UID',
            'FC회원명',
            'FC회원이메일',
            'FC회원휴대폰',
            'FC회원UID',
            'FC상태',
            'FC심의필상태',
            'FC 프로필 이미지',
            '상담 희망일시',
            '상담내용',
            '상담상태',
            '상담 거부사유',
            '상담 생성일',
            '상담 수정일',
        ];
    }

    private function reviewExportRow(array $row): array
    {
        return [
            $row['review_id'] ?? '',
            $row['counsel_uid'] ?? '',
            $row['rating'] ?? '',
            $row['title'] ?? '',
            $row['body'] ?? '',
            $row['display_status'] ?? '',
            $row['view_count'] ?? 0,
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
            $row['deleted_at'] ?? '',
            $row['user_name'] ?? '',
            $row['user_email'] ?? '',
            $row['user_phone'] ?? '',
            $row['member_uid'] ?? '',
            $row['fc_name'] ?? '',
            $row['fc_email'] ?? '',
            $row['fc_phone'] ?? '',
            $row['fc_member_uid'] ?? '',
            $row['fc_status'] ?? '',
            $row['fc_review_status'] ?? '',
            $row['fc_profile_image'] ?? '',
            $row['counsel_reserve_datetime'] ?? '',
            $row['counsel_content'] ?? '',
            $row['counsel_status'] ?? '',
            $row['counsel_reject_reason'] ?? '',
            $row['counsel_created_at'] ?? '',
            $row['counsel_updated_at'] ?? '',
        ];
    }

    private function memberTypeLabel(string $type): string
    {
        return $type === 'FC' ? 'FC' : '개인';
    }

    private function ynLabel($value): string
    {
        return ((string) $value) === 'Y' ? 'Y' : 'N';
    }

    private function boolLabel($value): string
    {
        return ((string) $value === '1' || (string) $value === 'Y') ? 'Y' : 'N';
    }

    private function genderLabel($value): string
    {
        return match ((string) $value) {
            'M' => '남',
            'F' => '여',
            default => '',
        };
    }

    private function formatBirthday($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strlen($value) !== 8) {
            return $value;
        }

        return substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
    }

    private function timeLabel($value): string
    {
        return $value === '' || $value === null ? '' : sprintf('%02d:00', (int) $value);
    }

    private function buildXlsx(array $sheets): string
    {
        return (new AdminExcelExporter())->build($sheets);

        $tempFile = tempnam(sys_get_temp_dir(), 'dashboard_xlsx_');
        if ($tempFile === false) {
            throw new \RuntimeException('엑셀 파일 임시 경로를 생성할 수 없습니다.');
        }

        $zipPath = $tempFile . '.xlsx';
        rename($tempFile, $zipPath);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            throw new \RuntimeException('엑셀 파일을 생성할 수 없습니다.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypesXml(count($sheets)));
        $zip->addFromString('_rels/.rels', $this->xlsxRelsXml());
        $zip->addFromString('docProps/app.xml', $this->xlsxAppXml($sheets));
        $zip->addFromString('docProps/core.xml', $this->xlsxCoreXml());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbookXml($sheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRelsXml(count($sheets)));
        $zip->addFromString('xl/styles.xml', $this->xlsxStylesXml());

        foreach ($sheets as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $this->xlsxSheetXml($sheet));
        }

        $zip->close();

        $binary = file_get_contents($zipPath);
        @unlink($zipPath);

        if ($binary === false) {
            throw new \RuntimeException('엑셀 파일을 읽을 수 없습니다.');
        }

        return $binary;
    }

    private function xlsxContentTypesXml(int $sheetCount): string
    {
        $overrides = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function xlsxRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function xlsxAppXml(array $sheets): string
    {
        $titles = '';
        foreach ($sheets as $sheet) {
            $titles .= '<vt:lpstr>' . $this->xmlEscape((string) $sheet['name']) . '</vt:lpstr>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>MyFC</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>' . count($sheets) . '</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="' . count($sheets) . '" baseType="lpstr">' . $titles . '</vt:vector></TitlesOfParts>'
            . '<Company>MyFC</Company>'
            . '<LinksUpToDate>false</LinksUpToDate>'
            . '<SharedDoc>false</SharedDoc>'
            . '<HyperlinksChanged>false</HyperlinksChanged>'
            . '<AppVersion>16.0300</AppVersion>'
            . '</Properties>';
    }

    private function xlsxCoreXml(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . ' xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>MyFC</dc:creator>'
            . '<cp:lastModifiedBy>MyFC</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function xlsxWorkbookXml(array $sheets): string
    {
        $sheetXml = '';
        foreach ($sheets as $index => $sheet) {
            $sheetXml .= '<sheet name="' . $this->xmlEscape((string) $sheet['name']) . '" sheetId="' . ($index + 1) . '" r:id="rId' . ($index + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetXml . '</sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRelsXml(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . ($sheetCount + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private function xlsxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><color rgb="FF000000"/><name val="Arial"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function xlsxSheetXml(array $sheet): string
    {
        $rows = [];
        $rowIndex = 1;

        foreach ($sheet['meta'] ?? [] as $metaRow) {
            $rows[] = $this->xlsxRow($rowIndex++, $metaRow);
        }

        $headers = $sheet['headers'] ?? [];
        $rows[] = $this->xlsxRow($rowIndex++, $headers);

        foreach ($sheet['rows'] ?? [] as $dataRow) {
            $rows[] = $this->xlsxRow($rowIndex++, $dataRow);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $rows) . '</sheetData>'
            . '</worksheet>';
    }

    private function xlsxRow(int $rowNumber, array $values): string
    {
        $cells = [];
        foreach (array_values($values) as $index => $value) {
            $cells[] = $this->xlsxCell($this->columnLetter($index + 1) . $rowNumber, $value);
        }

        return '<row r="' . $rowNumber . '">' . implode('', $cells) . '</row>';
    }

    private function xlsxCell(string $reference, $value): string
    {
        if ($value === null) {
            $value = '';
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        if (is_numeric($value) && !is_string($value)) {
            return '<c r="' . $reference . '"><v>' . $value . '</v></c>';
        }

        $text = $this->normalizeExportText($value);

        return '<c r="' . $reference . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xmlEscape($text) . '</t></is></c>';
    }

    private function normalizeExportText($value): string
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                $text = $this->normalizeExportText($item);
                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            return implode(', ', $parts);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $text = trim((string) $value);
        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function columnLetter(int $index): string
    {
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }
}
