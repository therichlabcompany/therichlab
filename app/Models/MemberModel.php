<?php
    namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table = 'my_fc_member';
    protected $primaryKey = 'member_id';

    protected $allowedFields = [
        'email',
        'password',
        'phone',
        'name',
        'gender',           // ⭐ 추가
        'member_type',
        'member_uid',
        'fc_step',
        'last_login_at',
        'deleted_at',
        'status',
        'agree_marketing', // ⭐ 추가
    ];
}

