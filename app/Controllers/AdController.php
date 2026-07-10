<?php

namespace App\Controllers;

class AdController extends BaseController
{
    public function click($id)
    {
        $db = \Config\Database::connect();

        $ad = $db->table('ad_master')
            ->where('id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$ad) {
            return redirect()->to('/');
        }

        $today = date('Y-m-d');
        if (($ad['status'] ?? '') !== 'approved'
            || (!empty($ad['start_date']) && $today < $ad['start_date'])
            || (!empty($ad['end_date']) && $today > $ad['end_date'])
        ) {
            return redirect()->to('/');
        }

        $targetUrl = trim((string) ($ad['banner_link_url'] ?? ''));
        if ($targetUrl === '' && ($ad['ad_type'] ?? '') !== 'banner') {
            $member = $db->table('my_fc_member')
                ->select('member_uid')
                ->where('member_uid', (string) ($ad['fc_member_id'] ?? ''))
                ->orWhere('member_id', (string) ($ad['fc_member_id'] ?? ''))
                ->get()
                ->getRowArray();

            if ($member && !empty($member['member_uid'])) {
                $targetUrl = base_url('fc/view/?uid=' . $member['member_uid']);
            }
        }
        $referer = $this->request->getHeaderLine('Referer');

        $db->transStart();

        $db->table('ad_click_log')->insert([
            'ad_id' => (int) $ad['id'],
            'fc_member_id' => $ad['fc_member_id'] ?? null,
            'click_date' => date('Y-m-d'),
            'clicked_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => substr($this->request->getUserAgent()->getAgentString(), 0, 255),
            'referer_url' => $referer !== '' ? substr($referer, 0, 500) : null,
            'target_url' => $targetUrl !== '' ? substr($targetUrl, 0, 500) : null,
        ]);

        $db->table('ad_master')
            ->where('id', (int) $ad['id'])
            ->set('click_count', 'click_count + 1', false)
            ->update([
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $db->transComplete();

        if ($targetUrl !== '' && filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to($targetUrl);
        }

        return redirect()->to('/');
    }
}
