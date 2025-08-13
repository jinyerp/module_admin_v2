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

/**
 * AdminUserLogController
 *
 * 관리자 사용자 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminUserLog.admin_user_id 필드가 AdminUser.id와 연결
 * - 로그별 관리자 정보 표시 및 통계
 * - 관리자별 로그 분석 및 모니터링
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminUserLog.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 사용자 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminUserLogTest.php
 * ```
 */
class AdminUserLogController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.user_logs.index';
    public $createPath = 'jiny-admin::admin.user_logs.create';
    public $editPath = 'jiny-admin::admin.user_logs.edit';
    public $showPath = 'jiny-admin::admin.user_logs.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['admin_user_id', 'status', 'ip_address', 'search', 'date_from', 'date_to'];
    protected $validFilters = [
        'admin_user_id' => 'string|uuid',
        'status' => 'in:success,fail',
        'ip_address' => 'string|max:45',
        'search' => 'string',
        'date_from' => 'date',
        'date_to' => 'date'
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

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     * Activity Log 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_user_logs';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_user_logs';
    }

    /**
     * 관리자 사용자 로그 목록 조회 (템플릿 메소드 구현)
     * AdminUser와의 연관성을 고려하여 관리자 정보도 함께 표시
     */
    protected function _index(Request $request): View
    {
        $query = AdminUserLog::with('admin');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['search']);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 통계 데이터 추가 (AdminUser와의 연관성 반영)
        $stats = $this->getLogStats();

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.admin.user-logs.',
            'stats' => $stats,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 로그 통계 데이터 조회
     * AdminUser와의 연관성을 반영한 통계
     */
    private function getLogStats()
    {
        return [
            'total' => AdminUserLog::count(),
            'success' => AdminUserLog::where('status', 'success')->count(),
            'failed' => AdminUserLog::where('status', 'fail')->count(),
            'today' => AdminUserLog::whereDate('created_at', today())->count(),
            'this_week' => AdminUserLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'unique_users' => AdminUserLog::distinct('admin_user_id')->count(),
            'recent_activity' => AdminUserLog::with('admin')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    /**
     * 관리자 사용자 로그 생성 폼 (템플릿 메소드 구현)
     */
    protected function _create(Request $request): View
    {
        // 관리자 목록을 가져와서 선택할 수 있도록 함
        $adminUsers = AdminUser::select('id', 'name', 'email')->get();

        return view($this->createPath, [
            'route' => 'admin.admin.user-logs.',
            'adminUsers' => $adminUsers,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 사용자 로그 저장 (템플릿 메소드 구현)
     */
    protected function _store(Request $request): JsonResponse
    {
        $validationRules = [
            'admin_user_id' => 'required|string|uuid|exists:admin_users,id',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];
        
        $data = $request->validate($validationRules);
        
        // AdminUser 존재 여부 확인
        $adminUser = AdminUser::find($data['admin_user_id']);
        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => '존재하지 않는 관리자입니다.'
            ], 422);
        }
        
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
     * AdminUser 정보도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        $userLog = AdminUserLog::with('admin')->findOrFail($id);
        
        // 관련 관리자 정보 추가 조회
        $adminUser = AdminUser::find($userLog->admin_user_id);
        
        return view($this->showPath, [
            'route' => 'admin.admin.user-logs.',
            'userLog' => $userLog,
            'adminUser' => $adminUser,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 관리자 사용자 로그 수정 폼 (템플릿 메소드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $userLog = AdminUserLog::with('admin')->findOrFail($id);
        
        // 관리자 목록을 가져와서 선택할 수 있도록 함
        $adminUsers = AdminUser::select('id', 'name', 'email')->get();
        
        return view($this->editPath, [
            'route' => 'admin.admin.user-logs.',
            'userLog' => $userLog,
            'adminUsers' => $adminUsers,
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
            'admin_user_id' => 'required|string|uuid|exists:admin_users,id',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:512',
            'status' => 'required|in:success,fail',
            'message' => 'nullable|string|max:500',
        ];
        
        $data = $request->validate($validationRules);
        
        // AdminUser 존재 여부 확인
        $adminUser = AdminUser::find($data['admin_user_id']);
        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => '존재하지 않는 관리자입니다.'
            ], 422);
        }
        
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
        $userLog = AdminUserLog::with('admin')->findOrFail($id);
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
     * AdminUser와의 연관성을 반영한 상세 통계
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

        // 관리자별 통계 (AdminUser와의 연관성 반영)
        $adminStats = AdminUserLog::selectRaw('admin_user_id, COUNT(*) as count')
            ->with('admin:id,name,email')
            ->groupBy('admin_user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // 상태별 통계
        $statusStats = AdminUserLog::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return view('jiny-admin::logs.login_logs.stats', compact(
            'dailyStats',
            'hourlyStats',
            'ipStats',
            'adminStats',
            'statusStats'
        ));
    }

    /**
     * 특정 관리자의 로그 통계
     * AdminUser와의 연관성을 반영한 개별 관리자 통계
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
            'success' => AdminUserLog::where('admin_user_id', $adminUserId)->where('status', 'success')->count(),
            'failed' => AdminUserLog::where('admin_user_id', $adminUserId)->where('status', 'fail')->count(),
            'today' => AdminUserLog::where('admin_user_id', $adminUserId)->whereDate('created_at', today())->count(),
            'this_week' => AdminUserLog::where('admin_user_id', $adminUserId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return view('jiny-admin::admin.logs.login_logs.admin-stats', compact('admin', 'logs', 'stats'));
    }

    /**
     * 로그 내보내기
     * AdminUser 정보도 함께 포함하여 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = AdminUserLog::with('admin');

            // 필터 적용
            $filters = $this->getFilterParameters($request);
            $query = $this->applyFilter($filters, $query, ['search']);

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
            fputcsv($file, ['ID', '관리자 ID', '관리자 이름', '관리자 이메일', 'IP 주소', '상태', '메시지', '생성일시']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->admin_user_id,
                    $log->admin->name ?? 'N/A',
                    $log->admin->email ?? 'N/A',
                    $log->ip_address,
                    $log->status,
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
        $query = AdminUserLog::with('admin');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['search']);
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $filename = 'admin_user_logs_' . date('Ymd_His') . '.csv';
        $columns = [
            'id', 'admin_user_id', 'admin_name', 'admin_email', 'ip_address', 'user_agent', 'status', 'message', 'created_at'
        ];
        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM 추가 (엑셀 한글깨짐 방지)
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            // 헤더
            fputcsv($handle, $columns);
            $query->chunk(500, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    $data = [
                        $row->id,
                        $row->admin_user_id,
                        $row->admin->name ?? 'N/A',
                        $row->admin->email ?? 'N/A',
                        $row->ip_address,
                        $row->user_agent,
                        $row->status,
                        $row->message,
                        $row->created_at
                    ];
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
