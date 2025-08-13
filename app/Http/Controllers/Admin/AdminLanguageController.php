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

use Jiny\Admin\App\Models\AdminLanguage;
use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminLanguageController
 *
 * 관리자 언어 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminUser.language_id 필드가 AdminLanguage.id와 연결
 * - 언어별 사용자 수 계산 및 표시
 * - 다국어 지원 및 지역화 관리
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminLanguage.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 언어 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminLanguageTest.php
 * ```
 */
class AdminLanguageController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.languages.index';
    public $createPath = 'jiny-admin::admin.languages.create';
    public $editPath = 'jiny-admin::admin.languages.edit';
    public $showPath = 'jiny-admin::admin.languages.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['name', 'code', 'locale', 'is_active', 'is_default', 'sort_order'];
    protected $validFilters = ['name', 'code', 'locale', 'is_active', 'is_default', 'sort_order'];
    protected $sortableColumns = ['id', 'name', 'code', 'locale', 'is_active', 'is_default', 'sort_order', 'created_at', 'updated_at'];

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
        return 'admin_languages';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_languages';
    }

    /**
     * 언어별 사용자 수 계산
     * AdminUser와 AdminLanguage의 연관성을 반영
     */
    private function calculateUserCountsByLanguage()
    {
        $languages = AdminLanguage::all();
        $userCounts = [];
        
        foreach ($languages as $language) {
            // AdminUser.language_id 필드가 AdminLanguage.id와 연결
            $userCount = AdminUser::where('language_id', $language->id)->count();
            $userCounts[$language->id] = $userCount;
        }
        
        return $userCounts;
    }

    /**
     * 언어 목록 조회
     * index() 에서 템플릿 메소드 호출
     * AdminUser와의 연관성을 고려하여 사용자 수 표시
     */
    protected function _index(Request $request): View
    {
        $query = AdminLanguage::query();

        // 각 언어별 사용자 수 계산 (AdminUser와의 연관성 반영)
        $languages = $query->get();
        $languagesWithUserCount = $languages->map(function ($language) {
            // AdminUser.language_id 필드가 AdminLanguage.id와 연결
            $language->users_count = AdminUser::where('language_id', $language->id)->count();
            return $language;
        });

        // 필터링
        if ($request->filled('filter_name')) {
            $languagesWithUserCount = $languagesWithUserCount->filter(function ($language) use ($request) {
                return str_contains(strtolower($language->name), strtolower($request->filter_name));
            });
        }
        if ($request->filled('filter_code')) {
            $languagesWithUserCount = $languagesWithUserCount->filter(function ($language) use ($request) {
                return str_contains(strtolower($language->code), strtolower($request->filter_code));
            });
        }
        if ($request->filled('filter_locale')) {
            $languagesWithUserCount = $languagesWithUserCount->filter(function ($language) use ($request) {
                return str_contains(strtolower($language->locale), strtolower($request->filter_locale));
            });
        }
        if ($request->filled('filter_is_active')) {
            $languagesWithUserCount = $languagesWithUserCount->filter(function ($language) use ($request) {
                return $language->is_active == $request->filter_is_active;
            });
        }
        if ($request->filled('filter_is_default')) {
            $languagesWithUserCount = $languagesWithUserCount->filter(function ($language) use ($request) {
                return $language->is_default == $request->filter_is_default;
            });
        }

        // 정렬
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            if ($sortOrder === 'asc') {
                $languagesWithUserCount = $languagesWithUserCount->sortBy($sortBy);
            } else {
                $languagesWithUserCount = $languagesWithUserCount->sortByDesc($sortBy);
            }
        } else {
            $languagesWithUserCount = $languagesWithUserCount->sortBy('sort_order');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);
        $total = $languagesWithUserCount->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $languagesWithUserCount->slice($offset, $perPage);
        
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_name', 'filter_code', 'filter_locale', 'filter_is_active', 'filter_is_default'
        ]);

        // Activity Log 기록
        $this->logActivity('list', '언어 목록 조회', null, $filters);

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.admin.languages.',
        ]);
    }

    /**
     * 언어 생성 폼
     */
    protected function _create(Request $request): View
    {
        // Activity Log 기록
        $this->logActivity('create', '언어 생성 폼 접근', null, []);

        return view($this->createPath, [
            'route' => 'admin.admin.languages.',
        ]);
    }

    /**
     * 언어 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_languages,code',
                'locale' => 'required|string|max:10|unique:admin_languages,locale',
                'is_active' => 'boolean',
                'is_default' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '언어명을 입력해주세요.',
                'name.max' => '언어명은 255자를 초과할 수 없습니다.',
                'code.required' => '언어 코드를 입력해주세요.',
                'code.max' => '언어 코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 언어 코드입니다.',
                'locale.required' => '로케일을 입력해주세요.',
                'locale.max' => '로케일은 10자를 초과할 수 없습니다.',
                'locale.unique' => '이미 존재하는 로케일입니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');

            // 기본 언어로 설정하는 경우 다른 언어의 기본 설정 해제
            if ($validated['is_default']) {
                AdminLanguage::where('is_default', true)->update(['is_default' => false]);
            }

            $language = AdminLanguage::create($validated);

            // Activity Log 기록
            $this->logActivity('create', '언어 생성', $language->id, $validated);

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 등록되었습니다.',
                'data' => [
                    'id' => $language->id,
                    'name' => $language->name,
                    'code' => $language->code
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
                'message' => '언어 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 언어 상세 보기
     * 해당 언어를 사용하는 AdminUser 목록도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        $language = AdminLanguage::findOrFail($id);
        
        // 해당 언어를 사용하는 AdminUser 목록 조회 (연관성 반영)
        $usersWithThisLanguage = AdminUser::where('language_id', $language->id)->get();

        // Activity Log 기록
        $this->logActivity('read', '언어 상세 조회', $id, ['language_id' => $id]);

        return view($this->showPath, [
            'language' => $language,
            'users' => $usersWithThisLanguage,
            'route' => 'admin.admin.languages.',
        ]);
    }

    /**
     * 언어 수정 폼
     */
    protected function _edit(Request $request, $id): View
    {
        $language = AdminLanguage::findOrFail($id);
        
        // 해당 언어를 사용하는 AdminUser 수 확인
        $userCount = AdminUser::where('language_id', $language->id)->count();

        // Activity Log 기록
        $this->logActivity('update', '언어 수정 폼 접근', $id, ['language_id' => $id]);

        return view($this->editPath, [
            'language' => $language,
            'userCount' => $userCount,
            'route' => 'admin.admin.languages.',
        ]);
    }

    /**
     * 언어 수정
     * AdminUser와의 연관성을 고려하여 안전하게 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        try {
            $language = AdminLanguage::findOrFail($id);

            // 수정 전 데이터 가져오기 (Audit Log용)
            $oldData = $language->toArray();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_languages,code,' . $id,
                'locale' => 'required|string|max:10|unique:admin_languages,locale,' . $id,
                'is_active' => 'boolean',
                'is_default' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '언어명을 입력해주세요.',
                'name.max' => '언어명은 255자를 초과할 수 없습니다.',
                'code.required' => '언어 코드를 입력해주세요.',
                'code.max' => '언어 코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 언어 코드입니다.',
                'locale.required' => '로케일을 입력해주세요.',
                'locale.max' => '로케일은 10자를 초과할 수 없습니다.',
                'locale.unique' => '이미 존재하는 로케일입니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');

            // 기본 언어로 설정하는 경우 다른 언어의 기본 설정 해제
            if ($validated['is_default'] && !$language->is_default) {
                AdminLanguage::where('is_default', true)->update(['is_default' => false]);
            }

            $language->update($validated);

            // Activity Log 기록
            $this->logActivity('update', '언어 수정', $language->id, $validated);
            
            // Audit Log 기록
            $this->logAudit('update', $oldData, $validated, '언어 수정', $language->id);

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $language->id,
                    'name' => $language->name,
                    'code' => $language->code
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
                'message' => '언어 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 언어 삭제
     * AdminUser와의 연관성을 확인하여 안전하게 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        
        try {
            $language = AdminLanguage::findOrFail($id);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $language->toArray();

            // 사용 중인 언어인지 확인 (AdminUser.language_id 필드와 AdminLanguage.id 연결)
            $usersUsingLanguage = AdminUser::where('language_id', $language->id)->count();
            if ($usersUsingLanguage > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '사용 중인 언어는 삭제할 수 없습니다. (사용자 수: ' . $usersUsingLanguage . '명)'
                ], 400);
            }

            // 기본 언어인지 확인
            if ($language->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 언어는 삭제할 수 없습니다.'
                ], 400);
            }

            $language->delete();

            // Activity Log 기록
            $this->logActivity('delete', '언어 삭제', $id, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '언어 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '언어가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '언어 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼 반환
     * 해당 언어를 사용하는 AdminUser 정보도 함께 표시
     */
    public function deleteConfirm(Request $request, $id)
    {
        $language = AdminLanguage::findOrFail($id);
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        // 해당 언어를 사용하는 AdminUser 목록 조회
        $usersWithThisLanguage = AdminUser::where('language_id', $language->id)->get();
        
        return view('jiny-admin::admin.languages.form_delete', [
            'language' => $language,
            'users' => $usersWithThisLanguage,
            'title' => '언어 삭제',
            'randomKey' => $randomKey
        ]);
    }

    /**
     * 일괄 삭제
     * AdminUser와의 연관성을 확인하여 안전하게 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:admin_languages,id'
            ]);

            $ids = $request->input('ids');
            
            // 사용 중인 언어가 포함되어 있는지 확인 (AdminUser.language_id 필드와 AdminLanguage.id 연결)
            $languages = AdminLanguage::whereIn('id', $ids)->get();
            $usedLanguages = [];
            
            foreach ($languages as $language) {
                $userCount = AdminUser::where('language_id', $language->id)->count();
                if ($userCount > 0) {
                    $usedLanguages[] = $language->name . ' (' . $userCount . '명 사용 중)';
                }
            }
            
            if (!empty($usedLanguages)) {
                return response()->json([
                    'success' => false,
                    'message' => '다음 언어들은 사용 중이므로 삭제할 수 없습니다: ' . implode(', ', $usedLanguages)
                ], 400);
            }

            // 기본 언어가 포함되어 있는지 확인
            $defaultLanguages = $languages->where('is_default', true);
            if ($defaultLanguages->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 언어는 삭제할 수 없습니다.'
                ], 400);
            }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = AdminLanguage::whereIn('id', $ids)->get()->toArray();

            AdminLanguage::whereIn('id', $ids)->delete();

            // Activity Log 기록
            $this->logActivity('delete', '언어 일괄 삭제', null, ['deleted_ids' => $ids]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '언어 일괄 삭제', null);

            return response()->json([
                'success' => true,
                'message' => count($ids) . '개의 언어가 성공적으로 삭제되었습니다.'
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
     * 언어 활성화/비활성화 토글
     */
    public function toggleActive(AdminLanguage $language): RedirectResponse
    {
        $oldData = ['is_active' => $language->is_active];
        
        $language->update(['is_active' => !$language->is_active]);
        
        // Activity Log 기록
        $this->logActivity('update', '언어 활성화 상태 변경', $language->id, [
            'language_id' => $language->id,
            'new_status' => $language->is_active
        ]);

        return redirect()->back()->with('success', '언어 상태가 변경되었습니다.');
    }

    /**
     * 정렬 순서 업데이트
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:admin_languages,id'
        ]);

        $orders = $request->input('orders');
        
        foreach ($orders as $index => $id) {
            // sort_order 컬럼이 존재하는지 확인
            if (Schema::hasColumn('admin_languages', 'sort_order')) {
                AdminLanguage::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        }

        // Activity Log 기록
        $this->logActivity('update', '언어 정렬 순서 업데이트', null, ['orders' => $orders]);

        return redirect()->route('admin.admin.languages.index')
            ->with('success', '정렬 순서가 업데이트되었습니다.');
    }

    /**
     * 통계 정보
     * AdminUser와의 연관성을 반영한 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => AdminLanguage::count(),
            'active' => AdminLanguage::where('is_active', true)->count(),
            'inactive' => AdminLanguage::where('is_active', false)->count(),
            'default' => AdminLanguage::where('is_default', true)->count(),
            'with_users' => AdminLanguage::whereIn('id', AdminUser::distinct('language_id')->pluck('language_id'))->count(),
            'without_users' => AdminLanguage::whereNotIn('id', AdminUser::distinct('language_id')->pluck('language_id'))->count(),
            'total_users' => AdminUser::count(),
            'language_distribution' => AdminLanguage::all()->map(function ($language) {
                return [
                    'name' => $language->name,
                    'code' => $language->code,
                    'user_count' => AdminUser::where('language_id', $language->id)->count(),
                    'is_active' => $language->is_active,
                    'is_default' => $language->is_default
                ];
            })
        ];

        return view('jiny-admin::admin.languages.stats', compact('stats'));
    }

    /**
     * 언어 활성화/비활성화 AJAX 토글
     */
    public function toggleEnableAjax(Request $request, $id): JsonResponse
    {
        try {
            $language = AdminLanguage::findOrFail($id);
            $oldData = ['is_active' => $language->is_active];
            
            $language->update(['is_active' => !$language->is_active]);
            
            // Activity Log 기록
            $this->logActivity('update', '언어 활성화 상태 AJAX 변경', $id, [
                'language_id' => $id,
                'new_status' => $language->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => '언어 상태가 변경되었습니다.',
                'is_active' => $language->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '상태 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 모든 언어 활성화
     */
    public function enableAllAjax(Request $request): JsonResponse
    {
        try {
            $oldData = AdminLanguage::all()->pluck('is_active', 'id')->toArray();
            
            AdminLanguage::query()->update(['is_active' => true]);
            
            // Activity Log 기록
            $this->logActivity('update', '모든 언어 활성화', null, ['action' => 'enable_all']);

            return response()->json([
                'success' => true,
                'message' => '모든 언어가 활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 기존 데이터 조회
     */
    protected function getOldData($id)
    {
        return AdminLanguage::find($id);
    }

    /**
     * 기본 언어 설정
     */
    public function setDefault(Request $request)
    {
        try {
            $request->validate([
                'language_id' => 'required|integer|exists:admin_languages,id'
            ]);

            $languageId = $request->input('language_id');
            $language = AdminLanguage::findOrFail($languageId);

            // 기존 기본 언어 해제
            $oldDefault = AdminLanguage::where('is_default', true)->first();
            if ($oldDefault) {
                $oldDefault->update(['is_default' => false]);
            }

            // 새로운 기본 언어 설정
            $language->update(['is_default' => true]);

            // Activity Log 기록
            $this->logActivity('update', '기본 언어 설정', $languageId, [
                'language_id' => $languageId,
                'old_default_id' => $oldDefault ? $oldDefault->id : null
            ]);

            return response()->json([
                'success' => true,
                'message' => $language->name . '이(가) 기본 언어로 설정되었습니다.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '기본 언어 설정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 로케일 동기화
     */
    public function syncLocale(Request $request)
    {
        try {
            $request->validate([
                'language_id' => 'required|integer|exists:admin_languages,id',
                'locale' => 'required|string|max:10'
            ]);

            $languageId = $request->input('language_id');
            $locale = $request->input('locale');
            
            $language = AdminLanguage::findOrFail($languageId);
            $oldData = ['locale' => $language->locale];
            
            $language->update(['locale' => $locale]);
            
            // Activity Log 기록
            $this->logActivity('update', '로케일 동기화', $languageId, [
                'language_id' => $languageId,
                'old_locale' => $oldData['locale'],
                'new_locale' => $locale
            ]);

            return response()->json([
                'success' => true,
                'message' => '로케일이 동기화되었습니다.',
                'data' => [
                    'language_id' => $languageId,
                    'locale' => $locale
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
                'message' => '로케일 동기화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
