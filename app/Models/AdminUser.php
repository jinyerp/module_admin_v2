<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminUser extends Authenticatable
{
    use Notifiable, HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Jiny\Admin\Database\Factories\AdminUserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'admin_level_id',
        'status',
        'is_verified',
        'email_verified_at',
        'phone',
        'avatar',
        'memo',
        'last_login_at',
        'login_count',
        'google_2fa_secret',
        'google_2fa_enabled',
        'google_2fa_backup_codes',
        'google_2fa_verified_at',
        'google_2fa_disabled_at',
        'google_2fa_required',
        'ms_2fa_secret',
        'ms_2fa_enabled',
        'ms_2fa_backup_codes',
        'ms_2fa_verified_at',
        'ms_2fa_disabled_at',
        'ms_2fa_required',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'is_verified' => 'boolean',
        'google_2fa_backup_codes' => 'array',
        'google_2fa_verified_at' => 'datetime',
        'google_2fa_disabled_at' => 'datetime',
        'google_2fa_enabled' => 'boolean',
        'google_2fa_required' => 'boolean',
        'ms_2fa_backup_codes' => 'array',
        'ms_2fa_verified_at' => 'datetime',
        'ms_2fa_disabled_at' => 'datetime',
        'ms_2fa_enabled' => 'boolean',
        'ms_2fa_required' => 'boolean',
    ];

    /**
     * 2FA가 활성화되어 있는지 확인
     */
    public function has2FAEnabled(): bool
    {
        return $this->google_2fa_enabled || $this->ms_2fa_enabled;
    }

    /**
     * 2FA 설정이 필요한지 확인
     */
    public function needs2FASetup(): bool
    {
        return $this->google_2fa_required || $this->ms_2fa_required;
    }

    /**
     * Google 2FA가 활성화되어 있는지 확인
     */
    public function hasGoogle2FAEnabled(): bool
    {
        return $this->google_2fa_enabled && !empty($this->google_2fa_secret);
    }

    /**
     * Microsoft 2FA가 활성화되어 있는지 확인
     */
    public function hasMS2FAEnabled(): bool
    {
        return $this->ms_2fa_enabled && !empty($this->ms_2fa_secret);
    }

    /**
     * 백업 코드가 있는지 확인
     */
    public function hasBackupCodes(): bool
    {
        $googleBackupCodes = $this->google_2fa_backup_codes ?? [];
        $msBackupCodes = $this->ms_2fa_backup_codes ?? [];
        
        return !empty($googleBackupCodes) || !empty($msBackupCodes);
    }

    /**
     * Google 2FA 백업 코드가 있는지 확인
     */
    public function hasGoogleBackupCodes(): bool
    {
        $backupCodes = $this->google_2fa_backup_codes ?? [];
        return !empty($backupCodes);
    }

    /**
     * Microsoft 2FA 백업 코드가 있는지 확인
     */
    public function hasMSBackupCodes(): bool
    {
        $backupCodes = $this->ms_2fa_backup_codes ?? [];
        return !empty($backupCodes);
    }

    /**
     * 백업 코드 검증
     */
    public function verifyBackupCode(string $code): bool
    {
        $googleBackupCodes = $this->google_2fa_backup_codes ?? [];
        $msBackupCodes = $this->ms_2fa_backup_codes ?? [];
        
        $allBackupCodes = array_merge($googleBackupCodes, $msBackupCodes);
        
        return in_array($code, $allBackupCodes);
    }

    /**
     * Google 2FA 백업 코드 검증
     */
    public function verifyGoogleBackupCode(string $code): bool
    {
        $backupCodes = $this->google_2fa_backup_codes ?? [];
        return in_array($code, $backupCodes);
    }

    /**
     * Microsoft 2FA 백업 코드 검증
     */
    public function verifyMSBackupCode(string $code): bool
    {
        $backupCodes = $this->ms_2fa_backup_codes ?? [];
        return in_array($code, $backupCodes);
    }

    /**
     * 백업 코드 사용 (일회용)
     */
    public function useBackupCode(string $code): bool
    {
        if ($this->verifyGoogleBackupCode($code)) {
            $backupCodes = $this->google_2fa_backup_codes ?? [];
            $backupCodes = array_values(array_filter($backupCodes, fn($c) => $c !== $code));
            $this->update(['google_2fa_backup_codes' => $backupCodes]);
            return true;
        }
        
        if ($this->verifyMSBackupCode($code)) {
            $backupCodes = $this->ms_2fa_backup_codes ?? [];
            $backupCodes = array_values(array_filter($backupCodes, fn($c) => $c !== $code));
            $this->update(['ms_2fa_backup_codes' => $backupCodes]);
            return true;
        }
        
        return false;
    }

    /**
     * 2FA 시크릿 키 가져오기
     */
    public function get2FASecret(): ?string
    {
        if ($this->google_2fa_enabled) {
            return $this->google_2fa_secret;
        }
        
        if ($this->ms_2fa_enabled) {
            return $this->ms_2fa_secret;
        }
        
        return null;
    }

    /**
     * Google 2FA 시크릿 키 가져오기
     */
    public function getGoogle2FASecret(): ?string
    {
        return $this->google_2fa_secret;
    }

    /**
     * Microsoft 2FA 시크릿 키 가져오기
     */
    public function getMS2FASecret(): ?string
    {
        return $this->ms_2fa_secret;
    }

    /**
     * 2FA 백업 코드 생성
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        }
        return $codes;
    }

    /**
     * Google 2FA 백업 코드 설정
     */
    public function setGoogleBackupCodes(array $codes): void
    {
        $this->update(['google_2fa_backup_codes' => $codes]);
    }

    /**
     * Microsoft 2FA 백업 코드 설정
     */
    public function setMSBackupCodes(array $codes): void
    {
        $this->update(['ms_2fa_backup_codes' => $codes]);
    }

    /**
     * 2FA 활성화
     */
    public function enable2FA(string $secret, string $type = 'google'): void
    {
        if ($type === 'google') {
            $this->update([
                'google_2fa_enabled' => true,
                'google_2fa_secret' => $secret,
                'google_2fa_verified_at' => now(),
            ]);
        } elseif ($type === 'ms') {
            $this->update([
                'ms_2fa_enabled' => true,
                'ms_2fa_secret' => $secret,
                'ms_2fa_verified_at' => now(),
            ]);
        }
    }

    /**
     * 2FA 비활성화
     */
    public function disable2FA(string $type = 'google'): void
    {
        if ($type === 'google') {
            $this->update([
                'google_2fa_enabled' => false,
                'google_2fa_disabled_at' => now(),
                'google_2fa_backup_codes' => null,
            ]);
        } elseif ($type === 'ms') {
            $this->update([
                'ms_2fa_enabled' => false,
                'ms_2fa_disabled_at' => now(),
                'ms_2fa_backup_codes' => null,
            ]);
        }
    }

    /**
     * 2FA 필요 여부 설정
     */
    public function set2FARequired(bool $required, string $type = 'google'): void
    {
        if ($type === 'google') {
            $this->update(['google_2fa_required' => $required]);
        } elseif ($type === 'ms') {
            $this->update(['ms_2fa_required' => $required]);
        }
    }

    /**
     * 2FA가 필요한지 확인
     */
    public function is2FARequired(): bool
    {
        return $this->google_2fa_required || $this->ms_2fa_required;
    }

    /**
     * Google 2FA가 필요한지 확인
     */
    public function isGoogle2FARequired(): bool
    {
        return $this->google_2fa_required;
    }

    /**
     * Microsoft 2FA가 필요한지 확인
     */
    public function isMS2FARequired(): bool
    {
        return $this->ms_2fa_required;
    }

    /**
     * 2FA 설정 완료 여부 확인
     */
    public function is2FASetupComplete(): bool
    {
        if ($this->google_2fa_required && !$this->hasGoogle2FAEnabled()) {
            return false;
        }
        
        if ($this->ms_2fa_required && !$this->hasMS2FAEnabled()) {
            return false;
        }
        
        return true;
    }

    /**
     * 2FA 검증 완료 여부 확인
     */
    public function is2FAVerified(): bool
    {
        if ($this->google_2fa_enabled && !$this->google_2fa_verified_at) {
            return false;
        }
        
        if ($this->ms_2fa_enabled && !$this->ms_2fa_verified_at) {
            return false;
        }
        
        return true;
    }

    /**
     * 2FA 타입 가져오기
     */
    public function get2FAType(): ?string
    {
        if ($this->google_2fa_enabled) {
            return 'google';
        }
        
        if ($this->ms_2fa_enabled) {
            return 'ms';
        }
        
        return null;
    }

    /**
     * 2FA 상태 요약 정보
     */
    public function get2FAStatus(): array
    {
        return [
            'enabled' => $this->has2FAEnabled(),
            'required' => $this->is2FARequired(),
            'setup_complete' => $this->is2FASetupComplete(),
            'verified' => $this->is2FAVerified(),
            'type' => $this->get2FAType(),
            'has_backup_codes' => $this->hasBackupCodes(),
            'google_enabled' => $this->hasGoogle2FAEnabled(),
            'ms_enabled' => $this->hasMS2FAEnabled(),
        ];
    }

    /**
     * 로그인 횟수 증가
     */
    public function incrementLoginCount(): void
    {
        $this->increment('login_count');
        $this->update(['last_login_at' => now()]);
    }

    /**
     * 관리자 등급 확인
     */
    public function isSuperAdmin(): bool
    {
        return $this->type === 'super';
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->type === 'staff';
    }

    /**
     * 상태 확인
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * 이메일 인증 확인
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * 관계 정의
     */
    public function adminLevel()
    {
        return $this->belongsTo(AdminLevel::class, 'admin_level_id');
    }

    public function logs()
    {
        return $this->hasMany(AdminUserLog::class, 'admin_user_id');
    }

    public function passwordErrors()
    {
        return $this->hasMany(AdminUserPasswordError::class, 'admin_user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_user_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_user_id');
    }

    public function permissionLogs()
    {
        return $this->hasMany(AdminPermissionLog::class, 'admin_user_id');
    }

    public function twoFALogs()
    {
        return $this->hasMany(Admin2FALog::class, 'admin_user_id');
    }
}
