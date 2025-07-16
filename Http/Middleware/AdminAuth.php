<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // admin 가드를 사용하여 인증 확인
        if (!Auth::guard('admin')->check()) {
            // 인증되지 않은 경우 로그인 페이지로 리다이렉트
            return redirect()->route('admin.login')
                ->with('error', '관리자 로그인이 필요합니다.');
        }

        // 인증된 관리자 정보를 뷰에서 사용할 수 있도록 공유
        $adminUser = Auth::guard('admin')->user();
        view()->share('adminUser', $adminUser);

        return $next($request);
    }
}
