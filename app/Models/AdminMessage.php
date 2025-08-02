<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AdminMessage 모델
 *
 * 관리자가 사용자에게 전송하는 메시지 관리
 */
class AdminMessage extends Model
{
    use HasFactory;

    protected $table = 'admin_messages';

    protected $fillable = [
        'admin_id',
        'user_id',
        'title',
        'content',
        'type',
        'status',
        'sent_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // 관계 등은 필요시 추가
}
