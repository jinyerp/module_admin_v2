<?php

namespace Jiny\Admin\App\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Jiny\Admin\App\Services\CachePerformanceService;

class GeneratePerformanceLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:generate-performance-logs {--count=50 : 생성할 로그 개수}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '테스트용 성능 로그를 생성합니다.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        $this->info("{$count}개의 성능 로그를 생성합니다...");

        $metricTypes = [
            'web' => ['request_time', 'response_size'],
            'database' => ['query_time', 'connection_count'],
            'cache' => ['hit_rate', 'miss_rate'],
            'memory' => ['usage', 'peak_usage'],
            'error' => ['exception_count', 'error_rate']
        ];

        $endpoints = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/settings',
            '/api/users',
            '/api/auth/login',
            '/api/products',
            '/api/orders'
        ];

        $methods = ['GET', 'POST', 'PUT', 'DELETE'];

        $cacheService = new CachePerformanceService();

        for ($i = 0; $i < $count; $i++) {
            $metricType = array_rand($metricTypes);
            $metricName = $metricTypes[$metricType][array_rand($metricTypes[$metricType])];
            
            $value = $this->generateRandomValue($metricName);
            $status = $this->determineStatus($value, $metricName);
            
            SystemPerformanceLog::create([
                'metric_name' => $metricName,
                'metric_type' => $metricType,
                'value' => $value,
                'unit' => $this->getUnit($metricName),
                'threshold' => $this->getThreshold($metricName),
                'status' => $status,
                'endpoint' => $endpoints[array_rand($endpoints)],
                'method' => $methods[array_rand($methods)],
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'ip_address' => '192.168.1.' . rand(1, 254),
                'session_id' => 'session_' . rand(1000, 9999),
                'additional_data' => json_encode([
                    'generated' => true,
                    'iteration' => $i + 1,
                    'random_data' => rand(1, 1000)
                ]),
                'measured_at' => now()->subMinutes(rand(0, 1440)), // 최근 24시간 내
            ]);

            // 진행률 표시
            if (($i + 1) % 10 === 0) {
                $this->info("진행률: " . round(($i + 1) / $count * 100, 1) . "%");
            }
        }

        // 캐시 성능 로그도 생성
        $cacheService->logCacheHitRate();
        $cacheService->logCacheSize();

        $this->info("성능 로그 생성이 완료되었습니다!");
        $this->info("생성된 로그 수: " . SystemPerformanceLog::count());
    }

    /**
     * 메트릭에 따른 랜덤 값 생성
     */
    private function generateRandomValue(string $metricName): float
    {
        return match ($metricName) {
            'request_time' => rand(50, 2000) / 10, // 5ms ~ 200ms
            'response_size' => rand(100, 50000), // 100B ~ 50KB
            'query_time' => rand(10, 5000) / 10, // 1ms ~ 500ms
            'connection_count' => rand(1, 50),
            'hit_rate' => rand(60, 95), // 60% ~ 95%
            'miss_rate' => rand(5, 40), // 5% ~ 40%
            'usage' => rand(10, 500) / 10, // 1MB ~ 50MB
            'peak_usage' => rand(20, 1000) / 10, // 2MB ~ 100MB
            'exception_count' => rand(0, 10),
            'error_rate' => rand(0, 5), // 0% ~ 5%
            default => rand(1, 100)
        };
    }

    /**
     * 메트릭에 따른 단위 반환
     */
    private function getUnit(string $metricName): string
    {
        return match ($metricName) {
            'request_time', 'query_time' => 'ms',
            'response_size', 'usage', 'peak_usage' => 'MB',
            'connection_count', 'exception_count' => 'count',
            'hit_rate', 'miss_rate', 'error_rate' => '%',
            default => 'count'
        };
    }

    /**
     * 메트릭에 따른 임계값 반환
     */
    private function getThreshold(string $metricName): string
    {
        return match ($metricName) {
            'request_time' => '1000', // 1초
            'query_time' => '1000', // 1초
            'hit_rate' => '80', // 80%
            'usage' => '128', // 128MB
            'error_rate' => '5', // 5%
            default => '100'
        };
    }

    /**
     * 값에 따른 상태 결정
     */
    private function determineStatus(float $value, string $metricName): string
    {
        $threshold = (float) $this->getThreshold($metricName);
        
        if ($metricName === 'hit_rate') {
            return $value < $threshold ? 'warning' : 'normal';
        }
        
        return $value > $threshold ? 'warning' : 'normal';
    }
} 