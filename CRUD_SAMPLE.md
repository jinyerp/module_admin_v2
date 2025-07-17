# Jiny-Admin CRUD 샘플 코드 (Sample)

이 문서는 CRUD_CONVENTION.md의 규칙을 실제 코드로 구현한 예시입니다.

---

## 1. 마이그레이션 예시 (database/migrations/2025_07_14_000001_create_admin_users_table.php)

```php
// 실제 파일 전체 복사
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('type')->default('admin')->comment('관리자 등급(super, admin, staff 등)');
            $table->string('status')->default('active')->comment('상태(active, inactive, suspended 등)');
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->text('memo')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
```

---

## 2. 모델 예시 (Jiny\Admin\Models\AdminUser)

```php
// 실제 파일 전체 복사
<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 관리자 회원 모델
 *
 * - 관리자 전용 인증 및 정보 관리
 * - 슈퍼관리자(super), 일반관리자(admin), 스태프(staff) 등 다양한 등급 지원
 * - 별도의 admin_users 테이블을 사용하여 보안 및 관리 분리
 */
class AdminUser extends Authenticatable
{
    /**
     * 테이블명
     * @var string
     */
    protected $table = 'admin_users';

    /**
     * PK 타입 및 auto-increment 사용
     */
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    /**
     * 대량 할당 가능 필드
     * @var array
     */
    protected $fillable = [
        'name', // 관리자 이름
        'email', // 관리자 이메일(로그인)
        'password', // 비밀번호(해시)
        'type', // 관리자 등급(super, admin, staff 등)
        'status', // 계정 상태(active, inactive, suspended 등)
        'last_login_at', // 마지막 로그인 일시
        'login_count', // 로그인 횟수
        'is_verified', // 이메일 인증 여부
        'email_verified_at', // 이메일 인증 일시
        'phone', // 연락처(선택)
        'avatar', // 프로필 이미지(선택)
        'memo', // 관리자 메모(선택)
        'remember_token' // 자동 로그인 토큰
    ];

    /**
     * 숨김 처리 필드
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * 타입 캐스팅
     * @var array
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    // === 도메인 지식 ===
    // - 관리자 등급(type):
    //   * super: 시스템 전체 권한, 모든 관리자 관리 가능
    //   * admin: 일반 관리 권한, 일부 시스템 설정 가능
    //   * staff: 제한적 관리 권한, 주로 운영 지원
    // - status: active(활성), inactive(비활성), suspended(정지)
    // - is_verified: 이메일 인증 여부(보안 강화)
    // - login_count, last_login_at: 보안 모니터링 및 감사 용도
    // - memo: 내부 관리용 메모(예: 권한 변경 이력 등)
}
```

---

## 3. 컨트롤러 예시 (Jiny\Admin\Http\Controllers\Admin\AdminUserController)

```php
// 실제 파일 전체 복사
// (파일이 길어 일부 생략 가능, 실제 프로젝트에서는 전체 복사 권장)
<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Jiny\Admin\Models\AdminUser;

class AdminUserController extends Controller
{
    // ... (전체 코드 복사)
}
```

---

## 4. 라우트 예시 (routes/web.php)

```php
// 실제 users 라우트 그룹 전체 복사
Route::prefix('admin/admin/users')->name('users.')->group(function () {
    Route::get('/', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'index'])->name('index'); // 목록 출력
    Route::get('/create', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'create'])->name('create'); // 생성 폼
    Route::post('/', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'store'])->name('store'); // 저장
    Route::get('/{id}', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'show'])->name('show')->where('id', '[0-9]+'); // 상세 조회
    Route::get('/{id}/edit', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'edit'])->name('edit')->where('id', '[0-9]+'); // 수정폼
    Route::put('/{id}', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'update'])->name('update')->where('id', '[0-9]+'); // 갱신
    Route::delete('/{id}', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'destroy'])->name('destroy')->where('id', '[0-9]+'); // 삭제
    Route::post('/bulk-delete', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'bulkDelete'])->name('bulk-delete'); // 선택 삭제
    // CSV 다운로드 라우트 추가
    Route::get('/download-csv', [
        \Jiny\Admin\Http\Controllers\Admin\AdminUserController::class,
        'downloadCsv'])->name('downloadCsv');
});
```

---

## 5. 뷰 예시 (resources/views/users/*.blade.php)

> 아래 코드는 실제 resources/views/users/의 모든 Blade 파일(index, create, edit, show, filters, errors, message 등) 전체를 복사하여, 컬럼/입력요소만 바꿔서 사용합니다. 내부의 자바스크립트, UI, 모달, 필터, 에러, 메시지 등 모든 기능을 반드시 유지합니다.

### index.blade.php
```blade
// ... 실제 resources/views/users/index.blade.php 전체 코드 복사 ...
```

### create.blade.php
```blade
// ... 실제 resources/views/users/create.blade.php 전체 코드 복사 ...
```

### edit.blade.php
```blade
// ... 실제 resources/views/users/edit.blade.php 전체 코드 복사 ...
```

### show.blade.php
```blade
// ... 실제 resources/views/users/show.blade.php 전체 코드 복사 ...
```

### filters.blade.php
```blade
// ... 실제 resources/views/users/filters.blade.php 전체 코드 복사 ...
```

### errors.blade.php
```blade
// ... 실제 resources/views/users/errors.blade.php 전체 코드 복사 ...
```

### message.blade.php
```blade
// ... 실제 resources/views/users/message.blade.php 전체 코드 복사 ...
```

---

이 샘플을 복사/수정하여 새로운 CRUD를 만들면 규칙에 맞는 일관된 결과를 얻을 수 있습니다. 