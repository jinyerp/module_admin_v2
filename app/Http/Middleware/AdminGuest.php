<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminGuest
{
    /**
     * Handle an incoming request.
     * 로그인하지 않은 사용자만 접근 가능한 미들웨어
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('AdminGuest middleware is running', [
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);
        
        // admin guard로 인증된 세션이 있는지 확인
        $adminGuard = Auth::guard('admin');
        $isAuthenticated = $adminGuard->check();
        
        \Log::info('Admin guest check', [
            'is_authenticated' => $isAuthenticated,
            'guard_name' => 'admin'
        ]);
        
        if ($isAuthenticated) {
            \Log::info('Admin already authenticated, redirecting to dashboard');
            
            // AJAX 요청인 경우 JSON 응답
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 로그인되어 있습니다.',
                    'redirect' => route('admin.dashboard')
                ], 403);
            }
            
            // 일반 요청인 경우 대시보드로 리다이렉트
            return redirect()->route('admin.dashboard')
                ->with('info', '이미 로그인되어 있습니다.');
        }
        
        \Log::info('Admin guest access allowed');
        
        // 세션이 없으면 통과
        return $next($request);
    }
    
    /**
     * AJAX 요청인지 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }
} 