<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

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
        return false; // 임시로 false 반환
    }

    /**
     * 2FA 설정이 필요한지 확인
     */
    public function needs2FASetup(): bool
    {
        return false; // 임시로 false 반환
    }
}
