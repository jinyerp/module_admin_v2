<?php

namespace Jiny\Admin\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\AdminActivityLogTrait;
use Jiny\Admin\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminActivityLogController extends Controller
{
    use AdminActivityLogTrait;

    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_activity_logs';

    public function __construct()
    {
        $this->filterable = [
            'admin_id', // admin_id: 관리자 ID (숫자)
            'action', // action: 수행된 액션 (문자열)
            'module', // module: 모듈명 (문자열)
            'target_type', // target_type: 대상 타입 (문자열)
            'target_id', // target_id: 대상 ID (숫자)
            'ip_address', // ip_address: IP 주소 (문자열)
            'severity', // severity: 심각도 (enum)
        ];

        $this->validFilters = [
            'admin_id' => 'integer|exists:admin_emails,id',
            'action' => 'string|max:50',
            'module' => 'string|max:100',
            'target_type' => 'string|max:100',
            'target_id' => 'integer|min:1',
            'ip_address' => 'string|max:45',
            'severity' => 'in:low,medium,high,critical',
        ];
    }

    /**
     * 관리자 활동 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminActivityLog::with('admin');

        // 필터 파라미터 추출, 조건 적용용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);

        // 추가 필터 적용
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // 정렬
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);

        $logs = $query->paginate(20);

        // 통계 데이터
        $stats = [
            'total' => AdminActivityLog::count(),
            'today' => AdminActivityLog::createdToday()->count(),
            'this_week' => AdminActivityLog::createdThisWeek()->count(),
            'high_severity' => AdminActivityLog::highSeverity()->count(),
        ];

        // 액션별 통계
        $actionStats = AdminActivityLog::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 모듈별 통계
        $moduleStats = AdminActivityLog::selectRaw('module, COUNT(*) as count')
            ->groupBy('module')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-admin::logs.activity-logs.index', compact(
            'logs',
            'stats',
            'actionStats',
            'moduleStats',
            'filters',
            'sort',
            'dir'
        ));
    }

    /**
     * 필터링 적용
     */
    public function applyFilter($filters, $query)
    {
        // 기본 필터 적용
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        // 검색어(부분일치) 별도 처리
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%")
                  ->orWhere('module', 'like', "%{$filters['search']}%")
                  ->orWhere('action', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    protected function getFilterParameters(Request $request)
    {
        $filters = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') && !empty($value)) {
                $filters[substr($key, 7)] = $value;
            }
        }

        return $filters;
    }

    /**
     * 관리자 활동 로그 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::logs.activity-logs.create');
    }

    /**
     * 관리자 활동 로그 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 유효성 검사 규칙 (생성용)
        $validationRules = [
            'admin_id' => 'required|integer|exists:admin_emails,id',
            'action' => 'required|string|max:50',
            'module' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'target_type' => 'nullable|string|max:100',
            'target_id' => 'nullable|integer|min:1',
            'severity' => 'required|in:low,medium,high,critical',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:500',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Activity Log Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        $data = $request->all();

        $activityLog = AdminActivityLog::create($data);

        // 관리자 액션 로깅
        $this->logCreateAction($activityLog, $data, "새로운 활동 로그 생성: {$activityLog->action}");

        return redirect()->route('admin.admin.logs.activity.index')
            ->with('success', '활동 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 관리자 활동 로그 상세 조회
     */
    public function show(AdminActivityLog $activityLog): View
    {
        return view('jiny-admin::logs.activity-logs.show', compact('activityLog'));
    }

    /**
     * 관리자 활동 로그 수정 폼
     */
    public function edit(AdminActivityLog $activityLog): View
    {
        return view('jiny-admin::logs.activity-logs.edit', compact('activityLog'));
    }

    /**
     * 관리자 활동 로그 수정
     */
    public function update(Request $request, AdminActivityLog $activityLog): RedirectResponse
    {
        // 유효성 검사 규칙 (수정용)
        $validationRules = [
            'admin_id' => 'required|integer|exists:admin_emails,id',
            'action' => 'required|string|max:50',
            'module' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'target_type' => 'nullable|string|max:100',
            'target_id' => 'nullable|integer|min:1',
            'severity' => 'required|in:low,medium,high,critical',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:500',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Activity Log Update Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'activity_log_id' => $activityLog->id,
            ]);
            throw $e;
        }

        $oldData = $activityLog->toArray();
        $newData = $request->all();

        $activityLog->update($newData);

        // 관리자 액션 로깅
        $this->logUpdateAction($activityLog, $oldData, $newData, "활동 로그 수정: {$activityLog->action}");

        return redirect()->route('admin.admin.logs.activity.show', $activityLog)
            ->with('success', '활동 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 관리자 활동 로그 삭제
     */
    public function destroy(AdminActivityLog $activityLog)
    {
        try {
            $data = $activityLog->toArray();
            $activityLog->delete();

            // 관리자 액션 로깅
            $this->logDeleteAction($activityLog, $data, "활동 로그 삭제: {$activityLog->action}");

            return redirect()->route('admin.admin.logs.activity.index')
                ->with('success', '활동 로그가 성공적으로 삭제되었습니다.');

        } catch (\Exception $e) {
            \Log::error('Activity Log Delete Failed', [
                'error' => $e->getMessage(),
                'activity_log_id' => $activityLog->id,
            ]);

            return redirect()->route('admin.admin.logs.activity.index')
                ->with('error', '활동 로그 삭제 중 오류가 발생했습니다.');
        }
    }

    /**
     * 통계 페이지
     */
    public function stats(): View
    {
        // 전체 통계
        $totalStats = [
            'total_logs' => AdminActivityLog::count(),
            'today_logs' => AdminActivityLog::createdToday()->count(),
            'this_week_logs' => AdminActivityLog::createdThisWeek()->count(),
            'high_severity_logs' => AdminActivityLog::highSeverity()->count(),
        ];

        // 액션별 통계
        $actionStats = AdminActivityLog::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 모듈별 통계
        $moduleStats = AdminActivityLog::selectRaw('module, COUNT(*) as count')
            ->groupBy('module')
            ->orderBy('count', 'desc')
            ->get();

        // 심각도별 통계
        $severityStats = AdminActivityLog::selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->orderBy('count', 'desc')
            ->get();

        // 일별 통계 (최근 30일)
        $dailyStats = AdminActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('jiny-admin::logs.activity-logs.stats', compact(
            'totalStats',
            'actionStats',
            'moduleStats',
            'severityStats',
            'dailyStats'
        ));
    }

    /**
     * 특정 관리자의 활동 통계
     */
    public function adminStats(int $adminId): View
    {
        $admin = \Jiny\Admin\Models\AdminUser::findOrFail($adminId);

        // 관리자별 통계
        $adminStats = [
            'total_activities' => AdminActivityLog::byAdmin($adminId)->count(),
            'today_activities' => AdminActivityLog::byAdmin($adminId)->createdToday()->count(),
            'this_week_activities' => AdminActivityLog::byAdmin($adminId)->createdThisWeek()->count(),
            'high_severity_activities' => AdminActivityLog::byAdmin($adminId)->highSeverity()->count(),
        ];

        // 관리자별 액션 통계
        $actionStats = AdminActivityLog::byAdmin($adminId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 관리자별 모듈 통계
        $moduleStats = AdminActivityLog::byAdmin($adminId)
            ->selectRaw('module, COUNT(*) as count')
            ->groupBy('module')
            ->orderBy('count', 'desc')
            ->get();

        // 최근 활동 목록
        $recentActivities = AdminActivityLog::byAdmin($adminId)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('jiny-admin::logs.activity-logs.admin-stats', compact(
            'admin',
            'adminStats',
            'actionStats',
            'moduleStats',
            'recentActivities'
        ));
    }

    /**
     * 데이터 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = AdminActivityLog::with('admin');

            // 필터 적용
            $filters = $this->getFilterParameters($request);
            $query = $this->applyFilter($filters, $query);

            // 추가 필터 적용
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->get();

            // CSV 데이터 생성
            $csvData = [];
            $csvData[] = ['ID', '관리자', '액션', '모듈', '설명', 'IP 주소', '심각도', '생성일'];

            foreach ($logs as $log) {
                $csvData[] = [
                    $log->id,
                    $log->admin_name,
                    $log->action,
                    $log->module,
                    $log->description,
                    $log->ip_address,
                    $log->severity,
                    $log->created_at->format('Y-m-d H:i:s'),
                ];
            }

            // CSV 파일 생성
            $filename = 'activity_logs_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/public/exports/' . $filename);

            // 디렉토리 생성
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            $file = fopen($filepath, 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);

            // 관리자 액션 로깅
            $this->logExportAction('activity-logs', $filters, "활동 로그 내보내기: {$filename}");

            return response()->json([
                'success' => true,
                'message' => '데이터가 성공적으로 내보내졌습니다.',
                'filename' => $filename,
                'download_url' => asset('storage/exports/' . $filename),
            ]);

        } catch (\Exception $e) {
            \Log::error('Activity Log Export Failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '데이터 내보내기 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 대량 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_activity_logs,id',
            ]);

            $ids = $request->ids;
            $count = count($ids);

            // 로그 삭제
            AdminActivityLog::whereIn('id', $ids)->delete();

            // 관리자 액션 로깅
            $this->logBulkDeleteAction('activity-logs', $count, $ids, "활동 로그 대량 삭제: {$count}개");

            return response()->json([
                'success' => true,
                'message' => "{$count}개의 활동 로그가 성공적으로 삭제되었습니다.",
            ]);

        } catch (\Exception $e) {
            \Log::error('Activity Log Bulk Delete Failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '대량 삭제 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    /**
     * 오래된 로그 정리
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:1|max:3650', // 최대 10년
            ]);

            $days = $request->days;
            $cutoffDate = now()->subDays($days);

            // 오래된 로그 삭제
            $deletedCount = AdminActivityLog::where('created_at', '<', $cutoffDate)->delete();

            // 관리자 액션 로깅
            $this->logActivity('cleanup', 'system', "오래된 활동 로그 정리: {$days}일 이전, {$deletedCount}개 삭제", [
                'severity' => 'medium',
                'metadata' => [
                    'days' => $days,
                    'deleted_count' => $deletedCount,
                    'cutoff_date' => $cutoffDate->toDateString(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount}개의 오래된 활동 로그가 정리되었습니다.",
            ]);

        } catch (\Exception $e) {
            \Log::error('Activity Log Cleanup Failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그 정리 중 오류가 발생했습니다.',
            ], 500);
        }
    }
}
