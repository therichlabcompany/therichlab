<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Member extends BaseController
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
            'member_type',
            'status',
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

        // =========================
        // base builder
        // =========================
        $baseBuilder = $db->table('my_fc_member')
            ->where('deleted_at', null);

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
        return view('admin/member/index', [
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
        $db = \Config\Database::connect();

        $member = $db->table('my_fc_member')
            ->where('member_id', $id)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/member/detail', [
            'm' => $member
        ]);
    }


    // =========================
    // 상태 변경
    // =========================
    public function changeStatus()
    {
        $memberId = $this->request->getPost('member_id');
        $status = $this->request->getPost('status');

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }

    // =========================
    // soft delete
    // =========================
    public function delete()
    {
        $memberId = $this->request->getPost('member_id');

        $this->db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }
}
