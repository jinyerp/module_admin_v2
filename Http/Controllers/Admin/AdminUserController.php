<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\AdminUser;

class AdminUserController extends Controller
{
    private $prefix;
    public function __construct()
    {
        $this->prefix = 'admin.admin.users';
    }

    /**
     * 관리자 목록
     */
    public function index(Request $request): View
    {
        $query = AdminUser::query();

        // 필터 파라미터
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 정렬
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);

        // 페이징
        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage)->appends($request->all());

        // 통계 데이터
        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('status', 'active')->count(),
            'inactive' => AdminUser::where('status', 'inactive')->count(),
            'suspended' => AdminUser::where('status', 'suspended')->count(),
        ];

        $prefix = $this->prefix;
        return view('jiny-admin::users.index', compact('users', 'stats', 'sort', 'dir', 'prefix'));
    }

    public function create(): View
    {
        $prefix = $this->prefix;
        return view('jiny-admin::users.create', compact('prefix'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|string|min:6',
            'type' => 'required|in:super,admin,staff',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        $validated['password'] = bcrypt($validated['password']);
        $user = AdminUser::create($validated);
        return redirect()->route('admin.admin.users.index')->with('success', '관리자가 성공적으로 등록되었습니다.');
    }

    public function show(AdminUser $user): View
    {
        $prefix = $this->prefix;
        return view('jiny-admin::users.show', compact('user', 'prefix'));
    }

    public function edit(AdminUser $user): View
    {
        $prefix = $this->prefix;
        return view('jiny-admin::users.edit', compact('user', 'prefix'));
    }

    public function update(Request $request, AdminUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'type' => 'required|in:super,admin,staff',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        return redirect()->route('admin.admin.users.show', $user)->with('success', '관리자 정보가 수정되었습니다.');
    }

    public function destroy(AdminUser $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('admin.admin.users.index')->with('success', '관리자가 삭제되었습니다.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:admin_users,id',
        ]);

        $ids = $request->ids;
        $count = count($ids);

        AdminUser::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count}명의 관리자가 성공적으로 삭제되었습니다.",
        ]);
    }
}
