<?php

namespace Jiny\Admin\App\Http\Middleware;

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
        \Log::info('AdminAuth middleware is running', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'session_data' => $request->session()->all()
        ]);
        
        // admin 가드를 사용하여 인증 확인
        $adminGuard = Auth::guard('admin');
        $isAuthenticated = $adminGuard->check();
        
        \Log::info('Admin authentication check', [
            'is_authenticated' => $isAuthenticated,
            'guard_name' => 'admin',
            'user_id' => $isAuthenticated ? $adminGuard->id() : null,
            'user' => $isAuthenticated ? $adminGuard->user() : null,
            'session_user_id' => $request->session()->get('admin_user_id'),
            'session_last_activity' => $request->session()->get('admin_last_activity')
        ]);
        
        if (!$isAuthenticated) {
            \Log::info('Admin not authenticated, redirecting to login');
            return $this->handleUnauthenticated($request);
        }

        \Log::info('Admin is authenticated, proceeding', [
            'admin_id' => $adminGuard->id(),
            'admin_email' => $adminGuard->user()->email ?? 'unknown'
        ]);
        
        // 인증된 관리자 정보를 뷰에서 사용할 수 있도록 공유
        $this->shareAdminUserToView();

        return $next($request);
    }

    /**
     * 인증되지 않은 요청 처리
     */
    private function handleUnauthenticated(Request $request): Response
    {
        // 현재 URL을 세션에 저장 (로그인 후 돌아가기 위함)
        $currentUrl = $request->fullUrl();
        
        // AJAX 요청인 경우 JSON 응답
        if ($this->isAjaxRequest($request)) {
            return $this->createAjaxResponse($currentUrl);
        }
        
        // 일반 요청인 경우 로그인 페이지로 리다이렉트
        return $this->createRedirectResponse($currentUrl);
    }

    /**
     * AJAX 요청인지 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }

    /**
     * AJAX 응답 생성
     */
    private function createAjaxResponse(string $currentUrl): Response
    {
        return response()->json([
            'success' => false,
            'message' => '관리자 로그인이 필요합니다.',
            'redirect' => route('admin.login'),
            'requires_auth' => true,
            'intended_url' => $currentUrl
        ], 401);
    }

    /**
     * 리다이렉트 응답 생성
     */
    private function createRedirectResponse(string $currentUrl): Response
    {
        return redirect()->route('admin.login')
            ->with('error', '관리자 로그인이 필요합니다.')
            ->with('intended_url', $currentUrl);
    }

    /**
     * 인증된 관리자 정보를 뷰에 공유
     */
    private function shareAdminUserToView(): void
    {
        $adminUser = Auth::guard('admin')->user();
        view()->share('adminUser', $adminUser);
    }
}
