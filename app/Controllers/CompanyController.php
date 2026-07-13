<?php

namespace App\Controllers;
use Config\Database;

class CompanyController extends BaseController
{
    public function __construct()
    {
        // default 그룹으로 DB 연결
        $this->db = Database::connect('default');

        // 공용 함수(db_conn())를 사용하고 싶다면 아래처럼 사용 가능
        // $this->db = db_conn();
    }

    public function terms(): string
    {
        return $this->renderPolicy('TERMS', '이용약관');
    }


    public function privacy(): string
    {
        return $this->renderPolicy('PRIVACY', '개인정보처리방침');
    }

    public function legal(): string
    {
        return $this->renderPolicy('LEGAL', '법적책임');
    }

    private function renderPolicy(string $type, string $defaultTitle): string
    {
        helper(['region', 'insurance']);

        $term = $this->db->table('my_fc_terms')
            ->where('term_type', $type)
            ->where('display_status', 'Y')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->orderBy('term_id', 'DESC')
            ->get()
            ->getRowArray();

        return $this->renderView('company/document', [
            'header_class' => 'form-page terms-page',
            'popup_page' => [],
            'modal_page' => [],
            'title' => $term['title'] ?? $defaultTitle,
            'content' => $term['content'] ?? '',
            'version' => $term['version'] ?? '',
        ]);
    }

}
