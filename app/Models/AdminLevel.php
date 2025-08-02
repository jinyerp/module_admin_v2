<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 관리자 등급 모델
 *
 * 시스템에서 지원하는 관리자 등급을 정의하고 권한을 관리합니다.
 * - 등급별 CRUD 권한 정의
 * - 등급별 사용자 관리
 * - 권한 체크 기능 제공
 */
class AdminLevel extends Model
{
    protected $table = 'admin_levels';

    protected $fillable = [
        'name',           // 등급명
        'code',           // 등급 코드 (unique)
        'badge_color',    // 배지 색상
        'can_list',       // 목록 조회 권한
        'can_create',     // 생성 권한
        'can_read',       // 조회 권한
        'can_update',     // 수정 권한
        'can_delete',     // 삭제 권한
    ];

    protected $casts = [
        'can_list' => 'boolean',
        'can_create' => 'boolean',
        'can_read' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
    ];

    /**
     * 이 등급을 사용하는 관리자들
     */
    public function users(): HasMany
    {
        return $this->hasMany(AdminUser::class, 'type', 'code');
    }

    /**
     * 특정 권한을 가지고 있는지 확인
     */
    public function hasPermission(string $permission): bool
    {
        return match($permission) {
            'list' => $this->can_list,
            'create' => $this->can_create,
            'read' => $this->can_read,
            'update' => $this->can_update,
            'delete' => $this->can_delete,
            default => false,
        };
    }

    /**
     * 여러 권한 중 하나라도 가지고 있는지 확인
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 모든 권한을 가지고 있는지 확인
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 모든 권한을 가지고 있는지 확인
     */
    public function hasFullAccess(): bool
    {
        return $this->hasAllPermissions(['list', 'create', 'read', 'update', 'delete']);
    }

    /**
     * 읽기 전용 권한만 가지고 있는지 확인
     */
    public function isReadOnly(): bool
    {
        return $this->can_read && !$this->can_create && !$this->can_update && !$this->can_delete;
    }

    /**
     * 활성 등급만 조회하는 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 특정 권한을 가진 등급만 조회하는 스코프
     */
    public function scopeWithPermission($query, string $permission)
    {
        $column = 'can_' . $permission;
        if (in_array($column, ['can_list', 'can_create', 'can_read', 'can_update', 'can_delete'])) {
            return $query->where($column, true);
        }
        return $query;
    }

    /**
     * 사용 중인 등급인지 확인
     */
    public function isInUse(): bool
    {
        return $this->users()->exists();
    }

    /**
     * 사용자 수 조회
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    /**
     * 등급 권한 요약 조회
     */
    public function getPermissionSummary(): array
    {
        return [
            'list' => $this->can_list,
            'create' => $this->can_create,
            'read' => $this->can_read,
            'update' => $this->can_update,
            'delete' => $this->can_delete,
        ];
    }

    /**
     * 권한 텍스트 조회
     */
    public function getPermissionText(): string
    {
        $permissions = [];
        if ($this->can_list) $permissions[] = '목록';
        if ($this->can_create) $permissions[] = '생성';
        if ($this->can_read) $permissions[] = '조회';
        if ($this->can_update) $permissions[] = '수정';
        if ($this->can_delete) $permissions[] = '삭제';

        return implode(', ', $permissions) ?: '권한 없음';
    }
} 