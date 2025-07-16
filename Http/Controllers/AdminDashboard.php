<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * 관리자 대시보드 컨트롤러
 * - admin guard로 인증된 관리자만 접근 가능 (미들웨어는 라우트에서 처리)
 * - 인증된 관리자 정보를 blade에 전달
 */
class AdminDashboard extends Controller
{
    /**
     * 관리자 대시보드
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('jiny-admin::admin.dashboard.index', [
            'admin' => $admin
        ]);
    }
}
