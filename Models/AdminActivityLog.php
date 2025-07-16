<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';

    protected $fillable = [
        'admin_id',
        'action',
        'module',
        'description',
        'target_type',
        'target_id',
        'severity',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 관리자 관계
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'admin_id');
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
     * 높은 심각도 로그
     */
    public function scopeHighSeverity(Builder $query): Builder
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    /**
     * 특정 관리자의 로그
     */
    public function scopeByAdmin(Builder $query, int $adminId): Builder
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * 관리자 이름 접근자
     */
    public function getAdminNameAttribute(): string
    {
        return $this->admin->email ?? 'Unknown';
    }

    /**
     * 심각도 레이블
     */
    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'low' => '낮음',
            'medium' => '보통',
            'high' => '높음',
            'critical' => '심각',
            default => '알 수 없음'
        };
    }

    /**
     * 심각도 색상 클래스
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'text-success',
            'medium' => 'text-warning',
            'high' => 'text-danger',
            'critical' => 'text-danger fw-bold',
            default => 'text-muted'
        };
    }
}
