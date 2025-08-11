# Jiny Admin 패키지

## 📋 개요

Jiny Admin은 Laravel 기반의 강력한 관리자 패널 시스템입니다. 완전한 CRUD 기능, 인증 시스템, 권한 관리, 로그 추적 등을 제공하는 종합적인 관리자 솔루션입니다.

## 🚀 주요 특징

- 🔐 **다중 인증 시스템**: Guard 기반 및 기본 Auth 기반 지원
- 🛡️ **2FA 보안**: Google Authenticator 기반 2단계 인증
- 📊 **완전한 CRUD**: 자동화된 CRUD 생성 및 관리
- 📝 **활동 로그**: 모든 관리자 활동 추적
- 🔍 **감사 로그**: 데이터 변경 이력 추적
- 🎨 **반응형 UI**: Tailwind CSS 기반 모던 인터페이스
- 📱 **모바일 지원**: 반응형 디자인으로 모든 디바이스 지원

## 📚 문서 구조

```
docs/
├── README.md              # 이 파일 - 패키지 기본 설명
├── start.md               # 설치 및 배포 가이드
├── features/              # 기능별 상세 설명
│   ├── authentication.md  # 인증 시스템
│   ├── authorization.md   # 권한 관리
│   ├── crud-system.md    # CRUD 시스템
│   ├── logging.md        # 로그 시스템
│   └── ui-components.md  # UI 컴포넌트
├── versions/              # 버전별 지원 기능
│   ├── v1.0.md           # v1.0 지원 기능
│   └── v1.1.md           # v1.1 지원 기능
└── roadmap/               # 향후 계획
    ├── todo.md            # TODO 목록
    └── future-plans.md    # 미래 계획
```

## 🛠️ 빠른 시작

Jiny Admin을 빠르게 시작하려면 [설치 및 배포 가이드](./start.md)를 참조하세요.

### 핵심 단계
1. **패키지 설치**: `composer require jiny/admin`
2. **서비스 프로바이더 등록**: `config/app.php`에 추가
3. **마이그레이션 실행**: `php artisan migrate`
4. **기본 관리자 생성**: `php artisan admin:user`

## 🔗 상세 문서

- **[설치 및 배포 가이드](./start.md)** - 상세한 설치 과정과 배포 방법
- **[기능별 상세 설명](./features/)** - 각 기능의 상세한 사용법과 설정
- **[버전별 지원 기능](./versions/)** - 각 버전에서 지원하는 기능 목록
- **[로드맵](./roadmap/)** - 향후 개발 계획과 TODO 목록

## 🤝 기여하기

버그 리포트, 기능 제안, 코드 기여를 환영합니다. [Issues](../../issues)를 통해 의견을 남겨주세요.

## 📄 라이선스

이 패키지는 MIT 라이선스 하에 배포됩니다.

---

**더 자세한 정보는 각 폴더의 문서를 참조하세요.** 