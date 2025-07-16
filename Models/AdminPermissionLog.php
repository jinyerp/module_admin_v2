<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 관리자 권한 로그 모델
 *
 * 관리자의 모든 권한 관련 활동을 상세히 기록합니다.
 * - 권한 부여/회수 이력 추적
 * - 권한 체크 및 접근 거부 기록
 * - 리소스별 권한 활동 추적
 * - 보안 관련 정보 수집
 */
class AdminPermissionLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'admin_permission_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'admin_id',
        'permission_name',
        'resource_type',
        'resource_id',
        'action',
        'result',
        'ip_address',
        'user_agent',
        'reason',
        'context',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 권한 액션 상수
     */
    const ACTION_GRANT = 'grant';
    const ACTION_REVOKE = 'revoke';
    const ACTION_CHECK = 'check';
    const ACTION_DENY = 'deny';

    /**
     * 결과 상수
     */
    const RESULT_SUCCESS = 'success';
    const RESULT_FAILED = 'failed';
    const RESULT_DENIED = 'denied';

    /**
     * 관리자와의 관계
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'admin_id');
    }

    /**
     * 권한 부여 로그 생성
     */
    public static function logGrant(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): self
    {
        return self::create([
            'admin_id' => $adminId,
            'permission_name' => $permissionName,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => self::ACTION_GRANT,
            'result' => self::RESULT_SUCCESS,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 권한 회수 로그 생성
     */
    public static function logRevoke(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): self
    {
        return self::create([
            'admin_id' => $adminId,
            'permission_name' => $permissionName,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => self::ACTION_REVOKE,
            'result' => self::RESULT_SUCCESS,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 권한 체크 로그 생성
     */
    public static function logCheck(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, bool $hasPermission = true): self
    {
        return self::create([
            'admin_id' => $adminId,
            'permission_name' => $permissionName,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => self::ACTION_CHECK,
            'result' => $hasPermission ? self::RESULT_SUCCESS : self::RESULT_DENIED,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 권한 거부 로그 생성
     */
    public static function logDeny(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): self
    {
        return self::create([
            'admin_id' => $adminId,
            'permission_name' => $permissionName,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'action' => self::ACTION_DENY,
            'result' => self::RESULT_DENIED,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 성공한 권한 활동만 조회
     */
    public function scopeSuccessful($query)
    {
        return $query->where('result', self::RESULT_SUCCESS);
    }

    /**
     * 실패한 권한 활동만 조회
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('result', [self::RESULT_FAILED, self::RESULT_DENIED]);
    }

    /**
     * 특정 권한명으로 조회
     */
    public function scopeByPermission($query, string $permissionName)
    {
        return $query->where('permission_name', $permissionName);
    }

    /**
     * 특정 리소스로 조회
     */
    public function scopeByResource($query, string $resourceType, ?int $resourceId = null)
    {
        $query->where('resource_type', $resourceType);

        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }

        return $query;
    }

    /**
     * 특정 액션으로 조회
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 최근 활동 조회
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 권한 부여 활동인지 확인
     */
    public function isGrantAction(): bool
    {
        return $this->action === self::ACTION_GRANT;
    }

    /**
     * 권한 회수 활동인지 확인
     */
    public function isRevokeAction(): bool
    {
        return $this->action === self::ACTION_REVOKE;
    }

    /**
     * 권한 체크 활동인지 확인
     */
    public function isCheckAction(): bool
    {
        return $this->action === self::ACTION_CHECK;
    }

    /**
     * 권한 거부 활동인지 확인
     */
    public function isDenyAction(): bool
    {
        return $this->action === self::ACTION_DENY;
    }

    /**
     * 성공한 활동인지 확인
     */
    public function isSuccessful(): bool
    {
        return $this->result === self::RESULT_SUCCESS;
    }

    /**
     * 실패한 활동인지 확인
     */
    public function isFailed(): bool
    {
        return in_array($this->result, [self::RESULT_FAILED, self::RESULT_DENIED]);
    }

    /**
     * 거부된 활동인지 확인
     */
    public function isDenied(): bool
    {
        return $this->result === self::RESULT_DENIED;
    }
}
