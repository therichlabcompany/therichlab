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
        'role',
        'status',
        'last_login_at',
    ];
}