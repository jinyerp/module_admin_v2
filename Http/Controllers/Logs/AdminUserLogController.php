<?php

namespace Jiny\Admin\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\AdminUserLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminUserLogController extends Controller
{
    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_user_logs';

    public function __construct()
    {
        $this->filterable = [
            'admin_user_id', // admin_user_id: 관리자 UUID
            'status', // status: 성공/실패 상태
            'ip_address', // ip_address: IP 주소
        ];

        $this->validFilters = [
            'admin_user_id' => 'string|uuid',
            'status' => 'in:success,fail',
            'ip_address' => 'string|max:45',
        ];
    }

    /**
     * 관리자 사용자 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminUserLog::with('admin');

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
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        // 정렬
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);

        $logs = $query->paginate(20);

        // 통계 데이터
        $stats = [
            'total' => AdminUserLog::count(),
            'today' => AdminUserLog::createdToday()->count(),
            'this_week' => AdminUserLog::createdThisWeek()->count(),
            'success' => AdminUserLog::success()->count(),
            'failed' => AdminUserLog::failed()->count(),
        ];

        // 상태별 통계
        $statusStats = AdminUserLog::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        // IP별 통계
        $ipStats = AdminUserLog::selectRaw('ip_address, COUNT(*) as count')
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-admin::logs.login_logs.index', compact(
            'logs',
            'stats',
            'statusStats',
            'ipStats',
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
                $q->where('message', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%")
                  ->orWhere('user_agent', 'like', "%{$filters['search']}%");
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
     * 관리자 사용자 로그 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::logs.login_logs.create');
    }

    /**
     * 관리자 사용자 로그 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 유효성 검사 규칙 (생성용)
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('User Log Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        $data = $request->all();

        $userLog = AdminUserLog::create($data);

        // 관리자 액션 로깅
        $this->logCreateAction($userLog, $data, "새로운 사용자 로그 생성: {$userLog->status}");

        return redirect()
            ->route('admin.admin.logs.user.index')
            ->with('success', '사용자 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 관리자 사용자 로그 상세 조회
     */
    public function show(string $userLog): View
    {
        $userLog = AdminUserLog::findOrFail($userLog);
        return view('jiny-admin::logs.login_logs.show', compact('userLog'));
    }

    /**
     * 관리자 사용자 로그 수정 폼
     */
    public function edit(string $userLog): View
    {
        $userLog = AdminUserLog::findOrFail($userLog);
        return view('jiny-admin::logs.login_logs.edit', compact('userLog'));
    }

    /**
     * 관리자 사용자 로그 수정
     */
    public function update(Request $request, string $userLog): RedirectResponse
    {
        // 유효성 검사 규칙 (수정용)
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('User Log Update Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'user_log_id' => $userLog->id,
            ]);
            throw $e;
        }

        $data = $request->all();

        $userLogModel = AdminUserLog::findOrFail($userLog);
        $userLogModel->update($data);

        // 관리자 액션 로깅
        $this->logUpdateAction($userLogModel, $data, "사용자 로그 수정: {$userLogModel->status}");

        return redirect()
            ->route('admin.admin.logs.user.index')
            ->with('success', '사용자 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 관리자 사용자 로그 삭제
     */
    public function destroy(string $userLog)
    {
        try {
            $userLogModel = AdminUserLog::findOrFail($userLog);
            $userLogId = $userLogModel->id;
            $userLogModel->delete();

            // 관리자 액션 로깅
            $this->logDeleteAction($userLogModel, "사용자 로그 삭제: {$userLogId}");

            return redirect()
                ->route('admin.admin.logs.user.index')
                ->with('success', '사용자 로그가 성공적으로 삭제되었습니다.');
        } catch (\Exception $e) {
            \Log::error('User Log Delete Failed', [
                'user_log_id' => $userLog,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.admin.logs.user.index')
                ->with('error', '사용자 로그 삭제 중 오류가 발생했습니다.');
        }
    }

    /**
     * 통계 페이지
     */
    public function stats(): View
    {
        // 일별 통계
        $dailyStats = AdminUserLog::selectRaw('DATE(created_at) as date, COUNT(*) as count, status')
            ->groupBy('date', 'status')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->groupBy('date');

        // 시간별 통계
        $hourlyStats = AdminUserLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // IP별 통계
        $ipStats = AdminUserLog::selectRaw('ip_address, COUNT(*) as count')
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();

        // 관리자별 통계
        $adminStats = AdminUserLog::selectRaw('admin_user_id, COUNT(*) as count')
            ->groupBy('admin_user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-admin::logs.login_logs.stats', compact(
            'dailyStats',
            'hourlyStats',
            'ipStats',
            'adminStats'
        ));
    }

        /**
     * 특정 관리자의 로그 통계
     */
    public function adminStats(string $adminUserId): View
    {
        $admin = \App\Models\AdminUser::find($adminUserId);

        if (!$admin) {
            abort(404, '관리자를 찾을 수 없습니다.');
        }

        $logs = AdminUserLog::where('admin_user_id', $adminUserId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => AdminUserLog::where('admin_user_id', $adminUserId)->count(),
            'success' => AdminUserLog::where('admin_user_id', $adminUserId)->success()->count(),
            'failed' => AdminUserLog::where('admin_user_id', $adminUserId)->failed()->count(),
            'today' => AdminUserLog::where('admin_user_id', $adminUserId)->createdToday()->count(),
            'this_week' => AdminUserLog::where('admin_user_id', $adminUserId)->createdThisWeek()->count(),
        ];

        return view('jiny-admin::logs.login_logs.admin-stats', compact('admin', 'logs', 'stats'));
    }

    /**
     * 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = AdminUserLog::with('admin');

            // 필터 적용
            $filters = $this->getFilterParameters($request);
            $query = $this->applyFilter($filters, $query);

            // 날짜 필터
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->get();

            $filename = 'admin_user_logs_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/exports/' . $filename);

            // CSV 파일 생성
            $file = fopen($filepath, 'w');
            fputcsv($file, ['ID', '관리자', 'IP 주소', '상태', '메시지', '생성일시']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->admin_name,
                    $log->ip_address,
                    $log->status_label,
                    $log->message,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);

            return response()->json([
                'success' => true,
                'message' => '로그가 성공적으로 내보내졌습니다.',
                'filename' => $filename,
                'download_url' => route('admin.admin.logs.user.download', ['filename' => $filename])
            ]);

        } catch (\Exception $e) {
            \Log::error('User Log Export Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그 내보내기 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'string|uuid'
            ]);

            $ids = $request->ids;
            $deletedCount = AdminUserLog::whereIn('id', $ids)->delete();

            // 관리자 액션 로깅
            $this->logBulkDeleteAction($ids, "사용자 로그 일괄 삭제: {$deletedCount}개");

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount}개의 로그가 성공적으로 삭제되었습니다.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('User Log Bulk Delete Failed', [
                'error' => $e->getMessage(),
                'ids' => $request->ids ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 로그 정리 (오래된 로그 삭제)
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 90);
            $cutoffDate = now()->subDays($days);

            $deletedCount = AdminUserLog::where('created_at', '<', $cutoffDate)->delete();

            // 관리자 액션 로깅
            $this->logCleanupAction($days, "사용자 로그 정리: {$days}일 이전 로그 {$deletedCount}개 삭제");

            return response()->json([
                'success' => true,
                'message' => "{$days}일 이전 로그 {$deletedCount}개가 성공적으로 삭제되었습니다.",
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            \Log::error('User Log Cleanup Failed', [
                'error' => $e->getMessage(),
                'days' => $request->get('days', 90),
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그 정리 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 로그 생성 액션 기록
     */
    protected function logCreateAction($model, $data, $description)
    {
        if (!$this->activeLog) return;

        \Log::info('Admin User Log Created', [
            'action' => 'create',
            'table' => $this->logTableName,
            'model_id' => $model->id,
            'data' => $data,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * 로그 수정 액션 기록
     */
    protected function logUpdateAction($model, $data, $description)
    {
        if (!$this->activeLog) return;

        \Log::info('Admin User Log Updated', [
            'action' => 'update',
            'table' => $this->logTableName,
            'model_id' => $model->id,
            'data' => $data,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * 로그 삭제 액션 기록
     */
    protected function logDeleteAction($model, $description)
    {
        if (!$this->activeLog) return;

        \Log::info('Admin User Log Deleted', [
            'action' => 'delete',
            'table' => $this->logTableName,
            'model_id' => $model->id,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * 일괄 삭제 액션 기록
     */
    protected function logBulkDeleteAction($ids, $description)
    {
        if (!$this->activeLog) return;

        \Log::info('Admin User Log Bulk Deleted', [
            'action' => 'bulk_delete',
            'table' => $this->logTableName,
            'ids' => $ids,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * 정리 액션 기록
     */
    protected function logCleanupAction($days, $description)
    {
        if (!$this->activeLog) return;

        \Log::info('Admin User Log Cleanup', [
            'action' => 'cleanup',
            'table' => $this->logTableName,
            'days' => $days,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }
}
