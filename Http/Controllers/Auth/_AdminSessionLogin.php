<?php

namespace Jiny\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Jiny\Admin\Models\AdminUser;
use Jiny\Admin\Models\AdminUserLog;

/**
 * 관리자 세션 로그인 컨트롤러 (config 기반 조건부 guard 사용)
 */
class AdminSessionLogin extends Controller
{
    /**
     * 로그인 폼 출력
     */
    public function showLoginForm()
    {
        return view('jiny-admin::auth.index', [
            'register_enabled' => false
        ]);
    }

    /**
     * 로그인 처리 (config 기반 조건부 guard 사용)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip = $request->ip();
        $ua = $request->header('User-Agent');
        $logStatus = 'fail';
        $logMsg = null;

        if (config('admin.auth.use_admin_guard', false)) {
            // 고정된 admin 가드 사용
            if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
                $admin = Auth::guard('admin')->user();
                $admin->last_login_at = now();
                $admin->login_count = ($admin->login_count ?? 0) + 1;
                $admin->save();
                $logStatus = 'success';
                $logMsg = '로그인 성공';

                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }
                $this->log($admin->id, $ip, $ua, $logStatus, $logMsg);
                return redirect()->intended(route('admin.dashboard'))->with('success', '관리자 로그인 (Guard)');
            } else {
                $logMsg = '이메일 또는 비밀번호 불일치';
                $this->log(null, $ip, $ua, $logStatus, $logMsg);
                return back()->withErrors(['email' => $logMsg])->onlyInput('email');
            }
        } else {
            // 기본 Auth + admin_users 체크
            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $user = Auth::user();
                $admin = AdminUser::where('email', $user->email)->first();

                if ($admin) {
                    $admin->last_login_at = now();
                    $admin->login_count = ($admin->login_count ?? 0) + 1;
                    $admin->save();
                    $logStatus = 'success';
                    $logMsg = '로그인 성공';

                    if ($request->hasSession()) {
                        $request->session()->regenerate();
                    }
                    $this->log($admin->id, $ip, $ua, $logStatus, $logMsg);
                    return redirect()->intended(route('admin.dashboard'))->with('success', '관리자 로그인 성공');
                } else {
                    Auth::logout();
                    $logMsg = '관리자 권한이 없습니다.';
                    $this->log(null, $ip, $ua, $logStatus, $logMsg);
                    return back()->withErrors(['email' => $logMsg])->onlyInput('email');
                }
            } else {
                $logMsg = '이메일 또는 비밀번호 불일치';
                $this->log(null, $ip, $ua, $logStatus, $logMsg);
                return back()->withErrors(['email' => $logMsg])->onlyInput('email');
            }
        }
    }

    /**
     * AJAX 로그인: JSON 응답
     */
    public function loginAjax(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $ip = $request->ip();
        $ua = $request->header('User-Agent');
        $logStatus = 'fail';
        $logMsg = null;

        if (config('admin.auth.use_admin_guard', false)) {
            // 고정된 admin 가드 사용
            if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
                $admin = Auth::guard('admin')->user();
                $admin->last_login_at = now();
                $admin->login_count = ($admin->login_count ?? 0) + 1;
                $admin->save();
                $logStatus = 'success';
                $logMsg = '로그인 성공';

                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }
                $this->log($admin->id, $ip, $ua, $logStatus, $logMsg);
                return response()->json([
                    'success' => true,
                    'message' => '관리자 로그인 (Guard)',
                    'redirect' => route('admin.dashboard'),
                    'user' => $admin
                ]);
            } else {
                $logMsg = '이메일 또는 비밀번호 불일치';
                $this->log(null, $ip, $ua, $logStatus, $logMsg);
                return response()->json([
                    'success' => false,
                    'message' => $logMsg
                ], 401);
            }
        } else {
            // 기본 Auth + admin_users 체크
            if (Auth::attempt($credentials, $request->filled('remember'))) {
                $user = Auth::user();
                $admin = AdminUser::where('email', $user->email)->first();

                if ($admin) {
                    $admin->last_login_at = now();
                    $admin->login_count = ($admin->login_count ?? 0) + 1;
                    $admin->save();
                    $logStatus = 'success';
                    $logMsg = '로그인 성공';

                    if ($request->hasSession()) {
                        $request->session()->regenerate();
                    }
                    $this->log($admin->id, $ip, $ua, $logStatus, $logMsg);
                    return response()->json([
                        'success' => true,
                        'message' => '관리자 로그인 성공',
                        'redirect' => route('admin.dashboard'),
                        'user' => $admin
                    ]);
                } else {
                    Auth::logout();
                    $logMsg = '관리자 권한이 없습니다.';
                    $this->log(null, $ip, $ua, $logStatus, $logMsg);
                    return response()->json([
                        'success' => false,
                        'message' => $logMsg
                    ], 403);
                }
            } else {
                $logMsg = '이메일 또는 비밀번호 불일치';
                $this->log(null, $ip, $ua, $logStatus, $logMsg);
                return response()->json([
                    'success' => false,
                    'message' => $logMsg
                ], 401);
            }
        }
    }




    /**
     * 로그아웃 처리 (config 기반 조건부 guard 사용)
     */
    public function logout(Request $request)
    {
        $ip = $request->ip();
        $ua = $request->header('User-Agent');
        $admin = null;

        if (config('admin.auth.use_admin_guard', false)) {
            // 고정된 admin 가드 사용
            $admin = Auth::guard('admin')->user();
            Auth::guard('admin')->logout();

        } else {
            // 기본 Auth 로그아웃
            $user = Auth::user();
            if ($user) {
                $admin = AdminUser::where('email', $user->email)->first();
            }
            Auth::logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // 로그아웃 기록
        if ($admin) {
            $this->log($admin->id, $ip, $ua, 'success', '로그아웃');
        }

        return redirect()->route('admin.login')->with('success', '로그아웃되었습니다.');
    }

    /**
     * 로그인/로그아웃 기록 저장
     */
    protected function log($adminUserId, $ip, $ua, $status, $msg)
    {
        if ($adminUserId) {
            AdminUserLog::create([
                'id' => (string) Str::uuid(),
                'admin_user_id' => $adminUserId,
                'ip_address' => $ip,
                'user_agent' => $ua,
                'status' => $status,
                'message' => $msg,
                'created_at' => now(),
            ]);
        }
    }
}
