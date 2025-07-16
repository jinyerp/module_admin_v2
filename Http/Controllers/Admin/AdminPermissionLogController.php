<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\AdminPermissionLog;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 관리자 권한 로그 컨트롤러
 *
 * 관리자의 권한 관련 활동을 조회하고 관리합니다.
 * - 권한 부여/회수 이력 조회
 * - 권한 체크 및 접근 거부 기록 조회
 * - 리소스별 권한 활동 분석
 * - 보안 관련 통계 제공
 */
class AdminPermissionLogController extends Controller
{
    /**
     * 권한 로그 목록 페이지
     */
    public function index(Request $request): View
    {
        $query = AdminPermissionLog::with('admin');

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $logs = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getStats($request);

        return view('admin::admin.permission-logs.index', compact('logs', 'stats'));
    }

    /**
     * 권한 로그 상세 조회
     */
    public function show(int $id): View
    {
        $log = AdminPermissionLog::with('admin')->findOrFail($id);

        return view('admin::admin.permission-logs.show', compact('log'));
    }

    /**
     * 권한 로그 API 조회
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = AdminPermissionLog::with('admin');

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
     * 권한 로그 상세 API 조회
     */
    public function apiShow(int $id): JsonResponse
    {
        $log = AdminPermissionLog::with('admin')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * 권한 로그 통계 API
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
     * 권한별 활동 분석
     */
    public function permissionAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = AdminPermissionLog::select(
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
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 관리자별 권한 활동 분석
     */
    public function adminAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = AdminPermissionLog::with('admin')
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
            ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 리소스별 권한 활동 분석
     */
    public function resourceAnalysis(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analysis = AdminPermissionLog::select(
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
        ->get();

        return response()->json([
            'success' => true,
            'data' => $analysis
        ]);
    }

    /**
     * 시간별 권한 활동 트렌드
     */
    public function timeTrend(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $trend = AdminPermissionLog::select(
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
        ->get();

        return response()->json([
            'success' => true,
            'data' => $trend
        ]);
    }

    /**
     * 검색 필터 적용
     */
    private function applyFilters($query, Request $request)
    {
        // 관리자 필터
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        // 권한명 필터
        if ($request->filled('permission_name')) {
            $query->where('permission_name', 'like', '%' . $request->permission_name . '%');
        }

        // 리소스 타입 필터
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        // 액션 필터
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 결과 필터
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        // 날짜 범위 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // IP 주소 필터
        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        return $query;
    }

    /**
     * 정렬 적용
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * 통계 데이터 조회
     */
    private function getStats(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $query = AdminPermissionLog::where('created_at', '>=', $startDate);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        return [
            'total_actions' => $query->count(),
            'successful_actions' => $query->where('result', 'success')->count(),
            'denied_actions' => $query->where('result', 'denied')->count(),
            'failed_actions' => $query->where('result', 'failed')->count(),
            'grants' => $query->where('action', 'grant')->count(),
            'revokes' => $query->where('action', 'revoke')->count(),
            'checks' => $query->where('action', 'check')->count(),
            'denies' => $query->where('action', 'deny')->count(),
            'unique_admins' => $query->distinct('admin_id')->count('admin_id'),
            'unique_permissions' => $query->distinct('permission_name')->count('permission_name'),
            'unique_resources' => $query->distinct('resource_type')->count('resource_type'),
            'success_rate' => $query->count() > 0 ?
                round(($query->where('result', 'success')->count() / $query->count()) * 100, 2) : 0,
        ];
    }

    /**
     * 권한 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $query = AdminPermissionLog::with('admin');

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
            'filename' => 'permission_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * CSV 형식으로 변환
     */
    private function convertToCsv($logs)
    {
        $headers = [
            'ID', '관리자', '권한명', '리소스 타입', '리소스 ID',
            '액션', '결과', 'IP 주소', '사유', '생성일'
        ];

        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log->id,
                $log->admin ? $log->admin->email : 'N/A',
                $log->permission_name,
                $log->resource_type,
                $log->resource_id,
                $log->action,
                $log->result,
                $log->ip_address,
                $log->reason,
                $log->created_at->format('Y-m-d H:i:s')
            ];
        }

        return [
            'headers' => $headers,
            'rows' => $rows
        ];
    }
}
