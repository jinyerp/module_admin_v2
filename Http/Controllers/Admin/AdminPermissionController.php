<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\AdminAuditLogTrait;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Admin\AdminPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class AdminPermissionController extends Controller
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
    protected $logTableName = 'admin_permissions';

    public function __construct()
    {
        $this->filterable = [
            'name', // name: 권한명 (문자열)
            'display_name', // display_name: 표시명 (문자열)
            'module', // module: 모듈명 (문자열)
            'is_active', // is_active: 활성화 여부 (boolean)
            'sort_order' // sort_order: 정렬 순서 (숫자)
        ];

        $this->validFilters = [
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    /**
     * 권한 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminPermission::query();

        // 필터 파라미터 추출, 조건 적용용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);

        // 정렬
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(15);

        // 목록 출력
        return view('jiny-admin::admin.permissions.index', [
            'permissions' => $rows, // 데이터
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
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('display_name', 'like', "%{$filters['search']}%")
                  ->orWhere('module', 'like', "%{$filters['search']}%");
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
     * 권한 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::admin.permissions.create');
    }

    /**
     * 권한 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('Permission Store Request', [
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
        ]);

        // 유효성 검사 규칙 (생성용)
        $validationRules = [
            'name' => 'required|string|max:255|unique:admin_permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Permission Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // 디버깅: 처리된 데이터 로그
        \Log::info('Permission Store Processed Data', [
            'processed_data' => $data,
        ]);

        $permission = AdminPermission::create($data);

        // 관리자 액션 로깅
        $this->logCreateAction($permission, $data, "새로운 권한 생성: {$permission->display_name}");

        // 디버깅: 생성된 데이터 로그
        \Log::info('Permission Store Completed', [
            'created_permission' => $permission->toArray(),
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', '성공적으로 생성되었습니다.');
    }

    /**
     * 권한 상세 조회
     */
    public function show(AdminPermission $permission): View
    {
        return view('jiny-admin::admin.permissions.show', compact('permission'));
    }

    /**
     * 권한 수정 폼
     */
    public function edit(AdminPermission $permission): View
    {
        return view('jiny-admin::admin.permissions.edit', compact('permission'));
    }

    /**
     * 권한 업데이트
     */
    public function update(Request $request, AdminPermission $permission): RedirectResponse
    {
        // 디버깅: 요청 데이터 로그
        \Log::info('Permission Update Request', [
            'permission_id' => $permission->id,
            'request_data' => $request->all(),
            'has_is_active' => $request->has('is_active'),
        ]);

        // 유효성 검사 규칙 (수정용 - unique 규칙에서 현재 레코드 제외)
        $validationRules = [
            'name' => 'required|string|max:255|unique:admin_permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Permission Update Validation Failed', [
                'permission_id' => $permission->id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // 체크박스 처리
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // 디버깅: 처리된 데이터 로그
        \Log::info('Permission Update Processed Data', [
            'processed_data' => $data,
        ]);

        // 업데이트 전 원본 데이터 저장
        $oldValues = $permission->toArray();

        $permission->update($data);

        // 관리자 액션 로깅
        $this->logUpdateAction($permission, $oldValues, $data, "권한 정보 수정: {$permission->display_name}");

        // 디버깅: 업데이트 후 데이터 로그
        \Log::info('Permission Update Completed', [
            'updated_permission' => $permission->fresh()->toArray(),
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', '성공적으로 수정되었습니다.');
    }

    /**
     * 권한 삭제
     */
    public function destroy(AdminPermission $permission)
    {
        try {
            // 권한을 사용하는 관리자가 있는지 확인
            if ($permission->userPermissions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '이 권한을 사용하는 관리자가 있어 삭제할 수 없습니다.'
                ], 400);
            }

            // 삭제 전 원본 데이터 저장
            $oldValues = $permission->toArray();

            $permission->delete();

            // 관리자 액션 로깅
            $this->logDeleteAction($permission, $oldValues, "권한 삭제: {$permission->display_name}");

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
     * 권한 활성화/비활성화 토글
     */
    public function toggleActive(AdminPermission $permission): RedirectResponse
    {
        $oldValues = $permission->toArray();
        $permission->update(['is_active' => !$permission->is_active]);

        // 관리자 액션 로깅
        $action = $permission->is_active ? '활성화' : '비활성화';
        $this->logUpdateAction($permission, $oldValues, $permission->toArray(), "권한 {$action}: {$permission->display_name}");

        $status = $permission->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.permissions.index')
            ->with('success', "권한이 {$status}되었습니다.");
    }

    /**
     * 권한 순서 변경
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:admin_permissions,id',
        ]);

        foreach ($request->get('permissions') as $index => $permissionId) {
            AdminPermission::where('id', $permissionId)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', '권한 순서가 업데이트되었습니다.');
    }

    /**
     * 권한 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => AdminPermission::count(),
            'active' => AdminPermission::where('is_active', true)->count(),
            'inactive' => AdminPermission::where('is_active', false)->count(),
            'modules' => AdminPermission::getStats(),
        ];

        return view('jiny-admin::admin.permissions.stats', compact('stats'));
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

        // 삭제할 권한 정보 조회 (로깅용)
        $permissionsToDelete = AdminPermission::whereIn('id', $ids)->get();

        // 사용 중인 권한이 있는지 확인
        $usedPermissions = AdminPermission::whereIn('id', $ids)
            ->whereHas('userPermissions')
            ->get();

        if ($usedPermissions->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => '사용 중인 권한이 있어 삭제할 수 없습니다.'
            ], 400);
        }

        // 데이터를 삭제합니다.
        $deletedCount = AdminPermission::whereIn('id', $ids)->delete();

        // 관리자 액션 로깅
        $this->logBulkDeleteAction($ids, $deletedCount, "대량 권한 삭제: {$deletedCount}개");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 권한이 성공적으로 삭제되었습니다."
        ]);
    }
}
