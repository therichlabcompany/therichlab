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
        'phone_verified',
        'name',
        'birth',
        'gender',
        'nickname',
        'profile_image',
        'member_type',
        'member_uid',
        'fc_step',
        'fc_review_status',
        'login_fail_count',
        'password_reset_at',
        'last_login_at',
        'agree_age',
        'agree_terms',
        'agree_privacy',
        'agree_marketing',
        'join_ip',
        'admin_memo',
        'created_at',
        'updated_at',
        'deleted_at',
        'status',
        'app_token',
        'fcm_token',
        'fcm_token_updated_at',
        'app_platform',
        'app_token_expire_at',
        'app_token_updated_at',
    ];
}
