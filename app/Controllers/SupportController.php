<?php

namespace App\Controllers;

class SupportController extends BaseController
{
    /** iOS 앱 등록용 독립 고객지원 페이지 */
    public function customerSupport(): string
    {
        return view('support/customer_support');
    }
}
