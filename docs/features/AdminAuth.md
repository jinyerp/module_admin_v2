# AdminAuthSessionController - 관리자 인증 세션 컨트롤러

## 개요

`AdminAuthSessionController`는 관리자 로그인 인증을 처리하는 핵심 컨트롤러입니다. 체인 형태의 로그인 검사 시스템을 구현하여 보안성을 강화하고, 일반 웹 요청과 AJAX 요청을 모두 지원합니다.

## 주요 기능

### 1. 체인 형태의 로그인 검사 시스템
- **9단계 검증 프로세스**를 통해 보안성 강화
- 각 검사 단계는 독립적인 private 함수로 분리
- 모든 검사를 통과해야 로그인 성공

### 2. 다중 요청 타입 지원
- 일반 웹 요청: 리다이렉트 응답
- AJAX 요청: JSON 응답
- 자동 요청 타입 감지

### 3. 보안 기능
- 로그인 시도 제한 (브루트 포스 방지)
- 세션 재생성 및 보안 강화
- IP 주소 및 User-Agent 기록
- 2FA 인증 연동

## 동작 과정

### 로그인 검사 체인
1. **기본 입력 검증**: 이메일, 비밀번호 형식 검사
2. **시스템 상태 검사**: 점검 모드, DB 연결 상태 확인
3. **로그인 시도 제한**: 과도한 시도 방지
4. **사용자 계정 검증**: 계정 존재 여부 확인
5. **비밀번호 검증**: 해시 비교 및 가드 인증
6. **계정 상태 검증**: 활성화 상태, 이메일 인증 확인
7. **로그인 성공 처리**: 세션 업데이트, 로그 기록
8. **2FA 검증**: 2FA 활성화 여부 확인
9. **최종 리다이렉트**: 적절한 페이지로 이동

## API 엔드포인트

| 메소드 | 경로 | 설명 | 미들웨어 |
|--------|------|------|----------|
| POST | `/admin/login` | 일반 로그인 처리 | `web` |
| POST | `/admin/login/ajax` | AJAX 로그인 처리 | `web` |

## 뷰 경로

```php
protected string $loginView = 'jiny-admin::auth.login';
protected string $dashboardView = 'jiny-admin::dashboard.index';
protected string $twoFactorChallengeView = 'jiny-admin::auth.auth_2fa_challenge';
```

## 설정 옵션

### 로그인 시도 제한
```php
// config/admin.php
'auth' => [
    'login' => [
        'max_attempts' => 5,        // 최대 시도 횟수
        'lockout_time' => 300,      // 잠금 시간 (초)
    ]
]
```

### 비밀번호 규칙
```php
'auth' => [
    'password' => [
        'min_length' => 8,          // 최소 길이
        'require_special' => true,  // 특수문자 포함
        'require_number' => true,   // 숫자 포함
        'require_uppercase' => true // 대문자 포함
    ]
]
```

## 의존성

- `AdminUser` 모델: 관리자 사용자 정보
- `AdminUserLog` 모델: 로그인 활동 기록
- `Auth` Facade: Laravel 인증 시스템
- `Cache` Facade: 로그인 시도 제한
- `DB` Facade: 데이터베이스 연결 확인

## 보안 고려사항

### 1. 세션 보안
- 로그인 시 세션 재생성
- CSRF 토큰 검증
- 세션 하이재킹 방지

### 2. 인증 보안
- 비밀번호 해시 직접 검증 (추가 보안)
- 로그인 시도 횟수 제한
- 계정 상태별 접근 제어

### 3. 로깅 및 모니터링
- 모든 로그인 시도 기록
- IP 주소 및 User-Agent 추적
- 실패한 로그인 시도 분석

## 오류 처리

### 오류 코드 체계
- `PASSWORD_MISMATCH`: 비밀번호 불일치
- `USER_NOT_FOUND`: 사용자 없음
- `TOO_MANY_ATTEMPTS`: 과도한 시도
- `ACCOUNT_INACTIVE`: 비활성 계정
- `ACCOUNT_SUSPENDED`: 정지된 계정
- `ACCOUNT_PENDING`: 승인 대기
- `EMAIL_NOT_VERIFIED`: 이메일 미인증
- `SYSTEM_ERROR`: 시스템 오류

### 응답 형식
```json
{
    "success": false,
    "message": "오류 메시지",
    "error_code": "ERROR_CODE"
}
```

## 확장 가능성

### 1. 추가 인증 단계
- CAPTCHA 검증
- SMS 인증
- 소셜 로그인 연동

### 2. 보안 강화
- 다중 인증 (MFA)
- IP 화이트리스트/블랙리스트
- 지리적 위치 기반 제한

### 3. 모니터링 및 분석
- 로그인 패턴 분석
- 이상 로그인 감지
- 실시간 알림

## 관련 파일

- **컨트롤러**: `jiny/admin/app/Http/Controllers/Auth/AdminAuthSessionController.php`
- **라우트**: `jiny/admin/routes/web.php`
- **뷰**: `jiny/admin/resources/views/auth/`
- **모델**: `jiny/admin/app/Models/AdminUser.php`
- **테스트**: `jiny/admin/tests/Feature/Auth/AdminAuthSessionTest.php`

## 개발 가이드

### 1. 새로운 인증 단계 추가
```php
// 10단계: 추가 검증
$additionalCheckResult = $this->performAdditionalCheck($request);
if (!$additionalCheckResult['success']) {
    return $this->handleFailure($additionalCheckResult['message'], $isAjax, 400);
}
```

### 2. 커스텀 오류 메시지
```php
// config/admin.php
'auth' => [
    'messages' => [
        'custom_error' => '사용자 정의 오류 메시지'
    ]
]
```

### 3. 로그인 후 처리 확장
```php
// 로그인 성공 후 추가 처리
$this->performPostLoginActions($admin, $request);
```

## 성능 최적화

### 1. 캐시 활용
- 로그인 시도 횟수 캐싱
- 사용자 정보 캐싱
- 세션 데이터 캐싱

### 2. 데이터베이스 최적화
- 인덱스 활용
- 쿼리 최적화
- 연결 풀링

### 3. 비동기 처리
- 로그 기록 비동기 처리
- 이메일 발송 비동기 처리
- 알림 발송 비동기 처리
