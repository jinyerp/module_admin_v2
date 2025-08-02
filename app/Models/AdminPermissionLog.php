<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 관리자 권한 로그 모델 (단순화된 버전)
 *
 * 관리자의 권한 관련 활동을 기록합니다.
 * - 권한 사용 이력
 * - 접근 거부 기록
 * - 보안 감사 및 추적
 */
class AdminPermissionLog extends Model
{
    /**
     * 테이블명
     */
    protected $table = 'admin_permission_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'admin_user_id',    // 권한 활동 수행자
        'action',           // 수행한 액션 (create, read, update, delete, list)
        'resource_type',    // 리소스 타입 (level, user, country 등)
        'resource_id',      // 리소스 ID
        'result',           // 결과 (success, denied, failed)
        'ip_address',       // IP 주소
        'user_agent',       // 사용자 에이전트
        'reason',           // 사유
    ];

    /**
     * 타입 캐스팅 설정
     */
    protected $casts = [
        'resource_id' => 'integer',
    ];

    /**
     * 숨겨진 속성들
     */
    protected $hidden = [
        'updated_at',
    ];

    /**
     * 관리자 관계
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * 성공한 활동만 조회하는 스코프
     */
    public function scopeSuccessful($query)
    {
        return $query->where('result', 'success');
    }

    /**
     * 거부된 활동만 조회하는 스코프
     */
    public function scopeDenied($query)
    {
        return $query->where('result', 'denied');
    }

    /**
     * 실패한 활동만 조회하는 스코프
     */
    public function scopeFailed($query)
    {
        return $query->where('result', 'failed');
    }

    /**
     * 특정 액션만 조회하는 스코프
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 특정 관리자의 활동만 조회하는 스코프
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_user_id', $adminId);
    }

    /**
     * 특정 리소스의 활동만 조회하는 스코프
     */
    public function scopeByResource($query, $resourceType, $resourceId = null)
    {
        $query->where('resource_type', $resourceType);
        
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        
        return $query;
    }

    /**
     * 특정 IP 주소의 활동만 조회하는 스코프
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 특정 기간의 활동만 조회하는 스코프
     */
    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * 최근 활동만 조회하는 스코프
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 활동이 성공했는지 확인
     */
    public function isSuccessful(): bool
    {
        return $this->result === 'success';
    }

    /**
     * 활동이 거부되었는지 확인
     */
    public function isDenied(): bool
    {
        return $this->result === 'denied';
    }

    /**
     * 활동이 실패했는지 확인
     */
    public function isFailed(): bool
    {
        return $this->result === 'failed';
    }

    /**
     * 활동 결과 텍스트 조회
     */
    public function getResultText(): string
    {
        return match($this->result) {
            'success' => '성공',
            'denied' => '거부',
            'failed' => '실패',
            default => '알 수 없음'
        };
    }

    /**
     * 활동 액션 텍스트 조회
     */
    public function getActionText(): string
    {
        return match($this->action) {
            'list' => '목록 조회',
            'create' => '생성',
            'read' => '상세 조회',
            'update' => '수정',
            'delete' => '삭제',
            default => '알 수 없음'
        };
    }

    /**
     * 활동 결과 색상 조회
     */
    public function getResultColor(): string
    {
        return match($this->result) {
            'success' => 'green',
            'denied' => 'yellow',
            'failed' => 'red',
            default => 'gray'
        };
    }

    /**
     * 활동 액션 색상 조회
     */
    public function getActionColor(): string
    {
        return match($this->action) {
            'list' => 'blue',
            'create' => 'green',
            'read' => 'blue',
            'update' => 'yellow',
            'delete' => 'red',
            default => 'gray'
        };
    }
}
