<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Admin2FALog 모델
 * 
 * 관리자 2단계 인증(2FA) 로그를 관리하는 모델
 */
class Admin2FALog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'admin_2fa_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'admin_user_id',
        'action',
        'status',
        'ip_address',
        'user_agent',
        'message',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 숨겨진 속성들
     */
    protected $hidden = [
        'user_agent',
    ];

    /**
     * AdminUser와의 관계
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * 성공한 로그만 조회하는 스코프
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * 실패한 로그만 조회하는 스코프
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'fail');
    }

    /**
     * 특정 관리자의 로그만 조회하는 스코프
     */
    public function scopeByAdminUser($query, $adminUserId)
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    /**
     * 특정 IP의 로그만 조회하는 스코프
     */
    public function scopeByIpAddress($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 특정 액션의 로그만 조회하는 스코프
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 최근 로그만 조회하는 스코프
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Jiny\Admin\Database\Factories\Admin2FALogFactory::new();
    }
} 