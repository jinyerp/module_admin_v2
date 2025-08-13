# AdminSystemBackupLog 기능 문서

## 개요
`AdminSystemBackupLog`는 시스템 백업 작업의 로그를 관리하는 기능입니다. 데이터베이스, 파일 시스템, 소스 코드 등 다양한 백업 타입을 지원하며, 백업 과정의 상태 추적과 결과 모니터링을 제공합니다.

## 도메인 모델

### 핵심 개념
- **백업 타입**: database, files, code, full
- **백업 상태**: running, completed, failed, cancelled
- **백업 보안**: 암호화, 압축, 체크섬 검증
- **백업 정책**: 자동 백업, 보관 기간, 스토리지 위치

### 주요 엔티티
- `SystemBackupLog`: 백업 작업 로그
- `AdminUser`: 백업을 시작한 관리자
- `BackupPolicy`: 백업 정책 설정

## 핵심 기능

### 1. 백업 실행 및 관리
- 다양한 백업 타입 지원 (데이터베이스, 파일, 코드, 전체)
- 백그라운드 백업 실행
- 실시간 상태 모니터링
- 백업 파일 다운로드 및 삭제

### 2. 백업 로그 관리
- 백업 작업의 전체 생명주기 추적
- 성공/실패 통계 및 분석
- 오류 메시지 및 디버깅 정보
- 백업 성능 메트릭

### 3. 보안 및 무결성
- 백업 파일 암호화
- 체크섬 검증
- 접근 권한 관리
- 감사 로그 기록

## 비즈니스 로직

### 백업 정책 검증
- 백업 주기 준수 여부
- 보관 기간 정책 준수
- 스토리지 용량 관리
- 백업 무결성 검증

### 성능 최적화
- 백업 크기 최적화
- 압축 알고리즘 선택
- 병렬 백업 처리
- 네트워크 대역폭 관리

## API 엔드포인트

### 백업 관리
- `GET /admin/systems/backup-logs` - 백업 로그 목록
- `POST /admin/systems/backup-logs` - 백업 로그 생성
- `GET /admin/systems/backup-logs/{id}` - 백업 로그 상세
- `PUT /admin/systems/backup-logs/{id}` - 백업 로그 수정
- `DELETE /admin/systems/backup-logs/{id}` - 백업 로그 삭제

### 백업 실행
- `GET /admin/systems/backup-logs/create-backup` - 백업 실행 폼
- `POST /admin/systems/backup-logs/execute-backup` - 백업 실행
- `GET /admin/systems/backup-logs/{id}/download` - 백업 다운로드
- `DELETE /admin/systems/backup-logs/{id}/file` - 백업 파일 삭제

### 통계 및 분석
- `GET /admin/systems/backup-logs/stats` - 백업 통계
- `POST /admin/systems/backup-logs/export` - 로그 내보내기
- `POST /admin/systems/backup-logs/bulk-delete` - 일괄 삭제

## 데이터베이스 스키마

### system_backup_logs 테이블
```sql
CREATE TABLE system_backup_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    backup_type VARCHAR(50) NOT NULL COMMENT '백업 타입',
    backup_name VARCHAR(255) NOT NULL COMMENT '백업 이름',
    file_path VARCHAR(500) NULL COMMENT '백업 파일 경로',
    file_size VARCHAR(100) NULL COMMENT '파일 크기',
    checksum VARCHAR(255) NULL COMMENT '체크섬',
    status VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT '백업 상태',
    started_at TIMESTAMP NULL COMMENT '시작 시간',
    completed_at TIMESTAMP NULL COMMENT '완료 시간',
    duration_seconds INT NULL COMMENT '소요 시간(초)',
    error_message TEXT NULL COMMENT '오류 메시지',
    initiated_by BIGINT UNSIGNED NULL COMMENT '시작한 관리자 ID',
    storage_location VARCHAR(255) NULL COMMENT '스토리지 위치',
    is_encrypted BOOLEAN DEFAULT FALSE COMMENT '암호화 여부',
    is_compressed BOOLEAN DEFAULT FALSE COMMENT '압축 여부',
    metadata JSON NULL COMMENT '메타데이터',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_backup_type (backup_type),
    INDEX idx_status (status),
    INDEX idx_initiated_by (initiated_by),
    INDEX idx_created_at (created_at),
    INDEX idx_started_at (started_at),
    
    FOREIGN KEY (initiated_by) REFERENCES admin_users(id) ON DELETE SET NULL
);
```

## 보안 고려사항

### 접근 제어
- 백업 로그 조회 권한 검증
- 백업 파일 다운로드 권한 검증
- 백업 실행 권한 검증
- 민감한 백업 정보 보호

### 데이터 보호
- 백업 파일 암호화
- 체크섬 검증을 통한 무결성 보장
- 백업 파일 접근 로그 기록
- 백업 정책 준수 모니터링

## 성능 최적화

### 백업 성능
- 백업 크기 최적화
- 압축 알고리즘 선택
- 병렬 처리 활용
- 네트워크 대역폭 최적화

### 로그 성능
- 인덱스 최적화
- 파티셔닝 전략
- 오래된 로그 정리
- 캐싱 활용

## 모니터링

### 백업 상태 모니터링
- 실시간 백업 진행 상황
- 백업 성공/실패 알림
- 백업 완료 시간 추적
- 백업 크기 변화 모니터링

### 시스템 리소스 모니터링
- 디스크 사용량 모니터링
- 백업 프로세스 리소스 사용량
- 네트워크 대역폭 사용량
- 백업 작업 큐 상태

## 테스트 전략

### 단위 테스트
- 백업 로직 검증
- 파일 처리 로직 검증
- 암호화/압축 로직 검증
- 오류 처리 로직 검증

### 통합 테스트
- 백업 실행 전체 과정 검증
- 데이터베이스 연동 검증
- 파일 시스템 연동 검증
- 권한 시스템 연동 검증

### 성능 테스트
- 대용량 백업 성능 테스트
- 동시 백업 처리 테스트
- 백업 복원 성능 테스트
- 리소스 사용량 테스트

## 배포

### 환경별 설정
- 개발 환경: 테스트 백업만 실행
- 스테이징 환경: 제한된 백업 실행
- 운영 환경: 전체 백업 기능 활성화

### 백업 정책 설정
- 백업 주기 설정
- 보관 기간 설정
- 스토리지 위치 설정
- 암호화 설정

### 모니터링 설정
- 백업 상태 알림 설정
- 오류 알림 설정
- 성능 메트릭 수집 설정
- 로그 수집 설정
