<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Admin2FA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // admin 가드로 인증된 사용자 확인
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', '관리자 로그인이 필요합니다.');
        }

        $user = Auth::guard('admin')->user();

        // 2FA가 활성화되어 있지 않으면 통과
        if (!$user->has2FAEnabled()) {
            return $next($request);
        }

        // 2FA 인증이 완료되었는지 확인
        if (session('2fa_verified')) {
            return $next($request);
        }

        // 2FA 인증이 필요한 경우 challenge 페이지로 리다이렉트
        return redirect()->route('admin.2fa.challenge')
            ->with('error', '2차 인증이 필요합니다.');
    }
} 