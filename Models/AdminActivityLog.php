<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $table = 'admin_activity_logs';
    protected $fillable = [
        'admin_user_id',
        'action',
        'description',
        'ip_address',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
