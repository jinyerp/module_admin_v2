# AdminPermissionController 안내

이 문서는 `AdminPermissionController`의 주요 기능과 도메인 지식, 그리고 실제 데이터베이스 테이블(`admin_permissions`) 구조에 대해 상세히 설명합니다.

## 개요
`AdminPermissionController`는 관리자 권한(Permission) 관리 기능을 제공하는 컨트롤러입니다. 이 컨트롤러는 권한의 생성, 조회, 수정, 삭제, 대량 삭제, CSV 다운로드 등 권한 관리에 필요한 CRUD 및 부가 기능을 담당합니다.

---

## 데이터베이스 테이블 구조

`admin_permissions` 테이블은 시스템 내 모든 권한을 정의합니다. 주요 컬럼은 다음과 같습니다.

| 컬럼명         | 타입         | 설명                       | 제약조건 및 인덱스         |
|---------------|--------------|----------------------------|----------------------------|
| id            | bigint       | PK, 권한 고유 ID           | auto increment, PK         |
| name          | string(255)  | 권한명 (예: user.create)   | unique, not null           |
| display_name  | string(255)  | 표시명 (예: 사용자 생성)   | not null                   |
| description   | text         | 권한 설명                  | nullable                   |
| module        | string(255)  | 모듈명 (예: user)          | not null, index            |
| is_active     | boolean      | 활성화 상태                | default true, index        |
| sort_order    | integer      | 정렬 순서                  | default 0, index           |
| created_at    | timestamp    | 생성일                     |                            |
| updated_at    | timestamp    | 수정일                     |                            |

- **인덱스:** `module`, `is_active`, `sort_order` 컬럼에 인덱스가 생성되어 있어, 모듈별/활성화별/정렬순서별 조회가 빠릅니다.
- **제약조건:** `name`은 유일(unique)해야 하며, 중복 등록이 불가합니다.

---

## 주요 기능 설명

### 1. 목록 조회 (`index`)
- **설명:** 등록된 모든 권한을 페이징하여 목록으로 조회합니다.
- **정렬:** `sort`, `direction` 파라미터로 정렬 기준/방향 지정 (기본값: 생성일 내림차순)
- **DB 매핑:** `AdminPermission::query()->orderBy($sort, $dir)->paginate(20)`
- **반환:** 권한 목록 뷰(`jiny-admin::permissions.index`)
- **활용:** 인덱스가 적용된 컬럼(`module`, `is_active`, `sort_order`)을 활용한 필터링/정렬이 가능합니다.

### 2. 생성 폼 (`create`)
- **설명:** 새로운 권한을 등록하기 위한 입력 폼을 반환합니다.
- **입력 필드:** name, display_name, module, description, is_active, sort_order
- **반환:** 생성 폼 뷰(`jiny-admin::permissions.create`)

### 3. 저장 (`store`)
- **설명:** 입력받은 권한 정보를 검증 후 DB에 저장합니다.
- **검증:**
  - `name`: 필수, 고유, 100자 이하 (DB에서는 unique index)
  - `display_name`: 필수, 100자 이하
  - `module`: 선택, 50자 이하 (DB에서는 not null, 실제 운영시 빈 문자열 허용 가능)
  - `description`: 선택, 255자 이하 (DB에서는 text, nullable)
  - `is_active`: boolean (DB에서는 default true)
  - `sort`: 정수, 선택 (DB에서는 sort_order, default 0)
- **DB 매핑:** `AdminPermission::create($validated)`
- **반환:** 목록 페이지로 리다이렉트 및 성공 메시지

### 4. 상세 조회 (`show`)
- **설명:** 특정 권한의 상세 정보를 조회합니다.
- **DB 매핑:** `AdminPermission::findOrFail($id)`
- **반환:** 상세 뷰(`jiny-admin::permissions.show`)

### 5. 수정 폼 (`edit`)
- **설명:** 특정 권한의 수정 폼을 반환합니다.
- **DB 매핑:** `AdminPermission::findOrFail($id)`
- **반환:** 수정 폼 뷰(`jiny-admin::permissions.edit`)

### 6. 갱신 (`update`)
- **설명:** 수정된 권한 정보를 검증 후 DB에 반영합니다.
- **검증:** 저장과 동일, 단 name은 본인 제외 고유
- **DB 매핑:** `$row->update($validated)`
- **반환:** 목록 페이지로 리다이렉트 및 성공 메시지

### 7. 삭제 (`destroy`)
- **설명:** 특정 권한을 삭제합니다.
- **DB 매핑:** `$row->delete()`
- **반환:** 목록 페이지로 리다이렉트 및 성공 메시지

### 8. 대량 삭제 (`bulkDelete`)
- **설명:** 선택된 여러 권한을 한 번에 삭제합니다.
- **입력:** `ids` 배열 (삭제할 권한 ID 목록)
- **DB 매핑:** `AdminPermission::whereIn('id', $ids)->delete()`
- **반환:** 목록 페이지로 리다이렉트 및 성공 메시지

### 9. CSV 다운로드 (`downloadCsv`)
- **설명:** 모든 권한 데이터를 CSV 파일로 다운로드합니다.
- **출력:** CSV 파일(`permissions.csv`)
- **컬럼:** ID, 권한명(name), 표시명(display_name), 모듈(module), 설명(description), 활성화(is_active), 정렬(sort_order), 생성일, 수정일
- **DB 매핑:** `AdminPermission::all()`

---

## 도메인 지식

### 권한(Permission) 관리
- **권한**은 시스템 내에서 사용자의 접근 및 행위 제어를 위해 정의됩니다.
- 각 권한은 이름(`name`), 표시명(`display_name`), 모듈(`module`), 설명(`description`), 활성화 여부(`is_active`), 정렬(`sort_order`) 등의 속성을 가집니다.
- 권한은 관리자 기능, 메뉴, API 등 다양한 시스템 리소스에 연결될 수 있습니다.
- **모듈(module):** 권한이 속하는 기능 영역(예: user, admin, product 등)으로, 대규모 시스템에서 권한을 그룹화하는 데 사용됩니다.
- **정렬 순서(sort_order):** UI에서 권한 목록을 정렬할 때 사용됩니다.
- **활성화(is_active):** 권한의 사용 여부를 제어합니다. 비활성화된 권한은 실제 시스템에서 무시될 수 있습니다.

### CRUD 패턴
- 본 컨트롤러는 전형적인 CRUD(생성, 조회, 수정, 삭제) 패턴을 따릅니다.
- 추가적으로 대량 삭제, CSV 다운로드 등 관리 편의 기능을 제공합니다.

### 대량 삭제
- 여러 권한을 한 번에 삭제할 수 있어, 대규모 권한 관리에 효율적입니다.
- 인덱스가 적용된 컬럼을 활용하면 대량 삭제 시 성능 저하를 방지할 수 있습니다.

### CSV 다운로드
- 권한 데이터를 외부로 내보내어 백업, 분석, 이관 등에 활용할 수 있습니다.
- CSV에는 DB의 모든 주요 컬럼이 포함되어, 데이터 이관 및 외부 시스템 연동에 용이합니다.

---

## 관련 뷰(View)
- `jiny-admin::permissions.index` : 권한 목록
- `jiny-admin::permissions.create` : 권한 생성 폼
- `jiny-admin::permissions.edit` : 권한 수정 폼
- `jiny-admin::permissions.show` : 권한 상세

---

## 참고
- 이 컨트롤러는 `Jiny\Admin\Models\AdminPermission` 모델을 사용합니다.
- 라우트 네이밍은 `admin.admin.permissions.` 접두어를 사용합니다.
- 실제 라우트 및 뷰 파일 위치는 프로젝트 구조에 따라 다를 수 있습니다.
- DB 마이그레이션 파일: `2025_07_14_000002_create_admin_permissions_system_tables.php`를 참고하여 테이블 구조를 확인할 수 있습니다. 