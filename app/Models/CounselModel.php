<?php

namespace App\Models;

use CodeIgniter\Model;

class CounselModel extends Model
{
    protected $table = 'my_fc_counsel';

    protected $primaryKey = 'counsel_id';

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $useTimestamps = true;

    protected $createdField='created_at';
    protected $updatedField='updated_at';
    protected $deletedField='deleted_at';

    protected $allowedFields=[

        'counsel_uid',

        'fc_member_uid',

        'member_uid',

        'name',

        'email',

        'phone',

        'reserve_datetime',

        'content',

        'status'

    ];
}