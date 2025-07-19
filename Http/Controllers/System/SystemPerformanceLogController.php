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

        return redirect()->route('admin.systems.performance-logs.index')
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

        return view('jiny-admin::admin.system-performance-logs.show', [
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

        return redirect()->route('admin.systems.performance-logs.index')
            ->with('success', '성능 로그가 성공적으로 내보내기되었습니다.');
    }

    /**
     * 실시간 성능 모니터링
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

            // 실제 시스템 메트릭 수집
            $cpuData = $this->getCpuUsage();
            $memoryData = $this->getMemoryUsage();
            $diskData = $this->getDiskUsage();
            $networkData = $this->getNetworkUsage();

            return response()->json([
                'cpu' => $cpuData,
                'memory' => $memoryData,
                'disk' => $diskData,
                'network' => $networkData,
                'recentLogs' => $recentLogs,
                'criticalAlerts' => $criticalAlerts,
                'timestamp' => now()->toISOString(),
                'debug' => [
                    'os_info' => $this->getOsInfo(),
                    'disk_raw' => $diskData,
                    'network_raw' => $networkData
                ]
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

        return view('jiny-admin::admin.system-performance-logs.realtime', [
            'recentLogs' => $recentLogs,
            'criticalAlerts' => $criticalAlerts,
            'metricTypes' => SystemPerformanceLog::getMetricTypes(),
        ]);
    }

    /**
     * CPU 사용률 측정
     */
    private function getCpuUsage()
    {
        try {
            $osInfo = $this->getOsInfo();
            
            switch ($osInfo['family']) {
                case 'Windows':
                    return $this->getWindowsCpuUsage();
                case 'Linux':
                    return $this->getLinuxCpuUsage($osInfo['distribution']);
                case 'Darwin': // macOS
                    return $this->getMacCpuUsage();
                default:
                    return $this->getGenericCpuUsage();
            }
        } catch (Exception $e) {
            return [
                'current' => [
                    '1min' => 0,
                    '5min' => 0,
                    '15min' => 0
                ]
            ];
        }
    }

    /**
     * Windows CPU 사용률
     */
    private function getWindowsCpuUsage()
    {
        try {
            // PowerShell을 사용하여 더 정확한 CPU 사용률 측정
            $psCommand = 'Get-Counter "\Processor(_Total)\% Processor Time" -SampleInterval 1 -MaxSamples 1 | Select-Object -ExpandProperty CounterSamples | Select-Object -ExpandProperty CookedValue';
            $cpuUsage = shell_exec('powershell -Command "' . $psCommand . '" 2>&1');
            
            if (is_numeric(trim($cpuUsage))) {
                $usage = (float)trim($cpuUsage);
            } else {
                // PowerShell이 실패하면 WMI 사용
                $cpuUsage = shell_exec('wmic cpu get loadpercentage /value 2>&1');
                if (preg_match('/LoadPercentage=(\d+)/', $cpuUsage, $matches)) {
                    $usage = (int)$matches[1];
                } else {
                    $usage = 0;
                }
            }
            
            return [
                'current' => [
                    '1min' => round($usage, 2),
                    '5min' => round($usage, 2),
                    '15min' => round($usage, 2),
                    'instant' => round($usage, 2)
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    '1min' => 0,
                    '5min' => 0,
                    '15min' => 0,
                    'instant' => 0
                ]
            ];
        }
    }

    /**
     * Linux CPU 사용률 (배포판별 최적화)
     */
    private function getLinuxCpuUsage($distribution)
    {
        switch (strtolower($distribution)) {
            case 'ubuntu':
            case 'debian':
                return $this->getUbuntuCpuUsage();
            case 'centos':
            case 'redhat':
            case 'fedora':
                return $this->getCentosCpuUsage();
            case 'alpine':
                return $this->getAlpineCpuUsage();
            default:
                return $this->getGenericLinuxCpuUsage();
        }
    }

    /**
     * Ubuntu/Debian CPU 사용률
     */
    private function getUbuntuCpuUsage()
    {
        // Ubuntu는 /proc/loadavg와 /proc/stat 모두 사용
        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        // /proc/stat에서 더 정확한 CPU 사용률 계산
        $stat = file_get_contents('/proc/stat');
        $lines = explode("\n", $stat);
        $cpuLine = explode(" ", preg_replace('/\s+/', ' ', trim($lines[0])));
        
        $total = array_sum(array_slice($cpuLine, 1));
        $idle = $cpuLine[4];
        $usage = 100 - (($idle / $total) * 100);
        
        return [
            'current' => [
                '1min' => min(100, ($load[0] / $cpuCount) * 100),
                '5min' => min(100, ($load[1] / $cpuCount) * 100),
                '15min' => min(100, ($load[2] / $cpuCount) * 100),
                'instant' => round($usage, 2)
            ]
        ];
    }

    /**
     * CentOS/RHEL CPU 사용률
     */
    private function getCentosCpuUsage()
    {
        // CentOS는 vmstat 명령어 사용
        $vmstat = shell_exec('vmstat 1 2 2>/dev/null | tail -1');
        if (preg_match('/\s+(\d+)\s+(\d+)\s+(\d+)/', $vmstat, $matches)) {
            $idle = (int)$matches[3];
            $usage = 100 - $idle;
        } else {
            // vmstat이 없으면 기본 방법 사용
            $load = sys_getloadavg();
            $cpuCount = $this->getCpuCount();
            $usage = min(100, ($load[0] / $cpuCount) * 100);
        }
        
        return [
            'current' => [
                '1min' => $usage,
                '5min' => $usage,
                '15min' => $usage,
                'instant' => round($usage, 2)
            ]
        ];
    }

    /**
     * Alpine Linux CPU 사용률
     */
    private function getAlpineCpuUsage()
    {
        // Alpine은 기본적으로 /proc/loadavg 사용
        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        return [
            'current' => [
                '1min' => min(100, ($load[0] / $cpuCount) * 100),
                '5min' => min(100, ($load[1] / $cpuCount) * 100),
                '15min' => min(100, ($load[2] / $cpuCount) * 100)
            ]
        ];
    }

    /**
     * 일반 Linux CPU 사용률
     */
    private function getGenericLinuxCpuUsage()
    {
        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        return [
            'current' => [
                '1min' => min(100, ($load[0] / $cpuCount) * 100),
                '5min' => min(100, ($load[1] / $cpuCount) * 100),
                '15min' => min(100, ($load[2] / $cpuCount) * 100)
            ]
        ];
    }

    /**
     * macOS CPU 사용률
     */
    private function getMacCpuUsage()
    {
        // macOS는 top 명령어 사용
        $top = shell_exec('top -l 1 -n 0 | grep "CPU usage" 2>/dev/null');
        if (preg_match('/(\d+\.?\d*)% user/', $top, $matches)) {
            $usage = (float)$matches[1];
        } else {
            $usage = 0;
        }
        
        return [
            'current' => [
                '1min' => $usage,
                '5min' => $usage,
                '15min' => $usage
            ]
        ];
    }

    /**
     * 일반 CPU 사용률 (fallback)
     */
    private function getGenericCpuUsage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpuCount = $this->getCpuCount();
            
            return [
                'current' => [
                    '1min' => min(100, ($load[0] / $cpuCount) * 100),
                    '5min' => min(100, ($load[1] / $cpuCount) * 100),
                    '15min' => min(100, ($load[2] / $cpuCount) * 100)
                ]
            ];
        }
        
        return [
            'current' => [
                '1min' => 0,
                '5min' => 0,
                '15min' => 0
            ]
        ];
    }

    /**
     * 메모리 사용률 측정
     */
    private function getMemoryUsage()
    {
        try {
            $osInfo = $this->getOsInfo();
            
            switch ($osInfo['family']) {
                case 'Windows':
                    return $this->getWindowsMemoryUsage();
                case 'Linux':
                    return $this->getLinuxMemoryUsage($osInfo['distribution']);
                case 'Darwin': // macOS
                    return $this->getMacMemoryUsage();
                default:
                    return $this->getGenericMemoryUsage();
            }
        } catch (Exception $e) {
            return [
                'current' => [
                    'usage_percent' => 0,
                    'total' => 0,
                    'used' => 0,
                    'free' => 0
                ]
            ];
        }
    }

    /**
     * Windows 메모리 사용률
     */
    private function getWindowsMemoryUsage()
    {
        try {
            // PowerShell을 사용하여 더 정확한 메모리 정보 측정
            $psCommand = 'Get-Counter "\Memory\Available MBytes" -SampleInterval 1 -MaxSamples 1 | Select-Object -ExpandProperty CounterSamples | Select-Object -ExpandProperty CookedValue';
            $availableMB = shell_exec('powershell -Command "' . $psCommand . '" 2>&1');
            
            if (is_numeric(trim($availableMB))) {
                $available = (float)trim($availableMB) * 1024 * 1024; // MB to bytes
                
                // 총 메모리는 WMI로 가져오기
                $memoryInfo = shell_exec('wmic computersystem get TotalPhysicalMemory /value 2>&1');
                $total = 0;
                if (preg_match('/TotalPhysicalMemory=(\d+)/', $memoryInfo, $matches)) {
                    $total = (int)$matches[1];
                }
                
                $used = $total - $available;
                $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
                
                return [
                    'current' => [
                        'usage_percent' => round($usagePercent, 2),
                        'total' => $total,
                        'used' => $used,
                        'free' => $available
                    ]
                ];
            } else {
                // PowerShell이 실패하면 WMI 사용
                $memoryInfo = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value 2>&1');
                $total = 0;
                $free = 0;
                
                if (preg_match('/TotalVisibleMemorySize=(\d+)/', $memoryInfo, $matches)) {
                    $total = (int)$matches[1] * 1024; // KB to bytes
                }
                if (preg_match('/FreePhysicalMemory=(\d+)/', $memoryInfo, $matches)) {
                    $free = (int)$matches[1] * 1024; // KB to bytes
                }
                
                $used = $total - $free;
                $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
                
                return [
                    'current' => [
                        'usage_percent' => round($usagePercent, 2),
                        'total' => $total,
                        'used' => $used,
                        'free' => $free
                    ]
                ];
            }
        } catch (Exception $e) {
            return [
                'current' => [
                    'usage_percent' => 0,
                    'total' => 0,
                    'used' => 0,
                    'free' => 0
                ]
            ];
        }
    }

    /**
     * Linux 메모리 사용률 (배포판별 최적화)
     */
    private function getLinuxMemoryUsage($distribution)
    {
        switch (strtolower($distribution)) {
            case 'ubuntu':
            case 'debian':
                return $this->getUbuntuMemoryUsage();
            case 'centos':
            case 'redhat':
            case 'fedora':
                return $this->getCentosMemoryUsage();
            case 'alpine':
                return $this->getAlpineMemoryUsage();
            default:
                return $this->getGenericLinuxMemoryUsage();
        }
    }

    /**
     * Ubuntu/Debian 메모리 사용률
     */
    private function getUbuntuMemoryUsage()
    {
        $memoryInfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $memoryInfo, $totalMatch);
        preg_match('/MemAvailable:\s+(\d+)/', $memoryInfo, $availableMatch);
        
        $total = (int)$totalMatch[1] * 1024; // KB to bytes
        $available = (int)$availableMatch[1] * 1024; // KB to bytes
        $used = $total - $available;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
        
        return [
            'current' => [
                'usage_percent' => round($usagePercent, 2),
                'total' => $total,
                'used' => $used,
                'free' => $available
            ]
        ];
    }

    /**
     * CentOS/RHEL 메모리 사용률
     */
    private function getCentosMemoryUsage()
    {
        // CentOS는 free 명령어 사용
        $free = shell_exec('free -b 2>/dev/null | grep Mem');
        if (preg_match('/Mem\s+(\d+)\s+(\d+)\s+(\d+)/', $free, $matches)) {
            $total = (int)$matches[1];
            $used = (int)$matches[2];
            $free = (int)$matches[3];
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
        } else {
            // free 명령어가 없으면 /proc/meminfo 사용
            return $this->getUbuntuMemoryUsage();
        }
        
        return [
            'current' => [
                'usage_percent' => round($usagePercent, 2),
                'total' => $total,
                'used' => $used,
                'free' => $free
            ]
        ];
    }

    /**
     * Alpine Linux 메모리 사용률
     */
    private function getAlpineMemoryUsage()
    {
        // Alpine은 /proc/meminfo 사용
        $memoryInfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $memoryInfo, $totalMatch);
        preg_match('/MemFree:\s+(\d+)/', $memoryInfo, $freeMatch);
        
        $total = (int)$totalMatch[1] * 1024; // KB to bytes
        $free = (int)$freeMatch[1] * 1024; // KB to bytes
        $used = $total - $free;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
        
        return [
            'current' => [
                'usage_percent' => round($usagePercent, 2),
                'total' => $total,
                'used' => $used,
                'free' => $free
            ]
        ];
    }

    /**
     * 일반 Linux 메모리 사용률
     */
    private function getGenericLinuxMemoryUsage()
    {
        $memoryInfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $memoryInfo, $totalMatch);
        preg_match('/MemAvailable:\s+(\d+)/', $memoryInfo, $availableMatch);
        
        if (!$availableMatch) {
            // MemAvailable이 없으면 MemFree 사용
            preg_match('/MemFree:\s+(\d+)/', $memoryInfo, $freeMatch);
            $available = (int)$freeMatch[1] * 1024;
        } else {
            $available = (int)$availableMatch[1] * 1024;
        }
        
        $total = (int)$totalMatch[1] * 1024; // KB to bytes
        $used = $total - $available;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
        
        return [
            'current' => [
                'usage_percent' => round($usagePercent, 2),
                'total' => $total,
                'used' => $used,
                'free' => $available
            ]
        ];
    }

    /**
     * macOS 메모리 사용률
     */
    private function getMacMemoryUsage()
    {
        $vmstat = shell_exec('vm_stat 2>/dev/null');
        $lines = explode("\n", $vmstat);
        
        $total = 0;
        $free = 0;
        
        foreach ($lines as $line) {
            if (preg_match('/Mach Virtual Memory Statistics:/', $line)) {
                continue;
            }
            if (preg_match('/Pages free:\s+(\d+)/', $line, $matches)) {
                $free = (int)$matches[1] * 4096; // pages to bytes
            }
            if (preg_match('/Pages active:\s+(\d+)/', $line, $matches)) {
                $active = (int)$matches[1] * 4096;
            }
            if (preg_match('/Pages inactive:\s+(\d+)/', $line, $matches)) {
                $inactive = (int)$matches[1] * 4096;
            }
        }
        
        $total = $free + $active + $inactive;
        $used = $total - $free;
        $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
        
        return [
            'current' => [
                'usage_percent' => round($usagePercent, 2),
                'total' => $total,
                'used' => $used,
                'free' => $free
            ]
        ];
    }

    /**
     * 일반 메모리 사용률 (fallback)
     */
    private function getGenericMemoryUsage()
    {
        return [
            'current' => [
                'usage_percent' => 0,
                'total' => 0,
                'used' => 0,
                'free' => 0
            ]
        ];
    }

    /**
     * 디스크 사용률 측정
     */
    private function getDiskUsage()
    {
        try {
            $osInfo = $this->getOsInfo();
            
            switch ($osInfo['family']) {
                case 'Windows':
                    return $this->getWindowsDiskUsage();
                case 'Linux':
                    return $this->getLinuxDiskUsage($osInfo['distribution']);
                case 'Darwin': // macOS
                    return $this->getMacDiskUsage();
                default:
                    return $this->getGenericDiskUsage();
            }
        } catch (Exception $e) {
            return [
                'current' => [
                    'usage_percent' => 0,
                    'total' => 0,
                    'used' => 0,
                    'free' => 0
                ]
            ];
        }
    }

    /**
     * Windows 디스크 사용률
     */
    private function getWindowsDiskUsage()
    {
        try {
            // 더 간단한 PowerShell 명령어 사용
            $psCommand = 'Get-WmiObject -Class Win32_LogicalDisk | Where-Object {$_.DeviceID -eq "C:"} | Select-Object @{Name="Size";Expression={$_.Size}},@{Name="FreeSpace";Expression={$_.FreeSpace}} | ConvertTo-Json';
            $diskInfo = shell_exec('powershell -Command "' . $psCommand . '" 2>&1');
            
            if ($diskInfo && $diskInfo !== 'null' && $diskInfo !== '') {
                $diskData = json_decode($diskInfo, true);
                if ($diskData && isset($diskData['Size']) && isset($diskData['FreeSpace'])) {
                    $total = (int)$diskData['Size'];
                    $free = (int)$diskData['FreeSpace'];
                    $used = $total - $free;
                    $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
                    
                    return [
                        'current' => [
                            'usage_percent' => round($usagePercent, 2),
                            'total' => $total,
                            'used' => $used,
                            'free' => $free
                        ]
                    ];
                }
            }
            
            // PowerShell이 실패하면 WMI 사용
            $diskInfo = shell_exec('wmic logicaldisk where "DeviceID=\'C:\'" get size,freespace /value 2>&1');
            $total = 0;
            $free = 0;
            
            if (preg_match('/Size=(\d+)/', $diskInfo, $matches)) {
                $total = (int)$matches[1];
            }
            if (preg_match('/FreeSpace=(\d+)/', $diskInfo, $matches)) {
                $free = (int)$matches[1];
            }
            
            $used = $total - $free;
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'current' => [
                    'usage_percent' => round($usagePercent, 2),
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]
            ];
        } catch (Exception $e) {
            // 에러 발생 시 기본값 반환
            return [
                'current' => [
                    'usage_percent' => 0,
                    'total' => 0,
                    'used' => 0,
                    'free' => 0
                ]
            ];
        }
    }

    /**
     * Linux 디스크 사용률 (배포판별 최적화)
     */
    private function getLinuxDiskUsage($distribution)
    {
        switch (strtolower($distribution)) {
            case 'ubuntu':
            case 'debian':
                return $this->getUbuntuDiskUsage();
            case 'centos':
            case 'redhat':
            case 'fedora':
                return $this->getCentosDiskUsage();
            case 'alpine':
                return $this->getAlpineDiskUsage();
            default:
                return $this->getGenericLinuxDiskUsage();
        }
    }

    /**
     * Ubuntu/Debian 디스크 사용률
     */
    private function getUbuntuDiskUsage()
    {
        $diskInfo = shell_exec('df / 2>/dev/null | tail -1');
        if (preg_match('/\s+(\d+)\s+(\d+)\s+(\d+)/', $diskInfo, $matches)) {
            $total = (int)$matches[1] * 1024; // KB to bytes
            $used = (int)$matches[2] * 1024; // KB to bytes
            $free = (int)$matches[3] * 1024; // KB to bytes
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'current' => [
                    'usage_percent' => round($usagePercent, 2),
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]
            ];
        }
        
        return $this->getGenericLinuxDiskUsage();
    }

    /**
     * CentOS/RHEL 디스크 사용률
     */
    private function getCentosDiskUsage()
    {
        // CentOS는 df 명령어 사용
        $diskInfo = shell_exec('df -h / 2>/dev/null | tail -1');
        if (preg_match('/\s+(\d+[KMG])\s+(\d+[KMG])\s+(\d+[KMG])/', $diskInfo, $matches)) {
            $total = $this->convertToBytes($matches[1]);
            $used = $this->convertToBytes($matches[2]);
            $free = $this->convertToBytes($matches[3]);
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'current' => [
                    'usage_percent' => round($usagePercent, 2),
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]
            ];
        }
        
        return $this->getUbuntuDiskUsage();
    }

    /**
     * Alpine Linux 디스크 사용률
     */
    private function getAlpineDiskUsage()
    {
        // Alpine은 기본 df 명령어 사용
        return $this->getUbuntuDiskUsage();
    }

    /**
     * 일반 Linux 디스크 사용률
     */
    private function getGenericLinuxDiskUsage()
    {
        $diskInfo = shell_exec('df / 2>/dev/null | tail -1');
        if (preg_match('/\s+(\d+)\s+(\d+)\s+(\d+)/', $diskInfo, $matches)) {
            $total = (int)$matches[1] * 1024; // KB to bytes
            $used = (int)$matches[2] * 1024; // KB to bytes
            $free = (int)$matches[3] * 1024; // KB to bytes
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'current' => [
                    'usage_percent' => round($usagePercent, 2),
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]
            ];
        }
        
        return [
            'current' => [
                'usage_percent' => 0,
                'total' => 0,
                'used' => 0,
                'free' => 0
            ]
        ];
    }

    /**
     * macOS 디스크 사용률
     */
    private function getMacDiskUsage()
    {
        $diskInfo = shell_exec('df / 2>/dev/null | tail -1');
        if (preg_match('/\s+(\d+)\s+(\d+)\s+(\d+)/', $diskInfo, $matches)) {
            $total = (int)$matches[1] * 512; // 512-byte blocks to bytes
            $used = (int)$matches[2] * 512;
            $free = (int)$matches[3] * 512;
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'current' => [
                    'usage_percent' => round($usagePercent, 2),
                    'total' => $total,
                    'used' => $used,
                    'free' => $free
                ]
            ];
        }
        
        return [
            'current' => [
                'usage_percent' => 0,
                'total' => 0,
                'used' => 0,
                'free' => 0
            ]
        ];
    }

    /**
     * 일반 디스크 사용률 (fallback)
     */
    private function getGenericDiskUsage()
    {
        return [
            'current' => [
                'usage_percent' => 0,
                'total' => 0,
                'used' => 0,
                'free' => 0
            ]
        ];
    }

    /**
     * 네트워크 사용률 측정
     */
    private function getNetworkUsage()
    {
        try {
            $osInfo = $this->getOsInfo();
            
            switch ($osInfo['family']) {
                case 'Windows':
                    return $this->getWindowsNetworkUsage();
                case 'Linux':
                    return $this->getLinuxNetworkUsage($osInfo['distribution']);
                case 'Darwin': // macOS
                    return $this->getMacNetworkUsage();
                default:
                    return $this->getGenericNetworkUsage();
            }
        } catch (Exception $e) {
            return [
                'current' => [
                    'bytes_sent' => 0,
                    'bytes_received' => 0,
                    'packets_sent' => 0,
                    'packets_received' => 0
                ]
            ];
        }
    }

    /**
     * Windows 네트워크 사용률
     */
    private function getWindowsNetworkUsage()
    {
        try {
            // 더 간단한 PowerShell 명령어 사용
            $psCommand = 'Get-Counter "\Network Interface(*)\Bytes Total/sec" -SampleInterval 1 -MaxSamples 1 | Select-Object -ExpandProperty CounterSamples | Measure-Object -Property CookedValue -Sum | Select-Object -ExpandProperty Sum';
            $networkUsage = shell_exec('powershell -Command "' . $psCommand . '" 2>&1');
            
            if (is_numeric(trim($networkUsage))) {
                $bytesPerSec = (float)trim($networkUsage);
            } else {
                // 대안 방법: netstat 사용
                $netstat = shell_exec('netstat -e 2>&1');
                if (preg_match('/\s+(\d+)\s+(\d+)/', $netstat, $matches)) {
                    $bytesPerSec = ((int)$matches[1] + (int)$matches[2]) / 1024; // KB/s
                } else {
                    $bytesPerSec = 0;
                }
            }
            
            return [
                'current' => [
                    'bytes_sent' => $bytesPerSec,
                    'bytes_received' => $bytesPerSec,
                    'packets_sent' => 0,
                    'packets_received' => 0,
                    'bytes_per_sec' => $bytesPerSec
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'bytes_sent' => 0,
                    'bytes_received' => 0,
                    'packets_sent' => 0,
                    'packets_received' => 0,
                    'bytes_per_sec' => 0
                ]
            ];
        }
    }

    /**
     * Linux 네트워크 사용률
     */
    private function getLinuxNetworkUsage($distribution)
    {
        try {
            $networkInfo = file_get_contents('/proc/net/dev');
            $lines = explode("\n", $networkInfo);
            $totalBytesSent = 0;
            $totalBytesReceived = 0;
            
            foreach ($lines as $line) {
                if (preg_match('/^\s*(\w+):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches)) {
                    $interface = $matches[1];
                    if ($interface !== 'lo') { // loopback 제외
                        $totalBytesReceived += (int)$matches[2];
                        $totalBytesSent += (int)$matches[3];
                    }
                }
            }
            
            return [
                'current' => [
                    'bytes_sent' => $totalBytesSent,
                    'bytes_received' => $totalBytesReceived,
                    'packets_sent' => 0,
                    'packets_received' => 0
                ]
            ];
        } catch (Exception $e) {
            return [
                'current' => [
                    'bytes_sent' => 0,
                    'bytes_received' => 0,
                    'packets_sent' => 0,
                    'packets_received' => 0
                ]
            ];
        }
    }

    /**
     * macOS 네트워크 사용률
     */
    private function getMacNetworkUsage()
    {
        try {
            $networkInfo = shell_exec('netstat -ib 2>/dev/null | grep -E "^(en|wl)" | head -1');
            if (preg_match('/\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $networkInfo, $matches)) {
                $bytesReceived = (int)$matches[1];
                $bytesSent = (int)$matches[3];
                
                return [
                    'current' => [
                        'bytes_sent' => $bytesSent,
                        'bytes_received' => $bytesReceived,
                        'packets_sent' => 0,
                        'packets_received' => 0
                    ]
                ];
            }
        } catch (Exception $e) {
            // 에러 처리
        }
        
        return [
            'current' => [
                'bytes_sent' => 0,
                'bytes_received' => 0,
                'packets_sent' => 0,
                'packets_received' => 0
            ]
        ];
    }

    /**
     * 일반 네트워크 사용률 (fallback)
     */
    private function getGenericNetworkUsage()
    {
        return [
            'current' => [
                'bytes_sent' => 0,
                'bytes_received' => 0,
                'packets_sent' => 0,
                'packets_received' => 0
            ]
        ];
    }

    /**
     * 운영체제 정보 가져오기
     */
    private function getOsInfo()
    {
        $osInfo = [
            'family' => PHP_OS_FAMILY,
            'distribution' => 'unknown',
            'version' => 'unknown'
        ];

        try {
            if (PHP_OS_FAMILY === 'Linux') {
                // Linux 배포판 정보 가져오기
                if (file_exists('/etc/os-release')) {
                    $osRelease = file_get_contents('/etc/os-release');
                    if (preg_match('/ID=(\w+)/', $osRelease, $matches)) {
                        $osInfo['distribution'] = strtolower($matches[1]);
                    }
                    if (preg_match('/VERSION_ID="([^"]+)"/', $osRelease, $matches)) {
                        $osInfo['version'] = $matches[1];
                    }
                } elseif (file_exists('/etc/redhat-release')) {
                    $osInfo['distribution'] = 'redhat';
                } elseif (file_exists('/etc/debian_version')) {
                    $osInfo['distribution'] = 'debian';
                } elseif (file_exists('/etc/alpine-release')) {
                    $osInfo['distribution'] = 'alpine';
                }
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                $osInfo['distribution'] = 'macos';
                $version = shell_exec('sw_vers -productVersion 2>/dev/null');
                if ($version) {
                    $osInfo['version'] = trim($version);
                }
            } elseif (PHP_OS_FAMILY === 'Windows') {
                $osInfo['distribution'] = 'windows';
                $version = shell_exec('ver 2>&1');
                if (preg_match('/Version (\d+\.\d+)/', $version, $matches)) {
                    $osInfo['version'] = $matches[1];
                }
            }
        } catch (Exception $e) {
            // 에러 발생 시 기본값 유지
        }

        return $osInfo;
    }

    /**
     * CPU 코어 수 가져오기
     */
    private function getCpuCount()
    {
        try {
            $osInfo = $this->getOsInfo();
            
            switch ($osInfo['family']) {
                case 'Windows':
                    $cpuInfo = shell_exec('wmic cpu get NumberOfCores /value 2>&1');
                    if (preg_match('/NumberOfCores=(\d+)/', $cpuInfo, $matches)) {
                        return (int)$matches[1];
                    }
                    break;
                case 'Linux':
                    if (file_exists('/proc/cpuinfo')) {
                        $cpuInfo = file_get_contents('/proc/cpuinfo');
                        return substr_count($cpuInfo, 'processor');
                    }
                    break;
                case 'Darwin': // macOS
                    $cpuInfo = shell_exec('sysctl -n hw.ncpu 2>/dev/null');
                    if ($cpuInfo) {
                        return (int)trim($cpuInfo);
                    }
                    break;
            }
        } catch (Exception $e) {
            return 1; // 기본값
        }
        return 1;
    }

    /**
     * 바이트 단위 변환 (K, M, G 등)
     */
    private function convertToBytes($size)
    {
        $size = strtoupper(trim($size));
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int)$size;
        
        switch ($last) {
            case 'k':
                return $size * 1024;
            case 'm':
                return $size * 1024 * 1024;
            case 'g':
                return $size * 1024 * 1024 * 1024;
            default:
                return $size;
        }
    }
}
