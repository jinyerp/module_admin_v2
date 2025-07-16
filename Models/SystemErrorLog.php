<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class SystemErrorLog extends Model
{
    protected $table = 'system_error_logs';

    protected $fillable = [
        'error_code',
        'error_type',
        'error_message',
        'stack_trace',
        'file',
        'line',
        'function',
        'class',
        'user_type',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'request_data',
        'session_data',
        'severity',
        'is_resolved',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'session_data' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
