<?php

namespace App\Models;

use CodeIgniter\Model;

class CounselFileModel extends Model
{
    protected $table='my_fc_counsel_file';

    protected $primaryKey='file_id';

    protected $returnType='array';

    protected $allowedFields=[

        'counsel_uid',

        'file_type',

        'security_id',

        'original_name',

        'saved_name',

        'file_path',

        'file_ext',

        'file_size'

    ];
}