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
     * handle index
     * this method is used to handle the index request
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function _index(Request $request)
    {
        $query = AdminLevel::withCount(['users']);

        $filters = $this->getFilterParameters($request);
     
        // 부분 일치가 자연스러운 필드
        $likeFields = ['name', 'code', 'badge_color'];
        $query = $this->applyFilter($filters, $query, $likeFields);
        
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

    

    /**
     * handle create
     * this method is used to handle the create request
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function _create(Request $request)
    {  
        return view('jiny-admin::admin-levels.create');
    }

    public function _store(Request $request)
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
        AdminLevel::create($data);

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
    public function _edit(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);
        // $route = $this->getRouteName($request);

        return view('jiny-admin::admin-levels.edit', [
            'item' => $level // 수정 데이터는 item 변수로 전달(필수)
        ]);
    }

    public function _update(Request $request, $id)
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

    public function _destroy(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);
        $level->delete();

        return response()->json([
            'success' => true,
            'message' => '등급이 삭제되었습니다.'
        ]);
    }
} 