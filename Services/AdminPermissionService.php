<?php

namespace Jiny\Admin\Services;

use App\Models\Admin\AdminPermission;
use App\Models\Admin\AdminUserPermission;
use Jiny\Admin\Models\AdminUser;

class AdminPermissionService
{
    /**
     * 권한 목록 조회
     */
    public function getPermissions(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = AdminPermission::query();

        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * 권한 생성
     */
    public function createPermission(array $data): AdminPermission
    {
        return AdminPermission::create($data);
    }

    /**
     * 권한 수정
     */
    public function updatePermission(AdminPermission $permission, array $data): bool
    {
        return $permission->update($data);
    }

    /**
     * 권한 삭제
     */
    public function deletePermission(AdminPermission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * 관리자에게 권한 할당
     */
    public function assignPermissionToAdmin(string $adminId, int $permissionId, string $grantedBy = null, string $reason = null, $expiresAt = null): AdminUserPermission
    {
        return AdminUserPermission::create([
            'admin_id' => $adminId,
            'permission_id' => $permissionId,
            'granted_by' => $grantedBy,
            'granted_at' => now(),
            'expires_at' => $expiresAt,
            'reason' => $reason,
            'is_active' => true,
        ]);
    }

    /**
     * 관리자의 권한 조회
     */
    public function getAdminPermissions(string $adminId): \Illuminate\Database\Eloquent\Collection
    {
        return AdminUserPermission::where('admin_id', $adminId)
            ->where('is_active', true)
            ->with('permission')
            ->get();
    }

    /**
     * 관리자의 권한 확인
     */
    public function hasPermission(string $adminId, string $permissionName): bool
    {
        return AdminUserPermission::where('admin_id', $adminId)
            ->where('is_active', true)
            ->whereHas('permission', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }
}
