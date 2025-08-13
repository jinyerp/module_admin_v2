<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Jiny\Admin\App\Models\AdminActivityLog;
use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminActivityLogController
 *
 * 관리자 활동 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * 관리자 패널에서 발생하는 모든 사용자 활동을 추적하고 기록:
 * - 사용자 행동 분석 및 보안 감사
 * - 시스템 접근 및 데이터 변경 이력 추적
 * - 관리자별 활동 패턴 및 통계 분석
 * - 보안 위협 탐지 및 대응
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminActivityLog.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 활동 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminActivityLogTest.php
 * ```
 */
class AdminActivityLogController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.activity-logs.index';
    public $createPath = 'jiny-admin::admin.activity-logs.create';
    public $editPath = 'jiny-admin::admin.activity-logs.edit';
    public $showPath = 'jiny-admin::admin.activity-logs.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['admin_user_id', 'action', 'ip_address', 'search', 'date_from', 'date_to'];
    protected $validFilters = [
        'admin_user_id' => 'string|uuid',
        'action' => 'string|max:100',
        'ip_address' => 'string|max:45',
        'search' => 'string',
        'date_from' => 'date',
        'date_to' => 'date'
    ];
    protected $sortableColumns = ['id', 'admin_user_id', 'action', 'ip_address', 'created_at'];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_activity_logs';

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
        return 'admin_activity_logs';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.activity-logs';
    }

    /**
     * 활동 로그 목록 조회 (템플릿 메소드 구현)
     * 관리자 활동 로그를 필터링하여 표시
     */
    protected function _index(Request $request): View
    {
        $query = AdminActivityLog::with('adminUser');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['search']);
        
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 통계 데이터 추가
        $stats = $this->getActivityStats();

        // 관리자 목록 (필터용)
        $adminUsers = AdminUser::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.admin.activity-logs.',
            'stats' => $stats,
            'adminUsers' => $adminUsers,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 활동 로그 생성 폼 (보안상 비활성화)
     */
    protected function _create(Request $request): View
    {
        // 보안상 활동 로그는 수동 생성 불가
        abort(403, '활동 로그는 시스템에서 자동으로 생성됩니다.');
    }

    /**
     * 활동 로그 저장 (보안상 비활성화)
     */
    protected function _store(Request $request): JsonResponse
    {
        // 보안상 활동 로그는 수동 생성 불가
        return response()->json([
            'success' => false,
            'message' => '활동 로그는 시스템에서 자동으로 생성됩니다.'
        ], 403);
    }

    /**
     * 활동 로그 상세 조회 (템플릿 메소드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $log = AdminActivityLog::with('adminUser')->findOrFail($id);
        return view($this->showPath, [
            'route' => 'admin.admin.activity-logs.',
            'log' => $log,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 활동 로그 수정 폼 (보안상 비활성화)
     */
    protected function _edit(Request $request, $id): View
    {
        // 보안상 활동 로그는 수정 불가
        abort(403, '활동 로그는 보안상 수정할 수 없습니다.');
    }

    /**
     * 활동 로그 업데이트 (보안상 비활성화)
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        // 보안상 활동 로그는 수정 불가
        return response()->json([
            'success' => false,
            'message' => '활동 로그는 보안상 수정할 수 없습니다.'
        ], 403);
    }

    /**
     * 활동 로그 삭제 (보안상 비활성화)
     */
    protected function _destroy(Request $request): JsonResponse
    {
        // 보안상 활동 로그는 삭제 불가
        return response()->json([
            'success' => false,
            'message' => '활동 로그는 보안상 삭제할 수 없습니다.'
        ], 403);
    }

    /**
     * 필터링 적용
     */
    protected function applyFilter(array $filters, $query, array $likeFields = []): object
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
                $q->where('action', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    /**
     * 활동 로그 통계 데이터 조회
     */
    private function getActivityStats()
    {
        return [
            'total_logs' => AdminActivityLog::count(),
            'today_logs' => AdminActivityLog::whereDate('created_at', today())->count(),
            'this_week' => AdminActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'unique_users' => AdminActivityLog::distinct('admin_user_id')->count(),
            'recent_activity' => AdminActivityLog::with('adminUser')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];
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
     * 삭제 확인 폼 반환
     */
    public function deleteConfirm(Request $request, $id)
    {
        try {
            $item = AdminActivityLog::findOrFail($id);
            $url = route('admin.admin.activity-log.destroy', $id);
            $title = '활동 로그 삭제';
            
            // AJAX 요청인 경우 HTML만 반환
            if ($request->ajax()) {
                return view('jiny-admin::admin.activity-logs.form_delete', compact('item', 'url', 'title'));
            }
            
            // 일반 요청인 경우 전체 페이지 반환
            return view('jiny-admin::admin.activity-logs.form_delete', compact('item', 'url', 'title'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 해당 ID의 활동 로그가 존재하지 않는 경우
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '해당 활동 로그를 찾을 수 없습니다. 이미 삭제되었거나 존재하지 않습니다.'
                ], 404);
            }
            
            // 일반 요청인 경우 오류 페이지로 리다이렉트
            return redirect()->route('admin.admin.activity-log.index')
                ->with('error', '해당 활동 로그를 찾을 수 없습니다. 이미 삭제되었거나 존재하지 않습니다.');
        } catch (\Exception $e) {
            // 기타 예외 처리
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '활동 로그 조회 중 오류가 발생했습니다.'
                ], 500);
            }
            
            return redirect()->route('admin.admin.activity-log.index')
                ->with('error', '활동 로그 조회 중 오류가 발생했습니다.');
        }
    }

    /**
     * 활동 로그 통계
     */
    public function stats(): View
    {
        $globalStats = AdminActivityLog::selectRaw('COUNT(*) as total, COUNT(DISTINCT admin_user_id) as unique_users')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        $recentStats = AdminActivityLog::selectRaw('COUNT(*) as total, COUNT(DISTINCT admin_user_id) as unique_users')
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        // 일별 통계
        $dailyStats = AdminActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 시간별 통계
        $hourlyStats = AdminActivityLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('jiny-admin::admin.activity-logs.stats', [
            'globalStats' => $globalStats,
            'recentStats' => $recentStats,
            'dailyStats' => $dailyStats,
            'hourlyStats' => $hourlyStats,
            'route' => 'admin.admin.activity-log.',
        ]);
    }

    /**
     * 관리자별 활동 통계
     */
    public function adminStats(int $adminId): View
    {
        $admin = AdminUser::findOrFail($adminId);
        
        $stats = AdminActivityLog::where('admin_user_id', $adminId)
            ->selectRaw('COUNT(*) as total, COUNT(DISTINCT DATE(created_at)) as active_days')
            ->where('created_at', '>=', now()->subDays(30))
            ->first();

        // 최근 활동
        $recentActivities = AdminActivityLog::where('admin_user_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 액션별 통계
        $actionStats = AdminActivityLog::where('admin_user_id', $adminId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-admin::admin.activity-logs.admin-stats', [
            'admin' => $admin,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'actionStats' => $actionStats,
            'route' => 'admin.admin.activity-log.',
        ]);
    }

    /**
     * 활동 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $query = AdminActivityLog::with('adminUser');

        // 필터 적용
        if ($request->filled('admin_user_id')) {
            $query->where('admin_user_id', $request->admin_user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Activity Log 기록
        $this->logActivity('export', '활동 로그 내보내기', null, ['count' => $logs->count()]);

        // CSV 형식으로 변환
        $csvData = [];
        $csvData[] = [
            'ID', '관리자', '액션', '설명', 'IP주소', '생성일시'
        ];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->adminUser->name ?? 'N/A',
                $log->action,
                $log->description ?? 'N/A',
                $log->ip_address ?? 'N/A',
                $log->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'filename' => 'admin_activity_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        $logs = AdminActivityLog::with('adminUser')->get();
        $filename = 'activity_logs_'.now()->format('Ymd_His').'.csv';
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', '관리자', '활동', '설명', 'IP', '생성일']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->adminUser?->name,
                $log->action,
                $log->description,
                $log->ip_address,
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
        // 보안상 활동 로그는 삭제 불가
        return response()->json([
            'success' => false,
            'message' => '활동 로그는 보안상 삭제할 수 없습니다.'
        ], 403);
    }

    /**
     * 활동 로그 정리 (오래된 로그 삭제)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $days = $request->days;
        $deletedCount = AdminActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        // Activity Log 기록
        $this->logActivity('cleanup', '활동 로그 정리', null, ['days' => $days, 'deleted_count' => $deletedCount]);

        return response()->json([
            'success' => true,
            'message' => "{$days}일 이전의 {$deletedCount}개 로그가 삭제되었습니다."
        ]);
    }
}
