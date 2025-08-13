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
 * 
 * 관리자 세션의 로그아웃 처리와 세션 관리를 담당합니다.
 * 보안을 위해 세션 정보를 데이터베이스에 기록하고 관리합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminLogout.md
 * 
 * 테스트 파일 작성 시 참조하세요.
 * @test jiny/admin/tests/Feature/Auth/AdminLogoutSessionTest.php
 * 
 * 관련 라우트 정보:
 * @route jiny/admin/routes/web.php - admin.logout, admin.session.*
 */
class AdminLogoutSessionController extends Controller
{
    /**
     * 뷰 경로 변수들
     */
    protected string $loginView = 'jiny-admin::auth.login';

    /**
     * 로그아웃 처리 (admin 가드 전용)
     * 
     * 일반 웹 요청에 대한 로그아웃을 처리합니다.
     * 세션 정보를 정리하고 로그아웃 활동을 기록합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse 로그인 페이지로 리다이렉트
     * 
     * @route admin.logout (GET /admin/logout)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 로그아웃 데이터 준비 (IP, User-Agent)
     * 2. 현재 로그인된 관리자 정보 조회
     * 3. 로그아웃 수행 (세션 정리, 가드 로그아웃)
     * 4. 로그아웃 활동 기록
     * 5. 로그인 페이지로 리다이렉트
     * 
     * 반환값: 로그인 페이지로 리다이렉트 (성공 메시지 포함)
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
     * 
     * AJAX 요청에 대한 로그아웃을 처리합니다.
     * JSON 형태의 응답을 반환하여 프론트엔드에서 처리할 수 있습니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.logout.ajax (POST /admin/logout/ajax)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 로그아웃 데이터 준비 (IP, User-Agent)
     * 2. 현재 로그인된 관리자 정보 조회
     * 3. 로그아웃 수행 (세션 정리, 가드 로그아웃)
     * 4. 로그아웃 활동 기록
     * 5. JSON 응답 반환
     * 
     * 반환값: JSON 응답 (성공 상태, 메시지, 리다이렉트 URL)
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
     * 
     * 로그아웃 시 필요한 데이터를 수집합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return array 로그아웃 관련 데이터 (IP, User-Agent)
     * 
     * 동작 과정:
     * 1. 클라이언트 IP 주소 추출
     * 2. User-Agent 헤더 추출
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
     * 
     * 실제 로그아웃 처리를 수행합니다. 세션 정보를 정리하고
     * 데이터베이스의 세션 상태를 업데이트합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return void
     * 
     * 동작 과정:
     * 1. admin 가드 로그아웃
     * 2. 세션 관련 데이터 정리
     * 3. admin_sessions 테이블에서 세션 비활성화
     * 4. 2FA 관련 세션 정리
     * 5. 세션 무효화 및 토큰 재생성
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
     * 
     * 로그아웃 활동을 AdminUserLog 테이블에 기록합니다.
     * 
     * @param string|null $adminUserId 관리자 사용자 ID
     * @param array $data 로그아웃 관련 데이터
     * @return void
     * 
     * 동작 과정:
     * 1. 관리자 ID가 있는 경우에만 로그 생성
     * 2. AdminUserLog 테이블에 로그아웃 활동 기록
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
     * 
     * super 관리자만 사용할 수 있는 기능으로,
     * 모든 활성 세션을 강제로 종료합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.session.force-logout-all (POST /admin/session/force-logout-all)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 사용자의 super 관리자 권한 확인
     * 2. 모든 활성 세션 삭제
     * 3. 결과 반환
     * 
     * 반환값: JSON 응답 (성공/실패 상태, 메시지)
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
     * 
     * super 관리자만 사용할 수 있는 기능으로,
     * 특정 관리자의 모든 활성 세션을 종료합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @param string $adminUserId 관리자 사용자 ID
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.session.force-logout-user (POST /admin/session/force-logout-user/{adminUserId})
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 사용자의 super 관리자 권한 확인
     * 2. 특정 관리자의 모든 활성 세션 삭제
     * 3. 결과 반환
     * 
     * 반환값: JSON 응답 (성공/실패 상태, 메시지)
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
     * 
     * 현재 로그인된 관리자의 세션 정보를 조회합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.session.info (GET /admin/session/info)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 로그인된 관리자 확인
     * 2. 세션 ID로 세션 정보 조회
     * 3. 관리자 정보와 세션 정보 반환
     * 
     * 반환값: JSON 응답 (세션 정보, 관리자 정보)
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
     * 
     * super 관리자만 사용할 수 있는 기능으로,
     * 현재 활성화된 모든 세션 목록을 조회합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.session.active (GET /admin/session/active)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 사용자의 super 관리자 권한 확인
     * 2. 활성 세션 목록 조회 (최근 활동 순)
     * 3. 결과 반환
     * 
     * 반환값: JSON 응답 (활성 세션 목록)
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
