<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

use Jiny\Admin\App\Models\AdminPermissionLog;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Http\Controllers\AdminResourceController;

/**
 * 권한 로그 관리 컨트롤러 (단순화된 버전)
 * 
 * 관리자의 권한 사용 이력을 조회하고 관리합니다.
 */
class AdminPermissionLogController extends AdminResourceController
{
    protected $sortableColumns = ['id', 'admin_user_id', 'action', 'resource_type', 'resource_id', 'result', 'ip_address', 'created_at'];
    protected $filterable = ['action', 'resource_type', 'result', 'admin_user_id', 'ip_address'];

    /**
     * 권한 체크 헬퍼 메소드
     */
    private function checkPermission(string $permission): bool
    {
        // admin 가드를 사용하여 현재 인증된 사용자 확인
        $adminId = Auth::guard('admin')->id();
        
        if (!$adminId) {
            return false;
        }

        // 관리자 정보 조회
        $admin = AdminUser::find($adminId);
        
        if (!$admin) {
            return false;
        }

        // Super 등급은 모든 권한 허용
        if ($admin->type === 'super') {
            return true;
        }

        // 등급 정보 조회
        $level = $admin->level;
        
        if (!$level) {
            return false;
        }

        // 권한 체크 (등급 기반)
        return $level->hasPermission($permission);
    }

    /**
     * 권한 로그 목록
     */
    public function index(Request $request): View
    {
        // 조회 권한 체크
        if (!$this->checkPermission('list')) {
            abort(403, '권한 로그 조회 권한이 없습니다.');
        }

        $query = AdminPermissionLog::with('admin');

        // 필터링
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function($q) use ($search) {
                $q->whereHas('admin', function($adminQuery) use ($search) {
                    $adminQuery->where('name', 'like', "%{$search}%");
                })->orWhere('action', 'like', "%{$search}%");
            });
        }
        if ($request->filled('filter_action')) {
            $query->where('action', $request->filter_action);
        }
        if ($request->filled('filter_result')) {
            $query->where('result', $request->filter_result);
        }
        if ($request->filled('filter_resource_type')) {
            $query->where('resource_type', $request->filter_resource_type);
        }
        if ($request->filled('filter_admin_id')) {
            $query->where('admin_user_id', $request->filter_admin_id);
        }
        if ($request->filled('filter_date_from')) {
            $query->where('created_at', '>=', $request->filter_date_from);
        }
        if ($request->filled('filter_date_to')) {
            $query->where('created_at', '<=', $request->filter_date_to);
        }
        if ($request->filled('filter_ip')) {
            $query->where('ip_address', 'like', "%{$request->filter_ip}%");
        }
        if ($request->filled('filter_reason')) {
            $query->where('reason', 'like', "%{$request->filter_reason}%");
        }

        // 정렬
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $logs = $query->paginate(15);

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_search', 'filter_action', 'filter_result', 'filter_resource_type',
            'filter_admin_id', 'filter_date_from', 'filter_date_to', 'filter_ip', 'filter_reason'
        ]);

        return view('jiny-admin::admin.permission-logs.index', [
            'rows' => $logs,
            'filters' => $filters,
            'route' => 'admin.admin.permission-logs.',
        ]);
    }

    /**
     * 권한 로그 상세 보기
     */
    public function show(Request $request, $id): View
    {
        // 조회 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 조회 권한이 없습니다.');
        }

        $log = AdminPermissionLog::with('admin')->findOrFail($id);

        return view('jiny-admin::admin.permission-logs.show', [
            'item' => $log,
            'route' => 'admin.admin.permission-logs.',
        ]);
    }

    /**
     * 권한 로그 통계
     */
    public function stats(): View
    {
        // 조회 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 통계 조회 권한이 없습니다.');
        }

        $stats = [
            'total' => AdminPermissionLog::count(),
            'successful' => AdminPermissionLog::where('result', 'success')->count(),
            'denied' => AdminPermissionLog::where('result', 'denied')->count(),
            'failed' => AdminPermissionLog::where('result', 'failed')->count(),
            'recent_24h' => AdminPermissionLog::where('created_at', '>=', now()->subDay())->count(),
            'recent_7d' => AdminPermissionLog::where('created_at', '>=', now()->subWeek())->count(),
        ];

        return view('jiny-admin::admin.permission-logs.stats', [
            'stats' => $stats,
            'route' => 'admin.admin.permission-logs.',
        ]);
    }

    /**
     * CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        // 조회 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 다운로드 권한이 없습니다.');
        }

        $query = AdminPermissionLog::with('admin');

        // 필터링 적용
        if ($request->filled('filter_action')) {
            $query->where('action', $request->filter_action);
        }
        if ($request->filled('filter_resource_type')) {
            $query->where('resource_type', $request->filter_resource_type);
        }
        if ($request->filled('filter_result')) {
            $query->where('result', $request->filter_result);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'permission_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV 헤더
            fputcsv($file, [
                'ID', '관리자', '액션', '리소스 타입', '리소스 ID', 
                '결과', 'IP 주소', '사유', '생성일시'
            ]);

            // 데이터
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->admin->name ?? 'Unknown',
                    $log->getActionText(),
                    $log->resource_type,
                    $log->resource_id,
                    $log->getResultText(),
                    $log->ip_address,
                    $log->reason,
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 기존 데이터 조회
     */
    protected function getOldData($id)
    {
        return AdminPermissionLog::find($id);
    }

    /**
     * 테이블명 반환
     */
    protected function getTableName()
    {
        return 'admin_permission_logs';
    }

    /**
     * 모듈명 반환
     */
    protected function getModuleName()
    {
        return 'permission_log';
    }
}
