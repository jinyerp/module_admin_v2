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

use Jiny\Admin\App\Models\Country;
use Jiny\Admin\App\Http\Controllers\AdminResourceController;

class AdminCountryController extends AdminResourceController
{
    protected $sortableColumns = ['id', 'name', 'code', 'code3', 'currency_code', 'language_code', 'timezone', 'phone_code', 'is_active', 'is_default', 'sort_order', 'created_at', 'updated_at'];
    protected $filterable = ['name', 'code', 'code3', 'currency_code', 'language_code', 'timezone', 'phone_code', 'is_active', 'is_default', 'sort_order'];
    private $config;

    public function __construct()
    {
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    /**
     * 국가 목록 (템플릿 메소드 구현)
     */
    public function _index(Request $request): View
    {
        $query = Country::query();

        // 필터링
        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . $request->filter_name . '%');
        }
        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }
        if ($request->filled('filter_code3')) {
            $query->where('code3', 'like', '%' . $request->filter_code3 . '%');
        }
        if ($request->filled('filter_currency_code')) {
            $query->where('currency_code', 'like', '%' . $request->filter_currency_code . '%');
        }
        if ($request->filled('filter_language_code')) {
            $query->where('language_code', 'like', '%' . $request->filter_language_code . '%');
        }
        if ($request->filled('filter_timezone')) {
            $query->where('timezone', 'like', '%' . $request->filter_timezone . '%');
        }
        if ($request->filled('filter_phone_code')) {
            $query->where('phone_code', 'like', '%' . $request->filter_phone_code . '%');
        }
        if ($request->filled('filter_is_active')) {
            $query->where('is_active', $request->filter_is_active);
        }
        if ($request->filled('filter_is_default')) {
            $query->where('is_default', $request->filter_is_default);
        }
        if ($request->filled('filter_sort_order')) {
            $query->where('sort_order', $request->filter_sort_order);
        }

        // 정렬
        $sortBy = $request->get('sort', 'sort_order');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, $this->sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        // 페이지네이션
        $perPage = $request->get('per_page', 15);
        $rows = $query->paginate($perPage);

        // 필터 데이터 전달
        $filters = $request->only([
            'filter_name', 'filter_code', 'filter_code3', 'filter_currency_code',
            'filter_language_code', 'filter_timezone', 'filter_phone_code',
            'filter_is_active', 'filter_is_default', 'filter_sort_order'
        ]);

        return view('jiny-admin::admin.country.index', [
            'rows' => $rows,
            'filters' => $filters,
            'route' => 'admin.country.',
        ]);
    }

    /**
     * 국가 생성 폼
     */
    public function _create(Request $request): View
    {
        return view('jiny-admin::admin.country.create');
    }

    /**
     * 국가 상세 보기
     */
    public function _show(Request $request, $id): View
    {
        $country = Country::findOrFail($id);
        return view('jiny-admin::admin.country.show', compact('country'));
    }

    /**
     * 국가 수정 폼
     */
    public function _edit(Request $request, $id): View
    {
        $country = Country::findOrFail($id);
        return view('jiny-admin::admin.country.edit', compact('country'));
    }

    /**
     * 국가 저장
     */
    public function _store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|size:2|unique:countries,code',
                'code3' => 'nullable|string|size:3|unique:countries,code3',
                'currency_code' => 'nullable|string|size:3',
                'language_code' => 'nullable|string|size:2',
                'timezone' => 'nullable|string|max:255',
                'phone_code' => 'nullable|string|max:10',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'is_default' => 'boolean',
            ], [
                'name.required' => '국가명을 입력해주세요.',
                'name.max' => '국가명은 255자를 초과할 수 없습니다.',
                'code.required' => '국가코드를 입력해주세요.',
                'code.size' => '국가코드는 2자리여야 합니다.',
                'code.unique' => '이미 존재하는 국가코드입니다.',
                'code3.size' => '3자리 국가코드는 3자리여야 합니다.',
                'code3.unique' => '이미 존재하는 3자리 국가코드입니다.',
                'currency_code.size' => '통화코드는 3자리여야 합니다.',
                'language_code.size' => '언어코드는 2자리여야 합니다.',
                'timezone.max' => '시간대는 255자를 초과할 수 없습니다.',
                'phone_code.max' => '전화코드는 10자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');
            
            if ($validated['is_default']) {
                Country::where('is_default', true)->update(['is_default' => false]);
            }

            $country = Country::create($validated);

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
     * 국가 수정
     */
    public function _update(Request $request, $id): JsonResponse
    {
        try {
            $country = Country::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|size:2|unique:countries,code,' . $id,
                'code3' => 'nullable|string|size:3|unique:countries,code3,' . $id,
                'currency_code' => 'nullable|string|size:3',
                'language_code' => 'nullable|string|size:2',
                'timezone' => 'nullable|string|max:255',
                'phone_code' => 'nullable|string|max:10',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'is_default' => 'boolean',
            ], [
                'name.required' => '국가명을 입력해주세요.',
                'name.max' => '국가명은 255자를 초과할 수 없습니다.',
                'code.required' => '국가코드를 입력해주세요.',
                'code.size' => '국가코드는 2자리여야 합니다.',
                'code.unique' => '이미 존재하는 국가코드입니다.',
                'code3.size' => '3자리 국가코드는 3자리여야 합니다.',
                'code3.unique' => '이미 존재하는 3자리 국가코드입니다.',
                'currency_code.size' => '통화코드는 3자리여야 합니다.',
                'language_code.size' => '언어코드는 2자리여야 합니다.',
                'timezone.max' => '시간대는 255자를 초과할 수 없습니다.',
                'phone_code.max' => '전화코드는 10자를 초과할 수 없습니다.',
                'sort_order.integer' => '정렬순서는 숫자여야 합니다.',
                'sort_order.min' => '정렬순서는 0 이상이어야 합니다.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_default'] = $request->has('is_default');
            
            if ($validated['is_default']) {
                Country::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $country->update($validated);

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
     */
    public function _destroy(Request $request): JsonResponse
    {
        try {
            $id = $request->input('id');
            $country = Country::findOrFail($id);

            // 기본 국가는 삭제 불가
            if ($country->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 국가는 삭제할 수 없습니다.'
                ], 400);
            }

            $country->delete();

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
     * 삭제 확인 폼
     */
    public function deleteConfirm(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $randomKey = strtoupper(substr(md5(uniqid()), 0, 8));
        
        return view('jiny-admin::admin.country.form_delete', [
            'country' => $country,
            'title' => '국가 삭제',
            'randomKey' => $randomKey
        ]);
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:countries,id'
            ]);

            $ids = $request->input('ids');
            
            // 기본 국가가 포함되어 있는지 확인
            $defaultCountries = Country::whereIn('id', $ids)->where('is_default', true)->count();
            if ($defaultCountries > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '기본 국가는 삭제할 수 없습니다.'
                ], 400);
            }

            Country::whereIn('id', $ids)->delete();

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
     * 활성화 토글
     */
    public function toggleActive(Country $country): RedirectResponse
    {
        $country->update(['is_active' => !$country->is_active]);
        
        return redirect()->route('admin.country.index')
            ->with('success', '국가 상태가 변경되었습니다.');
    }

    /**
     * 기본 국가 설정
     */
    public function setDefault(Country $country): RedirectResponse
    {
        // 기존 기본 국가 해제
        Country::where('is_default', true)->update(['is_default' => false]);
        
        // 새로운 기본 국가 설정
        $country->update(['is_default' => true]);
        
        return redirect()->route('admin.country.index')
            ->with('success', '기본 국가가 변경되었습니다.');
    }

    /**
     * 정렬 순서 업데이트
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:countries,id'
        ]);

        $orders = $request->input('orders');
        
        foreach ($orders as $index => $id) {
            Country::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return redirect()->route('admin.country.index')
            ->with('success', '정렬 순서가 업데이트되었습니다.');
    }

    /**
     * 통계 정보
     */
    public function stats(): View
    {
        $stats = [
            'total' => Country::count(),
            'active' => Country::where('is_active', true)->count(),
            'inactive' => Country::where('is_active', false)->count(),
            'default' => Country::where('is_default', true)->count(),
        ];

        return view('jiny-admin::admin.country.stats', compact('stats'));
    }

    /**
     * AJAX 활성화 토글
     */
    public function toggleEnableAjax(Request $request, $id): JsonResponse
    {
        try {
            $country = Country::findOrFail($id);
            $country->update(['is_active' => !$country->is_active]);

            return response()->json([
                'success' => true,
                'message' => '상태가 변경되었습니다.',
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
     * AJAX 전체 활성화
     */
    public function enableAllAjax(Request $request): JsonResponse
    {
        try {
            Country::query()->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => '모든 국가가 활성화되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '전체 활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
