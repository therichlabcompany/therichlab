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

    public function getAdListByMemberPaging($memberId, $page, $perPage, $legacyMemberId = null, array $filters = [])
    {
        $builder = $this->groupStart()
            ->where('fc_member_id', (string) $memberId);

        if ($legacyMemberId !== null && $legacyMemberId !== '') {
            $builder->orWhere('fc_member_id', (string) $legacyMemberId);
        }

        $builder->groupEnd();

        if (($filters['ad_type'] ?? '') !== '') {
            $builder->where('ad_type', $filters['ad_type']);
        }

        if (($filters['status'] ?? '') !== '') {
            $today = date('Y-m-d');
            switch ($filters['status']) {
                case 'waiting':
                    $builder->groupStart()
                        ->whereIn('status', ['apply', 'pending'])
                        ->orGroupStart()->where('status', 'approved')->where('start_date >', $today)->groupEnd()
                        ->groupEnd();
                    break;
                case 'active':
                    $builder->where('status', 'approved')->where('start_date <=', $today)->where('end_date >=', $today);
                    break;
                case 'ended':
                    $builder->groupStart()
                        ->where('status', 'end')
                        ->orGroupStart()->where('status', 'approved')->where('end_date <', $today)->groupEnd()
                        ->groupEnd();
                    break;
                case 'stopped':
                    $builder->where('status', 'rejected');
                    break;
            }
        }

        if (($filters['ad_detail'] ?? '') !== '') {
            [$detailType, $detailValue] = array_pad(explode(':', $filters['ad_detail'], 2), 2, '');
            $detailColumn = [
                'region' => 'region_code',
                'product' => 'insurance_type',
                'review' => 'review_id',
                'language' => 'language_code',
                'banner' => 'banner_position',
            ][$detailType] ?? null;
            if ($detailColumn !== null && $detailValue !== '') {
                $builder->where($detailColumn, $detailType === 'review' ? (int) $detailValue : $detailValue);
            }
        }

        $total = $builder->countAllResults(false);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->findAll($perPage, ($page - 1) * $perPage);

        return [
            'list' => $list,
            'total' => $total
        ];
    }

    public function getMemberReviewAdIds($memberId, $legacyMemberId = null): array
    {
        $builder = $this->select('review_id')
            ->where('ad_type', 'review')
            ->groupStart()
            ->where('fc_member_id', (string) $memberId);

        if ($legacyMemberId !== null && $legacyMemberId !== '') {
            $builder->orWhere('fc_member_id', (string) $legacyMemberId);
        }

        return array_values(array_filter(array_map(
            static fn ($row) => (int) ($row['review_id'] ?? 0),
            $builder->groupEnd()->findAll()
        )));
    }
}
