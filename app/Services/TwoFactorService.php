<?php

namespace Jiny\Admin\App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\Admin2FALog;

class TwoFactorService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * 새로운 2FA 시크릿 생성
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * QR 코드 URL 생성
     */
    public function generateQRCodeUrl(AdminUser $user, string $secret): string
    {
        $appName = config('app.name', 'Jiny Admin');
        $email = $user->email;
        
        return $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );
    }

    /**
     * 2FA 코드 검증
     */
    public function verifyCode(AdminUser $user, string $code): bool
    {
        if (empty($user->google_2fa_secret)) {
            Log::warning('2FA 검증 실패: 시크릿 키가 없습니다.', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            return false;
        }

        $result = $this->google2fa->verifyKey($user->google_2fa_secret, $code);
        
        Log::info('2FA 코드 검증', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'code' => $code,
            'secret' => $user->google_2fa_secret,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * 백업 코드 생성
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }
        return $codes;
    }

    /**
     * 백업 코드 검증
     */
    public function verifyBackupCode(AdminUser $user, string $code): bool
    {
        return $user->useBackupCode($code);
    }

    /**
     * 2FA 설정 완료
     */
    public function enable2FA(AdminUser $user, string $secret, array $backupCodes): void
    {
        $user->enable2FA($secret, $backupCodes);
        
        $this->log2FA($user, 'enable', 'success', '2FA 활성화 완료');
    }

    /**
     * 2FA 비활성화
     */
    public function disable2FA(AdminUser $user): void
    {
        $user->disable2FA();
        
        $this->log2FA($user, 'disable', 'success', '2FA 비활성화 완료');
    }

    /**
     * 2FA 로그 기록
     */
    public function log2FA(AdminUser $user, string $action, string $status, string $message = null): void
    {
        try {
            Admin2FALog::create([
                'admin_user_id' => $user->id,
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'metadata' => [
                    'user_email' => $user->email,
                    'user_type' => $user->type,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('2FA 로그 기록 실패: ' . $e->getMessage());
        }
    }

    /**
     * 2FA 인증 시도 로그
     */
    public function log2FAAttempt(AdminUser $user, string $code, bool $success): void
    {
        $action = $success ? 'verify_success' : 'verify_fail';
        $message = $success ? '2FA 인증 성공' : '잘못된 2FA 코드';
        
        $this->log2FA($user, $action, $success ? 'success' : 'fail', $message);
    }

    /**
     * 백업 코드 사용 로그
     */
    public function logBackupCodeUsage(AdminUser $user, string $code, bool $success): void
    {
        $action = $success ? 'backup_used' : 'backup_fail';
        $message = $success ? '백업 코드 사용' : '잘못된 백업 코드';
        
        $this->log2FA($user, $action, $success ? 'success' : 'fail', $message);
    }
}
