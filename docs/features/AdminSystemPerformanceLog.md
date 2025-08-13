# AdminSystemPerformanceLog 기능 문서

## 개요
`AdminSystemPerformanceLogController`는 시스템의 성능 지표를 수집하고 분석하는 관리자 컨트롤러입니다. CPU 사용률, 메모리 사용량, 디스크 I/O, 네트워크 성능, 데이터베이스 성능 등 다양한 시스템 리소스의 성능 데이터를 실시간으로 모니터링하고, 성능 병목 지점을 식별하여 시스템 최적화를 지원합니다.

## 도메인 모델

### SystemPerformanceLog
- **테이블**: `system_performance_logs`
- **주요 속성**:
  - `metric_type`: 지표 타입 (cpu, memory, disk, network, database 등)
  - `metric_name`: 구체적인 지표명 (cpu_usage, memory_usage, disk_read 등)
  - `metric_value`: 지표 값 (숫자 또는 문자열)
  - `unit`: 단위 (%, MB, KB/s, ms 등)
  - `threshold`: 임계값 (경고 또는 알림 기준)
  - `status`: 상태 (normal, warning, critical)
  - `recorded_at`: 기록 시간
  - `server_info`: 서버 정보 (호스트명, IP 등)
  - `additional_data`: 추가 데이터 (JSON 형태)

## 주요 기능

### 1. 성능 지표 수집 및 모니터링
- **실시간 모니터링**: CPU, 메모리, 디스크, 네트워크 성능 실시간 추적
- **임계값 관리**: 각 지표별 경고 및 임계값 설정
- **상태 추적**: 정상/경고/위험 상태 자동 분류
- **트렌드 분석**: 시간별 성능 변화 추이 분석

### 2. 성능 데이터 분석
- **병목 지점 식별**: 성능 저하 원인 분석
- **패턴 분석**: 성능 패턴 및 주기성 분석
- **예측 분석**: 향후 성능 예측 및 계획 수립
- **비교 분석**: 기간별, 서버별 성능 비교

### 3. 알림 및 보고
- **실시간 알림**: 임계값 초과 시 즉시 알림
- **일일/주간/월간 리포트**: 정기적인 성능 보고서 생성
- **대시보드**: 실시간 성능 모니터링 대시보드
- **이메일 알림**: 중요 성능 이슈 시 이메일 발송

### 4. 데이터 관리
- **데이터 보존**: 성능 데이터의 체계적 보관
- **데이터 압축**: 오래된 데이터의 효율적 저장
- **백업 및 복구**: 성능 데이터의 안전한 보관
- **데이터 내보내기**: 분석을 위한 데이터 내보내기

## 비즈니스 로직

### 성능 지표 수집 규칙
1. **주기적 수집**: 설정된 간격으로 성능 데이터 수집
2. **임계값 검증**: 수집된 데이터의 임계값 초과 여부 확인
3. **상태 분류**: 정상/경고/위험 상태로 자동 분류
4. **알림 발송**: 임계값 초과 시 즉시 알림 발송

### 데이터 처리 정책
- **실시간 처리**: 최신 성능 데이터의 즉시 처리
- **배치 처리**: 대용량 데이터의 효율적 처리
- **데이터 정규화**: 다양한 단위와 형식의 데이터 정규화
- **이상치 탐지**: 비정상적인 성능 데이터 탐지 및 필터링

## API 엔드포인트

### 기본 CRUD
- `GET /admin/system-performance-logs` - 성능 로그 목록 조회
- `GET /admin/system-performance-logs/create` - 성능 로그 생성 폼
- `POST /admin/system-performance-logs` - 성능 로그 생성
- `GET /admin/system-performance-logs/{id}` - 성능 로그 상세 조회
- `GET /admin/system-performance-logs/{id}/edit` - 성능 로그 수정 폼
- `PUT /admin/system-performance-logs/{id}` - 성능 로그 수정
- `DELETE /admin/system-performance-logs/{id}` - 성능 로그 삭제

### 성능 모니터링
- `GET /admin/system-performance-logs/current` - 현재 성능 상태
- `GET /admin/system-performance-logs/history` - 성능 히스토리
- `GET /admin/system-performance-logs/trends` - 성능 트렌드
- `GET /admin/system-performance-logs/alerts` - 성능 알림 목록

### 분석 및 리포트
- `GET /admin/system-performance-logs/analysis` - 성능 분석
- `GET /admin/system-performance-logs/reports` - 성능 리포트
- `GET /admin/system-performance-logs/export` - 데이터 내보내기
- `GET /admin/system-performance-logs/dashboard` - 성능 대시보드

## 데이터베이스 스키마

```sql
CREATE TABLE system_performance_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metric_type VARCHAR(50) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,4) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    threshold DECIMAL(10,4) NULL,
    status ENUM('normal', 'warning', 'critical') NOT NULL,
    recorded_at TIMESTAMP NOT NULL,
    server_info JSON NULL,
    additional_data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_metric_type (metric_type),
    INDEX idx_metric_name (metric_name),
    INDEX idx_status (status),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_metric_type_recorded_at (metric_type, recorded_at)
);
```

## 보안 고려사항

### 접근 제어
- **인증**: 관리자 인증 필요
- **권한**: 성능 모니터링 권한 필요
- **감사**: 모든 접근 로그 기록

### 데이터 보호
- **민감 정보**: 서버 정보 중 민감한 부분 마스킹
- **데이터 암호화**: 중요 성능 데이터 암호화 저장
- **접근 로그**: 성능 데이터 접근 이력 추적

## 성능 최적화

### 데이터 수집 최적화
- **비동기 수집**: 성능 데이터 수집의 비동기 처리
- **배치 처리**: 대량 데이터의 효율적 처리
- **압축 저장**: 성능 데이터의 압축 저장

### 쿼리 최적화
- **인덱싱**: 자주 조회되는 조건에 대한 인덱스
- **파티셔닝**: 대용량 데이터의 파티셔닝
- **캐싱**: 자주 조회되는 성능 데이터 캐싱

## 모니터링 및 알림

### 성능 모니터링
- **CPU 모니터링**: CPU 사용률, 로드 평균, 프로세스 수
- **메모리 모니터링**: 메모리 사용량, 가상 메모리, 스왑
- **디스크 모니터링**: 디스크 사용량, I/O 성능, 대기 시간
- **네트워크 모니터링**: 네트워크 대역폭, 패킷 손실, 지연 시간
- **데이터베이스 모니터링**: 쿼리 성능, 연결 수, 락 상태

### 알림 시스템
- **임계값 알림**: 설정된 임계값 초과 시 알림
- **트렌드 알림**: 성능 저하 트렌드 감지 시 알림
- **장애 알림**: 시스템 장애 발생 시 즉시 알림
- **복구 알림**: 성능 복구 시 알림

## 테스트 전략

### 단위 테스트
- **모델 테스트**: 데이터 검증, 관계, 스코프 테스트
- **컨트롤러 테스트**: 각 메서드의 동작 검증
- **서비스 테스트**: 성능 데이터 처리 로직 검증

### 통합 테스트
- **API 테스트**: 전체 API 엔드포인트 테스트
- **데이터베이스 테스트**: 실제 데이터베이스 연동 테스트
- **성능 테스트**: 성능 데이터 수집 및 처리 성능 테스트

### 부하 테스트
- **대용량 데이터 테스트**: 대량 성능 데이터 처리 테스트
- **동시 접속 테스트**: 다중 사용자 동시 접속 테스트
- **메모리 테스트**: 메모리 사용량 및 누수 테스트

## 배포 및 운영

### 배포 절차
1. **마이그레이션**: 데이터베이스 스키마 업데이트
2. **설정 파일**: 성능 모니터링 설정 파일 업데이트
3. **에이전트 설치**: 성능 데이터 수집 에이전트 설치
4. **알림 설정**: 성능 알림 설정 및 테스트

### 운영 모니터링
- **에이전트 모니터링**: 성능 데이터 수집 에이전트 상태 모니터링
- **데이터 품질**: 수집된 성능 데이터의 품질 모니터링
- **알림 시스템**: 성능 알림 시스템의 정상 동작 확인

### 백업 및 복구
- **정기 백업**: 성능 데이터의 정기 백업
- **복구 절차**: 장애 발생 시 복구 절차 문서화
- **데이터 무결성**: 백업 데이터의 무결성 검증

## 관련 문서

- [AdminSystemController](./AdminSystemController.md) - 시스템 모니터링
- [AdminSystemBackupLog](./AdminSystemBackupLog.md) - 백업 로그 관리
- [AdminSystemMaintenanceLog](./AdminSystemMaintenanceLog.md) - 유지보수 로그 관리
- [AdminSystemOperationLog](./AdminSystemOperationLog.md) - 운영 로그 관리
- [AdminAuditLog](./AdminAuditLog.md) - 감사 로그 관리
- [AdminActivityLog](./AdminActivityLog.md) - 활동 로그 관리

