<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * 레이아웃을 포함한 뷰를 렌더링하는 메서드
     * 
     * @param string $view 뷰 파일명
     * @param array $data 뷰에 전달할 데이터
     * @param bool $useLayout 레이아웃 사용 여부
     * @return string
     */
    protected function renderView(string $view, array $data = [], bool $useLayout = true): string
    {
        $clientIp = $this->request->getIPAddress();
        $session = session();

        $memberId  = $session->get('member_id');
        $memberUid = $session->get('member_uid');

        // =========================
        // 🔥 FC 심의 데이터 조회
        // =========================
        $db = \Config\Database::connect();

        $review = null;

        if ($memberUid) {
            $review = $db->table('my_fc_reviewed')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();
        }


        $memberProfile = null;

        if ($memberUid) {
            $memberProfile = $db->table('my_fc_profile')
                ->select('profile_image')
                ->where('member_uid', $memberUid)
                
                ->get()
                ->getRowArray();
        }

        $popupList = [];

        if ($db->tableExists('my_fc_popup')) {
            $now = date('Y-m-d H:i:s');
            $popupList = $db->table('my_fc_popup')
                ->select('popup_id, title, image_path, link_url, link_target, display_status, start_at, end_at, sort_order')
                ->where('display_status', 'Y')
                ->groupStart()
                ->where('start_at IS NULL', null, false)
                ->orWhere('start_at <=', $now)
                ->groupEnd()
                ->groupStart()
                ->where('end_at IS NULL', null, false)
                ->orWhere('end_at >=', $now)
                ->groupEnd()
                ->where('deleted_at', null)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('popup_id', 'DESC')
                ->get()
                ->getResultArray();
        }

        // print_r($memberProfile);
        // exit;

        // =========================
        // 앱 / 웹 구분
        // =========================
         $userAgent = $this->request
        ->getHeaderLine('User-Agent');

        //echo $userAgent;


        $isApp = false;


        // Flutter WebView에서 지정한 User-Agent 체크
        if (
            strpos($userAgent, 'myfc_ios') !== false
            || strpos($userAgent, 'myfc_android') !== false
        ) {
            $isApp = true;
        }

        $appToken = null;

        //$isApp = true;          // 주석처리해야함 테스트용
        $appToken = null;
        $appPlatform = null;


        if ($isApp && $memberUid) {


            // =========================
            // 앱 플랫폼 체크
            // =========================
            if (
                stripos($userAgent, 'Android') !== false
            ) {

                $appPlatform = 'ANDROID';


            } elseif (
                stripos($userAgent, 'iPhone') !== false ||
                stripos($userAgent, 'iPad') !== false
            ) {

                $appPlatform = 'IOS';

            }



            // 회원 앱 토큰 조회
            $memberApp = $db->table('my_fc_member')
                ->select([
                    'app_token',
                    'app_platform',
                    'app_token_expire_at'
                ])
                ->where(
                    'member_uid',
                    $memberUid
                )
                ->get()
                ->getRowArray();



            $now = date('Y-m-d H:i:s');



            // 기존 토큰 + 만료 전
            if (
                !empty($memberApp['app_token']) &&
                $memberApp['app_token_expire_at'] > $now
            ) {


                $appToken =
                    $memberApp['app_token'];



                // 플랫폼 변경 체크
                $updateData = [

                    'app_platform' =>
                        $appPlatform,

                ];


                $db->table('my_fc_member')
                    ->where(
                        'member_uid',
                        $memberUid
                    )
                    ->update($updateData);



            } else {


                // 신규 토큰 생성
                $appToken =
                    $this->createAppToken();



                // 30일 만료
                $expireAt =
                    date(
                        'Y-m-d H:i:s',
                        strtotime('+30 days')
                    );



                $db->table('my_fc_member')
                    ->where(
                        'member_uid',
                        $memberUid
                    )
                    ->update([

                        'app_token' =>
                            $appToken,


                        'app_platform' =>
                            $appPlatform,


                        'app_token_expire_at' =>
                            $expireAt,


                        'app_token_updated_at' =>
                            date('Y-m-d H:i:s')

                    ]);

            }

        }

        // =========================
        // layout data (header)
        // =========================
        
        
        $layoutData = [

            "header_class" =>
                $data["header_class"] ?? '',


            "popup_page" =>
                $data["popup_page"] ?? [],


            "modal_page" =>
                $data["modal_page"] ?? [],


            // 회원 프로필
            "memberProfile" =>
                $memberProfile,

            "popupList" =>
                $popupList,


            // 앱 여부
            "isApp" =>
                $isApp,


            // 앱 토큰
            "appToken" =>
                $appToken,


            "userAgent" =>
                $userAgent

        ];

        // =========================
        // 🔥 footer data 추가
        // =========================
        $footerData = $layoutData;
        $footerData['review'] = $review; // 핵심

        return view('layout/header', $layoutData)
            . view($view, $data)
            . view('layout/footer', $footerData);
    }

    /**
     * 레이아웃을 포함한 뷰를 렌더링하는 메서드
     * 
     * @param string $view 뷰 파일명
     * @param array $data 뷰에 전달할 데이터
     * @param bool $useLayout 레이아웃 사용 여부
     * @return string
     */
    protected function renderAdminView(string $view, array $data = [], bool $useLayout = true): string
    {
        // 클라이언트 IP (필요 시 로그/권한 체크에 활용)
        $clientIp = $this->request->getIPAddress();

        // 전달받은 데이터를 그대로 사용
        // (title 등 값을 유지하기 위해 $data를 덮어쓰지 않음)
        $viewData = $data;

        // header/footer 에 전달할 데이터
        // 기본적으로 동일한 데이터를 전달
        $layoutData = $data;

        // 레이아웃을 사용하지 않는 경우
        if (! $useLayout) {
            return view($view, $viewData);
        }

        // 관리자 레이아웃 렌더링
        return view('admin/layout/header', $layoutData)
            . view($view, $viewData)
            . view('admin/layout/footer', $layoutData);
    }

    protected function forbiddenWordViolation(string $text, array $scopes = ['ALL']): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $scopes = array_values(array_unique(array_filter(array_map('strtoupper', $scopes), static fn ($scope) => $scope !== '')));
        if (empty($scopes)) {
            $scopes = ['ALL'];
        }

        if (!in_array('ALL', $scopes, true)) {
            $scopes[] = 'ALL';
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('my_fc_forbidden_word')) {
            return null;
        }

        $words = $db->table('my_fc_forbidden_word')
            ->select('keyword, match_type')
            ->where('deleted_at', null)
            ->where('display_status', 'Y')
            ->whereIn('apply_scope', $scopes)
            ->orderBy('word_id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($words as $word) {
            $keyword = trim((string) ($word['keyword'] ?? ''));
            if ($keyword === '') {
                continue;
            }

            $matchType = strtoupper(trim((string) ($word['match_type'] ?? 'PARTIAL')));
            if ($this->matchesForbiddenWord($text, $keyword, $matchType)) {
                return $keyword;
            }
        }

        return null;
    }

    protected function forbiddenWordErrorMessage(string $context, string $keyword = ''): string
    {
        $message = $context . '에 금칙어가 포함되어 있습니다.';
        if ($keyword !== '') {
            $message .= ' (' . $keyword . ')';
        }

        return $message;
    }

    private function matchesForbiddenWord(string $text, string $keyword, string $matchType): bool
    {
        if ($matchType === 'EXACT') {
            return mb_strtolower($text) === mb_strtolower($keyword);
        }

        if ($matchType === 'REGEX') {
            $pattern = '~' . str_replace('~', '\\~', $keyword) . '~iu';
            return @preg_match($pattern, $text) === 1;
        }

        return mb_stripos($text, $keyword) !== false;
    }

    private function createAppToken(): string
    {
        return bin2hex(
            random_bytes(32)
        );
    }
}
