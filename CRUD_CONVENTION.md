# Jiny-Admin CRUD 생성 규칙 (Convention)

이 문서는 jiny-admin 기반의 Laravel 프로젝트에서 CRUD(생성/조회/수정/삭제) 코드를 자동화/반복 생성할 때 반드시 따라야 할 규칙(Convention)을 정리한 문서입니다.

---

## 1. 전체 샘플/규칙의 기준
- 컨트롤러, 모델, 마이그레이션, 라우트, 뷰(Blade)는 반드시 실제 `AdminUser` 관련 전체 코드를 복사하여, 도메인/컬럼/입력요소만 변경해서 사용합니다.
- **컨트롤러/모델 등 모든 클래스의 네임스페이스는 반드시 `Jiny\Admin\~` 규칙을 적용합니다.**
    - 예시: `namespace Jiny\Admin\Http\Controllers;`, `namespace Jiny\Admin\Models;`
- **컨트롤러는 반드시 `use App\Http\Controllers\Controller;`를 상단에 추가하여 라라벨 기본 컨트롤러를 상속합니다.**
    - 예시: `class AdminUserController extends Controller`
- Blade 파일은 내부의 자바스크립트, UI 제어 코드, 모달, 필터, 에러, 메시지 등 모든 기능을 포함하여 복사합니다.
- Blade 파일 작업 시 반드시 users의 index/create/edit/show/errors/filters/message 등 전체 샘플 파일을 복사한 후, 도메인/컬럼/입력요소만 변경하는 방식으로 작업해야 합니다. (레이아웃, 자바스크립트, include, 팝업 등 모든 기능 유지) filters.blade.php도 예외 없이 users/filters.blade.php를 복사 후 도메인에 맞게 수정해야 하며, 검색/초기화 버튼은 직접 만들지 않고 반드시 <x-admin::filters :route="$route"> 컴포넌트에만 의존해야 합니다. (입력 필드만 남기고 버튼은 제거)
- 라우트는 `/admin/admin/users`와 같이 실제 사용 중인 경로/네임스페이스/네이밍을 그대로 샘플로 삼습니다.
- 일부만 발췌/생략하지 않고, 전체 코드를 복사하여 새로운 CRUD를 만들 때 컬럼/도메인만 바꿉니다.
- 컨트롤러의 view() 데이터 전달은 compact 대신 연상배열을 사용하며, 목록 데이터는 항상 rows 변수명으로 통일하여 전달합니다. blade에서도 rows로 받아야 하며, index.blade.php 등 목록 테이블을 생성하는 foreach 문에서는 반드시 $rows를 기반으로 반복해야 합니다.
- Blade에서 `$route` 변수 사용, 네임스페이스(`jiny-admin::users.*`) 일관 적용을 반드시 지킵니다.
- index.blade.php 등에서는 CSV 다운로드 버튼을 직접 추가하지 않고, 반드시 x-admin::filters 컴포넌트 내부에만 존재해야 하며, 상단에 별도로 만들지 않아야 합니다.
- index.blade.php 등에서는 테이블 하단에 '대량 삭제' 버튼을 직접 추가하지 않으며, 선택 삭제 등은 별도 관리하므로 이곳에서는 버튼/기능을 제공하지 않아야 합니다.

## 2. 마이그레이션/테이블명
- 테이블명은 **snake_case 복수형**으로 작성한다. (예: `admin_users`)

## 3. 컨트롤러
- 네임스페이스는 반드시 `Jiny\Admin\Http\Controllers` 또는 하위(Logs 등)로 작성한다.
- **반드시 상단에 `use App\Http\Controllers\Controller;`를 추가하고, `extends Controller`로 라라벨 기본 컨트롤러를 상속한다.**
- users 컨트롤러 샘플 전체를 복사해, 도메인/컬럼/입력요소만 변경한다.

## 4. 모델
- 네임스페이스는 반드시 `Jiny\Admin\Models`로 작성한다.
- users 모델 샘플 전체를 복사해, 도메인/컬럼/입력요소만 변경한다.

## 5. 라우트
- Prefix: `/admin/admin/{테이블명-kebab-case}`
- 네임스페이스: `admin.admin.{테이블명-kebab-case}.` (예: `admin.admin.users.`)
- CRUD 라우트 네임: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `bulk-delete`, `downloadCsv` 등
- 예시: `route('admin.admin.users.index')`
- 라우트 네이밍은 반드시 kebab-case(예: bulk-delete)로 작성하며, blade 등에서 route() 호출 시에도 kebab-case로 일치시켜야 합니다.

## 6. 뷰(Blade)
- 뷰 파일 경로: `resources/views/{테이블명-kebab-case}/`
- 네임스페이스: `jiny-admin::{테이블명-kebab-case}.*` (예: `jiny-admin::users.index`)
- 모든 뷰에서 `$route` 변수를 사용하여 라우트 호출
- CRUD 파일: `index.blade.php`, `show.blade.php`, `create.blade.php`, `edit.blade.php`, `filters.blade.php`, `message.blade.php`, `errors.blade.php`
- Blade 파일은 내부의 JS, UI, 모달, 필터, 에러, 메시지 등 모든 기능을 포함하여 복사합니다.

## 7. 네이밍 규칙
- 테이블명: snake_case 복수형
- 모델명: PascalCase 단수형
- 컨트롤러명: PascalCase + Controller
- 라우트 네임: kebab-case
- 뷰 네임스페이스: kebab-case

## 8. 변수/관계명
- 외래키: `{참조모델}_id` (예: `admin_user_id`)
- 관계명: `adminUser()` (예: belongsTo)
- 컨트롤러에서 뷰로 `$route` 항상 전달

## 9. 기타
- 컨트롤러에서 view() 호출 시 항상 `jiny-admin::` 네임스페이스 사용
- 뷰에서 route() 호출 시 항상 `$route` 변수 사용
- CRUD 생성 시 위 규칙을 반드시 준수

## 10. view() 데이터 전달 방식
- 컨트롤러에서 view()에 데이터를 전달할 때는 compact() 대신 연상배열(['key' => $value, ...])로 전달하는 것을 권장합니다.
- 예시: return view('jiny-admin::users.index', ['rows' => $rows, 'route' => $route]);

---

이 규칙을 따르면, 반복적이고 일관된 CRUD 코드 자동화가 가능합니다. 