<?php

namespace Jiny\Admin\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\SystemOperationLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 시스템 운영 로그 컨트롤러
 *
 * 시스템의 모든 운영 활동을 조회하고 관리합니다.
 * - 운영 활동 추적 및 분석
 * - 성능 모니터링
 * - 보안 관련 정보 수집
 * - 에러 및 예외 상황 기록
 */
class SystemOperationLogController extends Controller
{
    /**
     * 운영 로그 목록 페이지
     */
    public function index(Request $request): View
    {
        $query = SystemOperationLog::with(['performedBy', 'target']);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getStats($request);

        return view('jiny-admin::systems.operation-logs.index', compact('logs', 'stats'));
    }

    /**
     * 운영 로그 상세 조회
     */
    public function show(int $id): View
    {
        $log = SystemOperationLog::with(['performedBy', 'target'])->findOrFail($id);

        return view('jiny-admin::systems.operation-logs.show', compact('log'));
    }

    /**
     * 운영 로그 API 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = SystemOperationLog::with(['performedBy', 'target']);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs,
            'stats' => $this->getStats($request)
        ]);
    }

    /**
     * 운영 로그 상세 API 조회
     */
    public function apiShow(int $id): JsonResponse
    {
        $log = SystemOperationLog::with(['performedBy', 'target'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * 운영 로그 통계 API
     */
    public function apiStats(Request $request): JsonResponse
    {
        $stats = $this->getStats($request);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * 운영 타입별 분석
     */
    public function operationTypeAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = SystemOperationLog::select(
            'operation_type',
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
            DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
            DB::raw('COUNT(CASE WHEN status = "partial" THEN 1 END) as partial_operations'),
            DB::raw('AVG(execution_time) as avg_execution_time'),
            DB::raw('MAX(execution_time) as max_execution_time'),
            DB::raw('MIN(execution_time) as min_execution_time')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('operation_type')
        ->orderBy('total_operations', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 수행자별 분석
     */
    public function performerAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = SystemOperationLog::with('performedBy')
            ->select(
                'performed_by_type',
                'performed_by_id',
                DB::raw('COUNT(*) as total_operations'),
                DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
                DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
                DB::raw('COUNT(DISTINCT operation_type) as unique_operation_types'),
                DB::raw('AVG(execution_time) as avg_execution_time')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('performed_by_type', 'performed_by_id')
            ->orderBy('total_operations', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 성능 분석
     */
    public function performanceAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = SystemOperationLog::select(
            'operation_type',
            DB::raw('AVG(execution_time) as avg_execution_time'),
            DB::raw('MAX(execution_time) as max_execution_time'),
            DB::raw('MIN(execution_time) as min_execution_time'),
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN execution_time > 1000 THEN 1 END) as slow_operations')
        )
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('execution_time')
        ->groupBy('operation_type')
        ->orderBy('avg_execution_time', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 시간별 트렌드
     */
    public function timeTrend(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $trend = SystemOperationLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
            DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
            DB::raw('AVG(execution_time) as avg_execution_time')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $trend
        ]);
    }

    /**
     * 에러 분석
     */
    public function errorAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = SystemOperationLog::select(
            'operation_type',
            'error_message',
            DB::raw('COUNT(*) as error_count'),
            DB::raw('AVG(execution_time) as avg_execution_time')
        )
        ->where('created_at', '>=', $startDate)
        ->whereIn('status', ['failed', 'partial'])
        ->groupBy('operation_type', 'error_message')
        ->orderBy('error_count', 'desc')
        ->limit(20)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 검색 필터 적용
     */
    private function applyFilters($query, Request $request)
    {
        // 운영 타입 필터
        if ($request->filled('filter_operation_type')) {
            $query->where('operation_type', $request->filter_operation_type);
        }

        // 운영명 필터
        if ($request->filled('filter_operation_name')) {
            $query->where('operation_name', 'like', '%' . $request->filter_operation_name . '%');
        }

        // 수행자 타입 필터
        if ($request->filled('filter_performed_by_type')) {
            $query->where('performed_by_type', $request->filter_performed_by_type);
        }

        // 상태 필터
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // 중요도 필터
        if ($request->filled('filter_severity')) {
            $query->where('severity', $request->filter_severity);
        }

        // 날짜 범위 필터
        if ($request->filled('filter_date_from')) {
            $query->where('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->where('created_at', '<=', $request->filter_date_to . ' 23:59:59');
        }

        // IP 주소 필터
        if ($request->filled('filter_ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->filter_ip_address . '%');
        }

        // 세션 ID 필터
        if ($request->filled('filter_session_id')) {
            $query->where('session_id', $request->filter_session_id);
        }

        // 검색어 필터
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function($q) use ($search) {
                $q->where('operation_name', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%')
                  ->orWhere('operation_type', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    /**
     * 정렬 적용
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('direction', 'desc');

        // 허용된 정렬 필드만 사용
        $allowedSortFields = [
            'created_at', 'operation_name', 'operation_type', 'status', 
            'execution_time', 'severity', 'ip_address'
        ];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * 통계 데이터 조회
     */
    private function getStats(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $query = SystemOperationLog::where('created_at', '>=', $startDate);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 기본 통계
        $totalOperations = $query->count();
        $successfulOperations = (clone $query)->where('status', 'success')->count();
        $failedOperations = (clone $query)->where('status', 'failed')->count();
        $partialOperations = (clone $query)->where('status', 'partial')->count();
        
        // 실행 시간 통계
        $executionTimeQuery = (clone $query)->whereNotNull('execution_time');
        $avgExecutionTime = $executionTimeQuery->avg('execution_time');
        $maxExecutionTime = $executionTimeQuery->max('execution_time');
        $slowOperations = (clone $query)->where('execution_time', '>', 1000)->count();
        
        // 고유 값 통계 (SQLite 호환)
        $uniqueOperationTypes = (clone $query)->distinct()->count('operation_type');
        $uniquePerformers = (clone $query)->distinct()->count(DB::raw('performed_by_type || "_" || performed_by_id'));
        
        // 성공률 계산
        $successRate = $totalOperations > 0 ? round(($successfulOperations / $totalOperations) * 100, 2) : 0;

        return [
            'total_operations' => $totalOperations,
            'successful_operations' => $successfulOperations,
            'failed_operations' => $failedOperations,
            'partial_operations' => $partialOperations,
            'avg_execution_time' => $avgExecutionTime,
            'max_execution_time' => $maxExecutionTime,
            'slow_operations' => $slowOperations,
            'unique_operation_types' => $uniqueOperationTypes,
            'unique_performers' => $uniquePerformers,
            'success_rate' => $successRate,
        ];
    }

    /**
     * 운영 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $query = SystemOperationLog::with(['performedBy', 'target']);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->get();

        // CSV 형식으로 변환
        $csvData = $this->convertToCsv($logs);

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'filename' => 'operation_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * CSV 형식으로 변환
     */
    private function convertToCsv($logs)
    {
        $headers = [
            'ID', '운영 타입', '운영명', '수행자 타입', '수행자 ID',
            '대상 타입', '대상 ID', '상태', '실행 시간(ms)', '중요도',
            'IP 주소', '세션 ID', '생성일'
        ];

        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log->id,
                $log->operation_type,
                $log->operation_name,
                $log->performed_by_type,
                $log->performed_by_id,
                $log->target_type,
                $log->target_id,
                $log->status,
                $log->execution_time,
                $log->severity,
                $log->ip_address,
                $log->session_id,
                $log->created_at->format('Y-m-d H:i:s')
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows
        ];
    }
}
