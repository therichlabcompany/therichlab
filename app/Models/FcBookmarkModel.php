<?php

namespace App\Models;

use CodeIgniter\Model;

class FcBookmarkModel extends Model
{
    protected $table      = 'fc_bookmarks';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'member_uid',
        'fc_member_uid'
    ];

    public $timestamps = false;
}