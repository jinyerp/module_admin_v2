<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminPermission
 * @package Jiny\Admin\Models
 *
 * @property int $id
 * @property string $name 권한명
 * @property string $display_name 표시명
 * @property string $module 모듈
 * @property string $description 설명
 * @property bool $is_active 활성화
 * @property int $sort 정렬
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminPermission extends Model
{
    protected $table = 'admin_permissions';
    protected $fillable = [
        'name', 'display_name', 'module', 'description', 'is_active', 'sort'
    ];
}
