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
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        // =========================
        // sorting
        // =========================
        $sortField = $request->getGet('sort') ?? 'member_id';
        $sortOrder = strtoupper($request->getGet('order') ?? 'DESC');

        $allowedSort = [
            'member_id',
            'email',
            'name',
            'status',
            'fc_step',
            'fc_approval_status',
            'created_at'
        ];

        if (!in_array($sortField, $allowedSort)) {
            $sortField = 'member_id';
        }

        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }

        // =========================
        // search filters
        // =========================
        $startDate = $request->getGet('start_date');
        $endDate   = $request->getGet('end_date');

        $email = trim($request->getGet('email') ?? '');
        $name  = trim($request->getGet('name') ?? '');
        $phone = trim($request->getGet('phone') ?? '');
        $status   = trim($request->getGet('status') ?? '');
$approval = trim($request->getGet('approval') ?? '');

        // =========================
        // base builder
        // =========================
        $baseBuilder = $db->table('my_fc_member')
        ->where('deleted_at', null)
        ->where('member_type', 'FC');

        // =========================
        // date filter
        // =========================
        if (!empty($startDate)) {
            $baseBuilder->where('created_at >=', $startDate . ' 00:00:00');
        }

        if (!empty($endDate)) {
            $baseBuilder->where('created_at <=', $endDate . ' 23:59:59');
        }

        // =========================
        // keyword filters
        // =========================
        if (!empty($email)) {
            $baseBuilder->like('email', $email);
        }

        if (!empty($name)) {
            $baseBuilder->like('name', $name);
        }

        if (!empty($phone)) {
            $baseBuilder->like('phone', $phone);
        }

        if (!empty($status)) {
            $baseBuilder->where('status', $status);
        }

        if (!empty($approval)) {
            $baseBuilder->where('fc_approval_status', $approval);
        }

        // =========================
        // total count (clone 중요)
        // =========================
        $total = (clone $baseBuilder)->countAllResults();

        // =========================
        // list query
        // =========================
        $members = (clone $baseBuilder)
            ->orderBy($sortField, $sortOrder)
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
            'sort'    => $sortField,
            'order'   => $sortOrder,
        ]);
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

        return view('admin/fc_member/detail',[
            'm'             => $member,
            'profile'       => $profile,
            'activity'      => $activity,
            'activityItems' => $activityItems,
            'story'         => $story,
            'storyImages'   => $storyImages,
            'review'        => $review
        ]);
    }

    public function reviewApprove()
    {
        $db = \Config\Database::connect();

        $memberUid = $this->request->getPost('member_uid');

        $db->table('my_fc_reviewed')
            ->where('member_uid', $memberUid)
            ->update([
                'status' => 'APPROVE',
                'approve_at' => date('Y-m-d H:i:s'),
                'approve_admin_uid' => 'admin' // 필요시 세션으로 변경
            ]);

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


}
