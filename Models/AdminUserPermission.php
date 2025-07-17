<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminUserPermission
 * @package Jiny\Admin\Models
 *
 * @property int $id
 * @property int $admin_user_id 관리자ID
 * @property int $permission_id 권한ID
 * @property string|null $granted_at 부여일시
 * @property string|null $expired_at 만료일시
 * @property string $status 상태
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminUserPermission extends Model
{
    protected $table = 'admin_user_permissions';
    protected $fillable = [
        'admin_user_id', 'permission_id', 'granted_at', 'expired_at', 'status'
    ];
}
