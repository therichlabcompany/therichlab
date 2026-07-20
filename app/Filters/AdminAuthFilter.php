<?php
namespace App\Filters;

use Config\Database;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $adminId = (int) session()->get('admin_id');
        if ($adminId <= 0) {
            session()->remove(['admin_logged_in', 'admin_id', 'admin_username', 'admin_name', 'admin_role']);
            return redirect()->to('/admin/login');
        }

        $db = Database::connect();
        $admin = $db->table('admin_users')
            ->select('id, status')
            ->where('id', $adminId)
            ->get()
            ->getRowArray();

        if (!$admin || strtoupper(trim((string) ($admin['status'] ?? ''))) !== 'Y') {
            session()->remove(['admin_logged_in', 'admin_id', 'admin_username', 'admin_name', 'admin_role']);
            return redirect()->to('/admin/login')->with('error', '중지된 관리자 계정입니다.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing
    }
}
