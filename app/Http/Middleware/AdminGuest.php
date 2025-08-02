<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // admin guard로 인증된 세션이 있으면 리다이렉트
        if (Auth::guard('admin')->check()) {
            // 이미 로그인된 경우, 대시보드로 이동
            return redirect()->route('admin.dashboard');
        }
        
        // 세션이 없으면 통과
        return $next($request);
    }
} 