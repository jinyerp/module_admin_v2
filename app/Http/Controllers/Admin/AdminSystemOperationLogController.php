<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Jiny\Admin\App\Models\SystemOperationLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;

/**
 * 시스템 운영 로그 컨트롤러
 *
 * 시스템의 모든 운영 활동을 조회하고 관리합니다.
 * - 운영 활동 추적 및 분석
 * - 성능 모니터링
 * - 보안 관련 정보 수집
 * - 에러 및 예외 상황 기록
 * 
 * @see docs/features/AdminSystemOperationLog.md
 *  *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 시스템 유지보수 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminSystemOperationLogTest.php
 * ```
 */
class AdminSystemOperationLogController extends AdminResourceController
{
    /**
     * 뷰 경로 설정
     */
    protected string $indexPath = 'jiny-admin::admin.systems_operation_logs.index';
    protected string $createPath = 'jiny-admin::admin.systems_operation_logs.create';
    protected string $editPath = 'jiny-admin::admin.systems_operation_logs.edit';
    protected string $showPath = 'jiny-admin::admin.systems_operation_logs.show';

    /**
     * 필터링 및 정렬 설정
     */
    protected bool $filterable = true;
    protected array $validFilters = [
        'search', 'operation_type', 'operation_name', 'performed_by_type',
        'status', 'severity', 'date_from', 'date_to', 'ip_address', 'session_id'
    ];
    protected array $sortableColumns = [
        'created_at', 'operation_name', 'operation_type', 'status',
        'execution_time', 'severity', 'ip_address'
    ];

    /**
     * 로깅 설정
     */
    protected bool $activeLog = true;
    protected string $logTableName = 'system_operation_logs';

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블명 반환
     */
    protected function getTableName(): string
    {
        return 'system_operation_logs';
    }

    /**
     * 모듈명 반환
     */
    protected function getModuleName(): string
    {
        return 'system_operation_log';
    }

    /**
     * 운영 로그 목록 페이지 (템플릿 메서드 패턴)
     */
    public function index(Request $request): View
    {
        return $this->_index($request);
    }

    /**
     * 운영 로그 목록 페이지 내부 구현
     */
    protected function _index(Request $request): View
    {
        $query = SystemOperationLog::with(['performedBy', 'target']);

        // 필터 파라미터 가져오기
        $filters = $this->getFilterParameters($request);

        // 필터 적용
        $query = $this->applyFilter($filters, $query, ['operation_name', 'ip_address', 'operation_type']);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->paginate(20);
        $rows = $logs;

        // 통계 데이터
        $stats = $this->getOperationStats($request);

        return view($this->indexPath, compact('logs', 'stats', 'rows'));
    }

    /**
     * 운영 로그 상세 조회 (템플릿 메서드 패턴)
     */
    public function show(int $id): View
    {
        return $this->_show($id);
    }

    /**
     * 운영 로그 상세 조회 내부 구현
     */
    protected function _show(int $id): View
    {
        $log = SystemOperationLog::with(['performedBy', 'target'])->findOrFail($id);

        // 활동 로그 기록
        $this->logActivity('view', $id, 'system_operation_log');

        return view($this->showPath, compact('log'));
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
     * 운영 로그 통계
     */
    public function stats(): View
    {
        $stats = $this->getOperationStats(request());
        
        // 최근 운영 로그 10개 조회
        $recentLogs = SystemOperationLog::with(['performedBy', 'target'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-admin::admin.systems_operation_logs.stats', [
            'stats' => $stats,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * 운영 로그 일괄 삭제 (템플릿 메서드 패턴)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->_bulkDelete($request);
    }

    /**
     * 운영 로그 일괄 삭제 내부 구현
     */
    protected function _bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_operation_logs,id',
        ]);

        $count = SystemOperationLog::whereIn('id', $request->selected_logs)->delete();

        // 활동 로그 기록
        $this->logActivity('bulk_delete', $count, 'system_operation_log', [
            'deleted_count' => $count,
            'selected_ids' => $request->selected_logs
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count}개의 운영 로그가 성공적으로 삭제되었습니다.",
            'deleted_count' => $count
        ]);
    }

    /**
     * 운영 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemOperationLog::with(['performedBy', 'target']);

        // 필터 파라미터 가져오기
        $filters = $this->getFilterParameters($request);

        // 필터 적용
        $query = $this->applyFilter($filters, $query, ['operation_name', 'ip_address', 'operation_type']);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->get();

        // CSV 파일 생성
        $filename = 'operation_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!File::exists(dirname($filepath))) {
            File::makeDirectory(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        
        // 헤더 작성
        fputcsv($handle, [
            'ID', '운영 타입', '운영명', '수행자 타입', '수행자 ID',
            '대상 타입', '대상 ID', '상태', '실행 시간(ms)', '중요도',
            'IP 주소', '세션 ID', '생성일'
        ]);

        // 데이터 작성
        foreach ($logs as $log) {
            fputcsv($handle, [
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
            ]);
        }

        fclose($handle);

        // 활동 로그 기록
        $this->logActivity('export', count($logs), 'system_operation_log', [
            'exported_count' => count($logs),
            'filename' => $filename
        ]);

        return response()->download($filepath, $filename)->deleteFileAfterSend();
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
     * 운영 로그 통계 데이터 조회
     */
    private function getOperationStats(Request $request)
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
        
        // 고유 값 통계
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
}
