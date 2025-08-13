<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Services\TwoFactorService;

/**
 * AdminUser2FAController
 *
 * 관리자 사용자 2FA 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminUser2FAController.admin_user_id 필드가 AdminUser.id와 연결
 * - 2FA 설정 및 관리 기능 제공
 * - 백업 코드 생성 및 관리
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminUser2FA.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 사용자 2FA 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminUser2FATest.php
 * ```
 */
class AdminUser2FAController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.users.2fa.index';
    public $createPath = 'jiny-admin::admin.users.2fa.setup';
    public $editPath = 'jiny-admin::admin.users.2fa.manage';
    public $showPath = 'jiny-admin::admin.users.2fa.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['search', 'status', 'type', 'date_from', 'date_to'];
    protected $validFilters = ['search', 'status', 'type', 'date_from', 'date_to', 'is_2fa_enabled', 'last_2fa_used'];
    protected $sortableColumns = ['id', 'name', 'email', 'type', 'is_2fa_enabled', 'last_2fa_used', 'created_at'];

    private $twoFactorService;

    /**
     * 생성자
     * TwoFactorService 의존성 주입
     */
    public function __construct(TwoFactorService $twoFactorService)
    {
        parent::__construct();
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * 테이블 이름 반환
     * Activity Log 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_users';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_users_2fa';
    }

    /**
     * 2FA 설정 페이지
     * index() 에서 템플릿 메소드 호출
     */
    protected function _index(Request $request): View
    {
        $query = AdminUser::query();

        // 2FA 상태별 필터링
        if ($request->filled('filter_status')) {
            $status = $request->filter_status;
            if ($status === 'enabled') {
                $query->where('is_2fa_enabled', true);
            } elseif ($status === 'disabled') {
                $query->where('is_2fa_enabled', false);
            }
        }

        // 검색 필터링
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 날짜 필터링
        $query = $this->applyDateFilter($query, $request, 'created_at');

        // 정렬
        $sortBy = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $rows = $query->paginate($request->get('per_page', 15));

        // 필터 데이터 전달
        $filters = $request->only($this->filterable);

        // Activity Log 기록
        $this->logActivity('list', '2FA 사용자 목록 조회', null, $filters);

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.admin.users.2fa.',
        ]);
    }

    /**
     * 2FA 설정 폼
     */
    protected function _create(Request $request): View
    {
        $userId = $request->route('id');
        $user = AdminUser::findOrFail($userId);

        // Activity Log 기록
        $this->logActivity('create', '2FA 설정 폼 접근', $userId, ['user_id' => $userId]);

        return view($this->createPath, [
            'user' => $user,
            'route' => 'admin.admin.users.2fa.',
        ]);
    }

    /**
     * 2FA 설정 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        $userId = $request->route('id');
        $user = AdminUser::findOrFail($userId);

        try {
            $validated = $request->validate([
                'secret' => 'required|string',
                'backup_codes' => 'required|array',
                'backup_codes.*' => 'string'
            ]);

            // 2FA 설정 저장
            $user->update([
                'two_factor_secret' => $validated['secret'],
                'two_factor_backup_codes' => json_encode($validated['backup_codes']),
                'is_2fa_enabled' => true,
                'two_factor_enabled_at' => now()
            ]);

            // Activity Log 기록
            $this->logActivity('create', '2FA 설정 완료', $userId, [
                'user_id' => $userId,
                'has_backup_codes' => count($validated['backup_codes'])
            ]);

            return response()->json([
                'success' => true,
                'message' => '2FA가 성공적으로 설정되었습니다.',
                'data' => [
                    'user_id' => $userId,
                    'backup_codes' => $validated['backup_codes']
                ]
            ], 201);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('create', '2FA 설정 실패', $userId, [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '2FA 설정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2FA 관리 페이지
     */
    protected function _show(Request $request, $id): View
    {
        $user = AdminUser::findOrFail($id);

        // 2FA 상태 정보
        $twoFactorInfo = [
            'is_enabled' => $user->is_2fa_enabled ?? false,
            'enabled_at' => $user->two_factor_enabled_at,
            'last_used' => $user->last_2fa_used,
            'backup_codes_count' => $user->getBackupCodesCount(),
            'has_secret' => !empty($user->two_factor_secret)
        ];

        // Activity Log 기록
        $this->logActivity('read', '2FA 상태 조회', $id, ['user_id' => $id]);

        return view($this->showPath, [
            'user' => $user,
            'twoFactorInfo' => $twoFactorInfo,
            'route' => 'admin.admin.users.2fa.',
        ]);
    }

    /**
     * 2FA 관리 폼
     */
    protected function _edit(Request $request, $id): View
    {
        $user = AdminUser::findOrFail($id);

        // Activity Log 기록
        $this->logActivity('update', '2FA 관리 폼 접근', $id, ['user_id' => $id]);

        return view($this->editPath, [
            'user' => $user,
            'route' => 'admin.admin.users.2fa.',
        ]);
    }

    /**
     * 2FA 설정 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        $user = AdminUser::findOrFail($id);

        try {
            $validated = $request->validate([
                'action' => 'required|in:enable,disable,regenerate_backup'
            ]);

            $oldData = [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'backup_codes_count' => $user->getBackupCodesCount()
            ];

            switch ($validated['action']) {
                case 'enable':
                    $this->twoFactorService->enable2FA($user);
                    $message = '2FA가 활성화되었습니다.';
                    break;
                case 'disable':
                    $this->twoFactorService->disable2FA($user);
                    $message = '2FA가 비활성화되었습니다.';
                    break;
                case 'regenerate_backup':
                    $backupCodes = $this->twoFactorService->regenerateBackupCodes($user);
                    $message = '백업 코드가 재생성되었습니다.';
                    break;
            }

            // Activity Log 기록
            $this->logActivity('update', '2FA 설정 수정', $id, [
                'user_id' => $id,
                'action' => $validated['action']
            ]);

            // Audit Log 기록
            $this->logAudit('update', $oldData, [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'backup_codes_count' => $user->getBackupCodesCount()
            ], '2FA 설정 수정', $id);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user_id' => $id,
                    'action' => $validated['action']
                ]
            ]);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('update', '2FA 설정 수정 실패', $id, [
                'user_id' => $id,
                'action' => $validated['action'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '2FA 설정 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2FA 설정 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        $user = AdminUser::findOrFail($id);

        try {
            $oldData = [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'two_factor_secret' => $user->two_factor_secret,
                'backup_codes_count' => $user->getBackupCodesCount()
            ];

            // 2FA 설정 완전 제거
            $user->update([
                'two_factor_secret' => null,
                'two_factor_backup_codes' => null,
                'is_2fa_enabled' => false,
                'two_factor_enabled_at' => null,
                'last_2fa_used' => null
            ]);

            // Activity Log 기록
            $this->logActivity('delete', '2FA 설정 삭제', $id, ['user_id' => $id]);

            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '2FA 설정 삭제', $id);

            return response()->json([
                'success' => true,
                'message' => '2FA 설정이 완전히 제거되었습니다.'
            ]);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('delete', '2FA 설정 삭제 실패', $id, [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '2FA 설정 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2FA 활성화
     */
    public function enable(Request $request, $id): JsonResponse
    {
        $user = AdminUser::findOrFail($id);

        try {
            $oldData = [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'two_factor_enabled_at' => $user->two_factor_enabled_at
            ];

            $this->twoFactorService->enable2FA($user);

            // Activity Log 기록
            $this->logActivity('update', '2FA 활성화', $id, ['user_id' => $id]);

            // Audit Log 기록
            $this->logAudit('update', $oldData, [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'two_factor_enabled_at' => $user->two_factor_enabled_at
            ], '2FA 활성화', $id);

            return response()->json([
                'success' => true,
                'message' => '2FA가 활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('update', '2FA 활성화 실패', $id, [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '2FA 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2FA 비활성화
     */
    public function disable(Request $request, $id): JsonResponse
    {
        $user = AdminUser::findOrFail($id);

        try {
            $oldData = [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'two_factor_enabled_at' => $user->two_factor_enabled_at
            ];

            $this->twoFactorService->disable2FA($user);

            // Activity Log 기록
            $this->logActivity('update', '2FA 비활성화', $id, ['user_id' => $id]);

            // Audit Log 기록
            $this->logAudit('update', $oldData, [
                'is_2fa_enabled' => $user->is_2fa_enabled,
                'two_factor_enabled_at' => $user->two_factor_enabled_at
            ], '2FA 비활성화', $id);

            return response()->json([
                'success' => true,
                'message' => '2FA가 비활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('update', '2FA 비활성화 실패', $id, [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '2FA 비활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 백업 코드 재생성
     */
    public function regenerateBackupCodes(AdminUser $user): JsonResponse
    {
        try {
            $oldData = [
                'backup_codes_count' => $user->getBackupCodesCount()
            ];

            $backupCodes = $this->twoFactorService->regenerateBackupCodes($user);

            // Activity Log 기록
            $this->logActivity('update', '백업 코드 재생성', $user->id, [
                'user_id' => $user->id,
                'new_codes_count' => count($backupCodes)
            ]);

            // Audit Log 기록
            $this->logAudit('update', $oldData, [
                'backup_codes_count' => count($backupCodes)
            ], '백업 코드 재생성', $user->id);

            return response()->json([
                'success' => true,
                'message' => '백업 코드가 재생성되었습니다.',
                'data' => [
                    'backup_codes' => $backupCodes
                ]
            ]);

        } catch (\Exception $e) {
            // Activity Log 기록
            $this->logActivity('update', '백업 코드 재생성 실패', $user->id, [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '백업 코드 재생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자 관리 권한 확인
     */
    private function canManageUser($currentAdmin, $targetUser): bool
    {
        // Super 관리자는 모든 사용자 관리 가능
        if ($currentAdmin->type === 'super') {
            return true;
        }

        // 자신의 2FA 설정은 관리 가능
        if ($currentAdmin->id === $targetUser->id) {
            return true;
        }

        // 하위 등급 사용자만 관리 가능
        $currentLevel = $currentAdmin->getLevel();
        $targetLevel = $targetUser->getLevel();

        if ($currentLevel && $targetLevel) {
            return $currentLevel->sort_order < $targetLevel->sort_order;
        }

        return false;
    }
} 