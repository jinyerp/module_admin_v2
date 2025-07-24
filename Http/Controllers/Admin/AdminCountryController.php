<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

use Jiny\Admin\Models\Country;
use Jiny\Admin\Http\Controllers\AdminResourceController;

class AdminCountryController extends AdminResourceController
{
    protected $filterable = [
        'name', 'code', 'code3', 'currency_code', 'language_code', 'timezone', 'phone_code', 'is_active', 'is_default', 'sort_order'
    ];

    protected $validFilters = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|size:2',
        'code3' => 'required|string|size:3',
        'flag' => 'nullable|string|max:255',
        'currency_code' => 'nullable|string|size:3',
        'language_code' => 'nullable|string|max:5',
        'timezone' => 'nullable|string|max:255',
        'phone_code' => 'nullable|string|max:10',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer|min:0',
        'metadata' => 'nullable|json',
    ];

    protected $tableName = 'countries';
    protected $moduleName = 'country';
    protected $sortableColumns = ['name', 'code', 'code3', 'currency_code', 'language_code', 'timezone', 'phone_code', 'is_active', 'is_default', 'sort_order', 'created_at', 'updated_at'];

    // 템플릿 메소드 구현
    protected function _index(Request $request)
    {
        $query = Country::query();
        // 필터 적용
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }
        if ($request->filled('flag')) {
            $query->where('flag', 'like', '%' . $request->input('flag') . '%');
        }
        // 정렬 적용
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $query->orderBy($sort, $direction);
        $rows = $query->paginate(15);
        return view('jiny.admin::admin.country.index', [
            'rows' => $rows,
            'filters' => $request->only(['name', 'code', 'flag']),
            'sort' => $sort,
            'dir' => $direction,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _create(Request $request)
    {
        return view('jiny.admin::admin.country.create', [
            'item' => null,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _show(Request $request, $id)
    {
        $item = Country::findOrFail($id);
        
        return view('jiny-admin::admin.country.show', [
            'item' => $item,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _edit(Request $request, $id)
    {
        $item = Country::findOrFail($id);
        return view('jiny.admin::admin.country.edit', [
            'item' => $item,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2|unique:admin_country,code',
            'code3' => 'nullable|string|size:3|unique:admin_country,code3',
            'currency_code' => 'nullable|string|size:3',
            'language_code' => 'nullable|string|size:2',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
        $request->validate($validationRules);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        if ($data['is_default']) {
            Country::where('is_default', true)->update(['is_default' => false]);
        }
        $country = Country::create($data);
        return redirect()->route('admin.system.countries.index')->with('success', '성공적으로 생성되었습니다.');
    }

    protected function _update(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:2|unique:admin_country,code,' . $country->id,
            'code3' => 'nullable|string|size:3|unique:admin_country,code3,' . $country->id,
            'currency_code' => 'nullable|string|size:3',
            'language_code' => 'nullable|string|size:2',
            'sort_order' => 'nullable|integer|min:0',
            'enable' => 'boolean',
        ];
        $request->validate($validationRules);
        $data = $request->all();
        $country->update($data);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '성공적으로 수정되었습니다.',
                'item' => $country
            ]);
        }
        return redirect()->route('admin.system.countries.index')->with('success', '성공적으로 수정되었습니다.');
    }

    protected function _destroy(Request $request)
    {
        $id = $request->get('id') ?? $request->route('id');
        $country = Country::findOrFail($id);
        if ($country->is_default) {
            return response()->json([
                'success' => false,
                'message' => '기본 국가는 삭제할 수 없습니다.'
            ], 400);
        }
        $country->delete();
        session()->flash('deleted', '성공적으로 삭제되었습니다.');
        return response()->json([
            'success' => true,
            'message' => '성공적으로 삭제되었습니다.'
        ]);
    }

    // 추가 기능: 활성화/비활성화, 기본국가 설정, 순서변경, 통계, bulk delete 등은 그대로 유지
    public function toggleActive(Country $country): RedirectResponse
    {
        $oldValues = $country->toArray();
        $country->update(['is_active' => !$country->is_active]);
        $action = $country->is_active ? '활성화' : '비활성화';
        $this->logUpdateAction($country, $oldValues, $country->toArray(), "국가 {$action}: {$country->name}");
        $status = $country->is_active ? '활성화' : '비활성화';
        return redirect()->route('admin.system.countries.index')
            ->with('success', "국가가 {$status}되었습니다.");
    }

    public function setDefault(Country $country): RedirectResponse
    {
        DB::transaction(function() use ($country) {
            Country::where('is_default', true)->update(['is_default' => false]);
            $country->update(['is_default' => true]);
        });
        $this->logUpdateAction($country, [], $country->fresh()->toArray(), "기본 국가 설정: {$country->name}");
        return redirect()->route('admin.system.countries.index')
            ->with('success', '기본 국가가 설정되었습니다.');
    }

    public function updateOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'countries' => 'required|array',
            'countries.*' => 'integer|exists:countries,id',
        ]);
        foreach ($request->get('countries') as $index => $countryId) {
            Country::where('id', $countryId)->update(['sort_order' => $index + 1]);
        }
        return redirect()->route('admin.system.countries.index')
            ->with('success', '국가 순서가 업데이트되었습니다.');
    }

    public function stats(): View
    {
        $stats = [
            'total' => Country::count(),
            'active' => Country::where('is_active', true)->count(),
            'inactive' => Country::where('is_active', false)->count(),
            'default' => Country::where('is_default', true)->count(),
        ];
        return view('jiny.admin::admin.country.stats', [
            'stats' => $stats,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }
        $ids = array_map('intval', $request->input('ids'));
        $countriesToDelete = Country::whereIn('id', $ids)->get();
        $deletedCount = Country::whereIn('id', $ids)->delete();
        $this->logBulkDeleteAction($ids, $deletedCount, "대량 국가 삭제: {$deletedCount}개");
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 국가가 성공적으로 삭제되었습니다."
        ]);
    }

    /**
     * AJAX로 enable(활성화) 상태를 토글합니다.
     */
    public function toggleEnableAjax(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $field = $request->input('field', 'enable');
        $id = $request->input('id', null);
        
        $country = Country::findOrFail($id);
        $country->$field = $country->$field ? 0 : 1;
        $country->save();

        return response()->json([
            'success' => true, 
            $field => $country->$field
        ]);
    }


    /**
     * AJAX로 모든 국가의 enable(활성화) 상태를 일괄 변경합니다.
     */
    public function enableAllAjax(Request $request): \Illuminate\Http\JsonResponse
    {
        $enable = $request->input('enable', 0) ? 1 : 0;
        $field = $request->input('field', 'enable');
        \Jiny\Admin\Models\Country::query()
            ->update([$field => $enable]);
        
        return response()->json([
            'success' => true, 
            'enable' => $enable,
            'message' => '모든 국가의 enable 상태가 변경되었습니다.'
        ]);
    }





    // AdminResourceController에서 필요로 하는 데이터 반환 메서드 구현
    protected function getOldData($id)
    {
        return Country::find($id);
    }

    protected function getTableName()
    {
        return 'countries';
    }

    protected function getModuleName()
    {
        return 'country';
    }
}
