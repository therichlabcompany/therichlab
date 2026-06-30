<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberSecurityModel extends Model
{
    protected $table            = 'my_fc_member_security';
    protected $primaryKey       = 'security_id';

    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'member_uid',
        'original_name',
        'saved_name',
        'file_path',
        'file_ext',
        'file_size',
        'sort_order',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}