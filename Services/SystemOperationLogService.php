<?php

namespace Jiny\Admin\Services;

use Jiny\Admin\Models\SystemOperationLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 시스템 운영 로그 서비스
 *
 * 시스템의 모든 운영 활동을 처리하고 분석합니다.
 * - 운영 활동 로깅
 * - 성능 모니터링
 * - 보안 관련 정보 수집
 * - 에러 및 예외 상황 기록
 */
class SystemOperationLogService
{
    /**
     * 성공한 운영 로그 생성
     */
    public function logSuccess(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        ?array $responseData = null
    ): SystemOperationLog {
        return SystemOperationLog::logSuccess(
            $operationType,
            $operationName,
            $performedByType,
            $performedById,
            $targetType,
            $targetId,
            $executionTime,
            $requestData,
            $responseData
        );
    }

    /**
     * 실패한 운영 로그 생성
     */
    public function logFailed(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        string $errorMessage,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        string $severity = SystemOperationLog::SEVERITY_ERROR
    ): SystemOperationLog {
        return SystemOperationLog::logFailed(
            $operationType,
            $operationName,
            $performedByType,
            $performedById,
            $errorMessage,
            $targetType,
            $targetId,
            $executionTime,
            $requestData,
            $severity
        );
    }

    /**
     * 부분 성공한 운영 로그 생성
     */
    public function logPartial(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        string $errorMessage,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        ?array $responseData = null
    ): SystemOperationLog {
        return SystemOperationLog::logPartial(
            $operationType,
            $operationName,
            $performedByType,
            $performedById,
            $errorMessage,
            $targetType,
            $targetId,
            $executionTime,
            $requestData,
            $responseData
        );
    }

    /**
     * 운영 활동 통계 조회
     */
    public function getStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = SystemOperationLog::select(
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
            DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
            DB::raw('COUNT(CASE WHEN status = "partial" THEN 1 END) as partial_operations'),
            DB::raw('AVG(execution_time) as avg_execution_time'),
            DB::raw('MAX(execution_time) as max_execution_time'),
            DB::raw('COUNT(CASE WHEN execution_time > 1000 THEN 1 END) as slow_operations'),
            DB::raw('COUNT(DISTINCT operation_type) as unique_operation_types'),
            DB::raw('COUNT(DISTINCT performed_by_type, performed_by_id) as unique_performers')
        )
        ->where('created_at', '>=', $startDate)
        ->first();

        $totalOperations = $stats->total_operations;
        $successRate = $totalOperations > 0 ? round(($stats->successful_operations / $totalOperations) * 100, 2) : 0;

        return [
            'total_operations' => $totalOperations,
            'successful_operations' => $stats->successful_operations,
            'failed_operations' => $stats->failed_operations,
            'partial_operations' => $stats->partial_operations,
            'avg_execution_time' => round($stats->avg_execution_time ?? 0, 2),
            'max_execution_time' => $stats->max_execution_time,
            'slow_operations' => $stats->slow_operations,
            'unique_operation_types' => $stats->unique_operation_types,
            'unique_performers' => $stats->unique_performers,
            'success_rate' => $successRate,
        ];
    }

    /**
     * 운영 타입별 분석
     */
    public function getOperationTypeAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
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
        ->get()
        ->toArray();
    }

    /**
     * 수행자별 분석
     */
    public function getPerformerAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::with('performedBy')
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
            ->get()
            ->toArray();
    }

    /**
     * 성능 분석
     */
    public function getPerformanceAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
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
        ->get()
        ->toArray();
    }

    /**
     * 시간별 트렌드
     */
    public function getTimeTrend(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
            DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
            DB::raw('AVG(execution_time) as avg_execution_time')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->toArray();
    }

    /**
     * 에러 분석
     */
    public function getErrorAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
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
        ->get()
        ->toArray();
    }

    /**
     * 느린 운영 분석
     */
    public function getSlowOperationsAnalysis(int $days = 30, int $thresholdMs = 1000): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
            'operation_type',
            'operation_name',
            'execution_time',
            'performed_by_type',
            'performed_by_id',
            'created_at'
        )
        ->where('created_at', '>=', $startDate)
        ->where('execution_time', '>', $thresholdMs)
        ->orderBy('execution_time', 'desc')
        ->limit(50)
        ->get()
        ->toArray();
    }

    /**
     * 세션별 분석
     */
    public function getSessionAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
            'session_id',
            'performed_by_type',
            'performed_by_id',
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(DISTINCT operation_type) as unique_operation_types'),
            DB::raw('MIN(created_at) as session_start'),
            DB::raw('MAX(created_at) as session_end'),
            DB::raw('TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(created_at)) as session_duration_minutes')
        )
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('session_id')
        ->groupBy('session_id', 'performed_by_type', 'performed_by_id')
        ->orderBy('total_operations', 'desc')
        ->limit(100)
        ->get()
        ->toArray();
    }

    /**
     * IP 주소별 분석
     */
    public function getIpAddressAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return SystemOperationLog::select(
            'ip_address',
            DB::raw('COUNT(*) as total_operations'),
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful_operations'),
            DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_operations'),
            DB::raw('COUNT(DISTINCT performed_by_type, performed_by_id) as unique_performers'),
            DB::raw('COUNT(DISTINCT operation_type) as unique_operation_types')
        )
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('ip_address')
        ->groupBy('ip_address')
        ->orderBy('total_operations', 'desc')
        ->limit(50)
        ->get()
        ->toArray();
    }

    /**
     * 운영 로그 정리 (오래된 로그 삭제)
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return SystemOperationLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * 운영 로그 백업
     */
    public function backupLogs(string $backupPath): bool
    {
        try {
            $logs = SystemOperationLog::with(['performedBy', 'target'])->get();

            $backupData = [
                'backup_date' => now()->toISOString(),
                'total_records' => $logs->count(),
                'logs' => $logs->toArray()
            ];

            file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));

            return true;
        } catch (\Exception $e) {
            \Log::error('운영 로그 백업 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 운영 로그 복원
     */
    public function restoreLogs(string $backupPath): bool
    {
        try {
            if (!file_exists($backupPath)) {
                throw new \Exception('백업 파일이 존재하지 않습니다.');
            }

            $backupData = json_decode(file_get_contents($backupPath), true);

            if (!$backupData || !isset($backupData['logs'])) {
                throw new \Exception('백업 파일 형식이 올바르지 않습니다.');
            }

            foreach ($backupData['logs'] as $logData) {
                SystemOperationLog::create($logData);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('운영 로그 복원 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 성능 모니터링 알림 생성
     */
    public function generatePerformanceAlerts(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        $alerts = [];

        // 느린 운영 알림
        $slowOperations = SystemOperationLog::select('operation_type')
            ->where('created_at', '>=', $startDate)
            ->where('execution_time', '>', 5000) // 5초 이상
            ->groupBy('operation_type')
            ->havingRaw('COUNT(*) > 10')
            ->get();

        foreach ($slowOperations as $operation) {
            $alerts[] = [
                'type' => 'slow_operation',
                'message' => "운영 타입 '{$operation->operation_type}'이 느린 성능을 보입니다.",
                'severity' => 'warning'
            ];
        }

        // 실패율 높은 운영 알림
        $highFailureOperations = SystemOperationLog::select('operation_type')
            ->where('created_at', '>=', $startDate)
            ->groupBy('operation_type')
            ->havingRaw('COUNT(CASE WHEN status = "failed" THEN 1 END) / COUNT(*) > 0.1')
            ->havingRaw('COUNT(*) > 5')
            ->get();

        foreach ($highFailureOperations as $operation) {
            $alerts[] = [
                'type' => 'high_failure_rate',
                'message' => "운영 타입 '{$operation->operation_type}'의 실패율이 높습니다.",
                'severity' => 'error'
            ];
        }

        return $alerts;
    }
}
