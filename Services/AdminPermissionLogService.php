<?php

namespace Jiny\Admin\Services;

use Jiny\Admin\Models\AdminPermissionLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 관리자 권한 로그 서비스
 *
 * 관리자의 권한 관련 활동을 처리하고 분석합니다.
 * - 권한 활동 로깅
 * - 권한 활동 분석
 * - 보안 통계 생성
 * - 권한 패턴 분석
 */
class AdminPermissionLogService
{
    /**
     * 권한 부여 로그 생성
     */
    public function logGrant(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): AdminPermissionLog
    {
        return AdminPermissionLog::logGrant($adminId, $permissionName, $resourceType, $resourceId, $reason);
    }

    /**
     * 권한 회수 로그 생성
     */
    public function logRevoke(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): AdminPermissionLog
    {
        return AdminPermissionLog::logRevoke($adminId, $permissionName, $resourceType, $resourceId, $reason);
    }

    /**
     * 권한 체크 로그 생성
     */
    public function logCheck(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, bool $hasPermission = true): AdminPermissionLog
    {
        return AdminPermissionLog::logCheck($adminId, $permissionName, $resourceType, $resourceId, $hasPermission);
    }

    /**
     * 권한 거부 로그 생성
     */
    public function logDeny(int $adminId, string $permissionName, string $resourceType, ?int $resourceId = null, ?string $reason = null): AdminPermissionLog
    {
        return AdminPermissionLog::logDeny($adminId, $permissionName, $resourceType, $resourceId, $reason);
    }

    /**
     * 권한 활동 통계 조회
     */
    public function getStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = AdminPermissionLog::select(
            DB::raw('COUNT(*) as total_actions'),
            DB::raw('COUNT(CASE WHEN result = "success" THEN 1 END) as successful_actions'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_actions'),
            DB::raw('COUNT(CASE WHEN result = "failed" THEN 1 END) as failed_actions'),
            DB::raw('COUNT(CASE WHEN action = "grant" THEN 1 END) as grants'),
            DB::raw('COUNT(CASE WHEN action = "revoke" THEN 1 END) as revokes'),
            DB::raw('COUNT(CASE WHEN action = "check" THEN 1 END) as checks'),
            DB::raw('COUNT(CASE WHEN action = "deny" THEN 1 END) as denies'),
            DB::raw('COUNT(DISTINCT admin_id) as unique_admins'),
            DB::raw('COUNT(DISTINCT permission_name) as unique_permissions'),
            DB::raw('COUNT(DISTINCT resource_type) as unique_resources')
        )
        ->where('created_at', '>=', $startDate)
        ->first();

        $totalActions = $stats->total_actions;
        $successRate = $totalActions > 0 ? round(($stats->successful_actions / $totalActions) * 100, 2) : 0;

        return [
            'total_actions' => $totalActions,
            'successful_actions' => $stats->successful_actions,
            'denied_actions' => $stats->denied_actions,
            'failed_actions' => $stats->failed_actions,
            'grants' => $stats->grants,
            'revokes' => $stats->revokes,
            'checks' => $stats->checks,
            'denies' => $stats->denies,
            'unique_admins' => $stats->unique_admins,
            'unique_permissions' => $stats->unique_permissions,
            'unique_resources' => $stats->unique_resources,
            'success_rate' => $successRate,
        ];
    }

    /**
     * 권한별 활동 분석
     */
    public function getPermissionAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return AdminPermissionLog::select(
            'permission_name',
            DB::raw('COUNT(*) as total_actions'),
            DB::raw('COUNT(CASE WHEN result = "success" THEN 1 END) as successful_actions'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_actions'),
            DB::raw('COUNT(CASE WHEN action = "grant" THEN 1 END) as grants'),
            DB::raw('COUNT(CASE WHEN action = "revoke" THEN 1 END) as revokes'),
            DB::raw('COUNT(CASE WHEN action = "check" THEN 1 END) as checks'),
            DB::raw('COUNT(CASE WHEN action = "deny" THEN 1 END) as denies')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('permission_name')
        ->orderBy('total_actions', 'desc')
        ->get()
        ->toArray();
    }

    /**
     * 관리자별 권한 활동 분석
     */
    public function getAdminAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return AdminPermissionLog::with('admin')
            ->select(
                'admin_id',
                DB::raw('COUNT(*) as total_actions'),
                DB::raw('COUNT(CASE WHEN result = "success" THEN 1 END) as successful_actions'),
                DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_actions'),
                DB::raw('COUNT(DISTINCT permission_name) as unique_permissions'),
                DB::raw('COUNT(DISTINCT resource_type) as unique_resources')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('admin_id')
            ->orderBy('total_actions', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 리소스별 권한 활동 분석
     */
    public function getResourceAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return AdminPermissionLog::select(
            'resource_type',
            DB::raw('COUNT(*) as total_actions'),
            DB::raw('COUNT(CASE WHEN result = "success" THEN 1 END) as successful_actions'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_actions'),
            DB::raw('COUNT(DISTINCT admin_id) as unique_admins'),
            DB::raw('COUNT(DISTINCT permission_name) as unique_permissions')
        )
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('resource_type')
        ->groupBy('resource_type')
        ->orderBy('total_actions', 'desc')
        ->get()
        ->toArray();
    }

    /**
     * 시간별 권한 활동 트렌드
     */
    public function getTimeTrend(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return AdminPermissionLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_actions'),
            DB::raw('COUNT(CASE WHEN result = "success" THEN 1 END) as successful_actions'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_actions'),
            DB::raw('COUNT(CASE WHEN action = "grant" THEN 1 END) as grants'),
            DB::raw('COUNT(CASE WHEN action = "revoke" THEN 1 END) as revokes')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->toArray();
    }

    /**
     * 권한 패턴 분석
     */
    public function getPermissionPatterns(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // 가장 많이 사용되는 권한 조합
        $permissionCombinations = AdminPermissionLog::select(
            'admin_id',
            'permission_name',
            'resource_type',
            DB::raw('COUNT(*) as usage_count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('admin_id', 'permission_name', 'resource_type')
        ->orderBy('usage_count', 'desc')
        ->limit(20)
        ->get();

        // 권한 사용 시간대 분석
        $hourlyUsage = AdminPermissionLog::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as action_count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();

        // 권한 사용 요일 분석
        $dailyUsage = AdminPermissionLog::select(
            DB::raw('DAYOFWEEK(created_at) as day_of_week'),
            DB::raw('COUNT(*) as action_count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('day_of_week')
        ->orderBy('day_of_week')
        ->get();

        return [
            'permission_combinations' => $permissionCombinations->toArray(),
            'hourly_usage' => $hourlyUsage->toArray(),
            'daily_usage' => $dailyUsage->toArray(),
        ];
    }

    /**
     * 보안 위험 분석
     */
    public function getSecurityRiskAnalysis(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        // 거부된 권한 시도 분석
        $deniedAttempts = AdminPermissionLog::select(
            'admin_id',
            'permission_name',
            'resource_type',
            DB::raw('COUNT(*) as denied_count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('result', 'denied')
        ->groupBy('admin_id', 'permission_name', 'resource_type')
        ->orderBy('denied_count', 'desc')
        ->limit(10)
        ->get();

        // 의심스러운 IP 주소 분석
        $suspiciousIPs = AdminPermissionLog::select(
            'ip_address',
            DB::raw('COUNT(*) as attempt_count'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_count')
        )
        ->where('created_at', '>=', $startDate)
        ->whereNotNull('ip_address')
        ->groupBy('ip_address')
        ->having('denied_count', '>', 0)
        ->orderBy('denied_count', 'desc')
        ->limit(10)
        ->get();

        // 권한 남용 패턴 분석
        $abusePatterns = AdminPermissionLog::select(
            'admin_id',
            DB::raw('COUNT(*) as total_attempts'),
            DB::raw('COUNT(CASE WHEN result = "denied" THEN 1 END) as denied_attempts'),
            DB::raw('COUNT(DISTINCT permission_name) as unique_permissions'),
            DB::raw('COUNT(DISTINCT resource_type) as unique_resources')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('admin_id')
        ->having('denied_attempts', '>', 0)
        ->orderBy('denied_attempts', 'desc')
        ->limit(10)
        ->get();

        return [
            'denied_attempts' => $deniedAttempts->toArray(),
            'suspicious_ips' => $suspiciousIPs->toArray(),
            'abuse_patterns' => $abusePatterns->toArray(),
        ];
    }

    /**
     * 권한 로그 정리 (오래된 로그 삭제)
     */
    public function cleanupOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return AdminPermissionLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * 권한 로그 백업
     */
    public function backupLogs(string $backupPath): bool
    {
        try {
            $logs = AdminPermissionLog::with('admin')->get();

            $backupData = [
                'backup_date' => now()->toISOString(),
                'total_records' => $logs->count(),
                'logs' => $logs->toArray()
            ];

            file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));

            return true;
        } catch (\Exception $e) {
            \Log::error('권한 로그 백업 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 권한 로그 복원
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
                AdminPermissionLog::create($logData);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('권한 로그 복원 실패: ' . $e->getMessage());
            return false;
        }
    }
}
