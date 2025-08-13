# AdminSession 관리 기능

## 개요

AdminSession은 관리자 사용자의 세션 정보를 관리하는 핵심 기능입니다. 이 기능은 보안 모니터링, 세션 추적, 그리고 관리자 활동 감시를 위한 중요한 역할을 합니다.

## 도메인 모델

### AdminSession 엔티티

```php
AdminSession {
    id: string                    // 고유 식별자
    session_id: string           // 세션 ID (Laravel 세션 ID)
    admin_user_id: integer       // 관리자 사용자 ID (AdminUser와 연결)
    ip_address: string          // 로그인 IP 주소
    user_agent: string          // 사용자 에이전트 정보
    login_at: datetime          // 로그인 시간
    last_activity: datetime     // 마지막 활동 시간
    created_at: datetime        // 생성 시간
    updated_at: datetime        // 수정 시간
}
```

### AdminUser와의 연관성

- **1:N 관계**: AdminUser는 여러 AdminSession을 가질 수 있음
- **외래키**: `admin_sessions.admin_user_id` → `admin_users.id`
- **Cascade**: AdminUser 삭제 시 관련 세션도 함께 삭제

## 주요 기능

### 1. 세션 모니터링
- 실시간 세션 상태 추적
- 비활성 세션 감지 (30분 이상 활동 없음)
- IP 주소별 접근 패턴 분석

### 2. 보안 관리
- 세션 하이재킹 방지
- 의심스러운 IP 주소 감지
- 사용자 에이전트 검증

### 3. 세션 제어
- 강제 로그아웃
- 세션 새로고침
- 일괄 세션 관리

## 비즈니스 로직

### 세션 활성 상태 판단
```php
// 30분 이상 활동이 없으면 비활성으로 간주
$isActive = $session->last_activity > now()->subMinutes(30);
```

### 중복 세션 처리
- 동일 사용자의 여러 세션 중 가장 최근 활동이 있는 세션만 유지
- 나머지는 자동 정리 대상

### 세션 만료 정책
- 기본 만료 시간: 30분 (설정 가능)
- 자동 정리 주기: 1시간마다
- 수동 정리: 관리자가 필요시 실행

## API 엔드포인트

### 세션 목록 조회
```
GET /admin/sessions
Query Parameters:
- search: 검색어 (이름, 이메일, IP)
- type: 관리자 타입
- active: 활성 상태 (active/inactive)
- date_from: 시작 날짜
- date_to: 종료 날짜
- sort: 정렬 기준
- order: 정렬 순서 (asc/desc)
- per_page: 페이지당 항목 수
```

### 세션 생성
```
POST /admin/sessions
Body:
{
    "admin_user_id": 1,
    "session_id": "unique_session_id",
    "ip_address": "127.0.0.1",
    "user_agent": "Mozilla/5.0...",
    "login_at": "2024-01-01T00:00:00Z"
}
```

### 세션 수정
```
PUT /admin/sessions/{id}
Body:
{
    "ip_address": "127.0.0.1",
    "user_agent": "Updated User Agent",
    "last_activity": "2024-01-01T00:00:00Z"
}
```

### 세션 삭제
```
DELETE /admin/sessions/{id}
```

### 일괄 삭제
```
POST /admin/sessions/bulk-delete
Body:
{
    "ids": ["session_1", "session_2", "session_3"]
}
```

## 데이터베이스 스키마

### admin_sessions 테이블

```sql
CREATE TABLE admin_sessions (
    id VARCHAR(255) PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    admin_user_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    login_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_admin_user_id (admin_user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_last_activity (last_activity),
    INDEX idx_login_at (login_at),
    
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);
```

## 보안 고려사항

### 1. 세션 보안
- 세션 ID는 암호화하여 저장
- IP 주소 변경 시 세션 무효화
- 사용자 에이전트 변경 시 경고

### 2. 접근 제어
- 관리자 권한이 있는 사용자만 접근 가능
- 세션 정보 조회 시 로그 기록
- 민감한 정보는 마스킹 처리

### 3. 감사 로그
- 모든 세션 관련 작업 기록
- 변경 이력 추적
- 보안 사고 발생 시 증거 자료

## 성능 최적화

### 1. 인덱싱 전략
- 자주 조회되는 컬럼에 인덱스 생성
- 복합 인덱스 활용 (admin_user_id + last_activity)
- 부분 인덱스 (활성 세션만)

### 2. 캐싱 전략
- 활성 세션 목록 캐싱
- 사용자별 세션 정보 캐싱
- Redis를 활용한 세션 저장소

### 3. 정리 작업
- 주기적인 비활성 세션 정리
- 오래된 로그 데이터 아카이빙
- 데이터베이스 파티셔닝

## 모니터링 및 알림

### 1. 메트릭 수집
- 동시 접속자 수
- 세션 생성/삭제 비율
- 평균 세션 지속 시간

### 2. 알림 설정
- 비정상적인 세션 활동 감지
- 동일 IP에서 다중 로그인 시도
- 세션 하이재킹 의심 패턴

### 3. 대시보드
- 실시간 세션 현황
- 지역별 접속 통계
- 보안 이벤트 요약

## 테스트 전략

### 1. 단위 테스트
- 세션 생성/수정/삭제 로직
- 유효성 검증
- 비즈니스 규칙 검증

### 2. 통합 테스트
- AdminUser와의 연관성
- 데이터베이스 연동
- API 엔드포인트 동작

### 3. 보안 테스트
- 인증/권한 검증
- SQL 인젝션 방지
- XSS 공격 방지

## 배포 및 운영

### 1. 환경별 설정
- 개발/스테이징/운영 환경 분리
- 데이터베이스 연결 설정
- 로그 레벨 설정

### 2. 백업 전략
- 세션 데이터 백업
- 로그 데이터 아카이빙
- 재해 복구 계획

### 3. 모니터링 도구
- 로그 수집 및 분석
- 성능 메트릭 수집
- 알림 시스템 연동

## 향후 개선 계획

### 1. 기능 확장
- 세션 타임라인 시각화
- 고급 분석 리포트
- 자동화된 보안 대응

### 2. 성능 개선
- 비동기 처리 도입
- 분산 세션 저장소
- 실시간 알림 시스템

### 3. 보안 강화
- 다중 인증 지원
- 생체 인식 연동
- AI 기반 이상 탐지
