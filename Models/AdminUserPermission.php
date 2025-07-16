<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdminUserPermission 모델
 *
 * 특정 관리자에게 특정 권한을 할당하는 관계를 관리
 */
class AdminUserPermission extends Model
{
    use HasFactory;

    protected $table = 'admin_user_permissions';

    protected $fillable = [
        'admin_id',
        'permission_id',
        'granted_by',
        'granted_at',
        'expires_at',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'admin_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(AdminPermission::class, 'permission_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'granted_by');
    }
}
