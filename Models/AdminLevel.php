<?php
namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLevel extends Model
{
    use HasFactory;

    protected $table = 'admin_levels';

    protected $fillable = [
        'name', 'code', 'badge_color', 'can_list', 'can_create', 'can_read', 'can_update', 'can_delete'
    ];

    public function users()
    {
        return $this->hasMany(
            \Jiny\Admin\Models\AdminUser::class, 
            'admin_level_id');
    }
} 