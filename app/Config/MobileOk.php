<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class MobileOk extends BaseConfig
{
    /**
     * 연동 모드. development 또는 production.
     */
    public string $mode = 'development';

    /**
     * MobileOK 연동 사용 여부.
     */
    public bool $enabled = true;

    /**
     * 개발용 자바스크립트 로드 URL.
     */
    public string $requestJsUrlDevelopment = 'https://scert.mobile-ok.com/resources/js/index.js';

    /**
     * 운영용 자바스크립트 로드 URL.
     */
    public string $requestJsUrlProduction = 'https://cert.mobile-ok.com/resources/js/index.js';

    /**
     * 개발용 결과 조회 API URL.
     */
    public string $resultRequestUrlDevelopment = 'https://scert.mobile-ok.com/gui/service/v1/result/request';

    /**
     * 운영용 결과 조회 API URL.
     */
    public string $resultRequestUrlProduction = 'https://cert.mobile-ok.com/gui/service/v1/result/request';

    /**
     * 개발용 키파일 경로.
     */
    public string $keyPathDevelopment = ROOTPATH . 'writable/private/mok-development/mok_keyInfo.dat';

    /**
     * 운영용 키파일 경로.
     */
    public string $keyPathProduction = ROOTPATH . 'writable/private/mok/mok_keyInfo.dat';

    /**
     * 키파일 비밀번호.
     */
    public string $keyPassword = 'myfc20263131';

    /**
     * 이용기관 서비스 ID.
     */
    public string $serviceId = '';

    /**
     * 이용기관 거래ID 접두어.
     */
    public string $clientPrefix = 'myfciv';

    /**
     * 본인확인 서비스 용도 코드.
     */
    public string $usageCode = '01001';

    /**
     * 본인확인 서비스 타입.
     */
    public string $serviceType = 'telcoAuth';

    /**
     * 본인확인 결과 전달 타입.
     */
    public string $retTransferType = 'MOKToken';

    /**
     * 표준창 요청 모드.
     */
    public string $requestMode = 'WB';

    /**
     * 세션에 저장할 거래 ID 키 이름.
     */
    public string $sessionClientTxIdKey = 'sessionClientTxId';

    /**
     * 결과 토큰 유효 시간(분).
     */
    public int $resultTokenTtlMinutes = 10;

    public function __construct()
    {
        parent::__construct();

        $mode = strtolower(trim((string) (env('mobileok.mode') ?? $this->mode)));
        if (in_array($mode, ['development', 'production'], true)) {
            $this->mode = $mode;
        }

        $enabled = env('mobileok.enabled');
        if ($enabled !== null) {
            $this->enabled = filter_var($enabled, FILTER_VALIDATE_BOOL);
        }

        $this->requestJsUrlDevelopment = (string) (env('mobileok.requestJsUrlDevelopment') ?? $this->requestJsUrlDevelopment);
        $this->requestJsUrlProduction = (string) (env('mobileok.requestJsUrlProduction') ?? $this->requestJsUrlProduction);
        $this->resultRequestUrlDevelopment = (string) (env('mobileok.resultRequestUrlDevelopment') ?? $this->resultRequestUrlDevelopment);
        $this->resultRequestUrlProduction = (string) (env('mobileok.resultRequestUrlProduction') ?? $this->resultRequestUrlProduction);
        $this->keyPathDevelopment = (string) (env('mobileok.keyPathDevelopment') ?? $this->keyPathDevelopment);
        $this->keyPathProduction = (string) (env('mobileok.keyPathProduction') ?? $this->keyPathProduction);
        $this->keyPassword = (string) (env('mobileok.keyPassword') ?? $this->keyPassword);
        $this->serviceId = (string) (env('mobileok.serviceId') ?? $this->serviceId);
        $this->clientPrefix = (string) (env('mobileok.clientPrefix') ?? $this->clientPrefix);
        $this->usageCode = (string) (env('mobileok.usageCode') ?? $this->usageCode);
        $this->serviceType = (string) (env('mobileok.serviceType') ?? $this->serviceType);
        $this->retTransferType = (string) (env('mobileok.retTransferType') ?? $this->retTransferType);
        $this->requestMode = (string) (env('mobileok.requestMode') ?? $this->requestMode);
        $this->sessionClientTxIdKey = (string) (env('mobileok.sessionClientTxIdKey') ?? $this->sessionClientTxIdKey);
    }
}
