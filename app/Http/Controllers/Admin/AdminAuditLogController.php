<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Jiny\Admin\App\Models\AdminAuditLog;
use Jiny\Admin\App\Models\AdminUser;

class AdminAuditLogController extends Controller
{
    protected $filterable = ['admin_id', 'action', 'table_name', 'severity'];
    protected $validFilters = [
        'admin_id' => 'integer|exists:admin_users,id',
        'action' => 'string|max:50',
        'table_name' => 'string|max:100',
        'severity' => 'in:low,medium,high,critical',
    ];
    protected $sortableColumns = ['id', 'admin_id', 'action', 'table_name', 'severity', 'created_at'];

    /**
     * 감사 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminAuditLog::with('admin');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, []);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        return view('jiny-admin::admin.audit-logs.index', [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.admin.audit-logs.',
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 감사 로그 생성 폼 (보안상 비활성화)
     */
    public function create(Request $request): View
    {
        // 보안상 감사 로그는 수동 생성 불가
        abort(403, '감사 로그는 시스템에서 자동으로 생성됩니다.');
    }

    /**
     * 감사 로그 저장 (보안상 비활성화)
     */
    public function store(Request $request): JsonResponse
    {
        // 보안상 감사 로그는 수동 생성 불가
        return response()->json([
            'success' => false,
            'message' => '감사 로그는 시스템에서 자동으로 생성됩니다.'
        ], 403);
    }

    /**
     * 감사 로그 상세 조회
     */
    public function show(Request $request, $id): View
    {
        $log = AdminAuditLog::with('admin')->findOrFail($id);
        return view('jiny-admin::admin.audit-logs.show', [
            'log' => $log,
            'route' => 'admin.admin.audit-logs.',
        ]);
    }

    /**
     * 감사 로그 수정 폼 (보안상 비활성화)
     */
    public function edit(Request $request, $id): View
    {
        // 보안상 감사 로그는 수정 불가
        abort(403, '감사 로그는 보안상 수정할 수 없습니다.');
    }

    /**
     * 감사 로그 업데이트 (보안상 비활성화)
     */
    public function update(Request $request, $id): JsonResponse
    {
        // 보안상 감사 로그는 수정 불가
        return response()->json([
            'success' => false,
            'message' => '감사 로그는 보안상 수정할 수 없습니다.'
        ], 403);
    }

    /**
     * 감사 로그 삭제 (보안상 비활성화)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        // 보안상 감사 로그는 삭제 불가
        return response()->json([
            'success' => false,
            'message' => '감사 로그는 보안상 삭제할 수 없습니다.'
        ], 403);
    }

    /**
     * 필터링 적용
     */
    protected function applyFilter($filters, $query, $likeFields = [])
    {
        // 기본 필터 적용
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                if (in_array($column, $likeFields)) {
                    $query->where($column, 'like', "%{$filters[$column]}%");
                } else {
                    $query->where($column, $filters[$column]);
                }
            }
        }

        // 검색어(부분일치) 별도 처리
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%")
                  ->orWhere('table_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    /**
     * 필터 파라미터 가져오기
     */
    protected function getFilterParameters(Request $request): array
    {
        $filters = [];
        
        foreach ($this->filterable as $field) {
            if ($request->filled($field)) {
                $filters[$field] = $request->get($field);
            }
        }

        // 검색어 추가
        if ($request->filled('search')) {
            $filters['search'] = $request->get('search');
        }

        return $filters;
    }

    /**
     * 삭제 확인 페이지
     */
    public function deleteConfirm(Request $request, $id)
    {
        try {
            $log = AdminAuditLog::findOrFail($id);
            $url = route('admin.admin.audit-logs.destroy', $id);
            $title = '감사 로그 삭제';
            
            // AJAX 요청인 경우 HTML만 반환
            if ($request->ajax()) {
                return view('jiny-admin::admin.audit-logs.form_delete', compact('log', 'url', 'title'));
            }
            
            // 일반 요청인 경우 전체 페이지 반환
            return view('jiny-admin::admin.audit-logs.form_delete', compact('log', 'url', 'title'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 해당 ID의 감사 로그가 존재하지 않는 경우
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 감사 로그를 찾을 수 없습니다. 이미 삭제되었거나 존재하지 않습니다.'
                ], 404);
            }
            
            // 일반 요청인 경우 오류 페이지로 리다이렉트
            return redirect()->route('admin.admin.audit-logs.index')
                ->with('error', '해당 감사 로그를 찾을 수 없습니다. 이미 삭제되었거나 존재하지 않습니다.');
        } catch (\Exception $e) {
            // 기타 예외 처리
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '감사 로그 조회 중 오류가 발생했습니다.'
                ], 500);
            }
            
            return redirect()->route('admin.admin.audit-logs.index')
                ->with('error', '감사 로그 조회 중 오류가 발생했습니다.');
        }
    }

    /**
     * 감사 로그 통계
     */
    public function stats(): View
    {
        $globalStats = AdminAuditLog::selectRaw('COUNT(*) as total, COUNT(DISTINCT admin_id) as unique_admins')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        $recentStats = AdminAuditLog::selectRaw('COUNT(*) as total, COUNT(DISTINCT admin_id) as unique_admins')
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        // 일별 통계
        $dailyStats = AdminAuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 시간별 통계
        $hourlyStats = AdminAuditLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('jiny-admin::admin.audit-logs.stats', [
            'globalStats' => $globalStats,
            'recentStats' => $recentStats,
            'dailyStats' => $dailyStats,
            'hourlyStats' => $hourlyStats,
            'route' => 'admin.admin.audit-logs.',
        ]);
    }

    /**
     * 관리자별 활동 통계
     */
    public function adminStats(int $adminId): View
    {
        $admin = AdminUser::findOrFail($adminId);
        
        $stats = AdminAuditLog::where('admin_id', $adminId)
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT DATE(created_at)) as active_days')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        // 최근 활동
        $recentActivities = AdminAuditLog::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 액션별 통계
        $actionStats = AdminAuditLog::where('admin_id', $adminId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-admin::admin.audit-logs.admin-stats', [
            'admin' => $admin,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'actionStats' => $actionStats,
            'route' => 'admin.admin.audit-logs.',
        ]);
    }

    /**
     * 감사 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $query = AdminAuditLog::with('admin');

        // 필터 적용
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // CSV 형식으로 변환
        $csvData = [];
        $csvData[] = [
            'ID', '관리자', '액션', '테이블명', '레코드ID', '설명', 'IP주소', '심각도', '생성일시'
        ];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->admin->email ?? 'N/A',
                $log->action,
                $log->table_name ?? 'N/A',
                $log->record_id ?? 'N/A',
                $log->description ?? 'N/A',
                $log->ip_address ?? 'N/A',
                $log->severity,
                $log->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'filename' => 'admin_audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        $logs = AdminAuditLog::with('admin')->get();
        $filename = 'audit_logs_'.now()->format('Ymd_His').'.csv';
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', '관리자', '액션', '테이블명', '레코드ID', '설명', 'IP주소', '심각도', '생성일']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->admin?->email,
                $log->action,
                $log->table_name,
                $log->record_id,
                $log->description,
                $log->ip_address,
                $log->severity,
                $log->created_at,
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    /**
     * 선택 삭제 (보안상 비활성화)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        // 보안상 감사 로그는 삭제 불가
        return response()->json([
            'success' => false,
            'message' => '감사 로그는 보안상 삭제할 수 없습니다.'
        ], 403);
    }

    /**
     * 감사 로그 정리 (오래된 로그 삭제)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $days = $request->days;
        $deletedCount = AdminAuditLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$days}일 이전의 {$deletedCount}개 로그가 삭제되었습니다."
        ]);
    }
}
