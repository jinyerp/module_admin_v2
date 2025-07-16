<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUser;

class AdminAuthenticate
{
    public function handle($request, Closure $next)
    {
        if (config('admin.auth.use_admin_guard', false)) {
            // 고정된 admin 가드 사용
            if (!Auth::guard('admin')->check()) {
                return redirect()->route('admin.login');
            }

        } else {
            // 기본 Auth + admin_users 체크
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            $admin = AdminUser::where('email', $user->email)->first();
            if (!$admin) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => '관리자 권한이 없습니다.']);
            }
        }

        return $next($request);
    }
}
