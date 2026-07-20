<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class Management extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = Database::connect();
    }

    public function inactiveMembers()
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;
        $startDate = trim((string) ($this->request->getGet('start_date') ?? ''));
        $endDate = trim((string) ($this->request->getGet('end_date') ?? ''));
        $keyword = trim((string) ($this->request->getGet('q') ?? ''));

        $builder = $this->db->table('my_fc_member')
            ->select('member_id, member_type, email, phone, name, created_at, deleted_at')
            ->where('deleted_at IS NOT NULL', null, false);

        if ($startDate !== '') {
            $builder->where('deleted_at >=', $startDate . ' 00:00:00');
        }

        if ($endDate !== '') {
            $builder->where('deleted_at <=', $endDate . ' 23:59:59');
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('name', $keyword)
                ->orLike('email', $keyword)
                ->orLike('phone', $keyword)
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $rows = $builder
            ->orderBy('deleted_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['name'] = $this->maskName((string) ($row['name'] ?? ''));
            $row['email'] = $this->maskEmail((string) ($row['email'] ?? ''));
            $row['phone'] = $this->maskPhone((string) ($row['phone'] ?? ''));
            $row['member_type'] = ($row['member_type'] ?? '') === 'FC' ? 'FC' : '개인';
        }

        return $this->page([
            'title' => '탈퇴 회원',
            'breadcrumb' => 'Main > 대시보드 > 탈퇴 회원',
            'countLabel' => '탈퇴 회원',
            'count' => $total,
            'searchPlaceholder' => '이름, 이메일, 휴대폰번호 또는 탈퇴기간으로 검색',
            'searchAction' => base_url('admin/inactive-members'),
            'searchValue' => $keyword,
            'dateFrom' => $startDate,
            'dateTo' => $endDate,
            'tabs' => ['최근 탈퇴 순'],
            'headers' => ['회원구분', '이름', '이메일', '휴대폰번호', '가입일', '탈퇴일'],
            'rows' => array_map(static fn ($row) => [
                $row['member_type'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['created_at'] ?? '-',
                $row['deleted_at'] ?? '-',
            ], $rows),
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'pageQuery' => array_filter([
                'q' => $keyword,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ], static fn ($value) => (string) $value !== ''),
        ]);
    }

    public function counsels()
    {
        $filters = $this->counselFilters();
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 5;
        $builder = $this->counselListBuilder();
        $this->applyCounselFilters($builder, $filters);

        $countBuilder = $this->counselListBuilder();
        $this->applyCounselFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $rows = $builder
            ->orderBy('c.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $exportUrl = base_url('admin/contents/counsels/export');
        $query = array_filter([
            'status' => $filters['status'],
            'q' => $filters['q'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
        ], static fn ($value) => (string) $value !== '');

        if (!empty($query)) {
            $exportUrl .= '?' . http_build_query($query);
        }

        return $this->page([
            'title' => '상담 관리',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 상담 관리',
            'countLabel' => '상담 관리',
            'count' => $total,
            'searchPlaceholder' => '개인회원명, FC회원명, 개인/FC회원 이메일주소로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => ['status' => $filters['status']],
            'tabs' => $this->counselTabs($filters),
            'actions' => [['label' => 'EXCEL', 'url' => $exportUrl]],
            'perPage' => $perPage,
            'headers' => ['상담 신청자', '상담 FC', '상담상태', '상담신청일', '희망 상담요청일자'],
            'rows' => array_map(function ($row) {
                return [
                    '<a href="' . base_url('admin/contents/counsels/' . (int) $row['counsel_id']) . '">' . esc(($row['name'] ?? '-') . ' (' . ($row['email'] ?? '-') . ')') . '</a>',
                    esc(($row['fc_name'] ?? '-') . ' (' . ($row['fc_email'] ?? '-') . ')'),
                    $this->counselStatus((string) ($row['status'] ?? '')),
                    esc($row['created_at'] ?? '-'),
                    esc($row['reserve_datetime'] ?? '-'),
                ];
            }, $rows),
            'page' => $page,
            'totalPages' => $totalPages,
            'pageQuery' => array_filter([
                'status' => $filters['status'],
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== ''),
        ]);
    }

    public function counselsExport()
    {
        $filters = $this->counselFilters();
        $builder = $this->counselListBuilder();
        $this->applyCounselFilters($builder, $filters);

        $rows = $builder
            ->orderBy('c.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['상담 신청자', '신청자 이메일', '신청자 휴대폰', '상담 FC', 'FC 이메일', '상담상태', '상담신청일', '상담요청일', '상담거부 사유']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['phone'] ?? '',
                $row['fc_name'] ?? '',
                $row['fc_email'] ?? '',
                $this->counselStatus((string) ($row['status'] ?? '')),
                $row['created_at'] ?? '',
                $row['reserve_datetime'] ?? '',
                $row['reject_reason'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'counsels_' . date('Ymd_His') . '.csv';

        return $this->response
            ->download($fileName, $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function counselDetail($id)
    {
        $row = $this->db->table('my_fc_counsel c')
            ->select('c.*, fm.member_id AS fc_id, fm.name AS fc_name, fm.email AS fc_email, um.member_id AS user_id, um.name AS user_name, um.email AS user_email, um.phone AS user_phone, um.birth, um.gender')
            ->join('my_fc_member fm', 'fm.member_uid = c.fc_member_uid', 'left')
            ->join('my_fc_member um', 'um.member_uid = c.member_uid', 'left')
            ->where('c.counsel_id', (int) $id)
            ->get()
            ->getRowArray();

        return $this->page([
            'title' => '상담 상세',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 상담 관리 > 상담 상세',
            'backUrl' => base_url('admin/contents/counsels'),
            'detail' => [
                '상담 FC 정보' => '<a href="' . base_url('admin/fc-members/' . (int) ($row['fc_id'] ?? 0)) . '">' . esc(($row['fc_name'] ?? '-') . ' (' . ($row['fc_email'] ?? '-') . ')') . '</a>',
                '상담 신청 회원 정보' => '<a href="' . base_url('admin/members/' . (int) ($row['user_id'] ?? 0)) . '">' . esc(($row['user_name'] ?? '-') . ' (' . ($row['user_email'] ?? '-') . ')') . '</a>',
                '휴대폰 번호' => esc($row['user_phone'] ?? '-'),
                '생년월일 / 성별' => esc(($row['birth'] ?? '-') . ' / ' . (($row['gender'] ?? '') === 'F' ? '여성' : '남성')),
                '상담상태' => $this->counselStatus((string) ($row['status'] ?? '')),
                '상담요청 일시' => esc($row['created_at'] ?? '-'),
                '상담신청 일자' => esc($row['reserve_datetime'] ?? '-'),
                '상담 요청 내용' => nl2br(esc((string) ($row['content'] ?? '-'))),
                '상담거부 사유' => nl2br(esc((string) ($row['reject_reason'] ?? '-'))),
            ],
        ]);
    }

    public function deliberations()
    {
        $filters = $this->deliberationFilters();
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;

        $builder = $this->deliberationListBuilder();
        $this->applyDeliberationFilters($builder, $filters);

        $countBuilder = $this->deliberationListBuilder();
        $this->applyDeliberationFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $rows = $builder
            ->orderBy('r.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '심의필 신청 관리',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 심의필 신청 관리',
            'countLabel' => '심의필 신청',
            'count' => $total,
            'searchPlaceholder' => 'FC회원명, FC회원 이메일주소, 심의필 번호로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => ['status' => $filters['status']],
            'tabs' => $this->deliberationTabs($filters),
            'actions' => [['label' => 'EXCEL', 'url' => $this->deliberationsExportUrl($filters)]],
            'headers' => ['신청 FC', '승인 상태', '심의필번호', '승인신청일'],
            'rows' => array_map(function ($row) {
                return [
                    '<a href="' . base_url('admin/contents/deliberations/' . (int) $row['id']) . '">' . esc(($row['name'] ?? '-') . ' (' . ($row['email'] ?? '-') . ')') . '</a>',
                    $this->reviewedStatus((string) ($row['status'] ?? '')),
                    esc($row['deliberation_no'] ?? '-'),
                    esc($row['created_at'] ?? '-'),
                ];
            }, $rows),
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'pageQuery' => array_filter([
                'status' => $filters['status'],
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== ''),
        ]);
    }

    public function deliberationsExport()
    {
        $filters = $this->deliberationFilters();
        $builder = $this->deliberationListBuilder();
        $this->applyDeliberationFilters($builder, $filters);

        $rows = $builder
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $csv = "\xEF\xBB\xBF";
        $csv .= $this->csvLine($this->deliberationExportHeaders());

        foreach ($rows as $row) {
            $csv .= $this->csvLine($this->deliberationExportRow($row));
        }

        $fileName = 'deliberations_' . date('Ymd_His') . '.csv';

        return $this->response
            ->download($fileName, $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function deliberationDetail($id)
    {
        $row = $this->db->table('my_fc_reviewed r')
            ->select('r.*, m.member_id, m.name, m.email')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left')
            ->where('r.id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()->to(base_url('admin/contents/deliberations'))->with('error', '심의필 정보를 찾을 수 없습니다.');
        }

        $history = $this->reviewHistoryBuilder()
            ->where('h.review_id', (int) $id)
            ->orderBy('h.changed_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/management/deliberation_detail', [
            'title' => '심의필 신청 상세',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 심의필 신청 관리 > 상세',
            'backUrl' => base_url('admin/contents/deliberations'),
            'row' => $row,
            'history' => $history,
            'statusLabel' => $this->reviewedStatus((string) ($row['status'] ?? '')),
            'decisionUrl' => base_url('admin/contents/deliberations/' . (int) $id . '/decision'),
            'adminName' => (string) (session()->get('admin_username') ?? session()->get('admin_name') ?? session()->get('admin_id') ?? 'admin'),
        ]);
    }

    public function deliberationDownload($id)
    {
        $row = $this->db->table('my_fc_reviewed')
            ->select('deliberation_file')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        $storedPath = trim((string) ($row['deliberation_file'] ?? ''));
        if ($storedPath === '') {
            return redirect()->back()->with('error', '다운로드할 파일이 없습니다.');
        }

        $fullPath = WRITEPATH . 'uploads/review/' . ltrim($storedPath, '/');
        if (!is_file($fullPath)) {
            $altPath = WRITEPATH . ltrim($storedPath, '/');
            if (is_file($altPath)) {
                $fullPath = $altPath;
            }
        }

        if (!is_file($fullPath)) {
            return redirect()->back()->with('error', '다운로드할 실제 파일이 없습니다.');
        }

        return $this->response
            ->download($fullPath, null)
            ->setFileName(basename($storedPath));
    }

    public function deliberationDecision($id)
    {
        $reviewId = (int) $id;
        $decision = strtoupper(trim((string) $this->request->getPost('decision')));
        $rejectReason = trim((string) $this->request->getPost('reject_reason'));

        if (!in_array($decision, ['APPROVE', 'REJECT'], true)) {
            return redirect()->back()->with('error', '잘못된 요청입니다.');
        }

        if ($decision === 'REJECT' && $rejectReason === '') {
            return redirect()->back()->with('error', '거부 사유를 입력해주세요.');
        }

        $review = $this->db->table('my_fc_reviewed')
            ->where('id', $reviewId)
            ->get()
            ->getRowArray();

        if (!$review) {
            return redirect()->back()->with('error', '심의필 정보를 찾을 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $admin = (string) (session()->get('admin_username')
            ?? session()->get('admin_name')
            ?? session()->get('admin_id')
            ?? 'admin');
        $oldStatus = (string) ($review['status'] ?? '');
        $newStatus = $decision;

        $updateData = [
            'status' => $newStatus,
            'reject_reason' => $decision === 'REJECT' ? $rejectReason : null,
            'approve_at' => $now,
            'approve_admin_uid' => $admin,
            'updated_at' => $now,
        ];

        $this->db->transStart();

        $this->db->table('my_fc_reviewed')
            ->where('id', $reviewId)
            ->update($updateData);

        $this->db->table('my_fc_member')
            ->where('member_uid', $review['member_uid'])
            ->update([
                'fc_review_status' => $newStatus,
                'updated_at' => $now,
            ]);

        $this->insertReviewedHistory(
            $reviewId,
            (string) $review['member_uid'],
            $oldStatus !== '' ? $oldStatus : null,
            $newStatus,
            $decision === 'REJECT' ? $rejectReason : null,
            $admin,
            $now
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', '승인 처리 실패');
        }

        return redirect()->back()->with('success', $decision === 'APPROVE' ? '승인 처리 완료' : '거부 처리 완료');
    }

    public function reviews()
    {
        $filters = $this->reviewFilters();
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 20;
        $builder = $this->reviewBuilder();
        $this->applyReviewFilters($builder, $filters);

        $countBuilder = $this->reviewBuilder();
        $this->applyReviewFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $rows = $builder
            ->orderBy('r.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '후기 관리',
            'pageClass' => 'review-management-page',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 후기 관리',
            'countLabel' => '후기 관리',
            'count' => $total,
            'searchAction' => base_url('admin/contents/reviews'),
            'searchPlaceholder' => '작성자명, FC회원명, 제목으로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => ['display_status' => $filters['display_status']],
            'tabs' => $this->reviewTabs($filters),
            'actions' => [['label' => 'EXCEL', 'url' => $this->reviewsExportUrl($filters)]],
            'perPage' => $perPage,
            'headers' => ['제목', '별점', '작성자', '상담 FC', '노출상태', '조회수', '작성일'],
            'rows' => array_map(function ($row) {
                return [
                    '<a href="' . base_url('admin/contents/reviews/' . (int) $row['review_id']) . '">' . esc($row['title'] ?? '-') . '</a>',
                    esc((string) ($row['rating'] ?? '-')),
                    esc($row['user_name'] ?? '-'),
                    esc($row['fc_name'] ?? '-'),
                    $this->reviewDisplayStatusLabel((string) ($row['display_status'] ?? 'Y')),
                    number_format((int) ($row['view_count'] ?? 0)),
                    esc($row['created_at'] ?? '-'),
                ];
            }, $rows),
            'page' => $page,
            'totalPages' => $totalPages,
            'pageQuery' => array_filter([
                'display_status' => $filters['display_status'],
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== ''),
        ]);
    }

    public function reviewsExport()
    {
        $filters = $this->reviewFilters();
        $builder = $this->reviewBuilder();
        $this->applyReviewFilters($builder, $filters);

        $rows = $builder
            ->orderBy('r.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $csv = "\xEF\xBB\xBF";
        $csv .= $this->csvLine($this->reviewExportHeaders());

        foreach ($rows as $row) {
            $csv .= $this->csvLine($this->reviewExportRow($row));
        }

        $fileName = 'reviews_' . date('Ymd_His') . '.csv';

        return $this->response
            ->download($fileName, $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function reviewDetail($id)
    {
        $row = $this->reviewBuilder()
            ->where('r.review_id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()->to(base_url('admin/contents/reviews'))->with('error', '후기 정보를 찾을 수 없습니다.');
        }

        return view('admin/management/review_detail', [
            'title' => '후기 상세',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 후기 관리 > 상세',
            'backUrl' => base_url('admin/contents/reviews'),
            'row' => $row,
            'statusLabel' => $this->reviewDisplayStatusLabel((string) ($row['display_status'] ?? 'Y')),
            'toggleUrl' => base_url('admin/contents/reviews/' . (int) $id . '/display'),
        ]);
    }

    public function reviewDisplayUpdate($id)
    {
        $reviewId = (int) $id;
        $displayStatus = strtoupper(trim((string) $this->request->getPost('display_status')));

        if (!in_array($displayStatus, ['Y', 'N'], true)) {
            return redirect()->back()->with('error', '잘못된 요청입니다.');
        }

        $row = $this->db->table('my_fc_counsel_review')
            ->where('review_id', $reviewId)
            ->get()
            ->getRowArray();

        if (!$row) {
            return redirect()->back()->with('error', '후기 정보를 찾을 수 없습니다.');
        }

        $this->db->table('my_fc_counsel_review')
            ->where('review_id', $reviewId)
            ->update([
                'display_status' => $displayStatus,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->back()->with('success', $displayStatus === 'Y' ? '노출 처리 완료' : '비노출 처리 완료');
    }

    public function insuranceIn()
    {
        $keyword = trim((string) $this->request->getGet('q'));
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));
        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 20;

        $countBuilder = $this->insuranceInQuestionBuilder(false);
        $this->applyInsuranceInFilters($countBuilder, $keyword, $startDate, $endDate);
        $total = $countBuilder->countAllResults();

        $builder = $this->insuranceInQuestionBuilder(true);
        $this->applyInsuranceInFilters($builder, $keyword, $startDate, $endDate);
        $questions = $builder
            ->orderBy('q.question_id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return view('admin/insurance_in/index', [
            'title' => '보험IN 관리',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 보험IN 관리',
            'questions' => $questions,
            'count' => $total,
            'q' => $keyword,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    public function insuranceInDetail($id)
    {
        helper(['region', 'insurance']);
        $questionId = (int) $id;
        $question = $this->insuranceInQuestionBuilder(true)
            ->where('q.question_id', $questionId)
            ->get()
            ->getRowArray();

        if (!$question) {
            return redirect()->to(base_url('admin/contents/insurance-in'))->with('error', '보험IN 게시물을 찾을 수 없습니다.');
        }

        $answers = $this->db->table('my_fc_insurance_in_answer a')
            ->select("a.*, m.member_id, m.name, m.email, COALESCE(NULLIF(p.profile_image, ''), NULLIF(m.profile_image, '')) AS profile_image, p.company, p.company_sub, p.ga, p.license_date,
                pa.region, pa.insurance_types, IFNULL(rv.rating, 0) rating, IFNULL(rv.rating_count, 0) rating_count", false)
            ->join('my_fc_member m', 'm.member_uid = a.fc_member_uid', 'left')
            ->join('my_fc_profile p', 'p.member_uid = a.fc_member_uid', 'left')
            ->join('my_fc_profile_activity pa', 'pa.member_uid = a.fc_member_uid', 'left')
            ->join('(SELECT fc_member_uid, AVG(rating) rating, COUNT(*) rating_count FROM my_fc_counsel_review WHERE deleted_at IS NULL GROUP BY fc_member_uid) rv', 'rv.fc_member_uid = a.fc_member_uid', 'left', false)
            ->where('a.question_id', $questionId)
            ->where('a.status', 'DISPLAY')
            ->where('a.deleted_at', null)
            ->orderBy('a.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $files = $this->db->table('my_fc_insurance_in_file')
            ->where('question_id', $questionId)
            ->orderBy('file_id', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/insurance_in/detail', [
            'title' => '보험IN 상세',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 보험IN 관리 > 상세',
            'question' => $question,
            'answers' => $answers,
            'files' => $files,
        ]);
    }

    public function insuranceInDelete($id)
    {
        $questionId = (int) $id;
        $question = $this->db->table('my_fc_insurance_in_question')
            ->where('question_id', $questionId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$question) {
            return redirect()->to(base_url('admin/contents/insurance-in'))->with('error', '보험IN 게시물을 찾을 수 없습니다.');
        }

        $this->db->table('my_fc_insurance_in_question')
            ->where('question_id', $questionId)
            ->update([
                'status' => 'DELETED',
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(base_url('admin/contents/insurance-in'))->with('success', '보험IN 게시물이 삭제되었습니다.');
    }

    public function insuranceInAnswerDelete($questionId, $answerId)
    {
        $questionId = (int) $questionId;
        $answerId = (int) $answerId;
        $answer = $this->db->table('my_fc_insurance_in_answer')
            ->where('answer_id', $answerId)
            ->where('question_id', $questionId)
            ->where('status', 'DISPLAY')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$answer) {
            return redirect()->to(base_url('admin/contents/insurance-in/' . $questionId))->with('error', '삭제할 답변을 찾을 수 없습니다.');
        }

        $this->db->table('my_fc_insurance_in_answer')
            ->where('answer_id', $answerId)
            ->update([
                'status' => 'DELETED',
                'deleted_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(base_url('admin/contents/insurance-in/' . $questionId))->with('success', 'FC 답변이 삭제되었습니다.');
    }

    private function insuranceInQuestionBuilder(bool $withAnswerCount)
    {
        $builder = $this->db->table('my_fc_insurance_in_question q')
            ->select('q.*, m.member_id, m.name AS writer_name, m.email AS writer_email')
            ->join('my_fc_member m', 'm.member_uid = q.member_uid', 'left')
            ->where('q.status', 'OPEN')
            ->where('q.deleted_at', null);

        if ($withAnswerCount) {
            $builder->select('(SELECT COUNT(*) FROM my_fc_insurance_in_answer a WHERE a.question_id = q.question_id AND a.status = \'DISPLAY\' AND a.deleted_at IS NULL) answer_count', false);
        }

        return $builder;
    }

    private function applyInsuranceInFilters($builder, string $keyword, string $startDate, string $endDate): void
    {
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('q.title', $keyword)
                ->orLike('q.body', $keyword)
                ->orLike('m.name', $keyword)
                ->orLike('m.email', $keyword)
                ->groupEnd();
        }
        if ($startDate !== '') {
            $builder->where('q.created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate !== '') {
            $builder->where('q.created_at <=', $endDate . ' 23:59:59');
        }
    }

    public function securities()
    {
        $keyword = trim((string) $this->request->getGet('q'));
        $builder = $this->db->table('my_fc_member_security s')
            ->select('s.member_uid, COUNT(*) AS file_count, MAX(s.created_at) AS last_created_at, m.member_id, m.name, m.email')
            ->join('my_fc_member m', 'm.member_uid = s.member_uid', 'left')
            ->where('s.deleted_at', null);

        if ($keyword !== '') {
            $builder->groupStart()->like('m.name', $keyword)->orLike('m.email', $keyword)->groupEnd();
        }

        $rows = $builder->groupBy('s.member_uid, m.member_id, m.name, m.email')->orderBy('last_created_at', 'DESC')->limit(20)->get()->getResultArray();

        return $this->page([
            'title' => '증권 관리',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 증권 관리',
            'countLabel' => '증권 관리',
            'count' => count($rows),
            'searchAction' => base_url('admin/contents/securities'),
            'searchPlaceholder' => '등록자명, 등록자 이메일로 검색',
            'searchValue' => $keyword,
            'headers' => ['등록자', '파일갯수', '등록일자'],
            'rows' => array_map(function ($row) {
                return [
                    '<a href="' . base_url('admin/contents/securities/' . urlencode((string) $row['member_uid'])) . '">' . esc(($row['name'] ?? '-') . ' (' . ($row['email'] ?? '-') . ')') . '</a>',
                    number_format((int) ($row['file_count'] ?? 0)),
                    esc($row['last_created_at'] ?? '-'),
                ];
            }, $rows),
        ]);
    }

    public function securityDetail($memberUid)
    {
        $files = $this->db->table('my_fc_member_security s')
            ->select('s.*, m.name, m.email')
            ->join('my_fc_member m', 'm.member_uid = s.member_uid', 'left')
            ->where('s.member_uid', $memberUid)
            ->where('s.deleted_at', null)
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $first = $files[0] ?? [];
        $filesHtml = '';

        if (!empty($files)) {
            $filesHtml .= '<div class="table-responsive"><table class="table table-bordered table-sm align-middle mb-0">';
            $filesHtml .= '<thead><tr><th style="width:60px;">NO</th><th>파일명</th><th style="width:110px;">확장자</th><th style="width:110px;">용량</th><th style="width:140px;">등록일</th><th style="width:150px;">관리</th></tr></thead><tbody>';

            foreach ($files as $index => $file) {
                $downloadUrl = base_url('admin/members/files/' . (int) $file['security_id'] . '/download');
                $filesHtml .= '<tr>';
                $filesHtml .= '<td class="text-center">' . ($index + 1) . '</td>';
                $filesHtml .= '<td><a href="' . esc($downloadUrl) . '">' . esc($file['original_name'] ?? $file['saved_name'] ?? '-') . '</a></td>';
                $filesHtml .= '<td class="text-center">' . esc($file['file_ext'] ?? '-') . '</td>';
                $filesHtml .= '<td class="text-end">' . esc($this->formatBytes($file['file_size'] ?? 0)) . '</td>';
                $filesHtml .= '<td class="text-center">' . esc($file['created_at'] ?? '-') . '</td>';
                $filesHtml .= '<td class="text-center">';
                $filesHtml .= '<div class="d-inline-flex gap-1">';
                $filesHtml .= '<a class="btn btn-outline-primary btn-sm" href="' . esc($downloadUrl) . '">다운로드</a>';
                $filesHtml .= '<form action="' . esc(base_url('admin/members/files/delete')) . '" method="post" onsubmit="return confirm(\'해당 파일을 삭제하시겠습니까?\');" style="display:inline-block;">';
                $filesHtml .= csrf_field();
                $filesHtml .= '<input type="hidden" name="security_id" value="' . (int) $file['security_id'] . '">';
                $filesHtml .= '<button type="submit" class="btn btn-outline-danger btn-sm">삭제</button>';
                $filesHtml .= '</form>';
                $filesHtml .= '</div>';
                $filesHtml .= '</td>';
                $filesHtml .= '</tr>';
            }

            $filesHtml .= '</tbody></table></div>';
        } else {
            $filesHtml = '<div class="text-muted">등록된 첨부파일이 없습니다.</div>';
        }

        return $this->page([
            'title' => '증권 관리 상세',
            'breadcrumb' => 'Main > 대시보드 > 컨텐츠 관리 > 증권 관리 > 증권 상세',
            'backUrl' => base_url('admin/contents/securities'),
            'detail' => [
                '등록자' => esc(($first['name'] ?? '-') . ' (' . ($first['email'] ?? '-') . ')'),
                '등록일' => esc($first['created_at'] ?? '-'),
                '증권파일' => $filesHtml,
            ],
            'missing' => ['증권 관리 전용 삭제 이력/노출상태 테이블은 없습니다.'],
        ]);
    }

    public function ads($kind = 'normal')
    {
        $kind = $this->normalizeAdKind((string) $kind);
        $titleMap = [
            'normal' => '일반 광고 관리',
            'top' => '상단 배너 광고',
            'bottom' => '하단 배너 광고',
        ];

        $filters = $this->adListFilters();
        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 20;
        $countBuilder = $this->adListBuilder($kind);
        $this->applyAdListFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();
        $builder = $this->adListBuilder($kind);
        $this->applyAdListFilters($builder, $filters);
        $rows = $builder->orderBy('a.created_at', 'DESC')->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $query = array_filter($filters, static fn($value, $key) => (string) $value !== '' && !($key === 'member_id' && (int) $value === 0), ARRAY_FILTER_USE_BOTH);
        $exportUrl = base_url('admin/ads/' . $kind . '/export') . ($query ? '?' . http_build_query($query) : '');
        $summaryBuilder = $this->adListBuilder($kind);
        $this->applyAdListFilters($summaryBuilder, $filters);
        $summaryRows = $summaryBuilder->get()->getResultArray();

        return $this->page([
            'title' => $titleMap[$kind] ?? $titleMap['normal'],
            'pageClass' => 'ad-management-page',
            'breadcrumb' => 'Main > 광고관리 > ' . ($titleMap[$kind] ?? $titleMap['normal']),
            'countLabel' => ($kind === 'normal' ? '일반 광고 현황' : '배너 광고 현황'),
            'count' => $total,
            'searchPlaceholder' => '신청자명, 신청자 이메일로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => ['status' => $filters['status'], 'member_id' => $filters['member_id']],
            'tabs' => $this->adListTabs($kind, $filters),
            'actions' => [
                ['label' => $kind === 'normal' ? '일반 광고 추가' : '배너 광고 추가', 'url' => base_url('admin/ads/' . $kind . '/create')],
                ['label' => 'EXCEL', 'url' => $exportUrl],
            ],
            'summary' => [
                '이번달 총 광고 결제 금액' => number_format(array_sum(array_map(static fn ($row) => !empty($row['created_at']) && date('Ym', strtotime($row['created_at'])) === date('Ym') ? (int) ($row['amount'] ?? 0) : 0, $summaryRows))) . '원',
                '총 광고 진행 건수' => count($summaryRows) . '건',
                '현재 진행중인 광고' => count(array_filter($summaryRows, fn ($row) => $this->isAdActive($row))) . '건',
            ],
            'headers' => ['선택', '광고 상품 명', '클릭 수', '광고 기간', '광고 금액', '광고 진행 상태', '광고 신청 FC', '광고신청일', '관리'],
            'bulkForm' => [
                'action' => base_url('admin/ads/' . $kind . '/bulk-end'),
                'label' => '선택한 광고 중단',
                'confirm' => '선택한 광고를 중단 처리하시겠습니까? 확인 즉시 반영됩니다.',
            ],
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'pageQuery' => $query,
            'rows' => array_map(function ($row) use ($kind) {
                $adId = (int) ($row['id'] ?? 0);
                $typeLabel = $this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? ''));
                $statusLabel = $this->adStatusLabelForRow($row);
                $statusKey = $this->isAdActive($row) ? 'active' : strtolower((string) ($row['status'] ?? 'apply'));
                $createdAt = !empty($row['created_at']) ? strtotime((string) $row['created_at']) : false;
                return [
                    '<input type="checkbox" class="form-check-input ad-row-check" name="ad_ids[]" value="' . $adId . '" aria-label="광고 #' . $adId . ' 선택">',
                    '<div class="ad-product-cell"><span class="ad-product-mark">AD</span><div><strong>' . esc($typeLabel) . '</strong><small>광고번호 #' . $adId . '</small></div></div>',
                    '<a class="ad-metric-button" href="' . base_url('admin/ads/' . $kind . '/clicks?ad_id=' . $adId) . '"><strong>' . number_format((int) ($row['click_count'] ?? 0)) . '</strong><span>클릭 상세</span></a>',
                    '<div class="ad-period-cell"><span>' . esc($row['start_date'] ?? '-') . '</span><i>→</i><span>' . esc($row['end_date'] ?? '-') . '</span></div>',
                    '<strong class="ad-amount">' . number_format((int) ($row['amount'] ?? 0)) . '<small>원</small></strong>',
                    '<a class="ad-status-badge is-' . esc($statusKey) . '" href="' . base_url('admin/ads/' . $kind . '/status?ad_id=' . $adId) . '"><span></span>' . esc($statusLabel) . '</a>',
                    !empty($row['member_id']) ? '<a class="ad-fc-button" href="' . base_url('admin/fc-members/' . (int) $row['member_id']) . '">' . esc($row['name'] ?? '-') . '<small>FC 상세</small></a>' : '<span class="text-muted">-</span>',
                    $createdAt ? '<div class="ad-created-cell"><strong>' . esc(date('Y.m.d', $createdAt)) . '</strong><small>' . esc(date('H:i', $createdAt)) . '</small></div>' : '-',
                    $this->adDecisionControls($kind, $row),
                ];
            }, $rows),
        ]);
    }

    public function adsExport($kind = 'normal')
    {
        $kind = $this->normalizeAdKind((string) $kind);
        $filters = $this->adListFilters();
        $builder = $this->adListBuilder($kind);
        $this->applyAdListFilters($builder, $filters);
        $rows = $builder->orderBy('a.created_at', 'DESC')->get()->getResultArray();

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['광고번호', '광고 상품명', '클릭 수', '광고 시작일', '광고 종료일', '광고 금액', '진행 상태', '신청 FC', 'FC 이메일', '신청일']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['id'] ?? '',
                $this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? '')),
                $row['click_count'] ?? 0,
                $row['start_date'] ?? '',
                $row['end_date'] ?? '',
                $row['amount'] ?? 0,
                $this->adStatusLabelForRow($row),
                $row['name'] ?? '',
                $row['email'] ?? '',
                $row['created_at'] ?? '',
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'ads_' . $kind . '_' . date('Ymd_His') . '.csv';

        return $this->response
            ->download($fileName, $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function adsBulkEnd($kind = 'normal')
    {
        $kind = $this->normalizeAdKind((string) $kind);
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('ad_ids')))));
        if (!$ids) {
            return redirect()->back()->with('error', '중단할 광고를 선택해주세요.');
        }

        $rows = $this->db->table('ad_master')->whereIn('id', $ids)->get()->getResultArray();
        $targets = array_filter($rows, fn($row) => $this->adBelongsToKind($row, $kind) && (string) ($row['status'] ?? '') === 'approved');
        if (!$targets) {
            return redirect()->back()->with('error', '선택한 항목 중 중단 가능한 진행중 광고가 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $this->db->transStart();
        foreach ($targets as $row) {
            $adId = (int) $row['id'];
            $this->db->table('ad_master')->where('id', $adId)->update([
                'status' => 'rejected',
                'end_date' => $today,
                'updated_at' => $now,
            ]);
            $this->insertAdStatusHistory($adId, (string) ($row['status'] ?? ''), 'rejected', '관리자 선택 광고 중단');
        }
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', '선택 광고 중단 처리에 실패했습니다.');
        }
        return redirect()->back()->with('success', count($targets) . '건의 광고가 중단 처리되었습니다.');
    }

    public function adCreate($kind = 'normal')
    {
        $kind = $this->normalizeAdKind($kind);

        $members = $this->db->table('my_fc_member')
            ->select('member_id, name, email')
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->orderBy('member_id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        return view('admin/ad/create', [
            'kind' => $kind,
            'title' => $this->adKindTitle($kind) . ' 등록',
            'members' => $members,
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function adStore($kind = 'normal')
    {
        helper('fileupload_helper');

        $kind = $this->normalizeAdKind($kind);
        $now = date('Y-m-d H:i:s');

        $fcMemberId = (int) $this->request->getPost('fc_member_id');
        $status = (string) ($this->request->getPost('status') ?: 'apply');
        $amount = (int) $this->request->getPost('amount');
        $startDate = trim((string) $this->request->getPost('start_date')) ?: null;
        $endDate = trim((string) $this->request->getPost('end_date')) ?: null;

        if ($fcMemberId < 1) {
            return redirect()->back()->withInput()->with('error', '광고 신청 FC를 선택해주세요.');
        }

        if (!in_array($status, ['apply', 'pending', 'approved', 'rejected', 'end'], true)) {
            return redirect()->back()->withInput()->with('error', '광고 상태 값이 올바르지 않습니다.');
        }

        $member = $this->db->table('my_fc_member')
            ->select('member_id, member_uid')
            ->where('member_id', $fcMemberId)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            return redirect()->back()->withInput()->with('error', '존재하지 않는 FC 회원입니다.');
        }

        if ($status === 'approved') {
            $startDate = $startDate ?: date('Y-m-d');
            if (!$endDate) {
                return redirect()->back()->withInput()->with('error', '진행중 광고는 종료일을 입력해주세요.');
            }
            if ($endDate < $startDate) {
                return redirect()->back()->withInput()->with('error', '광고 종료일은 시작일 이후로 입력해주세요.');
            }
        }

        $data = [
            'fc_member_id' => (string) ($member['member_uid'] ?? $fcMemberId),
            'status' => $status,
            'amount' => $amount,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'click_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($status === 'approved') {
            $data['approved_at'] = $now;
            $data['approved_by'] = $this->currentAdminName();
        }

        if ($kind === 'normal') {
            $adType = (string) $this->request->getPost('ad_type');
            if (!in_array($adType, ['region_fc', 'product_fc', 'review', 'language_fc'], true)) {
                return redirect()->back()->withInput()->with('error', '일반 광고 상품을 선택해주세요.');
            }

            $data['ad_type'] = $adType;
            $data['region_code'] = trim((string) $this->request->getPost('region_code')) ?: null;
            $data['insurance_type'] = trim((string) $this->request->getPost('insurance_type')) ?: null;
            $data['review_id'] = $this->request->getPost('review_id') !== '' ? (int) $this->request->getPost('review_id') : null;
            $data['language_code'] = trim((string) $this->request->getPost('language_code')) ?: null;
        } else {
            $data['ad_type'] = 'banner';
            $data['banner_position'] = $kind;
            $data['banner_link_url'] = trim((string) $this->request->getPost('banner_link_url')) ?: null;
            $data['banner_need_design'] = $this->request->getPost('banner_need_design') ? 1 : 0;

            $file = $this->request->getFile('banner_image');
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $data['banner_image_url'] = '/uploads/banner/' . upload_file(
                    $file,
                    'uploads/banner',
                    ['jpg', 'jpeg', 'png', 'webp', 'gif']
                );
            } elseif (empty($data['banner_need_design'])) {
                return redirect()->back()->withInput()->with('error', '배너 이미지를 업로드하거나 제작 요청을 선택해주세요.');
            }
        }

        $this->db->transStart();

        $this->db->table('ad_master')->insert($data);
        $adId = (int) $this->db->insertID();

        $this->insertAdStatusHistory(
            $adId,
            null,
            $status,
            '광고 등록'
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', '광고 등록에 실패했습니다.');
        }

        return redirect()
            ->to(base_url('admin/ads/' . $kind))
            ->with('success', '광고가 등록되었습니다.');
    }

    public function adDecision($kind = 'normal', $id = null)
    {
        $kind = $this->normalizeAdKind((string) $kind);
        $adId = (int) $id;
        $decision = (string) $this->request->getPost('decision');
        $memo = trim((string) $this->request->getPost('memo'));

        if (!in_array($decision, ['approved', 'rejected', 'end'], true)) {
            return redirect()->back()->with('error', '광고 처리 값이 올바르지 않습니다.');
        }

        $ad = $this->db->table('ad_master')
            ->where('id', $adId)
            ->get()
            ->getRowArray();

        if (!$ad || !$this->adBelongsToKind($ad, $kind)) {
            return redirect()->back()->with('error', '광고 정보를 찾을 수 없습니다.');
        }

        $oldStatus = (string) ($ad['status'] ?? '');
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $update = [
            'status' => $decision,
            'updated_at' => $now,
        ];

        if ($decision === 'approved') {
            $endDate = trim((string) $this->request->getPost('end_date'));
            if (!$endDate) {
                return redirect()->back()->with('error', '승인할 광고의 종료일을 입력해주세요.');
            }
            if ($endDate < $today) {
                return redirect()->back()->with('error', '광고 종료일은 오늘 이후로 입력해주세요.');
            }

            $update['start_date'] = $today;
            $update['end_date'] = $endDate;
            $update['approved_at'] = $now;
            $update['approved_by'] = $this->currentAdminName();
            $memo = $memo !== '' ? $memo : '광고 승인';
        } elseif ($decision === 'end') {
            $update['end_date'] = $today;
            $memo = $memo !== '' ? $memo : '광고 종료';
        } else {
            $memo = $memo !== '' ? $memo : '광고 거절';
        }

        $this->db->transStart();

        $this->db->table('ad_master')
            ->where('id', $adId)
            ->update($update);

        if ($oldStatus !== $decision) {
            $this->insertAdStatusHistory($adId, $oldStatus, $decision, $memo);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', '광고 상태 변경에 실패했습니다.');
        }

        return redirect()
            ->to(base_url('admin/ads/' . $kind))
            ->with('success', '광고 상태가 변경되었습니다.');
    }

    public function adClicks($kind = 'normal')
    {
        $kind = $this->normalizeAdKind($kind);
        $adId = (int) ($this->request->getGet('ad_id') ?? 0);
        $keyword = trim((string) $this->request->getGet('q'));
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));
        $page = max(1, (int) $this->request->getGet('page'));

        $builder = $this->db->table('ad_click_log l')
            ->select("
                l.ad_id,
                l.click_date,
                COUNT(*) AS daily_click_count,
                a.ad_type,
                a.banner_position,
                a.amount,
                a.start_date,
                a.end_date,
                a.click_count,
                m.member_id,
                m.name,
                m.email
            ")
            ->join('ad_master a', 'a.id = l.ad_id', 'inner')
            ->join('my_fc_member m', $this->adMemberJoinCondition(), 'left', false);

        if ($kind === 'normal') {
            $builder->whereIn('a.ad_type', ['region_fc', 'product_fc', 'review', 'language_fc']);
        } else {
            $builder->where('a.ad_type', 'banner')
                ->where('a.banner_position', $kind);
        }

        if ($adId > 0) {
            $builder->where('l.ad_id', $adId);
        }
        if ($keyword !== '') {
            $builder->groupStart()->like('m.name', $keyword)->orLike('m.email', $keyword)->groupEnd();
        }
        if ($startDate !== '') $builder->where('l.click_date >=', $startDate);
        if ($endDate !== '') $builder->where('l.click_date <=', $endDate);

        $allRows = $builder
            ->groupBy('l.ad_id, l.click_date, a.ad_type, a.banner_position, a.amount, a.start_date, a.end_date, a.click_count, m.member_id, m.name, m.email')
            ->orderBy('l.click_date', 'DESC')
            ->orderBy('l.ad_id', 'DESC')
            ->get()
            ->getResultArray();

        if ((string) $this->request->getGet('download') === 'csv') {
            $handle = fopen('php://temp', 'r+');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['광고 ID', '광고 상품명', '클릭 일자', '일자별 클릭 수', '누적 클릭 수', '광고 신청 FC', '광고 기간']);
            foreach ($allRows as $row) {
                fputcsv($handle, [(int) $row['ad_id'], $this->adTypeLabel((string) $row['ad_type'], (string) ($row['banner_position'] ?? '')), $row['click_date'], $row['daily_click_count'], $row['click_count'], $row['name'], ($row['start_date'] ?? '-') . ' ~ ' . ($row['end_date'] ?? '-')]);
            }
            rewind($handle); $csv = stream_get_contents($handle); fclose($handle);
            $fileName = 'ad_clicks_' . date('Ymd_His') . '.csv';

            return $this->response
                ->download($fileName, $csv)
                ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Transfer-Encoding', 'binary')
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $perPage = 20;
        $rows = array_slice($allRows, ($page - 1) * $perPage, $perPage);

        $totalClicks = array_sum(array_map(static fn ($row) => (int) ($row['daily_click_count'] ?? 0), $allRows));

        return $this->page([
            'title' => '클릭수 상세',
            'breadcrumb' => 'Main > 광고관리 > 클릭수 상세',
            'backUrl' => base_url('admin/ads/' . $kind),
            'searchPlaceholder' => '광고 신청 FC명 또는 이메일로 검색',
            'searchValue' => $keyword,
            'dateFrom' => $startDate,
            'dateTo' => $endDate,
            'searchHidden' => ['ad_id' => $adId],
            'actions' => [[
                'label' => 'EXCEL',
                'url' => current_url() . '?' . http_build_query(array_filter(['ad_id' => $adId, 'q' => $keyword, 'start_date' => $startDate, 'end_date' => $endDate, 'download' => 'csv'], static fn($v) => (string) $v !== '')),
            ]],
            'page' => $page,
            'totalPages' => max(1, (int) ceil(count($allRows) / $perPage)),
            'pageQuery' => array_filter(['ad_id' => $adId, 'q' => $keyword, 'start_date' => $startDate, 'end_date' => $endDate], static fn($v) => (string) $v !== ''),
            'summary' => [
                '조회 광고 ID' => $adId > 0 ? (string) $adId : '전체',
                '조회된 클릭 수' => number_format($totalClicks) . '건',
                '일자별 집계 건수' => count($allRows) . '건',
            ],
            'headers' => ['광고 ID', '광고 상품 명', '클릭 일자', '일자별 클릭 수', '누적 클릭 수', '광고 신청 FC', '광고 기간'],
            'rows' => array_map(function ($row) {
                return [
                    (int) ($row['ad_id'] ?? 0),
                    esc($this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? ''))),
                    esc($row['click_date'] ?? '-'),
                    number_format((int) ($row['daily_click_count'] ?? 0)),
                    number_format((int) ($row['click_count'] ?? 0)),
                    !empty($row['member_id']) ? '<a href="' . base_url('admin/fc-members/' . (int) $row['member_id']) . '">' . esc($row['name'] ?? '-') . '</a>' : '-',
                    esc(($row['start_date'] ?? '-') . ' ~ ' . ($row['end_date'] ?? '-')),
                ];
            }, $rows),
            'missing' => $rows ? [] : ['아직 기록된 광고 클릭 로그가 없습니다.'],
        ]);
    }

    public function adStatus($kind = 'normal')
    {
        $kind = $this->normalizeAdKind($kind);
        $adId = (int) ($this->request->getGet('ad_id') ?? 0);

        $builder = $this->db->table('ad_status_history h')
            ->select("
                h.*,
                a.ad_type,
                a.banner_position,
                a.status AS current_status,
                a.start_date,
                a.end_date,
                m.member_id,
                m.name,
                m.email
            ")
            ->join('ad_master a', 'a.id = h.ad_id', 'inner')
            ->join('my_fc_member m', $this->adMemberJoinCondition(), 'left', false);

        if ($kind === 'normal') {
            $builder->whereIn('a.ad_type', ['region_fc', 'product_fc', 'review', 'language_fc']);
        } else {
            $builder->where('a.ad_type', 'banner')
                ->where('a.banner_position', $kind);
        }

        if ($adId > 0) {
            $builder->where('h.ad_id', $adId);
        }

        $rows = $builder
            ->orderBy('h.changed_at', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '광고 진행 상태 상세',
            'breadcrumb' => 'Main > 광고관리 > 광고 진행 상태 상세',
            'backUrl' => base_url('admin/ads/' . $kind),
            'summary' => [
                '조회 광고 ID' => $adId > 0 ? (string) $adId : '전체',
                '상태 변경 이력' => count($rows) . '건',
            ],
            'headers' => ['광고 ID', '광고 상품 명', '이전 상태', '변경 상태', '현재 상태', '변경일시', '변경자', '메모', '광고 신청 FC'],
            'rows' => array_map(function ($row) {
                return [
                    (int) ($row['ad_id'] ?? 0),
                    esc($this->adTypeLabel((string) ($row['ad_type'] ?? ''), (string) ($row['banner_position'] ?? ''))),
                    esc($this->adStatusLabel((string) ($row['old_status'] ?? ''))),
                    esc($this->adStatusLabel((string) ($row['new_status'] ?? ''))),
                    esc($this->adStatusLabel((string) ($row['current_status'] ?? ''))),
                    esc($row['changed_at'] ?? '-'),
                    esc($row['changed_by'] ?? '-'),
                    esc($row['memo'] ?? '-'),
                    !empty($row['member_id']) ? '<a href="' . base_url('admin/fc-members/' . (int) $row['member_id']) . '">' . esc($row['name'] ?? '-') . '</a>' : '-',
                ];
            }, $rows),
            'missing' => $rows ? [] : ['아직 기록된 광고 상태 변경 이력이 없습니다.'],
        ]);
    }

    public function accounts()
    {
        $canManageAccounts = $this->canManageAdminAccounts();
        $rows = $this->db->table('admin_users')
            ->select('id, username, name, email, phone, role, status, created_at, last_login_at')
            ->orderBy('id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '관리자 계정',
            'breadcrumb' => 'Main > 계정관리',
            'countLabel' => '관리자 계정',
            'count' => count($rows),
            'actions' => $canManageAccounts ? [['label' => '계정추가', 'url' => base_url('admin/accounts/create')]] : [],
            'headers' => ['ID', '이름', '이메일', '휴대폰번호', '권한', '가입 상태', '계정 생성일', '최근 로그인', '관리'],
            'rows' => array_map(function ($row) use ($canManageAccounts) {
                $id = (int) ($row['id'] ?? 0);
                $status = (string) ($row['status'] ?? 'N');
                $nextStatus = $status === 'Y' ? 'N' : 'Y';
                $statusLabel = $status === 'Y' ? '정상' : '중지';
                $statusButton = $status === 'Y' ? '중지' : '정상전환';
                $statusClass = $status === 'Y' ? 'btn-outline-danger' : 'btn-outline-success';
                $statusForm = $canManageAccounts && ($row['username'] ?? '') !== 'admin'
                    ? '<form action="' . base_url('admin/accounts/' . $id . '/status') . '" method="post" onsubmit="return confirm(\'계정 상태를 변경하시겠습니까?\');" style="display:inline-block;margin-left:6px;">'
                        . csrf_field()
                        . '<input type="hidden" name="status" value="' . esc($nextStatus) . '">'
                        . '<button type="submit" class="btn ' . $statusClass . ' btn-sm">' . $statusButton . '</button></form>'
                    : '';
                $editButton = $canManageAccounts
                    ? '<a href="' . base_url('admin/accounts/' . $id . '/edit') . '" class="btn btn-outline-primary btn-sm">수정</a>'
                    : '<span class="text-muted">변경 권한 없음</span>';

                return [
                    esc($row['username'] ?? '-'),
                    esc($row['name'] ?? '-'),
                    esc($row['email'] ?? '-'),
                    esc($row['phone'] ?? '-'),
                    esc($this->adminRoleLabel((string) ($row['role'] ?? 'admin'))),
                    $statusLabel,
                    esc($row['created_at'] ?? '-'),
                    esc($row['last_login_at'] ?? '-'),
                    $editButton . $statusForm,
                ];
            }, $rows),
        ]);
    }

    public function accountCreate()
    {
        if (!$this->canManageAdminAccounts()) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '최고 관리자 admin 계정만 관리자 계정을 변경할 수 있습니다.');
        }

        return view('admin/account/form', [
            'title' => '관리자 계정 등록',
            'breadcrumb' => 'Main > 계정관리 > 등록',
            'backUrl' => base_url('admin/accounts'),
            'actionUrl' => base_url('admin/accounts/create'),
            'account' => [],
            'buttonLabel' => '저장',
            'passwordRequired' => true,
            'roleOptions' => $this->adminRoleOptions(),
        ]);
    }

    public function accountStore()
    {
        if (!$this->canManageAdminAccounts()) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '최고 관리자 admin 계정만 관리자 계정을 변경할 수 있습니다.');
        }

        $data = $this->accountPostData(null);

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('admin_users')->insert($data);

        return redirect()->to(base_url('admin/accounts'))->with('success', '관리자 계정이 등록되었습니다.');
    }

    public function accountEdit($id)
    {
        if (!$this->canManageAdminAccounts()) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '최고 관리자 admin 계정만 관리자 계정을 변경할 수 있습니다.');
        }

        $account = $this->db->table('admin_users')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$account) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '관리자 계정을 찾을 수 없습니다.');
        }

        return view('admin/account/form', [
            'title' => '관리자 계정 수정',
            'breadcrumb' => 'Main > 계정관리 > 수정',
            'backUrl' => base_url('admin/accounts'),
            'actionUrl' => base_url('admin/accounts/' . (int) $id . '/update'),
            'account' => $account,
            'buttonLabel' => '수정',
            'passwordRequired' => false,
            'roleOptions' => $this->adminRoleOptions(),
        ]);
    }

    public function accountUpdate($id)
    {
        if (!$this->canManageAdminAccounts()) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '최고 관리자 admin 계정만 관리자 계정을 변경할 수 있습니다.');
        }

        $account = $this->db->table('admin_users')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$account) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '관리자 계정을 찾을 수 없습니다.');
        }

        $data = $this->accountPostData($account);

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('admin_users')
            ->where('id', (int) $id)
            ->update($data);

        return redirect()->to(base_url('admin/accounts'))->with('success', '관리자 계정이 수정되었습니다.');
    }

    public function accountStatus($id)
    {
        if (!$this->canManageAdminAccounts()) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '최고 관리자 admin 계정만 관리자 계정을 변경할 수 있습니다.');
        }

        $account = $this->db->table('admin_users')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$account) {
            return redirect()->to(base_url('admin/accounts'))->with('error', '관리자 계정을 찾을 수 없습니다.');
        }

        $status = strtoupper(trim((string) $this->request->getPost('status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            return redirect()->back()->with('error', '변경할 상태값이 올바르지 않습니다.');
        }

        if (($account['username'] ?? '') === 'admin' && $status === 'N') {
            return redirect()->back()->with('error', '최고 관리자 admin 계정은 중지할 수 없습니다.');
        }

        if ((int) session()->get('admin_id') === (int) $id && $status === 'N') {
            return redirect()->back()->with('error', '현재 로그인한 본인 계정은 중지할 수 없습니다.');
        }

        $this->db->table('admin_users')
            ->where('id', (int) $id)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(base_url('admin/accounts'))->with('success', '계정 상태가 변경되었습니다.');
    }

    public function codes(string $group = '')
    {
        $filters = $this->codeFilters($group);
        $builder = $this->codeListBuilder();
        $this->applyCodeFilters($builder, $filters);

        $countBuilder = $this->codeListBuilder();
        $this->applyCodeFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();

        $rows = $builder
            ->orderBy('code_group', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('code_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '코드 관리',
            'breadcrumb' => 'Main > 코드관리',
            'countLabel' => '코드',
            'count' => $total,
            'searchPlaceholder' => '코드값, 코드명, 설명으로 검색',
            'searchValue' => $filters['q'],
            'searchHidden' => [
                'code_group' => $filters['code_group'],
                'display_status' => $filters['display_status'],
            ],
            'tabs' => $this->codeTabs($filters),
            'actions' => [['label' => '코드 등록', 'url' => base_url('admin/codes/create')]],
            'headers' => ['구분', '코드값', '코드명', '상태', '정렬', '등록자', '등록일', '관리'],
            'rows' => array_map(function ($row) {
                $id = (int) ($row['code_id'] ?? 0);
                $deleteForm = '<form action="' . base_url('admin/codes/' . $id . '/delete') . '" method="post" onsubmit="return confirm(\'코드를 삭제하시겠습니까?\');" style="display:inline-block;margin-left:6px;">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">삭제</button></form>';

                return [
                    esc($this->codeGroupLabel((string) ($row['code_group'] ?? ''))),
                    esc($row['code_value'] ?? '-'),
                    '<a href="' . base_url('admin/codes/' . $id . '/edit') . '">' . esc($row['code_label'] ?? '-') . '</a>',
                    $this->displayStatusLabel((string) ($row['display_status'] ?? 'N')),
                    esc((string) ((int) ($row['sort_order'] ?? 0))),
                    esc($row['created_by'] ?? '-'),
                    esc($row['created_at'] ?? '-'),
                    '<a href="' . base_url('admin/codes/' . $id . '/edit') . '" class="btn btn-outline-primary btn-sm">수정</a>' . $deleteForm,
                ];
            }, $rows),
        ]);
    }

    public function codeCreate()
    {
        return view('admin/code/form', [
            'title' => '코드 등록',
            'breadcrumb' => 'Main > 코드관리 > 등록',
            'backUrl' => base_url('admin/codes'),
            'actionUrl' => base_url('admin/codes/create'),
            'code' => [],
            'buttonLabel' => '저장',
            'codeGroups' => $this->codeGroupOptions(),
        ]);
    }

    public function codeStore()
    {
        $data = $this->codePostData();

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_common_code')->insert($data);

        return redirect()->to(base_url('admin/codes'))->with('success', '코드가 등록되었습니다.');
    }

    public function codeEdit($id)
    {
        $code = $this->db->table('my_fc_common_code')
            ->where('code_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$code) {
            return redirect()->to(base_url('admin/codes'))->with('error', '코드 정보를 찾을 수 없습니다.');
        }

        return view('admin/code/form', [
            'title' => '코드 수정',
            'breadcrumb' => 'Main > 코드관리 > 수정',
            'backUrl' => base_url('admin/codes'),
            'actionUrl' => base_url('admin/codes/' . (int) $id . '/update'),
            'code' => $code,
            'buttonLabel' => '수정',
            'codeGroups' => $this->codeGroupOptions(),
        ]);
    }

    public function codeUpdate($id)
    {
        $code = $this->db->table('my_fc_common_code')
            ->where('code_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$code) {
            return redirect()->to(base_url('admin/codes'))->with('error', '코드 정보를 찾을 수 없습니다.');
        }

        $data = $this->codePostData($code);

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_common_code')
            ->where('code_id', (int) $id)
            ->update($data);

        return redirect()->to(base_url('admin/codes'))->with('success', '코드가 수정되었습니다.');
    }

    public function codeDelete($id)
    {
        $code = $this->db->table('my_fc_common_code')
            ->where('code_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$code) {
            return redirect()->to(base_url('admin/codes'))->with('error', '코드 정보를 찾을 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('my_fc_common_code')
            ->where('code_id', (int) $id)
            ->update([
                'display_status' => 'N',
                'updated_at' => $now,
                'deleted_at' => $now,
            ]);

        return redirect()->to(base_url('admin/codes'))->with('success', '코드가 삭제되었습니다.');
    }

    public function forbiddenWords()
    {
        $filters = $this->forbiddenWordFilters();
        $builder = $this->forbiddenWordListBuilder();
        $this->applyForbiddenWordFilters($builder, $filters);

        $countBuilder = $this->forbiddenWordListBuilder();
        $this->applyForbiddenWordFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();

        $rows = $builder
            ->orderBy('word_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '금칙어 관리',
            'breadcrumb' => 'Main > 금칙어 관리',
            'countLabel' => '금칙어',
            'count' => $total,
            'searchPlaceholder' => '금칙어 또는 메모로 검색',
            'searchValue' => $filters['q'],
            'searchHidden' => [
                'display_status' => $filters['display_status'],
                'apply_scope' => $filters['apply_scope'],
            ],
            'tabs' => $this->forbiddenWordTabs($filters),
            'actions' => [['label' => '금칙어 등록', 'url' => base_url('admin/forbidden-words/create')]],
            'headers' => ['금칙어', '매칭', '적용 범위', '상태', '등록자', '등록일', '관리'],
            'rows' => array_map(function ($row) {
                $id = (int) ($row['word_id'] ?? 0);
                $deleteForm = '<form action="' . base_url('admin/forbidden-words/' . $id . '/delete') . '" method="post" onsubmit="return confirm(\'금칙어를 삭제하시겠습니까?\');" style="display:inline-block;margin-left:6px;">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">삭제</button></form>';

                return [
                    '<a href="' . base_url('admin/forbidden-words/' . $id . '/edit') . '">' . esc($row['keyword'] ?? '-') . '</a>',
                    esc($this->forbiddenMatchTypeLabel((string) ($row['match_type'] ?? 'PARTIAL'))),
                    esc($this->forbiddenScopeLabel((string) ($row['apply_scope'] ?? 'ALL'))),
                    $this->displayStatusLabel((string) ($row['display_status'] ?? 'N')),
                    esc($row['created_by'] ?? '-'),
                    esc($row['created_at'] ?? '-'),
                    '<a href="' . base_url('admin/forbidden-words/' . $id . '/edit') . '" class="btn btn-outline-primary btn-sm">수정</a>' . $deleteForm,
                ];
            }, $rows),
        ]);
    }

    public function forbiddenWordCreate()
    {
        return view('admin/forbidden_word/form', [
            'title' => '금칙어 등록',
            'breadcrumb' => 'Main > 금칙어 관리 > 등록',
            'backUrl' => base_url('admin/forbidden-words'),
            'actionUrl' => base_url('admin/forbidden-words/create'),
            'word' => [],
            'buttonLabel' => '저장',
            'matchTypes' => $this->forbiddenMatchTypeOptions(),
            'scopes' => $this->forbiddenScopeOptions(),
        ]);
    }

    public function forbiddenWordStore()
    {
        $data = $this->forbiddenWordPostData();

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_forbidden_word')->insert($data);

        return redirect()->to(base_url('admin/forbidden-words'))->with('success', '금칙어가 등록되었습니다.');
    }

    public function forbiddenWordEdit($id)
    {
        $word = $this->db->table('my_fc_forbidden_word')
            ->where('word_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$word) {
            return redirect()->to(base_url('admin/forbidden-words'))->with('error', '금칙어 정보를 찾을 수 없습니다.');
        }

        return view('admin/forbidden_word/form', [
            'title' => '금칙어 수정',
            'breadcrumb' => 'Main > 금칙어 관리 > 수정',
            'backUrl' => base_url('admin/forbidden-words'),
            'actionUrl' => base_url('admin/forbidden-words/' . (int) $id . '/update'),
            'word' => $word,
            'buttonLabel' => '수정',
            'matchTypes' => $this->forbiddenMatchTypeOptions(),
            'scopes' => $this->forbiddenScopeOptions(),
        ]);
    }

    public function forbiddenWordUpdate($id)
    {
        $word = $this->db->table('my_fc_forbidden_word')
            ->where('word_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$word) {
            return redirect()->to(base_url('admin/forbidden-words'))->with('error', '금칙어 정보를 찾을 수 없습니다.');
        }

        $data = $this->forbiddenWordPostData($word);

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_forbidden_word')
            ->where('word_id', (int) $id)
            ->update($data);

        return redirect()->to(base_url('admin/forbidden-words'))->with('success', '금칙어가 수정되었습니다.');
    }

    public function forbiddenWordDelete($id)
    {
        $word = $this->db->table('my_fc_forbidden_word')
            ->where('word_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$word) {
            return redirect()->to(base_url('admin/forbidden-words'))->with('error', '금칙어 정보를 찾을 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('my_fc_forbidden_word')
            ->where('word_id', (int) $id)
            ->update([
                'display_status' => 'N',
                'updated_at' => $now,
                'deleted_at' => $now,
            ]);

        return redirect()->to(base_url('admin/forbidden-words'))->with('success', '금칙어가 삭제되었습니다.');
    }

    public function placeholderPage($slug = '')
    {
        $map = [
            'popups' => ['팝업 관리', 'Main > 알림 관리 > 팝업관리', ['팝업 관리 테이블이 없습니다.']],
            'popups-create' => ['팝업 추가', 'Main > 알림 관리 > 팝업추가', ['팝업 관리 테이블이 없습니다.']],
            'pushes' => ['앱푸쉬', 'Main > 알림 관리 > 앱푸쉬', ['앱푸쉬 발송/예약 테이블이 없습니다.']],
            'pushes-create' => ['앱푸쉬 추가', 'Main > 알림 관리 > 앱푸쉬 추가', ['앱푸쉬 이미지, 클릭 URL, 발송 예약 테이블이 없습니다.']],
            'terms' => ['약관 관리', 'Main > 약관 관리', ['약관 버전 관리 테이블이 없습니다.']],
            'terms-create' => ['약관 등록', 'Main > 약관 관리 > 등록', ['약관 저장 테이블이 없습니다.']],
            'codes' => ['코드 관리', 'Main > 코드관리', ['공통 코드 테이블이 없습니다. 보험상품군, 지역, 언어, 원수사, GA 코드 테이블이 필요합니다.']],
            'forbidden' => ['금칙어 관리', 'Main > 금칙어 관리', ['금칙어 테이블이 없습니다.']],
            'stats' => ['통계 관리', 'Main > 통계 관리', ['통계 관리는 PPT 기준 2차 예정입니다.']],
            'preview' => ['프로필 미리보기', 'Main > 대시보드 > FC회원 > 상세 > 프로필 미리보기', ['프론트 프로필 미리보기 팝업 연동은 준비중입니다.']],
            'fc-edit' => ['FC 회원 수정', 'Main > 대시보드 > FC회원 > 상세 > 회원 수정', ['FC 전체 프로필 수정 저장 프로그램은 준비중입니다.']],
            'fc-create' => ['FC 회원 신규 등록', 'Main > 대시보드 > FC회원 > 신규 등록', ['FC 회원 신규 등록 프로그램은 준비중입니다.']],
            'member-create' => ['개인회원 신규 등록', 'Main > 대시보드 > 개인회원 > 신규 등록', ['개인회원 신규 등록 프로그램은 준비중입니다.']],
        ];
        $item = $map[$slug] ?? ['준비중', 'Main', ['해당 기능은 준비중입니다.']];

        return $this->placeholder($item[0], $item[1], $item[2]);
    }

    public function popups()
    {
        $filters = $this->popupFilters();
        $builder = $this->popupListBuilder();
        $this->applyPopupFilters($builder, $filters);

        $countBuilder = $this->popupListBuilder();
        $this->applyPopupFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();

        $rows = $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('popup_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '팝업 관리',
            'breadcrumb' => 'Main > 알림 관리 > 팝업관리',
            'countLabel' => '팝업 관리',
            'count' => $total,
            'tabs' => $this->popupTabs($filters),
            'actions' => [
                ['label' => '팝업 등록', 'url' => base_url('admin/popups/create')],
            ],
            'headers' => ['팝업', '노출상태', '노출기간', '정렬', '등록일', '관리'],
            'rows' => array_map(function ($row) {
                $thumb = !empty($row['image_path'])
                    ? '<img src="' . esc(base_url(ltrim($row['image_path'], '/'))) . '" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #d8e0ea;">'
                    : '<span class="text-muted">이미지 없음</span>';

                $title = '<a href="' . base_url('admin/popups/' . (int) $row['popup_id']) . '">' . esc($row['title'] ?? '-') . '</a>';
                $deleteForm = '<form action="' . esc(base_url('admin/popups/' . (int) $row['popup_id'] . '/delete')) . '" method="post" onsubmit="return confirm(\'이 팝업을 삭제하시겠습니까?\');" style="display:inline-block;margin-left:6px;">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">삭제</button></form>';

                return [
                    $thumb . '<div class="mt-2 fw-semibold">' . $title . '</div>',
                    $this->popupStatusLabel((string) ($row['display_status'] ?? 'N')),
                    esc(($row['start_at'] ?? '-') . ' ~ ' . ($row['end_at'] ?? '-')),
                    esc((string) ((int) ($row['sort_order'] ?? 0))),
                    esc($row['created_at'] ?? '-'),
                    '<a href="' . base_url('admin/popups/' . (int) $row['popup_id'] . '/edit') . '" class="btn btn-outline-primary btn-sm">수정</a>' . $deleteForm,
                ];
            }, $rows),
        ]);
    }

    public function popupCreate()
    {
        return view('admin/popup/form', [
            'title' => '팝업 등록',
            'breadcrumb' => 'Main > 알림 관리 > 팝업추가',
            'backUrl' => base_url('admin/popups'),
            'actionUrl' => base_url('admin/popups/create'),
            'popup' => [],
            'buttonLabel' => '저장',
            'imageRequired' => true,
        ]);
    }

    public function popupStore()
    {
        try {
            $data = $this->popupPostData(null, false);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        try {
            $this->db->table('my_fc_popup')->insert($data);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', '팝업을 등록하지 못했습니다. 잠시 후 다시 시도해주세요.');
        }

        return redirect()->to(base_url('admin/popups'))->with('success', '팝업이 등록되었습니다.');
    }

    public function popupEdit($id)
    {
        $popup = $this->db->table('my_fc_popup')
            ->where('popup_id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$popup) {
            return redirect()->to(base_url('admin/popups'))->with('error', '팝업 정보를 찾을 수 없습니다.');
        }

        return view('admin/popup/form', [
            'title' => '팝업 수정',
            'breadcrumb' => 'Main > 알림 관리 > 팝업관리 > 수정',
            'backUrl' => base_url('admin/popups'),
            'actionUrl' => base_url('admin/popups/' . (int) $id . '/update'),
            'popup' => $popup,
            'buttonLabel' => '수정',
            'imageRequired' => false,
        ]);
    }

    public function popupUpdate($id)
    {
        $popup = $this->db->table('my_fc_popup')
            ->where('popup_id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$popup) {
            return redirect()->to(base_url('admin/popups'))->with('error', '팝업 정보를 찾을 수 없습니다.');
        }

        try {
            $data = $this->popupPostData($popup, true);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_popup')
            ->where('popup_id', (int) $id)
            ->update($data);

        return redirect()->to(base_url('admin/popups'))->with('success', '팝업이 수정되었습니다.');
    }

    public function popupDelete($id)
    {
        $popup = $this->db->table('my_fc_popup')
            ->where('popup_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$popup) {
            return redirect()->to(base_url('admin/popups'))->with('error', '팝업 정보를 찾을 수 없습니다.');
        }

        $this->deletePublicFile((string) ($popup['image_path'] ?? ''));

        $this->db->table('my_fc_popup')
            ->where('popup_id', (int) $id)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s'),
                'display_status' => 'N',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(base_url('admin/popups'))->with('success', '팝업이 삭제되었습니다.');
    }

    public function pushes()
    {
        $filters = $this->pushFilters();
        $builder = $this->pushListBuilder();
        $this->applyPushFilters($builder, $filters);

        $countBuilder = $this->pushListBuilder();
        $this->applyPushFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();

        $rows = $builder
            ->orderBy('push_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '앱푸쉬',
            'breadcrumb' => 'Main > 알림 관리 > 앱푸쉬',
            'countLabel' => '앱푸쉬',
            'count' => $total,
            'searchPlaceholder' => '푸시 제목 또는 내용으로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => ['status' => $filters['status']],
            'tabs' => $this->pushTabs($filters),
            'actions' => [
                ['label' => '푸시 등록', 'url' => base_url('admin/pushes/create')],
            ],
            'headers' => ['푸시', '대상', '발송상태', '예약/발송일', '발송수', '등록일'],
            'rows' => array_map(function ($row) {
                $thumb = !empty($row['image_path'])
                    ? '<img src="' . esc(base_url(ltrim($row['image_path'], '/'))) . '" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #d8e0ea;">'
                    : '<span class="text-muted">이미지 없음</span>';

                $title = '<a href="' . base_url('admin/pushes/' . (int) $row['push_id']) . '">' . esc($row['title'] ?? '-') . '</a>';
                $schedule = ($row['send_type'] ?? '') === 'RESERVED'
                    ? ($row['scheduled_at'] ?? '-')
                    : ($row['sent_at'] ?? $row['scheduled_at'] ?? '-');

                return [
                    $thumb . '<div class="mt-2 fw-semibold">' . $title . '</div><div class="text-muted small">' . esc(mb_strimwidth((string) ($row['body'] ?? ''), 0, 80, '...')) . '</div>',
                    $this->pushTargetLabel((string) ($row['target_type'] ?? 'ALL')),
                    $this->pushStatusLabel((string) ($row['status'] ?? 'READY')),
                    esc($schedule ?: '-'),
                    number_format((int) ($row['total_count'] ?? 0)) . ' / 성공 ' . number_format((int) ($row['success_count'] ?? 0)) . ' / 실패 ' . number_format((int) ($row['fail_count'] ?? 0)),
                    esc($row['created_at'] ?? '-'),
                ];
            }, $rows),
        ]);
    }

    public function pushCreate()
    {
        return view('admin/push/form', [
            'title' => '앱푸쉬 등록',
            'breadcrumb' => 'Main > 알림 관리 > 앱푸쉬 추가',
            'backUrl' => base_url('admin/pushes'),
            'actionUrl' => base_url('admin/pushes/create'),
            'push' => [],
            'buttonLabel' => '저장',
        ]);
    }

    public function pushStore()
    {
        try {
            $data = $this->pushPostData();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $targets = $this->pushTargetMembers((string) $data['target_type']);
        $data['total_count'] = count($targets);

        $this->db->transStart();
        $this->db->table('my_fc_push')->insert($data);
        $pushId = (int) $this->db->insertID();

        foreach ($targets as $target) {
            $this->db->table('my_fc_push_target')->insert([
                'push_id' => $pushId,
                'member_id' => (int) $target['member_id'],
                'member_uid' => $target['member_uid'],
                'fcm_token' => $target['fcm_token'],
                'send_status' => 'WAIT',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', '앱푸쉬 저장에 실패했습니다.');
        }

        return redirect()->to(base_url('admin/pushes/' . $pushId))->with('success', '앱푸쉬가 등록되었습니다.');
    }

    public function pushDetail($id)
    {
        $push = $this->db->table('my_fc_push')
            ->where('push_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$push) {
            return redirect()->to(base_url('admin/pushes'))->with('error', '앱푸쉬 정보를 찾을 수 없습니다.');
        }

        $recentTargets = $this->db->table('my_fc_push_target t')
            ->select('t.*, m.name, m.email, m.member_type')
            ->join('my_fc_member m', 'm.member_uid = t.member_uid', 'left')
            ->where('t.push_id', (int) $id)
            ->orderBy('t.target_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        $cancelForm = '';
        if (in_array($push['status'], ['READY', 'RESERVED', 'FAILED'], true)) {
            $cancelForm = '<form action="' . base_url('admin/pushes/' . (int) $id . '/cancel') . '" method="post" onsubmit="return confirm(\'발송을 취소하시겠습니까?\');" style="display:inline-block;margin-left:8px;">'
                . csrf_field()
                . '<button type="submit" class="btn btn-outline-danger btn-sm">발송취소</button></form>';
        }

        return $this->page([
            'title' => '앱푸쉬 상세',
            'breadcrumb' => 'Main > 알림 관리 > 앱푸쉬 > 상세',
            'backUrl' => base_url('admin/pushes'),
            'actions' => [
                ['label' => '목록', 'url' => base_url('admin/pushes')],
            ],
            'summary' => [
                '발송 대상' => number_format((int) ($push['total_count'] ?? 0)),
                '성공' => number_format((int) ($push['success_count'] ?? 0)),
                '실패' => number_format((int) ($push['fail_count'] ?? 0)),
                '클릭' => number_format((int) ($push['click_count'] ?? 0)),
            ],
            'detail' => [
                '제목' => esc($push['title'] ?? '-'),
                '내용' => nl2br(esc((string) ($push['body'] ?? '-'))),
                '이미지' => !empty($push['image_path'])
                    ? '<img src="' . esc(base_url(ltrim($push['image_path'], '/'))) . '" alt="" style="max-width:240px;border:1px solid #d8e0ea;border-radius:8px;">'
                    : '<span class="text-muted">이미지 없음</span>',
                '클릭 URL' => !empty($push['click_url'])
                    ? '<a href="' . esc($push['click_url']) . '" target="_blank" rel="noopener">' . esc($push['click_url']) . '</a>'
                    : '-',
                '대상' => $this->pushTargetLabel((string) ($push['target_type'] ?? 'ALL')),
                '발송 유형' => ($push['send_type'] ?? '') === 'RESERVED' ? '예약 발송' : '즉시 발송',
                '발송 상태' => $this->pushStatusLabel((string) ($push['status'] ?? 'READY')) . $cancelForm,
                '예약일시' => esc($push['scheduled_at'] ?? '-'),
                '발송일시' => esc($push['sent_at'] ?? '-'),
                '취소일시' => esc($push['canceled_at'] ?? '-'),
                '등록일' => esc($push['created_at'] ?? '-'),
            ],
            'headers' => ['회원', '유형', '발송상태', '발송일시', '오류'],
            'rows' => array_map(function ($row) {
                return [
                    esc(($row['name'] ?? '-') . ' (' . ($row['email'] ?? '-') . ')'),
                    ($row['member_type'] ?? '') === 'FC' ? 'FC회원' : '개인회원',
                    $this->pushTargetStatusLabel((string) ($row['send_status'] ?? 'WAIT')),
                    esc($row['sent_at'] ?? '-'),
                    esc($row['error_message'] ?? '-'),
                ];
            }, $recentTargets),
        ]);
    }

    public function pushCancel($id)
    {
        $push = $this->db->table('my_fc_push')
            ->where('push_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$push) {
            return redirect()->to(base_url('admin/pushes'))->with('error', '앱푸쉬 정보를 찾을 수 없습니다.');
        }

        if (in_array($push['status'], ['SENDING', 'SENT', 'CANCELED'], true)) {
            return redirect()->back()->with('error', '이미 발송 중이거나 완료/취소된 푸시는 취소할 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();
        $this->db->table('my_fc_push')
            ->where('push_id', (int) $id)
            ->update([
                'status' => 'CANCELED',
                'canceled_at' => $now,
                'updated_at' => $now,
            ]);

        $this->db->table('my_fc_push_target')
            ->where('push_id', (int) $id)
            ->where('send_status', 'WAIT')
            ->update([
                'send_status' => 'CANCELED',
                'updated_at' => $now,
            ]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', '앱푸쉬 취소에 실패했습니다.');
        }

        return redirect()->to(base_url('admin/pushes/' . (int) $id))->with('success', '앱푸쉬 발송이 취소되었습니다.');
    }

    public function terms()
    {
        $filters = $this->termFilters();
        $builder = $this->termListBuilder();
        $this->applyTermFilters($builder, $filters);

        $countBuilder = $this->termListBuilder();
        $this->applyTermFilters($countBuilder, $filters);
        $total = $countBuilder->countAllResults();

        $rows = $builder
            ->orderBy('term_id', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->page([
            'title' => '약관 관리',
            'breadcrumb' => 'Main > 약관 관리',
            'countLabel' => '약관',
            'count' => $total,
            'searchPlaceholder' => '약관 제목, 버전, 내용으로 검색',
            'searchValue' => $filters['q'],
            'dateFrom' => $filters['start_date'],
            'dateTo' => $filters['end_date'],
            'searchHidden' => [
                'term_type' => $filters['term_type'],
                'display_status' => $filters['display_status'],
            ],
            'tabs' => $this->termTabs($filters),
            'actions' => [
                ['label' => '약관 등록', 'url' => base_url('admin/terms/create')],
            ],
            'headers' => ['타입', '버전', '제목', '상태', '등록자', '등록일', '관리'],
            'rows' => array_map(function ($row) {
                $deleteForm = '<form action="' . base_url('admin/terms/' . (int) $row['term_id'] . '/delete') . '" method="post" onsubmit="return confirm(\'약관을 삭제하시겠습니까?\');" style="display:inline-block;margin-left:6px;">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">삭제</button></form>';

                return [
                    esc($this->termTypeLabel((string) ($row['term_type'] ?? ''))),
                    esc($row['version'] ?? '-'),
                    '<a href="' . base_url('admin/terms/' . (int) $row['term_id']) . '">' . esc($row['title'] ?? '-') . '</a>',
                    $this->termStatusLabel((string) ($row['display_status'] ?? 'N')),
                    esc($row['created_by'] ?? '-'),
                    esc($row['created_at'] ?? '-'),
                    '<a href="' . base_url('admin/terms/' . (int) $row['term_id'] . '/edit') . '" class="btn btn-outline-primary btn-sm">수정</a>' . $deleteForm,
                ];
            }, $rows),
        ]);
    }

    public function termCreate()
    {
        return view('admin/terms/form', [
            'title' => '약관 등록',
            'breadcrumb' => 'Main > 약관 관리 > 등록',
            'backUrl' => base_url('admin/terms'),
            'actionUrl' => base_url('admin/terms/create'),
            'term' => [],
            'buttonLabel' => '저장',
            'termTypes' => $this->termTypeOptions(),
        ]);
    }

    public function termStore()
    {
        $data = $this->termPostData();

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_terms')->insert($data);

        return redirect()->to(base_url('admin/terms'))->with('success', '약관이 등록되었습니다.');
    }

    public function termEdit($id)
    {
        $term = $this->db->table('my_fc_terms')
            ->where('term_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$term) {
            return redirect()->to(base_url('admin/terms'))->with('error', '약관 정보를 찾을 수 없습니다.');
        }

        return view('admin/terms/form', [
            'title' => '약관 수정',
            'breadcrumb' => 'Main > 약관 관리 > 수정',
            'backUrl' => base_url('admin/terms'),
            'actionUrl' => base_url('admin/terms/' . (int) $id . '/update'),
            'term' => $term,
            'buttonLabel' => '수정',
            'termTypes' => $this->termTypeOptions(),
        ]);
    }

    public function termUpdate($id)
    {
        $term = $this->db->table('my_fc_terms')
            ->where('term_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$term) {
            return redirect()->to(base_url('admin/terms'))->with('error', '약관 정보를 찾을 수 없습니다.');
        }

        $data = $this->termPostData($term);

        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }

        $this->db->table('my_fc_terms')
            ->where('term_id', (int) $id)
            ->update($data);

        return redirect()->to(base_url('admin/terms'))->with('success', '약관이 수정되었습니다.');
    }

    public function termDelete($id)
    {
        $term = $this->db->table('my_fc_terms')
            ->where('term_id', (int) $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$term) {
            return redirect()->to(base_url('admin/terms'))->with('error', '약관 정보를 찾을 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('my_fc_terms')
            ->where('term_id', (int) $id)
            ->update([
                'display_status' => 'N',
                'updated_at' => $now,
                'deleted_at' => $now,
            ]);

        return redirect()->to(base_url('admin/terms'))->with('success', '약관이 삭제되었습니다.');
    }

    private function counselListBuilder()
    {
        $rejectReasonSelect = $this->hasCounselRejectReasonColumn()
            ? 'c.reject_reason'
            : "'' AS reject_reason";

        return $this->db->table('my_fc_counsel c')
            ->select(
                'c.counsel_id, c.name, c.email, c.phone, c.status, c.reserve_datetime, c.created_at, '
                . $rejectReasonSelect
                . ', fm.name AS fc_name, fm.email AS fc_email, fm.phone AS fc_phone',
                false
            )
            ->join('my_fc_member fm', 'fm.member_uid = c.fc_member_uid', 'left')
            ->where('c.deleted_at', null);
    }

    private function counselFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        if (!in_array($status, ['REQUEST', 'PROGRESS', 'COMPLETE', 'CANCEL'], true)) {
            $status = '';
        }

        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        return [
            'status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function applyCounselFilters($builder, array $filters): void
    {
        if ($filters['status'] !== '') {
            $builder->where('c.status', $filters['status']);
        }

        if ($filters['start_date'] !== '') {
            $builder->where('c.created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if ($filters['end_date'] !== '') {
            $builder->where('c.created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if ($filters['q'] !== '') {
            $keyword = $filters['q'];
            $builder
                ->groupStart()
                ->like('c.name', $keyword)
                ->orLike('c.email', $keyword)
                ->orLike('c.phone', $keyword)
                ->orLike('fm.name', $keyword)
                ->orLike('fm.email', $keyword)
                ->orLike('fm.phone', $keyword)
                ->groupEnd();
        }
    }

    private function counselTabs(array $filters): array
    {
        $tabs = [
            '' => '전체',
            'REQUEST' => '상담 대기',
            'COMPLETE' => '상담완료',
            'CANCEL' => '상담거부',
        ];

        $items = [];
        foreach ($tabs as $status => $label) {
            $query = array_filter([
                'status' => $status,
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/contents/counsels');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
                'active' => $filters['status'] === $status,
            ];
        }

        return $items;
    }

    private function hasCounselRejectReasonColumn(): bool
    {
        return $this->db->fieldExists('reject_reason', 'my_fc_counsel');
    }

    private function reviewBuilder()
    {
        return $this->db->table('my_fc_counsel_review r')
            ->select('r.*, u.name AS user_name, u.email AS user_email, f.name AS fc_name, f.email AS fc_email')
            ->join('my_fc_member u', 'u.member_uid = r.member_uid', 'left')
            ->join('my_fc_member f', 'f.member_uid = r.fc_member_uid', 'left')
            ->where('r.deleted_at', null);
    }

    private function popupListBuilder()
    {
        return $this->db->table('my_fc_popup')
            ->where('deleted_at', null);
    }

    private function popupFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('display_status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            $status = '';
        }

        return [
            'display_status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => trim((string) $this->request->getGet('start_date')),
            'end_date' => trim((string) $this->request->getGet('end_date')),
        ];
    }

    private function applyPopupFilters($builder, array $filters): void
    {
        if (($filters['display_status'] ?? '') !== '') {
            $builder->where('display_status', $filters['display_status']);
        }
    }

    private function popupTabs(array $filters): array
    {
        $tabs = [
            '' => '전체',
            'Y' => '노출',
            'N' => '비노출',
        ];

        $items = [];
        foreach ($tabs as $status => $label) {
            $query = array_filter([
                'display_status' => $status,
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/popups');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
                'active' => ($filters['display_status'] ?? '') === $status,
            ];
        }

        return $items;
    }

    private function popupStatusLabel(string $status): string
    {
        return $status === 'Y' ? '노출' : '비노출';
    }

    private function popupPostData(?array $existingPopup = null, bool $isUpdate = false)
    {
        $title = trim((string) $this->request->getPost('title'));
        $linkUrl = trim((string) $this->request->getPost('link_url'));
        $linkTarget = (string) $this->request->getPost('link_target');
        $displayStatus = strtoupper(trim((string) $this->request->getPost('display_status')));
        $sortOrder = (int) $this->request->getPost('sort_order');
        $startAtRaw = trim((string) $this->request->getPost('start_at'));
        $endAtRaw = trim((string) $this->request->getPost('end_at'));

        if ($title === '') {
            return redirect()->back()->withInput()->with('error', '팝업 제목을 입력해주세요.');
        }

        if (!in_array($displayStatus, ['Y', 'N'], true)) {
            $displayStatus = 'Y';
        }

        if (!in_array($linkTarget, ['_self', '_blank'], true)) {
            $linkTarget = '_self';
        }

        $startAt = $this->normalizeDateTimeInput($startAtRaw);
        $endAt = $this->normalizeDateTimeInput($endAtRaw);

        if ($startAt === null || $endAt === null) {
            return redirect()->back()->withInput()->with('error', '노출 시작일과 종료일을 입력해주세요.');
        }

        if (strtotime($startAt) > strtotime($endAt)) {
            return redirect()->back()->withInput()->with('error', '노출 시작일은 종료일보다 빠르거나 같아야 합니다.');
        }

        $imagePath = $existingPopup['image_path'] ?? '';
        $file = $this->request->getFile('popup_image');

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $savedName = $this->storePopupImage($file);
            $this->deletePublicFile($imagePath);
            $imagePath = '/uploads/popup/' . $savedName;
        } elseif ($imagePath === '') {
            return redirect()->back()->withInput()->with('error', '팝업 이미지를 업로드해주세요.');
        }

        $data = [
            'title' => $title,
            'image_path' => $imagePath,
            'link_url' => $linkUrl !== '' ? $linkUrl : null,
            'link_target' => $linkTarget,
            'display_status' => $displayStatus,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'sort_order' => $sortOrder,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (! $isUpdate) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    private function storePopupImage($file): string
    {
        $extension = strtolower((string) $file->getClientExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            throw new \RuntimeException('팝업 이미지는 JPG, PNG, WEBP, GIF 파일만 등록할 수 있습니다.');
        }

        $targetPath = FCPATH . 'uploads/popup';
        if (!is_dir($targetPath) && !mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
            throw new \RuntimeException('팝업 이미지 저장 경로를 준비하지 못했습니다.');
        }

        $savedName = $file->getRandomName();
        $file->move($targetPath, $savedName);

        return $savedName;
    }

    private function pushListBuilder()
    {
        return $this->db->table('my_fc_push')
            ->where('deleted_at', null);
    }

    private function pushFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        if (!in_array($status, ['READY', 'RESERVED', 'SENDING', 'SENT', 'CANCELED', 'FAILED'], true)) {
            $status = '';
        }

        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        return [
            'status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function applyPushFilters($builder, array $filters): void
    {
        if (($filters['status'] ?? '') !== '') {
            $builder->where('status', $filters['status']);
        }

        if (($filters['start_date'] ?? '') !== '') {
            $builder->where('created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if (($filters['end_date'] ?? '') !== '') {
            $builder->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if (($filters['q'] ?? '') !== '') {
            $builder
                ->groupStart()
                ->like('title', $filters['q'])
                ->orLike('body', $filters['q'])
                ->groupEnd();
        }
    }

    private function pushTabs(array $filters): array
    {
        $tabs = [
            '' => '전체',
            'READY' => '발송대기',
            'RESERVED' => '예약',
            'SENT' => '발송완료',
            'CANCELED' => '취소',
            'FAILED' => '실패',
        ];

        $items = [];
        foreach ($tabs as $status => $label) {
            $query = array_filter([
                'status' => $status,
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/pushes');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
                'active' => ($filters['status'] ?? '') === $status,
            ];
        }

        return $items;
    }

    private function pushPostData()
    {
        helper('fileupload_helper');

        $title = trim((string) $this->request->getPost('title'));
        $body = trim((string) $this->request->getPost('body'));
        $clickUrl = trim((string) $this->request->getPost('click_url'));
        $targetType = strtoupper(trim((string) $this->request->getPost('target_type')));
        $sendType = strtoupper(trim((string) $this->request->getPost('send_type')));
        $scheduledAtRaw = trim((string) $this->request->getPost('scheduled_at'));

        if ($title === '') {
            return redirect()->back()->withInput()->with('error', '푸시 제목을 입력해주세요.');
        }

        if ($body === '') {
            return redirect()->back()->withInput()->with('error', '푸시 내용을 입력해주세요.');
        }

        if (!in_array($targetType, ['ALL', 'USER', 'FC'], true)) {
            $targetType = 'ALL';
        }

        if (!in_array($sendType, ['NOW', 'RESERVED'], true)) {
            $sendType = 'NOW';
        }

        $scheduledAt = null;
        $status = 'READY';

        if ($sendType === 'RESERVED') {
            $scheduledAt = $this->normalizeDateTimeInput($scheduledAtRaw);
            if ($scheduledAt === null) {
                return redirect()->back()->withInput()->with('error', '예약 발송일시를 입력해주세요.');
            }

            $status = 'RESERVED';
        } else {
            $scheduledAt = date('Y-m-d H:i:s');
        }

        $imagePath = null;
        $file = $this->request->getFile('push_image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $savedName = upload_file($file, 'uploads/push', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
            $imagePath = '/uploads/push/' . $savedName;
        }

        $now = date('Y-m-d H:i:s');

        return [
            'title' => $title,
            'body' => $body,
            'image_path' => $imagePath,
            'click_url' => $clickUrl !== '' ? $clickUrl : null,
            'target_type' => $targetType,
            'send_type' => $sendType,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'created_by' => (string) (session()->get('admin_username') ?? session()->get('admin_id') ?? 'admin'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function pushTargetMembers(string $targetType): array
    {
        $builder = $this->db->table('my_fc_member')
            ->select('member_id, member_uid, fcm_token')
            ->where('deleted_at', null)
            ->where('status', 'ACTIVE')
            ->where('fcm_token IS NOT NULL', null, false)
            ->where('fcm_token !=', '');

        if ($targetType === 'USER') {
            $builder->where('member_type', 'USER');
        } elseif ($targetType === 'FC') {
            $builder->where('member_type', 'FC');
        }

        return $builder
            ->orderBy('member_id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function pushStatusLabel(string $status): string
    {
        return [
            'READY' => '발송대기',
            'RESERVED' => '예약',
            'SENDING' => '발송중',
            'SENT' => '발송완료',
            'CANCELED' => '발송취소',
            'FAILED' => '발송실패',
        ][$status] ?? $status;
    }

    private function pushTargetStatusLabel(string $status): string
    {
        return [
            'WAIT' => '대기',
            'SENT' => '성공',
            'FAILED' => '실패',
            'CANCELED' => '취소',
        ][$status] ?? $status;
    }

    private function pushTargetLabel(string $targetType): string
    {
        return [
            'ALL' => '전체회원',
            'USER' => '개인회원',
            'FC' => 'FC회원',
        ][$targetType] ?? '전체회원';
    }

    private function accountPostData(?array $existingAccount = null)
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = trim((string) $this->request->getPost('phone'));
        $role = trim((string) $this->request->getPost('role'));
        $status = strtoupper(trim((string) $this->request->getPost('status')));

        if ($existingAccount !== null) {
            $username = (string) $existingAccount['username'];
        }

        if ($username === '' || !preg_match('/^[A-Za-z0-9_.-]{4,50}$/', $username)) {
            return redirect()->back()->withInput()->with('error', 'ID는 영문, 숫자, 점, 밑줄, 하이픈 4~50자로 입력해주세요.');
        }

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', '이름을 입력해주세요.');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', '이메일 형식이 올바르지 않습니다.');
        }

        if ($phone !== '' && !preg_match('/^[0-9\\-+() ]{8,20}$/', $phone)) {
            return redirect()->back()->withInput()->with('error', '휴대폰번호 형식이 올바르지 않습니다.');
        }

        if (!array_key_exists($role, $this->adminRoleOptions())) {
            $role = 'admin';
        }

        if (!in_array($status, ['Y', 'N'], true)) {
            $status = 'Y';
        }

        if ($existingAccount === null) {
            if ($password === '') {
                return redirect()->back()->withInput()->with('error', '비밀번호를 입력해주세요.');
            }

            $duplicate = $this->db->table('admin_users')
                ->where('username', $username)
                ->countAllResults();

            if ($duplicate > 0) {
                return redirect()->back()->withInput()->with('error', '이미 사용 중인 ID입니다.');
            }
        }

        if ($password !== '' && strlen($password) < 8) {
            return redirect()->back()->withInput()->with('error', '비밀번호는 8자 이상 입력해주세요.');
        }

        if ($existingAccount !== null && ($existingAccount['username'] ?? '') === 'admin' && $status === 'N') {
            return redirect()->back()->withInput()->with('error', '최고 관리자 admin 계정은 중지할 수 없습니다.');
        }

        if ($existingAccount !== null && (int) session()->get('admin_id') === (int) $existingAccount['id'] && $status === 'N') {
            return redirect()->back()->withInput()->with('error', '현재 로그인한 본인 계정은 중지할 수 없습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'username' => $username,
            'name' => $name,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone !== '' ? $phone : null,
            'role' => $role,
            'status' => $status,
            'updated_at' => $now,
        ];

        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($existingAccount === null) {
            $data['created_at'] = $now;
        }

        return $data;
    }

    private function canManageAdminAccounts(): bool
    {
        return (string) session()->get('admin_username') === 'admin';
    }

    private function adminRoleOptions(): array
    {
        return [
            'admin' => '관리자',
            'super' => '최고 관리자',
        ];
    }

    private function adminRoleLabel(string $role): string
    {
        return $this->adminRoleOptions()[$role] ?? $role;
    }

    private function codeListBuilder()
    {
        return $this->db->table('my_fc_common_code')
            ->where('deleted_at', null);
    }

    private function codeFilters(string $routeGroup = ''): array
    {
        $group = strtoupper(trim((string) ($this->request->getGet('code_group') ?: $routeGroup)));
        if (!array_key_exists($group, $this->codeGroupOptions())) {
            $group = '';
        }

        $status = strtoupper(trim((string) $this->request->getGet('display_status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            $status = '';
        }

        return [
            'code_group' => $group,
            'display_status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
        ];
    }

    private function applyCodeFilters($builder, array $filters): void
    {
        if (($filters['code_group'] ?? '') !== '') {
            $builder->where('code_group', $filters['code_group']);
        }

        if (($filters['display_status'] ?? '') !== '') {
            $builder->where('display_status', $filters['display_status']);
        }

        if (($filters['q'] ?? '') !== '') {
            $builder
                ->groupStart()
                ->like('code_value', $filters['q'])
                ->orLike('code_label', $filters['q'])
                ->orLike('description', $filters['q'])
                ->groupEnd();
        }
    }

    private function codeTabs(array $filters): array
    {
        $tabs = [
            ['label' => '전체', 'code_group' => '', 'display_status' => ''],
            ['label' => '노출', 'code_group' => '', 'display_status' => 'Y'],
            ['label' => '중지', 'code_group' => '', 'display_status' => 'N'],
        ];

        foreach ($this->codeGroupOptions() as $group => $label) {
            $tabs[] = ['label' => $label, 'code_group' => $group, 'display_status' => ''];
        }

        $items = [];
        foreach ($tabs as $tab) {
            $query = array_filter([
                'code_group' => $tab['code_group'],
                'display_status' => $tab['display_status'],
                'q' => $filters['q'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/codes');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $tab['label'],
                'url' => $url,
                'active' => ($filters['code_group'] ?? '') === $tab['code_group']
                    && ($filters['display_status'] ?? '') === $tab['display_status'],
            ];
        }

        return $items;
    }

    private function codePostData(?array $existingCode = null)
    {
        $group = strtoupper(trim((string) $this->request->getPost('code_group')));
        $value = trim((string) $this->request->getPost('code_value'));
        $label = trim((string) $this->request->getPost('code_label'));
        $parentCode = trim((string) $this->request->getPost('parent_code'));
        $description = trim((string) $this->request->getPost('description'));
        $sortOrder = (int) $this->request->getPost('sort_order');
        $status = strtoupper(trim((string) $this->request->getPost('display_status')));

        if (!array_key_exists($group, $this->codeGroupOptions())) {
            return redirect()->back()->withInput()->with('error', '코드 구분을 선택해주세요.');
        }

        if ($value === '' || !preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $value)) {
            return redirect()->back()->withInput()->with('error', '코드값은 영문, 숫자, 점, 밑줄, 하이픈 1~80자로 입력해주세요.');
        }

        if ($label === '') {
            return redirect()->back()->withInput()->with('error', '코드명을 입력해주세요.');
        }

        if (!in_array($status, ['Y', 'N'], true)) {
            $status = 'Y';
        }

        $duplicateBuilder = $this->db->table('my_fc_common_code')
            ->where('code_group', $group)
            ->where('code_value', $value)
            ->where('deleted_at', null);

        if ($existingCode !== null) {
            $duplicateBuilder->where('code_id !=', (int) $existingCode['code_id']);
        }

        if ($duplicateBuilder->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', '같은 구분과 코드값이 이미 등록되어 있습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'code_group' => $group,
            'code_value' => $value,
            'code_label' => $label,
            'parent_code' => $parentCode !== '' ? $parentCode : null,
            'description' => $description !== '' ? $description : null,
            'sort_order' => $sortOrder,
            'display_status' => $status,
            'updated_at' => $now,
        ];

        if ($existingCode === null) {
            $data['created_by'] = (string) (session()->get('admin_username') ?? session()->get('admin_id') ?? 'admin');
            $data['created_at'] = $now;
        }

        return $data;
    }

    private function codeGroupOptions(): array
    {
        return [
            'INSURANCE' => '보험상품군',
            'REGION' => '지역',
            'LANGUAGE' => '언어',
            'INSURER' => '원수사',
            'GA' => 'GA',
        ];
    }

    private function codeGroupLabel(string $group): string
    {
        return $this->codeGroupOptions()[$group] ?? $group;
    }

    private function forbiddenWordListBuilder()
    {
        return $this->db->table('my_fc_forbidden_word')
            ->where('deleted_at', null);
    }

    private function forbiddenWordFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('display_status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            $status = '';
        }

        $scope = strtoupper(trim((string) $this->request->getGet('apply_scope')));
        if (!array_key_exists($scope, $this->forbiddenScopeOptions())) {
            $scope = '';
        }

        return [
            'display_status' => $status,
            'apply_scope' => $scope,
            'q' => trim((string) $this->request->getGet('q')),
        ];
    }

    private function applyForbiddenWordFilters($builder, array $filters): void
    {
        if (($filters['display_status'] ?? '') !== '') {
            $builder->where('display_status', $filters['display_status']);
        }

        if (($filters['apply_scope'] ?? '') !== '') {
            $builder->where('apply_scope', $filters['apply_scope']);
        }

        if (($filters['q'] ?? '') !== '') {
            $builder
                ->groupStart()
                ->like('keyword', $filters['q'])
                ->orLike('memo', $filters['q'])
                ->groupEnd();
        }
    }

    private function forbiddenWordTabs(array $filters): array
    {
        $tabs = [
            ['label' => '전체', 'display_status' => '', 'apply_scope' => ''],
            ['label' => '노출', 'display_status' => 'Y', 'apply_scope' => ''],
            ['label' => '중지', 'display_status' => 'N', 'apply_scope' => ''],
        ];

        foreach ($this->forbiddenScopeOptions() as $scope => $label) {
            $tabs[] = ['label' => $label, 'display_status' => '', 'apply_scope' => $scope];
        }

        $items = [];
        foreach ($tabs as $tab) {
            $query = array_filter([
                'display_status' => $tab['display_status'],
                'apply_scope' => $tab['apply_scope'],
                'q' => $filters['q'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/forbidden-words');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $tab['label'],
                'url' => $url,
                'active' => ($filters['display_status'] ?? '') === $tab['display_status']
                    && ($filters['apply_scope'] ?? '') === $tab['apply_scope'],
            ];
        }

        return $items;
    }

    private function forbiddenWordPostData(?array $existingWord = null)
    {
        $keyword = trim((string) $this->request->getPost('keyword'));
        $matchType = strtoupper(trim((string) $this->request->getPost('match_type')));
        $scope = strtoupper(trim((string) $this->request->getPost('apply_scope')));
        $status = strtoupper(trim((string) $this->request->getPost('display_status')));
        $memo = trim((string) $this->request->getPost('memo'));

        if ($keyword === '') {
            return redirect()->back()->withInput()->with('error', '금칙어를 입력해주세요.');
        }

        if (!array_key_exists($matchType, $this->forbiddenMatchTypeOptions())) {
            $matchType = 'PARTIAL';
        }

        if (!array_key_exists($scope, $this->forbiddenScopeOptions())) {
            $scope = 'ALL';
        }

        if (!in_array($status, ['Y', 'N'], true)) {
            $status = 'Y';
        }

        $duplicateBuilder = $this->db->table('my_fc_forbidden_word')
            ->where('keyword', $keyword)
            ->where('apply_scope', $scope)
            ->where('deleted_at', null);

        if ($existingWord !== null) {
            $duplicateBuilder->where('word_id !=', (int) $existingWord['word_id']);
        }

        if ($duplicateBuilder->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', '같은 적용 범위에 동일한 금칙어가 이미 등록되어 있습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'keyword' => $keyword,
            'match_type' => $matchType,
            'apply_scope' => $scope,
            'display_status' => $status,
            'memo' => $memo !== '' ? $memo : null,
            'updated_at' => $now,
        ];

        if ($existingWord === null) {
            $data['created_by'] = (string) (session()->get('admin_username') ?? session()->get('admin_id') ?? 'admin');
            $data['created_at'] = $now;
        }

        return $data;
    }

    private function forbiddenMatchTypeOptions(): array
    {
        return [
            'PARTIAL' => '부분 일치',
            'EXACT' => '완전 일치',
            'REGEX' => '정규식',
        ];
    }

    private function forbiddenMatchTypeLabel(string $type): string
    {
        return $this->forbiddenMatchTypeOptions()[$type] ?? $type;
    }

    private function forbiddenScopeOptions(): array
    {
        return [
            'ALL' => '전체',
            'PROFILE' => '프로필',
            'REVIEW' => '후기',
            'COUNSEL' => '상담',
        ];
    }

    private function forbiddenScopeLabel(string $scope): string
    {
        return $this->forbiddenScopeOptions()[$scope] ?? $scope;
    }

    private function displayStatusLabel(string $status): string
    {
        return $status === 'Y' ? '노출' : '중지';
    }

    private function termListBuilder()
    {
        return $this->db->table('my_fc_terms')
            ->where('deleted_at', null);
    }

    private function termFilters(): array
    {
        $type = strtoupper(trim((string) $this->request->getGet('term_type')));
        if (!array_key_exists($type, $this->termTypeOptions())) {
            $type = '';
        }

        $status = strtoupper(trim((string) $this->request->getGet('display_status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            $status = '';
        }

        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        return [
            'term_type' => $type,
            'display_status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function applyTermFilters($builder, array $filters): void
    {
        if (($filters['term_type'] ?? '') !== '') {
            $builder->where('term_type', $filters['term_type']);
        }

        if (($filters['display_status'] ?? '') !== '') {
            $builder->where('display_status', $filters['display_status']);
        }

        if (($filters['start_date'] ?? '') !== '') {
            $builder->where('created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if (($filters['end_date'] ?? '') !== '') {
            $builder->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if (($filters['q'] ?? '') !== '') {
            $builder
                ->groupStart()
                ->like('title', $filters['q'])
                ->orLike('version', $filters['q'])
                ->orLike('content', $filters['q'])
                ->groupEnd();
        }
    }

    private function termTabs(array $filters): array
    {
        $tabs = [
            ['label' => '전체', 'term_type' => '', 'display_status' => ''],
            ['label' => '노출', 'term_type' => '', 'display_status' => 'Y'],
            ['label' => '중지', 'term_type' => '', 'display_status' => 'N'],
            ['label' => '이용약관', 'term_type' => 'TERMS', 'display_status' => ''],
            ['label' => '개인정보', 'term_type' => 'PRIVACY', 'display_status' => ''],
            ['label' => '법적책임', 'term_type' => 'LEGAL', 'display_status' => ''],
            ['label' => '마케팅', 'term_type' => 'MARKETING', 'display_status' => ''],
        ];

        $items = [];
        foreach ($tabs as $tab) {
            $query = array_filter([
                'term_type' => $tab['term_type'],
                'display_status' => $tab['display_status'],
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/terms');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $tab['label'],
                'url' => $url,
                'active' => ($filters['term_type'] ?? '') === $tab['term_type']
                    && ($filters['display_status'] ?? '') === $tab['display_status'],
            ];
        }

        return $items;
    }

    private function termPostData(?array $existingTerm = null)
    {
        $type = strtoupper(trim((string) $this->request->getPost('term_type')));
        $version = trim((string) $this->request->getPost('version'));
        $title = trim((string) $this->request->getPost('title'));
        $content = trim((string) $this->request->getPost('content'));
        $displayStatus = strtoupper(trim((string) $this->request->getPost('display_status')));

        if (!array_key_exists($type, $this->termTypeOptions())) {
            return redirect()->back()->withInput()->with('error', '약관 타입을 선택해주세요.');
        }

        if ($version === '') {
            return redirect()->back()->withInput()->with('error', '약관 버전을 입력해주세요.');
        }

        if ($title === '') {
            return redirect()->back()->withInput()->with('error', '약관 제목을 입력해주세요.');
        }

        if ($content === '') {
            return redirect()->back()->withInput()->with('error', '약관 내용을 입력해주세요.');
        }

        if (!in_array($displayStatus, ['Y', 'N'], true)) {
            $displayStatus = 'Y';
        }

        $duplicateBuilder = $this->db->table('my_fc_terms')
            ->where('term_type', $type)
            ->where('version', $version)
            ->where('deleted_at', null);

        if ($existingTerm !== null) {
            $duplicateBuilder->where('term_id !=', (int) $existingTerm['term_id']);
        }

        if ($duplicateBuilder->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', '같은 타입과 버전의 약관이 이미 등록되어 있습니다.');
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'term_type' => $type,
            'version' => $version,
            'title' => $title,
            'content' => $content,
            'display_status' => $displayStatus,
            'updated_at' => $now,
        ];

        if ($existingTerm === null) {
            $data['created_by'] = (string) (session()->get('admin_username') ?? session()->get('admin_id') ?? 'admin');
            $data['created_at'] = $now;
        }

        return $data;
    }

    private function termTypeOptions(): array
    {
        return [
            'TERMS' => '이용약관',
            'PRIVACY' => '개인정보처리방침',
            'LEGAL' => '법적책임',
            'MARKETING' => '마케팅 수신 동의',
            'LOCATION' => '위치기반서비스 약관',
            'FC' => 'FC 회원 약관',
            'OTHER' => '기타',
        ];
    }

    private function termTypeLabel(string $type): string
    {
        return $this->termTypeOptions()[$type] ?? $type;
    }

    private function termStatusLabel(string $status): string
    {
        return $status === 'Y' ? '노출' : '중지';
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function deletePublicFile(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '') {
            return;
        }

        $fullPath = FCPATH . ltrim($path, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function placeholder(string $title, string $breadcrumb, array $missing)
    {
        return $this->page([
            'title' => $title,
            'breadcrumb' => $breadcrumb,
            'readyAlert' => true,
            'missing' => $missing,
            'headers' => ['항목', '상태'],
            'rows' => array_map(static fn ($message) => [esc($message), '준비중'], $missing),
        ]);
    }

    private function page(array $data)
    {
        return view('admin/common/page', $data);
    }

    private function maskName(string $name): string
    {
        if ($name === '') {
            return '-';
        }

        return mb_substr($name, 0, 1) . str_repeat('*', max(1, mb_strlen($name) - 1));
    }

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email !== '' ? $email : '-';
        }

        [$id, $domain] = explode('@', $email, 2);
        return mb_substr($id, 0, max(1, (int) floor(mb_strlen($id) / 2))) . '***@' . $domain;
    }

    private function maskPhone(string $phone): string
    {
        return preg_replace('/(\d{3})-?(\d{3,4})-?(\d{4})/', '$1-$2-****', $phone) ?: ($phone !== '' ? $phone : '-');
    }

    private function counselStatus(string $status): string
    {
        return [
            'REQUEST' => '상담 대기',
            'PROGRESS' => '상담 진행',
            'COMPLETE' => '상담 완료',
            'CANCEL' => '상담 거부',
        ][$status] ?? '상담 대기';
    }

    private function reviewedStatus(string $status): string
    {
        return [
            'WAIT' => '승인 대기',
            'APPROVE' => '승인 완료',
            'REJECT' => '승인 거부',
        ][$status] ?? '승인 대기';
    }

    private function deliberationFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('status')));
        if (!in_array($status, ['ALL', 'WAIT', 'APPROVE', 'REJECT'], true)) {
            $status = 'ALL';
        }

        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = '';
        }

        return [
            'status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function applyDeliberationFilters($builder, array $filters): void
    {
        if (($filters['status'] ?? 'ALL') !== 'ALL') {
            $builder->where('r.status', $filters['status']);
        }

        if (($filters['q'] ?? '') !== '') {
            $q = $filters['q'];
            $builder->groupStart()
                ->like('m.name', $q)
                ->orLike('m.email', $q)
                ->orLike('r.deliberation_no', $q)
                ->groupEnd();
        }

        if (($filters['start_date'] ?? '') !== '') {
            $builder->where('r.created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if (($filters['end_date'] ?? '') !== '') {
            $builder->where('r.created_at <=', $filters['end_date'] . ' 23:59:59');
        }
    }

    private function deliberationTabs(array $filters): array
    {
        $tabs = [
            'ALL' => '전체',
            'WAIT' => '승인 대기',
            'APPROVE' => '승인 완료',
            'REJECT' => '승인 거부',
        ];

        $items = [];
        foreach ($tabs as $status => $label) {
            $query = array_filter([
                'status' => $status,
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/contents/deliberations');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
                'active' => ($filters['status'] ?? 'ALL') === $status,
            ];
        }

        return $items;
    }

    private function reviewsExportUrl(array $filters): string
    {
        $query = array_filter([
            'display_status' => $filters['display_status'],
            'q' => $filters['q'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
        ], static fn ($value) => (string) $value !== '');

        $url = base_url('admin/contents/reviews/export');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private function reviewDisplayStatusLabel(string $status): string
    {
        return $status === 'N' ? '비노출' : '노출중';
    }

    private function reviewFilters(): array
    {
        $status = strtoupper(trim((string) $this->request->getGet('display_status')));
        if (!in_array($status, ['Y', 'N'], true)) {
            $status = '';
        }

        return [
            'display_status' => $status,
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => trim((string) $this->request->getGet('start_date')),
            'end_date' => trim((string) $this->request->getGet('end_date')),
        ];
    }

    private function applyReviewFilters($builder, array $filters): void
    {
        if (($filters['display_status'] ?? '') !== '') {
            $builder->where('r.display_status', $filters['display_status']);
        }

        if (($filters['start_date'] ?? '') !== '') {
            $builder->where('r.created_at >=', $filters['start_date'] . ' 00:00:00');
        }

        if (($filters['end_date'] ?? '') !== '') {
            $builder->where('r.created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if (($filters['q'] ?? '') !== '') {
            $q = $filters['q'];
            $builder->groupStart()
                ->like('r.title', $q)
                ->orLike('r.body', $q)
                ->orLike('u.name', $q)
                ->orLike('u.email', $q)
                ->orLike('f.name', $q)
                ->orLike('f.email', $q)
                ->groupEnd();
        }
    }

    private function reviewTabs(array $filters): array
    {
        $tabs = [
            '' => '전체',
            'Y' => '노출중',
            'N' => '비노출',
        ];

        $items = [];
        foreach ($tabs as $status => $label) {
            $query = array_filter([
                'display_status' => $status,
                'q' => $filters['q'],
                'start_date' => $filters['start_date'],
                'end_date' => $filters['end_date'],
            ], static fn ($value) => (string) $value !== '');

            $url = base_url('admin/contents/reviews');
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }

            $items[] = [
                'label' => $label,
                'url' => $url,
                'active' => ($filters['display_status'] ?? '') === $status,
            ];
        }

        return $items;
    }

    private function deliberationsExportUrl(array $filters): string
    {
        $query = array_filter([
            'status' => $filters['status'],
            'q' => $filters['q'],
            'start_date' => $filters['start_date'],
            'end_date' => $filters['end_date'],
        ], static fn ($value) => (string) $value !== '');

        $url = base_url('admin/contents/deliberations/export');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private function deliberationExportHeaders(): array
    {
        return [
            '신청ID',
            'FC회원명',
            'FC회원이메일',
            'FC회원UID',
            '심의필번호',
            '승인상태',
            '승인기간시작',
            '승인기간종료',
            '심의의견',
            '회신문파일',
            '거부사유',
            '승인관리자',
            '승인일시',
            '신청일시',
            '수정일시',
        ];
    }

    private function deliberationExportRow(array $row): array
    {
        return [
            $row['id'] ?? '',
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['member_uid'] ?? '',
            $row['deliberation_no'] ?? '',
            $this->reviewedStatus((string) ($row['status'] ?? 'WAIT')),
            $row['approval_start'] ?? '',
            $row['approval_end'] ?? '',
            $row['deliberation_opinion'] ?? '',
            $row['deliberation_file'] ?? '',
            $row['reject_reason'] ?? '',
            $row['approve_admin_uid'] ?? '',
            $row['approve_at'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
        ];
    }

    private function reviewExportHeaders(): array
    {
        return [
            '후기ID',
            '제목',
            '별점',
            '작성자명',
            '작성자이메일',
            '상담FC명',
            '상담FC이메일',
            '상담UID',
            '노출상태',
            '조회수',
            '내용',
            '작성일시',
            '수정일시',
        ];
    }

    private function reviewExportRow(array $row): array
    {
        return [
            $row['review_id'] ?? '',
            $row['title'] ?? '',
            $row['rating'] ?? '',
            $row['user_name'] ?? '',
            $row['user_email'] ?? '',
            $row['fc_name'] ?? '',
            $row['fc_email'] ?? '',
            $row['counsel_uid'] ?? '',
            $this->reviewDisplayStatusLabel((string) ($row['display_status'] ?? 'Y')),
            $row['view_count'] ?? 0,
            $row['body'] ?? '',
            $row['created_at'] ?? '',
            $row['updated_at'] ?? '',
        ];
    }

    private function csvLine(array $columns): string
    {
        return implode(',', array_map(static function ($value) {
            return '"' . str_replace('"', '""', (string) $value) . '"';
        }, $columns)) . "\n";
    }

    private function formatBytes($bytes): string
    {
        $bytes = (int) $bytes;

        if ($bytes <= 0) {
            return '-';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return number_format($bytes / 1024, 1) . ' KB';
    }

    private function reviewHistoryBuilder()
    {
        return $this->db->table('my_fc_reviewed_history h')
            ->select('h.*');
    }

    private function deliberationListBuilder()
    {
        return $this->db->table('my_fc_reviewed r')
            ->select('r.id, r.member_uid, r.deliberation_no, r.status, r.created_at, m.name, m.email')
            ->join('my_fc_member m', 'm.member_uid = r.member_uid', 'left');
    }

    private function insertReviewedHistory(
        int $reviewId,
        string $memberUid,
        ?string $oldStatus,
        string $newStatus,
        ?string $rejectReason,
        string $changedBy,
        string $changedAt
    ): void {
        $this->db->table('my_fc_reviewed_history')->insert([
            'review_id' => $reviewId,
            'member_uid' => $memberUid,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reject_reason' => $rejectReason,
            'changed_by' => $changedBy,
            'changed_at' => $changedAt,
        ]);
    }

    private function adListFilters(): array
    {
        return [
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => trim((string) $this->request->getGet('start_date')),
            'end_date' => trim((string) $this->request->getGet('end_date')),
            'status' => trim((string) $this->request->getGet('status')),
            'member_id' => max(0, (int) $this->request->getGet('member_id')),
        ];
    }

    private function adListBuilder(string $kind)
    {
        $typeMap = [
            'normal' => ['region_fc', 'product_fc', 'review', 'language_fc'],
            'top' => ['banner'],
            'bottom' => ['banner'],
        ];
        $builder = $this->db->table('ad_master a')
            ->select('a.*, m.member_id, m.member_uid, m.name, m.email')
            ->join('my_fc_member m', $this->adMemberJoinCondition(), 'left', false)
            ->whereIn('a.ad_type', $typeMap[$kind] ?? $typeMap['normal']);
        if ($kind === 'top' || $kind === 'bottom') {
            $builder->where('a.banner_position', $kind);
        }
        return $builder;
    }

    private function applyAdListFilters($builder, array $filters): void
    {
        if ($filters['q'] !== '') {
            $builder->groupStart()->like('m.name', $filters['q'])->orLike('m.email', $filters['q'])->groupEnd();
        }
        if ($filters['start_date'] !== '') $builder->where('a.created_at >=', $filters['start_date'] . ' 00:00:00');
        if ($filters['end_date'] !== '') $builder->where('a.created_at <=', $filters['end_date'] . ' 23:59:59');

        $today = date('Y-m-d');
        switch ($filters['status']) {
            case 'waiting':
                $builder->groupStart()->whereIn('a.status', ['apply', 'pending'])->orGroupStart()->where('a.status', 'approved')->where('a.start_date >', $today)->groupEnd()->groupEnd();
                break;
            case 'active':
                $builder->where('a.status', 'approved')->where('a.start_date <=', $today)->where('a.end_date >=', $today);
                break;
            case 'ended':
                $builder->groupStart()->where('a.status', 'end')->orGroupStart()->where('a.status', 'approved')->where('a.end_date <', $today)->groupEnd()->groupEnd();
                break;
            case 'stopped':
                $builder->where('a.status', 'rejected');
                break;
        }

        if ((int) $filters['member_id'] > 0) {
            $member = $this->db->table('my_fc_member')->select('member_id, member_uid')->where('member_id', (int) $filters['member_id'])->get()->getRowArray();
            $builder->groupStart()->where('a.fc_member_id', (string) $filters['member_id']);
            if ($member) $builder->orWhere('a.fc_member_id', (string) ($member['member_uid'] ?? ''));
            $builder->groupEnd();
        }
    }

    private function adListTabs(string $kind, array $filters): array
    {
        $tabs = ['' => '전체', 'waiting' => '진행대기', 'active' => '진행중', 'ended' => '진행종료', 'stopped' => '진행중단'];
        $base = array_filter($filters, static fn($value, $key) => $key !== 'status' && (string) $value !== '', ARRAY_FILTER_USE_BOTH);
        return array_map(static function ($label, $status) use ($kind, $filters, $base) {
            $query = $base;
            if ($status !== '') $query['status'] = $status;
            return [
                'label' => $label,
                'url' => base_url('admin/ads/' . $kind) . ($query ? '?' . http_build_query($query) : ''),
                'active' => (string) $filters['status'] === (string) $status,
            ];
        }, $tabs, array_keys($tabs));
    }

    private function adDecisionControls(string $kind, array $row): string
    {
        $id = (int) ($row['id'] ?? 0);
        $status = (string) ($row['status'] ?? '');
        $actionUrl = base_url('admin/ads/' . $kind . '/' . $id . '/decision');
        $defaultEndDate = date('Y-m-d', strtotime('+1 month'));

        if (in_array($status, ['apply', 'pending', 'rejected'], true)) {
            return '
                <div class="ad-manage-box">
                    <form action="' . $actionUrl . '" method="post" class="ad-approve-form">
                        ' . csrf_field() . '
                        <input type="hidden" name="decision" value="approved">
                        <label>광고 종료일</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="' . esc($defaultEndDate) . '" min="' . esc(date('Y-m-d')) . '" required>
                        <button type="submit" class="btn btn-primary btn-sm">승인하기</button>
                    </form>
                    <form action="' . $actionUrl . '" method="post" class="ad-reject-form">
                        ' . csrf_field() . '
                        <input type="hidden" name="decision" value="rejected">
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'광고 신청을 거절하시겠습니까?\')">신청 거절</button>
                    </form>
                </div>';
        }

        if ($status === 'approved') {
            return '
                <div class="ad-manage-box is-running"><span class="ad-manage-caption">현재 광고 진행중</span><form action="' . $actionUrl . '" method="post">
                    ' . csrf_field() . '
                    <input type="hidden" name="decision" value="end">
                    <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm(\'광고를 종료하시겠습니까?\')">광고 종료</button>
                </form></div>';
        }

        return '-';
    }

    private function adStatusLabelForRow(array $row): string
    {
        if (!$this->isAdActive($row) && (string) ($row['status'] ?? '') === 'approved') {
            return '진행종료';
        }

        return $this->adStatusLabel((string) ($row['status'] ?? ''));
    }

    private function isAdActive(array $row): bool
    {
        if ((string) ($row['status'] ?? '') !== 'approved') {
            return false;
        }

        $today = date('Y-m-d');
        $startDate = (string) ($row['start_date'] ?? '');
        $endDate = (string) ($row['end_date'] ?? '');

        if ($startDate !== '' && $today < $startDate) {
            return false;
        }

        if ($endDate !== '' && $today > $endDate) {
            return false;
        }

        return true;
    }

    private function adBelongsToKind(array $ad, string $kind): bool
    {
        $type = (string) ($ad['ad_type'] ?? '');
        if ($kind === 'normal') {
            return in_array($type, ['region_fc', 'product_fc', 'review', 'language_fc'], true);
        }

        return $type === 'banner' && (string) ($ad['banner_position'] ?? '') === $kind;
    }

    private function adMemberJoinCondition(): string
    {
        return '(m.member_uid COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci OR CAST(m.member_id AS CHAR) COLLATE utf8mb4_unicode_ci = a.fc_member_id COLLATE utf8mb4_unicode_ci)';
    }

    private function currentAdminName(): string
    {
        return (string) (
            session()->get('admin_username')
            ?? session()->get('admin_id')
            ?? session()->get('username')
            ?? 'admin'
        );
    }

    private function normalizeAdKind(string $kind): string
    {
        return in_array($kind, ['normal', 'top', 'bottom'], true) ? $kind : 'normal';
    }

    private function adKindTitle(string $kind): string
    {
        return [
            'normal' => '일반 광고',
            'top' => '상단 배너 광고',
            'bottom' => '하단 배너 광고',
        ][$this->normalizeAdKind($kind)];
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
            'banner' => '배너 광고',
        ][$type] ?? $type;
    }

    private function adStatusLabel(string $status): string
    {
        return [
            'apply' => '진행대기',
            'pending' => '진행대기',
            'approved' => '진행중',
            'rejected' => '진행중단',
            'end' => '진행종료',
        ][$status] ?? $status;
    }

    private function insertAdStatusHistory(int $adId, ?string $oldStatus, string $newStatus, string $memo = ''): void
    {
        $this->db->table('ad_status_history')->insert([
            'ad_id' => $adId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $this->currentAdminName(),
            'memo' => $memo !== '' ? $memo : null,
            'changed_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
