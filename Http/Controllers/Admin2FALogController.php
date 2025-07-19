<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Jiny\Admin\Models\Admin2FALog;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\DB;

class Admin2FALogController extends AdminResourceController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->filterable = [
            'admin_user_id',
            'action',
            'status',
            'ip_address',
        ];
        
        $this->validFilters = [
            'admin_user_id' => 'nullable|integer|exists:admin_users,id',
            'action' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:success,fail',
            'ip_address' => 'nullable|string|max:45',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ];
    }

    /**
     * get table name
     */
    protected function getTableName()
    {
        return 'admin_2fa_logs';
    }

    /**
     * get module name
     */
    protected function getModuleName()
    {
        return 'admin_2fa_logs';
    }

    /**
     * 수정 전 데이터 가져오기
     */
    protected function getOldData($id)
    {
        $log = Admin2FALog::find($id);
        return $log ? $log->toArray() : null;
    }

    /**
     * 2FA 로그 목록
     */
    protected function _index(Request $request)
    {
        $query = Admin2FALog::with('adminUser')
            ->orderBy('created_at', 'desc');

        // 검색 필터
        if ($request->filled('filter_admin_user_id')) {
            $query->where('admin_user_id', $request->filter_admin_user_id);
        }

        if ($request->filled('filter_action')) {
            $query->where('action', $request->filter_action);
        }

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        if ($request->filled('filter_ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->filter_ip_address . '%');
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('created_at', '<=', $request->filter_date_to);
        }

        if ($request->filled('filter_message')) {
            $query->where('message', 'like', '%' . $request->filter_message . '%');
        }

        $rows = $query->paginate(20);

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

        // 라우트 이름 설정
        $route = 'admin.admin.logs.2fa';

        return view('jiny-admin::admin.2fa-logs.index', compact('rows', 'stats', 'actionStats', 'adminUsers', 'route'));
    }

    /**
     * 2FA 로그 상세 보기
     */
    public function _show(Request $request, $id)
    {
        $log = Admin2FALog::with('adminUser')->findOrFail($id);
        
        return view('jiny-admin::admin.2fa-logs.show', 
        [
            'item' => $log        
        ]);
    }

    /**
     * 2FA 로그 통계
     */
    public function stats(Request $request)
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

        return view('jiny-admin::admin.2fa-logs.stats', 
        [
            'dailyStats' => $dailyStats,
            'actionStats' => $actionStats,
            'adminStats' => $adminStats,
            'ipStats' => $ipStats,
            'route' => $this->getRouteName($request),
        ]);
    }

    /**
     * 2FA 로그 내보내기
     */
    public function export(Request $request)
    {
        $query = Admin2FALog::with('adminUser')
            ->orderBy('created_at', 'desc');

        // 필터 적용
        if ($request->filled('admin_user_id')) {
            $query->where('admin_user_id', $request->admin_user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = '2fa_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV 헤더
            fputcsv($file, [
                'ID', '관리자', '이메일', '액션', '상태', '메시지', 
                'IP 주소', '사용자 에이전트', '생성일'
            ]);

            // 데이터
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
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 2FA 로그 일괄 삭제
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_2fa_logs,id'
            ]);

            $deletedCount = Admin2FALog::whereIn('id', $request->ids)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'deleted_count' => $deletedCount,
                    'message' => "{$deletedCount}개의 로그가 삭제되었습니다."
                ]);
            }

            return back()->with('success', "{$deletedCount}개의 로그가 삭제되었습니다.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 2FA 로그 정리 (오래된 로그 삭제)
     */
    public function cleanup(Request $request)
    {
        try {
            // 기본값으로 30일 설정
            $days = $request->input('days', 30);
            
            $request->validate([
                'days' => 'nullable|integer|min:1|max:365'
            ]);

            $cutoffDate = now()->subDays($days);
            $deletedCount = Admin2FALog::where('created_at', '<', $cutoffDate)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'deleted_count' => $deletedCount,
                    'message' => "{$days}일 이전의 {$deletedCount}개 로그가 정리되었습니다."
                ]);
            }

            return back()->with('success', "{$days}일 이전의 {$deletedCount}개 로그가 정리되었습니다.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '로그 정리 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', '로그 정리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 단일 2FA 로그 삭제
     */
    public function _destroy(Request $request)
    {
        try {
            // 라우트 파라미터에서 ID 가져오기
            $id = $request->route('id');
            
            if (!$id) {
                throw new \Exception('삭제할 로그 ID가 제공되지 않았습니다.');
            }

            $log = Admin2FALog::findOrFail($id);
            $log->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '로그가 삭제되었습니다.'
                ]);
            }

            return back()->with('success', '로그가 삭제되었습니다.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '로그 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', '로그 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 2FA 로그 일괄 삭제 (POST 메소드)
     */
    public function bulkDeletePost(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_2fa_logs,id'
            ]);

            $deletedCount = Admin2FALog::whereIn('id', $request->ids)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'deleted_count' => $deletedCount,
                    'message' => "{$deletedCount}개의 로그가 삭제되었습니다."
                ]);
            }

            return back()->with('success', "{$deletedCount}개의 로그가 삭제되었습니다.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
} 