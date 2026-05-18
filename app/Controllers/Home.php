<?php

namespace App\Controllers;
use Config\Database;

class Home extends BaseController
{
    public function __construct()
    {
        // default 그룹으로 DB 연결
        $this->db = Database::connect('default');

        // 공용 함수(db_conn())를 사용하고 싶다면 아래처럼 사용 가능
        // $this->db = db_conn();
    }

    public function index(): string
    {
        
        //return pageView('welcome_message');
        $header_class="main-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];
        return $this->renderView('main/index', $data);
    }
}
