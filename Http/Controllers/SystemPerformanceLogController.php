<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\SystemPerformanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemPerformanceLogController extends Controller
{
    public function index(Request $request)
    {
        $route = 'admin.system.performance-logs.';
        $rows = SystemPerformanceLog::orderByDesc('id')->paginate(20);
        return view('jiny.admin::system_performance_log.index', [
            'rows' => $rows,
            'route' => $route,
        ]);
    }

    public function show($id)
    {
        $route = 'admin.system.performance-logs.';
        $log = SystemPerformanceLog::findOrFail($id);
        return view('jiny.admin::system_performance_log.show', [
            'log' => $log,
            'route' => $route,
        ]);
    }

    public function create()
    {
        $route = 'admin.system.performance-logs.';
        return view('jiny.admin::system_performance_log.create', [
            'route' => $route,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'metric_name' => 'required|string|max:255',
            'metric_type' => 'required|string|max:255',
            'value' => 'required|numeric|between:0,999999.9999',
            'unit' => 'required|string|max:255',
            'threshold' => 'nullable|string|max:255',
            'status' => 'required|in:normal,warning,critical',
            'server_name' => 'nullable|string|max:255',
            'component' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);
        SystemPerformanceLog::create($data);
        return redirect()->route('admin.system.performance-logs.index')->with('message', '성능 로그가 등록되었습니다.');
    }

    public function edit($id)
    {
        $route = 'admin.system.performance-logs.';
        $log = SystemPerformanceLog::findOrFail($id);
        return view('jiny.admin::system_performance_log.edit', [
            'log' => $log,
            'route' => $route,
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'metric_name' => 'required|string|max:255',
            'metric_type' => 'required|string|max:255',
            'value' => 'required|numeric|between:0,999999.9999',
            'unit' => 'required|string|max:255',
            'threshold' => 'nullable|string|max:255',
            'status' => 'required|in:normal,warning,critical',
            'server_name' => 'nullable|string|max:255',
            'component' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);
        $log = SystemPerformanceLog::findOrFail($id);
        $log->update($data);
        return redirect()->route('admin.system.performance-logs.index')->with('message', '성능 로그가 수정되었습니다.');
    }

    public function destroy($id)
    {
        $log = SystemPerformanceLog::findOrFail($id);
        $log->delete();
        return redirect()->route('admin.system.performance-logs.index')->with('message', '성능 로그가 삭제되었습니다.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        SystemPerformanceLog::whereIn('id', $ids)->delete();
        return redirect()->route('admin.system.performance-logs.index')->with('message', '선택한 로그가 삭제되었습니다.');
    }

    public function downloadCsv(Request $request)
    {
        $logs = SystemPerformanceLog::all();
        $filename = 'system_performance_logs_'.now()->format('Ymd_His').'.csv';
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', '메트릭명', '타입', '값', '단위', '임계값', '상태', '서버명', '컴포넌트', '추가데이터', '측정시각', '생성일']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->metric_name,
                $log->metric_type,
                $log->value,
                $log->unit,
                $log->threshold,
                $log->status,
                $log->server_name,
                $log->component,
                json_encode($log->additional_data),
                $log->measured_at,
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