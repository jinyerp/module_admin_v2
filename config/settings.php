<?php

return [
    'auth' => [
        'login' => [
            'max_attempts' => 5,
            'lockout_time' => 300,
            'remember_me' => true,
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