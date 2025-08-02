<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;

class AdminUserLogController extends AdminResourceController
{
    protected $filterable = ['admin_user_id', 'status', 'ip_address'];
    protected $validFilters = [
        'admin_user_id' => 'string|uuid',
        'status' => 'in:success,fail',
        'ip_address' => 'string|max:45',
    ];
    protected $sortableColumns = ['id', 'admin_user_id', 'ip_address', 'status', 'created_at'];

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
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_user_logs';
    }

    /**
     * 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.user-logs';
    }

    /**
     * 관리자 사용자 로그 목록 조회 (템플릿 메소드 구현)
     */
    protected function _index(Request $request): View
    {
        $query = AdminUserLog::query();
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, []);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        //dd($rows);
        return view('jiny-admin::admin.user_logs.index', [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.admin.user-logs.',
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
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
                $q->where('message', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%")
                  ->orWhere('user_agent', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    /**
     * 관리자 사용자 로그 생성 폼 (템플릿 메소드 구현)
     */
    protected function _create(Request $request): View
    {
        return view('jiny-admin::admin.user_logs.create', [
            'route' => 'admin.admin.user-logs.',
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 사용자 로그 저장 (템플릿 메소드 구현)
     */
    protected function _store(Request $request): JsonResponse
    {
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];
        $data = $request->validate($validationRules);
        $userLog = AdminUserLog::create($data);
        
        // Activity Log 기록
        $this->logActivity('create', '로그 생성', $userLog->id, $data);
        
        return response()->json([
            'success' => true,
            'message' => '성공적으로 생성되었습니다.',
            'userLog' => $userLog
        ]);
    }

    /**
     * 관리자 사용자 로그 상세 조회 (템플릿 메소드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $userLog = AdminUserLog::findOrFail($id);
        return view('jiny-admin::admin.user_logs.show', [
            'route' => 'admin.admin.user-logs.',
            'userLog' => $userLog,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 사용자 로그 수정 폼 (템플릿 메소드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $userLog = AdminUserLog::findOrFail($id);
        return view('jiny-admin::admin.user_logs.edit', [
            'route' => 'admin.admin.user-logs.',
            'userLog' => $userLog,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 사용자 로그 수정 (템플릿 메소드 구현)
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        $userLog = AdminUserLog::findOrFail($id);
        
        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = $userLog->toArray();
        
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];
        $data = $request->validate($validationRules);
        $userLog->update($data);
        
        // Activity Log 기록
        $this->logActivity('update', '로그 수정', $userLog->id, $data);
        
        // Audit Log 기록
        $this->logAudit('update', $oldData, $data, '관리자 사용자 로그 수정', $userLog->id);
        
        return response()->json([
            'success' => true,
            'message' => '성공적으로 수정되었습니다.',
            'userLog' => $userLog
        ]);
    }

    /**
     * 관리자 사용자 로그 삭제 (템플릿 메소드 구현)
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? $request->route('id');
        $userLog = AdminUserLog::findOrFail($id);
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = $userLog->toArray();
        
        $userLog->delete();
        
        // Activity Log 기록
        $this->logActivity('delete', '로그 삭제', $id, $oldData);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '관리자 사용자 로그 삭제', $id);
        
        return response()->json([
            'success' => true,
            'message' => '성공적으로 삭제되었습니다.'
        ]);
    }

    /**
     * 삭제 확인 폼 반환
     */
    public function deleteConfirm(Request $request, $id)
    {
        $userLog = AdminUserLog::findOrFail($id);
        $url = route('admin.admin.user-logs.destroy', $id);
        $title = '로그 삭제';
        
        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-admin::admin.user_logs.form_delete', compact('userLog', 'url', 'title'));
        }
        
        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-admin::admin.user_logs.form_delete', compact('userLog', 'url', 'title'));
    }

    /**
     * 수정 전 데이터 가져오기 (Audit Log용)
     */
    protected function getOldData($id)
    {
        $userLog = AdminUserLog::find($id);
        return $userLog ? $userLog->toArray() : null;
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
        $admin = AdminUser::find($adminUserId);

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

        return view('jiny-admin::admin.logs.login_logs.admin-stats', compact('admin', 'logs', 'stats'));
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
            $query = $this->applyFilter($filters, $query, []);

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
     * 관리자 사용자 로그 목록 CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        $query = AdminUserLog::query();
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, []);
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $filename = 'admin_user_logs_' . date('Ymd_His') . '.csv';
        $columns = [
            'id', 'admin_user_id', 'ip_address', 'user_agent', 'status', 'message', 'created_at'
        ];
        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM 추가 (엑셀 한글깨짐 방지)
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            // 헤더
            fputcsv($handle, $columns);
            $query->chunk(500, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $data = [];
                    foreach ($columns as $col) {
                        $data[] = $row->{$col};
                    }
                    fputcsv($handle, $data);
                }
            });
            fclose($handle);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }
        $ids = $request->input('ids');
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = AdminUserLog::whereIn('id', $ids)->get()->toArray();
        
        $deletedCount = AdminUserLog::whereIn('id', $ids)->delete();
        
        // Activity Log 기록
        $this->logActivity('delete', '일괄 삭제', null, ['deleted_ids' => $ids]);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '관리자 사용자 로그 일괄 삭제', null);
        
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 로그가 성공적으로 삭제되었습니다."
        ]);
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
