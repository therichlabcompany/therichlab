<?php

namespace App\Libraries;

use Google\Client;

class FirebasePush
{
    // Firebase 프로젝트 ID (JSON 파일의 project_id 값)
    protected string $projectId = 'myfc-8e7b4';

    // 서비스 계정 키 경로
    protected string $serviceAccountPath;

    public function __construct()
    {
        $this->serviceAccountPath = WRITEPATH . 'keys/firebase-key.json';
    }

    public function send(string $deviceToken, string $title, string $body, array $data = []): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $this->normalizeData($data),
                ],
            ];

            $client = \Config\Services::curlrequest();

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
                'connect_timeout' => 3,
                'timeout' => 5,
            ]);

            $status = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            $success = $status >= 200 && $status < 300;

            return [
                'success' => $success,
                'status'  => $status,
                'body'    => $body,
                'error' => $success ? null : (string) ($body['error']['message'] ?? 'Firebase 메시지 전송에 실패했습니다.'),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $this->safeErrorMessage($e->getMessage()),
            ];
        }
    }

    protected function getAccessToken(): string
    {
        if (!is_readable($this->serviceAccountPath)) {
            throw new \RuntimeException('Firebase 서비스 계정 키 파일을 찾을 수 없습니다.');
        }

        $client = new Client();
        $client->setAuthConfig($this->serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            throw new \RuntimeException($token['error_description'] ?? $token['error']);
        }

        return $token['access_token'];
    }

    protected function normalizeData(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = is_scalar($value)
                ? (string) $value
                : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $result;
    }

    protected function safeErrorMessage(string $message): string
    {
        $message = preg_replace('/(?:[A-Za-z0-9_-]{20,}:)?APA91[A-Za-z0-9_-]+/', '[redacted-token]', $message) ?? $message;

        return $message !== '' ? $message : 'Firebase 메시지 전송에 실패했습니다.';
    }
}
