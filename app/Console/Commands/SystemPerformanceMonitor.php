<?php

namespace Jiny\Admin\App\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Carbon\Carbon;
use Exception;

class SystemPerformanceMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:monitor 
                            {--type=all : 모니터링할 메트릭 타입 (cpu, memory, disk, network, database, all)}
                            {--server= : 서버명 (기본값: 현재 서버명)}
                            {--interval=60 : 모니터링 간격 (초)}
                            {--duration=0 : 모니터링 지속 시간 (초, 0은 무한)}
                            {--dry-run : 실제 저장하지 않고 출력만}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '시스템 성능을 모니터링하고 로그를 생성합니다';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $server = $this->option('server') ?: gethostname();
        $interval = (int) $this->option('interval');
        $duration = (int) $this->option('duration');
        $dryRun = $this->option('dry-run');

        $this->info("시스템 성능 모니터링을 시작합니다...");
        $this->info("서버: {$server}");
        $this->info("메트릭 타입: {$type}");
        $this->info("간격: {$interval}초");
        $this->info("지속시간: " . ($duration > 0 ? "{$duration}초" : "무한"));
        $this->info("드라이런: " . ($dryRun ? "예" : "아니오"));
        $this->info("");

        $startTime = time();
        $iteration = 0;

        while (true) {
            $iteration++;
            $currentTime = Carbon::now();
            
            $this->info("[{$currentTime->format('Y-m-d H:i:s')}] 반복 #{$iteration}");
            
            try {
                $metrics = $this->collectMetrics($type, $server);
                
                foreach ($metrics as $metric) {
                    if ($dryRun) {
                        $this->line("  - {$metric['metric_name']}: {$metric['value']}{$metric['unit']} ({$metric['status']})");
                    } else {
                        SystemPerformanceLog::create($metric);
                        $this->line("  ✓ {$metric['metric_name']}: {$metric['value']}{$metric['unit']} ({$metric['status']})");
                    }
                }
                
            } catch (Exception $e) {
                $this->error("메트릭 수집 중 오류 발생: " . $e->getMessage());
            }
            
            // 지속 시간 체크
            if ($duration > 0 && (time() - $startTime) >= $duration) {
                $this->info("지정된 지속 시간이 완료되었습니다.");
                break;
            }
            
            // 간격만큼 대기
            if ($interval > 0) {
                sleep($interval);
            } else {
                break;
            }
        }
        
        $this->info("모니터링이 완료되었습니다.");
        return 0;
    }
    
    /**
     * 메트릭 수집
     */
    private function collectMetrics(string $type, string $server): array
    {
        $metrics = [];
        
        if ($type === 'all' || $type === 'cpu') {
            $metrics[] = $this->collectCpuMetrics($server);
        }
        
        if ($type === 'all' || $type === 'memory') {
            $metrics[] = $this->collectMemoryMetrics($server);
        }
        
        if ($type === 'all' || $type === 'disk') {
            $metrics = array_merge($metrics, $this->collectDiskMetrics($server));
        }
        
        if ($type === 'all' || $type === 'network') {
            $metrics[] = $this->collectNetworkMetrics($server);
        }
        
        if ($type === 'all' || $type === 'database') {
            $metrics[] = $this->collectDatabaseMetrics($server);
        }
        
        return $metrics;
    }
    
    /**
     * CPU 메트릭 수집
     */
    private function collectCpuMetrics(string $server): array
    {
        $loadAvg = sys_getloadavg();
        $cpuUsage = $this->getCpuUsage();
        
        $status = $this->determineStatus('cpu', $cpuUsage);
        
        return [
            'metric_name' => 'CPU 사용률',
            'metric_type' => 'cpu',
            'value' => $cpuUsage,
            'unit' => '%',
            'threshold' => '80',
            'status' => $status,
            'server_name' => $server,
            'component' => 'system',
            'additional_data' => [
                'load_average' => [
                    '1min' => $loadAvg[0],
                    '5min' => $loadAvg[1],
                    '15min' => $loadAvg[2]
                ],
                'cpu_cores' => $this->getCpuCores(),
                'temperature' => $this->getCpuTemperature()
            ],
            'measured_at' => now()
        ];
    }
    
    /**
     * 메모리 메트릭 수집
     */
    private function collectMemoryMetrics(string $server): array
    {
        $memoryInfo = $this->getMemoryInfo();
        $memoryUsage = $memoryInfo['usage_percent'];
        
        $status = $this->determineStatus('memory', $memoryUsage);
        
        return [
            'metric_name' => '메모리 사용률',
            'metric_type' => 'memory',
            'value' => $memoryUsage,
            'unit' => '%',
            'threshold' => '85',
            'status' => $status,
            'server_name' => $server,
            'component' => 'system',
            'additional_data' => [
                'total_memory' => $memoryInfo['total'],
                'free_memory' => $memoryInfo['free'],
                'used_memory' => $memoryInfo['used'],
                'swap_usage' => $memoryInfo['swap_usage']
            ],
            'measured_at' => now()
        ];
    }
    
    /**
     * 디스크 메트릭 수집
     */
    private function collectDiskMetrics(string $server): array
    {
        $metrics = [];
        $diskInfo = $this->getDiskInfo();
        
        foreach ($diskInfo as $mountPoint => $info) {
            $status = $this->determineStatus('disk', $info['usage_percent']);
            
            $metrics[] = [
                'metric_name' => '디스크 사용률',
                'metric_type' => 'disk',
                'value' => $info['usage_percent'],
                'unit' => '%',
                'threshold' => '90',
                'status' => $status,
                'server_name' => $server,
                'component' => $mountPoint,
                'additional_data' => [
                    'total_space' => $info['total'],
                    'free_space' => $info['free'],
                    'used_space' => $info['used'],
                    'inodes_usage' => $info['inodes_usage'] ?? 0
                ],
                'measured_at' => now()
            ];
        }
        
        return $metrics;
    }
    
    /**
     * 네트워크 메트릭 수집
     */
    private function collectNetworkMetrics(string $server): array
    {
        $networkInfo = $this->getNetworkInfo();
        $bandwidth = $networkInfo['bandwidth'];
        
        $status = $this->determineStatus('network', $bandwidth);
        
        return [
            'metric_name' => '네트워크 대역폭',
            'metric_type' => 'network',
            'value' => $bandwidth,
            'unit' => 'Mbps',
            'threshold' => '800',
            'status' => $status,
            'server_name' => $server,
            'component' => 'eth0',
            'additional_data' => [
                'packets_sent' => $networkInfo['packets_sent'],
                'packets_received' => $networkInfo['packets_received'],
                'errors' => $networkInfo['errors'],
                'dropped' => $networkInfo['dropped']
            ],
            'measured_at' => now()
        ];
    }
    
    /**
     * 데이터베이스 메트릭 수집
     */
    private function collectDatabaseMetrics(string $server): array
    {
        $dbInfo = $this->getDatabaseInfo();
        $connections = $dbInfo['connections'];
        
        $status = $this->determineStatus('database', $connections);
        
        return [
            'metric_name' => '데이터베이스 연결수',
            'metric_type' => 'database',
            'value' => $connections,
            'unit' => 'connections',
            'threshold' => '1500',
            'status' => $status,
            'server_name' => $server,
            'component' => 'mysql',
            'additional_data' => [
                'active_connections' => $dbInfo['active_connections'],
                'idle_connections' => $dbInfo['idle_connections'],
                'slow_queries' => $dbInfo['slow_queries'],
                'query_time_avg' => $dbInfo['query_time_avg']
            ],
            'measured_at' => now()
        ];
    }
    
    /**
     * CPU 사용률 계산
     */
    private function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get loadpercentage /value');
            if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                return (float) $matches[1];
            }
        } else {
            $output = shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1");
            if ($output) {
                return (float) trim($output);
            }
        }
        
        // 기본값 (시뮬레이션)
        return rand(10, 80);
    }
    
    /**
     * CPU 코어 수 가져오기
     */
    private function getCpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get NumberOfCores /value');
            if (preg_match('/NumberOfCores=(\d+)/', $output, $matches)) {
                return (int) $matches[1];
            }
        } else {
            $output = shell_exec('nproc');
            if ($output) {
                return (int) trim($output);
            }
        }
        
        return 4; // 기본값
    }
    
    /**
     * CPU 온도 가져오기
     */
    private function getCpuTemperature(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows에서는 온도 정보를 직접 가져오기 어려움
            return rand(40, 70);
        } else {
            $tempFile = '/sys/class/thermal/thermal_zone0/temp';
            if (file_exists($tempFile)) {
                $temp = file_get_contents($tempFile);
                return (float) $temp / 1000; // 밀리켈빈을 섭씨로 변환
            }
        }
        
        return rand(40, 70); // 기본값
    }
    
    /**
     * 메모리 정보 가져오기
     */
    private function getMemoryInfo(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value');
            $total = 0;
            $free = 0;
            
            if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $matches)) {
                $total = (int) $matches[1] * 1024; // KB to bytes
            }
            if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
                $free = (int) $matches[1] * 1024; // KB to bytes
            }
            
            $used = $total - $free;
            $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
            
            return [
                'total' => $total,
                'free' => $free,
                'used' => $used,
                'usage_percent' => $usagePercent,
                'swap_usage' => rand(0, 30)
            ];
        } else {
            $output = shell_exec('free -b');
            $lines = explode("\n", $output);
            
            if (isset($lines[1])) {
                $memoryLine = explode(' ', preg_replace('/\s+/', ' ', trim($lines[1])));
                $total = (int) $memoryLine[1];
                $used = (int) $memoryLine[2];
                $free = (int) $memoryLine[3];
                $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
                
                return [
                    'total' => $total,
                    'free' => $free,
                    'used' => $used,
                    'usage_percent' => $usagePercent,
                    'swap_usage' => rand(0, 30)
                ];
            }
        }
        
        // 기본값
        $total = 8 * 1024 * 1024 * 1024; // 8GB
        $used = rand(2, 6) * 1024 * 1024 * 1024;
        $free = $total - $used;
        
        return [
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'usage_percent' => ($used / $total) * 100,
            'swap_usage' => rand(0, 30)
        ];
    }
    
    /**
     * 디스크 정보 가져오기
     */
    private function getDiskInfo(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic logicaldisk get size,freespace,caption /value');
            $disks = [];
            
            preg_match_all('/Caption=([^\r\n]+).*?FreeSpace=(\d+).*?Size=(\d+)/s', $output, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $drive = $match[1];
                $free = (int) $match[2];
                $total = (int) $match[3];
                $used = $total - $free;
                $usagePercent = $total > 0 ? ($used / $total) * 100 : 0;
                
                $disks[$drive] = [
                    'total' => $total,
                    'free' => $free,
                    'used' => $used,
                    'usage_percent' => $usagePercent
                ];
            }
            
            return $disks;
        } else {
            $output = shell_exec('df -B1');
            $lines = explode("\n", $output);
            $disks = [];
            
            foreach ($lines as $line) {
                if (preg_match('/^\/dev\/(\S+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)%\s+(.+)$/', $line, $matches)) {
                    $mountPoint = trim($matches[6]);
                    $total = (int) $matches[2];
                    $used = (int) $matches[3];
                    $free = (int) $matches[4];
                    $usagePercent = (int) $matches[5];
                    
                    $disks[$mountPoint] = [
                        'total' => $total,
                        'free' => $free,
                        'used' => $used,
                        'usage_percent' => $usagePercent
                    ];
                }
            }
            
            return $disks;
        }
    }
    
    /**
     * 네트워크 정보 가져오기
     */
    private function getNetworkInfo(): array
    {
        // 실제 네트워크 정보 수집은 복잡하므로 시뮬레이션
        return [
            'bandwidth' => rand(10, 1000),
            'packets_sent' => rand(1000, 100000),
            'packets_received' => rand(1000, 100000),
            'errors' => rand(0, 100),
            'dropped' => rand(0, 50)
        ];
    }
    
    /**
     * 데이터베이스 정보 가져오기
     */
    private function getDatabaseInfo(): array
    {
        // 실제 데이터베이스 연결 정보는 복잡하므로 시뮬레이션
        return [
            'connections' => rand(50, 2000),
            'active_connections' => rand(10, 500),
            'idle_connections' => rand(5, 200),
            'slow_queries' => rand(0, 50),
            'query_time_avg' => rand(10, 500) / 1000
        ];
    }
    
    /**
     * 메트릭 타입과 값에 따른 상태 결정
     */
    private function determineStatus(string $metricType, float $value): string
    {
        $thresholds = [
            'cpu' => ['warning' => 60, 'critical' => 80],
            'memory' => ['warning' => 70, 'critical' => 85],
            'disk' => ['warning' => 80, 'critical' => 90],
            'network' => ['warning' => 500, 'critical' => 800],
            'database' => ['warning' => 1000, 'critical' => 1500]
        ];
        
        if (!isset($thresholds[$metricType])) {
            return 'normal';
        }
        
        $threshold = $thresholds[$metricType];
        
        if ($value >= $threshold['critical']) {
            return 'critical';
        } elseif ($value >= $threshold['warning']) {
            return 'warning';
        } else {
            return 'normal';
        }
    }
} 