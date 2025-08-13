# AdminTwoFactorController - 관리자 2FA 인증 컨트롤러

## 개요

`AdminTwoFactorController`는 로그인 후 2FA(2-Factor Authentication) 인증을 처리하고, 2FA 관련 도움말을 제공하는 컨트롤러입니다. 관리자별 2FA 설정은 `AdminUser2FAController`에서 처리하며, 이 컨트롤러는 2FA 인증 과정과 도움말 제공에 집중합니다.

## 주요 기능

### 1. 2FA 인증 처리
- 2FA가 활성화된 관리자 계정의 인증 코드 검증
- TOTP(Time-based One-Time Password) 인증 지원
- 백업 코드를 통한 인증 지원

### 2. 2FA 인증 페이지 관리
- 2FA 인증 페이지 표시 및 관리
- 2FA 도움말 페이지 제공
- 인증 상태에 따른 적절한 페이지 안내

### 3. 보안 및 인증 관리
- 2FA 인증 완료 세션 설정
- 인증 시도 로그 기록
- 보안 강화를 위한 다중 인증

## 동작 과정

### 1. 2FA 인증 페이지 접근
1. **접근 요청 수신**: `/admin/2fa/challenge` 경로로 GET 요청
2. **사용자 확인**: 현재 로그인된 관리자 정보 조회
3. **2FA 상태 확인**: 2FA 활성화 여부 확인
4. **페이지 표시**: 
   - 2FA 활성화 → 2FA 인증 페이지 표시
   - 2FA 비활성화 → 대시보드로 리다이렉트

### 2. 2FA 인증 코드 검증
1. **입력 데이터 검증**: 6자리 인증 코드 유효성 검사
2. **TOTP 코드 검증**: TwoFactorService를 통한 TOTP 검증
3. **백업 코드 검증**: TOTP 실패 시 백업 코드 검증
4. **인증 완료 처리**: 
   - 성공 시 → 2FA 인증 완료 세션 설정, 대시보드로 리다이렉트
   - 실패 시 → 오류 메시지와 함께 인증 페이지로 돌아가기

### 3. 2FA 도움말 제공
1. **도움말 페이지 접근**: `/admin/2fa/help` 경로로 GET 요청
2. **도움말 내용 표시**: 2FA 설정 및 사용에 대한 안내 정보 제공

## API 엔드포인트

| 메소드 | 경로 | 설명 | 미들웨어 |
|--------|------|------|----------|
| GET | `/admin/2fa/challenge` | 2FA 인증 페이지 표시 | `web`, `admin.auth` |
| POST | `/admin/2fa/verify` | 2FA 인증 코드 검증 | `web`, `admin.auth` |
| GET | `/admin/2fa/help` | 2FA 도움말 페이지 | `web`, `admin.auth` |

## 뷰 경로

```php
protected string $challengeView = 'jiny-admin::auth.auth_2fa_challenge';
protected string $helpView = 'jiny-admin::auth.help_2fa';
protected string $dashboardView = 'jiny-admin::dashboard.index';
```

## 의존성

### TwoFactorService
```php
protected $twoFactorService;

public function __construct(TwoFactorService $twoFactorService)
{
    $this->twoFactorService = $twoFactorService;
}
```

### 주요 메소드
- `verifyCode($user, $code)`: TOTP 코드 검증
- `verifyBackupCode($user, $code)`: 백업 코드 검증
- `log2FAAttempt($user, $code, $success)`: 2FA 시도 로그 기록
- `logBackupCodeUsage($user, $code, $success)`: 백업 코드 사용 로그 기록

## 보안 고려사항

### 1. 인증 보안
- 6자리 TOTP 코드 검증
- 백업 코드를 통한 대체 인증
- 인증 시도 로그 기록 및 모니터링

### 2. 세션 보안
- 2FA 인증 완료 후 세션 설정
- 인증 상태 세션 변수 관리
- 보안 강화를 위한 다중 인증

### 3. 접근 제어
- 인증된 사용자만 2FA 기능 접근
- 2FA 활성화된 계정만 인증 페이지 표시
- 권한별 기능 제한

## 오류 처리

### 1. 유효성 검사 오류
```php
// 인증 코드 유효성 검사
$request->validate([
    'code' => 'required|string|size:6'
]);
```

### 2. 인증 실패 처리
```php
// TOTP 코드 검증 실패
if (!$this->twoFactorService->verifyCode($user, $code)) {
    // 백업 코드 검증 시도
    if (!$this->twoFactorService->verifyBackupCode($user, $code)) {
        // 실패 로그 기록
        $this->twoFactorService->log2FAAttempt($user, $code, false);
        
        return back()->withErrors(['code' => '잘못된 인증 코드입니다.']);
    }
}
```

### 3. 사용자 상태 검증
```php
// 2FA 활성화 여부 확인
if (!$user || !$user->has2FAEnabled()) {
    return redirect()->route('admin.dashboard');
}
```

## 성능 최적화

### 1. 서비스 주입
```php
// 의존성 주입을 통한 성능 최적화
public function __construct(TwoFactorService $twoFactorService)
{
    $this->twoFactorService = $twoFactorService;
}
```

### 2. 캐싱 활용
```php
// 2FA 설정 정보 캐싱
$twoFactorConfig = Cache::remember('2fa_config_' . $user->id, 300, function () use ($user) {
    return $this->twoFactorService->get2FAConfig($user);
});
```

### 3. 비동기 처리
```php
// 로그 기록을 비동기로 처리
dispatch(function () use ($user, $code, $success) {
    $this->twoFactorService->log2FAAttempt($user, $code, $success);
})->afterResponse();
```

## 확장 가능성

### 1. 추가 인증 방식
- SMS 인증
- 이메일 인증
- 하드웨어 토큰 지원
- 생체 인증

### 2. 보안 강화
- 인증 시도 제한
- IP 기반 접근 제한
- 의심스러운 활동 감지
- 실시간 알림

### 3. 사용자 경험 개선
- QR 코드 생성
- 모바일 앱 연동
- 다국어 지원
- 접근성 향상

## 개발 가이드

### 1. 새로운 인증 방식 추가
```php
// 추가 인증 방식 구현
private function verifyCustomAuth($user, $code)
{
    // 커스텀 인증 로직 구현
    if ($this->twoFactorService->verifyCustomAuth($user, $code)) {
        $this->twoFactorService->logCustomAuthAttempt($user, $code, true);
        return true;
    }
    
    return false;
}
```

### 2. 커스텀 검증 로직
```php
// 추가적인 검증 로직
private function performAdditionalValidation($user, $code)
{
    // 사용자별 추가 검증 규칙
    if ($user->requiresAdditionalValidation()) {
        return $this->validateAdditionalRules($user, $code);
    }
    
    return true;
}
```

### 3. 인증 완료 후처리
```php
// 인증 완료 후 추가 처리
private function performPostAuthActions($user)
{
    // 인증 완료 이벤트 발생
    event(new TwoFactorAuthCompleted($user));
    
    // 추가적인 후처리 로직
    $this->updateUserAuthStatus($user);
    $this->sendAuthCompletionNotification($user);
}
```

## 테스트 시나리오

### 1. 정상 인증 테스트
- 2FA 인증 페이지 표시
- TOTP 코드 검증 성공
- 백업 코드 검증 성공
- 인증 완료 후 세션 설정

### 2. 보안 테스트
- 잘못된 인증 코드 처리
- 2FA 비활성화된 계정 접근 제한
- 인증 시도 로그 기록
- 세션 보안 검증

### 3. 오류 처리 테스트
- 유효성 검사 실패 시 처리
- 인증 실패 시 적절한 오류 메시지
- 사용자 상태 검증 실패 시 처리

## 관련 파일

- **컨트롤러**: `jiny/admin/app/Http/Controllers/Auth/AdminTwoFactorController.php`
- **서비스**: `jiny/admin/app/Services/TwoFactorService.php`
- **라우트**: `jiny/admin/routes/web.php`
- **뷰**: `jiny/admin/resources/views/auth/`
- **테스트**: `jiny/admin/tests/Feature/Auth/AdminTwoFactorTest.php`

## 배포 고려사항

### 1. 환경별 설정
- 개발/스테이징/운영 환경별 2FA 설정
- 환경변수를 통한 설정 관리
- 설정 파일 캐싱 활용

### 2. 보안 설정
- HTTPS 강제 적용
- 보안 헤더 설정
- 접근 로그 기록

### 3. 모니터링 설정
- 2FA 인증 시도 로그 수집
- 성능 메트릭 수집
- 오류 발생 시 알림 설정

## 모니터링 및 로깅

### 1. 인증 활동 로그
```php
// 2FA 인증 시도 로그 기록
$this->twoFactorService->log2FAAttempt($user, $code, $success);

// 백업 코드 사용 로그 기록
$this->twoFactorService->logBackupCodeUsage($user, $code, $success);
```

### 2. 성능 메트릭
```php
// 인증 처리 시간 측정
$startTime = microtime(true);
$success = $this->twoFactorService->verifyCode($user, $code);
$processingTime = microtime(true) - $startTime;

\Log::info('2FA 인증 처리 시간', [
    'user_id' => $user->id,
    'success' => $success,
    'processing_time' => $processingTime
]);
```

### 3. 보안 모니터링
```php
// 의심스러운 인증 시도 감지
if ($this->detectSuspiciousActivity($user, $request)) {
    \Log::warning('의심스러운 2FA 인증 시도', [
        'user_id' => $user->id,
        'ip_address' => $request->ip(),
        'user_agent' => $request->header('User-Agent')
    ]);
    
    // 보안 팀에 알림 발송
    $this->notifySecurityTeam($user, $request);
}
```

## 통합 가이드

### 1. 외부 2FA 서비스 연동
```php
// Google Authenticator 연동
private function verifyGoogleAuthenticator($user, $code)
{
    $secret = $user->google_authenticator_secret;
    return $this->googleAuthenticatorService->verify($secret, $code);
}

// Authy 연동
private function verifyAuthy($user, $code)
{
    $authyId = $user->authy_id;
    return $this->authyService->verify($authyId, $code);
}
```

### 2. 모바일 앱 연동
```php
// 모바일 앱 푸시 알림
private function sendMobilePushNotification($user)
{
    $deviceToken = $user->mobile_device_token;
    $this->pushNotificationService->send($deviceToken, '2FA 인증이 필요합니다.');
}
```

### 3. API 연동
```php
// 외부 API를 통한 인증
private function verifyExternalAPI($user, $code)
{
    $response = $this->httpClient->post('/api/2fa/verify', [
        'user_id' => $user->external_id,
        'code' => $code
    ]);
    
    return $response->json('success', false);
}
```
