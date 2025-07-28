<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 로그인 폼 컨트롤러
 */
class AdminLoginFormController extends Controller
{
    /**
     * 로그인 폼 출력
     */
    public function showLoginForm()
    {
        // 최초 관리자 설정
        // 등록된 관리자 회원이 없는 경우 설정 페이지로 이동
        if (DB::table('admin_users')->count() == 0) {
            return redirect()->route('admin.setup');
        }

        return view('jiny-admin::auth.login', [
            'register_enabled' => false
        ]);
    }
}
