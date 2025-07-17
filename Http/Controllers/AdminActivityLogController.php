<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\AdminActivityLog;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $route = 'admin.admin.activity-log.';
        $rows = AdminActivityLog::with('adminUser')->orderByDesc('id')->paginate(20);
        return view('jiny-admin::activity-logs.index', [
            'rows' => $rows,
            'route' => $route,
        ]);
    }

    public function show($id)
    {
        $route = 'admin.admin.activity-log.';
        $log = AdminActivityLog::with('adminUser')->findOrFail($id);
        return view('jiny-admin::activity-logs.show', [
            'log' => $log,
            'route' => $route,
        ]);
    }

    public function create()
    {
        $route = 'admin.admin.activity-log.';
        $users = AdminUser::all();
        return view('jiny-admin::activity-logs.create', [
            'users' => $users,
            'route' => $route,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'admin_user_id' => 'required|exists:admin_users,id',
            'action' => 'required|string',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|string',
        ]);
        AdminActivityLog::create($data);
        return redirect()->route('admin.admin.activity-log.index')->with('message', '활동 로그가 등록되었습니다.');
    }

    public function edit($id)
    {
        $route = 'admin.admin.activity-log.';
        $log = AdminActivityLog::findOrFail($id);
        $users = AdminUser::all();
        return view('jiny-admin::activity-logs.edit', [
            'log' => $log,
            'users' => $users,
            'route' => $route,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'admin_user_id' => 'required|exists:admin_users,id',
            'action' => 'required|string',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|string',
        ]);
        $log = AdminActivityLog::findOrFail($id);
        $log->update($data);
        return redirect()->route('admin.admin.activity-log.index')->with('message', '활동 로그가 수정되었습니다.');
    }

    public function destroy($id)
    {
        $log = AdminActivityLog::findOrFail($id);
        $log->delete();
        return redirect()->route('admin.admin.activity-log.index')->with('message', '활동 로그가 삭제되었습니다.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        AdminActivityLog::whereIn('id', $ids)->delete();
        return redirect()->route('admin.admin.activity-log.index')->with('message', '선택한 로그가 삭제되었습니다.');
    }

    public function downloadCsv(Request $request)
    {
        $logs = AdminActivityLog::with('adminUser')->get();
        $filename = 'activity_logs_'.now()->format('Ymd_His').'.csv';
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', '관리자', '활동', '설명', 'IP', '생성일']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->adminUser?->name,
                $log->action,
                $log->description,
                $log->ip_address,
                $log->created_at,
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }
}
