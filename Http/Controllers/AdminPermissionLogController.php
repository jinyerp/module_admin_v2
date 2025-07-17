<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\AdminPermissionLog;

class AdminPermissionLogController extends Controller
{
    private $route;
    public function __construct()
    {
        $this->route = 'admin.admin.permission-logs.';
    }

    public function index(Request $request): View
    {
        $query = AdminPermissionLog::query();
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);
        $rows = $query->paginate(20);
        return view('jiny-admin::permission-logs.index', [
            'rows' => $rows,
            'route' => $this->route,
            'filters' => $request->all()
        ]);
    }

    // create, store, show, edit, update, destroy 등 users 컨트롤러와 동일하게 구현 (필요시 추가 구현)
} 