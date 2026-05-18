<?php

namespace App\Controllers;
use Config\Database;


class FcPageControoller extends BaseController
{
    protected $db;

    public function __construct()
    {
        // default 그룹으로 DB 연결
        $this->db = Database::connect('default');

        // 공용 함수(db_conn())를 사용하고 싶다면 아래처럼 사용 가능
        // $this->db = db_conn();
    }

    public function info(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page signup-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fcpage/info', $data);
    }

  
    

}
