<?php
namespace App\Models;

use CodeIgniter\Model;

class AdMasterModel extends Model
{
    protected $table = 'ad_master';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'fc_member_id',
        'ad_type',
        'status',
        'amount',
        'start_date',
        'end_date',
        'approved_at',
        'approved_by',
        'click_count',

        // region
        'region_code',

        // banner
        'banner_image_url',
        'banner_link_url',
        'banner_need_design',
        'banner_position',

        // product
        'insurance_type',

        // review
        'review_id',

        // language
        'language_code',
    ];

    public function getAdListByMemberPaging($memberId, $page, $perPage)
    {
        $builder = $this->where('fc_member_id', $memberId);

        $total = $builder->countAllResults(false);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->findAll($perPage, ($page - 1) * $perPage);

        return [
            'list' => $list,
            'total' => $total
        ];
    }
}
