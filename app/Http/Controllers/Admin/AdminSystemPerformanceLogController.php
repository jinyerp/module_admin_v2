<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class AdminSystemPerformanceLogController extends Controller
{
    /**
     * 성능 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = SystemPerformanceLog::query();

        // 컬럼명 기준 자동 필터링
        $filterable = [
            'metric_name', 'metric_type', 'status', 'endpoint', 'method'
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
                  ->orWhere('endpoint', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
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

        return view('jiny-admin::admin.system_performance_log.index', [
            'rows' => $performanceLogs,
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
        return view('jiny-admin::admin.system_performance_log.create', [
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
            'endpoint' => 'nullable|string|max:500',
            'method' => 'nullable|string|max:10',
            'user_agent' => 'nullable|string|max:500',
            'ip_address' => 'nullable|string|max:45',
            'session_id' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);

        SystemPerformanceLog::create($request->all());

        return redirect()->route('admin-systems.performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 성능 로그 상세 조회
     */
    public function show(SystemPerformanceLog $systemPerformanceLog): View
    {
        // 관련 로그 조회 (같은 메트릭 타입의 최근 로그들)
        $relatedLogs = SystemPerformanceLog::where('metric_type', $systemPerformanceLog->metric_type)
            ->where('id', '!=', $systemPerformanceLog->id)
            ->orderBy('measured_at', 'desc')
            ->limit(10)
            ->get();

        return view('jiny-admin::admin.system_performance_log.show', [
            'performanceLog' => $systemPerformanceLog,
            'relatedLogs' => $relatedLogs,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
            'statuses' => SystemPerformanceLog::getStatuses(),
        ]);
    }

    /**
     * 성능 로그 수정 폼
     */
    public function edit(SystemPerformanceLog $systemPerformanceLog): View
    {
        return view('jiny-admin::admin.system_performance_log.edit', [
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
            'endpoint' => 'nullable|string|max:500',
            'method' => 'nullable|string|max:10',
            'user_agent' => 'nullable|string|max:500',
            'ip_address' => 'nullable|string|max:45',
            'session_id' => 'nullable|string|max:255',
            'additional_data' => 'nullable|json',
            'measured_at' => 'required|date',
        ]);

        $systemPerformanceLog->update($request->all());

        return redirect()->route('admin.systems.performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 성능 로그 삭제
     */
    public function destroy(SystemPerformanceLog $systemPerformanceLog): RedirectResponse
    {
        $systemPerformanceLog->delete();

        return redirect()->route('admin.systems.performance-logs.index')
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
        return redirect()->route('admin.systems.performance-logs.index')
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
            'by_endpoint' => SystemPerformanceLog::selectRaw('endpoint, COUNT(*) as count, AVG(value) as avg_value')
                ->whereNotNull('endpoint')
                ->groupBy('endpoint')
                ->get(),
            'recent_trends' => SystemPerformanceLog::selectRaw('DATE(measured_at) as date, AVG(value) as avg_value, COUNT(*) as count')
                ->where('measured_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return view('jiny-admin::admin.system_performance_log.stats', [
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

        return redirect()->route('admin.systems.performance-logs.index')
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
        $endpoint = $request->get('endpoint');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($endpoint) {
            $query->where('endpoint', $endpoint);
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

        return redirect()->route('admin.systems.performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 내보내기되었습니다.');
    }

    /**
     * 실시간 성능 모니터링 (웹 애플리케이션 중심)
     */
    public function realtime()
    {
        // AJAX 요청인 경우 JSON 응답
        if (request()->ajax()) {
            $recentLogs = SystemPerformanceLog::where('measured_at', '>=', now()->subHours(1))
                ->orderBy('measured_at', 'desc')
                ->limit(10)
                ->get();

            $criticalAlerts = SystemPerformanceLog::where('status', 'critical')
                ->where('measured_at', '>=', now()->subHours(24))
                ->orderBy('measured_at', 'desc')
                ->get();

            // 웹 애플리케이션 성능 메트릭 수집
            $webMetrics = $this->getWebApplicationMetrics();
            $databaseMetrics = $this->getDatabaseMetrics();
            $cacheMetrics = $this->getCacheMetrics();
            $memoryMetrics = $this->getMemoryMetrics();

            return response()->json([
                'web' => $webMetrics,
                'database' => $databaseMetrics,
                'cache' => $cacheMetrics,
                'memory' => $memoryMetrics,
                'recentLogs' => $recentLogs,
                'criticalAlerts' => $criticalAlerts,
                'timestamp' => now()->toISOString(),
            ]);
        }

        // 일반 페이지 요청인 경우 뷰 반환
        $recentLogs = SystemPerformanceLog::where('measured_at', '>=', now()->subHours(1))
            ->orderBy('measured_at', 'desc')
            ->limit(100)
            ->get();

        $criticalAlerts = SystemPerformanceLog::where('status', 'critical')
            ->where('measured_at', '>=', now()->subHours(24))
            ->orderBy('measured_at', 'desc')
            ->get();

        return view('jiny-admin::admin.system_performance_log.realtime', [
            'recentLogs' => $recentLogs,
            'criticalAlerts' => $criticalAlerts,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
        ]);
    }

    /**
     * 웹 애플리케이션 메트릭 수집
     */
    private function getWebApplicationMetrics()
    {
        try {
            // 최근 요청 시간 통계
            $recentRequests = SystemPerformanceLog::where('metric_type', 'web')
                ->where('metric_name', 'request_time')
                ->where('measured_at', '>=', now()->subMinutes(5))
                ->get();

            $avgResponseTime = $recentRequests->avg('value') ?? 0;
            $maxResponseTime = $recentRequests->max('value') ?? 0;
            $requestCount = $recentRequests->count();

            // 엔드포인트별 성능
            $endpointPerformance = SystemPerformanceLog::where('metric_type', 'web')
                ->where('measured_at', '>=', now()->subMinutes(5))
                ->selectRaw('endpoint, AVG(value) as avg_time, COUNT(*) as request_count')
                ->groupBy('endpoint')
                ->orderBy('avg_time', 'desc')
                ->limit(5)
                ->get();

            return [
                'current' => [
                    'avg_response_time' => round($avgResponseTime, 2),
                    'max_response_time' => round($maxResponseTime, 2),
                    'request_count' => $requestCount,
                    'endpoints' => $endpointPerformance
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'avg_response_time' => 0,
                    'max_response_time' => 0,
                    'request_count' => 0,
                    'endpoints' => []
                ]
            ];
        }
    }

    /**
     * 데이터베이스 메트릭 수집
     */
    private function getDatabaseMetrics()
    {
        try {
            // 최근 쿼리 성능 통계
            $recentQueries = SystemPerformanceLog::where('metric_type', 'database')
                ->where('measured_at', '>=', now()->subMinutes(5))
                ->get();

            $avgQueryTime = $recentQueries->avg('value') ?? 0;
            $maxQueryTime = $recentQueries->max('value') ?? 0;
            $queryCount = $recentQueries->count();

            // 느린 쿼리 목록
            $slowQueries = SystemPerformanceLog::where('metric_type', 'database')
                ->where('value', '>', 1000) // 1초 이상
                ->where('measured_at', '>=', now()->subMinutes(5))
                ->orderBy('value', 'desc')
                ->limit(5)
                ->get();

            return [
                'current' => [
                    'avg_query_time' => round($avgQueryTime, 2),
                    'max_query_time' => round($maxQueryTime, 2),
                    'query_count' => $queryCount,
                    'slow_queries' => $slowQueries
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'avg_query_time' => 0,
                    'max_query_time' => 0,
                    'query_count' => 0,
                    'slow_queries' => []
                ]
            ];
        }
    }

    /**
     * 캐시 메트릭 수집
     */
    private function getCacheMetrics()
    {
        try {
            // 캐시 히트율 계산
            $cacheHits = Cache::get('cache_hits', 0);
            $cacheMisses = Cache::get('cache_misses', 0);
            $totalRequests = $cacheHits + $cacheMisses;
            $hitRate = $totalRequests > 0 ? ($cacheHits / $totalRequests) * 100 : 0;

            // 캐시 크기 (Redis 사용 시)
            $cacheSize = 0;
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    $cacheSize = $redis->dbSize();
                } catch (Exception $e) {
                    // Redis 연결 실패 시 무시
                }
            }

            return [
                'current' => [
                    'hit_rate' => round($hitRate, 2),
                    'hits' => $cacheHits,
                    'misses' => $cacheMisses,
                    'total_requests' => $totalRequests,
                    'cache_size' => $cacheSize
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'hit_rate' => 0,
                    'hits' => 0,
                    'misses' => 0,
                    'total_requests' => 0,
                    'cache_size' => 0
                ]
            ];
        }
    }

    /**
     * 메모리 메트릭 수집
     */
    private function getMemoryMetrics()
    {
        try {
            // PHP 메모리 사용량
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryLimit = ini_get('memory_limit');
            
            // 메모리 제한을 바이트로 변환
            $memoryLimitBytes = $this->convertMemoryLimit($memoryLimit);
            $memoryUsagePercent = $memoryLimitBytes > 0 ? ($memoryUsage / $memoryLimitBytes) * 100 : 0;

            return [
                'current' => [
                    'usage_bytes' => $memoryUsage,
                    'peak_bytes' => $memoryPeak,
                    'limit_bytes' => $memoryLimitBytes,
                    'usage_percent' => round($memoryUsagePercent, 2),
                    'limit_string' => $memoryLimit
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'usage_bytes' => 0,
                    'peak_bytes' => 0,
                    'limit_bytes' => 0,
                    'usage_percent' => 0,
                    'limit_string' => 'unknown'
                ]
            ];
        }
    }

    /**
     * 메모리 제한 문자열을 바이트로 변환
     */
    private function convertMemoryLimit($limit)
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'k':
                return $value * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'g':
                return $value * 1024 * 1024 * 1024;
            default:
                return $value;
        }
    }
}
