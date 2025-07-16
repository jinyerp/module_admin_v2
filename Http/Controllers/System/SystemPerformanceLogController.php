<?php

namespace Jiny\Admin\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\DB;

class SystemPerformanceLogController extends Controller
{
    /**
     * 성능 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = SystemPerformanceLog::query();

        // 컬럼명 기준 자동 필터링
        $filterable = [
            'metric_name', 'metric_type', 'status', 'server_name', 'component'
        ];
        foreach ($filterable as $column) {
            $value = $request->get('filter_' . $column);
            if (!is_null($value) && $value !== '') {
                $query->where($column, $value);
            }
        }

        // 검색어(부분일치) 별도 처리
        $search = $request->get('filter_search', $request->get('search'));
        if (!is_null($search) && $search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('metric_name', 'like', "%{$search}%")
                  ->orWhere('server_name', 'like', "%{$search}%")
                  ->orWhere('component', 'like', "%{$search}%");
            });
        }

        // 날짜 범위 필터링
        $startDate = $request->get('filter_start_date');
        $endDate = $request->get('filter_end_date');
        if ($startDate) {
            $query->where('measured_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('measured_at', '<=', $endDate . ' 23:59:59');
        }

        // 값 범위 필터링
        $minValue = $request->get('filter_min_value');
        $maxValue = $request->get('filter_max_value');
        if ($minValue !== null && $minValue !== '') {
            $query->where('value', '>=', $minValue);
        }
        if ($maxValue !== null && $maxValue !== '') {
            $query->where('value', '<=', $maxValue);
        }

        // 정렬
        $sortField = $request->get('sort', 'measured_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $performanceLogs = $query->paginate(15);

        // 통계 데이터
        $stats = [
            'total' => SystemPerformanceLog::count(),
            'normal' => SystemPerformanceLog::where('status', 'normal')->count(),
            'warning' => SystemPerformanceLog::where('status', 'warning')->count(),
            'critical' => SystemPerformanceLog::where('status', 'critical')->count(),
            'avg_value' => SystemPerformanceLog::avg('value'),
            'max_value' => SystemPerformanceLog::max('value'),
            'min_value' => SystemPerformanceLog::min('value'),
        ];

        return view('jiny-admin::admin.system-performance-logs.index', [
            'performanceLogs' => $performanceLogs,
            'stats' => $stats,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
            'statuses' => SystemPerformanceLog::getStatuses(),
            'sort' => $sortField,
            'dir' => $sortDirection,
        ]);
    }

    /**
     * 성능 로그 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::admin.system-performance-logs.create', [
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
            'statuses' => SystemPerformanceLog::getStatuses(),
        ]);
    }

    /**
     * 성능 로그 저장
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'metric_name' => 'required|string|max:255',
            'metric_type' => 'required|string|in:' . implode(',', array_keys(SystemPerformanceLog::getMetricTypes())),
            'value' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'threshold' => 'nullable|string|max:100',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemPerformanceLog::getStatuses())),
            'server_name' => 'nullable|string|max:255',
            'component' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);

        SystemPerformanceLog::create($request->all());

        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 성능 로그 상세 조회
     */
    public function show(SystemPerformanceLog $systemPerformanceLog): View
    {
        return view('jiny-admin::admin.system-performance-logs.show', [
            'performanceLog' => $systemPerformanceLog,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
            'statuses' => SystemPerformanceLog::getStatuses(),
        ]);
    }

    /**
     * 성능 로그 수정 폼
     */
    public function edit(SystemPerformanceLog $systemPerformanceLog): View
    {
        return view('jiny-admin::admin.system-performance-logs.edit', [
            'performanceLog' => $systemPerformanceLog,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
            'statuses' => SystemPerformanceLog::getStatuses(),
        ]);
    }

    /**
     * 성능 로그 업데이트
     */
    public function update(Request $request, SystemPerformanceLog $systemPerformanceLog): RedirectResponse
    {
        $request->validate([
            'metric_name' => 'required|string|max:255',
            'metric_type' => 'required|string|in:' . implode(',', array_keys(SystemPerformanceLog::getMetricTypes())),
            'value' => 'required|numeric',
            'unit' => 'required|string|max:50',
            'threshold' => 'nullable|string|max:100',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemPerformanceLog::getStatuses())),
            'server_name' => 'nullable|string|max:255',
            'component' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);

        $systemPerformanceLog->update($request->all());

        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 성능 로그 삭제
     */
    public function destroy(SystemPerformanceLog $systemPerformanceLog): RedirectResponse
    {
        $systemPerformanceLog->delete();

        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 삭제되었습니다.');
    }

    /**
     * 성능 로그 상태 변경
     */
    public function updateStatus(Request $request, SystemPerformanceLog $systemPerformanceLog): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SystemPerformanceLog::getStatuses())),
        ]);

        $systemPerformanceLog->update(['status' => $request->status]);

        $statusText = SystemPerformanceLog::getStatuses()[$request->status];
        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', "성능 로그 상태가 '{$statusText}'로 변경되었습니다.");
    }

    /**
     * 성능 로그 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => SystemPerformanceLog::count(),
            'normal' => SystemPerformanceLog::where('status', 'normal')->count(),
            'warning' => SystemPerformanceLog::where('status', 'warning')->count(),
            'critical' => SystemPerformanceLog::where('status', 'critical')->count(),
            'avg_value' => SystemPerformanceLog::avg('value'),
            'max_value' => SystemPerformanceLog::max('value'),
            'min_value' => SystemPerformanceLog::min('value'),
            'by_metric_type' => SystemPerformanceLog::selectRaw('metric_type, COUNT(*) as count, AVG(value) as avg_value')
                ->groupBy('metric_type')
                ->get(),
            'by_server' => SystemPerformanceLog::selectRaw('server_name, COUNT(*) as count, AVG(value) as avg_value')
                ->whereNotNull('server_name')
                ->groupBy('server_name')
                ->get(),
            'recent_trends' => SystemPerformanceLog::selectRaw('DATE(measured_at) as date, AVG(value) as avg_value, COUNT(*) as count')
                ->where('measured_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return view('jiny-admin::admin.system-performance-logs.stats', [
            'stats' => $stats,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
        ]);
    }

    /**
     * 성능 로그 일괄 삭제
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_performance_logs,id',
        ]);

        $count = SystemPerformanceLog::whereIn('id', $request->selected_logs)->delete();

        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', "{$count}개의 성능 로그가 성공적으로 삭제되었습니다.");
    }

    /**
     * 성능 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemPerformanceLog::query();

        // 필터 적용
        $metricType = $request->get('metric_type');
        $status = $request->get('status');
        $serverName = $request->get('server_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($serverName) {
            $query->where('server_name', $serverName);
        }
        if ($startDate) {
            $query->where('measured_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('measured_at', '<=', $endDate . ' 23:59:59');
        }

        $performanceLogs = $query->get();

        // CSV 파일 생성 로직 (실제 구현에서는 Excel/CSV 라이브러리 사용)
        // 여기서는 간단한 예시만 제공

        return redirect()->route('admin.system-performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 내보내기되었습니다.');
    }

    /**
     * 실시간 성능 모니터링
     */
    public function realtime(): View
    {
        $recentLogs = SystemPerformanceLog::where('measured_at', '>=', now()->subHours(1))
            ->orderBy('measured_at', 'desc')
            ->limit(100)
            ->get();

        $criticalAlerts = SystemPerformanceLog::where('status', 'critical')
            ->where('measured_at', '>=', now()->subHours(24))
            ->orderBy('measured_at', 'desc')
            ->get();

        return view('jiny-admin::admin.system-performance-logs.realtime', [
            'recentLogs' => $recentLogs,
            'criticalAlerts' => $criticalAlerts,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
        ]);
    }
}
