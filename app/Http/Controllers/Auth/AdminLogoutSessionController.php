<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Jiny\Admin\App\Models\AdminUserLog;

/**
 * 관리자 세션 로그아웃 컨트롤러 (admin 가드 전용)
 */
class AdminLogoutSessionController extends Controller
{
    /**
     * 로그아웃 처리 (admin 가드 전용)
     */
    public function logout(Request $request)
    {
        $logoutData = $this->prepareLogoutData($request);
        $admin = Auth::guard('admin')->user();

        $this->performLogout($request);
        $this->logLogoutActivity($admin ? $admin->id : null, $logoutData);

        return redirect()->route('admin.login')->with('success', '로그아웃되었습니다.');
    }

    /**
     * AJAX 로그아웃 처리
     */
    public function logoutAjax(Request $request)
    {
        $logoutData = $this->prepareLogoutData($request);
        $admin = Auth::guard('admin')->user();

        $this->performLogout($request);
        $this->logLogoutActivity($admin ? $admin->id : null, $logoutData);

        return response()->json([
            'success' => true,
            'message' => '로그아웃되었습니다.',
            'redirect' => route('admin.login')
        ]);
    }

    /**
     * 로그아웃 데이터 준비
     */
    private function prepareLogoutData(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ];
    }

    /**
     * 로그아웃 수행
     */
    private function performLogout(Request $request): void
    {
        $sessionId = $request->session()->getId();
        $admin = Auth::guard('admin')->user();

        // admin 가드 로그아웃
        Auth::guard('admin')->logout();

        if ($request->hasSession()) {
            // 세션 관련 데이터 정리
            $request->session()->forget([
                'admin_last_activity', 
                'admin_user_id', 
                'admin_session_expired',
                'intended_url'
            ]);
            
            // admin_sessions 테이블에서 세션 정보 비활성화 (삭제 대신)
            try {
                DB::table('admin_sessions')
                    ->where('session_id', $sessionId)
                    ->update([
                        'is_active' => false,
                        'updated_at' => now()
                    ]);
            } catch (\Exception $e) {
                // 데이터베이스 오류가 발생해도 로그아웃은 계속 진행
                \Log::warning('관리자 로그아웃 시 세션 정리 실패', [
                    'session_id' => $sessionId,
                    'admin_id' => $admin ? $admin->id : null,
                    'error' => $e->getMessage()
                ]);
            }
            
            // 2FA 관련 세션 정리
            $request->session()->forget([
                '2fa_verified', 
                '2fa_setup_secret', 
                '2fa_setup_backup_codes'
            ]);
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * 로그아웃 활동 기록
     */
    private function logLogoutActivity($adminUserId, array $data): void
    {
        if ($adminUserId) {
            AdminUserLog::create([
                'id' => (string) Str::uuid(),
                'admin_user_id' => $adminUserId,
                'ip_address' => $data['ip'],
                'user_agent' => $data['ua'],
                'status' => 'success',
                'message' => '로그아웃',
                'created_at' => now(),
            ]);
        }
    }

    /**
     * 모든 세션 강제 종료 (관리자용)
     */
    public function forceLogoutAllSessions(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || $admin->type !== 'super') {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        // 모든 활성 세션 삭제
        DB::table('admin_sessions')->where('is_active', true)->delete();

        return response()->json([
            'success' => true,
            'message' => '모든 세션이 강제 종료되었습니다.'
        ]);
    }

    /**
     * 특정 관리자의 모든 세션 종료
     */
    public function forceLogoutUserSessions(Request $request, $adminUserId)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || $admin->type !== 'super') {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        // 특정 관리자의 모든 활성 세션 삭제
        DB::table('admin_sessions')
            ->where('admin_user_id', $adminUserId)
            ->where('is_active', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => '해당 관리자의 모든 세션이 종료되었습니다.'
        ]);
    }

    /**
     * 현재 세션 정보 조회
     */
    public function getCurrentSessionInfo(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => '로그인이 필요합니다.'
            ], 401);
        }

        $sessionId = $request->session()->getId();
        $sessionInfo = DB::table('admin_sessions')
            ->where('session_id', $sessionId)
            ->first();

        return response()->json([
            'success' => true,
            'session_info' => $sessionInfo,
            'admin_user' => $admin
        ]);
    }

    /**
     * 활성 세션 목록 조회 (관리자용)
     */
    public function getActiveSessions(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin || $admin->type !== 'super') {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        $sessions = DB::table('admin_sessions')
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions
        ]);
    }
}
