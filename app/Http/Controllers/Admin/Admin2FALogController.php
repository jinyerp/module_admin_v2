<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Models\Admin2FALog;
use Jiny\Admin\App\Models\AdminUser;

class Admin2FALogController extends AdminResourceController
{
    protected $filterable = ['admin_user_id', 'action', 'status', 'ip_address'];
    protected $validFilters = [
        'admin_user_id' => 'string|uuid',
        'action' => 'string|max:255',
        'status' => 'in:success,fail',
        'ip_address' => 'string|max:45',
    ];
    protected $sortableColumns = ['id', 'admin_user_id', 'action', 'status', 'ip_address', 'created_at'];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_2fa_logs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_2fa_logs';
    }

    /**
     * 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.2fa-logs';
    }

    /**
     * 2FA 로그 목록 조회 (템플릿 메소드 구현)
     */
    protected function _index(Request $request): View
    {
        $query = Admin2FALog::with('adminUser');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, []);
        
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 통계 데이터
        $stats = [
            'total_logs' => Admin2FALog::count(),
            'success_logs' => Admin2FALog::where('status', 'success')->count(),
            'fail_logs' => Admin2FALog::where('status', 'fail')->count(),
            'today_logs' => Admin2FALog::whereDate('created_at', today())->count(),
        ];

        // 액션별 통계
        $actionStats = Admin2FALog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 관리자 목록 (필터용)
        $adminUsers = AdminUser::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('jiny-admin::admin.user_2fa_logs.index', [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.admin.user-2fa-logs.',
            'stats' => $stats,
            'actionStats' => $actionStats,
            'adminUsers' => $adminUsers,
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

        // 날짜 필터
        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * 2FA 로그 생성 폼 (템플릿 메소드 구현)
     */
    protected function _create(Request $request): View
    {
        return view('jiny-admin::admin.user_2fa_logs.create', [
            'route' => 'admin.admin.user-2fa-logs.',
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 2FA 로그 저장 (템플릿 메소드 구현)
     */
    protected function _store(Request $request): JsonResponse
    {
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'action' => 'required|string|max:255',
            'status' => 'required|in:success,fail',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'message' => 'nullable|string|max:500',
        ];
        $data = $request->validate($validationRules);
        $log = Admin2FALog::create($data);
        
        // Activity Log 기록
        $this->logActivity('create', '2FA 로그 생성', $log->id, $data);
        
        return response()->json([
            'success' => true,
            'message' => '성공적으로 생성되었습니다.',
            'log' => $log
        ]);
    }

    /**
     * 2FA 로그 상세 조회 (템플릿 메소드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $log = Admin2FALog::with('adminUser')->findOrFail($id);
        return view('jiny-admin::admin.user_2fa_logs.show', [
            'route' => 'admin.admin.user-2fa-logs.',
            'log' => $log,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 2FA 로그 수정 폼 (템플릿 메소드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $log = Admin2FALog::findOrFail($id);
        return view('jiny-admin::admin.user_2fa_logs.edit', [
            'route' => 'admin.admin.user-2fa-logs.',
            'log' => $log,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 2FA 로그 수정 (템플릿 메소드 구현)
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        $log = Admin2FALog::findOrFail($id);
        
        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = $log->toArray();
        
        $validationRules = [
            'admin_user_id' => 'required|string|uuid',
            'action' => 'required|string|max:255',
            'status' => 'required|in:success,fail',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'message' => 'nullable|string|max:500',
        ];
        $data = $request->validate($validationRules);
        $log->update($data);
        
        // Activity Log 기록
        $this->logActivity('update', '2FA 로그 수정', $log->id, $data);
        
        // Audit Log 기록
        $this->logAudit('update', $oldData, $data, '2FA 로그 수정', $log->id);
        
        return response()->json([
            'success' => true,
            'message' => '성공적으로 수정되었습니다.',
            'log' => $log
        ]);
    }

    /**
     * 2FA 로그 삭제 (템플릿 메소드 구현)
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? $request->route('id');
        $log = Admin2FALog::findOrFail($id);
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = $log->toArray();
        
        $log->delete();
        
        // Activity Log 기록
        $this->logActivity('delete', '2FA 로그 삭제', $id, $oldData);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '2FA 로그 삭제', $id);
        
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
        $log = Admin2FALog::findOrFail($id);
        $url = route('admin.admin.user-2fa-logs.destroy', $id);
        $title = '2FA 로그 삭제';
        
        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-admin::admin.user_2fa_logs.form_delete', compact('log', 'url', 'title'));
        }
        
        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-admin::admin.user_2fa_logs.form_delete', compact('log', 'url', 'title'));
    }

    /**
     * 수정 전 데이터 가져오기 (Audit Log용)
     */
    protected function getOldData($id)
    {
        $log = Admin2FALog::find($id);
        return $log ? $log->toArray() : null;
    }

    /**
     * 2FA 로그 통계
     */
    public function stats(Request $request): View
    {
        // 일별 통계
        $dailyStats = Admin2FALog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success'),
            DB::raw('SUM(CASE WHEN status = "fail" THEN 1 ELSE 0 END) as fail')
        )
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 액션별 통계
        $actionStats = Admin2FALog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 관리자별 통계
        $adminStats = Admin2FALog::with('adminUser')
            ->select('admin_user_id', DB::raw('count(*) as count'))
            ->groupBy('admin_user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // IP별 통계
        $ipStats = Admin2FALog::select('ip_address', DB::raw('count(*) as count'))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-admin::admin.user_2fa_logs.stats', [
            'dailyStats' => $dailyStats,
            'actionStats' => $actionStats,
            'adminStats' => $adminStats,
            'ipStats' => $ipStats,
            'route' => 'admin.admin.user-2fa-logs.',
        ]);
    }

    /**
     * 2FA 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = Admin2FALog::with('adminUser');

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

            $filename = '2fa_logs_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/exports/' . $filename);

            // CSV 파일 생성
            $file = fopen($filepath, 'w');
            fputcsv($file, ['ID', '관리자', '이메일', '액션', '상태', '메시지', 'IP 주소', '사용자 에이전트', '생성일']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->adminUser->name ?? 'N/A',
                    $log->adminUser->email ?? 'N/A',
                    $log->action,
                    $log->status,
                    $log->message,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);

            return response()->json([
                'success' => true,
                'message' => '로그가 성공적으로 내보내졌습니다.',
                'filename' => $filename,
                'download_url' => route('admin.admin.logs.2fa.download', ['filename' => $filename])
            ]);

        } catch (\Exception $e) {
            \Log::error('2FA Log Export Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '로그 내보내기 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 2FA 로그 CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        $query = Admin2FALog::with('adminUser');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, []);
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $filename = '2fa_logs_' . date('Ymd_His') . '.csv';
        $columns = [
            'id', 'admin_user_id', 'action', 'status', 'ip_address', 'user_agent', 'message', 'created_at'
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
        $oldData = Admin2FALog::whereIn('id', $ids)->get()->toArray();
        
        $deletedCount = Admin2FALog::whereIn('id', $ids)->delete();
        
        // Activity Log 기록
        $this->logActivity('delete', '일괄 삭제', null, ['deleted_ids' => $ids]);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '2FA 로그 일괄 삭제', null);
        
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

            $deletedCount = Admin2FALog::where('created_at', '<', $cutoffDate)->delete();

            // 관리자 액션 로깅
            $this->logCleanupAction($days, "2FA 로그 정리: {$days}일 이전 로그 {$deletedCount}개 삭제");

            return response()->json([
                'success' => true,
                'message' => "{$days}일 이전 로그 {$deletedCount}개가 성공적으로 삭제되었습니다.",
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            \Log::error('2FA Log Cleanup Failed', [
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

        \Log::info('Admin 2FA Log Created', [
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

        \Log::info('Admin 2FA Log Updated', [
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

        \Log::info('Admin 2FA Log Deleted', [
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

        \Log::info('Admin 2FA Log Bulk Deleted', [
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

        \Log::info('Admin 2FA Log Cleanup', [
            'action' => 'cleanup',
            'table' => $this->logTableName,
            'days' => $days,
            'description' => $description,
            'admin_id' => auth()->id(),
        ]);
    }
} 