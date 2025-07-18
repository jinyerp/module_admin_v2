<?php
namespace Jiny\Admin\Http\Controllers;

use Jiny\Admin\Models\AdminLevel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jiny\Admin\Http\Controllers\AdminResourceController;

class AdminLevelController extends AdminResourceController
{
    private $filterable = [];
    private $validFilters = [];

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

    public function index(Request $request)
    {
        $query = AdminLevel::withCount(['users']);

        $filters = $this->getFilterParameters($request);
        //dd($filters);
        $query = $this->applyFilter($filters, $query);
        
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $rows = $query->paginate(15);
       
        return view('jiny-admin::admin-levels.index', 
        [
            'rows' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => $this->getRouteName($request)
        ]);
    }

    /**
     * 필터 적용
     */
    public function applyFilter($filters, $query)
    {
        // 부분 일치가 자연스러운 필드
        $likeFields = ['name', 'code', 'badge_color'];

        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                if (in_array($column, $likeFields)) {
                    $query->where($column, 'like', "%{$filters[$column]}%");
                } else {
                    $query->where($column, $filters[$column]);
                }
            }
        }

        // search는 or 조건
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('badge_color', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    

    public function create(Request $request)
    {  
        return view('jiny-admin::admin-levels.create',[
            'route' => $this->getRouteName($request)
        ]);
    }

    public function store(Request $request)
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

    public function edit(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);
        // $route = $this->getRouteName($request);

        return view('jiny-admin::admin-levels.edit', [
            'item' => $level, // 수정 데이터는 item 변수로 전달(필수)
            'route' => $this->getRouteName($request)
        ]);
    }

    public function update(Request $request, $id)
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

    public function destroy(Request $request, $id)
    {
        $level = AdminLevel::findOrFail($id);
        $level->delete();

        return response()->json([
            'success' => true,
            'message' => '등급이 삭제되었습니다.'
        ]);
    }
} 