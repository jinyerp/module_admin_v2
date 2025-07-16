<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserBlacklist;

class DashboardController extends Controller
{
    /**
     * 대시보드 메인 페이지
     */
    public function index()
    {
        // 기본 통계 데이터
        $stats = $this->getStats();

        // 최근 7일 가입자 추이 데이터
        $registrationData = $this->getRegistrationTrend();

        // 최근 활동 데이터
        $recentActivities = $this->getRecentActivities();

        return view('jiny-admin::admin.dashboard', compact('stats', 'registrationData', 'recentActivities'));
    }

    /**
     * 차트 데이터 API
     */
    public function chartData(Request $request)
    {
        $days = $request->get('days', 7);
        $data = $this->getRegistrationTrend($days);

        return response()->json($data);
    }

    /**
     * 기본 통계 데이터
     */
    private function getStats()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'today_registrations' => User::whereDate('created_at', today())->count(),
            'blacklist_count' => UserBlacklist::count(),
            'this_week_registrations' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_registrations' => User::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * 가입자 추이 데이터
     */
    private function getRegistrationTrend($days = 7)
    {
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('m/d');

            // 해당 날짜의 가입자 수 조회
            $count = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'average' => round(array_sum($data) / count($data), 1)
        ];
    }

    /**
     * 최근 활동 데이터
     */
    private function getRecentActivities()
    {
        return User::with(['addresses', 'phones', 'selectedAvatar'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'created_at_formatted' => $user->created_at->diffForHumans(),
                    'avatar' => $user->selectedAvatar?->image ?? null,
                    'type' => 'registration'
                ];
            });
    }

    /**
     * 월별 가입자 통계
     */
    public function monthlyStats()
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('Y-m');

            $count = User::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
            $data[] = $count;
        }

        return response()->json([
            'labels' => $months,
            'data' => $data
        ]);
    }

    /**
     * 상태별 사용자 통계
     */
    public function statusStats()
    {
        $stats = [
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];

        return response()->json([
            'labels' => ['활성', '비활성', '정지'],
            'data' => array_values($stats),
            'colors' => ['#10B981', '#F59E0B', '#EF4444']
        ]);
    }

    /**
     * 시간대별 가입자 통계
     */
    public function hourlyStats()
    {
        $hours = [];
        $data = [];

        for ($i = 0; $i < 24; $i++) {
            $hours[] = sprintf('%02d:00', $i);

            $count = User::whereRaw('HOUR(created_at) = ?', [$i])
                        ->whereDate('created_at', '>=', now()->subDays(30))
                        ->count();
            $data[] = $count;
        }

        return response()->json([
            'labels' => $hours,
            'data' => $data
        ]);
    }
}
