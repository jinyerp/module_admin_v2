# AdminLevel 관리 기능

## 개요
관리자 등급(AdminLevel) 관리 기능은 시스템 내에서 관리자들의 권한을 체계적으로 관리하기 위한 핵심 기능입니다. 각 등급별로 CRUD 권한을 설정하고, 사용자에게 적절한 등급을 할당하여 보안을 강화합니다.

## 주요 기능

### 1. 등급 관리
- **등급 생성**: 새로운 관리자 등급을 생성하고 권한을 설정
- **등급 수정**: 기존 등급의 정보와 권한을 수정
- **등급 삭제**: 사용하지 않는 등급을 삭제 (사용 중인 등급은 삭제 불가)
- **등급 조회**: 등급 목록과 상세 정보를 조회

### 2. 권한 관리
- **CRUD 권한**: 각 등급별로 Create, Read, Update, Delete 권한을 개별 설정
- **권한 토글**: 권한을 쉽게 활성화/비활성화할 수 있는 토글 기능
- **권한 검증**: 사용자의 권한을 실시간으로 검증하고 로깅

### 3. 정렬 및 필터링
- **정렬 순서**: 등급의 표시 순서를 사용자가 정의
- **필터링**: 이름, 코드, 색상, 권한별로 등급을 필터링
- **검색**: 등급명과 코드로 빠른 검색

### 4. 통계 및 모니터링
- **사용 현황**: 각 등급별로 할당된 사용자 수 표시
- **권한 로그**: 모든 권한 관련 작업을 상세하게 로깅
- **감사 추적**: 등급 변경 사항을 추적하여 보안 강화

## 데이터 모델

### AdminLevel 테이블 구조
```sql
admin_levels
├── id (Primary Key)
├── name (등급명)
├── code (등급 코드)
├── badge_color (배지 색상)
├── can_create (생성 권한)
├── can_read (조회 권한)
├── can_update (수정 권한)
├── can_delete (삭제 권한)
├── sort_order (정렬 순서)
├── created_at (생성일시)
└── updated_at (수정일시)
```

### 권한 필드 설명
- **can_create**: 새로운 리소스를 생성할 수 있는 권한
- **can_read**: 기존 리소스를 조회할 수 있는 권한
- **can_update**: 기존 리소스를 수정할 수 있는 권한
- **can_delete**: 기존 리소스를 삭제할 수 있는 권한

## 등급 체계

### 기본 등급 유형
1. **Super**: 모든 권한을 가진 최고 관리자
2. **Admin**: 일반 관리자 (제한된 권한)
3. **Staff**: 일반 직원 (최소 권한)

### 권한 상속 구조
```
Super Admin
├── 모든 권한 허용
├── 등급 관리
└── 사용자 관리

Admin
├── 제한된 CRUD 권한
├── 자신의 등급 내에서만 작업
└── 권한 로그 조회

Staff
├── 읽기 권한만
├── 제한된 리소스 접근
└── 기본적인 모니터링
```

## 보안 기능

### 1. 권한 검증
- 모든 요청에 대해 사용자의 권한을 검증
- 권한이 없는 작업은 즉시 차단
- 권한 검증 실패 시 상세한 로그 기록

### 2. 감사 추적
- 모든 등급 변경 사항을 상세하게 기록
- 변경 전후 데이터를 비교하여 추적
- IP 주소와 사용자 에이전트 정보 기록

### 3. 권한 로깅
- 권한 체크 성공/실패를 모두 로깅
- 권한 거부 시 상세한 이유 기록
- 보안 사고 발생 시 빠른 대응 가능

## 사용자 인터페이스

### 1. 등급 목록 페이지
- 등급별 사용자 수 표시
- 권한 상태를 시각적으로 표현
- 빠른 편집 및 삭제 기능

### 2. 등급 생성/수정 폼
- 직관적인 권한 설정 UI
- 실시간 유효성 검사
- 권한 미리보기 기능

### 3. 권한 토글 스위치
- 각 권한을 개별적으로 토글
- 즉시 반영되는 권한 변경
- 변경 사항 확인 대화상자

## API 엔드포인트

### RESTful API 구조
```
GET    /admin/levels          - 등급 목록 조회
GET    /admin/levels/create   - 등급 생성 폼
POST   /admin/levels          - 등급 생성
GET    /admin/levels/{id}     - 등급 상세 조회
GET    /admin/levels/{id}/edit - 등급 수정 폼
PUT    /admin/levels/{id}     - 등급 수정
DELETE /admin/levels/{id}     - 등급 삭제
POST   /admin/levels/bulk-delete - 일괄 삭제
POST   /admin/levels/{id}/toggle-permission - 권한 토글
POST   /admin/levels/update-order - 정렬 순서 업데이트
GET    /admin/levels/stats    - 통계 정보
```

### 응답 형식
```json
{
  "success": true,
  "message": "등급이 성공적으로 생성되었습니다.",
  "data": {
    "id": 1,
    "name": "일반 관리자",
    "code": "admin",
    "permissions": {
      "can_create": true,
      "can_read": true,
      "can_update": true,
      "can_delete": false
    }
  }
}
```

## 비즈니스 로직

### 1. 등급 생성 규칙
- 등급 코드는 고유해야 함
- 등급명은 필수 입력 항목
- 기본 권한은 모두 false로 설정
- 정렬 순서는 자동으로 최하위에 배치

### 2. 등급 수정 제한
- 사용 중인 등급의 코드는 변경 불가
- 권한 변경은 즉시 반영
- 수정 이력은 감사 로그에 기록

### 3. 등급 삭제 제한
- 사용 중인 등급은 삭제 불가
- 삭제 전 사용자 수 확인
- 일괄 삭제 시 안전성 검증

### 4. 권한 상속 규칙
- Super 등급은 모든 권한을 자동으로 가짐
- 하위 등급은 상위 등급의 권한을 상속받지 않음
- 각 등급의 권한은 독립적으로 설정

## 에러 처리

### 1. 유효성 검사 오류
```json
{
  "success": false,
  "errors": {
    "name": ["등급명을 입력해주세요."],
    "code": ["이미 존재하는 등급코드입니다."]
  }
}
```

### 2. 권한 오류
```json
{
  "success": false,
  "message": "등급 수정 권한이 없습니다."
}
```

### 3. 비즈니스 로직 오류
```json
{
  "success": false,
  "message": "사용 중인 등급은 삭제할 수 없습니다."
}
```

## 테스트

### 테스트 실행 방법
```bash
# 전체 등급 관리 테스트 실행
php artisan test jiny/admin/tests/Feature/Admin/AdminLevelTest.php

# 특정 테스트 메소드만 실행
php artisan test --filter test_level_creation
```

### 테스트 커버리지
- 등급 CRUD 작업 테스트
- 권한 검증 테스트
- 유효성 검사 테스트
- 비즈니스 로직 테스트
- API 응답 형식 테스트

## 성능 최적화

### 1. 데이터베이스 최적화
- 인덱스 설정: `code`, `sort_order` 필드
- 쿼리 최적화: N+1 문제 방지
- 캐싱: 자주 사용되는 등급 정보 캐싱

### 2. 메모리 최적화
- 페이지네이션: 대량 데이터 처리
- 지연 로딩: 필요할 때만 권한 정보 로드
- 메모리 풀: 객체 재사용

## 모니터링 및 알림

### 1. 로그 모니터링
- 권한 검증 실패 빈도 모니터링
- 등급 변경 패턴 분석
- 보안 위험도 평가

### 2. 알림 시스템
- 권한 변경 시 관리자 알림
- 보안 위험 감지 시 즉시 알림
- 정기적인 권한 검토 알림

## 향후 개선 계획

### 1. 기능 개선
- 역할 기반 접근 제어(RBAC) 확장
- 동적 권한 설정 기능
- 권한 템플릿 시스템

### 2. 보안 강화
- 다중 인증(MFA) 지원
- 세션 관리 개선
- API 보안 강화

### 3. 사용자 경험
- 드래그 앤 드롭 정렬
- 실시간 권한 미리보기
- 권한 설정 마법사

## 관련 파일

### 컨트롤러
- `jiny/admin/app/Http/Controllers/Admin/AdminLevelController.php`

### 모델
- `jiny/admin/app/Models/AdminLevel.php`

### 뷰
- `jiny/admin/resources/views/admin/levels/index.blade.php`
- `jiny/admin/resources/views/admin/levels/create.blade.php`
- `jiny/admin/resources/views/admin/levels/edit.blade.php`
- `jiny/admin/resources/views/admin/levels/show.blade.php`

### 테스트
- `jiny/admin/tests/Feature/Admin/AdminLevelTest.php`

### 마이그레이션
- `jiny/admin/database/migrations/xxxx_xx_xx_create_admin_levels_table.php`

## 개발 가이드라인

### 1. 코드 스타일
- PSR-12 코딩 표준 준수
- 의미있는 변수명과 메소드명 사용
- 적절한 주석과 문서화

### 2. 보안 고려사항
- 모든 사용자 입력 검증
- SQL 인젝션 방지
- XSS 공격 방지
- CSRF 토큰 검증

### 3. 테스트 작성
- 단위 테스트와 통합 테스트 작성
- 엣지 케이스 테스트
- 보안 시나리오 테스트

## 문제 해결

### 1. 일반적인 문제
- 권한 검증 실패: 사용자 인증 상태 확인
- 등급 삭제 실패: 사용 중인 등급인지 확인
- 권한 변경 미반영: 캐시 클리어 필요

### 2. 디버깅 방법
- 로그 파일 확인
- 데이터베이스 상태 점검
- 권한 체크 로직 검증

### 3. 성능 문제
- 데이터베이스 쿼리 최적화
- 캐시 활용
- 불필요한 데이터 로딩 방지
