<?php

namespace App\Libraries;

use Config\MobileOk as MobileOkConfig;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

class MobileOkService
{
    public function __construct(
        protected ?MobileOkConfig $config = null
    ) {
        $this->config ??= config(MobileOkConfig::class);
    }

    public function config(): MobileOkConfig
    {
        return $this->config;
    }

    public function isEnabled(): bool
    {
        return $this->config->enabled;
    }

    public function mode(): string
    {
        return $this->config->mode;
    }

    public function isProduction(): bool
    {
        return $this->mode() === 'production';
    }

    public function requestJsUrl(): string
    {
        return $this->isProduction()
            ? $this->config->requestJsUrlProduction
            : $this->config->requestJsUrlDevelopment;
    }

    public function resultRequestUrl(): string
    {
        return $this->isProduction()
            ? $this->config->resultRequestUrlProduction
            : $this->config->resultRequestUrlDevelopment;
    }

    public function keyPath(): string
    {
        return $this->isProduction()
            ? $this->config->keyPathProduction
            : $this->config->keyPathDevelopment;
    }

    public function keyPassword(): string
    {
        return $this->config->keyPassword;
    }

    public function serviceId(): string
    {
        return $this->config->serviceId;
    }

    public function clientPrefix(): string
    {
        return $this->config->clientPrefix;
    }

    public function usageCode(): string
    {
        return $this->config->usageCode;
    }

    public function serviceType(): string
    {
        return $this->config->serviceType;
    }

    public function retTransferType(): string
    {
        return $this->config->retTransferType;
    }

    public function requestMode(): string
    {
        return $this->config->requestMode;
    }

    public function returnUrl(string $path = 'member/phone-auth/result'): string
    {
        return base_url(ltrim($path, '/'));
    }

    public function currentBaseUrl(): string
    {
        return rtrim(base_url('/'), '/') . '/';
    }

    public function sessionClientTxIdKey(): string
    {
        return $this->config->sessionClientTxIdKey;
    }

    public function resultTokenTtlMinutes(): int
    {
        return $this->config->resultTokenTtlMinutes;
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled()
            && $this->keyPath() !== ''
            && is_file($this->keyPath())
            && $this->keyPassword() !== ''
            && $this->clientPrefix() !== ''
            && $this->serviceId() !== '';
    }

    /**
     * 운영 반영 전에 필요한 설정 누락 항목을 반환한다.
     *
     * @return list<string>
     */
    public function missingConfiguration(): array
    {
        $missing = [];

        if (!$this->isEnabled()) {
            $missing[] = 'MobileOK 사용 여부가 비활성화 상태입니다.';
        }

        if ($this->keyPath() === '') {
            $missing[] = '키파일 경로가 비어 있습니다.';
        } elseif (!is_file($this->keyPath())) {
            $missing[] = '키파일이 존재하지 않습니다.';
        }

        if ($this->keyPassword() === '') {
            $missing[] = '키파일 비밀번호가 비어 있습니다.';
        }

        if ($this->clientPrefix() === '') {
            $missing[] = '거래ID 접두어가 비어 있습니다.';
        }

        if ($this->serviceId() === '') {
            $missing[] = '서비스 ID가 비어 있습니다.';
        }

        return $missing;
    }

    /**
     * 본인확인 거래용 거래 ID를 만든다.
     */
    public function makeClientTxId(?string $prefix = null): string
    {
        $basePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix ?? $this->clientPrefix()) ?? '';
        $randomTail = bin2hex(random_bytes(16));
        $maxPrefixLength = max(0, 40 - strlen($randomTail));
        $basePrefix = substr($basePrefix, 0, $maxPrefixLength);

        $clientTxId = $basePrefix . $randomTail;

        if (strlen($clientTxId) < 20) {
            $clientTxId = str_pad($clientTxId, 20, '0');
        }

        return substr($clientTxId, 0, 40);
    }

    /**
     * 요청 정보 생성 시 사용할 "거래ID|시간" 문자열을 만든다.
     */
    public function makeRequestClientInfo(string $clientTxId, ?DateTimeImmutable $dateTime = null): string
    {
        $dateTime ??= new DateTimeImmutable('now', new DateTimeZone('Asia/Seoul'));

        return $clientTxId . '|' . $dateTime->format('YmdHis');
    }

    /**
     * 세션 저장용 키를 만든다.
     */
    public function makeSessionKey(string $suffix): string
    {
        return $this->sessionClientTxIdKey() . '.' . ltrim($suffix, '.');
    }

    /**
     * 결과 토큰 만료 시각을 검사한다.
     */
    public function isExpired(string $issueDate, ?DateTimeImmutable $now = null): bool
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('Asia/Seoul'));
        $issuedAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $issueDate, new DateTimeZone('Asia/Seoul'));

        if ($issuedAt === false) {
            try {
                $issuedAt = new DateTimeImmutable($issueDate, new DateTimeZone('Asia/Seoul'));
            } catch (\Throwable $e) {
                throw new RuntimeException('본인확인 토큰 시간 형식이 올바르지 않습니다.', 0, $e);
            }
        }

        $deadline = $issuedAt->modify('+' . $this->resultTokenTtlMinutes() . ' minutes');

        return $deadline < $now;
    }

    /**
     * 휴대폰 번호를 비교/저장 전에 통일할 때 사용한다.
     */
    public function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public function sdkAutoloadPath(): string
    {
        return ROOTPATH . 'vendor/autoload.php';
    }

    public function sdkManagerPath(): string
    {
        return ROOTPATH . 'app/ThirdParty/MobileOk/mobileOK_manager_phpseclib_v3.0_v1.0.2.php';
    }

    public function sdkAvailable(): bool
    {
        return is_file($this->sdkAutoloadPath()) && is_file($this->sdkManagerPath());
    }

    /**
     * MobileOK SDK manager 인스턴스를 초기화한다.
     */
    public function createSdkManager(): object
    {
        if (!is_file($this->sdkAutoloadPath())) {
            throw new RuntimeException('phpseclib autoload 파일이 존재하지 않습니다.');
        }

        if (!is_file($this->sdkManagerPath())) {
            throw new RuntimeException('MobileOK SDK 파일이 존재하지 않습니다.');
        }

        require_once $this->sdkAutoloadPath();
        require_once $this->sdkManagerPath();

        if (!class_exists('mobileOK_Key_Manager', false)) {
            throw new RuntimeException('MobileOK SDK 클래스를 불러오지 못했습니다.');
        }

        $manager = new \mobileOK_Key_Manager();
        $manager->key_init($this->keyPath(), $this->keyPassword());

        return $manager;
    }

    /**
     * 모바일 본인확인 요청 JSON을 만든다.
     *
     * @return array<string, mixed>
     */
    public function makeRequestPayload(object $manager, string $clientTxId, string $resultReturnUrl): array
    {
        $encryptReqClientInfo = $manager->rsa_encrypt($this->makeRequestClientInfo($clientTxId));

        return [
            'usageCode' => $this->usageCode(),
            'serviceId' => $this->serviceId(),
            'encryptReqClientInfo' => $encryptReqClientInfo,
            'serviceType' => $this->serviceType(),
            'retTransferType' => $this->retTransferType(),
            'returnUrl' => $resultReturnUrl,
        ];
    }
}
