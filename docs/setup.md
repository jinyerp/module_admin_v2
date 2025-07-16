# 관리자 인증 시스템 설치 가이드

## 개요

이 문서는 Jiny Admin 패키지의 관리자 인증 시스템 설치 및 설정 방법을 안내합니다.

### 주요 특징

- **ADMIN_USE_GUARD 설정**: 간단한 환경변수로 가드 사용 여부 결정
- **고정된 admin 가드**: 가드 이름은 'admin'으로 고정
- **users + admin_users 테이블 연동**: 관리자 권한 체크
- **로그인/로그아웃 로그**: 보안 감사 및 모니터링
- **artisan 커맨드**: 관리자 계정 생성/삭제

## 설치 단계

### 1. 패키지 설치

```bash
composer require jiny/admin
```

### 2. 서비스 프로바이더 등록

`config/app.php`의 `providers` 배열에 추가:

```php
'providers' => [
    // ... 기존 providers ...
    Jiny\Admin\JinyAdminServiceProvider::class,
],
```

### 3. 마이그레이션 실행

```bash
php artisan migrate
```

### 4. 설정 파일 발행 (선택사항)

```bash
php artisan vendor:publish --tag=jiny-admin-config
```

## 환경 설정

### 1. .env 파일 설정

#### 개발 환경 (기본 Auth 사용)
```env
# 관리자 인증 설정
ADMIN_USE_GUARD=false
```

#### 프로덕션 환경 (Guard 사용)
```env
# 관리자 인증 설정
ADMIN_USE_GUARD=true
```

### 2. config/admin/auth.php 설정

```php
return [
    // ... 기존 설정 ...
    
    // 관리자 인증 설정
    'use_admin_guard' => env('ADMIN_USE_GUARD', false), // guard 사용 여부
];
```

## 관리자 계정 생성

### 1. 기본 관리자 생성

```bash
php artisan admin:user
```

대화형으로 다음 정보를 입력:
- 이메일
- 이름
- 비밀번호
- 관리자 유형 (super, admin, staff)
- 계정 상태 (active, inactive, suspended)

### 2. 관리자 계정 삭제

```bash
php artisan admin:user-delete
```

삭제할 관리자의 이메일을 입력하면 admin_users 테이블에서 삭제됩니다.

## 인증 방식

### 1. Guard 사용 방식 (ADMIN_USE_GUARD=true)

- **장점**: 보안 강화, 세션 분리, 동시 로그인 가능
- **특징**: 고정된 'admin' 가드 사용, 로그인 시 "관리자 로그인 (Guard)" 메시지 표시
- **사용 시나리오**: 프로덕션 환경, 보안이 중요한 시스템

### 2. 기본 Auth + admin_users 체크 방식 (ADMIN_USE_GUARD=false)

- **장점**: 설정 간단, 빠른 구현
- **단점**: 세션 혼재, 동시 로그인 제한
- **사용 시나리오**: 개발 환경, 간단한 관리자 시스템

## 라우트

### 관리자 인증 라우트

```php
// 로그인
GET  /admin/login     - 로그인 폼
POST /admin/login     - 로그인 처리

// 로그아웃
GET  /admin/logout    - 로그아웃 처리

// 대시보드 (인증 필요)
GET  /admin/dashboard - 관리자 대시보드
```

## 미들웨어

### admin.auth 미들웨어

관리자 인증이 필요한 라우트에 적용:

```php
Route::middleware(['admin.auth'])->group(function () {
    // 관리자 전용 라우트
});
```

## 문제 해결

### 1. "Auth guard [admin] is not defined" 오류

**원인**: 서비스 프로바이더가 제대로 등록되지 않음

**해결 방법**:
1. `config/app.php`에 서비스 프로바이더 등록 확인
2. 캐시 클리어:

```bash
php artisan config:clear
php artisan cache:clear
```

### 2. 관리자 로그인 후 계속 로그인 페이지로 리다이렉트

**원인**: admin_users 테이블에 해당 이메일이 없음

**해결 방법**:
1. `php artisan admin:user`로 관리자 계정 생성
2. admin_users 테이블에 계정이 등록되었는지 확인

### 3. 세션 관련 문제

**해결 방법**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan session:table
php artisan migrate
```

## 보안 고려사항

### 1. 환경별 설정

- **개발 환경**: ADMIN_USE_GUARD=false (간단한 설정)
- **프로덕션 환경**: ADMIN_USE_GUARD=true (보안 강화)

### 2. 로그 모니터링

관리자 로그인/로그아웃 로그는 `admin_user_logs` 테이블에 저장됩니다:

```sql
SELECT * FROM admin_user_logs ORDER BY created_at DESC;
```

### 3. 세션 보안

- 로그인 시 세션 재생성
- 로그아웃 시 세션 무효화
- CSRF 토큰 사용

## 개발 팁

### 1. Blade에서 관리자 정보 접근

```php
@if(Auth::check())
    @php
        $admin = \Jiny\Admin\Models\AdminUser::where('email', Auth::user()->email)->first();
    @endphp
    @if($admin)
        관리자: {{ $admin->name }} ({{ $admin->type }})
    @endif
@endif
```

### 2. 관리자 권한 체크

```php
public function someAdminFunction()
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    
    $admin = AdminUser::where('email', Auth::user()->email)->first();
    if (!$admin) {
        return redirect()->route('login')->withErrors(['email' => '관리자 권한이 없습니다.']);
    }
    
    // 관리자 전용 로직
}
```

## 지원

문제가 발생하거나 추가 기능이 필요한 경우:

1. 이 문서의 문제 해결 섹션 확인
2. 로그 파일 확인 (`storage/logs/laravel.log`)
3. 관리자 로그 테이블 확인 (`admin_user_logs`)
4. 개발팀에 문의

---

**버전**: 1.0.0  
**최종 업데이트**: 2024년 1월


