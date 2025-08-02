<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AdminSetting 모델
 *
 * 시스템의 모든 설정값을 중앙에서 관리하는 모델입니다.
 *
 * 주요 기능:
 * - 애플리케이션 설정 (데이터베이스 기반 설정 관리)
 * - 설정 그룹별 분류 및 관리 (조직화된 설정 관리)
 * - 설정값 타입 관리 (string, boolean, integer, json 등)
 * - 공개/비공개 설정 구분 (보안 강화)
 * - 설정 설명 및 메타데이터 관리 (설정 이해도 향상)
 */
class AdminSetting extends Model
{
    use HasFactory;

    protected $table = 'admin_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];
}
