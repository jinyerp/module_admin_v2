# AdminActivityLog 기능 문서

## 개요
`AdminActivityLog`는 관리자 패널에서 발생하는 모든 사용자 활동을 추적하고 기록하는 핵심 기능입니다. 보안 감사, 사용자 행동 분석, 문제 해결을 위한 상세한 활동 로그를 제공합니다.

## 도메인 모델

### 핵심 개념
- **활동 로그**: 관리자가 수행한 모든 작업의 기록
- **보안 감사**: 시스템 접근 및 데이터 변경 이력 추적
- **사용자 행동 분석**: 관리자별 활동 패턴 및 통계 분석

### 주요 엔티티
- `AdminActivityLog`: 활동 로그 메인 모델
- `AdminUser`: 활동을 수행한 관리자 사용자
- `ActivityType`: 활동 유형 분류

## 주요 기능

### 1. 활동 로깅
- **자동 로깅**: 모든 관리자 작업 자동 기록
- **상세 정보**: 작업 내용, IP 주소, 사용자 에이전트, 타임스탬프
- **컨텍스트 정보**: 관련 데이터 및 메타데이터 포함

### 2. 보안 모니터링
- **접근 추적**: 관리자별 시스템 접근 이력
- **위험 행동 탐지**: 비정상적인 활동 패턴 감지
- **감사 추적**: 데이터 변경 및 시스템 설정 변경 이력

### 3. 통계 및 분석
- **사용자별 통계**: 개별 관리자 활동 분석
- **시간별 통계**: 시간대별 활동 패턴 분석
- **액션별 통계**: 작업 유형별 사용 빈도 분석

## 비즈니스 로직

### 로깅 규칙
1. **필수 로깅**: 모든 CRUD 작업, 로그인/로그아웃, 설정 변경
2. **보안 로깅**: 권한 변경, 사용자 관리, 시스템 설정
3. **데이터 무결성**: 로그 수정/삭제 불가 (보안상)

### 보안 정책
- **읽기 전용**: 생성된 로그는 수정/삭제 불가
- **접근 제한**: 관리자만 로그 조회 가능
- **데이터 보존**: 지정된 기간 동안 로그 보존

## API 엔드포인트

### 기본 CRUD (읽기 전용)
- `GET /admin/activity-logs` - 활동 로그 목록
- `GET /admin/activity-logs/{id}` - 활동 로그 상세
- `GET /admin/activity-logs/stats` - 통계 정보
- `GET /admin/activity-logs/admin/{id}/stats` - 관리자별 통계

### 유틸리티
- `GET /admin/activity-logs/export` - 로그 내보내기
- `GET /admin/activity-logs/download-csv` - CSV 다운로드
- `POST /admin/activity-logs/cleanup` - 오래된 로그 정리

## 데이터베이스 스키마

### admin_activity_logs 테이블
```sql
CREATE TABLE admin_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id CHAR(36) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    resource_type VARCHAR(50),
    resource_id VARCHAR(255),
    old_values JSON,
    new_values JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_admin_user_id (admin_user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_resource (resource_type, resource_id)
);
```

## 보안 고려사항

### 데이터 보호
- **민감 정보 마스킹**: 비밀번호, 개인정보 등 민감 데이터 제외
- **접근 제어**: 관리자 권한에 따른 로그 접근 제한
- **감사 추적**: 로그 접근 이력 자체 기록

### 보안 모니터링
- **비정상 활동 탐지**: 대량 로그 생성, 특정 IP 접근 등
- **권한 상승 탐지**: 권한 변경 시도 모니터링
- **데이터 유출 방지**: 로그 데이터 외부 전송 제한

## 성능 최적화

### 인덱싱 전략
- **복합 인덱스**: 자주 사용되는 조합 쿼리 최적화
- **파티셔닝**: 날짜별 테이블 파티셔닝으로 성능 향상
- **아카이빙**: 오래된 로그 자동 아카이빙

### 쿼리 최적화
- **페이지네이션**: 대용량 데이터 효율적 처리
- **지연 로딩**: 관계 데이터 필요시에만 로드
- **캐싱**: 통계 데이터 캐싱으로 응답 속도 향상

## 모니터링 및 알림

### 시스템 모니터링
- **로그 생성률**: 초당 로그 생성 수 모니터링
- **저장 공간**: 로그 데이터베이스 용량 모니터링
- **성능 지표**: 쿼리 응답 시간, 처리량 모니터링

### 알림 설정
- **용량 경고**: 저장 공간 80% 사용 시 알림
- **비정상 활동**: 대량 로그 생성 시 알림
- **시스템 오류**: 로깅 실패 시 즉시 알림

## 테스트 전략

### 단위 테스트
- **모델 테스트**: 엔티티 관계 및 스코프 검증
- **컨트롤러 테스트**: API 엔드포인트 동작 검증
- **서비스 테스트**: 비즈니스 로직 검증

### 통합 테스트
- **데이터베이스 테스트**: 스키마 및 관계 검증
- **API 테스트**: 전체 워크플로우 검증
- **성능 테스트**: 대용량 데이터 처리 성능 검증

### 보안 테스트
- **권한 테스트**: 접근 제어 검증
- **데이터 무결성**: 로그 수정/삭제 방지 검증
- **감사 추적**: 보안 이벤트 기록 검증

## 배포 및 운영

### 배포 체크리스트
- [ ] 데이터베이스 마이그레이션 실행
- [ ] 인덱스 생성 및 최적화
- [ ] 로깅 설정 및 권한 구성
- [ ] 모니터링 및 알림 설정

### 운영 가이드
- **정기 백업**: 로그 데이터 정기 백업
- **성능 모니터링**: 쿼리 성능 및 시스템 리소스 모니터링
- **보안 감사**: 정기적인 보안 감사 및 로그 검토

### 문제 해결
- **로그 누락**: 로깅 시스템 상태 확인
- **성능 저하**: 인덱스 및 쿼리 최적화
- **저장 공간 부족**: 아카이빙 및 정리 작업 실행

## 관련 문서
- [AdminUser 관리](../AdminUser.md)
- [AdminAuditLog 감사 로그](../AdminAuditLog.md)
- [AdminSystemController 시스템 관리](../AdminSystemController.md)
- [AdminResourceController 기본 리소스 컨트롤러](../AdminResourceController.md)
