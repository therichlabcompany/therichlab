<?php

namespace App\Controllers;

class FcController extends BaseController
{
    public function index(): string
    {
        //return pageView('welcome_message');
        $header_class="search-page results";
        $popup_page = [
            "popup_insurance.php"
            ,"popup_region.php"
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/list', $data);
    }

    public function view(): string
    {
        //return pageView('welcome_message');
        $header_class="detail-page";

        $popup_page = [
        ];

        $modal_page = [
            "hugi_modal.php"
            ,"story_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/view', $data);
    }

    public function counsel(): string
    {
        //return pageView('welcome_message');
        $header_class="detail-page";

        $popup_page = [
        ];

        $modal_page = [
            "counsel_last_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/counsel', $data);
    }

    public function counselLast(): string
    {
        //return pageView('welcome_message');
        $header_class="flow-result";

        $popup_page = [
        ];

        $modal_page = [
            "counsel_last_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('fc/counselLast', $data);
    }

    



}
