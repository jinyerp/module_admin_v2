<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Jiny\Admin\App\Models\AdminUser;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $table = 'admin_activity_logs';
    
    protected $fillable = [
        'admin_user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'target_type',
        'target_id',
        'old_values',
        'new_values',
        'severity',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 액션 상수
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_READ = 'read';
    const ACTION_EDIT = 'edit';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';

    /**
     * 관리자 관계
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    /**
     * 관리자 이름 접근자
     */
    public function getAdminNameAttribute(): string
    {
        return $this->adminUser->email ?? 'Unknown';
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
