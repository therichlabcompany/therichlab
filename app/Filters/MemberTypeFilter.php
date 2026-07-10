<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MemberTypeFilter implements FilterInterface
{
    private ?string $requiredType;

    public function __construct(?string $requiredType = null)
    {
        $this->requiredType = $requiredType;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $isLoggedIn = (bool) $session->get('logged_in');
        $memberUid = trim((string) $session->get('member_uid'));

        if (!$isLoggedIn || $memberUid === '') {
            return $this->reject($request, 401, '로그인이 필요합니다.', '/member/login');
        }

        $requiredType = $this->requiredType;
        if (!empty($arguments[0])) {
            $requiredType = strtoupper(trim((string) $arguments[0]));
        }

        if ($requiredType === null || $requiredType === '') {
            return null;
        }

        $memberType = strtoupper(trim((string) $session->get('member_type')));
        if ($memberType !== $requiredType) {
            $redirect = $memberType === 'FC' ? '/mypage/fcinfo' : '/mypage/info';
            $message = $requiredType === 'FC'
                ? 'FC 회원 전용 마이페이지입니다.'
                : '일반회원 전용 마이페이지입니다.';

            return $this->reject($request, 403, $message, $redirect);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function reject(RequestInterface $request, int $status, string $message, string $redirect)
    {
        $accept = strtolower($request->getHeaderLine('Accept'));
        $isJsonRequest = $request->isAJAX()
            || $request->getMethod() !== 'GET'
            || str_contains($accept, 'application/json');

        if ($isJsonRequest) {
            return service('response')->setStatusCode($status)->setJSON([
                'status' => 'error',
                'result' => 'fail',
                'message' => $message,
                'redirect' => base_url(ltrim($redirect, '/')),
            ]);
        }

        return redirect()->to($redirect)->with('error', $message);
    }
}
