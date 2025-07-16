<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\AdminAuditLogTrait;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Admin\AdminEmail;
use App\Models\Admin\AdminPermission;
use App\Models\Admin\AdminUserPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Jiny\Admin\Models\AdminUser;

class AdminUserPermissionController extends Controller
{
    use AdminAuditLogTrait;

    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_user_permissions';

    public function __construct()
    {
        $this->filterable = [
            'admin_id', // admin_id: 관리자 ID (숫자)
            'permission_id', // permission_id: 권한 ID (숫자)
            'is_active', // is_active: 활성화 여부 (boolean)
        ];

        $this->validFilters = [
            'admin_id' => 'required|string|exists:admin_emails,id',
            'permission_id' => 'required|integer|exists:admin_permissions,id',
            'granted_by' => 'nullable|string|exists:admin_emails,id',
            'expires_at' => 'nullable|date',
            'reason' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * 관리자별 권한 할당 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminUserPermission::with(['admin', 'permission', 'grantedBy']);

        // 필터 파라미터 추출, 조건 적용용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);

        // 정렬
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 목록 출력
        return view('admin.user-permissions.index', [
            'userPermissions' => $rows, // 데이터
            'filters' => $filters, // 필터
            'sort' => $sortField, // 정렬
            'dir' => $sortDirection, // 정렬 방향
        ]);
    }

    /**
     * 필터링 적용
     * @param Request $request
     * @param Builder $query
     * @return Builder
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
            $query->whereHas('admin', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            })->orWhereHas('permission', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('display_name', 'like', "%{$filters['search']}%");
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
     * 관리자별 권한 할당 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();
        $permissions = AdminPermission::active()->ordered()->get();

        return view('admin.user-permissions.create', compact('admins', 'permissions'));
    }

    /**
     * 권한 할당 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('User Permission Store Request', [
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
        ]);

        // 유효성 검사 규칙
        $validationRules = [
            'admin_id' => 'required|string|exists:admin_emails,id',
            'permission_id' => 'required|integer|exists:admin_permissions,id',
            'granted_by' => 'nullable|string|exists:admin_emails,id',
            'expires_at' => 'nullable|date|after:now',
            'reason' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('User Permission Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['granted_at'] = now();
        $data['granted_by'] = $data['granted_by'] ?? auth()->id() ?? '550e8400-e29b-41d4-a716-446655440000';

        // 디버깅: 처리된 데이터 로그
        \Log::info('User Permission Store Processed Data', [
            'processed_data' => $data,
        ]);

        // 중복 권한 할당 방지
        $existingPermission = AdminUserPermission::where('admin_id', $data['admin_id'])
            ->where('permission_id', $data['permission_id'])
            ->first();

        if ($existingPermission) {
            return redirect()->route('admin.user-permissions.index')
                ->with('error', '이미 할당된 권한입니다.');
        }

        $userPermission = AdminUserPermission::create($data);

        // 관리자 액션 로깅
        $admin = AdminUser::find($data['admin_id']);
        $permission = AdminPermission::find($data['permission_id']);
        $this->logCreateAction($userPermission, $data, "권한 할당: {$admin->name}에게 {$permission->display_name} 권한 부여");

        // 디버깅: 생성된 데이터 로그
        \Log::info('User Permission Store Completed', [
            'created_user_permission' => $userPermission->toArray(),
        ]);

        return redirect()->route('admin.user-permissions.index')
            ->with('success', '성공적으로 권한이 할당되었습니다.');
    }

    /**
     * 권한 할당 상세 조회
     */
    public function show(AdminUserPermission $userPermission): View
    {
        return view('admin.user-permissions.show', compact('userPermission'));
    }

    /**
     * 권한 할당 수정 폼
     */
    public function edit(AdminUserPermission $userPermission): View
    {
        $admins = AdminUser::where('is_active', true)->get();
        $permissions = AdminPermission::active()->ordered()->get();

        return view('admin.user-permissions.edit', compact('userPermission', 'admins', 'permissions'));
    }

    /**
     * 권한 할당 업데이트
     */
    public function update(Request $request, AdminUserPermission $userPermission): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('User Permission Update Request', [
            'user_permission_id' => $userPermission->id,
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
        ]);

        // 유효성 검사 규칙
        $validationRules = [
            'admin_id' => 'required|string|exists:admin_emails,id',
            'permission_id' => 'required|integer|exists:admin_permissions,id',
            'granted_by' => 'nullable|string|exists:admin_emails,id',
            'expires_at' => 'nullable|date',
            'reason' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('User Permission Update Validation Failed', [
                'user_permission_id' => $userPermission->id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // 디버깅: 처리된 데이터 로그
        \Log::info('User Permission Update Processed Data', [
            'processed_data' => $data,
        ]);

        // 업데이트 전 원본 데이터 저장
        $oldValues = $userPermission->toArray();

        $userPermission->update($data);

        // 관리자 액션 로깅
        $admin = AdminUser::find($data['admin_id']);
        $permission = AdminPermission::find($data['permission_id']);
        $this->logUpdateAction($userPermission, $oldValues, $data, "권한 할당 수정: {$admin->name}의 {$permission->display_name} 권한");

        // 디버깅: 업데이트 후 데이터 로그
        \Log::info('User Permission Update Completed', [
            'updated_user_permission' => $userPermission->fresh()->toArray(),
        ]);

        return redirect()->route('admin.user-permissions.index')
            ->with('success', '성공적으로 수정되었습니다.');
    }

    /**
     * 권한 할당 삭제
     */
    public function destroy(AdminUserPermission $userPermission)
    {
        try {
            // 삭제 전 원본 데이터 저장
            $oldValues = $userPermission->toArray();

            $userPermission->delete();

            // 관리자 액션 로깅
            $admin = $userPermission->admin;
            $permission = $userPermission->permission;
            $this->logDeleteAction($userPermission, $oldValues, "권한 할당 해제: {$admin->name}의 {$permission->display_name} 권한");

            // 플래시 메시지 설정
            session()->flash('deleted', '성공적으로 삭제되었습니다.');

            return response()->json([
                'success' => true,
                'message' => '성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 권한 할당 활성화/비활성화 토글
     */
    public function toggleActive(AdminUserPermission $userPermission): RedirectResponse
    {
        $oldValues = $userPermission->toArray();
        $userPermission->update(['is_active' => !$userPermission->is_active]);

        // 관리자 액션 로깅
        $action = $userPermission->is_active ? '활성화' : '비활성화';
        $admin = $userPermission->admin;
        $permission = $userPermission->permission;
        $this->logUpdateAction($userPermission, $oldValues, $userPermission->toArray(), "권한 할당 {$action}: {$admin->name}의 {$permission->display_name} 권한");

        $status = $userPermission->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.user-permissions.index')
            ->with('success', "권한 할당이 {$status}되었습니다.");
    }

    /**
     * 관리자별 권한 할당 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => AdminUserPermission::count(),
            'active' => AdminUserPermission::where('is_active', true)->count(),
            'inactive' => AdminUserPermission::where('is_active', false)->count(),
            'expired' => AdminUserPermission::expired()->count(),
            'adminStats' => AdminUserPermission::getAdminStats(),
            'permissionStats' => AdminUserPermission::getStats(),
        ];

        return view('admin.user-permissions.stats', compact('stats'));
    }

    /**
     * 선택 삭제 (bulk delete)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        // 입력된 값이 배열인지 확인합니다.
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }

        // ids 배열을 정수로 변환
        $ids = array_map('intval', $request->input('ids'));

        // 삭제할 권한 할당 정보 조회 (로깅용)
        $userPermissionsToDelete = AdminUserPermission::whereIn('id', $ids)->get();

        // 데이터를 삭제합니다.
        $deletedCount = AdminUserPermission::whereIn('id', $ids)->delete();

        // 관리자 액션 로깅
        $this->logBulkDeleteAction($ids, $deletedCount, "대량 권한 할당 해제: {$deletedCount}개");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 권한 할당이 성공적으로 삭제되었습니다."
        ]);
    }

    /**
     * 관리자별 권한 할당 페이지
     */
    public function assignToAdmin(AdminUser $admin): View
    {
        $assignedPermissions = $admin->userPermissions()->with('permission')->get();
        $availablePermissions = AdminPermission::active()
            ->whereNotIn('id', $assignedPermissions->pluck('permission_id'))
            ->ordered()
            ->get();

        return view('admin.user-permissions.assign', compact('admin', 'assignedPermissions', 'availablePermissions'));
    }

    /**
     * 관리자에게 권한 일괄 할당
     */
    public function bulkAssign(Request $request, AdminUser $admin): RedirectResponse
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer|exists:admin_permissions,id',
            'expires_at' => 'nullable|date|after:now',
            'reason' => 'nullable|string',
        ]);

        $permissionIds = $request->input('permission_ids');
        $expiresAt = $request->input('expires_at');
        $reason = $request->input('reason');

        $grantedBy = auth()->id() ?? '550e8400-e29b-41d4-a716-446655440000';

        foreach ($permissionIds as $permissionId) {
            // 중복 할당 방지
            $existing = AdminUserPermission::where('admin_id', $admin->id)
                ->where('permission_id', $permissionId)
                ->first();

            if (!$existing) {
                AdminUserPermission::create([
                    'admin_id' => $admin->id,
                    'permission_id' => $permissionId,
                    'granted_by' => $grantedBy,
                    'granted_at' => now(),
                    'expires_at' => $expiresAt,
                    'reason' => $reason,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.user-permissions.assign', $admin)
            ->with('success', '권한이 성공적으로 할당되었습니다.');
    }
}
