<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\App\Models\AdminLevel;
use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Jiny\Admin\App\Models\AdminUser;
use App\Helpers\PermissionHelper;
use Jiny\Admin\App\Models\AdminPermissionLog;

/**
 * AdminLevelController
 *
 * 관리자 등급 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminUser.type 필드가 AdminLevel.code와 연결
 * - 등급별 사용자 수 계산 및 표시
 * - 권한 기반 사용자 접근 제어
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminLevel.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 등급 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminLevelTest.php
 * ```
 */
class AdminLevelController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.levels.index';
    public $createPath = 'jiny-admin::admin.levels.create';
    public $editPath = 'jiny-admin::admin.levels.edit';
    public $showPath = 'jiny-admin::admin.levels.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['name', 'code', 'badge_color', 'can_create', 'can_read', 'can_update', 'can_delete'];
    protected $validFilters = ['name', 'code', 'badge_color', 'can_create', 'can_read', 'can_update', 'can_delete'];
    protected $sortableColumns = ['id', 'name', 'code', 'badge_color', 'can_create', 'can_read', 'can_update', 'can_delete', 'created_at', 'updated_at'];
    
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
        return 'admin_levels';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_levels';
    }

    /**
     * 권한 체크 헬퍼 메소드
     * AdminUser의 등급 정보를 기반으로 권한을 검증
     */
    private function checkPermission(string $permission): bool
    {
        // admin 가드를 사용하여 현재 인증된 사용자 확인
        $adminId = Auth::guard('admin')->id();
        
        // 디버깅 로그 추가
        \Log::info('권한 체크 시작', [
            'permission' => $permission,
            'admin_id' => $adminId,
            'url' => request()->url(),
        ]);
        
        // 인증되지 않은 경우 기본적으로 false 반환
        if (!$adminId) {
            \Log::warning('권한 체크 실패: 인증되지 않은 사용자', [
                'permission' => $permission,
                'url' => request()->url(),
                'user_agent' => request()->userAgent()
            ]);
            return false;
        }

        // 관리자 정보 조회
        $admin = AdminUser::find($adminId);
        
        if (!$admin) {
            \Log::warning('권한 체크 실패: 관리자 정보를 찾을 수 없음', [
                'admin_id' => $adminId,
                'permission' => $permission
            ]);
            return false;
        }

        // 디버깅: 관리자 정보 로그
        \Log::info('관리자 정보', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_type' => $admin->type,
            'permission' => $permission
        ]);

        // Super 등급은 모든 권한 허용
        if ($admin->type === 'super') {
            \Log::info('Super 등급 사용자 - 모든 권한 허용', [
                'permission' => $permission,
                'admin_type' => $admin->type
            ]);
            return true;
        }

        // 등급 정보 조회 (AdminUser.type과 AdminLevel.code 연결)
        $level = AdminLevel::where('code', $admin->type)->first();
        
        if (!$level) {
            \Log::warning('권한 체크 실패: 등급 정보를 찾을 수 없음', [
                'admin_id' => $adminId,
                'admin_type' => $admin->type,
                'permission' => $permission
            ]);
            return false;
        }

        // 디버깅: 등급 정보 로그
        \Log::info('등급 정보', [
            'level_id' => $level->id,
            'level_name' => $level->name,
            'level_code' => $level->code,
            'can_list' => $level->can_list ?? false,
            'can_create' => $level->can_create,
            'can_read' => $level->can_read,
            'can_update' => $level->can_update,
            'can_delete' => $level->can_delete,
            'requested_permission' => $permission
        ]);

        // 권한 체크 (등급 기반)
        $hasPermission = $level->hasPermission($permission);
        
        \Log::info('권한 체크 결과', [
            'permission' => $permission,
            'has_permission' => $hasPermission
        ]);

        return $hasPermission;
    }

    /**
     * 권한 로그 기록
     */
    private function logPermissionAction(string $action, string $resourceType, $resourceId = null, string $result = 'success', string $reason = null): void
    {
        try {
            $adminId = Auth::guard('admin')->id();
            if (!$adminId) return;

            AdminPermissionLog::create([
                'admin_user_id' => $adminId,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'result' => $result,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            \Log::error('권한 로그 기록 실패', [
                'error' => $e->getMessage(),
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
            ]);
        }
    }

    /**
     * 등급별 사용자 수 계산
     * AdminUser와 AdminLevel의 연관성을 반영
     */
    private function calculateUserCountsByLevel()
    {
        $levels = AdminLevel::all();
        $userCounts = [];
        
        foreach ($levels as $level) {
            // AdminUser.type 필드가 AdminLevel.code와 연결
            $userCount = AdminUser::where('type', $level->code)->count();
            $userCounts[$level->id] = $userCount;
        }
        
        return $userCounts;
    }

    /**
     * 등급 목록 조회
     * index() 에서 템플릿 메소드 호출
     * AdminUser와의 연관성을 고려하여 사용자 수 표시
     */
    protected function _index(Request $request): View
    {
        // 임시 디버깅: 현재 사용자 정보 출력
        $adminId = Auth::guard('admin')->id();
        $admin = AdminUser::find($adminId);
        
        // 인증 상태 디버깅
        \Log::info('인증 상태 디버깅', [
            'auth_id' => $adminId,
            'auth_check' => Auth::check(),
            'auth_guard_check' => Auth::guard('admin')->check(),
            'auth_guard_id' => Auth::guard('admin')->id(),
            'session_id' => session()->getId(),
            'user_exists' => $admin ? 'yes' : 'no',
            'user_name' => $admin ? $admin->name : 'N/A',
            'user_type' => $admin ? $admin->type : 'N/A',
        ]);
        
        if ($admin) {
            // AdminUser.type과 AdminLevel.code 연결 확인
            $level = AdminLevel::where('code', $admin->type)->first();
            \Log::info('현재 사용자 디버깅', [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'admin_type' => $admin->type,
                'level_exists' => $level ? 'yes' : 'no',
                'level_name' => $level ? $level->name : 'N/A',
                'level_code' => $level ? $level->code : 'N/A',
                'can_list' => $level ? ($level->can_list ?? false) : 'N/A'
            ]);
        } else {
            \Log::warning('사용자 정보를 찾을 수 없음', ['admin_id' => $adminId]);
        }

        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        // 조회 권한 체크
        if (!$this->checkPermission('list')) {
            $this->logPermissionAction('list', 'level', null, 'denied', '등급 조회 권한이 없습니다.');
            abort(403, '등급 조회 권한이 없습니다.');
        }

        $query = AdminLevel::query();

        // 각 등급별 사용자 수 계산 (AdminUser와의 연관성 반영)
        $levels = $query->get();
        $levelsWithUserCount = $levels->map(function ($level) {
            // AdminUser.type 필드가 AdminLevel.code와 연결
            $level->users_count = AdminUser::where('type', $level->code)->count();
            return $level;
        });

        // 필터링
        if ($request->filled('filter_name')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return str_contains(strtolower($level->name), strtolower($request->filter_name));
            });
        }
        if ($request->filled('filter_code')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return str_contains(strtolower($level->code), strtolower($request->filter_code));
            });
        }
        if ($request->filled('filter_badge_color')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return str_contains(strtolower($level->badge_color ?? ''), strtolower($request->filter_badge_color));
            });
        }
        if ($request->filled('filter_can_create')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return $level->can_create == $request->filter_can_create;
            });
        }
        if ($request->filled('filter_can_read')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return $level->can_read == $request->filter_can_read;
            });
        }
        if ($request->filled('filter_can_update')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return $level->can_update == $request->filter_can_update;
            });
        }
        if ($request->filled('filter_can_delete')) {
            $levelsWithUserCount = $levelsWithUserCount->filter(function ($level) use ($request) {
                return $level->can_delete == $request->filter_can_delete;
            });
        }


        // 정렬
        $sortBy = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            if ($sortOrder === 'asc') {
                $levelsWithUserCount = $levelsWithUserCount->sortBy($sortBy);
            } else {
                $levelsWithUserCount = $levelsWithUserCount->sortByDesc($sortBy);
            }
        } else {
            $levelsWithUserCount = $levelsWithUserCount->sortBy('id');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);
        $total = $levelsWithUserCount->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $levelsWithUserCount->slice($offset, $perPage);
        
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_name', 'filter_code', 'filter_badge_color',
            'filter_can_create', 'filter_can_read', 'filter_can_update', 'filter_can_delete', 'filter_sort_order'
        ]);

        // 권한 로그 기록
        $this->logPermissionAction('list', 'level', null, 'success');

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.admin.levels.',
        ]);
    }

    /**
     * 등급 생성 폼
     */
    protected function _create(Request $request): View
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        // 생성 권한 체크
        if (!$this->checkPermission('create')) {
            $this->logPermissionAction('create', 'level', null, 'denied', '등급 생성 권한이 없습니다.');
            abort(403, '등급 생성 권한이 없습니다.');
        }

        // 권한 로그 기록
        $this->logPermissionAction('create', 'level', null, 'success');

        return view($this->createPath, [
            'route' => 'admin.admin.levels.',
        ]);
    }

    /**
     * 등급 상세 보기
     * 해당 등급을 사용하는 AdminUser 목록도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 조회 권한 체크
        if (!$this->checkPermission('read')) {
            $this->logPermissionAction('read', 'level', $id, 'denied', '등급 조회 권한이 없습니다.');
            abort(403, '등급 조회 권한이 없습니다.');
        }
        */

        $level = AdminLevel::findOrFail($id);
        
        // 해당 등급을 사용하는 AdminUser 목록 조회 (연관성 반영)
        $usersWithThisLevel = AdminUser::where('type', $level->code)->get();

        // 권한 로그 기록
        $this->logPermissionAction('read', 'level', $id, 'success');

        return view($this->showPath, [
            'level' => $level,
            'users' => $usersWithThisLevel,
            'route' => 'admin.admin.levels.',
        ]);
    }

    /**
     * 등급 수정 폼
     */
    protected function _edit(Request $request, $id): View
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 수정 권한 체크
        if (!$this->checkPermission('update')) {
            $this->logPermissionAction('update', 'level', $id, 'denied', '등급 수정 권한이 없습니다.');
            abort(403, '등급 수정 권한이 없습니다.');
        }
        */

        $level = AdminLevel::findOrFail($id);
        
        // 해당 등급을 사용하는 AdminUser 수 확인
        $userCount = AdminUser::where('type', $level->code)->count();

        // 권한 로그 기록
        $this->logPermissionAction('update', 'level', $id, 'success');

        return view($this->editPath, [
            'level' => $level,
            'userCount' => $userCount,
            'route' => 'admin.admin.levels.',
        ]);
    }

    /**
     * 등급 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 생성 권한 체크
        if (!$this->checkPermission('create')) {
            $this->logPermissionAction('create', 'level', null, 'denied', '등급 생성 권한이 없습니다.');
            return response()->json([
                'success' => false,
                'message' => '등급 생성 권한이 없습니다.'
            ], 403);
        }
        */

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:admin_levels,code',
                'badge_color' => 'nullable|string|max:50',
                'can_create' => 'boolean',
                'can_read' => 'boolean',
                'can_update' => 'boolean',
                'can_delete' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '등급명을 입력해주세요.',
                'name.max' => '등급명은 255자를 초과할 수 없습니다.',
                'code.required' => '등급코드를 입력해주세요.',
                'code.max' => '등급코드는 255자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 등급코드입니다.',
                'badge_color.max' => '배지 색상은 50자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['can_create'] = $request->has('can_create');
            $validated['can_read'] = $request->has('can_read');
            $validated['can_update'] = $request->has('can_update');
            $validated['can_delete'] = $request->has('can_delete');

            $level = AdminLevel::create($validated);

            // Activity Log 기록
            $this->logActivity('create', '등급 생성', $level->id, $validated);

            // 권한 로그 기록
            $this->logPermissionAction('create', 'level', $level->id, 'success');

            return response()->json([
                'success' => true,
                'message' => '등급이 성공적으로 등록되었습니다.',
                'data' => [
                    'id' => $level->id,
                    'name' => $level->name,
                    'code' => $level->code
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logPermissionAction('create', 'level', null, 'failed', '유효성 검사 실패');
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logPermissionAction('create', 'level', null, 'failed', '등급 등록 중 오류 발생');
            return response()->json([
                'success' => false,
                'message' => '등급 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 등급 수정
     * AdminUser와의 연관성을 고려하여 안전하게 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 수정 권한 체크
        if (!$this->checkPermission('update')) {
            $this->logPermissionAction('update', 'level', $id, 'denied', '등급 수정 권한이 없습니다.');
            return response()->json([
                'success' => false,
                'message' => '등급 수정 권한이 없습니다.'
            ], 403);
        }
        */

        try {
            $level = AdminLevel::findOrFail($id);

            // 수정 전 데이터 가져오기 (Audit Log용)
            $oldData = $level->toArray();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:admin_levels,code,' . $id,
                'badge_color' => 'nullable|string|max:50',
                'can_create' => 'boolean',
                'can_read' => 'boolean',
                'can_update' => 'boolean',
                'can_delete' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '등급명을 입력해주세요.',
                'name.max' => '등급명은 255자를 초과할 수 없습니다.',
                'code.required' => '등급코드를 입력해주세요.',
                'code.max' => '등급코드는 255자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 등급코드입니다.',
                'badge_color.max' => '배지 색상은 50자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['can_create'] = $request->has('can_create');
            $validated['can_read'] = $request->has('can_read');
            $validated['can_update'] = $request->has('can_update');
            $validated['can_delete'] = $request->has('can_delete');

            $level->update($validated);

            // Activity Log 기록
            $this->logActivity('update', '등급 수정', $level->id, $validated);
            
            // Audit Log 기록
            $this->logAudit('update', $oldData, $validated, '등급 수정', $level->id);

            // 권한 로그 기록
            $this->logPermissionAction('update', 'level', $id, 'success');

            return response()->json([
                'success' => true,
                'message' => '등급이 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $level->id,
                    'name' => $level->name,
                    'code' => $level->code
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logPermissionAction('update', 'level', $id, 'failed', '유효성 검사 실패');
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logPermissionAction('update', 'level', $id, 'failed', '등급 수정 중 오류 발생');
            return response()->json([
                'success' => false,
                'message' => '등급 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 등급 삭제
     * AdminUser와의 연관성을 확인하여 안전하게 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 삭제 권한 체크
        if (!$this->checkPermission('delete')) {
            $this->logPermissionAction('delete', 'level', $id, 'denied', '등급 삭제 권한이 없습니다.');
            return response()->json([
                'success' => false,
                'message' => '등급 삭제 권한이 없습니다.'
            ], 403);
        }
        */

        try {
            $level = AdminLevel::findOrFail($id);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $level->toArray();

            // 사용 중인 등급인지 확인 (AdminUser.type 필드와 AdminLevel.code 연결)
            $usersUsingLevel = AdminUser::where('type', $level->code)->count();
            if ($usersUsingLevel > 0) {
                $this->logPermissionAction('delete', 'level', $id, 'denied', '사용 중인 등급은 삭제할 수 없습니다.');
                return response()->json([
                    'success' => false,
                    'message' => '사용 중인 등급은 삭제할 수 없습니다. (사용자 수: ' . $usersUsingLevel . '명)'
                ], 400);
            }

            $level->delete();

            // Activity Log 기록
            $this->logActivity('delete', '등급 삭제', $id, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '등급 삭제', null);

            // 권한 로그 기록
            $this->logPermissionAction('delete', 'level', $id, 'success');

            return response()->json([
                'success' => true,
                'message' => '등급이 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            $this->logPermissionAction('delete', 'level', $id, 'failed', '등급 삭제 중 오류 발생');
            return response()->json([
                'success' => false,
                'message' => '등급 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼 반환
     * 해당 등급을 사용하는 AdminUser 정보도 함께 표시
     */
    public function deleteConfirm(Request $request, $id)
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 삭제 권한 체크
        if (!$this->checkPermission('delete')) {
            $this->logPermissionAction('delete', 'level', $id, 'denied', '등급 삭제 권한이 없습니다.');
            abort(403, '등급 삭제 권한이 없습니다.');
        }
        */

        $level = AdminLevel::findOrFail($id);
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        // 해당 등급을 사용하는 AdminUser 목록 조회
        $usersWithThisLevel = AdminUser::where('type', $level->code)->get();
        
        // 권한 로그 기록
        $this->logPermissionAction('delete', 'level', $id, 'success');

        return view('jiny-admin::admin.levels.form_delete', [
            'level' => $level,
            'users' => $usersWithThisLevel,
            'title' => '등급 삭제',
            'randomKey' => $randomKey
        ]);
    }

    /**
     * 일괄 삭제
     * AdminUser와의 연관성을 확인하여 안전하게 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 삭제 권한 체크
        if (!$this->checkPermission('delete')) {
            $this->logPermissionAction('bulk_delete', 'level', null, 'denied', '등급 일괄 삭제 권한이 없습니다.');
            return response()->json([
                'success' => false,
                'message' => '등급 일괄 삭제 권한이 없습니다.'
            ], 403);
        }
        */

        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_levels,id'
            ]);

            $ids = $request->input('ids');
            
            // 사용 중인 등급이 포함되어 있는지 확인 (AdminUser.type 필드와 AdminLevel.code 연결)
            $levels = AdminLevel::whereIn('id', $ids)->get();
            $usedLevels = [];
            
            foreach ($levels as $level) {
                $userCount = AdminUser::where('type', $level->code)->count();
                if ($userCount > 0) {
                    $usedLevels[] = $level->name . ' (' . $userCount . '명 사용 중)';
                }
            }
            
            if (!empty($usedLevels)) {
                $this->logPermissionAction('bulk_delete', 'level', null, 'denied', '사용 중인 등급이 포함되어 있어 일괄 삭제할 수 없습니다.');
                return response()->json([
                    'success' => false,
                    'message' => '다음 등급들은 사용 중이므로 삭제할 수 없습니다: ' . implode(', ', $usedLevels)
                ], 400);
            }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = AdminLevel::whereIn('id', $ids)->get()->toArray();

            AdminLevel::whereIn('id', $ids)->delete();

            // Activity Log 기록
            $this->logActivity('delete', '등급 일괄 삭제', null, ['deleted_ids' => $ids]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '등급 일괄 삭제', null);

            // 권한 로그 기록
            $this->logPermissionAction('bulk_delete', 'level', null, 'success');

            return response()->json([
                'success' => true,
                'message' => count($ids) . '개의 등급이 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logPermissionAction('bulk_delete', 'level', null, 'failed', '유효성 검사 실패');
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logPermissionAction('bulk_delete', 'level', null, 'failed', '일괄 삭제 중 오류 발생');
            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 권한 토글
     */
    public function togglePermission(Request $request, $id): JsonResponse
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 수정 권한 체크
        if (!$this->checkPermission('update')) {
            $this->logPermissionAction('toggle_permission', 'level', $id, 'denied', '등급 권한 토글 권한이 없습니다.');
            return response()->json([
                'success' => false,
                'message' => '등급 수정 권한이 없습니다.'
            ], 403);
        }
        */

        try {
            $level = AdminLevel::findOrFail($id);
            $permission = $request->input('permission');
            
            if (!in_array($permission, ['can_create', 'can_read', 'can_update', 'can_delete'])) {
                $this->logPermissionAction('toggle_permission', 'level', $id, 'failed', '유효하지 않은 권한입니다.');
                return response()->json([
                    'success' => false,
                    'message' => '유효하지 않은 권한입니다.'
                ], 400);
            }

            $level->update([$permission => !$level->$permission]);

            // 권한 로그 기록
            $this->logPermissionAction('toggle_permission', 'level', $id, 'success');

            return response()->json([
                'success' => true,
                'message' => '권한이 변경되었습니다.',
                'permission' => $permission,
                'value' => $level->$permission
            ]);

        } catch (\Exception $e) {
            $this->logPermissionAction('toggle_permission', 'level', $id, 'failed', '권한 변경 중 오류 발생');
            return response()->json([
                'success' => false,
                'message' => '권한 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 정렬 순서 업데이트
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 수정 권한 체크
        if (!$this->checkPermission('update')) {
            $this->logPermissionAction('update_order', 'level', null, 'denied', '등급 정렬 순서 업데이트 권한이 없습니다.');
            abort(403, '등급 수정 권한이 없습니다.');
        }
        */

        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:admin_levels,id'
        ]);

        $orders = $request->input('orders');
        
        foreach ($orders as $index => $id) {
            // sort_order 컬럼이 존재하는지 확인
            if (Schema::hasColumn('admin_levels', 'sort_order')) {
                AdminLevel::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        }

        // 권한 로그 기록
        $this->logPermissionAction('update_order', 'level', null, 'success');

        return redirect()->route('admin.admin.levels.index')
            ->with('success', '정렬 순서가 업데이트되었습니다.');
    }

    /**
     * 통계 정보
     * AdminUser와의 연관성을 반영한 통계
     */
    public function stats()
    {
        // 임시: 권한 체크 우회 (디버깅용)
        // TODO: 실제 권한 체크로 복원
        
        /*
        // 조회 권한 체크
        if (!$this->checkPermission('read')) {
            $this->logPermissionAction('stats', 'level', null, 'denied', '등급 통계 조회 권한이 없습니다.');
            abort(403, '등급 조회 권한이 없습니다.');
        }
        */

        $stats = [
            'total' => AdminLevel::count(),
            'with_users' => AdminLevel::whereIn('code', AdminUser::distinct('type')->pluck('type'))->count(),
            'without_users' => AdminLevel::whereNotIn('code', AdminUser::distinct('type')->pluck('type'))->count(),
            'total_users' => AdminUser::count(),
            'level_distribution' => AdminLevel::all()->map(function ($level) {
                return [
                    'name' => $level->name,
                    'code' => $level->code,
                    'user_count' => AdminUser::where('type', $level->code)->count(),
                    'color' => $level->badge_color
                ];
            })
        ];

        // 권한 로그 기록
        $this->logPermissionAction('stats', 'level', null, 'success');

        return response()->json($stats);
    }

    /**
     * 기존 데이터 조회
     */
    protected function getOldData($id)
    {
        return AdminLevel::find($id);
    }

    /**
     * 등급별 사용자 통계 조회
     * AdminUser와의 연관성을 반영한 상세 통계
     */
    public function getUserStatistics()
    {
        $levels = AdminLevel::orderBy('sort_order')->get();
        $statistics = [];
        
        foreach ($levels as $level) {
            $users = AdminUser::where('type', $level->code)->get();
            
            $statistics[] = [
                'level' => $level,
                'user_count' => $users->count(),
                'active_users' => $users->where('status', 'active')->count(),
                'inactive_users' => $users->where('status', 'inactive')->count(),
                'suspended_users' => $users->where('status', 'suspended')->count(),
                'recent_users' => $users->where('created_at', '>=', now()->subDays(30))->count(),
            ];
        }
        
        return $statistics;
    }
} 