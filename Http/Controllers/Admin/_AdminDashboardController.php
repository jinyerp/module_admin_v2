<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * 관리자 대시보드를 표시합니다.
     */
    public function index(): View
    {
        // 대시보드 통계 데이터 가져오기
        $stats = app('admin.stats')->getDashboardStats();

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * 관리자 홈 페이지를 표시합니다.
     */
    public function home(): View
    {
        return view('admin.home');
    }

    /**
     * 관리자 설정 페이지를 표시합니다.
     */
    public function settings(): View
    {
        return view('admin.settings');
    }

    /**
     * 관리자 사용자 관리 페이지를 표시합니다.
     */
    public function users(): View
    {
        return view('admin.users.index');
    }
}
