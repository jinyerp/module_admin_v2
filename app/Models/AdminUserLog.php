<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
