<?php

return [
    'prefix' => 'admin',
    
    'login' => [
        'security' => [
            'max_attempts' => 5, // 5회 비밀번호 틀린 경우
            'lockout_time' => 1800, // 30분간 접속 제한 (30분 = 1800초)
            'max_attempts_admin_lock' => 25, // 25번 비밀번호 오류 시 관리자 해제 필요
            'admin_lock_time' => 0, // 관리자 해제 전까지 무제한 제한 (0 = 무제한)
            'log_attempts' => true, // 로그인 시도 기록
            'log_failures' => true, // 로그인 실패 기록
            'log_successes' => true, // 로그인 성공 기록
            'notify_admin_on_lockout' => true, // 계정 잠금 시 관리자에게 알림
            'auto_unlock_after_admin_reset' => true, // 관리자 해제 후 자동 잠금 해제
        ],
        'session' => [
            'timeout' => 3600, // 세션 타임아웃 (1시간)
            'concurrent_sessions' => 3, // 동시 접속 세션 수 제한
            'force_logout_on_password_change' => true, // 비밀번호 변경 시 다른 세션 강제 로그아웃
            'regenerate_on_login' => true, // 로그인 시 세션 재생성
        ],
        'ip_security' => [
            'whitelist' => [], // 접속 허용 IP 목록
            'blacklist' => [], // 접속 차단 IP 목록
            'geolocation_check' => false, // 지리적 위치 기반 접속 제한
            'allowed_countries' => [], // 접속 허용 국가 목록
            'blocked_countries' => [], // 접속 차단 국가 목록
        ],
        'notification' => [
            'email_on_failed_login' => true, // 로그인 실패 시 이메일 알림
            'email_on_account_lockout' => true, // 계정 잠금 시 이메일 알림
            'sms_on_failed_login' => false, // 로그인 실패 시 SMS 알림
            'sms_on_account_lockout' => false, // 계정 잠금 시 SMS 알림
        ],
    ],
    
    'password' => [
        'length_min' => 8, // 비밀번호 최소 길이
        'length_max' => 120, // 비밀번호 최대 길이
        'uppercase' => 1, // 대문자 포함 여부 (1: 포함, 0: 미포함)
        'lowercase' => 1, // 소문자 포함 여부 (1: 포함, 0: 미포함)
        'numbers' => 1, // 숫자 포함 여부 (1: 포함, 0: 미포함)
        'symbols' => 1, // 특수문자 포함 여부 (1: 포함, 0: 미포함)
    ],
    '2fa' => [
        'enabled' => env('ADMIN_2FA_ENABLED', true),
        'force_enable' => env('ADMIN_2FA_FORCE_ENABLE', false), // 모든 관리자에게 강제 적용
        'backup_codes_count' => env('ADMIN_2FA_BACKUP_CODES_COUNT', 8),
        'qr_code_size' => env('ADMIN_2FA_QR_SIZE', 200),
        'issuer' => env('ADMIN_2FA_ISSUER', 'Jiny Admin'),
        'window' => env('ADMIN_2FA_WINDOW', 1), // TOTP 검증 윈도우
    ],
    

    'auth' => [
        'login' => [
            'max_attempts' => 5, // 5회 비밀번호 틀린 경우
            'lockout_time' => 1800, // 30분간 접속 제한 (30분 = 1800초)
            'max_attempts_admin_lock' => 25, // 25번 비밀번호 오류 시 관리자 해제 필요
            'admin_lock_time' => 0, // 관리자 해제 전까지 무제한 제한 (0 = 무제한)
            'remember_me' => true,
            'log_attempts' => true, // 로그인 시도 기록
            'log_failures' => true, // 로그인 실패 기록
            'log_successes' => true, // 로그인 성공 기록
            'notify_admin_on_lockout' => true, // 계정 잠금 시 관리자에게 알림
            'auto_unlock_after_admin_reset' => true, // 관리자 해제 후 자동 잠금 해제
        ],
        'regist' => [
            'enable' => false,
            'email_verification' => true,
            'auto_approve' => false,
            'terms_required' => true,
        ],
        'password' => [
            'min_length' => 8,
            'require_special' => true,
            'require_number' => true,
            'require_uppercase' => true,
            'expire_days' => 90,
        ],
        'dormant' => [
            'enable' => true,
            'days' => 90,
            'auto_restore' => false,
        ],
        'login_disable' => [
            'enable' => false,
            'reason' => null,
        ],
        'security' => [
            'ip_whitelist' => [], // 접속 허용 IP 목록
            'ip_blacklist' => [], // 접속 차단 IP 목록
            'geolocation_check' => false, // 지리적 위치 기반 접속 제한
            'allowed_countries' => [], // 접속 허용 국가 목록
            'blocked_countries' => [], // 접속 차단 국가 목록
            'session_timeout' => 3600, // 세션 타임아웃 (1시간)
            'concurrent_sessions' => 3, // 동시 접속 세션 수 제한
            'force_logout_on_password_change' => true, // 비밀번호 변경 시 다른 세션 강제 로그아웃
        ],
    ],
    
    '2fa' => [
        'enabled' => true,
        'app_name' => env('APP_NAME', 'Jiny Admin'),
        'backup_codes_count' => 8,
        'backup_code_length' => 8,
        'time_window' => 2, // TOTP 검증 윈도우 (분)
        'qr_code_size' => 200,
        'required_for_all' => false, // 모든 관리자에게 2FA 필수 여부
        'exempt_roles' => ['super'], // 2FA 면제 역할
        'log_attempts' => true,
        'max_attempts' => 5, // 2FA 시도 제한
        'lockout_time' => 300, // 2FA 잠금 시간 (초)
    ],
    
    'database' => [
        'backup' => [
            'enabled' => true,
            'schedule' => '0 2 * * *', // 매일 새벽 2시
            'retention_days' => 30,
            'compress' => true,
        ],
        'maintenance' => [
            'enabled' => false,
            'message' => '시스템 점검 중입니다.',
        ],
    ],
    
    'mail' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Jiny Admin'),
        'smtp' => [
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION'),
        ],
    ],
    
    'system' => [
        'maintenance' => [
            'enabled' => false,
            'message' => '시스템 점검 중입니다.',
            'allowed_ips' => [],
        ],
        'logging' => [
            'enabled' => true,
            'level' => 'info',
            'retention_days' => 30,
        ],
        'performance' => [
            'cache_enabled' => true,
            'query_log' => false,
        ],
    ],
]; 