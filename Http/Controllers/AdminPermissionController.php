<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\AdminPermission;

class AdminPermissionController extends Controller
{
    private $route;
    public function __construct()
    {
        $this->route = 'admin.admin.permissions.';
    }

    // 목록
    public function index(Request $request): View
    {
        $query = AdminPermission::query();
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);
        $rows = $query->paginate(20);
        return view('jiny-admin::permissions.index', [
            'rows' => $rows,
            'route' => $this->route,
            'filters' => $request->all()
        ]);
    }

    // 생성 폼
    public function create(): View
    {
        return view('jiny-admin::permissions.create', [
            'route' => $this->route
        ]);
    }

    // 저장
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:admin_permissions,name',
            'display_name' => 'required|string|max:100',
            'module' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort' => 'integer|nullable',
        ]);
        AdminPermission::create($validated);
        return redirect()->route($this->route.'index')->with('message', '권한이 등록되었습니다.');
    }

    // 상세
    public function show($id): View
    {
        $row = AdminPermission::findOrFail($id);
        return view('jiny-admin::permissions.show', [
            'row' => $row,
            'route' => $this->route
        ]);
    }

    // 수정 폼
    public function edit($id): View
    {
        $row = AdminPermission::findOrFail($id);
        return view('jiny-admin::permissions.edit', [
            'row' => $row,
            'route' => $this->route
        ]);
    }

    // 갱신
    public function update(Request $request, $id): RedirectResponse
    {
        $row = AdminPermission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:admin_permissions,name,'.$id,
            'display_name' => 'required|string|max:100',
            'module' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort' => 'integer|nullable',
        ]);
        $row->update($validated);
        return redirect()->route($this->route.'index')->with('message', '권한이 수정되었습니다.');
    }

    // 삭제
    public function destroy($id): RedirectResponse
    {
        $row = AdminPermission::findOrFail($id);
        $row->delete();
        return redirect()->route($this->route.'index')->with('message', '권한이 삭제되었습니다.');
    }

    // 대량 삭제
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if ($ids) {
            AdminPermission::whereIn('id', $ids)->delete();
        }
        return redirect()->route($this->route.'index')->with('message', '선택한 권한이 삭제되었습니다.');
    }

    // CSV 다운로드
    public function downloadCsv(Request $request)
    {
        $rows = AdminPermission::all();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="permissions.csv"',
        ];
        $callback = function() use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', '권한명', '표시명', '모듈', '설명', '활성화', '정렬', '생성일', '수정일']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->name, $row->display_name, $row->module, $row->description, $row->is_active, $row->sort, $row->created_at, $row->updated_at
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }
} 