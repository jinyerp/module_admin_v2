<?php

return [
    '2fa' => [
        'enabled' => env('ADMIN_2FA_ENABLED', true),
        'force_enable' => env('ADMIN_2FA_FORCE_ENABLE', false), // 모든 관리자에게 강제 적용
        'backup_codes_count' => env('ADMIN_2FA_BACKUP_CODES_COUNT', 8),
        'qr_code_size' => env('ADMIN_2FA_QR_SIZE', 200),
        'issuer' => env('ADMIN_2FA_ISSUER', 'Jiny Admin'),
        'window' => env('ADMIN_2FA_WINDOW', 1), // TOTP 검증 윈도우
    ],
]; 