# 관리자 CRUD 레이아웃 가이드

## 개요

관리자 패널에서 CRUD(Create, Read, Update, Delete) 작업을 수행할 때 사용되는 레이아웃 시스템에 대한 가이드입니다.

## 레이아웃 구조

### 1. 기본 레이아웃 계층

```
jiny-admin::layouts.resource.{type}
    ↓
jiny-admin::layouts.resource.app
    ↓
jiny-admin::layouts.admin
```

### 2. CRUD 페이지별 레이아웃

| 페이지 타입 | 레이아웃 경로 | 설명 |
|------------|--------------|------|
| 목록 (Index) | `jiny-admin::layouts.resource.table` | 데이터 목록 표시 |
| 생성 (Create) | `jiny-admin::layouts.resource.create` | 새 데이터 입력 |
| 상세 (Show) | `jiny-admin::layouts.resource.show` | 데이터 상세 정보 |
| 수정 (Edit) | `jiny-admin::layouts.resource.edit` | 데이터 수정 |

## 레이아웃 상세 분석

### 1. Index 페이지 (`table.blade.php`)

**사용법:**
```php
@extends('jiny-admin::layouts.resource.table')
```

**특징:**
- 데이터 목록을 테이블 형태로 표시
- 필터링, 검색, 정렬 기능 포함
- 페이지네이션 지원
- 일괄 삭제 기능
- CSV 다운로드 기능

**필수 섹션:**
- `@section('heading')` - 페이지 제목과 설명
- `@section('content')` - 테이블 내용

### 2. Create 페이지 (`create.blade.php`)

**사용법:**
```php
@extends('jiny-admin::layouts.resource.create')
```

**특징:**
- 새 데이터 입력 폼
- AJAX 기반 제출 처리
- 실시간 유효성 검사
- 백드롭과 스피너 표시

**필수 섹션:**
- `@section('heading')` - 페이지 제목과 설명
- `@section('content')` - 폼 내용

### 3. Show 페이지 (`show.blade.php`)

**사용법:**
```php
@extends('jiny-admin::layouts.resource.show')
```

**특징:**
- 데이터 상세 정보 표시
- 읽기 전용 모드
- 수정/삭제 버튼 포함
- 관련 데이터 표시

**필수 섹션:**
- `@section('heading')` - 페이지 제목과 설명
- `@section('content')` - 상세 정보 내용

### 4. Edit 페이지 (`edit.blade.php`)

**사용법:**
```php
@extends('jiny-admin::layouts.resource.edit')
```

**특징:**
- 기존 데이터 수정 폼
- AJAX 기반 제출 처리
- 삭제 기능 포함
- 실시간 유효성 검사

**필수 섹션:**
- `@section('heading')` - 페이지 제목과 설명
- `@section('content')` - 폼 내용

## 공통 컴포넌트

### 1. 사이드메뉴 (`sidemenu.blade.php`)
- 네비게이션 메뉴
- 현재 페이지 하이라이트
- 권한 기반 메뉴 표시

### 2. 헤더 (`header.blade.php`)
- 페이지 제목
- 브레드크럼
- 사용자 정보
- 알림 메시지

### 3. 페이지네이션 (`pagenation.blade.php`)
- 데이터 페이지 분할
- 페이지 번호 표시
- 이전/다음 버튼

## JavaScript 기능

### 1. AJAX 처리
- 폼 제출 시 백드롭 표시
- 실시간 유효성 검사
- 성공/실패 메시지 표시

### 2. 필터링
- 실시간 검색
- 고급 검색 옵션
- 필터 초기화

### 3. 삭제 확인
- 난수키 기반 삭제 확인
- 모달 다이얼로그
- 안전한 삭제 처리

## 스타일링

### 1. Tailwind CSS
- 반응형 디자인
- 일관된 색상 체계
- 접근성 고려

### 2. 컴포넌트
- `x-ui::` 네임스페이스 사용
- 재사용 가능한 컴포넌트
- 일관된 UI/UX

## 권한 시스템

### 1. 메뉴 권한
- 사용자 레벨에 따른 메뉴 표시
- 권한 없는 기능 숨김 처리

### 2. 기능 권한
- CRUD 작업별 권한 확인
- 권한 없는 작업 차단

## 모범 사례

### 1. 컨트롤러 구조
```php
class AdminUserController extends AdminResourceController
{
    public function index()
    {
        // 데이터 조회
        $users = AdminUser::paginate(20);
        
        return view('jiny-admin::admin.users.index', [
            'rows' => $users,
            'route' => 'admin.users.'
        ]);
    }
}
```

### 2. 뷰 파일 구조
```php
@extends('jiny-admin::layouts.resource.table')

@section('heading')
    <div class="w-full">
        <div class="sm:flex sm:items-end justify-between">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">사용자 관리</h1>
                <p class="mt-2 text-base text-gray-700">시스템 사용자를 관리합니다.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <x-ui::button-primary href="{{ route($route.'create') }}">
                    새 사용자 추가
                </x-ui::button-primary>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- 테이블 내용 -->
@endsection
```

### 3. 필터 컴포넌트
```php
@section('filters')
    <x-ui::grid col="3">
        <div>
            <x-ui::form-input 
                name="filter_name" 
                label="이름" 
                placeholder="이름으로 검색" />
        </div>
        <div>
            <x-ui::form-listbox 
                name="filter_status" 
                label="상태" 
                :options="['active' => '활성', 'inactive' => '비활성']" />
        </div>
    </x-ui::grid>
@endsection
```

## 주의사항

### 1. 레이아웃 선택
- 올바른 레이아웃 경로 사용
- 네임스페이스 확인 (`jiny-admin::`)

### 2. 섹션 정의
- 필수 섹션 반드시 정의
- 섹션 이름 정확히 작성

### 3. 권한 처리
- 컨트롤러에서 권한 확인
- 뷰에서 권한에 따른 UI 처리

### 4. JavaScript 의존성
- 필요한 스크립트 로드
- 이벤트 리스너 등록

## 디버깅

### 1. 레이아웃 오류
- 레이아웃 파일 존재 확인
- 네임스페이스 경로 확인

### 2. 섹션 오류
- 필수 섹션 정의 확인
- 섹션 이름 오타 확인

### 3. 권한 오류
- 사용자 권한 확인
- 컨트롤러 권한 체크 확인

## 참고 자료

- [Laravel Blade 템플릿](https://laravel.com/docs/blade)
- [Tailwind CSS](https://tailwindcss.com/)
- [관리자 권한 시스템](./permissions.md)
- [컨트롤러 가이드](./controllers.md) 