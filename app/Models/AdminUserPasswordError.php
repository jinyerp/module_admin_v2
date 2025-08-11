<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 관리자 사용자 비밀번호 오류 기록 모델
 * 
 * 비밀번호 오류, 계정 잠금 등의 보안 이벤트를 기록합니다.
 */
class AdminUserPasswordError extends Model
{
    /**
     * 테이블명
     */
    protected $table = 'admin_user_password_error';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'admin_user_id',
        'email',
        'ip_address',
        'user_agent',
        'error_at',
        'error_type',
        'error_message',
        'additional_data',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'error_at' => 'datetime',
        'additional_data' => 'array',
    ];

    /**
     * AdminUser와의 관계
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id', 'id');
    }

    /**
     * 특정 이메일의 최근 오류 기록 조회
     */
    public static function getRecentErrors(string $email, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('email', $email)
            ->where('error_at', '>=', now()->subHours($hours))
            ->orderBy('error_at', 'desc')
            ->get();
    }

    /**
     * 특정 이메일의 오류 횟수 조회
     */
    public static function getErrorCount(string $email, int $hours = 24): int
    {
        return static::where('email', $email)
            ->where('error_at', '>=', now()->subHours($hours))
            ->count();
    }

    /**
     * 특정 IP의 오류 기록 조회
     */
    public static function getErrorsByIp(string $ip, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('ip_address', $ip)
            ->where('error_at', '>=', now()->subHours($hours))
            ->orderBy('error_at', 'desc')
            ->get();
    }

    /**
     * 오류 기록 생성 (헬퍼 메서드)
     */
    public static function logError(
        ?string $adminUserId,
        string $email,
        string $errorType = 'password',
        ?string $errorMessage = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $additionalData = null
    ): self {
        return static::create([
            'admin_user_id' => $adminUserId,
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'error_at' => now(),
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'additional_data' => $additionalData,
        ]);
    }

    /**
     * 오래된 오류 기록 정리
     */
    public static function cleanupOldRecords(int $days = 90): int
    {
        return static::where('error_at', '<', now()->subDays($days))->delete();
    }
}
