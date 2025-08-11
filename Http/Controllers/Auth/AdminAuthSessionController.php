<?php

namespace Jiny\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;
use Jiny\Admin\App\Models\AdminUserPasswordError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 관리자 인증 세션 컨트롤러
 * 
 * 체인 형태의 로그인 검사 시스템을 구현하여 보안성을 강화합니다.
 * 각 검사 단계는 독립적인 private 함수로 분리되어 있어 유지보수가 용이합니다.
 */
class AdminAuthSessionController extends Controller
{
    /**
     * 로그인 처리 메인 메서드
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginAjax(Request $request)
    {
        return $this->processLoginChain($request, true);
    }

    /**
     * 체인 형태의 로그인 처리 시스템
     * 
     * 각 검사 단계를 순차적으로 실행하여 모든 검사를 통과해야 로그인이 성공합니다.
     * 
     * @param Request $request
     * @param bool $isAjax
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
     * @param Request $request
     * @return array
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
     * @param Request $request
     * @return array
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
     * @param string $email
     * @return array
     */
    private function checkLoginAttempts(string $email): array
    {
        $maxAttempts = config('admin.settings.login.security.max_attempts', 5);
        $lockoutTime = config('admin.settings.login.security.lockout_time', 1800);
        $maxAttemptsAdminLock = config('admin.settings.login.security.max_attempts_admin_lock', 25);
        $adminLockTime = config('admin.settings.login.security.admin_lock_time', 0);
        
        // 데이터베이스에서 최근 오류 횟수 조회 (24시간 내)
        $attempts = AdminUserPasswordError::getErrorCount($email, 24);
        
        // 25회 실패 시 관리자 해제 필요 (무제한 제한)
        if ($attempts >= $maxAttemptsAdminLock) {
            return [
                'success' => false,
                'message' => '보안상의 이유로 계정이 잠겼습니다. 관리자에게 문의하여 해제를 요청해주세요.',
                'requires_admin_unlock' => true
            ];
        }

        // 5회 실패 시 30분간 제한
        if ($attempts >= $maxAttempts) {
            // 마지막 오류 시간 확인
            $lastError = AdminUserPasswordError::where('email', $email)
                ->orderBy('error_at', 'desc')
                ->first();
            
            if ($lastError && $lastError->error_at->addSeconds($lockoutTime)->isFuture()) {
                $remainingTime = $lastError->error_at->addSeconds($lockoutTime)->diffInSeconds(now());
                $minutes = ceil($remainingTime / 60);
                
                return [
                    'success' => false,
                    'message' => "로그인 시도가 너무 많습니다. {$minutes}분 후에 다시 시도해주세요.",
                    'remaining_attempts' => $maxAttemptsAdminLock - $attempts
                ];
            }
        }

        return [
            'success' => true,
            'remaining_attempts' => $maxAttemptsAdminLock - $attempts
        ];
    }

    /**
     * 4단계: 사용자 계정 검증
     * 
     * @param string $email
     * @return array
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
     * @param array $credentials
     * @param AdminUser $admin
     * @return array
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
     * @param AdminUser $admin
     * @return array
     */
    private function validateAccountStatus(AdminUser $admin): array
    {
        // 계정 활성화 상태 확인
        if (!$admin->is_active) {
            return [
                'success' => false,
                'message' => '비활성화된 계정입니다. 관리자에게 문의하여 활성화를 요청해주세요.'
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
     * @param Request $request
     * @param AdminUser $admin
     * @param array $loginData
     * @return array
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
     * @param AdminUser $admin
     * @param bool $isAjax
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
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
     * @param Request $request
     * @param AdminUser $admin
     * @param bool $isAjax
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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

        // 기본적으로 대시보드로 리다이렉트
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => '관리자 로그인 성공',
                'redirect' => route('admin.dashboard'),
                'user' => $admin
            ]);
        }
        
        return redirect()->intended(route('admin.dashboard'))->with('success', '관리자 로그인 성공');
    }

    /**
     * AJAX 요청인지 확인
     * 
     * @param Request $request
     * @return bool
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }

    /**
     * 로그인 데이터 준비
     * 
     * @param Request $request
     * @return array
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
     * 
     * @param Request $request
     * @param AdminUser $admin
     * @return void
     */
    private function updateSessionData(Request $request, AdminUser $admin): void
    {
        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->put('admin_last_activity', now()->toDateTimeString());
            $request->session()->put('admin_user_id', $admin->id);
            $request->session()->put('admin_user_type', $admin->type);
        }
    }

    /**
     * 데이터베이스에 세션 정보 저장
     * 
     * @param Request $request
     * @param AdminUser $admin
     * @param array $loginData
     * @return void
     */
    private function saveSessionToDatabase(Request $request, AdminUser $admin, array $loginData): void
    {
        $sessionId = $request->session()->getId();
        $loginLocation = $this->getLoginLocation($loginData['ip']);
        $device = $this->getDeviceInfo($loginData['ua']);

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
                'login_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * 로그인 시도 횟수 증가
     * 
     * @param string $email
     * @return void
     */
    private function incrementLoginAttempts(string $email): void
    {
        // 디버깅을 위한 로그 추가
        Log::info('incrementLoginAttempts 호출됨', ['email' => $email]);
        
        // 데이터베이스에 오류 기록
        try {
            AdminUserPasswordError::logError(
                null, // admin_user_id는 로그인 실패 시 null
                $email,
                'password',
                '비밀번호 오류',
                request()->ip(),
                request()->header('User-Agent'),
                [
                    'browser' => $this->getBrowserInfo(request()->header('User-Agent')),
                    'platform' => $this->getPlatformInfo(request()->header('User-Agent')),
                    'device' => $this->getDeviceInfo(request()->header('User-Agent')),
                ]
            );
            Log::info('AdminUserPasswordError 로그 기록 성공', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('AdminUserPasswordError 로그 기록 실패', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 로그인 시도 횟수 초기화
     * 
     * @param string $email
     * @return void
     */
    private function clearLoginAttempts(string $email): void
    {
        // 데이터베이스에서 해당 이메일의 오류 기록 삭제
        AdminUserPasswordError::where('email', $email)->delete();
    }

    /**
     * 로그인 위치 정보 가져오기
     * 
     * @param string $ip
     * @return string|null
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
     * @param string $userAgent
     * @return string|null
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
     * @param AdminUser $admin
     * @return string
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
     * @param string|null $adminUserId
     * @param array $data
     * @param string $status
     * @param string $msg
     * @return void
     */
    private function logLoginActivity($adminUserId, array $data, string $status, string $msg): void
    {
        // 디버깅을 위한 로그 추가
        Log::info('logLoginActivity 호출됨', [
            'admin_user_id' => $adminUserId,
            'status' => $status,
            'message' => $msg
        ]);
        
        // 로그인 실패 시에도 로그 기록 (admin_user_id가 null인 경우)
        try {
            AdminUserLog::create([
                'admin_user_id' => $adminUserId ? (int) $adminUserId : 0, // null인 경우 0 사용
                'action' => 'login',
                'table_name' => 'admin_users',
                'record_id' => $adminUserId ? (int) $adminUserId : null,
                'ip_address' => $data['ip'],
                'user_agent' => $data['ua'],
                'status' => $status,
                'message' => $msg,
            ]);
            Log::info('AdminUserLog 로그 기록 성공', ['status' => $status]);
        } catch (\Exception $e) {
            Log::error('AdminUserLog 로그 기록 실패', [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 실패 응답 처리
     * 
     * @param string $message
     * @param bool $isAjax
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function handleFailure(string $message, bool $isAjax, int $statusCode = 400)
    {
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }
        
        return back()->withErrors(['email' => $message])->onlyInput('email');
    }

    /**
     * 브라우저 정보 가져오기
     * 
     * @param string $userAgent
     * @return string|null
     */
    private function getBrowserInfo(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        // 간단한 브라우저 감지
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            return 'Internet Explorer';
        }

        return 'Unknown';
    }

    /**
     * 플랫폼 정보 가져오기
     * 
     * @param string $userAgent
     * @return string|null
     */
    private function getPlatformInfo(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        // 간단한 플랫폼 감지
        if (strpos($userAgent, 'Windows') !== false) {
            return 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            return 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            return 'iOS';
        }

        return 'Unknown';
    }
}
