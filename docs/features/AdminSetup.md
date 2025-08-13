# AdminSetupController - 관리자 최초 설정 컨트롤러

## 개요

`AdminSetupController`는 시스템 최초 실행 시 관리자 계정을 생성하고 초기 설정을 담당하는 컨트롤러입니다. 이미 관리자가 존재하는 경우 접근을 제한하여 보안을 강화하고, 최초 슈퍼 관리자 계정 생성을 안전하게 처리합니다.

## 주요 기능

### 1. 시스템 초기화 상태 관리
- 데이터베이스에 관리자 계정 존재 여부 확인
- 최초 관리자 설정 페이지 접근 제어
- 중복 설정 방지 및 보안 강화

### 2. 최초 슈퍼 관리자 계정 생성
- 슈퍼 관리자 계정 생성 및 설정
- 비밀번호 규칙 검증 및 보안 강화
- 관리자 등급 및 권한 설정

### 3. 보안 및 접근 제어
- 설정 완료 후 접근 제한
- 권한 없는 사용자의 설정 페이지 접근 방지
- 안전한 계정 생성 프로세스

## 동작 과정

### 1. 설정 페이지 접근 처리
1. **접근 요청 수신**: `/admin/setup` 경로로 GET 요청
2. **상태 확인**: `admin_users` 테이블의 계정 수 확인
3. **접근 제어**: 
   - 관리자 있음 → 로그인 페이지로 리다이렉트
   - 관리자 없음 → 설정 페이지 표시

### 2. 슈퍼 관리자 계정 생성
1. **입력 데이터 검증**: 이름, 이메일, 비밀번호 유효성 검사
2. **비밀번호 규칙 검사**: 설정된 보안 규칙에 따른 검증
3. **계정 생성**: 슈퍼 관리자 계정 생성 및 저장
4. **완료 처리**: 로그인 페이지로 리다이렉트 및 성공 메시지

## API 엔드포인트

| 메소드 | 경로 | 설명 | 미들웨어 |
|--------|------|------|----------|
| GET | `/admin/setup` | 설정 페이지 표시 | `web` |
| POST | `/admin/setup/superadmin` | 슈퍼 관리자 계정 생성 | `web` |

## 뷰 경로

```php
protected string $setupView = 'jiny-admin::setup.setup2';
protected string $loginView = 'jiny-admin::auth.login';
```

## 설정 옵션

### 비밀번호 규칙 설정
```php
// config/admin.php
'auth' => [
    'password' => [
        'min_length' => 8,          // 최소 길이
        'require_special' => true,  // 특수문자 포함
        'require_number' => true,   // 숫자 포함
        'require_uppercase' => true // 대문자 포함
    ]
]
```

### 관리자 등급 설정
```php
// admin_levels 테이블에서 super 등급 조회
$superLevelId = DB::table('admin_levels')
    ->where('code', 'super')
    ->value('id');
```

## 데이터베이스 구조

### admin_users 테이블
```sql
CREATE TABLE admin_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'staff',
    admin_level_id BIGINT,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    is_active BOOLEAN DEFAULT TRUE,
    is_super_admin BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### admin_levels 테이블
```sql
CREATE TABLE admin_levels (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    badge_color VARCHAR(7) NOT NULL,
    can_create BOOLEAN DEFAULT FALSE,
    can_read BOOLEAN DEFAULT FALSE,
    can_update BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 보안 고려사항

### 1. 접근 제어
- 설정 완료 후 중복 접근 방지
- 권한 없는 사용자의 설정 페이지 접근 제한
- 안전한 계정 생성 프로세스

### 2. 비밀번호 보안
- 강력한 비밀번호 규칙 적용
- 비밀번호 해시화 저장
- 보안 규칙 검증 및 적용

### 3. 데이터 보호
- 민감한 정보 암호화
- 입력 데이터 검증 및 정제
- SQL 인젝션 방지

## 오류 처리

### 1. 유효성 검사 오류
```php
// 입력 데이터 유효성 검사
$validator = Validator::make($request->all(), [
    'name' => 'required|min:2',
    'email' => 'required|email|unique:admin_users,email',
    'password' => 'required|min:8|confirmed',
]);

if ($validator->fails()) {
    return redirect()->back()
        ->withErrors($validator)
        ->withInput();
}
```

### 2. 비밀번호 규칙 검사
```php
// 비밀번호 규칙 검사
$passwordRules = $this->config['auth']['password'] ?? [];
$password = $request->input('password');
$errors = [];

if (isset($passwordRules['min_length']) && strlen($password) < $passwordRules['min_length']) {
    $errors[] = '비밀번호는 최소 '.$passwordRules['min_length'].'자 이상이어야 합니다.';
}

if (!empty($passwordRules['require_special']) && !preg_match('/[\W_]/', $password)) {
    $errors[] = '비밀번호에 특수문자가 포함되어야 합니다.';
}

if ($errors) {
    return redirect()->back()
        ->withErrors($errors)
        ->withInput();
}
```

### 3. 데이터베이스 오류
```php
try {
    // 슈퍼 관리자 계정 생성
    DB::table('admin_users')->insert($userData);
} catch (\Exception $e) {
    \Log::error('슈퍼 관리자 계정 생성 실패', [
        'error' => $e->getMessage(),
        'user_data' => $request->except('password')
    ]);
    
    return redirect()->back()
        ->withErrors(['error' => '계정 생성 중 오류가 발생했습니다.'])
        ->withInput();
}
```

## 성능 최적화

### 1. 데이터베이스 쿼리 최적화
```php
// 인덱스를 활용한 효율적인 쿼리
$adminCount = DB::table('admin_users')
    ->selectRaw('COUNT(*) as count')
    ->where('status', 'active')
    ->value('count');
```

### 2. 캐싱 활용
```php
// 설정 정보 캐싱
$config = Cache::remember('admin_config', 3600, function () {
    return config('admin.settings');
});
```

### 3. 배치 처리
```php
// 여러 설정을 한 번에 처리
DB::transaction(function () use ($userData, $levelData) {
    // 관리자 등급 생성
    $levelId = DB::table('admin_levels')->insertGetId($levelData);
    
    // 슈퍼 관리자 계정 생성
    $userData['admin_level_id'] = $levelId;
    DB::table('admin_users')->insert($userData);
});
```

## 확장 가능성

### 1. 추가 설정 옵션
- 시스템 기본 설정
- 데이터베이스 초기화
- 기본 데이터 시드

### 2. 다중 관리자 지원
- 여러 슈퍼 관리자 생성
- 관리자 그룹 설정
- 권한 계층 구조

### 3. 설정 마법사
- 단계별 설정 가이드
- 설정 검증 및 확인
- 설정 완료 체크리스트

## 개발 가이드

### 1. 새로운 설정 옵션 추가
```php
// 설정 페이지에 새로운 옵션 추가
return view($this->setupView, [
    'passwordRules' => $this->config['auth']['password'] ?? [],
    'newOption' => $this->config['new_option'] ?? [],
    'customSettings' => $this->getCustomSettings(),
]);
```

### 2. 커스텀 검증 로직
```php
// 추가적인 검증 로직
private function performCustomValidation($data)
{
    $errors = [];
    
    // 커스텀 검증 규칙
    if (!$this->validateCustomRule($data)) {
        $errors[] = '커스텀 규칙 검증에 실패했습니다.';
    }
    
    return $errors;
}
```

### 3. 설정 완료 후처리
```php
// 설정 완료 후 추가 처리
private function performPostSetupActions($adminUser)
{
    // 기본 데이터 생성
    $this->createDefaultData($adminUser);
    
    // 설정 완료 이벤트 발생
    event(new AdminSetupCompleted($adminUser));
    
    // 알림 발송
    $this->sendSetupCompletionNotification($adminUser);
}
```

## 테스트 시나리오

### 1. 정상 설정 테스트
- 설정 페이지 접근 및 표시
- 슈퍼 관리자 계정 생성
- 설정 완료 후 접근 제한

### 2. 보안 테스트
- 설정 완료 후 중복 접근 방지
- 권한 없는 사용자의 접근 제한
- 비밀번호 규칙 검증

### 3. 오류 처리 테스트
- 유효성 검사 실패 시 처리
- 데이터베이스 오류 시 처리
- 중복 이메일 처리

## 관련 파일

- **컨트롤러**: `jiny/admin/app/Http/Controllers/Auth/AdminSetupController.php`
- **라우트**: `jiny/admin/routes/web.php`
- **뷰**: `jiny/admin/resources/views/setup/setup2.blade.php`
- **설정**: `config/admin.php`
- **테스트**: `jiny/admin/tests/Feature/Auth/AdminSetupTest.php`

## 배포 고려사항

### 1. 환경별 설정
- 개발/스테이징/운영 환경별 설정 분리
- 환경변수를 통한 설정 관리
- 설정 파일 캐싱 활용

### 2. 보안 설정
- HTTPS 강제 적용
- 보안 헤더 설정
- 접근 로그 기록

### 3. 백업 및 복구
- 설정 데이터 백업
- 장애 발생 시 복구 절차
- 롤백 계획 수립

## 모니터링 및 로깅

### 1. 설정 활동 로그
```php
// 설정 활동 기록
\Log::info('관리자 설정 완료', [
    'admin_email' => $request->input('email'),
    'admin_name' => $request->input('name'),
    'ip_address' => $request->ip(),
    'user_agent' => $request->header('User-Agent'),
]);
```

### 2. 성능 메트릭
```php
// 설정 처리 시간 측정
$startTime = microtime(true);
// 설정 처리 로직
$processingTime = microtime(true) - $startTime;

\Log::info('설정 처리 시간', [
    'processing_time' => $processingTime,
    'admin_email' => $request->input('email')
]);
```

### 3. 오류 모니터링
```php
// 오류 발생 시 상세 로그 기록
\Log::error('관리자 설정 오류', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'request_data' => $request->except('password'),
    'ip_address' => $request->ip()
]);
```
