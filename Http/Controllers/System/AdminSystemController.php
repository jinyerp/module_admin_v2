<?php

namespace Jiny\Admin\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Jiny\Admin\Models\SystemBackupLog;
use Jiny\Admin\Models\SystemMaintenanceLog;
use Jiny\Admin\Models\SystemOperationLog;
use Jiny\Admin\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminSystemController extends Controller
{
    /**
     * 시스템 대시보드 메인 페이지
     */
    public function index(Request $request): View
    {
        // 최근 30일 데이터 기준
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // 백업 로그 통계
        $backupStats = [
            'total' => SystemBackupLog::count(),
            'completed' => SystemBackupLog::completed()->count(),
            'failed' => SystemBackupLog::failed()->count(),
            'success_rate' => SystemBackupLog::getSuccessRate(),
            'recent' => SystemBackupLog::where('created_at', '>=', $startDate)->count(),
        ];

        // 유지보수 로그 통계
        $maintenanceStats = [
            'total' => SystemMaintenanceLog::count(),
            'scheduled' => SystemMaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => SystemMaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => SystemMaintenanceLog::where('status', 'completed')->count(),
            'recent' => SystemMaintenanceLog::where('created_at', '>=', $startDate)->count(),
        ];

        // 운영 로그 통계
        $operationStats = [
            'total' => SystemOperationLog::count(),
            'success' => SystemOperationLog::where('status', 'success')->count(),
            'failed' => SystemOperationLog::where('status', 'failed')->count(),
            'recent' => SystemOperationLog::where('created_at', '>=', $startDate)->count(),
            'avg_execution_time' => SystemOperationLog::whereNotNull('execution_time')->avg('execution_time'),
        ];

        // 성능 로그 통계
        $performanceStats = [
            'total' => SystemPerformanceLog::count(),
            'normal' => SystemPerformanceLog::where('status', 'normal')->count(),
            'warning' => SystemPerformanceLog::where('status', 'warning')->count(),
            'critical' => SystemPerformanceLog::where('status', 'critical')->count(),
            'recent' => SystemPerformanceLog::where('measured_at', '>=', $startDate)->count(),
        ];

        // 최근 활동
        $recentBackups = SystemBackupLog::with('initiatedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentMaintenance = SystemMaintenanceLog::with(['initiatedBy', 'completedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentOperations = SystemOperationLog::with('performedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPerformance = SystemPerformanceLog::orderBy('measured_at', 'desc')
            ->limit(5)
            ->get();

        // 차트 데이터
        $chartData = $this->getChartData($startDate);

        return view('jiny-admin::admin.systems.index', compact(
            'backupStats',
            'maintenanceStats', 
            'operationStats',
            'performanceStats',
            'recentBackups',
            'recentMaintenance',
            'recentOperations',
            'recentPerformance',
            'chartData',
            'days'
        ));
    }

    /**
     * 차트 데이터 생성
     */
    private function getChartData($startDate)
    {
        // 백업 성공률 트렌드
        $backupTrend = SystemBackupLog::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
            COUNT(CASE WHEN status = "failed" THEN 1 END) as failed
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 운영 로그 트렌드
        $operationTrend = SystemOperationLog::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "success" THEN 1 END) as success,
            COUNT(CASE WHEN status = "failed" THEN 1 END) as failed,
            AVG(execution_time) as avg_execution_time
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 성능 로그 트렌드
        $performanceTrend = SystemPerformanceLog::selectRaw('
            DATE(measured_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "normal" THEN 1 END) as normal,
            COUNT(CASE WHEN status = "warning" THEN 1 END) as warning,
            COUNT(CASE WHEN status = "critical" THEN 1 END) as critical,
            AVG(value) as avg_value
        ')
        ->where('measured_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return [
            'backup_trend' => $backupTrend,
            'operation_trend' => $operationTrend,
            'performance_trend' => $performanceTrend,
        ];
    }

    /**
     * 시스템 상태 요약
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        $status = [
            'backup' => [
                'status' => SystemBackupLog::failed()->where('created_at', '>=', now()->subDays(7))->count() > 0 ? 'warning' : 'normal',
                'message' => SystemBackupLog::failed()->where('created_at', '>=', now()->subDays(7))->count() . '개의 최근 백업 실패'
            ],
            'maintenance' => [
                'status' => SystemMaintenanceLog::where('status', 'in_progress')->count() > 0 ? 'warning' : 'normal',
                'message' => SystemMaintenanceLog::where('status', 'in_progress')->count() . '개의 진행중인 유지보수'
            ],
            'performance' => [
                'status' => SystemPerformanceLog::where('status', 'critical')->where('measured_at', '>=', now()->subHours(1))->count() > 0 ? 'critical' : 'normal',
                'message' => SystemPerformanceLog::where('status', 'critical')->where('measured_at', '>=', now()->subHours(1))->count() . '개의 임계치 초과'
            ],
            'operations' => [
                'status' => SystemOperationLog::where('status', 'failed')->where('created_at', '>=', now()->subHours(1))->count() > 5 ? 'warning' : 'normal',
                'message' => SystemOperationLog::where('status', 'failed')->where('created_at', '>=', now()->subHours(1))->count() . '개의 최근 실패한 운영'
            ]
        ];

        return response()->json($status);
    }
}
