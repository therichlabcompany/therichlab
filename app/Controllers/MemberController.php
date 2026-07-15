<?php

namespace App\Controllers;

class MemberController extends BaseController
{
    public function login(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/login', $data);
    }

    public function find(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page account-find-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/find', $data);
    }

    public function findResult(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/findResult', $data);
    }

    public function passEmail(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page password-reset-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passEmail', $data);
    }

    public function passReset(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page password-reset-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passReset', $data);
    }

    public function passResult(): string
    {
        //return pageView('welcome_message');
        $header_class = "password-reset-page flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passResult', $data);
    }

    public function join(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [
            "agree_modal.php",
            "privacy_modal.php",
            "marketing_modal.php"
        ];


        $agreements = $this->signupAgreementDocuments();
        $mobileOk = service('mobileOk');
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "agreementTerms" => $agreements['TERMS'],
            "agreementPrivacy" => $agreements['PRIVACY'],
            "mobileOkEnabled" => $mobileOk->isConfigured(),
            "mobileOkJsUrl" => $mobileOk->requestJsUrl(),
            "mobileOkRequestUrl" => base_url('member/phone-auth/request'),
            "mobileOkResultUrl" => $mobileOk->returnUrl(),
        ];


        return $this->renderView('member/join', $data);
    }

    public function joinComplete(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/joinComplete', $data);
    }

    public function fcAgree(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "agree_modal.php",
            "privacy_modal.php",
            "marketing_modal.php"
        ];


        $agreements = $this->signupAgreementDocuments();
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "agreementTerms" => $agreements['TERMS'],
            "agreementPrivacy" => $agreements['PRIVACY'],
        ];


        return $this->renderView('member/fcAgree', $data);
    }

    private function signupAgreementDocuments(): array
    {
        $documents = [
            'TERMS' => ['title' => '이용약관', 'content' => ''],
            'PRIVACY' => ['title' => '개인정보 수집 및 이용', 'content' => ''],
        ];

        $rows = \Config\Database::connect()->table('my_fc_terms')
            ->whereIn('term_type', array_keys($documents))
            ->where('display_status', 'Y')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->orderBy('term_id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $type = (string) ($row['term_type'] ?? '');
            if (isset($documents[$type]) && $documents[$type]['content'] === '') {
                $documents[$type] = [
                    'title' => (string) ($row['title'] ?? $documents[$type]['title']),
                    'content' => (string) ($row['content'] ?? ''),
                ];
            }
        }

        return $documents;
    }

    public function fcJoin_step1(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $mobileOk = service('mobileOk');
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "mobileOkEnabled" => $mobileOk->isConfigured(),
            "mobileOkJsUrl" => $mobileOk->requestJsUrl(),
            "mobileOkRequestUrl" => base_url('member/phone-auth/request'),
            "mobileOkResultUrl" => $mobileOk->returnUrl(),
        ];


        return $this->renderView('member/fcJoin_step1', $data);
    }

    public function fcJoin_step2(): string
    {
        //return pageView('welcome_message');
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "fc_time_modal.php",
            "fc_lang_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "profile" => $memberUid
                ? ($db->table('my_fc_profile')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
        ];


        return $this->renderView('member/fcJoin_step2', $data);
    }

    public function fcJoin_step3(): string
    {
        //return pageView('welcome_message');
        helper(['region', 'insurance']);
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "region_modal.php",
            "insurance_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "activity" => $memberUid
                ? ($db->table('my_fc_profile_activity')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
            "activityItems" => $memberUid
                ? $db->table('my_fc_profile_activity_item')
                    ->where('member_uid', $memberUid)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('item_id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [],
        ];


        return $this->renderView('member/fcJoin_step3', $data);
    }

    public function fcJoin_step4(): string
    {
        //return pageView('welcome_message');
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "story" => $memberUid
                ? ($db->table('my_fc_profile_story')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
            "storyImages" => $memberUid
                ? $db->table('my_fc_profile_story_image')
                    ->where('member_uid', $memberUid)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [],
        ];


        return $this->renderView('member/fcJoin_step4', $data);
    }

    public function fcComplete(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/fcComplete', $data);
    }

    public function checkEmail()
    {
        $email = $this->request->getJSON(true)['email'] ?? null;

        if (!$email) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'email required'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'invalid email format'
            ]);
        }

        $db = \Config\Database::connect();

        $exists = $db->table('my_fc_member')
            ->where('email', $email)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0
        ]);
    }


    public function checkPhone()
    {
        $data = $this->request->getJSON(true);
        $phone = $data['phone'] ?? '';

        if (!$phone) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'phone required'
            ]);
        }

        // 숫자만 정리
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'invalid phone format'
            ]);
        }

        $db = \Config\Database::connect();

        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0
        ]);
    }

    public function register()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            $db->transBegin();

            $data = $this->request->getJSON(true);

            if (!$data) {
                throw new \Exception('잘못된 요청입니다.');
            }

            $email = strtolower(trim($data['email'] ?? ''));
            $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
            $password = $data['password'] ?? '';
            $passwordConfirm = $data['password_confirm'] ?? '';
            $memberType = strtoupper(trim($data['member_type'] ?? 'USER'));
            $name = trim($data['name'] ?? '');
            $birth = preg_replace('/[^0-9]/', '', $data['birth'] ?? '');
            $gender = strtoupper(trim($data['gender'] ?? ''));
            $phoneVerified = 'N';
            $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
            $authName = trim((string) $session->get('phone_auth_name'));
            $authBirth = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_birth'));
            $authGender = strtoupper(trim((string) $session->get('phone_auth_gender')));
            $authVerified = (bool) $session->get('phone_auth_verified');

            // =========================
            // 2. 검증
            // =========================
            if (!$email || !$phone || !$password || !$passwordConfirm) {
                throw new \Exception('필수값 누락');
            }

            if (!in_array($memberType, ['USER', 'FC'], true)) {
                throw new \Exception('회원 유형이 올바르지 않습니다.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('이메일 형식 오류');
            }

            if (strlen($phone) < 10 || strlen($phone) > 11) {
                throw new \Exception('휴대폰 번호를 확인해주세요.');
            }

            if ($password !== $passwordConfirm) {
                throw new \Exception('비밀번호 불일치');
            }

            if ($authVerified) {
                if ($authPhone === '' || $authPhone !== $phone) {
                    throw new \Exception('인증된 휴대폰 번호와 입력값이 일치하지 않습니다.');
                }

                $phoneVerified = 'Y';
            } elseif (in_array($memberType, ['USER', 'FC'], true)) {
                throw new \Exception('휴대폰 인증을 먼저 완료해주세요.');
            }

            if ($memberType === 'USER') {
                if (!$authVerified) {
                    throw new \Exception('휴대폰 인증을 먼저 완료해주세요.');
                }

                $name = $authName;
                $birth = $authBirth;
                $gender = $authGender;

                if ($name === '' || !preg_match('/^\d{8}$/', $birth) || !in_array($gender, ['M', 'F'], true)) {
                    throw new \Exception('휴대폰 인증 정보를 확인해주세요.');
                }

                if (
                    empty($data['agree_age']) ||
                    empty($data['agree_terms']) ||
                    empty($data['agree_privacy'])
                ) {
                    throw new \Exception('필수 약관에 동의해주세요.');
                }
            } elseif ($memberType === 'FC') {
                if ($name === '' && $authName !== '') {
                    $name = $authName;
                }

                if ($name === '') {
                    throw new \Exception('이름을 입력해주세요.');
                }
            }

            // =========================
            // 3. 중복 체크
            // =========================
            $exists = $db->table('my_fc_member')
                ->groupStart()
                ->where('email', $email)
                ->orWhere('phone', $phone)
                ->groupEnd()
                ->countAllResults();

            if ($exists > 0) {
                throw new \Exception('이미 가입된 정보입니다.');
            }

            $now = date('Y-m-d H:i:s');

            // =========================
            // 4. INSERT
            // =========================
            $memberData = [
                'member_uid' => $this->generateMemberUid(),
                'member_type' => $memberType,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'phone'       => $phone,
                'name'        => $name,
                'birth'       => $birth ?: null,
                'gender'      => $gender ?: null,
                'phone_verified' => $phoneVerified,
                'agree_age'       => !empty($data['agree_age']) ? 1 : 0,
                'agree_terms'     => !empty($data['agree_terms']) ? 1 : 0,
                'agree_privacy'   => !empty($data['agree_privacy']) ? 1 : 0,
                'agree_marketing' => !empty($data['agree_marketing']) ? 1 : 0,
                'join_ip' => $this->request->getIPAddress(),
                'fc_step' => $memberType === 'FC' ? 1 : 0,
                'created_at' => $now,
            ];

            $db->table('my_fc_member')->insert($memberData);

            $memberId = $db->insertID();

            // =========================
            // 2. user check
            // =========================
            $user = $db->table('my_fc_member')
                ->where('email', $email)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();


            $session = session();
            // =========================
            // 4. session 저장
            // =========================
            $sessionData = [
                'member_id'  => $user['member_id'],
                'member_uid' => $user['member_uid'],
                'email'      => $user['email'],
                'name'       => $user['name'],
                'member_type' => $user['member_type'],
                'logged_in'  => true,
            ];

            // 🔥 FC 추가 상태 관리
            if ($user['member_type'] === 'FC') {

                // FC 가입 단계 (예: 프로필 미완료 상태)
                $sessionData['fc_step'] = $user['fc_step'] ?? 99;
                $sessionData['fc_onboarding'] = true;
            }

            $session->set($sessionData);
            $session->remove([
                'phone_auth_verified',
                'phone_auth_phone',
                'phone_auth_name',
                'phone_auth_birth',
                'phone_auth_gender',
                'phone_auth_verified_at',
                'phone_auth_tx_id',
            ]);

            $db->transCommit();

            return $this->response->setJSON([
                'status' => 'success',
                'member_id' => $memberId
            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function phoneAuthRequest()
    {
        $mobileOk = service('mobileOk');
        $session = session();
        $request = $this->request;

        if (!$mobileOk->isConfigured()) {
            log_message('error', $this->formatMobileOkFailureLog('request', 'missing-configuration', [
                'message' => '본인인증 설정이 누락되었습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'enabled' => $mobileOk->isEnabled() ? 'Y' : 'N',
                'missingConfiguration' => $mobileOk->missingConfiguration(),
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
            ]));

            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'message' => implode(' ', $mobileOk->missingConfiguration()),
            ]);
        }

        if (!$mobileOk->sdkAvailable()) {
            log_message('error', $this->formatMobileOkFailureLog('request', 'sdk-missing', [
                'message' => 'MobileOK SDK 파일 또는 composer autoload를 찾을 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'sdkAutoloadPath' => $mobileOk->sdkAutoloadPath(),
                'sdkAutoloadExists' => is_file($mobileOk->sdkAutoloadPath()) ? 'Y' : 'N',
                'sdkManagerPath' => $mobileOk->sdkManagerPath(),
                'sdkManagerExists' => is_file($mobileOk->sdkManagerPath()) ? 'Y' : 'N',
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
            ]));

            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'message' => 'MobileOK SDK 파일 또는 composer autoload를 찾을 수 없습니다.',
            ]);
        }

        try {
            $clientTxId = $mobileOk->makeClientTxId();
            $session->set([
                'phone_auth_tx_id' => $clientTxId,
                'phone_auth_requested_at' => date('Y-m-d H:i:s'),
                'phone_auth_verified' => false,
                'phone_auth_phone' => null,
                'phone_auth_name' => null,
                'phone_auth_birth' => null,
                'phone_auth_gender' => null,
                'phone_auth_issue_date' => null,
                'phone_auth_result_code' => null,
            ]);

            $manager = $mobileOk->createSdkManager();
            $payload = $mobileOk->makeRequestPayload(
                $manager,
                $clientTxId,
                $mobileOk->returnUrl()
            );

            return $this->response
                ->setContentType('application/json', 'UTF-8')
                ->setBody(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            log_message('error', $this->formatMobileOkFailureLog('request', 'exception', [
                'message' => $e->getMessage(),
                'exceptionClass' => get_class($e),
                'mode' => $mobileOk->mode(),
                'enabled' => $mobileOk->isEnabled() ? 'Y' : 'N',
                'serviceId' => $mobileOk->serviceId(),
                'clientPrefix' => $mobileOk->clientPrefix(),
                'usageCode' => $mobileOk->usageCode(),
                'serviceType' => $mobileOk->serviceType(),
                'retTransferType' => $mobileOk->retTransferType(),
                'requestMode' => $mobileOk->requestMode(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'sdkAutoloadPath' => $mobileOk->sdkAutoloadPath(),
                'sdkAutoloadExists' => is_file($mobileOk->sdkAutoloadPath()) ? 'Y' : 'N',
                'sdkManagerPath' => $mobileOk->sdkManagerPath(),
                'sdkManagerExists' => is_file($mobileOk->sdkManagerPath()) ? 'Y' : 'N',
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
                'clientTxId' => $clientTxId ?? '',
            ]));

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '휴대폰 본인인증 요청을 생성하지 못했습니다.',
            ]);
        }
    }

    public function phoneAuthResult()
    {
        $mobileOk = service('mobileOk');
        $session = session();
        $db = \Config\Database::connect();
        $request = $this->request;

        $payload = $this->request->getJSON(true);
        if (!$payload) {
            $payload = $this->request->getPost();
        }

        if (!$payload) {
            log_message('error', $this->formatMobileOkFailureLog('result', 'payload-missing', [
                'message' => '본인인증 결과가 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => (string) $session->get('phone_auth_tx_id'),
                'sessionRequestedAt' => (string) $session->get('phone_auth_requested_at'),
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 결과가 없습니다.',
            ]);
        }

        $resultData = $payload['payload'] ?? $payload;
        $resultCode = (string) ($resultData['resultCode'] ?? '');
        $resultMsg = (string) ($resultData['resultMsg'] ?? '');
        $sessionTxId = (string) $session->get('phone_auth_tx_id');
        $resultTxId = (string) ($resultData['clientTxId'] ?? '');
        $issueDate = trim((string) ($resultData['issueDate'] ?? ''));

        if ($resultCode !== '' && $resultCode !== '2000') {
            log_message('error', $this->formatMobileOkFailureLog('result', 'result-code-failed', [
                'message' => $resultMsg !== '' ? $resultMsg : '본인인증에 실패했습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $resultMsg !== '' ? $resultMsg : '본인인증에 실패했습니다.',
            ]);
        }

        if ($sessionTxId !== '' && $resultTxId !== '' && $sessionTxId !== $resultTxId) {
            log_message('error', $this->formatMobileOkFailureLog('result', 'txid-mismatch', [
                'message' => '본인인증 거래 정보가 일치하지 않습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 거래 정보가 일치하지 않습니다.',
            ]);
        }

        if ($issueDate !== '' && $mobileOk->isExpired($issueDate)) {
            log_message('error', $this->formatMobileOkFailureLog('result', 'token-expired', [
                'message' => '본인인증 유효 시간이 만료되었습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 유효 시간이 만료되었습니다.',
            ]);
        }

        $phone = $mobileOk->normalizePhone((string) ($resultData['userPhone'] ?? $resultData['phone'] ?? ''));
        $name = trim((string) ($resultData['userName'] ?? $resultData['name'] ?? ''));
        $birth = preg_replace('/[^0-9]/', '', (string) ($resultData['userBirthday'] ?? $resultData['birth'] ?? ''));
        $gender = strtoupper(trim((string) ($resultData['userGender'] ?? $resultData['gender'] ?? '')));
        $ci = trim((string) ($resultData['ci'] ?? ''));
        $di = trim((string) ($resultData['di'] ?? ''));
        $siteId = trim((string) ($resultData['siteID'] ?? ''));
        $providerId = trim((string) ($resultData['providerId'] ?? ''));
        $serviceType = trim((string) ($resultData['serviceType'] ?? ''));
        $reqAuthType = trim((string) ($resultData['reqAuthType'] ?? ''));
        $reqDate = trim((string) ($resultData['reqDate'] ?? ''));
        $issuer = trim((string) ($resultData['issuer'] ?? ''));
        $nation = trim((string) ($resultData['userNation'] ?? ''));

        if ($gender === '1') {
            $gender = 'M';
        } elseif ($gender === '2') {
            $gender = 'F';
        }

        if ($phone === '') {
            log_message('error', $this->formatMobileOkFailureLog('result', 'phone-missing', [
                'message' => '휴대폰 번호를 확인할 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 확인할 수 없습니다.',
            ]);
        }

        if ($name === '' && $siteId === '') {
            log_message('error', $this->formatMobileOkFailureLog('result', 'identity-missing', [
                'message' => '본인인증 결과를 확인할 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 결과를 확인할 수 없습니다.',
            ]);
        }

        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'duplicate' => true,
                'message' => '이미 사용 중인 휴대폰 번호입니다.',
            ]);
        }

        $session->set([
            'phone_auth_verified' => true,
            'phone_auth_result_code' => $resultCode,
            'phone_auth_result_msg' => $resultMsg,
            'phone_auth_phone' => $phone,
            'phone_auth_name' => $name,
            'phone_auth_birth' => $birth,
            'phone_auth_gender' => $gender,
            'phone_auth_ci' => $ci,
            'phone_auth_di' => $di,
            'phone_auth_site_id' => $siteId,
            'phone_auth_provider_id' => $providerId,
            'phone_auth_service_type' => $serviceType,
            'phone_auth_req_auth_type' => $reqAuthType,
            'phone_auth_req_date' => $reqDate,
            'phone_auth_issuer' => $issuer,
            'phone_auth_nation' => $nation,
            'phone_auth_issue_date' => $issueDate,
            'phone_auth_verified_at' => date('Y-m-d H:i:s'),
            'phone_auth_tx_id' => $resultTxId !== '' ? $resultTxId : $sessionTxId,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'phone' => $phone,
            'name' => $name,
            'birth' => $birth,
            'gender' => $gender,
            'resultCode' => $resultCode,
            'resultMsg' => $resultMsg,
            'issueDate' => $issueDate,
            'phone_verified' => 'Y',
        ]);
    }

    /**
     * 업체 전달용 실패 로그를 만든다.
     *
     * @param array<string, mixed> $context
     */
    private function formatMobileOkFailureLog(string $stage, string $reason, array $context): string
    {
        $lines = [
            '[MobileOK Failure]',
            'stage: ' . $stage,
            'reason: ' . $reason,
        ];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $lines[] = $key . ': ' . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                continue;
            }

            $lines[] = $key . ': ' . (string) $value;
        }

        return implode("\n", $lines);
    }

    private function generateMemberUid()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz';
        $db = \Config\Database::connect();

        do {
            $uid = '';
            for ($i = 0; $i < 20; $i++) {
                $uid .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = $db->table('my_fc_member')
                ->where('member_uid', $uid)
                ->countAllResults();
        } while ($exists > 0);

        return $uid;
    }

    public function loginProc()
    {
        $this->response->setContentType('application/json');

        $db = \Config\Database::connect();

        $data = $this->request->getJSON(true);

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $remember = $data['remember'] ?? 0;

        // =========================
        // 1. validation
        // =========================
        if (!$email || !$password) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '필수값 누락'
            ]);
        }

        // =========================
        // 2. user check
        // =========================
        $user = $db->table('my_fc_member')
            ->where('email', $email)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '존재하지 않는 계정입니다'
            ]);
        }

        // =========================
        // 3. password check
        // =========================
        if (!password_verify($password, $user['password'])) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '비밀번호가 일치하지 않습니다'
            ]);
        }

        $session = session();

        // =========================
        // 4. session 저장
        // =========================
        $sessionData = [
            'member_id'  => $user['member_id'],
            'member_uid' => $user['member_uid'],
            'email'      => $user['email'],
            'name'       => $user['name'],
            'member_type' => $user['member_type'],
            'logged_in'  => true,
        ];

        // 🔥 FC 추가 상태 관리
        if ($user['member_type'] === 'FC') {

            // FC 가입 단계 (예: 프로필 미완료 상태)
            $sessionData['fc_step'] = $user['fc_step'] ?? 99;
            $sessionData['fc_onboarding'] = true;
        }

        $session->set($sessionData);

        // =========================
        // 5. 로그인 로그 업데이트
        // =========================
        $db->table('my_fc_member')
            ->where('member_id', $user['member_id'])
            ->update([
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

        // =========================
        // 6. 성공 응답
        // =========================
        return $this->response->setJSON([
            'status' => 'success',
            'member_id' => $user['member_id']
        ]);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/');
    }

    public function fcProfileUpdate()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            helper('fileupload_helper');

            $company = trim((string) $this->request->getPost('company'));
            $companySub = trim((string) $this->request->getPost('company_sub'));
            $ga = trim((string) $this->request->getPost('ga'));
            $position = trim((string) $this->request->getPost('position'));
            $licenseDate = trim((string) $this->request->getPost('license_date'));
            $licenseNo = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('license_no'));
            $timeFrom = $this->request->getPost('time_from');
            $timeTo = $this->request->getPost('time_to');
            $language = trim((string) $this->request->getPost('language'));

            if ($company === '' && $ga === '') {
                throw new \Exception('소속 원수사 또는 소속 GA 중 하나는 반드시 입력해주세요.');
            }

            if ($ga !== '' && $companySub !== '') {
                throw new \Exception('소속 GA를 입력한 경우 추가 소속 보험사는 입력할 수 없습니다.');
            }

            if ($position === '' || $licenseDate === '' || $licenseNo === '' || $language === '') {
                throw new \Exception('프로필 필수값을 입력해주세요.');
            }

            if (!is_numeric($timeFrom) || !is_numeric($timeTo)) {
                throw new \Exception('상담 가능 시간을 선택해주세요.');
            }

            $timeFrom = (int) $timeFrom;
            $timeTo = (int) $timeTo;

            if ($timeFrom < 0 || $timeFrom > 23 || $timeTo < 0 || $timeTo > 23) {
                throw new \Exception('상담 가능 시간 값이 올바르지 않습니다.');
            }

            // =========================
            // 1. 기존 데이터 확인
            // =========================
            $profile = $db->table('my_fc_profile')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

            // =========================
            // 2. 파일 업로드 처리 (있을 때만)
            // =========================
            $file = $this->request->getFile('profile_image');
            $fileName = null;

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $fileName = upload_file($file, 'uploads/profile');
            }

            // =========================
            // 3. 공통 데이터 구성
            // =========================
            $data = [
                'member_uid'   => $memberUid,
                'company'      => $company !== '' ? $company : null,
                'company_sub'  => $companySub !== '' ? $companySub : null,
                'ga'           => $ga !== '' ? $ga : null,
                'position'     => $position,
                'license_date' => $licenseDate,
                'license_no'   => $licenseNo,
                'time_from'    => $timeFrom,
                'time_to'      => $timeTo,
                'language'     => $language,
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            // =========================
            // 4. 이미지 조건부 업데이트 핵심
            // =========================
            if ($fileName) {
                $data['profile_image'] = $fileName;
            }

            // =========================
            // 5. INSERT / UPDATE 분기
            // =========================
            if ($profile) {

                // UPDATE
                $db->table('my_fc_profile')
                    ->where('member_uid', $memberUid)
                    ->update($data);
            } else {

                // INSERT
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['step'] = 2;

                $db->table('my_fc_profile')->insert($data);
            }

            // =========================
            // 6. member fc_step 업데이트
            // =========================
            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update([
                    'fc_step' => 2,
                    'fc_review_status' => 'WAIT',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // =========================
            // 7. session sync
            // =========================
            $session->set([
                'fc_step' => 2
            ]);

            $db->transCommit();

            return $this->response->setJSON([
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    public function fcActivitySave()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            helper('fileupload_helper');

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            $region = trim((string) $this->request->getPost('region'));
            $insuranceTypes = trim((string) $this->request->getPost('insurance_types'));
            $heroLine = trim((string) $this->request->getPost('history'));
            $intro = trim((string) $this->request->getPost('intro'));
            $career = trim((string) $this->request->getPost('career'));

            if ($region === '' || $heroLine === '' || $intro === '' || $career === '') {
                throw new \Exception('활동 정보 필수값을 입력해주세요.');
            }

            // ===========================
            // Activity 저장
            // ===========================

            $activityData = [

                'member_uid'      => $memberUid,

                'region'          => $region,

                'insurance_types' => $insuranceTypes !== '' ? $insuranceTypes : null,

                'hero_line'       => $heroLine,

                'intro'           => $intro,

                'career'          => $career,

                'updated_at'      => date('Y-m-d H:i:s'),

            ];

            $exists = $db->table('my_fc_profile_activity')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

            if ($exists) {

                $db->table('my_fc_profile_activity')
                    ->where('member_uid', $memberUid)
                    ->update($activityData);
            } else {

                $activityData['created_at'] = date('Y-m-d H:i:s');

                $db->table('my_fc_profile_activity')
                    ->insert($activityData);
            }

            // ===========================
            // 삭제 처리
            // ===========================

            $deleteItems = $this->request->getPost('delete_items');

            if (!empty($deleteItems)) {

                if (!is_array($deleteItems)) {
                    $deleteItems = explode(',', $deleteItems);
                }

                $deleteItems = array_filter($deleteItems);

                if (!empty($deleteItems)) {

                    $deleteRows = $db->table('my_fc_profile_activity_item')
                        ->where('member_uid', $memberUid)
                        ->whereIn('item_id', $deleteItems)
                        ->get()
                        ->getResultArray();

                    foreach ($deleteRows as $row) {

                        if (!empty($row['file_path'])) {

                            $path = ROOTPATH . 'public/uploads/activity/' . $row['file_path'];

                            if (is_file($path)) {
                                @unlink($path);
                            }
                        }
                    }

                    $db->table('my_fc_profile_activity_item')
                        ->where('member_uid', $memberUid)
                        ->whereIn('item_id', $deleteItems)
                        ->delete();
                }
            }

            // ===========================
            // items
            // ===========================

            $items = $this->request->getVar('items');

            if (!is_array($items)) {
                $items = [];
            }

            if (empty($items)) {
                throw new \Exception('이력 및 인증을 최소 1개 이상 입력해주세요.');
            }

            $keepIds = [];
            $savedItemCount = 0;

            // ===========================
            // items 저장
            // ===========================

            foreach ($items as $i => $item) {

                $type = $item['type'] ?? 'text';

                $data = [

                    'member_uid' => $memberUid,

                    'category'   => 'activity',

                    'type'       => $type,

                    'title'      => trim($item['title'] ?? ''),

                    'content'    => trim($item['content'] ?? '') ?: null,

                    'url'        => trim($item['url'] ?? '') ?: null,

                    'sort_order' => $i,

                    'is_visible' => 1,

                ];

                // 제목 없으면 skip
                if ($data['title'] == '') {
                    continue;
                }

                if (!in_array($type, ['file', 'link', 'text'], true)) {
                    continue;
                }

                $itemId = $item['item_id'] ?? null;

                // ==========================
                // FILE 업로드
                // ==========================

                if ($type == 'file') {

                    $file = $this->request->getFile("items.$i.file");

                    if ($file && $file->isValid() && !$file->hasMoved()) {

                        // 기존파일 삭제
                        if (!empty($itemId)) {

                            $old = $db->table('my_fc_profile_activity_item')
                                ->where('item_id', $itemId)
                                ->where('member_uid', $memberUid)
                                ->get()
                                ->getRowArray();

                            if (!empty($old['file_path'])) {

                                $oldPath = ROOTPATH .
                                    'public/uploads/activity/' .
                                    $old['file_path'];

                                if (is_file($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                        }

                        // 새파일 저장
                        $data['file_path'] = upload_file(
                            $file,
                            'uploads/activity'
                        );
                    } else {

                        // 수정 시 기존파일 유지
                        if (!empty($itemId)) {

                            $old = $db->table('my_fc_profile_activity_item')
                                ->where('item_id', $itemId)
                                ->where('member_uid', $memberUid)
                                ->get()
                                ->getRowArray();

                            if (!empty($old['file_path'])) {

                                $data['file_path'] = $old['file_path'];
                            }
                        }
                    }

                    if (empty($data['file_path'])) {
                        throw new \Exception('첨부 파일을 선택해주세요.');
                    }
                }

                if ($type === 'link' && empty($data['url'])) {
                    throw new \Exception('링크 주소를 입력해주세요.');
                }

                if ($type === 'text' && empty($data['content'])) {
                    throw new \Exception('기타 정보를 입력해주세요.');
                }

                // ==========================
                // UPDATE
                // ==========================

                if (!empty($itemId)) {

                    $db->table('my_fc_profile_activity_item')
                        ->where('item_id', $itemId)
                        ->where('member_uid', $memberUid)
                        ->update($data);

                    $keepIds[] = $itemId;
                    $savedItemCount++;
                }

                // ==========================
                // INSERT
                // ==========================

                else {

                    $data['created_at'] = date('Y-m-d H:i:s');

                    $db->table('my_fc_profile_activity_item')
                        ->insert($data);

                    $keepIds[] = $db->insertID();
                    $savedItemCount++;
                }
            }

            if ($savedItemCount < 1) {
                throw new \Exception('이력 및 인증을 최소 1개 이상 입력해주세요.');
            }

            // ===========================
            // 회원 단계 업데이트
            // ===========================

            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update([

                    'fc_step'    => 3,
                    'fc_review_status' => 'WAIT',

                    'updated_at' => date('Y-m-d H:i:s')

                ]);

            // ===========================
            // Commit
            // ===========================

            if ($db->transStatus() === false) {

                throw new \Exception('DB 저장 중 오류가 발생했습니다.');
            }

            $db->transCommit();

            return $this->response->setJSON([

                'status' => 'success',

                'message' => '저장되었습니다.',

            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->__toString());

            return $this->response->setJSON([

                'status' => 'error',

                'message' => $e->getMessage()

            ]);
        }
    }


    public function fcStorySave()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            helper('fileupload_helper');
            helper('filesystem');

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            //---------------------------------------------------
            // Story 조회
            //---------------------------------------------------

            $story = $db->table('my_fc_profile_story')
                ->where('member_uid',$memberUid)
                ->get()
                ->getRowArray();

            $storyData=[];

            //---------------------------------------------------
            // 대표 영상
            //---------------------------------------------------

            $video=$this->request->getFile('story_video');

            if($video && $video->isValid() && !$video->hasMoved()){

                if(!empty($story['story_video'])){

                    @unlink(ROOTPATH.'public/uploads/story/video/'.$story['story_video']);

                }

                $storyData['story_video']=upload_file(
                    $video,
                    'uploads/story/video'
                );

            }

            //---------------------------------------------------
            // 대표 이미지
            //---------------------------------------------------

            $image=$this->request->getFile('story_image');

            if($image && $image->isValid() && !$image->hasMoved()){

                if(!empty($story['story_image'])){

                    @unlink(ROOTPATH.'public/uploads/story/main/'.$story['story_image']);

                }

                $storyData['story_image']=upload_file(
                    $image,
                    'uploads/story/main'
                );

            }

            //---------------------------------------------------
            // Story 저장
            //---------------------------------------------------

            if($story){

                if(!empty($storyData)){

                    $storyData['updated_at']=date('Y-m-d H:i:s');

                    $db->table('my_fc_profile_story')
                        ->where('member_uid',$memberUid)
                        ->update($storyData);

                }

            }else{

                $storyData['member_uid']=$memberUid;
                $storyData['created_at']=date('Y-m-d H:i:s');

                $db->table('my_fc_profile_story')
                    ->insert($storyData);

            }

            //---------------------------------------------------
            // 기존 이미지
            //---------------------------------------------------

            $keepImages=$this->request->getPost('keep_images');

            if(!is_array($keepImages)){
                $keepImages=[];
            }

            $imageOrder=$this->request->getPost('story_image_order');

            if(!is_array($imageOrder)){
                $imageOrder=[];
            }

            $files=$this->request->getFiles();
            $newImageFiles=[];

            if(isset($files['story_images'])){
                $newImageFiles=is_array($files['story_images'])
                    ? $files['story_images']
                    : [$files['story_images']];
            }

            $validNewImageCount=0;

            foreach($newImageFiles as $file){
                if($file && $file->isValid()){
                    $validNewImageCount++;
                }
            }

            if(count($keepImages) + $validNewImageCount < 1){
                throw new \Exception('스토리 이미지를 최소 1개 이상 등록해주세요.');
            }

            $oldImages=$db->table('my_fc_profile_story_image')
                ->where('member_uid',$memberUid)
                ->get()
                ->getResultArray();

            $keepImages=array_values(array_unique(array_filter($keepImages)));
            $oldImageIds=array_column($oldImages,'id');

            if(empty($imageOrder)){
                foreach($keepImages as $id){
                    $imageOrder[]='existing:'.$id;
                }

                for($i=0;$i<$validNewImageCount;$i++){
                    $imageOrder[]='new';
                }
            }

            //---------------------------------------------------
            // 삭제 이미지 찾기
            //---------------------------------------------------

            foreach($oldImages as $img){

                if(!in_array($img['id'],$keepImages)){

                    @unlink(
                        ROOTPATH.'public/uploads/story/images/'.$img['image_path']
                    );

                    $db->table('my_fc_profile_story_image')
                        ->where('id',$img['id'])
                        ->delete();

                }

            }

            //---------------------------------------------------
            // 이미지 순서 및 신규 이미지
            //---------------------------------------------------

            $sort=0;
            $newFileIndex=0;

            foreach($imageOrder as $token){
                if(strpos($token,'existing:')===0){
                    $id=(int) substr($token,9);

                    if($id>0 && in_array($id,$oldImageIds)){
                        $db->table('my_fc_profile_story_image')
                            ->where('id',$id)
                            ->where('member_uid',$memberUid)
                            ->update([
                                'sort_order'=>$sort++
                            ]);
                    }

                    continue;
                }

                if($token==='new' && isset($newImageFiles[$newFileIndex])){
                    $file=$newImageFiles[$newFileIndex++];

                    if(!$file->isValid()) continue;

                    $path=upload_file(
                        $file,
                        'uploads/story/images'
                    );

                    $db->table('my_fc_profile_story_image')->insert([
                        'member_uid'=>$memberUid,
                        'image_path'=>$path,
                        'sort_order'=>$sort++,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                }
            }

            //---------------------------------------------------
            // Step
            //---------------------------------------------------

            $db->table('my_fc_member')
                ->where('member_uid',$memberUid)
                ->update([

                    'fc_step'=>4,
                    'fc_review_status'=>'WAIT',

                    'updated_at'=>date('Y-m-d H:i:s')

                ]);

            $session->set([
                'fc_step'=>4,
                'fc_onboarding'=>true,
            ]);

            $db->transCommit();

            return $this->response->setJSON([

                'status'=>'success',
                'redirect_url' => base_url('fc/view') . '?uid=' . rawurlencode((string) $memberUid),

            ]);

        }catch(\Throwable $e){

            $db->transRollback();

            return $this->response->setJSON([

                'status'=>'error',

                'message'=>$e->getMessage()

            ]);

        }

    }

    public function updateBasicInfo()
    {
        $this->response->setContentType('application/json');

        $session = session();

        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $data = $this->request->getJSON(true);

        $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
        $name = trim($data['name'] ?? '');
        $agreeMarketing = (int)($data['agree_marketing'] ?? 0);

        if (!$phone) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 입력해주세요.'
            ]);
        }

        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 확인해주세요.'
            ]);
        }

        if ($name === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '이름을 입력해주세요.'
            ]);
        }

        $db = \Config\Database::connect();

        $member = $db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_uid', $memberUid)
            ->where('member_type', 'FC')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'FC 회원만 수정할 수 있습니다.'
            ]);
        }

        // 휴대폰 중복 체크
        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->where('member_id !=', $memberId)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '이미 사용중인 휴대폰 번호입니다.'
            ]);
        }

        $db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->update([
                'name' => $name,
                'phone' => $phone,
                'agree_marketing' => $agreeMarketing,
                'fc_review_status' => 'WAIT',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '회원정보가 수정되었습니다.'
        ]);
    }
    public function appLogin()
    {

        $request = service('request');


        $appToken =
            $request->getPost('app_token');


        $fcmToken =
            $request->getPost('fcm_token');



        if(empty($appToken)){

            return $this->response->setJSON([
                'result'=>false,
                'message'=>'TOKEN_EMPTY'
            ]);

        }



        $db = \Config\Database::connect();



        // 회원 조회
        $member =
            $db->table('my_fc_member')
            ->where(
                'app_token',
                $appToken
            )
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();



        if(!$member){


            return $this->response->setJSON([

                'result'=>false,

                'message'=>'MEMBER_NOT_FOUND'

            ]);

        }



        // =========================
        // FCM 토큰 업데이트
        // =========================

        $db->table('my_fc_member')
            ->where(
                'member_id',
                $member['member_id']
            )
            ->update([

                'fcm_token'=>$fcmToken,

                'fcm_token_updated_at'=>date(
                    'Y-m-d H:i:s'
                )

            ]);




        // =========================
        // 세션 생성
        // =========================

        $session = session();


        $session->set([

            'member_id'=>
                $member['member_id'],


            'member_uid'=>
                $member['member_uid'],


            'email'=>
                $member['email'],


            'name'=>
                $member['name'],


            'member_type'=>
                $member['member_type'],


            'logged_in'=>true

        ]);




        return $this->response->setJSON([

            'result'=>true,

            'member'=>[

                'member_uid'=>
                    $member['member_uid'],

                'name'=>
                    $member['name']

            ]

        ]);

    }
}
