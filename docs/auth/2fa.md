# 관리자 2차 인증 (2FA)

## 개요

Jiny Admin 패키지의 Google Authenticator 기반 2차 인증 시스템입니다. 관리자 계정의 보안을 강화하기 위해 TOTP(Time-based One-Time Password) 방식을 사용합니다.

## 주요 기능

### 1. Google Authenticator 지원
- TOTP 표준을 따르는 Google Authenticator 앱과 호환
- QR 코드를 통한 간편한 설정
- 6자리 숫자 코드 인증

### 2. 백업 코드 시스템
- 8자리 대문자 백업 코드 생성
- 각 코드는 한 번만 사용 가능
- 앱 분실 시 복구 수단 제공

### 3. 보안 로그 시스템
- 2FA 설정, 인증, 비활성화 등의 모든 활동 로그
- IP 주소, 사용자 에이전트 정보 기록
- 실패한 인증 시도 추적
- 관리자별, 액션별, 상태별 로그 필터링
- 로그 정리 및 내보내기 기능

### 4. 관리자 로그 관리
- 2FA 로그 전용 관리 페이지 (`/admin/admin/logs/2fa`)
- 실시간 통계 및 분석
- 오래된 로그 자동 정리 기능
- CSV/Excel 형식 로그 내보내기

## 설치 및 설정

### 1. 패키지 의존성 설치

```bash
composer require pragmarx/google2fa
```

### 2. 마이그레이션 실행

```bash
php artisan migrate
```

### 3. 설정 파일 확인

`config/admin/settings.php`의 `2fa` 섹션을 확인하고 필요에 따라 수정:

```php
'2fa' => [
    'enabled' => true,
    'app_name' => env('APP_NAME', 'Jiny Admin'),
    'backup_codes_count' => 8,
    'backup_code_length' => 8,
    'time_window' => 2,
    'qr_code_size' => 200,
    'required_for_all' => false,
    'exempt_roles' => ['super'],
    'log_attempts' => true,
    'max_attempts' => 5,
    'lockout_time' => 300,
],
```

## 사용 방법

### 1. 2FA 설정

#### 1.1 관리자 로그인
- 관리자 계정으로 로그인

#### 1.2 2FA 설정 페이지 접속
- `/admin/2fa` 경로로 접속
- "2FA 설정하기" 버튼 클릭

#### 1.3 Google Authenticator 앱 설정
- QR 코드를 스캔하거나 수동으로 시크릿 키 입력
- 앱에서 6자리 코드 생성 확인

#### 1.4 인증 코드 입력
- 생성된 6자리 코드를 입력하여 설정 완료
- 백업 코드 다운로드 권장

### 2. 로그인 플로우

#### 2.1 기본 로그인
- 이메일과 비밀번호로 로그인

#### 2.2 2FA 인증 (활성화된 경우)
- 6자리 TOTP 코드 입력
- 또는 백업 코드 사용

#### 2.3 대시보드 접근
- 인증 완료 후 관리자 대시보드 접근

### 3. 백업 코드 사용

#### 3.1 백업 코드 다운로드
- 2FA 설정 시 자동 생성
- `/admin/2fa` 페이지에서 다운로드 가능

#### 3.2 백업 코드 사용
- 앱 분실 또는 기기 변경 시 사용
- 8자리 대문자 코드 입력
- 사용 후 자동으로 삭제됨

### 4. 2FA 로그 관리

#### 4.1 로그 페이지 접속
- 관리자 메뉴 → 감시 관리 → 2FA 로그
- URL: `/admin/admin/logs/2fa`

#### 4.2 로그 조회 및 필터링
- 관리자별, 액션별, 상태별 필터링
- IP 주소, 날짜 범위, 메시지 키워드 검색
- 고급 검색 옵션 지원

#### 4.3 통계 확인
- 전체 로그, 성공/실패 로그, 오늘 로그 수
- 실시간 통계 카드 표시

#### 4.4 로그 정리
- 30일 이상 된 오래된 로그 자동 삭제
- 정리 버튼으로 수동 정리 가능
- 삭제된 로그 수 확인

#### 4.5 로그 내보내기
- CSV/Excel 형식으로 로그 내보내기
- 필터링된 결과만 내보내기 가능

## API 엔드포인트

### 설정 관련

| 메서드 | 경로 | 설명 |
|--------|------|------|
| GET | `/admin/2fa` | 2FA 설정 페이지 |
| GET | `/admin/2fa/setup` | 2FA 설정 시작 (QR 코드) |
| POST | `/admin/2fa/enable` | 2FA 활성화 |
| POST | `/admin/2fa/disable` | 2FA 비활성화 |
| GET | `/admin/2fa/download` | 백업 코드 다운로드 |

### 인증 관련

| 메서드 | 경로 | 설명 |
|--------|------|------|
| GET | `/admin/2fa/challenge` | 2FA 인증 페이지 |
| POST | `/admin/2fa/verify` | 2FA 인증 처리 |

### 로그 관리 관련

| 메서드 | 경로 | 설명 |
|--------|------|------|
| GET | `/admin/admin/logs/2fa` | 2FA 로그 목록 |
| GET | `/admin/admin/logs/2fa/stats` | 2FA 로그 통계 |
| GET | `/admin/admin/logs/2fa/export` | 2FA 로그 내보내기 |
| POST | `/admin/admin/logs/2fa/cleanup` | 오래된 로그 정리 |
| GET | `/admin/admin/logs/2fa/{id}` | 2FA 로그 상세보기 |
| DELETE | `/admin/admin/logs/2fa/{id}` | 2FA 로그 삭제 |
| POST | `/admin/admin/logs/2fa/bulk-delete` | 2FA 로그 일괄 삭제 |

## 미들웨어

### Admin2FA 미들웨어
- 2FA가 활성화된 관리자 계정에 대한 인증 확인
- 인증되지 않은 경우 challenge 페이지로 리다이렉트

```php
Route::middleware(['admin:auth', 'admin:2fa'])->group(function () {
    // 2FA 인증이 필요한 라우트
});
```

## 데이터베이스 구조

### admin_users 테이블 추가 필드

| 필드명 | 타입 | 설명 |
|--------|------|------|
| `google_2fa_secret` | VARCHAR | Google Authenticator 시크릿 키 |
| `google_2fa_enabled` | BOOLEAN | 2FA 활성화 여부 |
| `google_2fa_backup_codes` | JSON | 백업 코드 배열 |
| `google_2fa_verified_at` | TIMESTAMP | 2FA 설정 완료 시각 |
| `google_2fa_disabled_at` | TIMESTAMP | 2FA 비활성화 시각 |

### admin_2fa_logs 테이블

| 필드명 | 타입 | 설명 |
|--------|------|------|
| `id` | BIGINT | 기본 키 |
| `admin_user_id` | BIGINT | 관리자 ID (외래키) |
| `action` | VARCHAR | 액션 (enable, disable, verify, setup 등) |
| `status` | VARCHAR | 상태 (success, fail) |
| `message` | TEXT | 상세 메시지 |
| `ip_address` | VARCHAR | IP 주소 |
| `user_agent` | TEXT | 사용자 에이전트 |
| `metadata` | JSON | 추가 메타데이터 |
| `created_at` | TIMESTAMP | 생성 시각 |
| `updated_at` | TIMESTAMP | 수정 시각 |

## 보안 고려사항

### 1. 시크릿 키 보안
- 시크릿 키는 암호화되어 저장되지 않음 (TOTP 표준)
- 데이터베이스 접근 권한 제한 필요

### 2. 백업 코드 관리
- 백업 코드는 안전한 곳에 보관
- 사용 후 즉시 삭제됨
- 정기적인 백업 코드 재생성 권장

### 3. 로그 모니터링
- 2FA 실패 시도 모니터링
- 의심스러운 활동 탐지
- 정기적인 로그 분석
- IP 주소 기반 이상 패턴 탐지

### 4. 세션 관리
- 2FA 인증 완료 후 세션 설정
- 세션 만료 시 재인증 필요

### 5. 로그 보안
- 로그 데이터 암호화 저장
- 접근 권한 제한
- 정기적인 로그 백업

## 문제 해결

### 1. 시간 동기화 문제
- 서버와 클라이언트 간 시간 차이 확인
- NTP 서버 설정 확인

### 2. QR 코드 스캔 실패
- 수동으로 시크릿 키 입력
- 앱 재설치 후 재시도

### 3. 백업 코드 분실
- 관리자에게 문의
- 2FA 재설정 필요

### 4. 앱 분실
- 백업 코드 사용
- 새 기기에서 재설정

### 5. 로그 관련 문제
- 로그 페이지 접속 불가: 권한 확인
- 로그 정리 실패: 데이터베이스 연결 확인
- 내보내기 실패: 디스크 공간 확인

## 개발자 가이드

### 1. 서비스 클래스 사용

```php
use Jiny\Admin\Services\TwoFactorService;

$twoFactorService = app(TwoFactorService::class);

// 시크릿 생성
$secret = $twoFactorService->generateSecret();

// QR 코드 URL 생성
$qrCodeUrl = $twoFactorService->generateQRCodeUrl($user, $secret);

// 코드 검증
$isValid = $twoFactorService->verifyCode($user, $code);
```

### 2. 모델 메서드 사용

```php
$user = AdminUser::find(1);

// 2FA 활성화 확인
if ($user->has2FAEnabled()) {
    // 2FA 인증 필요
}

// 백업 코드 사용
if ($user->useBackupCode($code)) {
    // 백업 코드 사용 성공
}
```

### 3. 로그 관련 기능

```php
use Jiny\Admin\Models\Admin2FALog;

// 로그 생성
Admin2FALog::create([
    'admin_user_id' => $user->id,
    'action' => 'verify',
    'status' => 'success',
    'ip_address' => request()->ip(),
    'message' => '2FA 인증 성공'
]);

// 통계 조회
$stats = [
    'total' => Admin2FALog::count(),
    'success' => Admin2FALog::where('status', 'success')->count(),
    'fail' => Admin2FALog::where('status', 'fail')->count(),
];
```

### 4. 설정 확인

```php
$config = config('admin.settings.2fa');

if ($config['enabled']) {
    // 2FA 기능 활성화
}
```

### 5. 컨트롤러 확장

```php
use Jiny\Admin\Http\Controllers\Admin2FALogController;

class Custom2FALogController extends Admin2FALogController
{
    protected function getTableName()
    {
        return 'custom_2fa_logs';
    }
    
    protected function getModuleName()
    {
        return 'custom_2fa_logs';
    }
}
```

## 구현된 기능 목록

### ✅ 완료된 기능

1. **2FA 기본 기능**
   - Google Authenticator TOTP 지원
   - QR 코드 생성 및 스캔
   - 6자리 인증 코드 검증
   - 백업 코드 시스템

2. **로그 시스템**
   - 2FA 관련 모든 활동 로깅
   - IP 주소, 사용자 에이전트 기록
   - 성공/실패 상태 구분
   - 메타데이터 JSON 저장

3. **관리자 로그 관리**
   - 전용 로그 관리 페이지 (`/admin/admin/logs/2fa`)
   - 실시간 통계 카드
   - 다중 필터링 시스템
   - 고급 검색 옵션

4. **로그 정리 및 내보내기**
   - 30일 이상 된 로그 자동 정리
   - CSV/Excel 형식 내보내기
   - 일괄 삭제 기능

5. **보안 기능**
   - 레이어 팝업 기반 삭제 확인
   - CSRF 토큰 보호
   - 권한 기반 접근 제어

### 🔄 향후 개선 예정

1. **고급 분석**
   - 시각적 차트 및 그래프
   - 이상 패턴 탐지
   - 실시간 알림 시스템

2. **보안 강화**
   - 로그 데이터 암호화
   - 감사 추적 기능
   - 자동 백업 시스템

3. **사용자 경험**
   - 모바일 반응형 개선
   - 다크 모드 지원
   - 키보드 단축키 지원

## 라이센스

이 기능은 MIT 라이센스 하에 제공됩니다.
