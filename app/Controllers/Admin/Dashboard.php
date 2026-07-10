<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['구분', '항목', '값']);

        fputcsv($handle, ['오늘의 데이터', '신규 개인 회원', $counts['newUsers']]);
        fputcsv($handle, ['오늘의 데이터', '신규 FC 회원', $counts['newFcMembers']]);
        fputcsv($handle, ['오늘의 데이터', '상담 요청', $counts['counselRequests']]);
        fputcsv($handle, ['오늘의 데이터', '신규 등록 후기', $counts['newReviews']]);
        fputcsv($handle, ['오늘의 데이터', '신규 광고 요청', $counts['newAds']]);
        fputcsv($handle, ['오늘의 데이터', '심의필 승인 요청', $counts['reviewedRequests']]);
        fputcsv($handle, ['오늘의 데이터', '일반 광고 요청', $counts['normalAds']]);
        fputcsv($handle, ['오늘의 데이터', '배너 광고 요청', $counts['bannerAds']]);

        foreach ($this->recentMembers($range) as $row) {
            fputcsv($handle, ['가입 회원', $row['member_type_label'], ($row['name'] ?? '') . ' / ' . ($row['email'] ?? '')]);
        }

        foreach ($this->recentCounsels($range) as $row) {
            fputcsv($handle, ['상담 요청', $row['user_name'] ?? '', $row['fc_name'] ?? '']);
        }

        foreach ($this->recentAds($range) as $row) {
            fputcsv($handle, ['광고 요청', $this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? '')), ($row['name'] ?? '') . ' / ' . ($row['amount'] ?? 0)]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="dashboard-' . $range['start_date'] . '-' . $range['end_date'] . '.csv"')
            ->setBody($csv);
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

    private function recentMembers(array $range): array
    {
        $rows = $this->db->table('my_fc_member m')
            ->select('m.member_id, m.member_uid, m.member_type, m.name, m.email, m.profile_image, m.created_at, p.profile_image AS fc_profile_image')
            ->join('my_fc_profile p', 'p.member_uid = m.member_uid', 'left')
            ->where('m.deleted_at', null)
            ->where('m.created_at >=', $range['start'])
            ->where('m.created_at <=', $range['end'])
            ->orderBy('m.member_id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['member_type_label'] = ($row['member_type'] ?? '') === 'FC' ? 'FC' : '개인';
            $row['detail_url'] = base_url(($row['member_type'] ?? '') === 'FC' ? 'admin/fc-members/' . (int) $row['member_id'] : 'admin/members/' . (int) $row['member_id']);
            $row['image_path'] = ($row['member_type'] ?? '') === 'FC' ? ($row['fc_profile_image'] ?? $row['profile_image'] ?? '') : '';
        }

        return $rows;
    }

    private function recentCounsels(array $range): array
    {
        return $this->db->table('my_fc_counsel c')
            ->select('c.counsel_id, c.name AS user_name, c.email AS user_email, c.created_at, fm.member_id AS fc_id, fm.name AS fc_name, fp.profile_image AS fc_profile_image')
            ->join('my_fc_member fm', 'fm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_profile fp', 'fp.member_uid = c.fc_member_uid', 'left')
            ->where('c.deleted_at', null)
            ->where('c.created_at >=', $range['start'])
            ->where('c.created_at <=', $range['end'])
            ->orderBy('c.counsel_id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
    }

    private function recentReviews(array $range): array
    {
        return $this->db->table('my_fc_counsel_review r')
            ->select('r.review_id, r.title, r.created_at, u.name AS user_name, f.name AS fc_name')
            ->join('my_fc_member u', 'u.member_uid = r.member_uid', 'left')
            ->join('my_fc_member f', 'f.member_uid = r.fc_member_uid', 'left')
            ->where('r.deleted_at', null)
            ->where('r.created_at >=', $range['start'])
            ->where('r.created_at <=', $range['end'])
            ->orderBy('r.review_id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
    }

    private function recentAds(array $range): array
    {
        return $this->db->table('ad_master a')
            ->select('a.id, a.ad_type, a.banner_position, a.amount, a.start_date, a.end_date, a.created_at, m.member_id, m.name, m.email')
            ->join('my_fc_member m', '(m.member_uid COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci OR CAST(m.member_id AS CHAR) COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci)', 'left', false)
            ->where('a.created_at >=', $range['start'])
            ->where('a.created_at <=', $range['end'])
            ->orderBy('a.id', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();
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
}
