<?php

namespace App\Controllers;

class MypageController extends BaseController
{
    public function index(): string
    {
        //return pageView('welcome_message');
        $header_class="search-page results";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/index', $data);
    }

    public function info(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/info', $data);
    }

    public function withdrawalLast(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page flow-result";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/withdrawalLast', $data);
    }

    public function certificate(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page form-page securities-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/certificate', $data);
    }

    public function favoriteFc(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page favorite-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/favoriteFc', $data);
    }

    public function counselList(): string
    {
        //return pageView('welcome_message');
        $header_class="consult-status-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/counselList', $data);
    }

    public function reviewWrite(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWrite', $data);
    }

    public function reviewWriteLast(): string
    {
        //return pageView('welcome_message');
        $header_class="flow-result";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWriteLast', $data);
    }

    public function reviewList(): string
    {
        //return pageView('welcome_message');
        $header_class="consult-status-page detail-page";
        $popup_page = [
        ];

        $modal_page = [
            "hugi_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewList', $data);
    }

    

    

}
