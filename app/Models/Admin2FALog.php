<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin2FALog extends Model
{
    protected $table = 'admin_2fa_logs';

    protected $fillable = [
        'admin_user_id',
        'action',
        'status',
        'message',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * 관리자와의 관계
     */
    public function adminUser()
    {
        return $this->belongsTo(\Jiny\Admin\App\Models\AdminUser::class, 'admin_user_id');
    }
} 