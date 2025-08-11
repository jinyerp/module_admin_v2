<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Jiny\Admin\App\Models\AdminUser;
use App\Models\Admin\AdminUserPermission;

class CheckAdminPermission
{
    /**
     * 관리자 권한 체크 미들웨어
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        \Log::info('CheckAdminPermission middleware is running', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'permission' => $permission
        ]);
        
        // admin 가드로 인증된 사용자 확인
        $adminGuard = Auth::guard('admin');
        if (!$adminGuard->check()) {
            \Log::warning('Admin not authenticated in permission check');
            
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => '관리자 로그인이 필요합니다.',
                    'redirect' => route('admin.login')
                ], 401);
            }
            
            return redirect()->route('admin.login')
                ->with('error', '관리자 로그인이 필요합니다.');
        }

        // 현재 로그인한 관리자 정보 가져오기
        $admin = $adminGuard->user();
        
        \Log::info('Admin permission check', [
            'admin_id' => $admin->id,
            'admin_role' => $admin->role ?? 'unknown',
            'permission' => $permission
        ]);

        if (!$admin) {
            \Log::error('Admin user not found in permission check');
            
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => '관리자 정보를 찾을 수 없습니다.'
                ], 403);
            }
            
            return redirect()->route('admin.login')
                ->with('error', '관리자 정보를 찾을 수 없습니다.');
        }

        // 최고관리자는 모든 권한 허용
        if ($admin->role === 'super_admin') {
            \Log::info('Super admin access granted');
            return $next($request);
        }

        // 권한 체크
        $hasPermission = AdminUserPermission::where('admin_id', $admin->id)
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

        \Log::info('Permission check result', [
            'admin_id' => $admin->id,
            'permission' => $permission,
            'has_permission' => $hasPermission
        ]);

        if (!$hasPermission) {
            \Log::warning('Permission denied', [
                'admin_id' => $admin->id,
                'permission' => $permission
            ]);
            
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 기능에 대한 권한이 없습니다.'
                ], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', '해당 기능에 대한 권한이 없습니다.');
        }

        \Log::info('Permission granted');
        return $next($request);
    }
    
    /**
     * AJAX 요청인지 확인
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->header('Accept') === 'application/json';
    }
}
