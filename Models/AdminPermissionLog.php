<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminPermissionLog
 * @package Jiny\Admin\Models
 *
 * @property int $id
 * @property int $permission_id 권한ID
 * @property int $admin_user_id 관리자ID
 * @property string $action 액션
 * @property string $ip_address IP
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminPermissionLog extends Model
{
    protected $table = 'admin_permission_logs';
    protected $fillable = [
        'permission_id', 'admin_user_id', 'action', 'ip_address'
    ];
}
