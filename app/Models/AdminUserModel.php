<?php
namespace App\Models;

use CodeIgniter\Model;

class AdminUserModel extends Model
{
    protected $table = 'admin_users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'username',
        'password_hash',
        'name',
        'email',
        'phone',
        'role',
        'status',
        'last_login_at',
        'failed_login_count',
        'login_locked_until',
        'last_failed_login_at',
    ];
}
