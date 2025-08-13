# Admin2FALog 기능 문서

## 개요
`Admin2FALog`는 관리자 2단계 인증(2FA) 과정의 보안 로그를 관리하는 기능입니다. 2FA 인증 시도, 성공/실패 결과, IP 주소, 사용자 에이전트 등 보안 관련 정보를 추적하고 분석하여 보안 위협을 탐지하고 대응할 수 있도록 지원합니다.

## 도메인 모델

### 핵심 개념
- **2FA 인증**: 2단계 인증 과정 (비밀번호 + 추가 인증 요소)
- **보안 로그**: 인증 시도, 성공, 실패 등의 보안 이벤트 기록
- **위협 탐지**: 비정상적인 인증 패턴 및 보안 위험 요소 식별
- **감사 추적**: 보안 사고 발생 시 원인 분석 및 책임 추적

### 주요 엔티티
- `Admin2FALog`: 2FA 인증 로그
- `AdminUser`: 2FA 인증을 시도한 관리자
- `SecurityPolicy`: 2FA 보안 정책 설정

## 핵심 기능

### 1. 2FA 인증 로그 관리
- 2FA 인증 시도 및 결과 추적
- 성공/실패 패턴 분석
- IP 주소 및 사용자 에이전트 정보 기록
- 인증 시도 시간 및 빈도 모니터링

### 2. 보안 모니터링
- 비정상적인 인증 시도 탐지
- 다중 실패 시도 감지
- 지리적 위치 기반 위험 요소 분석
- 디바이스별 인증 패턴 분석

### 3. 감사 및 분석
- 관리자별 2FA 사용 패턴 분석
- 보안 사고 발생 시 원인 분석
- 규정 준수 여부 검증
- 보안 정책 효과성 평가

## 비즈니스 로직

### 보안 정책 검증
- 2FA 인증 시도 제한 정책
- 실패 시도 제한 및 계정 잠금
- IP 주소 기반 접근 제한
- 디바이스 인증 정책

### 위협 탐지 로직
- 비정상적인 로그인 시간 패턴
- 다중 IP에서의 동시 접근
- 실패 시도 빈도 분석
- 지리적 위치 기반 위험도 평가

## API 엔드포인트

### 2FA 로그 관리
- `GET /admin/admin/user-2fa-logs` - 2FA 로그 목록
- `POST /admin/admin/user-2fa-logs` - 2FA 로그 생성
- `GET /admin/admin/user-2fa-logs/{id}` - 2FA 로그 상세
- `PUT /admin/admin/user-2fa-logs/{id}` - 2FA 로그 수정
- `DELETE /admin/admin/user-2fa-logs/{id}` - 2FA 로그 삭제

### 통계 및 분석
- `GET /admin/admin/user-2fa-logs/stats` - 2FA 로그 통계
- `POST /admin/admin/user-2fa-logs/export` - 로그 내보내기
- `POST /admin/admin/user-2fa-logs/bulk-delete` - 일괄 삭제
- `POST /admin/admin/user-2fa-logs/cleanup` - 오래된 로그 정리

## 데이터베이스 스키마

### admin_2fa_logs 테이블
```sql
CREATE TABLE admin_2fa_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id VARCHAR(36) NOT NULL COMMENT '관리자 사용자 ID',
    action VARCHAR(255) NOT NULL COMMENT '2FA 액션 (enable, disable, verify 등)',
    status ENUM('success', 'fail') NOT NULL COMMENT '인증 상태',
    ip_address VARCHAR(45) NULL COMMENT 'IP 주소',
    user_agent TEXT NULL COMMENT '사용자 에이전트',
    message TEXT NULL COMMENT '상세 메시지',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_admin_user_id (admin_user_id),
    INDEX idx_action (action),
    INDEX idx_status (status),
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);
```

## 보안 고려사항

### 접근 제어
- 2FA 로그 조회 권한 검증
- 민감한 보안 정보 접근 제한
- 로그 수정/삭제 권한 제한
- 감사 로그 무결성 보장

### 데이터 보호
- 개인정보 암호화 처리
- 로그 데이터 접근 로그 기록
- 보안 정책 준수 모니터링
- 데이터 보관 기간 관리

## 성능 최적화

### 로그 성능
- 인덱스 최적화
- 파티셔닝 전략
- 오래된 로그 정리
- 캐싱 활용

### 보안 성능
- 실시간 위협 탐지
- 비동기 로그 처리
- 배치 분석 처리
- 메모리 기반 캐싱

## 모니터링

### 보안 모니터링
- 실시간 2FA 인증 시도 모니터링
- 비정상 패턴 자동 탐지
- 보안 위험도 실시간 평가
- 알림 및 경고 시스템

### 성능 모니터링
- 로그 처리 성능 모니터링
- 데이터베이스 쿼리 성능
- 메모리 및 CPU 사용량
- 네트워크 대역폭 사용량

## 테스트 전략

### 단위 테스트
- 2FA 로그 생성/수정/삭제 로직 검증
- 필터링 및 정렬 로직 검증
- 보안 정책 검증 로직 테스트
- 오류 처리 로직 검증

### 통합 테스트
- 2FA 인증 전체 과정 검증
- 데이터베이스 연동 검증
- 권한 시스템 연동 검증
- 로그 시스템 연동 검증

### 보안 테스트
- 권한 우회 시도 테스트
- SQL 인젝션 방지 테스트
- XSS 공격 방지 테스트
- CSRF 공격 방지 테스트

## 배포

### 환경별 설정
- 개발 환경: 기본 보안 정책 적용
- 스테이징 환경: 운영과 유사한 보안 정책
- 운영 환경: 엄격한 보안 정책 적용

### 보안 정책 설정
- 2FA 인증 시도 제한 설정
- 실패 시도 제한 설정
- IP 기반 접근 제한 설정
- 알림 및 경고 설정

### 모니터링 설정
- 보안 이벤트 알림 설정
- 성능 메트릭 수집 설정
- 로그 수집 및 분석 설정
- 백업 및 복구 설정
