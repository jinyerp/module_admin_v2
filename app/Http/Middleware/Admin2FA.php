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
        \Log::info('Admin2FA middleware is running', [
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);
        
        // admin 가드로 인증된 사용자 확인
        $adminGuard = Auth::guard('admin');
        if (!$adminGuard->check()) {
            \Log::info('Admin not authenticated in 2FA middleware');
            
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => '관리자 로그인이 필요합니다.',
                    'redirect' => route('admin.login')
                ], 401);
            }
            
            return redirect()->route('admin.login')
                ->with('error', '관리자 로그인이 필요합니다.');
        }

        $user = $adminGuard->user();
        \Log::info('Admin 2FA check', [
            'admin_id' => $user->id,
            'has_2fa_enabled' => $user->has2FAEnabled(),
            '2fa_verified' => session('2fa_verified')
        ]);

        // 2FA가 활성화되어 있지 않으면 통과
        if (!$user->has2FAEnabled()) {
            \Log::info('2FA not enabled, proceeding');
            return $next($request);
        }

        // 2FA 인증이 완료되었는지 확인
        if (session('2fa_verified')) {
            \Log::info('2FA already verified, proceeding');
            return $next($request);
        }

        \Log::info('2FA verification required, redirecting to challenge');
        
        // 2FA 인증이 필요한 경우 challenge 페이지로 리다이렉트
        if ($this->isAjaxRequest($request)) {
            return response()->json([
                'success' => false,
                'message' => '2차 인증이 필요합니다.',
                'redirect' => route('admin.2fa.challenge')
            ], 403);
        }
        
        return redirect()->route('admin.2fa.challenge')
            ->with('error', '2차 인증이 필요합니다.');
    }
    
    /**
     * AJAX 요청인지 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }
} 