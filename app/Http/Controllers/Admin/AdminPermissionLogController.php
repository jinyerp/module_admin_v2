<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\App\Models\AdminPermissionLog;
use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminPermissionLogController
 *
 * 관리자 권한 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminPermissionLog.admin_user_id 필드가 AdminUser.id와 연결
 * - 권한 관련 로그 추적 및 분석
 * - 보안 모니터링 및 감사
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminPermissionLog.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 권한 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminPermissionLogTest.php
 * ```
 */
class AdminPermissionLogController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.permission_logs.index';
    public $createPath = 'jiny-admin::admin.permission_logs.create';
    public $editPath = 'jiny-admin::admin.permission_logs.edit';
    public $showPath = 'jiny-admin::admin.permission_logs.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['search', 'action', 'resource_type', 'result', 'admin_user', 'date_from', 'date_to'];
    protected $validFilters = ['search', 'action', 'resource_type', 'result', 'admin_user', 'date_from', 'date_to', 'ip_address'];
    protected $sortableColumns = ['id', 'action', 'resource_type', 'resource_id', 'result', 'admin_user_id', 'ip_address', 'created_at'];

    private $config;

    /**
     * 생성자
     * 패키지의 admin config를 읽어와서 초기화
     */
    public function __construct()
    {
        parent::__construct();
        
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    /**
     * 테이블 이름 반환
     * Activity Log 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_permission_logs';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_permission_logs';
    }

    /**
     * 권한 체크 헬퍼 메소드
     * AdminUser의 등급 정보를 기반으로 권한을 검증
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

        // 권한 로그 조회는 보안상 제한적
        // 일반적으로 super 관리자만 접근 가능
        return false;
    }

    /**
     * 권한 로그 목록 조회
     * index() 에서 템플릿 메소드 호출
     * AdminUser와의 연관성을 고려하여 관리자 정보 표시
     */
    protected function _index(Request $request): View
    {
        // 권한 체크
        if (!$this->checkPermission('list')) {
            abort(403, '권한 로그 조회 권한이 없습니다.');
        }

        $query = AdminPermissionLog::with('adminUser');

        // 필터링
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('resource_type', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_action')) {
            $query->where('action', $request->filter_action);
        }

        if ($request->filled('filter_resource_type')) {
            $query->where('resource_type', $request->filter_resource_type);
        }

        if ($request->filled('filter_result')) {
            $query->where('result', $request->filter_result);
        }

        if ($request->filled('filter_admin_user')) {
            $query->whereHas('adminUser', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter_admin_user}%")
                  ->orWhere('email', 'like', "%{$request->filter_admin_user}%");
            });
        }

        // 날짜 필터링
        $query = $this->applyDateFilter($query, $request, 'created_at');

        // 정렬
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rows = $query->paginate($request->get('per_page', 15));

        // 필터 데이터 전달
        $filters = $request->only($this->filterable);

        // Activity Log 기록
        $this->logActivity('list', '권한 로그 목록 조회', null, $filters);

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.admin.permission_logs.',
        ]);
    }

    /**
     * 권한 로그 생성 폼
     */
    protected function _create(Request $request): View
    {
        // 권한 체크
        if (!$this->checkPermission('create')) {
            abort(403, '권한 로그 생성 권한이 없습니다.');
        }

        // Activity Log 기록
        $this->logActivity('create', '권한 로그 생성 폼 접근', null, []);

        return view($this->createPath, [
            'route' => 'admin.admin.permission_logs.',
        ]);
    }

    /**
     * 권한 로그 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        // 권한 체크
        if (!$this->checkPermission('create')) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 생성 권한이 없습니다.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'admin_user_id' => 'required|integer|exists:admin_users,id',
                'action' => 'required|string|max:100',
                'resource_type' => 'required|string|max:100',
                'resource_id' => 'nullable|integer',
                'result' => 'required|in:success,denied,failed',
                'ip_address' => 'nullable|ip',
                'user_agent' => 'nullable|string|max:500',
                'reason' => 'nullable|string|max:1000',
            ], [
                'admin_user_id.required' => '관리자 ID를 입력해주세요.',
                'admin_user_id.exists' => '존재하지 않는 관리자입니다.',
                'action.required' => '액션을 입력해주세요.',
                'action.max' => '액션은 100자를 초과할 수 없습니다.',
                'resource_type.required' => '리소스 타입을 입력해주세요.',
                'resource_type.max' => '리소스 타입은 100자를 초과할 수 없습니다.',
                'result.required' => '결과를 입력해주세요.',
                'result.in' => '유효하지 않은 결과입니다.',
                'ip_address.ip' => '유효하지 않은 IP 주소입니다.',
                'user_agent.max' => '사용자 에이전트는 500자를 초과할 수 없습니다.',
                'reason.max' => '사유는 1000자를 초과할 수 없습니다.',
            ]);

            // 기본값 설정
            $validated['ip_address'] = $validated['ip_address'] ?? $request->ip();
            $validated['user_agent'] = $validated['user_agent'] ?? $request->userAgent();

            $permissionLog = AdminPermissionLog::create($validated);

            // Activity Log 기록
            $this->logActivity('create', '권한 로그 생성', $permissionLog->id, $validated);

            return response()->json([
                'success' => true,
                'message' => '권한 로그가 성공적으로 생성되었습니다.',
                'data' => [
                    'id' => $permissionLog->id,
                    'action' => $permissionLog->action,
                    'resource_type' => $permissionLog->resource_type
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 권한 로그 상세 보기
     * 해당 로그의 관리자 정보도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        // 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 조회 권한이 없습니다.');
        }

        $permissionLog = AdminPermissionLog::with('adminUser')->findOrFail($id);

        // Activity Log 기록
        $this->logActivity('read', '권한 로그 상세 조회', $id, ['log_id' => $id]);

        return view($this->showPath, [
            'permissionLog' => $permissionLog,
            'route' => 'admin.admin.permission_logs.',
        ]);
    }

    /**
     * 권한 로그 수정 폼
     */
    protected function _edit(Request $request, $id): View
    {
        // 권한 체크
        if (!$this->checkPermission('update')) {
            abort(403, '권한 로그 수정 권한이 없습니다.');
        }

        $permissionLog = AdminPermissionLog::findOrFail($id);

        // Activity Log 기록
        $this->logActivity('update', '권한 로그 수정 폼 접근', $id, ['log_id' => $id]);

        return view($this->editPath, [
            'permissionLog' => $permissionLog,
            'route' => 'admin.admin.permission_logs.',
        ]);
    }

    /**
     * 권한 로그 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        // 권한 체크
        if (!$this->checkPermission('update')) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 수정 권한이 없습니다.'
            ], 403);
        }

        try {
            $permissionLog = AdminPermissionLog::findOrFail($id);

            // 수정 전 데이터 가져오기 (Audit Log용)
            $oldData = $permissionLog->toArray();

            $validated = $request->validate([
                'action' => 'required|string|max:100',
                'resource_type' => 'required|string|max:100',
                'resource_id' => 'nullable|integer',
                'result' => 'required|in:success,denied,failed',
                'reason' => 'nullable|string|max:1000',
            ], [
                'action.required' => '액션을 입력해주세요.',
                'action.max' => '액션은 100자를 초과할 수 없습니다.',
                'resource_type.required' => '리소스 타입을 입력해주세요.',
                'resource_type.max' => '리소스 타입은 100자를 초과할 수 없습니다.',
                'result.required' => '결과를 입력해주세요.',
                'result.in' => '유효하지 않은 결과입니다.',
                'reason.max' => '사유는 1000자를 초과할 수 없습니다.',
            ]);

            $permissionLog->update($validated);

            // Activity Log 기록
            $this->logActivity('update', '권한 로그 수정', $permissionLog->id, $validated);
            
            // Audit Log 기록
            $this->logAudit('update', $oldData, $validated, '권한 로그 수정', $permissionLog->id);

            return response()->json([
                'success' => true,
                'message' => '권한 로그가 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $permissionLog->id,
                    'action' => $permissionLog->action,
                    'resource_type' => $permissionLog->resource_type
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 권한 로그 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        
        // 권한 체크
        if (!$this->checkPermission('delete')) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 삭제 권한이 없습니다.'
            ], 403);
        }

        try {
            $permissionLog = AdminPermissionLog::findOrFail($id);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $permissionLog->toArray();

            $permissionLog->delete();

            // Activity Log 기록
            $this->logActivity('delete', '권한 로그 삭제', $id, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '권한 로그 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '권한 로그가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '권한 로그 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 통계 정보
     * AdminUser와의 연관성을 반영한 통계
     */
    public function stats(): View
    {
        // 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 통계 조회 권한이 없습니다.');
        }

        $stats = [
            'total' => AdminPermissionLog::count(),
            'success' => AdminPermissionLog::where('result', 'success')->count(),
            'denied' => AdminPermissionLog::where('result', 'denied')->count(),
            'failed' => AdminPermissionLog::where('result', 'failed')->count(),
            'today' => AdminPermissionLog::whereDate('created_at', today())->count(),
            'this_week' => AdminPermissionLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => AdminPermissionLog::whereMonth('created_at', now()->month)->count(),
            'action_distribution' => AdminPermissionLog::select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'resource_type_distribution' => AdminPermissionLog::select('resource_type', DB::raw('count(*) as count'))
                ->groupBy('resource_type')
                ->orderBy('count', 'desc')
                ->get(),
            'admin_user_distribution' => AdminPermissionLog::select('admin_user_id', DB::raw('count(*) as count'))
                ->with('adminUser:id,name,email')
                ->groupBy('admin_user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'recent_activities' => AdminPermissionLog::with('adminUser:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('jiny-admin::admin.permission_logs.stats', compact('stats'));
    }

    /**
     * CSV 다운로드
     */
    public function downloadCsv(Request $request)
    {
        // 권한 체크
        if (!$this->checkPermission('read')) {
            abort(403, '권한 로그 다운로드 권한이 없습니다.');
        }

        try {
            $query = AdminPermissionLog::with('adminUser');

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

            if ($request->filled('filter_date_from')) {
                $query->whereDate('created_at', '>=', $request->filter_date_from);
            }

            if ($request->filled('filter_date_to')) {
                $query->whereDate('created_at', '<=', $request->filter_date_to);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            // CSV 헤더
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="permission_logs_' . date('Y-m-d_H-i-s') . '.csv"',
            ];

            // CSV 콜백
            $callback = function () use ($logs) {
                $file = fopen('php://output', 'w');
                
                // CSV 헤더
                fputcsv($file, [
                    'ID', '관리자', '액션', '리소스 타입', '리소스 ID', 
                    '결과', 'IP 주소', '사유', '생성일시'
                ]);

                // CSV 데이터
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->adminUser ? $log->adminUser->name : 'N/A',
                        $log->action,
                        $log->resource_type,
                        $log->resource_id,
                        $log->result,
                        $log->ip_address,
                        $log->reason,
                        $log->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            // Activity Log 기록
            $this->logActivity('export', '권한 로그 CSV 다운로드', null, [
                'filters' => $request->only($this->filterable),
                'total_records' => $logs->count()
            ]);

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CSV 다운로드 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 기존 데이터 조회
     */
    protected function getOldData($id)
    {
        return AdminPermissionLog::find($id);
    }
}
