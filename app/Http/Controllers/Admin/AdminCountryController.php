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

use Jiny\Admin\App\Models\AdminCountry;
use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminCountryController
 *
 * 관리자 국가 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * AdminUser와 밀접한 연관성을 가짐:
 * - AdminUser.country_id 필드가 AdminCountry.id와 연결
 * - 국가별 사용자 수 계산 및 표시
 * - 지역화 및 국가별 설정 관리
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminCountry.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 국가 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminCountryTest.php
 * ```
 */
class AdminCountryController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.countries.index';
    public $createPath = 'jiny-admin::admin.countries.create';
    public $editPath = 'jiny-admin::admin.countries.edit';
    public $showPath = 'jiny-admin::admin.countries.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['name', 'code', 'iso_code', 'phone_code', 'is_active', 'is_default', 'sort_order'];
    protected $validFilters = ['name', 'code', 'iso_code', 'phone_code', 'is_active', 'is_default', 'sort_order'];
    protected $sortableColumns = ['id', 'name', 'code', 'iso_code', 'phone_code', 'is_active', 'is_default', 'sort_order', 'created_at', 'updated_at'];

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
        return 'admin_countries';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.admin_countries';
    }

    /**
     * 국가별 사용자 수 계산
     * AdminUser와 AdminCountry의 연관성을 반영
     */
    private function calculateUserCountsByCountry()
    {
        $countries = AdminCountry::all();
        $userCounts = [];
        
        foreach ($countries as $country) {
            // AdminUser.country_id 필드가 AdminCountry.id와 연결
            $userCount = AdminUser::where('country_id', $country->id)->count();
            $userCounts[$country->id] = $userCount;
        }
        
        return $userCounts;
    }

    /**
     * 국가 목록 조회
     * index() 에서 템플릿 메소드 호출
     * AdminUser와의 연관성을 고려하여 사용자 수 표시
     */
    protected function _index(Request $request): View
    {
        $query = AdminCountry::query();

        // 각 국가별 사용자 수 계산 (AdminUser와의 연관성 반영)
        $countries = $query->get();
        $countriesWithUserCount = $countries->map(function ($country) {
            // AdminUser.country_id 필드가 AdminCountry.id와 연결
            $country->users_count = AdminUser::where('country_id', $country->id)->count();
            return $country;
        });

        // 필터링
        if ($request->filled('filter_name')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return str_contains(strtolower($country->name), strtolower($request->filter_name));
            });
        }
        if ($request->filled('filter_code')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return str_contains(strtolower($country->code), strtolower($request->filter_code));
            });
        }
        if ($request->filled('filter_iso_code')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return str_contains(strtolower($country->iso_code), strtolower($request->filter_iso_code));
            });
        }
        if ($request->filled('filter_phone_code')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return str_contains(strtolower($country->phone_code), strtolower($request->filter_phone_code));
            });
        }
        if ($request->filled('filter_is_active')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return $country->is_active == $request->filter_is_active;
            });
        }
        if ($request->filled('filter_is_default')) {
            $countriesWithUserCount = $countriesWithUserCount->filter(function ($country) use ($request) {
                return $country->is_default == $request->filter_is_default;
            });
        }

        // 정렬
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            if ($sortOrder === 'asc') {
                $countriesWithUserCount = $countriesWithUserCount->sortBy($sortBy);
            } else {
                $countriesWithUserCount = $countriesWithUserCount->sortByDesc($sortBy);
            }
        } else {
            $countriesWithUserCount = $countriesWithUserCount->sortBy('sort_order');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);
        $total = $countriesWithUserCount->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $countriesWithUserCount->slice($offset, $perPage);
        
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_name', 'filter_code', 'filter_iso_code', 'filter_phone_code', 'filter_is_active', 'filter_is_default'
        ]);

        // Activity Log 기록
        $this->logActivity('list', '국가 목록 조회', null, $filters);

        return view($this->indexPath, [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.admin.countries.',
        ]);
    }

    /**
     * 국가 생성 폼
     */
    protected function _create(Request $request): View
    {
        // Activity Log 기록
        $this->logActivity('create', '국가 생성 폼 접근', null, []);

        return view($this->createPath, [
            'route' => 'admin.admin.countries.',
        ]);
    }

    /**
     * 국가 저장
     */
    protected function _store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_countries,code',
                'iso_code' => 'required|string|max:3|unique:admin_countries,iso_code',
                'phone_code' => 'nullable|string|max:10',
                'currency_code' => 'nullable|string|max:3',
                'timezone' => 'nullable|string|max:50',
                'is_active' => 'boolean',
                'is_default' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '국가명을 입력해주세요.',
                'name.max' => '국가명은 255자를 초과할 수 없습니다.',
                'code.required' => '국가 코드를 입력해주세요.',
                'code.max' => '국가 코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 국가 코드입니다.',
                'iso_code.required' => 'ISO 코드를 입력해주세요.',
                'iso_code.max' => 'ISO 코드는 3자를 초과할 수 없습니다.',
                'iso_code.unique' => '이미 존재하는 ISO 코드입니다.',
                'phone_code.max' => '전화 코드는 10자를 초과할 수 없습니다.',
                'currency_code.max' => '통화 코드는 3자를 초과할 수 없습니다.',
                'timezone.max' => '시간대는 50자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');

            // 기본 국가로 설정하는 경우 다른 국가의 기본 설정 해제
            if ($validated['is_default']) {
                AdminCountry::where('is_default', true)->update(['is_default' => false]);
            }

            $country = AdminCountry::create($validated);

            // Activity Log 기록
            $this->logActivity('create', '국가 생성', $country->id, $validated);

            return response()->json([
                'success' => true,
                'message' => '국가가 성공적으로 등록되었습니다.',
                'data' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code
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
                'message' => '국가 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 국가 상세 보기
     * 해당 국가를 사용하는 AdminUser 목록도 함께 표시
     */
    protected function _show(Request $request, $id): View
    {
        $country = AdminCountry::findOrFail($id);
        
        // 해당 국가를 사용하는 AdminUser 목록 조회 (연관성 반영)
        $usersWithThisCountry = AdminUser::where('country_id', $country->id)->get();

        // Activity Log 기록
        $this->logActivity('read', '국가 상세 조회', $id, ['country_id' => $id]);

        return view($this->showPath, [
            'country' => $country,
            'users' => $usersWithThisCountry,
            'route' => 'admin.admin.countries.',
        ]);
    }

    /**
     * 국가 수정 폼
     */
    protected function _edit(Request $request, $id): View
    {
        $country = AdminCountry::findOrFail($id);
        
        // 해당 국가를 사용하는 AdminUser 수 확인
        $userCount = AdminUser::where('country_id', $country->id)->count();

        // Activity Log 기록
        $this->logActivity('update', '국가 수정 폼 접근', $id, ['country_id' => $id]);

        return view($this->editPath, [
            'country' => $country,
            'userCount' => $userCount,
            'route' => 'admin.admin.countries.',
        ]);
    }

    /**
     * 국가 수정
     * AdminUser와의 연관성을 고려하여 안전하게 수정
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        try {
            $country = AdminCountry::findOrFail($id);

            // 수정 전 데이터 가져오기 (Audit Log용)
            $oldData = $country->toArray();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:admin_countries,code,' . $id,
                'iso_code' => 'required|string|max:3|unique:admin_countries,iso_code,' . $id,
                'phone_code' => 'nullable|string|max:10',
                'currency_code' => 'nullable|string|max:3',
                'timezone' => 'nullable|string|max:50',
                'is_active' => 'boolean',
                'is_default' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
            ], [
                'name.required' => '국가명을 입력해주세요.',
                'name.max' => '국가명은 255자를 초과할 수 없습니다.',
                'code.required' => '국가 코드를 입력해주세요.',
                'code.max' => '국가 코드는 10자를 초과할 수 없습니다.',
                'code.unique' => '이미 존재하는 국가 코드입니다.',
                'iso_code.required' => 'ISO 코드를 입력해주세요.',
                'iso_code.max' => 'ISO 코드는 3자를 초과할 수 없습니다.',
                'iso_code.unique' => '이미 존재하는 ISO 코드입니다.',
                'phone_code.max' => '전화 코드는 10자를 초과할 수 없습니다.',
                'currency_code.max' => '통화 코드는 3자를 초과할 수 없습니다.',
                'timezone.max' => '시간대는 50자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');

            // 기본 국가로 설정하는 경우 다른 국가의 기본 설정 해제
            if ($validated['is_default'] && !$country->is_default) {
                AdminCountry::where('is_default', true)->update(['is_default' => false]);
            }

            $country->update($validated);

            // Activity Log 기록
            $this->logActivity('update', '국가 수정', $country->id, $validated);
            
            // Audit Log 기록
            $this->logAudit('update', $oldData, $validated, '국가 수정', $country->id);

            return response()->json([
                'success' => true,
                'message' => '국가가 성공적으로 수정되었습니다.',
                'data' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code
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
                'message' => '국가 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 국가 삭제
     * AdminUser와의 연관성을 확인하여 안전하게 삭제
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->route('id');
        
        try {
            $country = AdminCountry::findOrFail($id);

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = $country->toArray();

            // 사용 중인 국가인지 확인 (AdminUser.country_id 필드와 AdminCountry.id 연결)
            $usersUsingCountry = AdminUser::where('country_id', $country->id)->count();
            if ($usersUsingCountry > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '사용 중인 국가는 삭제할 수 없습니다. (사용자 수: ' . $usersUsingCountry . '명)'
                ], 400);
            }

            // 기본 국가인지 확인
            if ($country->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 국가는 삭제할 수 없습니다.'
                ], 400);
            }

            $country->delete();

            // Activity Log 기록
            $this->logActivity('delete', '국가 삭제', $id, ['deleted_id' => $id]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '국가 삭제', null);

            return response()->json([
                'success' => true,
                'message' => '국가가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '국가 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼 반환
     * 해당 국가를 사용하는 AdminUser 정보도 함께 표시
     */
    public function deleteConfirm(Request $request, $id)
    {
        $country = AdminCountry::findOrFail($id);
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        // 해당 국가를 사용하는 AdminUser 목록 조회
        $usersWithThisCountry = AdminUser::where('country_id', $country->id)->get();
        
        return view('jiny-admin::admin.countries.form_delete', [
            'country' => $country,
            'users' => $usersWithThisCountry,
            'title' => '국가 삭제',
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
                'ids.*' => 'integer|exists:admin_countries,id'
            ]);

            $ids = $request->input('ids');
            
            // 사용 중인 국가가 포함되어 있는지 확인 (AdminUser.country_id 필드와 AdminCountry.id 연결)
            $countries = AdminCountry::whereIn('id', $ids)->get();
            $usedCountries = [];
            
            foreach ($countries as $country) {
                $userCount = AdminUser::where('country_id', $country->id)->count();
                if ($userCount > 0) {
                    $usedCountries[] = $country->name . ' (' . $userCount . '명 사용 중)';
                }
            }
            
            if (!empty($usedCountries)) {
                return response()->json([
                    'success' => false,
                    'message' => '다음 국가들은 사용 중이므로 삭제할 수 없습니다: ' . implode(', ', $usedCountries)
                ], 400);
            }

            // 기본 국가가 포함되어 있는지 확인
            $defaultCountries = $countries->where('is_default', true);
            if ($defaultCountries->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 국가는 삭제할 수 없습니다.'
                ], 400);
            }

            // 삭제 전 데이터 가져오기 (Audit Log용)
            $oldData = AdminCountry::whereIn('id', $ids)->get()->toArray();

            AdminCountry::whereIn('id', $ids)->delete();

            // Activity Log 기록
            $this->logActivity('delete', '국가 일괄 삭제', null, ['deleted_ids' => $ids]);
            
            // Audit Log 기록
            $this->logAudit('delete', $oldData, null, '국가 일괄 삭제', null);

            return response()->json([
                'success' => true,
                'message' => count($ids) . '개의 국가가 성공적으로 삭제되었습니다.'
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
     * 국가 활성화/비활성화 토글
     */
    public function toggleActive(AdminCountry $country): RedirectResponse
    {
        $oldData = ['is_active' => $country->is_active];
        
        $country->update(['is_active' => !$country->is_active]);
        
        // Activity Log 기록
        $this->logActivity('update', '국가 활성화 상태 변경', $country->id, [
            'country_id' => $country->id,
            'new_status' => $country->is_active
        ]);

        return redirect()->back()->with('success', '국가 상태가 변경되었습니다.');
    }

    /**
     * 기본 국가 설정
     */
    public function setDefault(AdminCountry $country): RedirectResponse
    {
        $oldData = ['is_default' => $country->is_default];
        
        // 기존 기본 국가 해제
        AdminCountry::where('is_default', true)->update(['is_default' => false]);
        
        // 새로운 기본 국가 설정
        $country->update(['is_default' => true]);
        
        // Activity Log 기록
        $this->logActivity('update', '기본 국가 설정', $country->id, [
            'country_id' => $country->id,
            'action' => 'set_default'
        ]);

        return redirect()->back()->with('success', $country->name . '이(가) 기본 국가로 설정되었습니다.');
    }

    /**
     * 정렬 순서 업데이트
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:admin_countries,id'
        ]);

        $orders = $request->input('orders');
        
        foreach ($orders as $index => $id) {
            // sort_order 컬럼이 존재하는지 확인
            if (Schema::hasColumn('admin_countries', 'sort_order')) {
                AdminCountry::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        }

        // Activity Log 기록
        $this->logActivity('update', '국가 정렬 순서 업데이트', null, ['orders' => $orders]);

        return redirect()->route('admin.admin.countries.index')
            ->with('success', '정렬 순서가 업데이트되었습니다.');
    }

    /**
     * 통계 정보
     * AdminUser와의 연관성을 반영한 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => AdminCountry::count(),
            'active' => AdminCountry::where('is_active', true)->count(),
            'inactive' => AdminCountry::where('is_active', false)->count(),
            'default' => AdminCountry::where('is_default', true)->count(),
            'with_users' => AdminCountry::whereIn('id', AdminUser::distinct('country_id')->pluck('country_id'))->count(),
            'without_users' => AdminCountry::whereNotIn('id', AdminUser::distinct('country_id')->pluck('country_id'))->count(),
            'total_users' => AdminUser::count(),
            'country_distribution' => AdminCountry::all()->map(function ($country) {
                return [
                    'name' => $country->name,
                    'code' => $country->code,
                    'user_count' => AdminUser::where('country_id', $country->id)->count(),
                    'is_active' => $country->is_active,
                    'is_default' => $country->is_default
                ];
            })
        ];

        return view('jiny-admin::admin.countries.stats', compact('stats'));
    }

    /**
     * 국가 활성화/비활성화 AJAX 토글
     */
    public function toggleEnableAjax(Request $request, $id): JsonResponse
    {
        try {
            $country = AdminCountry::findOrFail($id);
            $oldData = ['is_active' => $country->is_active];
            
            $country->update(['is_active' => !$country->is_active]);
            
            // Activity Log 기록
            $this->logActivity('update', '국가 활성화 상태 AJAX 변경', $id, [
                'country_id' => $id,
                'new_status' => $country->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => '국가 상태가 변경되었습니다.',
                'is_active' => $country->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '상태 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 모든 국가 활성화
     */
    public function enableAllAjax(Request $request): JsonResponse
    {
        try {
            $oldData = AdminCountry::all()->pluck('is_active', 'id')->toArray();
            
            AdminCountry::query()->update(['is_active' => true]);
            
            // Activity Log 기록
            $this->logActivity('update', '모든 국가 활성화', null, ['action' => 'enable_all']);

            return response()->json([
                'success' => true,
                'message' => '모든 국가가 활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '일괄 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
