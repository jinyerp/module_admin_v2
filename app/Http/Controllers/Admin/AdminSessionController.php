<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Jiny\Admin\App\Models\AdminActivityLog;
use Jiny\Admin\App\Models\AdminAuditLog;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminSession;
use Illuminate\Support\Facades\Auth;

/**
 * AdminSessionController
 *
 * 관리자 세션 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminSession.admin_user_id 필드가 AdminUser.id와 연결
 * - 세션별 관리자 정보 표시 및 통계
 * - 보안 모니터링 및 세션 관리
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSession.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 세션 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminSessionTest.php
 * ```
 */
class AdminSessionController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.sessions.index';
    public $createPath = 'jiny-admin::admin.sessions.create';
    public $editPath = 'jiny-admin::admin.sessions.edit';
    public $showPath = 'jiny-admin::admin.sessions.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['search', 'type', 'active', 'date_from', 'date_to'];
    protected $validFilters = ['search', 'type', 'active', 'date_from', 'date_to', 'ip_address', 'last_activity'];
    protected $sortableColumns = ['session_id', 'admin_name', 'admin_email', 'admin_type', 'ip_address', 'last_activity', 'login_at'];

    private $route = 'admin.admin.sessions.';
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
        return 'admin_sessions';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_sessions';
    }



    /**
     * 세션 목록 조회
     * index() 에서 템플릿 메소드 호출
     * AdminSession 모델의 스코프와 관계를 활용하여 효율적으로 조회
     */
    protected function _index(Request $request): View
    {
        // AdminSession 모델을 사용하여 쿼리 빌더 시작
        $query = AdminSession::with('adminUser');

        // 검색 필터링
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('adminUser', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // 관리자 타입 필터링
        if ($request->filled('filter_type')) {
            $type = $request->filter_type;
            $query->whereHas('adminUser', function ($userQuery) use ($type) {
                $userQuery->where('type', $type);
            });
        }

        // 활성 상태 필터링
        if ($request->filled('filter_active')) {
            $active = $request->filter_active;
            if ($active === 'active') {
                $query->active();
            } else {
                $query->inactive();
            }
        }

        // 날짜 범위 필터링
        if ($request->filled('filter_date_from') && $request->filled('filter_date_to')) {
            $dateFrom = $request->filter_date_from;
            $dateTo = $request->filter_date_to;
            $query->byDateRange($dateFrom, $dateTo);
        }

        // 정렬
        $sortBy = $request->get('sort', 'last_activity');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            if ($sortBy === 'admin_name' || $sortBy === 'admin_email' || $sortBy === 'admin_type') {
                // AdminUser 관계를 통한 정렬
                $query->join('admin_users', 'admin_sessions.admin_user_id', '=', 'admin_users.id');
                if ($sortBy === 'admin_name') {
                    $query->orderBy('admin_users.name', $sortOrder);
                } elseif ($sortBy === 'admin_email') {
                    $query->orderBy('admin_users.email', $sortOrder);
                } elseif ($sortBy === 'admin_type') {
                    $query->orderBy('admin_users.type', $sortOrder);
                }
            } else {
                // AdminSession 테이블 직접 정렬
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('last_activity', 'desc');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $rows = $query->paginate($perPage);

        // 필터 데이터 전달
        $filters = $request->only($this->filterable);

        // Activity Log 기록
        $this->logActivity('list', '세션 목록 조회', null, $filters);

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => $this->route,
        ]);
    }

    /**
     * 세션 생성 폼
     */
    protected function _create(Request $request): View
    {
        // Activity Log 기록
        $this->logActivity('create', '세션 생성 폼 접근', null, []);

        return view($this->createPath, [
            'route' => $this->route,
        ]);
    }

    /**
     * 세션 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'admin_user_id' => 'required|integer|exists:admin_users,id',
                'session_id' => 'required|string|max:255|unique:admin_sessions,session_id',
                'ip_address' => 'required|ip',
                'user_agent' => 'nullable|string|max:500',
                'login_at' => 'required|date',
                'last_activity' => 'nullable|date',
            ], [
                'admin_user_id.required' => '관리자 ID를 입력해주세요.',
                'admin_user_id.exists' => '존재하지 않는 관리자입니다.',
                'session_id.required' => '세션 ID를 입력해주세요.',
                'session_id.unique' => '이미 존재하는 세션 ID입니다.',
                'ip_address.required' => 'IP 주소를 입력해주세요.',
                'ip_address.ip' => '유효하지 않은 IP 주소입니다.',
                'user_agent.max' => '사용자 에이전트는 500자를 초과할 수 없습니다.',
                'login_at.required' => '로그인 시간을 입력해주세요.',
                'login_at.date' => '유효하지 않은 날짜 형식입니다.',
                'last_activity.date' => '유효하지 않은 날짜 형식입니다.',
            ]);

            // 세션 생성 (실제 구현에서는 세션 테이블에 저장)
            $session = $this->createSessionInStorage($validated);

            // Activity Log 기록
            $this->logActivity('create', '세션 생성', $session->id ?? null, $validated);

            return response()->json([
                'success' => true,
                'message' => '세션이 성공적으로 생성되었습니다.',
                'data' => [
                    'session_id' => $validated['session_id'],
                    'admin_user_id' => $validated['admin_user_id']
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
                'message' => '세션 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 세션 상세 보기
     * 해당 세션의 관리자 정보도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        $session = $this->getSessionFromStorage($id);
        
        if (!$session) {
            abort(404, '세션을 찾을 수 없습니다.');
        }

        // AdminUser 정보 조회 (연관성 반영)
        $adminUser = AdminUser::find($session->admin_user_id);
        $session->adminUser = $adminUser;

        // Activity Log 기록
        $this->logActivity('read', '세션 상세 조회', $id, ['session_id' => $id]);

        return view($this->showPath, [
            'session' => $session,
            'route' => $this->route,
        ]);
    }

    /**
     * 세션 수정 폼
     */
    protected function _edit(Request $request, $id): View
    {
        $session = $this->getSessionFromStorage($id);
        
        if (!$session) {
            abort(404, '세션을 찾을 수 없습니다.');
        }

        // Activity Log 기록
        $this->logActivity('update', '세션 수정 폼 접근', $id, ['session_id' => $id]);

        return view($this->editPath, [
            'session' => $session,
            'route' => $this->route,
        ]);
    }

    /**
     * 세션 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        try {
            $session = $this->getSessionFromStorage($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => '세션을 찾을 수 없습니다.'
                ], 404);
            }

            // 수정 전 데이터 가져오기 (Audit Log용)
            $oldData = (array) $session;

            $validated = $request->validate([
                'ip_address' => 'required|ip',
                'user_agent' => 'nullable|string|max:500',
                'last_activity' => 'nullable|date',
            ], [
                'ip_address.required' => 'IP 주소를 입력해주세요.',
                'ip_address.ip' => '유효하지 않은 IP 주소입니다.',
                'user_agent.max' => '사용자 에이전트는 500자를 초과할 수 없습니다.',
                'last_activity.date' => '유효하지 않은 날짜 형식입니다.',
            ]);

            // 세션 수정 (실제 구현에서는 세션 테이블에 업데이트)
            $this->updateSessionInStorage($id, $validated);

            // Activity Log 기록
            $this->logActivity('update', '세션 수정', $id, $validated);
            
            // Audit Log 기록
            $this->logAudit('update', $oldData, $validated, '세션 수정', $id);

            return response()->json([
                'success' => true,
                'message' => '세션이 성공적으로 수정되었습니다.',
                'data' => [
                    'session_id' => $id,
                    'updated_fields' => array_keys($validated)
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
                'message' => '세션 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 세션 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        
        try {
            $session = $this->getSessionFromStorage($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => '세션을 찾을 수 없습니다.'
                ], 404);
            }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = (array) $session;

            // 세션 삭제 (실제 구현에서는 세션 테이블에서 삭제)
            $this->deleteSessionFromStorage($id);

            // Activity Log 기록
            $this->logActivity('delete', '세션 삭제', $id, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '세션 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '세션이 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '세션 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 세션 확인
     */
    public function confirm($id)
    {
        $session = $this->getSessionFromStorage($id);
        
        if (!$session) {
            abort(404, '세션을 찾을 수 없습니다.');
        }

        // AdminUser 정보 조회 (연관성 반영)
        $adminUser = AdminUser::find($session->admin_user_id);
        $session->adminUser = $adminUser;

        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        return view('jiny-admin::admin.sessions.form_delete', [
            'session' => $session,
            'title' => '세션 삭제',
            'randomKey' => $randomKey
        ]);
    }

    /**
     * 세션 삭제
     */
    public function destroy($id)
    {
        $session = $this->getSessionFromStorage($id);
        
        if (!$session) {
            return redirect()->route('admin.admin.sessions.index')
                ->with('error', '세션을 찾을 수 없습니다.');
        }

        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = (array) $session;

        // 세션 삭제 (실제 구현에서는 세션 테이블에서 삭제)
        $this->deleteSessionFromStorage($id);

        // Activity Log 기록
        $this->logActivity('delete', '세션 삭제', $id, ['deleted_id' => $id]);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '세션 삭제', null);

        return redirect()->route('admin.admin.sessions.index')
            ->with('success', '세션이 성공적으로 삭제되었습니다.');
    }

    /**
     * 세션 새로고침
     */
    public function refresh($id)
    {
        $session = $this->getSessionFromStorage($id);
        
        if (!$session) {
            return redirect()->route('admin.admin.sessions.index')
                ->with('error', '세션을 찾을 수 없습니다.');
        }

        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = (array) $session;

        // 세션 새로고침 (실제 구현에서는 세션 테이블에 업데이트)
        $this->refreshSessionInStorage($id);

        // Activity Log 기록
        $this->logActivity('update', '세션 새로고침', $id, ['action' => 'refresh']);
        
        // Audit Log 기록
        $this->logAudit('update', $oldData, ['last_activity' => now()], '세션 새로고침', $id);

        return redirect()->route('admin.admin.sessions.index')
            ->with('success', '세션이 새로고침되었습니다.');
    }

    /**
     * 세션 액션 로깅
     */
    protected function logSessionAction($action, $sessionId, $desc)
    {
        try {
            $adminId = Auth::guard('admin')->id();
            if (!$adminId) return;

            AdminActivityLog::create([
                'admin_user_id' => $adminId,
                'action' => $action,
                'resource_type' => 'session',
                'resource_id' => $sessionId,
                'description' => $desc,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('세션 액션 로깅 실패', [
                'error' => $e->getMessage(),
                'action' => $action,
                'session_id' => $sessionId,
            ]);
        }
    }

    /**
     * 사용자별 세션 중복 제거
     */
    private function deduplicateSessionsByUser(array $sessions): array
    {
        $userSessions = [];
        
        foreach ($sessions as $session) {
            $userId = $session->admin_user_id;
            
            if (!isset($userSessions[$userId])) {
                $userSessions[$userId] = $session;
            } else {
                // 더 최근 활동이 있는 세션 선택
                if ($session->last_activity > $userSessions[$userId]->last_activity) {
                    $userSessions[$userId] = $session;
                }
            }
        }
        
        return array_values($userSessions);
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'string'
            ]);

            $ids = $request->input('ids');
            $deletedCount = 0;

            foreach ($ids as $id) {
                $session = $this->getSessionFromStorage($id);
                if ($session) {
                    // 삭제 전 데이터 가져오기 (Audit Log용)
                    $oldData = (array) $session;

                    // 세션 삭제
                    $this->deleteSessionFromStorage($id);
                    $deletedCount++;

                    // Audit Log 기록
                    $this->logAudit('delete', $oldData, null, '세션 일괄 삭제', null);
                }
            }

            // Activity Log 기록
            $this->logActivity('delete', '세션 일괄 삭제', null, [
                'deleted_ids' => $ids,
                'deleted_count' => $deletedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => $deletedCount . '개의 세션이 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 한국 시간 포맷
     */
    private function formatKoreanTime($carbon): string
    {
        return $carbon->format('Y년 m월 d일 H시 i분 s초');
    }

    // 실제 구현에서는 다음 메서드들을 구현해야 합니다:

    /**
     * 스토리지에서 세션 목록 조회
     * AdminSession 모델을 사용하여 실제 데이터베이스에서 조회
     */
    private function getSessionsFromStorage()
    {
        return AdminSession::with('adminUser')
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * 스토리지에서 특정 세션 조회
     * AdminSession 모델을 사용하여 실제 데이터베이스에서 조회
     */
    private function getSessionFromStorage($id)
    {
        return AdminSession::with('adminUser')->where('session_id', $id)->first();
    }

    /**
     * 스토리지에 세션 생성
     * AdminSession 모델을 사용하여 실제 데이터베이스에 저장
     */
    private function createSessionInStorage($data)
    {
        return AdminSession::create($data);
    }

    /**
     * 스토리지의 세션 수정
     * AdminSession 모델을 사용하여 실제 데이터베이스에 업데이트
     */
    private function updateSessionInStorage($id, $data)
    {
        $session = AdminSession::where('session_id', $id)->first();
        if ($session) {
            return $session->update($data);
        }
        return false;
    }

    /**
     * 스토리지에서 세션 삭제
     * AdminSession 모델을 사용하여 실제 데이터베이스에서 삭제
     */
    private function deleteSessionFromStorage($id)
    {
        $session = AdminSession::where('session_id', $id)->first();
        if ($session) {
            return $session->delete();
        }
        return false;
    }

    /**
     * 스토리지의 세션 새로고침
     * AdminSession 모델을 사용하여 실제 데이터베이스에 업데이트
     */
    private function refreshSessionInStorage($id)
    {
        $session = AdminSession::where('session_id', $id)->first();
        if ($session) {
            return $session->refresh();
        }
        return false;
    }
} 