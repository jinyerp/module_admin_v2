<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_user_id',
        'action',
        'table_name',
        'record_id',
        'ip_address',
        'user_agent',
        'status',
        'message',
        'session_id',
        'request_method',
        'request_url',
        'response_code',
        'processing_time',
        'additional_data',
        'failed_attempts',
        'is_account_locked',
        'is_ip_blocked',
        'is_suspicious',
        'memo'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'additional_data' => 'array',
        'is_account_locked' => 'boolean',
        'is_ip_blocked' => 'boolean',
        'is_suspicious' => 'boolean'
    ];

    /**
     * 관리자 사용자와의 관계
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * 상태 라벨 접근자
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'success' => '성공',
            'failed' => '실패',
            'blocked' => '차단',
            default => $this->status
        };
    }

    /**
     * 관리자 이름 접근자
     */
    public function getAdminNameAttribute(): ?string
    {
        return $this->admin?->name;
    }

    /**
     * 성공 상태 스코프
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * 실패 상태 스코프
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * 오늘 생성된 로그 스코프
     */
    public function scopeCreatedToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * 이번 주 생성된 로그 스코프
     */
    public function scopeCreatedThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }
}
