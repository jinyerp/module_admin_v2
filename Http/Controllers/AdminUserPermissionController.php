<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\AdminUserPermission;

class AdminUserPermissionController extends Controller
{
    private $route;
    public function __construct()
    {
        $this->route = 'admin.admin.user-permissions.';
    }

    // 목록
    public function index(Request $request): View
    {
        $query = AdminUserPermission::query();
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);
        $rows = $query->paginate(20);
        return view('jiny-admin::user-permissions.index', [
            'rows' => $rows,
            'route' => $this->route,
            'filters' => $request->all()
        ]);
    }

    // 생성 폼
    public function create(): View
    {
        return view('jiny-admin::user-permissions.create', [
            'route' => $this->route
        ]);
    }

    // 저장
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_user_id' => 'required|integer',
            'permission_id' => 'required|integer',
            'granted_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'status' => 'required|string|max:20',
        ]);
        AdminUserPermission::create($validated);
        return redirect()->route($this->route.'index')->with('message', '사용자 권한이 등록되었습니다.');
    }

    // 상세
    public function show($id): View
    {
        $row = AdminUserPermission::findOrFail($id);
        return view('jiny-admin::user-permissions.show', [
            'row' => $row,
            'route' => $this->route
        ]);
    }

    // 수정 폼
    public function edit($id): View
    {
        $row = AdminUserPermission::findOrFail($id);
        return view('jiny-admin::user-permissions.edit', [
            'row' => $row,
            'route' => $this->route
        ]);
    }

    // 갱신
    public function update(Request $request, $id): RedirectResponse
    {
        $row = AdminUserPermission::findOrFail($id);
        $validated = $request->validate([
            'admin_user_id' => 'required|integer',
            'permission_id' => 'required|integer',
            'granted_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'status' => 'required|string|max:20',
        ]);
        $row->update($validated);
        return redirect()->route($this->route.'index')->with('message', '사용자 권한이 수정되었습니다.');
    }

    // 삭제
    public function destroy($id): RedirectResponse
    {
        $row = AdminUserPermission::findOrFail($id);
        $row->delete();
        return redirect()->route($this->route.'index')->with('message', '사용자 권한이 삭제되었습니다.');
    }

    // 대량 삭제
    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if ($ids) {
            AdminUserPermission::whereIn('id', $ids)->delete();
        }
        return redirect()->route($this->route.'index')->with('message', '선택한 사용자 권한이 삭제되었습니다.');
    }

    // CSV 다운로드
    public function downloadCsv(Request $request)
    {
        $rows = AdminUserPermission::all();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="user_permissions.csv"',
        ];
        $callback = function() use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', '관리자ID', '권한ID', '부여일시', '만료일시', '상태', '생성일', '수정일']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->admin_user_id, $row->permission_id, $row->granted_at, $row->expired_at, $row->status, $row->created_at, $row->updated_at
                ]);
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }
} 