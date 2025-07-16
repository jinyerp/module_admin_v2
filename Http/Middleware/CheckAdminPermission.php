<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminUser;
use App\Models\Admin\AdminUserPermission;

class CheckAdminPermission
{
    /**
     * 관리자 권한 체크 미들웨어
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        // 현재 로그인한 관리자 ID 가져오기 (임시로 UUID 사용)
        $adminId = auth()->id() ?? '550e8400-e29b-41d4-a716-446655440000';

        // 관리자 정보 조회
        $admin = AdminUser::find($adminId);

        if (!$admin) {
            return response()->json(['error' => '관리자 정보를 찾을 수 없습니다.'], 403);
        }

        // 최고관리자는 모든 권한 허용
        if ($admin->role === 'super_admin') {
            return $next($request);
        }

        // 권한 체크
        $hasPermission = AdminUserPermission::where('admin_id', $adminId)
            ->whereHas('permission', function($query) use ($permission) {
                $query->where('name', $permission)
                      ->where('is_active', true);
            })
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['error' => '권한이 없습니다.'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', '해당 기능에 대한 권한이 없습니다.');
        }

        return $next($request);
    }
}
