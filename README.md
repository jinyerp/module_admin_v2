# Jiny Admin Module

관리자 시스템을 위한 종합적인 관리 모듈입니다. 관리자 활동 추적, 권한 관리, 시스템 모니터링 등의 기능을 제공합니다.

## 폴더 구조
`jiny/admin` 이름으로 /jiny/admin 폴더 안에 존재합니다.


## 주요 기능

### 1. 관리자 활동 로그
- 관리자의 모든 활동을 추적하고 기록
- 활동 타입별 분류 (create, update, delete, login, logout 등)
- 모듈별 활동 분류 (users, system, settings, payments 등)
- 중요도별 로그 분류 (low, medium, high, critical)
- 변경 전후 값 비교 (old_values, new_values)

### 2. 관리자 권한 로그
- 권한 부여/회수 이력 추적
- 권한 체크 및 접근 거부 기록
- 리소스별 권한 활동 추적
- 보안 관련 정보 수집 (IP 주소, 사용자 에이전트)
- 권한 변경 사유 및 컨텍스트 정보 기록

### 3. 시스템 에러 로그
- 애플리케이션 에러 추적 및 디버깅
- 에러 타입별 분류 및 중요도 관리
- 스택 트레이스 및 에러 발생 위치 정보
- 에러 발생 시점의 사용자 및 요청 정보
- 에러 해결 상태 관리

### 4. 시스템 백업 로그
- 데이터베이스 및 파일 백업 이력 추적
- 백업 성공/실패 상태 및 성능 모니터링
- 백업 파일 무결성 검증 (체크섬, 파일 크기)
- 백업 보안 설정 관리 (암호화, 압축)

### 5. 시스템 유지보수 로그
- 예정된 및 긴급 유지보수 일정 관리
- 유지보수 진행 상황 및 완료 상태 추적
- 다운타임 영향도 평가 및 서비스 영향 분석
- 유지보수 책임자 및 작업 시간 기록

### 6. 시스템 성능 로그
- CPU, 메모리, 디스크, 네트워크, 데이터베이스 성능 모니터링
- 성능 임계값 설정 및 알림 관리
- 서버별 및 컴포넌트별 성능 분석
- 성능 트렌드 분석 및 예측

### 7. 시스템 운영 로그
- 시스템의 모든 운영 활동을 상세히 기록
- 사용자 및 관리자의 모든 시스템 활동 추적
- 운영 타입별 분류 및 성능 모니터링
- 보안 관련 정보 수집 (IP 주소, 세션 ID, 사용자 에이전트)
- 에러 및 예외 상황 기록
- 실행 시간 측정으로 성능 분석

## 설치 및 설정

### 1. 서비스 프로바이더 등록

`config/app.php` 파일의 `providers` 배열에 추가:

```php
'providers' => [
    // ...
    Jiny\Admin\JinyAdminServiceProvider::class,
],
```

### 2. 마이그레이션 실행

```bash
php artisan migrate
```

### 3. 라우트 등록

`routes/web.php` 파일에 추가:

```php
Route::prefix('admin')->group(function () {
    require __DIR__.'/../jiny/admin/routes/admin.php';
});
```

## 사용법

### 관리자 활동 로그

```php
use Jiny\Admin\Models\AdminActivityLog;

// 활동 로그 생성
AdminActivityLog::create([
    'admin_id' => $adminId,
    'action' => 'create',
    'module' => 'users',
    'description' => '새 사용자 생성',
    'target_type' => 'User',
    'target_id' => $userId,
    'severity' => 'medium'
]);
```

### 관리자 권한 로그

```php
use Jiny\Admin\Models\AdminPermissionLog;
use Jiny\Admin\Services\AdminPermissionLogService;

// 권한 부여 로그
AdminPermissionLog::logGrant($adminId, 'user.create', 'User', $userId, '새 사용자 생성 권한');

// 권한 체크 로그
AdminPermissionLog::logCheck($adminId, 'user.edit', 'User', $userId, $hasPermission);

// 서비스를 통한 로그 생성
$service = app(AdminPermissionLogService::class);
$service->logGrant($adminId, 'user.delete', 'User', $userId, '사용자 삭제 권한');
```

### 시스템 운영 로그

```php
use Jiny\Admin\Models\SystemOperationLog;
use Jiny\Admin\Services\SystemOperationLogService;

// 성공한 운영 로그
SystemOperationLog::logSuccess(
    'user.create',
    '사용자 생성',
    'Admin',
    $adminId,
    'User',
    $userId,
    150, // 실행 시간 (ms)
    ['name' => 'John Doe', 'email' => 'john@example.com'],
    ['id' => $userId, 'status' => 'created']
);

// 실패한 운영 로그
SystemOperationLog::logFailed(
    'user.update',
    '사용자 정보 수정',
    'Admin',
    $adminId,
    '사용자를 찾을 수 없습니다',
    'User',
    $userId,
    50
);

// 서비스를 통한 로그 생성
$service = app(SystemOperationLogService::class);
$service->logSuccess('system.backup', '시스템 백업', 'System', 0, null, null, 5000);
```

### 시스템 에러 로그

```php
use Jiny\Admin\Models\SystemErrorLog;

SystemErrorLog::create([
    'error_type' => 'Exception',
    'error_message' => '데이터베이스 연결 실패',
    'stack_trace' => $exception->getTraceAsString(),
    'file' => $exception->getFile(),
    'line' => $exception->getLine(),
    'severity' => 'high'
]);
```

## API 엔드포인트

### 관리자 활동 로그 API

```
GET /admin/system/activity-logs - 활동 로그 목록
GET /admin/system/activity-logs/{id} - 활동 로그 상세
GET /admin/system/activity-logs/stats - 통계
POST /admin/system/activity-logs/export - 내보내기
```

### 관리자 권한 로그 API

```
GET /admin/system/permission-logs - 권한 로그 목록
GET /admin/system/permission-logs/{id} - 권한 로그 상세
GET /admin/system/permission-logs/stats - 통계
GET /admin/system/permission-logs/permission-analysis - 권한별 분석
GET /admin/system/permission-logs/admin-analysis - 관리자별 분석
GET /admin/system/permission-logs/resource-analysis - 리소스별 분석
GET /admin/system/permission-logs/time-trend - 시간별 트렌드
POST /admin/system/permission-logs/export - 내보내기
```

### 시스템 로그 API

```
GET /admin/system/backup-logs - 백업 로그
GET /admin/system/maintenance-logs - 유지보수 로그
GET /admin/system/performance-logs - 성능 로그
GET /admin/system/error-logs - 에러 로그
GET /admin/system/operation-logs - 운영 로그
GET /admin/system/operation-logs/{id} - 운영 로그 상세
GET /admin/system/operation-logs/stats - 운영 로그 통계
GET /admin/system/operation-logs/operation-type-analysis - 운영 타입별 분석
GET /admin/system/operation-logs/performer-analysis - 수행자별 분석
GET /admin/system/operation-logs/performance-analysis - 성능 분석
GET /admin/system/operation-logs/time-trend - 시간별 트렌드
GET /admin/system/operation-logs/error-analysis - 에러 분석
POST /admin/system/operation-logs/export - 운영 로그 내보내기
```

## 설정

### 환경 변수

```env
# 관리자 로그 설정
ADMIN_LOG_RETENTION_DAYS=365
ADMIN_LOG_SEVERITY_LEVEL=medium

# 시스템 로그 설정
SYSTEM_LOG_RETENTION_DAYS=90
SYSTEM_LOG_SEVERITY_LEVEL=low

# 백업 설정
BACKUP_ENCRYPTION_ENABLED=true
BACKUP_COMPRESSION_ENABLED=true
```

### 설정 파일

`config/admin.php` 파일을 생성하여 모듈 설정을 관리할 수 있습니다:

```php
<?php

return [
    'logs' => [
        'retention_days' => env('ADMIN_LOG_RETENTION_DAYS', 365),
        'severity_level' => env('ADMIN_LOG_SEVERITY_LEVEL', 'medium'),
    ],
    'system' => [
        'log_retention_days' => env('SYSTEM_LOG_RETENTION_DAYS', 90),
        'log_severity_level' => env('SYSTEM_LOG_SEVERITY_LEVEL', 'low'),
    ],
    'backup' => [
        'encryption_enabled' => env('BACKUP_ENCRYPTION_ENABLED', true),
        'compression_enabled' => env('BACKUP_COMPRESSION_ENABLED', true),
    ],
];
```

## 개발 및 테스트

### 개발 환경 설정

1. 모듈 디렉토리로 이동:
```bash
cd jiny/admin
```

2. 의존성 설치:
```bash
composer install
```

3. 테스트 실행:
```bash
php artisan test
```

### 테스트 데이터 생성

```php
// 팩토리를 사용한 테스트 데이터 생성
use Jiny\Admin\Models\AdminActivityLog;
use Jiny\Admin\Models\AdminPermissionLog;

AdminActivityLog::factory()->count(100)->create();
AdminPermissionLog::factory()->count(50)->create();
```

## 보안 고려사항

1. **로그 데이터 보호**: 민감한 정보가 로그에 기록되지 않도록 주의
2. **접근 권한**: 로그 조회 권한을 적절히 제한
3. **데이터 보존**: 법적 요구사항에 맞는 로그 보존 기간 설정
4. **암호화**: 백업 파일의 암호화 설정
5. **정기 정리**: 오래된 로그 데이터 정기적 정리

## 라이센스

이 모듈은 MIT 라이센스 하에 배포됩니다.

## 기여

버그 리포트나 기능 제안은 GitHub 이슈를 통해 제출해 주세요. 
