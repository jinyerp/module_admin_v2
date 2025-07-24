<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

use Jiny\Admin\Models\Language;
use Jiny\Admin\Http\Controllers\AdminResourceController;

class AdminLanguageController extends AdminResourceController
{
    protected $filterable = [
        'name', 'code', 'flag', 'country', 'users', 'users_percent', 'enable'
    ];

    protected $validFilters = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10',
        'flag' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
        'users' => 'nullable|string|max:255',
        'users_percent' => 'nullable|string|max:255',
        'enable' => 'boolean',
    ];

    protected $tableName = 'admin_language';
    protected $moduleName = 'language';
    protected $sortableColumns = ['name', 'code', 'flag', 'country', 'users', 'users_percent', 'enable', 'created_at', 'updated_at'];

    protected function _index(Request $request)
    {
        $query = Language::query();
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }
        if ($request->filled('flag')) {
            $query->where('flag', 'like', '%' . $request->input('flag') . '%');
        }
        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $query->orderBy($sort, $direction);
        $rows = $query->paginate(15);
        return view('jiny.admin::admin.language.index', [
            'rows' => $rows,
            'filters' => $request->only(['name', 'code', 'flag', 'country']),
            'sort' => $sort,
            'dir' => $direction,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _create(Request $request)
    {
        return view('jiny.admin::admin.language.create', [
            'item' => null,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _show(Request $request, $id)
    {
        $item = Language::findOrFail($id);
        return view('jiny-admin::admin.language.show', [
            'item' => $item,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _edit(Request $request, $id)
    {
        $item = Language::findOrFail($id);
        return view('jiny.admin::admin.language.edit', [
            'item' => $item,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    protected function _store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:admin_language,code',
            'flag' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'users' => 'nullable|string|max:255',
            'users_percent' => 'nullable|string|max:255',
            'enable' => 'boolean',
        ];
        $request->validate($validationRules);
        $data = $request->all();
        $data['enable'] = $request->input('enable', 1);
        $language = Language::create($data);
        return redirect()->route('admin.language.index')->with('success', '성공적으로 생성되었습니다.');
    }

    protected function _update(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        $validationRules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:admin_language,code,' . $language->id,
            'flag' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'users' => 'nullable|string|max:255',
            'users_percent' => 'nullable|string|max:255',
            'enable' => 'boolean',
        ];
        $request->validate($validationRules);
        $data = $request->all();
        $language->update($data);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '성공적으로 수정되었습니다.',
                'item' => $language
            ]);
        }
        return redirect()->route('admin.language.index')->with('success', '성공적으로 수정되었습니다.');
    }

    protected function _destroy(Request $request)
    {
        $id = $request->get('id') ?? $request->route('id');
        $language = Language::findOrFail($id);
        $language->delete();
        session()->flash('deleted', '성공적으로 삭제되었습니다.');
        return response()->json([
            'success' => true,
            'message' => '성공적으로 삭제되었습니다.'
        ]);
    }

    public function toggleEnableAjax(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $field = $request->input('field', 'enable');
        $id = $request->input('id', null);
        $language = Language::findOrFail($id);
        $language->$field = $language->$field ? 0 : 1;
        $language->save();
        return response()->json([
            'success' => true,
            $field => $language->$field
        ]);
    }

    public function enableAllAjax(Request $request): \Illuminate\Http\JsonResponse
    {
        $enable = $request->input('enable', 0) ? 1 : 0;
        $field = $request->input('field', 'enable');
        Language::query()->update([$field => $enable]);
        return response()->json([
            'success' => true,
            'enable' => $enable,
            'message' => '모든 언어의 enable 상태가 변경되었습니다.'
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
        $deletedCount = Language::whereIn('id', $ids)->delete();
        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 언어가 성공적으로 삭제되었습니다."
        ]);
    }

    protected function getOldData($id)
    {
        return Language::find($id);
    }

    protected function getTableName()
    {
        return 'admin_language';
    }

    protected function getModuleName()
    {
        return 'language';
    }
}
