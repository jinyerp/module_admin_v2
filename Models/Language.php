<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Language 모델
 *
 * 시스템에서 지원하는 언어 정보를 관리합니다.
 */
class Language extends Model
{
    use HasFactory;

    protected $table = 'admin_language';

    protected $fillable = [
        'code',
        'name',
        'flag',
        'country',
        'users',
        'users_percent',
        'enable',
    ];

    protected $casts = [
        'enable' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // 스코프: 활성화된 언어만
    public function scopeActive($query)
    {
        return $query->where('enable', 1);
    }

    // 정적 메서드: 활성 언어 목록
    public static function getActive()
    {
        return static::active()->orderBy('name')->get();
    }

    // 정적 메서드: 코드로 언어 찾기
    public static function findByCode(string $code)
    {
        return static::where('code', $code)->first();
    }

    // 정적 메서드: 이름으로 언어 찾기
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }
} 