<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * 관리자 회원 모델
 *
 * - 관리자 전용 인증 및 정보 관리
 * - 슈퍼관리자(super), 일반관리자(admin), 스태프(staff) 등 다양한 등급 지원
 * - 별도의 admin_users 테이블을 사용하여 보안 및 관리 분리
 */
class AdminUser extends Authenticatable
{
    /**
     * 테이블명
     * @var string
     */
    protected $table = 'admin_users';

    /**
     * PK 타입 및 auto-increment 사용
     */
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    /**
     * 대량 할당 가능 필드
     * @var array
     */
    protected $fillable = [
        'name', // 관리자 이름
        'email', // 관리자 이메일(로그인)
        'password', // 비밀번호(해시)
        'type', // 관리자 등급(super, admin, staff 등)
        'status', // 계정 상태(active, inactive, suspended 등)
        'last_login_at', // 마지막 로그인 일시
        'login_count', // 로그인 횟수
        'is_verified', // 이메일 인증 여부
        'email_verified_at', // 이메일 인증 일시
        'phone', // 연락처(선택)
        'avatar', // 프로필 이미지(선택)
        'memo', // 관리자 메모(선택)
        'remember_token', // 자동 로그인 토큰
        
        'google_2fa_secret', // Google Authenticator 시크릿 키
        'google_2fa_enabled', // 2FA 활성화 여부
        'google_2fa_backup_codes', // 백업 코드 (JSON)
        'google_2fa_verified_at', // 2FA 설정 완료 시각
        'google_2fa_disabled_at', // 2FA 비활성화 시각
        'google_2fa_required' // 2FA 강제 설정 여부
    ];

    /**
     * 숨김 처리 필드
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'google_2fa_secret',
    ];

    /**
     * 타입 캐스팅
     * @var array
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        
        'google_2fa_enabled' => 'boolean',
        'google_2fa_backup_codes' => 'array',
        'google_2fa_verified_at' => 'datetime',
        'google_2fa_disabled_at' => 'datetime',
        'google_2fa_required' => 'boolean',
    ];

    // === 도메인 지식 ===
    // - 관리자 등급(type):
    //   * super: 시스템 전체 권한, 모든 관리자 관리 가능
    //   * admin: 일반 관리 권한, 일부 시스템 설정 가능
    //   * staff: 제한적 관리 권한, 주로 운영 지원
    // - status: active(활성), inactive(비활성), suspended(정지)
    // - is_verified: 이메일 인증 여부(보안 강화)
    // - login_count, last_login_at: 보안 모니터링 및 감사 용도
    // - memo: 내부 관리용 메모(예: 권한 변경 이력 등)
    // - google_2fa_*: Google Authenticator 2차인증 관련 필드

    /**
     * 2FA가 활성화되어 있는지 확인
     */
    public function has2FAEnabled(): bool
    {
        return $this->google_2fa_enabled && !empty($this->google_2fa_secret);
    }

    /**
     * 2FA가 설정되어 있는지 확인 (활성화 여부와 관계없이)
     */
    public function has2FASetup(): bool
    {
        return !empty($this->google_2fa_secret);
    }

    /**
     * 백업 코드가 있는지 확인
     */
    public function hasBackupCodes(): bool
    {
        return !empty($this->google_2fa_backup_codes) && is_array($this->google_2fa_backup_codes);
    }

    /**
     * 백업 코드 사용
     */
    public function useBackupCode(string $code): bool
    {
        if (!$this->hasBackupCodes()) {
            return false;
        }

        $backupCodes = $this->google_2fa_backup_codes;
        $index = array_search($code, $backupCodes);

        if ($index !== false) {
            unset($backupCodes[$index]);
            $this->google_2fa_backup_codes = array_values($backupCodes);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * 2FA 설정 완료
     */
    public function enable2FA(string $secret, array $backupCodes): void
    {
        $this->google_2fa_secret = $secret;
        $this->google_2fa_enabled = true;
        $this->google_2fa_backup_codes = $backupCodes;
        $this->google_2fa_verified_at = now();
        $this->google_2fa_disabled_at = null;
        $this->save();
    }

    /**
     * 2FA 비활성화
     */
    public function disable2FA(): void
    {
        $this->google_2fa_enabled = false;
        $this->google_2fa_disabled_at = now();
        $this->save();
    }

    /**
     * 2FA가 강제 설정되어야 하는지 확인
     */
    public function is2FARequired(): bool
    {
        // 개별 관리자 강제 설정 확인
        if ($this->google_2fa_required) {
            return true;
        }
        
        // 시스템 전체 강제 설정 확인
        if (config('admin.2fa.force_enable', false)) {
            return true;
        }
        
        // 특정 관리자 타입에 대한 강제 설정
        $requiredTypes = config('admin.2fa.required_types', ['super', 'admin']);
        if (in_array($this->type, $requiredTypes)) {
            return true;
        }
        
        return false;
    }

    /**
     * 2FA 설정이 완료되어야 하는지 확인
     */
    public function needs2FASetup(): bool
    {
        return $this->is2FARequired() && !$this->has2FAEnabled();
    }
}
