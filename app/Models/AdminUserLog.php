<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdminUserLog extends Model
{
    protected $table = 'admin_user_logs';
    
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false; // created_at만 사용하므로 timestamps 비활성화

    protected $fillable = [
        'admin_user_id',
        'ip_address',
        'user_agent',
        'status',
        'message',
        // 'id'는 auto-increment이므로 제외
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * 관리자 관계
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\App\Models\AdminUser::class, 'admin_user_id');
    }

    /**
     * 오늘 생성된 로그
     */
    public function scopeCreatedToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * 이번 주 생성된 로그
     */
    public function scopeCreatedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * 성공한 로그
     */
    public function scopeSuccess(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * 실패한 로그
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'fail');
    }

    /**
     * 특정 관리자의 로그
     */
    public function scopeByAdmin(Builder $query, string $adminUserId): Builder
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    /**
     * 관리자 이름 접근자
     */
    public function getAdminNameAttribute(): string
    {
        return $this->admin->name ?? $this->admin->email ?? 'Unknown';
    }

    /**
     * 상태 레이블
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'success' => '성공',
            'fail' => '실패',
            default => '알 수 없음'
        };
    }

    /**
     * 상태 색상 클래스
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'success' => 'text-success',
            'fail' => 'text-danger',
            default => 'text-muted'
        };
    }

    /**
     * 상태 배지 클래스
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'success' => 'badge bg-success',
            'fail' => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }
}
