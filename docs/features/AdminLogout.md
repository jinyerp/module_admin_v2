# AdminLogoutSessionController - 관리자 세션 로그아웃 컨트롤러

## 개요

`AdminLogoutSessionController`는 관리자 세션의 로그아웃 처리와 세션 관리를 담당하는 컨트롤러입니다. 보안을 위해 세션 정보를 데이터베이스에 기록하고 관리하며, 일반 웹 요청과 AJAX 요청을 모두 지원합니다.

## 주요 기능

### 1. 로그아웃 처리
- 일반 웹 요청과 AJAX 요청에 대한 로그아웃 처리
- admin 가드를 사용한 안전한 로그아웃
- 세션 데이터 정리 및 보안 강화

### 2. 세션 관리
- 데이터베이스에 세션 정보 저장 및 관리
- 만료된 세션 자동 정리
- 중복 세션 방지 및 관리

### 3. 보안 기능
- 세션 무효화 및 토큰 재생성
- 2FA 관련 세션 데이터 정리
- 로그아웃 활동 기록 및 추적

### 4. 세션 모니터링
- 현재 활성 세션 정보 조회
- 모든 세션 강제 종료 (super 관리자 전용)
- 특정 관리자 세션 강제 종료

## 동작 과정

### 1. 로그아웃 처리
1. **로그아웃 데이터 준비**: IP 주소, User-Agent 수집
2. **현재 사용자 확인**: admin 가드에서 로그인된 사용자 정보 조회
3. **로그아웃 수행**: 세션 정리, 가드 로그아웃, 데이터베이스 업데이트
4. **활동 기록**: 로그아웃 활동을 AdminUserLog에 기록
5. **응답 반환**: 적절한 형태의 응답 반환

### 2. 세션 정리
1. **admin 가드 로그아웃**: Laravel 인증 시스템에서 로그아웃
2. **세션 데이터 정리**: 관리자 관련 세션 데이터 제거
3. **데이터베이스 업데이트**: admin_sessions 테이블에서 세션 비활성화
4. **2FA 세션 정리**: 2FA 관련 세션 데이터 제거
5. **세션 무효화**: 세션 무효화 및 토큰 재생성

## API 엔드포인트

| 메소드 | 경로 | 설명 | 미들웨어 |
|--------|------|------|----------|
| GET | `/admin/logout` | 일반 로그아웃 처리 | `web`, `admin.auth` |
| POST | `/admin/logout` | POST 로그아웃 처리 | `web`, `admin.auth` |
| POST | `/admin/logout/ajax` | AJAX 로그아웃 처리 | `web`, `admin.auth` |
| GET | `/admin/session/info` | 현재 세션 정보 조회 | `web`, `admin.auth` |
| GET | `/admin/session/active` | 활성 세션 목록 조회 | `web`, `admin.auth` |
| POST | `/admin/session/force-logout-all` | 모든 세션 강제 종료 | `web`, `admin.auth` |
| POST | `/admin/session/force-logout-user/{id}` | 특정 사용자 세션 종료 | `web`, `admin.auth` |

## 뷰 경로

```php
protected string $loginView = 'jiny-admin::auth.login';
```

## 데이터베이스 구조

### admin_sessions 테이블
```sql
CREATE TABLE admin_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    admin_user_id BIGINT NOT NULL,
    admin_name VARCHAR(255) NOT NULL,
    admin_email VARCHAR(255) NOT NULL,
    admin_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    login_location VARCHAR(255),
    device VARCHAR(255),
    login_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 보안 고려사항

### 1. 세션 보안
- 로그아웃 시 세션 무효화
- CSRF 토큰 검증
- 세션 하이재킹 방지
- 안전한 쿠키 설정

### 2. 접근 제어
- 인증된 사용자만 로그아웃 가능
- super 관리자만 세션 관리 기능 접근
- 권한별 기능 제한

### 3. 데이터 보호
- 민감한 세션 정보 암호화
- 로그 데이터 보안 처리
- 개인정보 노출 방지

## 오류 처리

### 1. 데이터베이스 오류
```php
try {
    DB::table('admin_sessions')
        ->where('session_id', $sessionId)
        ->update(['is_active' => false]);
} catch (\Exception $e) {
    // 데이터베이스 오류가 발생해도 로그아웃은 계속 진행
    \Log::warning('관리자 로그아웃 시 세션 정리 실패', [
        'session_id' => $sessionId,
        'error' => $e->getMessage()
    ]);
}
```

### 2. 권한 검증
```php
if (!$admin || $admin->type !== 'super') {
    return response()->json([
        'success' => false,
        'message' => '권한이 없습니다.'
    ], 403);
}
```

## 성능 최적화

### 1. 세션 정리 최적화
```php
// 만료된 세션들을 배치로 정리
private function cleanupExpiredSessions(): void
{
    $sessionLifetime = config('session.lifetime', 120);
    $expiryTime = now()->subMinutes($sessionLifetime);

    DB::table('admin_sessions')
        ->where('last_activity', '<', $expiryTime)
        ->where('is_active', true)
        ->update([
            'is_active' => false,
            'updated_at' => now()
        ]);
}
```

### 2. 캐싱 활용
```php
// 활성 세션 수 캐싱
$activeSessionCount = Cache::remember('active_sessions_count', 60, function () {
    return DB::table('admin_sessions')
        ->where('is_active', true)
        ->count();
});
```

### 3. 배치 처리
```php
// 여러 세션을 한 번에 처리
DB::table('admin_sessions')
    ->whereIn('admin_user_id', $adminUserIds)
    ->update([
        'is_active' => false,
        'updated_at' => now()
    ]);
```

## 모니터링 및 로깅

### 1. 활동 로그
```php
// 로그아웃 활동 기록
AdminUserLog::create([
    'admin_user_id' => $adminUserId,
    'action' => 'logout',
    'ip_address' => $data['ip'],
    'user_agent' => $data['ua'],
    'status' => 'success',
    'message' => '로그아웃',
]);
```

### 2. 세션 통계
```php
// 세션 통계 정보 조회
$sessionStats = [
    'total_sessions' => DB::table('admin_sessions')->count(),
    'active_sessions' => DB::table('admin_sessions')->where('is_active', true)->count(),
    'expired_sessions' => DB::table('admin_sessions')->where('is_active', false)->count(),
];
```

### 3. 성능 메트릭
```php
// 세션 처리 시간 측정
$startTime = microtime(true);
$this->performLogout($request);
$processingTime = microtime(true) - $startTime;

\Log::info('로그아웃 처리 시간', [
    'admin_id' => $admin ? $admin->id : null,
    'processing_time' => $processingTime
]);
```

## 확장 가능성

### 1. 추가 보안 기능
- 로그아웃 시 알림 발송
- 의심스러운 로그아웃 감지
- IP 기반 접근 제한

### 2. 세션 분석
- 세션 패턴 분석
- 사용자 행동 분석
- 보안 위험도 평가

### 3. 통합 기능
- SSO 시스템 연동
- 외부 인증 시스템 연동
- 모바일 앱 세션 관리

## 개발 가이드

### 1. 새로운 세션 필드 추가
```php
// admin_sessions 테이블에 새 필드 추가
DB::table('admin_sessions')->updateOrInsert(
    ['session_id' => $sessionId],
    [
        'admin_user_id' => $admin->id,
        'new_field' => $newValue,
        // ... 기존 필드들
    ]
);
```

### 2. 커스텀 세션 정리 로직
```php
// 추가적인 세션 정리 로직
private function performCustomSessionCleanup($sessionId)
{
    // 커스텀 정리 로직 구현
    $this->cleanupCustomData($sessionId);
    $this->notifyCustomServices($sessionId);
}
```

### 3. 세션 이벤트 처리
```php
// 로그아웃 이벤트 발생
event(new AdminUserLoggedOut($admin, $sessionData));

// 이벤트 리스너에서 추가 처리
class AdminUserLoggedOutListener
{
    public function handle(AdminUserLoggedOut $event)
    {
        // 추가적인 로그아웃 후처리
    }
}
```

## 테스트 시나리오

### 1. 정상 로그아웃 테스트
- 일반 웹 요청 로그아웃
- AJAX 요청 로그아웃
- 세션 정리 확인
- 데이터베이스 업데이트 확인

### 2. 보안 테스트
- 권한 없는 사용자의 세션 관리 기능 접근
- CSRF 토큰 검증
- 세션 무효화 확인

### 3. 오류 처리 테스트
- 데이터베이스 연결 실패 시 처리
- 잘못된 세션 ID 처리
- 권한 검증 실패 시 처리

## 관련 파일

- **컨트롤러**: `jiny/admin/app/Http/Controllers/Auth/AdminLogoutSessionController.php`
- **라우트**: `jiny/admin/routes/web.php`
- **모델**: `jiny/admin/app/Models/AdminUserLog.php`
- **미들웨어**: `jiny/admin/app/Http/Middleware/AdminAuthMiddleware.php`
- **테스트**: `jiny/admin/tests/Feature/Auth/AdminLogoutSessionTest.php`

## 배포 고려사항

### 1. 데이터베이스 마이그레이션
- admin_sessions 테이블 생성
- 인덱스 설정
- 데이터 무결성 제약 조건

### 2. 설정 관리
- 세션 수명 설정
- 로그아웃 후처리 설정
- 보안 설정

### 3. 모니터링 설정
- 로그 수집 및 분석
- 성능 메트릭 수집
- 알림 설정
