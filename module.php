<?php

/**
 * Jiny Admin 모듈 정보
 * 
 * 이 파일은 Jiny Admin 패키지의 기본 정보를 정의합니다.
 */

return [
    // 기본 모듈 정보
    'name' => 'Jiny Admin',
    'version' => '1.0.0',
    'description' => 'Jiny Admin Management System',
    'author' => 'Jiny Team',
    'created_at' => '2024-01-01 00:00:00',
    
    // 모듈 경로 (__DIR__ 상수 사용)
    'path' => __DIR__,
    
    // 모듈 기능 정보
    'features' => [
        'user_management' => true,
        'role_management' => true,
        'permission_system' => true,
        'admin_interface' => true,
        'session_management' => true,
        'security_features' => true
    ],
    
    // 모듈 설정 정보
    'config' => [
        'auth_guard' => 'admin',
        'session_timeout' => 3600,
        'max_login_attempts' => 5,
        'password_policy' => 'strong'
    ],
    
    // 모듈 의존성
    'dependencies' => [
        'laravel/framework' => '^10.0',
        'illuminate/auth' => '^10.0',
        'illuminate/session' => '^10.0'
    ],
    
    // 모듈 구조 정보
    'structure' => [
        'App' => '애플리케이션 로직',
        'config' => '설정 파일',
        'database' => '데이터베이스 관련',
        'resources' => '리소스 파일',
        'routes' => '라우트 정의',
        'View' => '뷰 컴포넌트'
    ],
    
    // 모듈 메타데이터
    'metadata' => [
        'namespace' => 'Jiny\\Admin',
        'service_provider' => 'Jiny\\Admin\\JinyAdminServiceProvider',
        'main_model' => 'Jiny\\Admin\\App\\Models\\AdminUser',
        'auth_guard' => 'admin'
    ]
]; 