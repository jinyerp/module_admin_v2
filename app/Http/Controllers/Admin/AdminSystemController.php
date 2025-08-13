<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Jiny\Admin\App\Models\SystemBackupLog;
use Jiny\Admin\App\Models\SystemMaintenanceLog;
use Jiny\Admin\App\Models\SystemOperationLog;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Process;

/**
 * AdminSystemController
 *
 * 관리자 시스템 모니터링 및 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * 시스템 상태, 성능 지표, 하드웨어 정보, 운영 환경 등을 실시간으로 모니터링:
 * - CPU, 메모리, 디스크, 네트워크 상태 모니터링
 * - PHP, Laravel, 데이터베이스, 세션 정보 관리
 * - 시스템 성능 트렌드 및 병목 지점 분석
 * - 백업, 유지보수, 운영, 성능 로그 통합 관리
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSystemController.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 시스템 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminSystemControllerTest.php
 * ```
 */
class AdminSystemController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.systems.index';
    public $createPath = 'jiny-admin::admin.systems.create';
    public $editPath = 'jiny-admin::admin.systems.edit';
    public $showPath = 'jiny-admin::admin.systems.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['days', 'status', 'type'];
    protected $validFilters = [
        'days' => 'integer|min:1|max:365',
        'status' => 'string|in:normal,warning,critical',
        'type' => 'string|in:backup,maintenance,operation,performance'
    ];
    protected $sortableColumns = ['created_at', 'status', 'type'];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_systems';

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     * System 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'admin_systems';
    }

    /**
     * 모듈 이름 반환
     * System 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.systems';
    }

    /**
     * 시스템 대시보드 메인 페이지
     */
    public function index(Request $request): View
    {
        // 최근 30일 데이터 기준
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        // 백업 로그 통계
        $backupStats = [
            'total' => SystemBackupLog::count(),
            'completed' => SystemBackupLog::completed()->count(),
            'failed' => SystemBackupLog::failed()->count(),
            'success_rate' => SystemBackupLog::getSuccessRate(),
            'recent' => SystemBackupLog::where('created_at', '>=', $startDate)->count(),
        ];

        // 유지보수 로그 통계
        $maintenanceStats = [
            'total' => SystemMaintenanceLog::count(),
            'scheduled' => SystemMaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => SystemMaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => SystemMaintenanceLog::where('status', 'completed')->count(),
            'recent' => SystemMaintenanceLog::where('created_at', '>=', $startDate)->count(),
        ];

        // 운영 로그 통계
        $operationStats = [
            'total' => SystemOperationLog::count(),
            'success' => SystemOperationLog::where('status', 'success')->count(),
            'failed' => SystemOperationLog::where('status', 'failed')->count(),
            'recent' => SystemOperationLog::where('created_at', '>=', $startDate)->count(),
            'avg_execution_time' => SystemOperationLog::whereNotNull('execution_time')->avg('execution_time'),
        ];

        // 성능 로그 통계
        $performanceStats = [
            'total' => SystemPerformanceLog::count(),
            'normal' => SystemPerformanceLog::where('status', 'normal')->count(),
            'warning' => SystemPerformanceLog::where('status', 'warning')->count(),
            'critical' => SystemPerformanceLog::where('status', 'critical')->count(),
            'recent' => SystemPerformanceLog::where('measured_at', '>=', $startDate)->count(),
        ];

        // 최근 활동
        $recentBackups = SystemBackupLog::with('initiatedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentMaintenance = SystemMaintenanceLog::with(['initiatedBy', 'completedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentOperations = SystemOperationLog::with('performedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPerformance = SystemPerformanceLog::orderBy('measured_at', 'desc')
            ->limit(5)
            ->get();

        // 차트 데이터
        $chartData = $this->getChartData($startDate);

        // 시스템 정보
        $systemInfo = $this->getSystemInfo();

        // Activity Log 기록
        $this->logActivity('view', '시스템 대시보드 조회', null, ['days' => $days]);

        return view($this->indexPath, compact(
            'backupStats',
            'maintenanceStats', 
            'operationStats',
            'performanceStats',
            'recentBackups',
            'recentMaintenance',
            'recentOperations',
            'recentPerformance',
            'chartData',
            'systemInfo',
            'days'
        ));
    }

    /**
     * 차트 데이터 생성
     */
    private function getChartData($startDate)
    {
        // 백업 성공률 트렌드
        $backupTrend = SystemBackupLog::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
            COUNT(CASE WHEN status = "failed" THEN 1 END) as failed
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 운영 로그 트렌드
        $operationTrend = SystemOperationLog::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "success" THEN 1 END) as success,
            COUNT(CASE WHEN status = "failed" THEN 1 END) as failed,
            AVG(execution_time) as avg_execution_time
        ')
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // 성능 로그 트렌드
        $performanceTrend = SystemPerformanceLog::selectRaw('
            DATE(measured_at) as date,
            COUNT(*) as total,
            COUNT(CASE WHEN status = "normal" THEN 1 END) as normal,
            COUNT(CASE WHEN status = "warning" THEN 1 END) as warning,
            COUNT(CASE WHEN status = "critical" THEN 1 END) as critical,
            AVG(value) as avg_value
        ')
        ->where('measured_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return [
            'backup_trend' => $backupTrend,
            'operation_trend' => $operationTrend,
            'performance_trend' => $performanceTrend,
        ];
    }

    /**
     * 시스템 상태 요약
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        // 시스템 정보 가져오기
        $systemInfo = $this->getSystemInfo();
        
        $status = [
            'backup' => [
                'status' => SystemBackupLog::failed()->where('created_at', '>=', now()->subDays(7))->count() > 0 ? 'warning' : 'normal',
                'message' => SystemBackupLog::failed()->where('created_at', '>=', now()->subDays(7))->count() . '개의 최근 백업 실패'
            ],
            'maintenance' => [
                'status' => SystemMaintenanceLog::where('status', 'in_progress')->count() > 0 ? 'warning' : 'normal',
                'message' => SystemMaintenanceLog::where('status', 'in_progress')->count() . '개의 진행중인 유지보수'
            ],
            'performance' => [
                'status' => SystemPerformanceLog::where('status', 'critical')->where('measured_at', '>=', now()->subHours(1))->count() > 0 ? 'critical' : 'normal',
                'message' => SystemPerformanceLog::where('status', 'critical')->where('measured_at', '>=', now()->subHours(1))->count() . '개의 임계치 초과'
            ],
            'operations' => [
                'status' => SystemOperationLog::where('status', 'failed')->where('created_at', '>=', now()->subHours(1))->count() > 5 ? 'warning' : 'normal',
                'message' => SystemOperationLog::where('status', 'failed')->where('created_at', '>=', now()->subHours(1))->count() . '개의 최근 실패한 운영'
            ],
            // 시스템 하드웨어 정보 추가
            'cpu' => [
                'usage_percent' => $systemInfo['cpu']['usage_percent'],
                'cores' => $systemInfo['cpu']['cores'],
                'name' => $systemInfo['cpu']['name']
            ],
            'memory' => [
                'usage_percent' => $systemInfo['memory']['usage_percent'],
                'total' => $systemInfo['memory']['total'],
                'used' => $systemInfo['memory']['used'],
                'free' => $systemInfo['memory']['free']
            ],
            'disk' => [
                'usage_percent' => $systemInfo['disk']['usage_percent'],
                'total' => $systemInfo['disk']['total'],
                'used' => $systemInfo['disk']['used'],
                'free' => $systemInfo['disk']['free']
            ],
            'os' => [
                'name' => $systemInfo['os']['name'],
                'family' => $systemInfo['os']['family'],
                'version' => $systemInfo['os']['version']
            ],
            'uptime' => $systemInfo['uptime'],
            // PHP, Laravel, 데이터베이스, 세션 정보 추가
            'php' => $systemInfo['php'],
            'laravel' => $systemInfo['laravel'],
            'database' => $systemInfo['database'],
            'session' => $systemInfo['session']
        ];

        // Activity Log 기록
        $this->logActivity('status', '시스템 상태 조회', null, ['status' => $status]);

        return response()->json($status);
    }

    /**
     * 시스템 정보 수집
     */
    private function getSystemInfo()
    {
        $info = [
            'os' => $this->getOSInfo(),
            'disk' => $this->getDiskInfo(),
            'memory' => $this->getMemoryInfo(),
            'cpu' => $this->getCPUInfo(),
            'uptime' => $this->getUptimeInfo(),
            'php' => $this->getPHPInfo(),
            'laravel' => $this->getLaravelInfo(),
            'database' => $this->getDatabaseInfo(),
            'session' => $this->getSessionInfo(),
        ];

        // 디버깅을 위한 로그 (개발 환경에서만)
        if (config('app.debug')) {
            \Log::info('System Info Collected', $info);
        }

        return $info;
    }

    /**
     * 운영체제 정보
     */
    private function getOSInfo()
    {
        $osName = PHP_OS;
        $osFamily = PHP_OS_FAMILY;
        
        // 간단한 OS 정보만 반환
        return [
            'name' => $osName,
            'version' => $osFamily === 'Windows' ? 'Windows' : 'Linux/Unix',
            'family' => $osFamily,
        ];
    }

    /**
     * 디스크 정보
     */
    private function getDiskInfo()
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = ($diskTotal > 0) ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

        return [
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
            'used' => $this->formatBytes($diskUsed),
            'usage_percent' => $diskUsagePercent,
            'total_bytes' => $diskTotal,
            'free_bytes' => $diskFree,
            'used_bytes' => $diskUsed,
        ];
    }

    /**
     * 메모리 정보
     */
    private function getMemoryInfo()
    {
        $totalMemory = 0;
        $freeMemory = 0;
        $usedMemory = 0;
        $memoryUsagePercent = 0;

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows 메모리 정보 - 더 안정적인 방법
            try {
                // 방법 1: wmic with /value
                $result = Process::run('wmic computersystem get TotalPhysicalMemory /value');
                if ($result->successful()) {
                    $output = $result->output();
                    if (preg_match('/TotalPhysicalMemory=(\d+)/', $output, $matches)) {
                        $totalMemory = (int)$matches[1];
                    }
                }

                $result = Process::run('wmic OS get FreePhysicalMemory /value');
                if ($result->successful()) {
                    $output = $result->output();
                    if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
                        $freeMemory = (int)$matches[1] * 1024; // KB to bytes
                    }
                }

                // 방법 2: 만약 첫 번째 방법이 실패하면 다른 방법 시도
                if ($totalMemory === 0) {
                    $result = Process::run('wmic computersystem get TotalPhysicalMemory');
                    if ($result->successful()) {
                        $lines = explode("\n", trim($result->output()));
                        if (isset($lines[1])) {
                            $totalMemory = (int)trim($lines[1]);
                        }
                    }
                }

                if ($freeMemory === 0) {
                    $result = Process::run('wmic OS get FreePhysicalMemory');
                    if ($result->successful()) {
                        $lines = explode("\n", trim($result->output()));
                        if (isset($lines[1])) {
                            $freeMemory = (int)trim($lines[1]) * 1024; // KB to bytes
                        }
                    }
                }

                // 방법 3: PowerShell 사용 (wmic가 실패한 경우)
                if ($totalMemory === 0) {
                    $result = Process::run('powershell -Command "Get-WmiObject -Class Win32_ComputerSystem | Select-Object -ExpandProperty TotalPhysicalMemory"');
                    if ($result->successful()) {
                        $totalMemory = (int)trim($result->output());
                    }
                }

                if ($freeMemory === 0) {
                    $result = Process::run('powershell -Command "Get-WmiObject -Class Win32_OperatingSystem | Select-Object -ExpandProperty FreePhysicalMemory"');
                    if ($result->successful()) {
                        $freeMemory = (int)trim($result->output()) * 1024; // KB to bytes
                    }
                }

            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('Memory info collection failed: ' . $e->getMessage());
                }
            }
        } else {
            // Linux/Unix 메모리 정보
            try {
                $result = Process::run('free -b');
                if ($result->successful()) {
                    $lines = explode("\n", trim($result->output()));
                    foreach ($lines as $line) {
                        if (strpos($line, 'Mem:') !== false) {
                            $parts = preg_split('/\s+/', trim($line));
                            if (count($parts) >= 3) {
                                $totalMemory = (int)$parts[1];
                                $usedMemory = (int)$parts[2];
                                $freeMemory = (int)$parts[3];
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('Memory info collection failed: ' . $e->getMessage());
                }
            }
        }

        // 사용량 계산
        if ($totalMemory > 0) {
            $usedMemory = $totalMemory - $freeMemory;
            $memoryUsagePercent = round(($usedMemory / $totalMemory) * 100, 2);
        }

        return [
            'total' => $this->formatBytes($totalMemory),
            'free' => $this->formatBytes($freeMemory),
            'used' => $this->formatBytes($usedMemory),
            'usage_percent' => $memoryUsagePercent,
            'total_bytes' => $totalMemory,
            'free_bytes' => $freeMemory,
            'used_bytes' => $usedMemory,
        ];
    }

    /**
     * CPU 정보
     */
    private function getCPUInfo()
    {
        $cpuName = 'Unknown';
        $cores = 0;

        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // 방법 1: wmic with /value
                $result = Process::run('wmic cpu get Name /value');
                if ($result->successful()) {
                    $output = $result->output();
                    if (preg_match('/Name=(.+)/', $output, $matches)) {
                        $cpuName = trim($matches[1]);
                    }
                }

                $result = Process::run('wmic cpu get NumberOfCores /value');
                if ($result->successful()) {
                    $output = $result->output();
                    if (preg_match('/NumberOfCores=(\d+)/', $output, $matches)) {
                        $cores = (int)$matches[1];
                    }
                }

                // 방법 2: 만약 첫 번째 방법이 실패하면 다른 방법 시도
                if ($cpuName === 'Unknown') {
                    $result = Process::run('wmic cpu get Name');
                    if ($result->successful()) {
                        $lines = explode("\n", trim($result->output()));
                        if (isset($lines[1])) {
                            $cpuName = trim($lines[1]);
                        }
                    }
                }

                if ($cores === 0) {
                    $result = Process::run('wmic cpu get NumberOfCores');
                    if ($result->successful()) {
                        $lines = explode("\n", trim($result->output()));
                        if (isset($lines[1])) {
                            $cores = (int)trim($lines[1]);
                        }
                    }
                }

                // 방법 3: PowerShell 사용 (wmic가 실패한 경우)
                if ($cpuName === 'Unknown') {
                    $result = Process::run('powershell -Command "Get-WmiObject -Class Win32_Processor | Select-Object -First 1 -ExpandProperty Name"');
                    if ($result->successful()) {
                        $cpuName = trim($result->output());
                    }
                }

                if ($cores === 0) {
                    $result = Process::run('powershell -Command "Get-WmiObject -Class Win32_Processor | Select-Object -First 1 -ExpandProperty NumberOfCores"');
                    if ($result->successful()) {
                        $cores = (int)trim($result->output());
                    }
                }

            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('CPU info collection failed: ' . $e->getMessage());
                }
            }
        } else {
            try {
                $result = Process::run('cat /proc/cpuinfo | grep "model name" | head -1');
                if ($result->successful()) {
                    $output = trim($result->output());
                    if (preg_match('/model name\s+:\s+(.+)/', $output, $matches)) {
                        $cpuName = trim($matches[1]);
                    }
                }

                $result = Process::run('nproc');
                if ($result->successful()) {
                    $cores = (int)trim($result->output());
                }
            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('CPU info collection failed: ' . $e->getMessage());
                }
            }
        }

        // CPU 사용률
        $cpuUsage = $this->getCPUUsage();

        return [
            'name' => $cpuName,
            'cores' => $cores,
            'usage_percent' => $cpuUsage,
        ];
    }

    /**
     * CPU 사용률 가져오기
     */
    private function getCPUUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // 방법 1: wmic with /value
                $result = Process::run('wmic cpu get LoadPercentage /value');
                if ($result->successful()) {
                    $output = $result->output();
                    if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                        return (int)$matches[1];
                    }
                }

                // 방법 2: 일반적인 방법
                $result = Process::run('wmic cpu get LoadPercentage');
                if ($result->successful()) {
                    $lines = explode("\n", trim($result->output()));
                    if (isset($lines[1])) {
                        return (int)trim($lines[1]);
                    }
                }

                // 방법 3: PowerShell 사용 (wmic가 실패한 경우)
                $result = Process::run('powershell -Command "Get-WmiObject -Class Win32_Processor | Select-Object -First 1 -ExpandProperty LoadPercentage"');
                if ($result->successful()) {
                    $usage = trim($result->output());
                    if (is_numeric($usage)) {
                        return (int)$usage;
                    }
                }

            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('CPU usage collection failed: ' . $e->getMessage());
                }
            }
        } else {
            try {
                // 더 간단한 방법으로 CPU 사용률 계산
                $result = Process::run('top -bn1 | grep "Cpu(s)"');
                if ($result->successful()) {
                    $output = trim($result->output());
                    if (preg_match('/(\d+\.?\d*)%us/', $output, $matches)) {
                        return round((float)$matches[1], 2);
                    }
                }
            } catch (\Exception $e) {
                // 실패 시 기본값 사용
                if (config('app.debug')) {
                    \Log::error('CPU usage collection failed: ' . $e->getMessage());
                }
            }
        }

        return 0;
    }

    /**
     * 시스템 업타임 정보
     */
    private function getUptimeInfo()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // 더 간단한 방법으로 업타임 계산
                $result = Process::run('net statistics server | find "Statistics since"');
                if ($result->successful()) {
                    $output = trim($result->output());
                    if (preg_match('/Statistics since (.+)/', $output, $matches)) {
                        $startTime = strtotime($matches[1]);
                        if ($startTime !== false) {
                            $uptime = time() - $startTime;
                            return $this->formatUptime($uptime);
                        }
                    }
                }
            } catch (\Exception $e) {
                // 실패 시 기본값 사용
            }
        } else {
            try {
                $result = Process::run('uptime -p');
                if ($result->successful()) {
                    return trim($result->output());
                }
            } catch (\Exception $e) {
                // 실패 시 기본값 사용
            }
        }

        return 'Unknown';
    }

    /**
     * PHP 정보
     */
    private function getPHPInfo()
    {
        return [
            'version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'extensions' => [
                'mysql' => extension_loaded('mysqli') || extension_loaded('pdo_mysql'),
                'pdo' => extension_loaded('pdo'),
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'zip' => extension_loaded('zip'),
            ],
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    /**
     * Laravel 정보
     */
    private function getLaravelInfo()
    {
        return [
            'version' => app()->version(),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'fallback_locale' => config('app.fallback_locale'),
            'key' => config('app.key') ? '설정됨' : '설정되지 않음',
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];
    }

    /**
     * 데이터베이스 정보
     */
    private function getDatabaseInfo()
    {
        try {
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            $dbInfo = [
                'driver' => $connection,
                'host' => $config['host'] ?? 'N/A',
                'port' => $config['port'] ?? 'N/A',
                'database' => $config['database'] ?? 'N/A',
                'charset' => $config['charset'] ?? 'N/A',
                'collation' => $config['collation'] ?? 'N/A',
                'prefix' => $config['prefix'] ?? '',
            ];

            // MySQL 특정 정보
            if ($connection === 'mysql') {
                try {
                    $mysqlVersion = DB::select('SELECT VERSION() as version')[0]->version ?? 'N/A';
                    $dbInfo['mysql_version'] = $mysqlVersion;
                    
                    // 테이블 수
                    $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ?", [$config['database']])[0]->count ?? 0;
                    $dbInfo['table_count'] = $tableCount;
                    
                    // 데이터베이스 크기
                    $dbSize = DB::select("
                        SELECT 
                            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                        FROM information_schema.tables 
                        WHERE table_schema = ?
                    ", [$config['database']])[0]->size_mb ?? 0;
                    $dbInfo['size_mb'] = $dbSize;
                    
                } catch (\Exception $e) {
                    if (config('app.debug')) {
                        \Log::error('MySQL info collection failed: ' . $e->getMessage());
                    }
                    $dbInfo['mysql_version'] = 'N/A';
                    $dbInfo['table_count'] = 0;
                    $dbInfo['size_mb'] = 0;
                }
            }

            return $dbInfo;
            
        } catch (\Exception $e) {
            if (config('app.debug')) {
                \Log::error('Database info collection failed: ' . $e->getMessage());
            }
            
            return [
                'driver' => 'N/A',
                'host' => 'N/A',
                'port' => 'N/A',
                'database' => 'N/A',
                'charset' => 'N/A',
                'collation' => 'N/A',
                'prefix' => '',
                'mysql_version' => 'N/A',
                'table_count' => 0,
                'size_mb' => 0,
            ];
        }
    }

    /**
     * 세션 정보 가져오기
     */
    private function getSessionInfo()
    {
        $sessionConfig = config('session');
        
        return [
            'driver' => $sessionConfig['driver'],
            'lifetime' => $sessionConfig['lifetime'],
            'expire_on_close' => $sessionConfig['expire_on_close'],
            'encrypt' => $sessionConfig['encrypt'],
            'secure' => $sessionConfig['secure'],
            'http_only' => $sessionConfig['http_only'],
            'same_site' => $sessionConfig['same_site'] ?? 'lax',
            'table' => $sessionConfig['driver'] === 'database' ? $sessionConfig['table'] : null,
        ];
    }

    /**
     * PHP 상세 정보 페이지
     */
    public function phpDetail()
    {
        $systemInfo = [
            'php' => $this->getPHPInfo(),
        ];

        // Activity Log 기록
        $this->logActivity('view', 'PHP 상세 정보 조회', null, ['php_version' => $systemInfo['php']['version']]);

        return view('jiny-admin::admin.systems.php-detail', compact('systemInfo'));
    }

    /**
     * Laravel 상세 정보 페이지
     */
    public function laravelDetail()
    {
        $systemInfo = [
            'laravel' => $this->getLaravelInfo(),
        ];

        // Activity Log 기록
        $this->logActivity('view', 'Laravel 상세 정보 조회', null, ['laravel_version' => $systemInfo['laravel']['version']]);

        return view('jiny-admin::admin.systems.laravel-detail', compact('systemInfo'));
    }

    /**
     * 데이터베이스 상세 정보 페이지
     */
    public function databaseDetail()
    {
        $systemInfo = [
            'database' => $this->getDatabaseInfo(),
        ];

        // Activity Log 기록
        $this->logActivity('view', '데이터베이스 상세 정보 조회', null, ['driver' => $systemInfo['database']['driver']]);

        return view('jiny-admin::admin.systems.database-detail', compact('systemInfo'));
    }

    /**
     * 세션 상세 정보 페이지
     */
    public function sessionDetail()
    {
        $systemInfo = [
            'session' => $this->getSessionInfo(),
        ];

        // 세션 통계 정보 (실제 구현에서는 더 정확한 데이터를 가져와야 함)
        $activeSessions = null;
        $totalSessions = null;
        $avgSessionTime = null;
        $maxSessionTime = null;

        // Activity Log 기록
        $this->logActivity('view', '세션 상세 정보 조회', null, ['driver' => $systemInfo['session']['driver']]);

        return view('jiny-admin::admin.systems.session-detail', compact('systemInfo', 'activeSessions', 'totalSessions', 'avgSessionTime', 'maxSessionTime'));
    }

    /**
     * 바이트를 읽기 쉬운 형태로 변환
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * 업타임을 읽기 쉬운 형태로 변환
     */
    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = $days . '일';
        if ($hours > 0) $parts[] = $hours . '시간';
        if ($minutes > 0) $parts[] = $minutes . '분';

        return implode(' ', $parts);
    }
}
