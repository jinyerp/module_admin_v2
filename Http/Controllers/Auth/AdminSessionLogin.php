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
 * 관리자 세션 로그인 컨트롤러 (admin 가드 전용)
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
     * 로그인 처리 (admin 가드 전용)
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

        // admin 가드 사용
        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $admin = Auth::guard('admin')->user();

            // 로그인 정보 업데이트
            $admin->last_login_at = now();
            $admin->login_count = ($admin->login_count ?? 0) + 1;
            $admin->save();

            $logStatus = 'success';
            $logMsg = '로그인 성공';

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // 위치 정보 (GeoIP)
            $loginLocation = null;
            if (class_exists('Location')) {
                $location = \Location::get($ip);
                if ($location) {
                    $loginLocation = ($location->cityName ? $location->cityName.', ' : '').$location->countryName;
                }
            }
            // 디바이스 정보 (User Agent)
            $device = null;
            if (class_exists('Jenssegers\\Agent\\Agent')) {
                $agent = new \Jenssegers\Agent\Agent();
                $agent->setUserAgent($ua);
                $device = $agent->device().' / '.$agent->platform().' / '.$agent->browser();
            }
            
            // admin_sessions 테이블에 세션 정보 upsert
            $sessionId = $request->session()->getId();
            \DB::table('admin_sessions')->updateOrInsert(
                ['session_id' => $sessionId],
                [
                    'admin_user_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'admin_email' => $admin->email,
                    'admin_type' => $admin->type,
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                    'login_location' => $loginLocation,
                    'device' => $device,
                    'login_at' => now(),
                    'last_activity' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->log($admin->id, $ip, $ua, $logStatus, $logMsg);
            return redirect()->intended(route('admin.dashboard'))->with('success', '관리자 로그인 성공');
        } else {
            $logMsg = '이메일 또는 비밀번호가 일치하지 않습니다.';
            $this->log(null, $ip, $ua, $logStatus, $logMsg);
            return back()->withErrors(['email' => $logMsg])->onlyInput('email');
        }
    }

    /**
     * AJAX 로그인: JSON 응답 (admin 가드 전용)
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

        // admin 가드 사용
        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $admin = Auth::guard('admin')->user();

            // 로그인 정보 업데이트
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
            $logMsg = '이메일 또는 비밀번호가 일치하지 않습니다.';
            $this->log(null, $ip, $ua, $logStatus, $logMsg);
            return response()->json([
                'success' => false,
                'message' => $logMsg
            ], 401);
        }
    }

    /**
     * 로그아웃 처리 (admin 가드 전용)
     */
    public function logout(Request $request)
    {
        $ip = $request->ip();
        $ua = $request->header('User-Agent');
        $admin = Auth::guard('admin')->user();

        // admin 가드 로그아웃
        Auth::guard('admin')->logout();

        if ($request->hasSession()) {
            // admin_sessions 테이블에서 세션 정보 삭제
            $sessionId = $request->session()->getId();
            \DB::table('admin_sessions')->where('session_id', $sessionId)->delete();
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
