# AdminSystemOperationLog 기능 문서

## 개요
`AdminSystemOperationLogController`는 시스템의 모든 운영 활동을 추적하고 기록하는 관리자 컨트롤러입니다. 시스템 운영자들의 작업 내역, 성능 지표, 오류 발생 상황 등을 체계적으로 관리하여 시스템의 안정성과 성능을 모니터링합니다.

## 도메인 모델

### SystemOperationLog
- **테이블**: `system_operation_logs`
- **주요 속성**:
  - `operation_type`: 운영 타입 (user_management, system_configuration, data_backup 등)
  - `operation_name`: 구체적인 운영명
  - `performed_by_type`: 수행자 타입 (AdminUser, System 등)
  - `performed_by_id`: 수행자 ID
  - `target_type`: 대상 타입 (User, System, Database 등)
  - `target_id`: 대상 ID
  - `status`: 실행 상태 (success, failed, partial)
  - `execution_time`: 실행 시간 (밀리초)
  - `severity`: 중요도 (low, medium, high, critical)
  - `ip_address`: IP 주소
  - `session_id`: 세션 ID
  - `error_message`: 오류 메시지

## 주요 기능

### 1. 운영 로그 조회 및 관리
- **목록 조회**: 필터링, 정렬, 페이지네이션 지원
- **상세 조회**: 개별 운영 로그의 상세 정보 확인
- **통계 정보**: 운영 타입별, 상태별 통계 제공

### 2. 필터링 및 검색
- **기본 필터**: 운영 타입, 상태, 중요도, 날짜 범위
- **고급 검색**: 수행자, 대상, IP 주소 등으로 검색
- **정렬**: 생성일, 실행시간, 중요도 등으로 정렬

### 3. 데이터 내보내기
- **CSV 내보내기**: 필터링된 결과를 CSV 형식으로 다운로드
- **Excel 내보내기**: 엑셀 형식으로 데이터 내보내기

### 4. 일괄 작업
- **일괄 삭제**: 선택된 로그들의 일괄 삭제
- **자동 정리**: 오래된 로그들의 자동 정리

## 비즈니스 로직

### 로그 기록 규칙
1. **자동 기록**: 모든 시스템 운영 활동은 자동으로 로그에 기록
2. **성능 측정**: 실행 시간을 측정하여 성능 지표 제공
3. **오류 추적**: 실패한 작업의 상세 오류 정보 기록
4. **보안 감사**: IP 주소, 세션 ID 등 보안 관련 정보 기록

### 데이터 보존 정책
- **활성 로그**: 최근 30일간의 로그는 즉시 조회 가능
- **아카이브**: 30일 이상 된 로그는 아카이브 테이블로 이동
- **자동 삭제**: 1년 이상 된 로그는 자동 삭제

## API 엔드포인트

### 기본 CRUD
- `GET /admin/system-operation-logs` - 목록 조회
- `GET /admin/system-operation-logs/create` - 생성 폼
- `POST /admin/system-operation-logs` - 로그 생성
- `GET /admin/system-operation-logs/{id}` - 상세 조회
- `GET /admin/system-operation-logs/{id}/edit` - 수정 폼
- `PUT /admin/system-operation-logs/{id}` - 로그 수정
- `DELETE /admin/system-operation-logs/{id}` - 로그 삭제

### 추가 기능
- `POST /admin/system-operation-logs/bulk-delete` - 일괄 삭제
- `GET /admin/system-operation-logs/export` - 데이터 내보내기
- `GET /admin/system-operation-logs/stats` - 통계 정보

## 데이터베이스 스키마

```sql
CREATE TABLE system_operation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    operation_type VARCHAR(100) NOT NULL,
    operation_name VARCHAR(255) NOT NULL,
    performed_by_type VARCHAR(100) NOT NULL,
    performed_by_id BIGINT UNSIGNED NOT NULL,
    target_type VARCHAR(100) NULL,
    target_id BIGINT UNSIGNED NULL,
    status ENUM('success', 'failed', 'partial') NOT NULL,
    execution_time INT NULL,
    severity VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45) NULL,
    session_id VARCHAR(255) NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_operation_type (operation_type),
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_performed_by (performed_by_type, performed_by_id),
    INDEX idx_created_at (created_at)
);
```

## 보안 고려사항

### 접근 제어
- **인증**: 관리자 인증 필요
- **권한**: 시스템 운영 로그 조회 권한 필요
- **감사**: 모든 접근 로그 기록

### 데이터 보호
- **민감 정보**: 패스워드, 개인정보 등은 마스킹 처리
- **세션 정보**: 세션 ID는 암호화하여 저장
- **IP 주소**: IPv6 주소 지원

## 성능 최적화

### 인덱싱 전략
- **복합 인덱스**: 자주 조합되는 검색 조건에 대한 복합 인덱스
- **부분 인덱스**: 상태별, 날짜별 부분 인덱스
- **커버링 인덱스**: 자주 조회되는 컬럼들을 포함한 인덱스

### 쿼리 최적화
- **Eager Loading**: 관계 데이터 미리 로딩
- **페이지네이션**: 대용량 데이터 처리 시 페이지네이션 적용
- **캐싱**: 통계 정보 및 자주 조회되는 데이터 캐싱

## 모니터링 및 알림

### 성능 모니터링
- **실행 시간**: 각 운영의 실행 시간 추적
- **실패율**: 실패한 작업의 비율 모니터링
- **리소스 사용량**: 메모리, CPU 사용량 추적

### 알림 시스템
- **오류 알림**: 중요도가 높은 오류 발생 시 즉시 알림
- **성능 경고**: 실행 시간이 임계값을 초과할 때 경고
- **용량 경고**: 로그 저장 용량이 부족할 때 경고

## 테스트 전략

### 단위 테스트
- **모델 테스트**: 데이터 검증, 관계, 스코프 테스트
- **컨트롤러 테스트**: 각 메서드의 동작 검증
- **팩토리 테스트**: 테스트 데이터 생성 검증

### 통합 테스트
- **API 테스트**: 전체 API 엔드포인트 테스트
- **데이터베이스 테스트**: 실제 데이터베이스 연동 테스트
- **권한 테스트**: 인증 및 권한 검증

### 성능 테스트
- **부하 테스트**: 대용량 데이터 처리 성능 테스트
- **메모리 테스트**: 메모리 사용량 및 누수 테스트
- **응답 시간 테스트**: API 응답 시간 테스트

## 배포 및 운영

### 배포 절차
1. **마이그레이션**: 데이터베이스 스키마 업데이트
2. **설정 파일**: 환경별 설정 파일 업데이트
3. **캐시 클리어**: 애플리케이션 캐시 클리어
4. **권한 설정**: 파일 및 디렉토리 권한 설정

### 운영 모니터링
- **로그 모니터링**: 애플리케이션 로그 실시간 모니터링
- **성능 모니터링**: 시스템 성능 지표 모니터링
- **오류 추적**: 발생하는 오류의 패턴 분석

### 백업 및 복구
- **정기 백업**: 운영 로그 데이터 정기 백업
- **복구 절차**: 장애 발생 시 복구 절차 문서화
- **데이터 무결성**: 백업 데이터의 무결성 검증

## 관련 문서

- [AdminSystemController](./AdminSystemController.md) - 시스템 모니터링
- [AdminSystemBackupLog](./AdminSystemBackupLog.md) - 백업 로그 관리
- [AdminSystemMaintenanceLog](./AdminSystemMaintenanceLog.md) - 유지보수 로그 관리
- [AdminSystemPerformanceLog](./AdminSystemPerformanceLog.md) - 성능 로그 관리
- [AdminAuditLog](./AdminAuditLog.md) - 감사 로그 관리
- [AdminActivityLog](./AdminActivityLog.md) - 활동 로그 관리
