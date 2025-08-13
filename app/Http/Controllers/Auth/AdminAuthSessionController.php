<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;

/**
 * 관리자 인증 세션 컨트롤러
 * 
 * 체인 형태의 로그인 검사 시스템을 구현하여 보안성을 강화합니다.
 * 각 검사 단계는 독립적인 private 함수로 분리되어 있어 유지보수가 용이합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminAuth.md
 * 
 * 테스트 파일 작성 시 참조하세요.
 * @test jiny/admin/tests/Feature/Auth/AdminAuthSessionTest.php
 * 
 * 관련 라우트 정보:
 * @route jiny/admin/routes/web.php - admin.login.store, admin.login.ajax
 */
class AdminAuthSessionController extends Controller
{
    /**
     * 뷰 경로 변수들
     */
    protected string $loginView = 'jiny-admin::auth.login';
    protected string $dashboardView = 'jiny-admin::dashboard.index';
    protected string $twoFactorChallengeView = 'jiny-admin::auth.auth_2fa_challenge';

    /**
     * 로그인 처리 메인 메서드
     * 
     * 일반 웹 요청과 AJAX 요청을 구분하여 적절한 응답을 반환합니다.
     * 체인 형태의 로그인 검사 시스템을 통해 보안성을 강화합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * 
     * @route admin.login.store (POST /admin/login)
     * @middleware web
     * 
     * 동작 과정:
     * 1. AJAX 요청 여부 확인
     * 2. 체인 형태의 로그인 검사 실행
     * 3. 성공/실패에 따른 응답 반환
     */
    public function login(Request $request)
    {
        // AJAX 요청인 경우 JSON 응답, 일반 요청인 경우 리다이렉트
        $isAjax = $this->isAjaxRequest($request);
        
        return $this->processLoginChain($request, $isAjax);
    }

    /**
     * AJAX 로그인 처리
     * 
     * AJAX 요청을 위한 전용 로그인 처리 메서드입니다.
     * JSON 형태의 응답을 반환하여 프론트엔드에서 처리할 수 있습니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\JsonResponse JSON 응답
     * 
     * @route admin.login.ajax (POST /admin/login/ajax)
     * @middleware web
     * 
     * 동작 과정:
     * 1. AJAX 요청으로 간주
     * 2. 체인 형태의 로그인 검사 실행
     * 3. JSON 형태의 응답 반환
     */
    public function loginAjax(Request $request)
    {
        return $this->processLoginChain($request, true);
    }

    /**
     * 체인 형태의 로그인 처리 시스템
     * 
     * 각 검사 단계를 순차적으로 실행하여 모든 검사를 통과해야 로그인이 성공합니다.
     * 보안성을 강화하기 위해 단계별 검증을 수행합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @param bool $isAjax AJAX 요청 여부
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * 
     * 동작 과정:
     * 1. 기본 입력 검증 (이메일, 비밀번호)
     * 2. 시스템 상태 검사 (점검 모드, DB 연결)
     * 3. 로그인 시도 제한 검사 (브루트 포스 방지)
     * 4. 사용자 계정 검증 (존재 여부)
     * 5. 비밀번호 검증 (해시 비교)
     * 6. 계정 상태 검증 (활성화, 이메일 인증)
     * 7. 로그인 성공 처리 (세션, 로그)
     * 8. 2FA 검증 (필요시)
     * 9. 최종 리다이렉트 처리
     */
    private function processLoginChain(Request $request, bool $isAjax)
    {
        // 1단계: 기본 입력 검증
        $validationResult = $this->validateLoginInput($request);
        if (!$validationResult['success']) {
            return $this->handleFailure($validationResult['message'], $isAjax, 422);
        }

        $credentials = $validationResult['credentials'];
        $loginData = $this->prepareLoginData($request);

        // 2단계: 시스템 상태 검사
        $systemCheckResult = $this->checkSystemStatus($request);
        if (!$systemCheckResult['success']) {
            return $this->handleFailure($systemCheckResult['message'], $isAjax, 503);
        }

        // 3단계: 로그인 시도 제한 검사
        $attemptCheckResult = $this->checkLoginAttempts($credentials['email']);
        if (!$attemptCheckResult['success']) {
            $this->logLoginActivity(null, $loginData, 'fail', $attemptCheckResult['message']);
            return $this->handleFailure($attemptCheckResult['message'], $isAjax, 429);
        }

        // 4단계: 사용자 계정 검증
        $userCheckResult = $this->validateUserAccount($credentials['email']);
        if (!$userCheckResult['success']) {
            $this->logLoginActivity(null, $loginData, 'fail', $userCheckResult['message']);
            return $this->handleFailure($userCheckResult['message'], $isAjax, 401);
        }

        $admin = $userCheckResult['admin'];

        // 5단계: 비밀번호 검증
        $passwordCheckResult = $this->validatePassword($credentials, $admin);
        if (!$passwordCheckResult['success']) {
            $this->incrementLoginAttempts($credentials['email']);
            $this->logLoginActivity(null, $loginData, 'fail', $passwordCheckResult['message']);
            return $this->handleFailure($passwordCheckResult['message'], $isAjax, 401);
        }

        // 6단계: 계정 상태 검증
        $accountStatusResult = $this->validateAccountStatus($admin);
        if (!$accountStatusResult['success']) {
            $this->incrementLoginAttempts($credentials['email']);
            $this->logLoginActivity(null, $loginData, 'fail', $accountStatusResult['message']);
            return $this->handleFailure($accountStatusResult['message'], $isAjax, 403);
        }

        // 7단계: 로그인 성공 처리
        $loginSuccessResult = $this->processSuccessfulLogin($request, $admin, $loginData);
        if (!$loginSuccessResult['success']) {
            return $this->handleFailure($loginSuccessResult['message'], $isAjax, 500);
        }

        // 8단계: 2FA 검증
        $twoFactorResult = $this->handleTwoFactorAuth($admin, $isAjax);
        if ($twoFactorResult) {
            return $twoFactorResult;
        }

        // 9단계: 최종 리다이렉트 처리
        return $this->handleLoginRedirect($request, $admin, $isAjax);
    }

    /**
     * 1단계: 기본 입력 검증
     * 
     * 로그인 폼에서 전송된 이메일과 비밀번호의 기본적인 유효성을 검사합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return array 검증 결과 (success: bool, credentials: array|message: string)
     * 
     * 동작 과정:
     * 1. 이메일과 비밀번호 필수 여부 확인
     * 2. 이메일 형식 검증
     * 3. 비밀번호 최소 길이 검증
     */
    private function validateLoginInput(Request $request): array
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string', 'min:1'],
            ]);

            return [
                'success' => true,
                'credentials' => $credentials
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'message' => '입력 정보가 올바르지 않습니다.'
            ];
        }
    }

    /**
     * 2단계: 시스템 상태 검사
     * 
     * 시스템 점검 모드와 데이터베이스 연결 상태를 확인합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return array 검사 결과 (success: bool, message: string)
     * 
     * 동작 과정:
     * 1. 시스템 점검 모드 확인
     * 2. 데이터베이스 연결 상태 확인
     * 3. 오류 발생 시 로그 기록
     */
    private function checkSystemStatus(Request $request): array
    {
        // 시스템 점검 모드 확인
        if (config('admin.settings.system.maintenance.enabled', false)) {
            return [
                'success' => false,
                'message' => config('admin.settings.system.maintenance.message', '시스템 점검 중입니다.')
            ];
        }

        // 데이터베이스 연결 확인
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            Log::error('관리자 로그인 중 데이터베이스 연결 실패', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            return [
                'success' => false,
                'message' => '시스템 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
            ];
        }

        return ['success' => true];
    }

    /**
     * 3단계: 로그인 시도 제한 검사
     * 
     * 브루트 포스 공격을 방지하기 위해 로그인 시도 횟수를 제한합니다.
     * 
     * @param string $email 사용자 이메일
     * @return array 검사 결과 (success: bool, message: string)
     * 
     * 동작 과정:
     * 1. 캐시에서 로그인 시도 횟수 조회
     * 2. 최대 시도 횟수 초과 시 잠금 시간 확인
     * 3. 잠금 시간이 지났으면 카운터 초기화
     */
    private function checkLoginAttempts(string $email): array
    {
        $maxAttempts = config('admin.settings.auth.login.max_attempts', 5);
        $lockoutTime = config('admin.settings.auth.login.lockout_time', 300);
        
        $cacheKey = "admin_login_attempts:{$email}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            $remainingTime = Cache::get("admin_login_lockout:{$email}", 0) - time();
            
            if ($remainingTime > 0) {
                $minutes = ceil($remainingTime / 60);
                return [
                    'success' => false,
                    'message' => "로그인 시도가 너무 많습니다. {$minutes}분 후에 다시 시도해주세요."
                ];
            } else {
                // 잠금 시간이 지났으면 카운터 초기화
                Cache::forget($cacheKey);
                Cache::forget("admin_login_lockout:{$email}");
            }
        }

        return ['success' => true];
    }

    /**
     * 4단계: 사용자 계정 검증
     * 
     * 데이터베이스에서 해당 이메일의 관리자 계정이 존재하는지 확인합니다.
     * 
     * @param string $email 사용자 이메일
     * @return array 검증 결과 (success: bool, admin: AdminUser|message: string)
     * 
     * 동작 과정:
     * 1. 이메일로 관리자 계정 조회
     * 2. 계정 존재 여부 확인
     */
    private function validateUserAccount(string $email): array
    {
        $admin = AdminUser::where('email', $email)->first();

        if (!$admin) {
            return [
                'success' => false,
                'message' => '등록되지 않은 관리자 계정입니다.'
            ];
        }

        return [
            'success' => true,
            'admin' => $admin
        ];
    }

    /**
     * 5단계: 비밀번호 검증
     * 
     * 입력된 비밀번호와 저장된 해시를 비교하여 인증을 수행합니다.
     * 
     * @param array $credentials 인증 정보 (email, password)
     * @param AdminUser $admin 관리자 사용자 객체
     * @return array 검증 결과 (success: bool, message: string)
     * 
     * 동작 과정:
     * 1. Laravel Auth 가드를 사용한 인증 시도
     * 2. 실패 시 해시 직접 검증 (추가 보안)
     * 3. 세션 문제 등 기타 오류 처리
     */
    private function validatePassword(array $credentials, AdminUser $admin): array
    {
        // admin 가드를 사용한 인증 시도
        if (Auth::guard('admin')->attempt($credentials)) {
            return ['success' => true];
        }

        // 비밀번호 해시 직접 검증 (추가 보안)
        if (!Hash::check($credentials['password'], $admin->password)) {
            return [
                'success' => false,
                'message' => '비밀번호가 일치하지 않습니다.'
            ];
        }

        // 해시는 맞지만 가드 인증이 실패한 경우 (세션 문제 등)
        return [
            'success' => false,
            'message' => '인증에 실패했습니다. 다시 시도해주세요.'
        ];
    }

    /**
     * 6단계: 계정 상태 검증
     * 
     * 관리자 계정의 활성화 상태와 이메일 인증 상태를 확인합니다.
     * 
     * @param AdminUser $admin 관리자 사용자 객체
     * @return array 검증 결과 (success: bool, message: string)
     * 
     * 동작 과정:
     * 1. 계정 상태 확인 (active, inactive, suspended, pending)
     * 2. 이메일 인증 필요 여부 확인
     * 3. 상태별 적절한 오류 메시지 반환
     */
    private function validateAccountStatus(AdminUser $admin): array
    {
        // 계정 상태 확인
        if ($admin->status !== 'active') {
            $statusMessages = [
                'inactive' => '비활성화된 계정입니다.',
                'suspended' => '정지된 계정입니다.',
                'pending' => '승인 대기 중인 계정입니다.'
            ];

            return [
                'success' => false,
                'message' => $statusMessages[$admin->status] ?? '사용할 수 없는 계정입니다.'
            ];
        }

        // 이메일 인증 확인 (필요한 경우)
        if (config('admin.settings.auth.regist.email_verification', true)) {
            if (!$admin->is_verified) {
                return [
                    'success' => false,
                    'message' => '이메일 인증이 필요합니다.'
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * 7단계: 로그인 성공 처리
     * 
     * 로그인 성공 시 세션 정보 업데이트, 로그 기록, 데이터베이스 저장을 수행합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @param AdminUser $admin 관리자 사용자 객체
     * @param array $loginData 로그인 관련 데이터
     * @return array 처리 결과 (success: bool, message: string)
     * 
     * 동작 과정:
     * 1. 로그인 시도 카운터 초기화
     * 2. 관리자 정보 업데이트 (마지막 로그인 시간, 로그인 횟수)
     * 3. 세션 데이터 업데이트
     * 4. 데이터베이스에 세션 정보 저장
     * 5. 로그인 활동 기록
     */
    private function processSuccessfulLogin(Request $request, AdminUser $admin, array $loginData): array
    {
        try {
            // 로그인 시도 카운터 초기화
            $this->clearLoginAttempts($admin->email);

            // 관리자 정보 업데이트
            $admin->last_login_at = now();
            $admin->login_count = ($admin->login_count ?? 0) + 1;
            $admin->save();

            // 세션 데이터 업데이트
            $this->updateSessionData($request, $admin);

            // 데이터베이스에 세션 정보 저장
            $this->saveSessionToDatabase($request, $admin, $loginData);

            // 로그인 활동 기록
            $this->logLoginActivity($admin->id, $loginData, 'success', '로그인 성공');

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('관리자 로그인 성공 처리 중 오류', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '로그인 처리 중 오류가 발생했습니다.'
            ];
        }
    }

    /**
     * 8단계: 2FA 인증 처리
     * 
     * 2FA가 활성화된 계정의 경우 적절한 페이지로 리다이렉트합니다.
     * 
     * @param AdminUser $admin 관리자 사용자 객체
     * @param bool $isAjax AJAX 요청 여부
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     * 
     * 동작 과정:
     * 1. 2FA 활성화 여부 확인
     * 2. 2FA 설정 필요 여부 확인
     * 3. 적절한 페이지로 리다이렉트
     */
    private function handleTwoFactorAuth(AdminUser $admin, bool $isAjax)
    {
        // 2FA가 활성화된 경우 challenge 페이지로 리다이렉트
        if ($admin->has2FAEnabled()) {
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => '2FA 인증이 필요합니다.',
                    'redirect' => route('admin.2fa.challenge'),
                    'user' => $admin
                ]);
            }
            return redirect()->route('admin.2fa.challenge');
        }

        // 2FA 설정이 필요한 경우 관리자별 2FA 설정 페이지로 리다이렉트
        if ($admin->needs2FASetup()) {
            $message = $this->getTwoFactorSetupMessage($admin);
            
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.admin.users.2fa.setup', $admin->id),
                    'user' => $admin
                ]);
            }
            
            return redirect()->route('admin.admin.users.2fa.setup', $admin->id)
                ->with('warning', $message);
        }

        return null;
    }

    /**
     * 9단계: 최종 리다이렉트 처리
     * 
     * 로그인 성공 후 적절한 페이지로 리다이렉트합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @param AdminUser $admin 관리자 사용자 객체
     * @param bool $isAjax AJAX 요청 여부
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * 
     * 동작 과정:
     * 1. 세션에 저장된 intended_url 확인
     * 2. intended_url이 있으면 해당 페이지로 리다이렉트
     * 3. 없으면 기본 /admin 페이지로 리다이렉트
     */
    private function handleLoginRedirect(Request $request, AdminUser $admin, bool $isAjax)
    {
        // 세션에 저장된 intended_url이 있으면 해당 페이지로 리다이렉트
        $intendedUrl = $request->session()->get('intended_url');
        if ($intendedUrl) {
            $request->session()->forget('intended_url');
            
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => '관리자 로그인 성공',
                    'redirect' => $intendedUrl,
                    'user' => $admin
                ]);
            }
            
            return redirect($intendedUrl)->with('success', '관리자 로그인 성공');
        }

        // 기본적으로 /admin으로 리다이렉트
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => '관리자 로그인 성공',
                'redirect' => '/admin',
                'user' => $admin
            ]);
        }
        
        return redirect()->intended('/admin')->with('success', '관리자 로그인 성공');
    }

    /**
     * AJAX 요청인지 확인
     * 
     * @param Request $request HTTP 요청 객체
     * @return bool AJAX 요청 여부
     * 
     * 동작 과정:
     * 1. expectsJson() 메서드로 JSON 요청 확인
     * 2. Accept 헤더로 JSON 확인
     * 3. X-Requested-With 헤더로 AJAX 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || 
               $request->header('Accept') === 'application/json' ||
               $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * 로그인 데이터 준비
     * 
     * @param Request $request HTTP 요청 객체
     * @return array 로그인 관련 데이터 (IP, User-Agent, 타임스탬프)
     * 
     * 동작 과정:
     * 1. 클라이언트 IP 주소 추출
     * 2. User-Agent 헤더 추출
     * 3. 현재 시간 기록
     */
    private function prepareLoginData(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * 세션 데이터 업데이트
     * Laravel의 기본 Auth 시스템을 사용하여 admin 가드로 로그인
     * 
     * @param Request $request HTTP 요청 객체
     * @param AdminUser $admin 관리자 사용자 객체
     * @return void
     * 
     * 동작 과정:
     * 1. 세션 재생성 (보안 강화)
     * 2. admin 가드로 로그인
     * 3. 추가 세션 데이터 저장
     * 4. 로그 기록
     */
    private function updateSessionData(Request $request, AdminUser $admin): void
    {
        if ($request->hasSession()) {
            \Log::info('Starting admin session update', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'session_id' => $request->session()->getId()
            ]);
            
            // 세션 재생성 (보안 강화)
            $request->session()->regenerate();
            
            \Log::info('Session regenerated', [
                'new_session_id' => $request->session()->getId()
            ]);
            
            // Laravel의 기본 Auth 시스템을 사용하여 admin 가드로 로그인
            $adminGuard = Auth::guard('admin');
            $adminGuard->login($admin);
            
            \Log::info('Admin guard login completed', [
                'admin_id' => $admin->id,
                'guard_check' => $adminGuard->check(),
                'guard_user_id' => $adminGuard->id()
            ]);
            
            // 추가 세션 데이터 저장
            $request->session()->put('admin_last_activity', now()->toDateTimeString());
            $request->session()->put('admin_user_id', $admin->id);
            $request->session()->put('admin_user_type', $admin->type);
            
            \Log::info('Admin user logged in via Auth guard', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'guard' => 'admin',
                'session_data' => [
                    'admin_user_id' => $request->session()->get('admin_user_id'),
                    'admin_user_type' => $request->session()->get('admin_user_type'),
                    'admin_last_activity' => $request->session()->get('admin_last_activity')
                ]
            ]);
        } else {
            \Log::warning('No session available for admin login');
        }
    }

    /**
     * 데이터베이스에 세션 정보 저장
     * 
     * @param Request $request HTTP 요청 객체
     * @param AdminUser $admin 관리자 사용자 객체
     * @param array $loginData 로그인 관련 데이터
     * @return void
     * 
     * 동작 과정:
     * 1. 기존 활성 세션 비활성화 (중복 방지)
     * 2. 만료된 세션 정리
     * 3. 새로운 세션 정보 저장
     */
    private function saveSessionToDatabase(Request $request, AdminUser $admin, array $loginData): void
    {
        $sessionId = $request->session()->getId();
        $loginLocation = $this->getLoginLocation($loginData['ip']);
        $device = $this->getDeviceInfo($loginData['ua']);
        $now = now();

        // 1. 해당 관리자의 기존 활성 세션들을 비활성화 (중복 방지)
        DB::table('admin_sessions')
            ->where('admin_user_id', $admin->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'updated_at' => $now
            ]);

        // 2. 만료된 세션들을 정리 (선택적)
        $this->cleanupExpiredSessions();

        // 3. 새로운 세션 정보 저장
        DB::table('admin_sessions')->updateOrInsert(
            ['session_id' => $sessionId],
            [
                'admin_user_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_email' => $admin->email,
                'admin_type' => $admin->type,
                'ip_address' => $loginData['ip'],
                'user_agent' => $loginData['ua'],
                'login_location' => $loginLocation,
                'device' => $device,
                'login_at' => $now,
                'last_activity' => $now,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * 만료된 세션들을 정리
     * 
     * @return void
     * 
     * 동작 과정:
     * 1. 세션 수명 설정값 조회
     * 2. 만료 시간 계산
     * 3. 만료된 세션 비활성화
     */
    private function cleanupExpiredSessions(): void
    {
        $sessionLifetime = config('session.lifetime', 120); // 분 단위
        $expiryTime = now()->subMinutes($sessionLifetime);

        // 만료된 세션들을 비활성화
        DB::table('admin_sessions')
            ->where('last_activity', '<', $expiryTime)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'updated_at' => now()
            ]);
    }

    /**
     * 로그인 시도 횟수 증가
     * 
     * @param string $email 사용자 이메일
     * @return void
     * 
     * 동작 과정:
     * 1. 캐시에서 현재 시도 횟수 조회
     * 2. 최대 시도 횟수 도달 시 잠금 설정
     * 3. 시도 횟수 증가 및 캐시 저장
     */
    private function incrementLoginAttempts(string $email): void
    {
        $cacheKey = "admin_login_attempts:{$email}";
        $attempts = Cache::get($cacheKey, 0);
        $maxAttempts = config('admin.settings.auth.login.max_attempts', 5);
        $lockoutTime = config('admin.settings.auth.login.lockout_time', 300);

        if ($attempts >= $maxAttempts - 1) {
            // 잠금 설정
            Cache::put("admin_login_lockout:{$email}", time() + $lockoutTime, $lockoutTime);
        }

        Cache::put($cacheKey, $attempts + 1, $lockoutTime);
    }

    /**
     * 로그인 시도 횟수 초기화
     * 
     * @param string $email 사용자 이메일
     * @return void
     * 
     * 동작 과정:
     * 1. 로그인 시도 횟수 캐시 삭제
     * 2. 잠금 상태 캐시 삭제
     */
    private function clearLoginAttempts(string $email): void
    {
        Cache::forget("admin_login_attempts:{$email}");
        Cache::forget("admin_login_lockout:{$email}");
    }

    /**
     * 로그인 위치 정보 가져오기
     * 
     * @param string $ip IP 주소
     * @return string|null 위치 정보 (도시, 국가)
     * 
     * 동작 과정:
     * 1. Location 클래스 존재 여부 확인
     * 2. IP 주소로 위치 정보 조회
     * 3. 도시와 국가 정보 조합
     */
    private function getLoginLocation(string $ip): ?string
    {
        if (!class_exists('Location')) {
            return null;
        }

        $location = \Location::get($ip);
        if (!$location) {
            return null;
        }

        return ($location->cityName ? $location->cityName.', ' : '') . $location->countryName;
    }

    /**
     * 디바이스 정보 가져오기
     * 
     * @param string $userAgent User-Agent 문자열
     * @return string|null 디바이스 정보 (디바이스/플랫폼/브라우저)
     * 
     * 동작 과정:
     * 1. Jenssegers\Agent 클래스 존재 여부 확인
     * 2. User-Agent 파싱
     * 3. 디바이스, 플랫폼, 브라우저 정보 조합
     */
    private function getDeviceInfo(string $userAgent): ?string
    {
        if (!class_exists('Jenssegers\\Agent\\Agent')) {
            return null;
        }

        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($userAgent);
        
        return $agent->device() . ' / ' . $agent->platform() . ' / ' . $agent->browser();
    }

    /**
     * 2FA 설정 메시지 생성
     * 
     * @param AdminUser $admin 관리자 사용자 객체
     * @return string 2FA 설정 안내 메시지
     * 
     * 동작 과정:
     * 1. 기본 2FA 설정 안내 메시지
     * 2. 최초 super 관리자인 경우 특별한 안내
     */
    private function getTwoFactorSetupMessage(AdminUser $admin): string
    {
        $message = '보안을 위해 2FA 설정이 필요합니다.';
        
        // 최초 super 관리자인 경우 특별한 안내
        if ($admin->type === 'super' && $admin->login_count <= 1) {
            $message = '최초 super 관리자 계정입니다. 보안을 위해 2FA 설정을 완료해주세요.';
        }
        
        return $message;
    }

    /**
     * 로그인/로그아웃 기록 저장
     * 
     * @param string|null $adminUserId 관리자 사용자 ID
     * @param array $data 로그인 관련 데이터
     * @param string $status 상태 (success/fail)
     * @param string $msg 메시지
     * @return void
     * 
     * 동작 과정:
     * 1. 관리자 ID가 있는 경우에만 로그 생성
     * 2. AdminUserLog 테이블에 로그인 활동 기록
     */
    private function logLoginActivity($adminUserId, array $data, string $status, string $msg): void
    {
        if ($adminUserId) {
            AdminUserLog::create([
                'admin_user_id' => $adminUserId,
                'action' => 'login',
                'ip_address' => $data['ip'],
                'user_agent' => $data['ua'],
                'status' => $status,
                'message' => $msg,
            ]);
        }
    }

    /**
     * 실패 응답 처리
     * 
     * @param string $message 오류 메시지
     * @param bool $isAjax AJAX 요청 여부
     * @param int $statusCode HTTP 상태 코드
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * 
     * 동작 과정:
     * 1. AJAX 요청인 경우 JSON 응답 반환
     * 2. 일반 요청인 경우 백 페이지로 리다이렉트
     * 3. 오류 메시지를 세션에 저장
     */
    private function handleFailure(string $message, bool $isAjax, int $statusCode = 400)
    {
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => $this->getErrorCode($message)
            ], $statusCode);
        }
        
        return back()->withErrors(['email' => $message])->onlyInput('email');
    }

    /**
     * 오류 메시지에 따른 오류 코드 반환
     * 
     * @param string $message 오류 메시지
     * @return string 오류 코드
     * 
     * 동작 과정:
     * 1. 메시지 내용에 따른 오류 코드 분류
     * 2. 프론트엔드에서 처리할 수 있는 표준화된 오류 코드 반환
     */
    private function getErrorCode(string $message): string
    {
        if (str_contains($message, '비밀번호')) {
            return 'PASSWORD_MISMATCH';
        } elseif (str_contains($message, '등록되지 않은')) {
            return 'USER_NOT_FOUND';
        } elseif (str_contains($message, '시도가 너무 많습니다')) {
            return 'TOO_MANY_ATTEMPTS';
        } elseif (str_contains($message, '비활성화')) {
            return 'ACCOUNT_INACTIVE';
        } elseif (str_contains($message, '정지된')) {
            return 'ACCOUNT_SUSPENDED';
        } elseif (str_contains($message, '승인 대기')) {
            return 'ACCOUNT_PENDING';
        } elseif (str_contains($message, '이메일 인증')) {
            return 'EMAIL_NOT_VERIFIED';
        } elseif (str_contains($message, '시스템')) {
            return 'SYSTEM_ERROR';
        } else {
            return 'LOGIN_FAILED';
        }
    }
}
