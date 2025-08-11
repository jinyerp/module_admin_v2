# 인증 시스템 (Authentication)

## 📋 개요

Jiny Admin의 인증 시스템은 다중 인증 방식을 지원하며, 2FA(2단계 인증)와 세션 관리를 통해 보안을 강화합니다.

## 🔐 다중 인증 방식

### 1. Guard 기반 인증 (권장)

완전히 분리된 인증 시스템으로, 관리자와 일반 사용자의 인증을 완전히 분리합니다.

#### 설정
```env
# .env
ADMIN_USE_GUARD=true
ADMIN_GUARD_NAME=admin
```

#### 장점
- 보안 강화: 일반 사용자와 완전 분리
- 세션 분리: 동시 로그인 가능
- 독립적인 인증 로직

#### 구현
```php
// config/auth.php
'guards' => [
    'admin' => [
        'driver' => 'session',
        'provider' => 'admin_users',
    ],
],

'providers' => [
    'admin_users' => [
        'driver' => 'eloquent',
        'model' => Jiny\Admin\App\Models\AdminUser::class,
    ],
],
```

### 2. 기본 Auth 기반 인증

기존 users 테이블과 연동하여 간단하게 설정할 수 있습니다.

#### 설정
```env
# .env
ADMIN_USE_GUARD=false
```

#### 장점
- 간단한 설정
- 기존 시스템과 호환
- 빠른 구현

## 🛡️ 2FA (2단계 인증)

### 설정 방법

#### 1. 관리자별 2FA 설정
```php
// routes/admin.php
Route::prefix('{id}/2fa')->name('2fa.')->group(function () {
    Route::get('/setup', [AdminUser2FAController::class, 'setup']);
    Route::post('/enable', [AdminUser2FAController::class, 'enable']);
    Route::post('/disable', [AdminUser2FAController::class, 'disable']);
});
```

#### 2. Google Authenticator 연동
- **QR 코드 생성**: 관리자 설정 시 자동 생성
- **백업 코드**: 복구용 백업 코드 제공
- **실시간 검증**: TOTP 기반 실시간 인증

### 2FA 설정 예제

```php
// AdminUser2FAController.php
public function setup($id)
{
    $admin = AdminUser::findOrFail($id);
    
    // 2FA 시크릿 키 생성
    $secret = Google2FA::generateSecretKey();
    
    // QR 코드 생성
    $qrCodeUrl = Google2FA::getQRCodeUrl(
        config('app.name'),
        $admin->email,
        $secret
    );
    
    return view('admin.users.2fa.setup', compact('admin', 'secret', 'qrCodeUrl'));
}

public function enable(Request $request, $id)
{
    $request->validate([
        'code' => 'required|string',
        'secret' => 'required|string',
    ]);
    
    $admin = AdminUser::findOrFail($id);
    
    // 코드 검증
    if (Google2FA::verifyKey($request->secret, $request->code)) {
        $admin->update([
            'two_factor_secret' => $request->secret,
            'two_factor_enabled' => true,
        ]);
        
        return redirect()->route('admin.admin.users.show', $id)
            ->with('success', '2FA가 활성화되었습니다.');
    }
    
    return back()->withErrors(['code' => '잘못된 인증 코드입니다.']);
}
```

## 📱 세션 관리

### 세션 추적

모든 관리자 세션을 추적하고 관리합니다.

```php
// AdminSessionService.php
public function createSession($adminId)
{
    return AdminSession::create([
        'admin_user_id' => $adminId,
        'session_id' => session()->getId(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'last_activity' => now(),
        'status' => 'active',
    ]);
}

public function updateActivity($sessionId)
{
    AdminSession::where('session_id', $sessionId)
        ->update(['last_activity' => now()]);
}
```

### 세션 제어

#### 1. 세션 타임아웃
```env
# .env
ADMIN_SESSION_TIMEOUT=120
```

#### 2. 강제 로그아웃
```php
public function forceLogout($sessionId)
{
    $session = AdminSession::where('session_id', $sessionId)->first();
    
    if ($session) {
        // 세션 무효화
        Session::getHandler()->destroy($sessionId);
        
        // 세션 상태 업데이트
        $session->update(['status' => 'terminated']);
        
        // 로그 기록
        $this->logActivity('force_logout', '강제 로그아웃', $session->admin_user_id);
    }
}
```

#### 3. 동시 접속 제한
```php
public function checkConcurrentLogin($adminId, $maxSessions = 1)
{
    $activeSessions = AdminSession::where('admin_user_id', $adminId)
        ->where('status', 'active')
        ->count();
    
    if ($activeSessions >= $maxSessions) {
        // 가장 오래된 세션 종료
        $oldestSession = AdminSession::where('admin_user_id', $adminId)
            ->where('status', 'active')
            ->orderBy('last_activity')
            ->first();
            
        $this->forceLogout($oldestSession->session_id);
    }
}
```

## 🔒 보안 기능

### IP 제한

특정 IP 주소에서만 접근을 허용합니다.

```php
// AdminIpRestrictionMiddleware.php
public function handle($request, Closure $next)
{
    $allowedIps = config('admin.allowed_ips', []);
    $clientIp = request()->ip();
    
    if (!empty($allowedIps) && !in_array($clientIp, $allowedIps)) {
        abort(403, '허용되지 않은 IP 주소입니다.');
    }
    
    return $next($request);
}
```

### 로그인 시도 제한

무차별 대입 공격을 방지합니다.

```php
// AdminLoginAttemptService.php
public function checkAttempts($email, $ip)
{
    $attempts = AdminLoginAttempt::where('email', $email)
        ->where('ip_address', $ip)
        ->where('created_at', '>', now()->subMinutes(15))
        ->count();
    
    if ($attempts >= 5) {
        throw new \Exception('너무 많은 로그인 시도가 있었습니다. 15분 후에 다시 시도해주세요.');
    }
}

public function recordAttempt($email, $ip, $success = false)
{
    AdminLoginAttempt::create([
        'email' => $email,
        'ip_address' => $ip,
        'success' => $success,
        'user_agent' => request()->userAgent(),
    ]);
}
```

## 📊 인증 로그

### 로그인/로그아웃 로그

```php
// AdminUserLogService.php
public function logLogin($adminId, $status = 'success', $message = null)
{
    AdminUserLog::create([
        'admin_user_id' => $adminId,
        'action' => 'login',
        'status' => $status,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'message' => $message,
    ]);
}

public function logLogout($adminId)
{
    AdminUserLog::create([
        'admin_user_id' => $adminId,
        'action' => 'logout',
        'status' => 'success',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
}
```

### 2FA 로그

```php
public function log2FAAttempt($adminId, $status, $message = null)
{
    Admin2FALog::create([
        'admin_user_id' => $adminId,
        'status' => $status,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'message' => $message,
    ]);
}
```

## 🚀 사용 예제

### 로그인 컨트롤러

```php
// AdminAuthController.php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'remember' => 'boolean',
    ]);
    
    try {
        // 로그인 시도 제한 확인
        $this->loginAttemptService->checkAttempts($request->email, $request->ip());
        
        // 인증 시도
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');
        
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();
            
            // 세션 생성
            $this->sessionService->createSession($admin->id);
            
            // 로그인 로그
            $this->logService->logLogin($admin->id);
            
            // 2FA 확인
            if ($admin->two_factor_enabled) {
                return redirect()->route('admin.2fa.verify');
            }
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // 실패 로그
        $this->loginAttemptService->recordAttempt($request->email, $request->ip(), false);
        $this->logService->logLogin(null, 'failed', '잘못된 인증 정보');
        
        return back()->withErrors(['email' => '인증 정보가 올바르지 않습니다.']);
        
    } catch (\Exception $e) {
        return back()->withErrors(['email' => $e->getMessage()]);
    }
}
```

## ⚙️ 설정 옵션

### 환경 변수

```env
# 인증 설정
ADMIN_USE_GUARD=true
ADMIN_GUARD_NAME=admin
ADMIN_SESSION_TIMEOUT=120
ADMIN_2FA_ENABLED=true
ADMIN_MAX_LOGIN_ATTEMPTS=5
ADMIN_LOCKOUT_TIME=15

# IP 제한
ADMIN_ALLOWED_IPS=192.168.1.0/24,10.0.0.0/8

# 세션 설정
ADMIN_SESSION_SECURE=true
ADMIN_SESSION_HTTP_ONLY=true
ADMIN_SESSION_SAME_SITE=strict
```

### 설정 파일

```php
// config/admin.php
return [
    'auth' => [
        'use_guard' => env('ADMIN_USE_GUARD', true),
        'guard_name' => env('ADMIN_GUARD_NAME', 'admin'),
        'session_timeout' => env('ADMIN_SESSION_TIMEOUT', 120),
        '2fa_enabled' => env('ADMIN_2FA_ENABLED', true),
        'max_login_attempts' => env('ADMIN_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_time' => env('ADMIN_LOCKOUT_TIME', 15),
    ],
    
    'security' => [
        'allowed_ips' => env('ADMIN_ALLOWED_IPS', ''),
        'session_secure' => env('ADMIN_SESSION_SECURE', true),
        'session_http_only' => env('ADMIN_SESSION_HTTP_ONLY', true),
        'session_same_site' => env('ADMIN_SESSION_SAME_SITE', 'strict'),
    ],
];
```

## 🔗 관련 문서

- [권한 관리](./authorization.md)
- [로그 시스템](./logging.md)
- [CRUD 시스템](./crud-system.md)
- [UI 컴포넌트](./ui-components.md)

## 📝 참고사항

- 2FA는 Google Authenticator 앱과 호환됩니다
- 세션 타임아웃은 분 단위로 설정합니다
- IP 제한은 CIDR 표기법을 지원합니다
- 로그인 시도 제한은 IP와 이메일별로 개별 적용됩니다
