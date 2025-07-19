<?php
namespace Jiny\Admin\Http\Controllers;

use Jiny\Admin\Models\AdminLevel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Admin\Http\Controllers\AdminResourceController;

class AdminLevelController extends AdminResourceController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->filterable = [
            'name',
            'code',
            'badge_color',
            'can_create',
            'can_read',
            'can_update',
            'can_delete',
        ];
        
        $this->validFilters = [
            'name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'badge_color' => 'nullable|string|max:50',
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    /**
     * get table name
     * this method is used to get the table name
     * @return string
     */
    protected function getTableName()
    {
        return 'admin_levels';
    }

    /**
     * get module name
     * this method is used to get the module name
     * @return string
     */
    protected function getModuleName()
    {
        return 'admin_levels';
    }

    /**
     * 수정 전 데이터 가져오기
     */
    protected function getOldData($id)
    {
        $level = AdminLevel::find($id);
        return $level ? $level->toArray() : null;
    }

    /**
     * handle index
     * this method is used to handle the index request
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    protected function _index(Request $request)
    {
        $query = AdminLevel::withCount(['users']);

        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['name', 'code', 'badge_color']);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $rows = $query->paginate(15);
       
        return view('jiny-admin::admin-levels.index', 
        [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection
        ]);
    }

    // /**
    //  * 필터 적용
    //  */
    // public function applyFilter($filters, $query)
    // {
    //     // 부분 일치가 자연스러운 필드
    //     $likeFields = ['name', 'code', 'badge_color'];

    //     foreach ($this->filterable as $column) {
    //         if (isset($filters[$column]) && $filters[$column] !== '') {
    //             if (in_array($column, $likeFields)) {
    //                 $query->where($column, 'like', "%{$filters[$column]}%");
    //             } else {
    //                 $query->where($column, $filters[$column]);
    //             }
    //         }
    //     }

    //     // search는 or 조건
    //     if (isset($filters['search']) && $filters['search'] !== '') {
    //         $query->where(function($q) use ($filters) {
    //             $q->where('name', 'like', "%{$filters['search']}%")
    //               ->orWhere('code', 'like', "%{$filters['search']}%")
    //               ->orWhere('badge_color', 'like', "%{$filters['search']}%");
    //         });
    //     }

    //     return $query;
    // }

    /**
     * handle create
     * this method is used to handle the create request
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    protected function _create(Request $request)
    {  
        return view('jiny-admin::admin-levels.create');
    }

    /**
     * handle store
     * this method is used to handle the store request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:admin_levels,code',
            'badge_color' => 'nullable|string|max:50',
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ]);
        
        $level = AdminLevel::create($data);

        return response()->json([
            'success' => true,
            'message' => '등급이 추가되었습니다.'
        ]);
    }

    /**
     * handle edit
     * this method is used to handle the edit request
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    protected function _edit(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);

        return view('jiny-admin::admin-levels.edit', [
            'item' => $level, // 수정 데이터는 item 변수로 전달(필수)
        ]);
    }

    /**
     * handle update
     * this method is used to handle the update request
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _update(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:admin_levels,code,'.$id,
            'badge_color' => 'nullable|string|max:50',
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ]);
        
        $level->update($data);

        return response()->json([
            'success' => true,
            'message' => '등급이 수정되었습니다.'
        ]);
    }

    /**
     * handle destroy
     * this method is used to handle the destroy request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _destroy(Request $request)
    {
        $id = $request->get('id') ?? $request->route('id');
        $level = AdminLevel::findOrFail($id);
        $level->delete();

        return response()->json([
            'success' => true,
            'message' => '등급이 삭제되었습니다.'
        ]);
    }
} 