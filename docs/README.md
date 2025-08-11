# Jiny Admin 패키지 문서

## 📋 목차

- [1. 개요](#1-개요)
- [2. 주요 기능](#2-주요-기능)
- [3. 설치 및 설정](#3-설치-및-설정)
- [4. 아키텍처](#4-아키텍처)
- [5. 라우터별 기능](#5-라우터별-기능)
- [6. CRUD 시스템](#6-crud-시스템)
- [7. 인증 시스템](#7-인증-시스템)
- [8. 권한 관리](#8-권한-관리)
- [9. 로그 시스템](#9-로그-시스템)
- [10. 레이아웃 시스템](#10-레이아웃-시스템)
- [11. 컴포넌트](#11-컴포넌트)
- [12. 개발 가이드](#12-개발-가이드)

---

## 1. 개요

### 1.1 패키지 소개
Jiny Admin은 Laravel 기반의 강력한 관리자 패널 시스템입니다. 완전한 CRUD 기능, 인증 시스템, 권한 관리, 로그 추적 등을 제공합니다.

### 1.2 주요 특징
- 🔐 **다중 인증 시스템**: Guard 기반 및 기본 Auth 기반 지원
- 🛡️ **2FA 보안**: Google Authenticator 기반 2단계 인증
- 📊 **완전한 CRUD**: 자동화된 CRUD 생성 및 관리
- 📝 **활동 로그**: 모든 관리자 활동 추적
- 🔍 **감사 로그**: 데이터 변경 이력 추적
- 🎨 **반응형 UI**: Tailwind CSS 기반 모던 인터페이스
- 📱 **모바일 지원**: 반응형 디자인으로 모든 디바이스 지원

### 1.3 아키텍처 컨셉
```
┌─────────────────────────────────────────────────────────────┐
│                    Jiny Admin System                       │
├─────────────────────────────────────────────────────────────┤
│  Authentication Layer  │  Authorization Layer  │  UI Layer │
│  - Login/Logout       │  - Role Management    │  - Layouts │
│  - 2FA Support        │  - Permission Check   │  - Components│
│  - Session Tracking   │  - Access Control     │  - Templates│
├─────────────────────────────────────────────────────────────┤
│  Business Logic Layer  │  Data Access Layer   │  Log Layer │
│  - CRUD Operations    │  - Models            │  - Activity│
│  - Resource Mgmt      │  - Migrations        │  - Audit    │
│  - Validation         │  - Seeders           │  - Security│
└─────────────────────────────────────────────────────────────┘
```

---

## 2. 주요 기능

### 2.1 핵심 기능
- **관리자 인증**: 로그인/로그아웃, 세션 관리
- **2FA 보안**: Google Authenticator 지원
- **권한 관리**: 역할 기반 접근 제어
- **CRUD 자동화**: 리소스 컨트롤러 템플릿
- **로그 시스템**: 활동 로그, 감사 로그, 보안 로그
- **국가/언어 관리**: 다국어 지원 시스템
- **시스템 모니터링**: 성능, 백업, 유지보수 로그

### 2.2 보안 기능
- **다중 인증**: Guard 기반 분리된 인증
- **2FA 지원**: QR 코드 기반 2단계 인증
- **세션 추적**: 관리자 세션 모니터링
- **IP 제한**: 접근 IP 제한 기능
- **활동 로그**: 모든 관리자 활동 기록

### 2.3 관리 기능
- **사용자 관리**: 관리자 계정 CRUD
- **권한 관리**: 역할 및 권한 설정
- **시스템 설정**: 메일, 언어, 국가 설정
- **로그 관리**: 다양한 로그 조회 및 관리
- **백업 관리**: 시스템 백업 및 복원

---

## 3. 설치 및 설정

### 3.1 패키지 설치
```bash
composer require jiny/admin
```

### 3.2 서비스 프로바이더 등록
```php
// config/app.php
'providers' => [
    Jiny\Admin\JinyAdminServiceProvider::class,
],
```

### 3.3 마이그레이션 실행
```bash
php artisan migrate
```

### 3.4 환경 설정
```env
# .env
ADMIN_USE_GUARD=true
ADMIN_2FA_ENABLED=true
ADMIN_SESSION_TIMEOUT=120
```

### 3.5 기본 관리자 생성
```bash
php artisan admin:user
```

---

## 4. 아키텍처

### 4.1 디렉토리 구조
```
jiny/admin/
├── app/
│   ├── Http/Controllers/    # 컨트롤러
│   ├── Models/             # 모델
│   ├── Services/           # 서비스 클래스
│   └── Console/Commands/   # Artisan 명령어
├── database/
│   ├── migrations/         # 마이그레이션
│   └── seeders/           # 시더
├── resources/
│   └── views/
│       ├── layouts/        # 레이아웃
│       ├── components/     # 컴포넌트
│       └── admin/          # 관리자 뷰
├── routes/
│   └── admin.php          # 관리자 라우트
├── config/                # 설정 파일
├── View/                  # Blade 컴포넌트
└── docs/                  # 문서
```

### 4.2 서비스 구조
```
Services/
├── AdminSideMenuService    # 사이드 메뉴 관리
├── AdminPermissionService  # 권한 관리
├── AdminActivityService    # 활동 로그
├── AdminAuditService       # 감사 로그
└── AdminSessionService     # 세션 관리
```

---

## 5. 라우터별 기능

### 5.1 대시보드 (`/admin`)
- **기능**: 관리자 대시보드
- **컨트롤러**: `AdminDashboard`
- **특징**: 시스템 현황, 통계, 빠른 액션

### 5.2 데이터베이스 관리 (`/admin/database`)
- **기능**: 마이그레이션 관리
- **컨트롤러**: `DatabaseController`, `MigrationListController`
- **특징**: 마이그레이션 실행, 롤백, 상태 확인

### 5.3 기본 관리

#### 5.3.1 국가 관리 (`/admin/country`)
- **기능**: 국가 정보 CRUD
- **컨트롤러**: `AdminCountryController`
- **특징**: ISO 3166-1 표준, 대륙별 분류

#### 5.3.2 언어 관리 (`/admin/language`)
- **기능**: 언어 설정 CRUD
- **컨트롤러**: `AdminLanguageController`
- **특징**: 다국어 지원, 기본 언어 설정

#### 5.3.3 메일 설정 (`/admin/setting/mail`)
- **기능**: SMTP 설정 관리
- **컨트롤러**: `AdminSettingMailController`
- **특징**: 메일 테스트, 설정 저장

#### 5.3.4 시스템 정보 (`/admin/systems`)
- **기능**: 시스템 상태 확인
- **컨트롤러**: `AdminSystemController`
- **특징**: PHP, Laravel, DB, 세션 정보

### 5.4 관리자 관리

#### 5.4.1 관리자 사용자 (`/admin/admin/users`)
- **기능**: 관리자 계정 CRUD
- **컨트롤러**: `AdminUserController`
- **특징**: 2FA 설정, 권한 관리

#### 5.4.2 관리자 등급 (`/admin/admin/levels`)
- **기능**: 관리자 등급 CRUD
- **컨트롤러**: `AdminLevelController`
- **특징**: 권한 레벨 설정

#### 5.4.3 세션 관리 (`/admin/sessions`)
- **기능**: 관리자 세션 관리
- **컨트롤러**: `AdminSessionController`
- **특징**: 세션 추적, 강제 로그아웃

### 5.5 로그 관리

#### 5.5.1 사용자 로그 (`/admin/admin/user-logs`)
- **기능**: 관리자 활동 로그
- **컨트롤러**: `AdminUserLogController`
- **특징**: 활동 추적, 통계

#### 5.5.2 2FA 로그 (`/admin/admin/user-2fa-logs`)
- **기능**: 2FA 인증 로그
- **컨트롤러**: `Admin2FALogController`
- **특징**: 2FA 시도 기록

#### 5.5.3 활동 로그 (`/admin/admin/activity-log`)
- **기능**: 시스템 활동 로그
- **컨트롤러**: `AdminActivityLogController`
- **특징**: 상세 활동 기록

#### 5.5.4 감사 로그 (`/admin/admin/audit-logs`)
- **기능**: 데이터 변경 감사
- **컨트롤러**: `AdminAuditLogController`
- **특징**: 변경 이력 추적

#### 5.5.5 권한 로그 (`/admin/admin/permission-logs`)
- **기능**: 권한 변경 로그
- **컨트롤러**: `AdminPermissionLogController`
- **특징**: 권한 변경 추적

### 5.6 시스템 관리

#### 5.6.1 백업 로그 (`/admin/systems/backup-logs`)
- **기능**: 시스템 백업 관리
- **컨트롤러**: `AdminSystemBackupLogController`
- **특징**: 백업 생성, 다운로드

#### 5.6.2 유지보수 로그 (`/admin/systems/maintenance-logs`)
- **기능**: 시스템 유지보수 관리
- **컨트롤러**: `AdminSystemMaintenanceLogController`
- **특징**: 유지보수 일정 관리

#### 5.6.3 운영 로그 (`/admin/systems/operation-logs`)
- **기능**: 시스템 운영 로그
- **컨트롤러**: `AdminSystemOperationLogController`
- **특징**: 운영 활동 추적

#### 5.6.4 성능 로그 (`/admin/systems/performance-logs`)
- **기능**: 시스템 성능 모니터링
- **컨트롤러**: `AdminSystemPerformanceLogController`
- **특징**: 성능 지표 추적

---

## 6. CRUD 시스템

### 6.1 AdminResourceController

#### 6.1.1 개요
`AdminResourceController`는 모든 CRUD 작업의 기본 클래스입니다. 템플릿 메소드 패턴을 사용하여 일관된 CRUD 기능을 제공합니다.

#### 6.1.2 주요 메소드
```php
abstract class AdminResourceController extends Controller
{
    // 공개 메소드 (라우트에서 호출)
    public function index(Request $request)
    public function create(Request $request)
    public function store(Request $request)
    public function show(Request $request, $id)
    public function edit(Request $request, $id)
    public function update(Request $request, $id)
    public function destroy(Request $request)

    // 추상 메소드 (자식 클래스에서 구현)
    protected function _index(Request $request)
    protected function _create(Request $request)
    protected function _store(Request $request)
    protected function _show(Request $request, $id)
    protected function _edit(Request $request, $id)
    protected function _update(Request $request, $id)
    protected function _destroy(Request $request)
}
```

#### 6.1.3 템플릿 메소드 패턴
```php
public function index(Request $request)
{
    $route = $this->getRouteName($request);
    $view = $this->_index($request);  // 추상 메소드 호출
    $this->logActivity('read', '목록 조회', null, null);
    return $view->with('route', $route);
}
```

#### 6.1.4 로그 자동화
- **활동 로그**: 모든 CRUD 작업 자동 기록
- **감사 로그**: 데이터 변경 이력 자동 추적
- **보안 로그**: 접근 및 권한 관련 로그

### 6.2 CRUD 구현 예제

#### 6.2.1 기본 컨트롤러 생성
```php
class AdminCountryController extends AdminResourceController
{
    protected $tableName = 'admin_country';
    protected $moduleName = 'country';
    
    protected function _index(Request $request)
    {
        $countries = AdminCountry::query()
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(20);
            
        return view('admin.country.index', compact('countries'));
    }
    
    protected function _create(Request $request)
    {
        return view('admin.country.create');
    }
    
    protected function _store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:admin_country',
            'name' => 'required',
        ]);
        
        AdminCountry::create($validated);
        
        return redirect()->route('admin.country.index')
            ->with('success', '국가가 생성되었습니다.');
    }
}
```

#### 6.2.2 라우트 등록
```php
// routes/admin.php
Route::prefix('country')->name('country.')->group(function () {
    Route::get('/', [AdminCountryController::class, 'index'])->name('index');
    Route::get('/create', [AdminCountryController::class, 'create'])->name('create');
    Route::post('/', [AdminCountryController::class, 'store'])->name('store');
    Route::get('/{id}', [AdminCountryController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [AdminCountryController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminCountryController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminCountryController::class, 'destroy'])->name('destroy');
});
```

### 6.3 자동화 기능

#### 6.3.1 필터링 시스템
```php
protected $filterable = ['name', 'code', 'continent'];
protected $validFilters = ['search', 'status', 'date_from', 'date_to'];

protected function applyFilter($filters, $query, $likeFields)
{
    foreach ($this->filterable as $column) {
        if (isset($filters[$column]) && $filters[$column] !== '') {
            if (in_array($column, $likeFields)) {
                $query->where($column, 'like', "%{$filters[$column]}%");
            } else {
                $query->where($column, $filters[$column]);
            }
        }
    }
    return $query;
}
```

#### 6.3.2 정렬 시스템
```php
protected $sortableColumns = ['name', 'code', 'created_at'];

protected function sort($query, $request)
{
    $sortBy = $request->get('sort', 'created_at');
    $sortOrder = $request->get('direction', 'desc');

    if (in_array($sortBy, $this->sortableColumns)) {
        $query->orderBy($sortBy, $sortOrder);
    }
    
    return $query;
}
```

---

## 7. 인증 시스템

### 7.1 다중 인증 방식

#### 7.1.1 Guard 기반 인증 (권장)
```env
ADMIN_USE_GUARD=true
```
- **특징**: 완전히 분리된 인증 시스템
- **장점**: 보안 강화, 세션 분리, 동시 로그인
- **구현**: `admin` 가드 사용

#### 7.1.2 기본 Auth 기반 인증
```env
ADMIN_USE_GUARD=false
```
- **특징**: 기존 users 테이블과 연동
- **장점**: 간단한 설정, 기존 시스템과 호환
- **구현**: 기본 Auth + admin_users 테이블 체크

### 7.2 2FA (2단계 인증)

#### 7.2.1 설정 방법
```php
// 관리자별 2FA 설정
Route::prefix('{id}/2fa')->name('2fa.')->group(function () {
    Route::get('/setup', [AdminUser2FAController::class, 'setup']);
    Route::post('/enable', [AdminUser2FAController::class, 'enable']);
    Route::post('/disable', [AdminUser2FAController::class, 'disable']);
});
```

#### 7.2.2 Google Authenticator 연동
- **QR 코드 생성**: 관리자 설정 시 자동 생성
- **백업 코드**: 복구용 백업 코드 제공
- **실시간 검증**: TOTP 기반 실시간 인증

### 7.3 세션 관리

#### 7.3.1 세션 추적
```php
// 세션 정보 저장
AdminSession::create([
    'admin_user_id' => $adminId,
    'session_id' => session()->getId(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'last_activity' => now(),
]);
```

#### 7.3.2 세션 제어
- **세션 타임아웃**: 자동 로그아웃 설정
- **강제 로그아웃**: 관리자가 특정 세션 종료
- **동시 접속 제한**: 중복 로그인 제어

---

## 8. 권한 관리

### 8.1 역할 기반 접근 제어 (RBAC)

#### 8.1.1 관리자 등급
```php
// admin_levels 테이블
[
    'name' => 'Super',
    'code' => 'super',
    'can_list' => true,
    'can_create' => true,
    'can_read' => true,
    'can_update' => true,
    'can_delete' => true,
]
```

#### 8.1.2 권한 체크
```php
// 미들웨어에서 권한 체크
public function handle($request, Closure $next)
{
    $admin = auth('admin')->user();
    $level = $admin->level;
    
    if (!$level->can_list) {
        abort(403, '접근 권한이 없습니다.');
    }
    
    return $next($request);
}
```

### 8.2 권한 로그 시스템
- **권한 변경 로그**: 역할 변경 추적
- **접근 거부 로그**: 권한 없는 접근 시도 기록
- **권한 승인 로그**: 권한 부여/해제 기록

---

## 9. 로그 시스템

### 9.1 활동 로그 (Activity Log)
```php
AdminActivityLog::create([
    'admin_user_id' => $adminId,
    'action' => 'create',
    'module' => 'country',
    'description' => '새 국가 생성',
    'target_type' => 'admin_country',
    'target_id' => $countryId,
    'severity' => 'medium',
]);
```

### 9.2 감사 로그 (Audit Log)
```php
AdminAuditLog::create([
    'admin_id' => $adminId,
    'action' => 'update',
    'table_name' => 'admin_country',
    'record_id' => $countryId,
    'old_values' => $oldData,
    'new_values' => $newData,
    'description' => '국가 정보 수정',
]);
```

### 9.3 보안 로그 (Security Log)
- **로그인/로그아웃**: 인증 활동 기록
- **2FA 시도**: 2단계 인증 시도 기록
- **권한 거부**: 접근 거부 시도 기록

---

## 10. 레이아웃 시스템

### 10.1 레이아웃 계층 구조
```
jiny-admin::layouts.resource.{type}
    ↓
jiny-admin::layouts.resource.app
    ↓
jiny-admin::layouts.admin
```

### 10.2 CRUD 레이아웃

#### 10.2.1 목록 페이지 (`table.blade.php`)
```php
@extends('jiny-admin::layouts.resource.table')

@section('heading')
    <h1>국가 관리</h1>
    <p>시스템에서 사용되는 국가 정보를 관리합니다.</p>
@endsection

@section('content')
    <!-- 테이블 내용 -->
@endsection
```

#### 10.2.2 생성 페이지 (`create.blade.php`)
```php
@extends('jiny-admin::layouts.resource.create')

@section('heading')
    <h1>새 국가 추가</h1>
    <p>새로운 국가 정보를 입력하세요.</p>
@endsection

@section('content')
    <!-- 폼 내용 -->
@endsection
```

#### 10.2.3 상세 페이지 (`show.blade.php`)
```php
@extends('jiny-admin::layouts.resource.show')

@section('heading')
    <h1>국가 상세 정보</h1>
    <p>선택한 국가의 상세 정보를 확인합니다.</p>
@endsection

@section('content')
    <!-- 상세 정보 내용 -->
@endsection
```

#### 10.2.4 수정 페이지 (`edit.blade.php`)
```php
@extends('jiny-admin::layouts.resource.edit')

@section('heading')
    <h1>국가 정보 수정</h1>
    <p>선택한 국가의 정보를 수정합니다.</p>
@endsection

@section('content')
    <!-- 수정 폼 내용 -->
@endsection
```

### 10.3 공통 기능

#### 10.3.1 필터링 컴포넌트
```php
<x-admin::filters :route="$route">
    <x-ui::form-input name="search" label="검색" placeholder="국가명 또는 코드" />
    <x-ui::form-select name="continent" label="대륙">
        <option value="">전체</option>
        <option value="Asia">아시아</option>
        <option value="Europe">유럽</option>
    </x-ui::form-select>
</x-admin::filters>
```

#### 10.3.2 삭제 확인 모달
```php
<x-admin::modal-delete 
    :url="'admin.country'" 
    :rand-key="$randKey" />
```

---

## 11. 컴포넌트

### 11.1 메뉴 컴포넌트

#### 11.1.1 사이드 메뉴 (`side-menu.blade.php`)
```php
<x-admin::side-menu
    :top-menu="$topMenu"
    :bottom-menu="$bottomMenu"
    :menu-service="$menuService" />
```

#### 11.1.2 메뉴 드롭다운 (`menu-dropdown.blade.php`)
```php
<x-admin::menu-dropdown :id="$id" :active="$active">
    <x-slot name="trigger">
        <!-- 트리거 내용 -->
    </x-slot>
    <!-- 드롭다운 내용 -->
</x-admin::menu-dropdown>
```

#### 11.1.3 메뉴 아이템 (`menu-item.blade.php`)
```php
<x-admin::menu-item 
    :item="$item" 
    :depth="$depth" 
    :menu-service="$menuService" />
```

### 11.2 모달 컴포넌트

#### 11.2.1 배경 모달 (`backdrop.blade.php`)
```php
<x-admin::modal :id="$id" :size="$size">
    <!-- 모달 내용 -->
</x-admin::modal>
```

#### 11.2.2 삭제 확인 모달 (`modal-delete.blade.php`)
```php
<x-admin::modal-delete 
    :url="$url" 
    :rand-key="$randKey" />
```

### 11.3 필터 컴포넌트 (`filters.blade.php`)
```php
<x-admin::filters :route="$route">
    <!-- 필터 입력 필드들 -->
</x-admin::filters>
```

---

## 12. 개발 가이드

### 12.1 새로운 CRUD 모듈 생성

#### 12.1.1 1단계: 모델 생성
```bash
php artisan make:model AdminExample -m
```

#### 12.1.2 2단계: 마이그레이션 작성
```php
public function up()
{
    Schema::create('admin_examples', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}
```

#### 12.1.3 3단계: 컨트롤러 생성
```php
class AdminExampleController extends AdminResourceController
{
    protected $tableName = 'admin_examples';
    protected $moduleName = 'example';
    
    protected function _index(Request $request)
    {
        $examples = AdminExample::query()
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(20);
            
        return view('admin.example.index', compact('examples'));
    }
    
    // 다른 메소드들 구현...
}
```

#### 12.1.4 4단계: 라우트 등록
```php
// routes/admin.php
Route::prefix('example')->name('example.')->group(function () {
    Route::get('/', [AdminExampleController::class, 'index'])->name('index');
    Route::get('/create', [AdminExampleController::class, 'create'])->name('create');
    Route::post('/', [AdminExampleController::class, 'store'])->name('store');
    Route::get('/{id}', [AdminExampleController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [AdminExampleController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminExampleController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminExampleController::class, 'destroy'])->name('destroy');
});
```

#### 12.1.5 5단계: 뷰 생성
```php
// resources/views/admin/example/index.blade.php
@extends('jiny-admin::layouts.resource.table')

@section('heading')
    <h1>예제 관리</h1>
    <p>예제 데이터를 관리합니다.</p>
@endsection

@section('content')
    <x-admin::filters :route="$route">
        <x-ui::form-input name="search" label="검색" placeholder="이름" />
    </x-admin::filters>
    
    <x-ui::table-stripe>
        <!-- 테이블 내용 -->
    </x-ui::table-stripe>
@endsection
```

### 12.2 커스텀 컴포넌트 생성

#### 12.2.1 View 클래스 생성
```php
// View/CustomComponent.php
namespace Jiny\Admin\View;

use Illuminate\View\Component;

class CustomComponent extends Component
{
    public function render()
    {
        return view('jiny-admin::components.custom-component');
    }
}
```

#### 12.2.2 Blade 컴포넌트 등록
```php
// JinyAdminServiceProvider.php
Blade::component('admin::custom-component', \Jiny\Admin\View\CustomComponent::class);
```

### 12.3 미들웨어 생성

#### 12.3.1 커스텀 미들웨어
```php
// App/Http/Middleware/CustomAdminMiddleware.php
class CustomAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // 커스텀 로직
        return $next($request);
    }
}
```

#### 12.3.2 미들웨어 등록
```php
// JinyAdminServiceProvider.php
$router->aliasMiddleware('admin.custom', \App\Http\Middleware\CustomAdminMiddleware::class);
```

### 12.4 서비스 클래스 생성

#### 12.4.1 서비스 클래스
```php
// Services/CustomService.php
class CustomService
{
    public function doSomething()
    {
        // 비즈니스 로직
    }
}
```

#### 12.4.2 서비스 등록
```php
// JinyAdminServiceProvider.php
$this->app->singleton('admin.custom.service', function($app) {
    return new \Jiny\Admin\Services\CustomService();
});
```

---

## 📚 추가 리소스

### 문서
- [설치 가이드](setup.md)
- [CRUD 레이아웃 가이드](crud-layouts.md)
- [2FA 설정 가이드](auth/2fa-setup-guide.md)
- [권한 관리 가이드](permissions/permission-controller.md)

### 예제
- [CRUD 샘플](CRUD_SAMPLE.md)
- [CRUD 컨벤션](CRUD_CONVENTION.md)

### 개발 도구
- Artisan 명령어: `php artisan admin:user`
- 설정 발행: `php artisan vendor:publish --tag=jiny-admin-config`
- 플래그 발행: `php artisan vendor:publish --tag=jiny-admin-flags`

---

## 🤝 기여하기

이 패키지에 기여하고 싶으시다면:

1. 이슈를 등록하여 버그나 기능 요청을 알려주세요
2. Pull Request를 통해 코드 개선을 제안해주세요
3. 문서 개선이나 번역에 도움을 주세요

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다. 자세한 내용은 [LICENSE](license.md) 파일을 참조하세요. 