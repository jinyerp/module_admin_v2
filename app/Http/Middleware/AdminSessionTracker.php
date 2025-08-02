<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Added DB facade

class AdminSessionTracker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 관리자 인증이 확인된 경우에만 세션 활동 추적
        if (Auth::guard('admin')->check()) {
            $this->updateSessionActivity($request);
        }

        $response = $next($request);

        // 세션 만료 감지
        if ($this->shouldCheckSessionExpiry($request)) {
            $expiryResponse = $this->checkAndHandleSessionExpiry($request);
            if ($expiryResponse) {
                return $expiryResponse;
            }
        }

        return $response;
    }

    /**
     * 세션 활동 시간 업데이트
     */
    private function updateSessionActivity(Request $request): void
    {
        $currentTime = now();
        $request->session()->put('admin_last_activity', $currentTime->toDateTimeString());
        $request->session()->put('admin_user_id', Auth::guard('admin')->id());
        
        // admin_sessions 테이블의 last_activity도 업데이트
        $sessionId = $request->session()->getId();
        if ($sessionId) {
            try {
                // 기존 last_activity 확인
                $existingSession = DB::table('admin_sessions')
                    ->where('session_id', $sessionId)
                    ->first();
                
                if ($existingSession) {
                    $existingTime = \Carbon\Carbon::parse($existingSession->last_activity);
                    
                    // 기존 시간이 현재 시간보다 미래인 경우 현재 시간으로 설정
                    if ($existingTime->isAfter($currentTime)) {
                        $currentTime = now(); // 다시 현재 시간 가져오기
                    }
                }
                
                DB::table('admin_sessions')
                    ->where('session_id', $sessionId)
                    ->update([
                        'last_activity' => $currentTime,
                        'updated_at' => $currentTime
                    ]);
            } catch (\Exception $e) {
                \Log::warning('세션 활동 시간 업데이트 실패', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * 세션 만료 체크가 필요한지 확인
     */
    private function shouldCheckSessionExpiry(Request $request): bool
    {
        return $request->hasSession() && Auth::guard('admin')->check();
    }

    /**
     * 세션 만료를 체크하고 처리
     */
    private function checkAndHandleSessionExpiry(Request $request): ?Response
    {
        $lastActivity = $request->session()->get('admin_last_activity');

        if (!$lastActivity) {
            return null;
        }

        if ($this->isSessionExpired($lastActivity)) {
            return $this->handleSessionExpiry($request);
        }

        return null;
    }

    /**
     * 세션이 만료되었는지 확인
     */
    private function isSessionExpired(string $lastActivity): bool
    {
        $sessionLifetime = config('session.lifetime', 120); // 분 단위
        $expiryTime = Carbon::parse($lastActivity)->addMinutes($sessionLifetime);

        return now()->isAfter($expiryTime);
    }

    /**
     * 세션 만료 처리
     */
    private function handleSessionExpiry(Request $request): Response
    {
        // 현재 URL을 세션에 저장 (로그인 후 돌아가기 위함)
        $currentUrl = $request->fullUrl();
        $sessionId = $request->session()->getId();

        // 세션 만료 처리
        $this->expireSession($request, $currentUrl, $sessionId);

        // AJAX 요청인 경우 JSON 응답
        if ($this->isAjaxRequest($request)) {
            return $this->createSessionExpiredAjaxResponse($currentUrl);
        }

        return $this->createSessionExpiredRedirectResponse($currentUrl);
    }

    /**
     * 세션 만료 처리 (로그아웃 및 세션 정리)
     */
    private function expireSession(Request $request, string $currentUrl, string $sessionId): void
    {
        // admin_sessions 테이블에서 해당 세션을 비활성화
        try {
            DB::table('admin_sessions')
                ->where('session_id', $sessionId)
                ->update([
                    'is_active' => false,
                    'updated_at' => now()
                ]);
        } catch (\Exception $e) {
            \Log::warning('세션 만료 시 admin_sessions 정리 실패', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('admin_session_expired', true);
        $request->session()->put('intended_url', $currentUrl);
    }

    /**
     * AJAX 요청인지 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }

    /**
     * 세션 만료 AJAX 응답 생성
     */
    private function createSessionExpiredAjaxResponse(string $currentUrl): Response
    {
        return response()->json([
            'success' => false,
            'message' => '세션이 만료되었습니다. 다시 로그인해주세요.',
            'redirect' => route('admin.login'),
            'session_expired' => true,
            'intended_url' => $currentUrl
        ], 401);
    }

    /**
     * 세션 만료 리다이렉트 응답 생성
     */
    private function createSessionExpiredRedirectResponse(string $currentUrl): Response
    {
        return redirect()->route('admin.login')
            ->with('error', '세션이 만료되었습니다. 다시 로그인해주세요.')
            ->with('intended_url', $currentUrl);
    }
} 