<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Jiny\Admin\App\Models\SystemPerformanceLog>
 */
class SystemPerformanceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metricTypes = SystemPerformanceLog::getMetricTypes();
        $statuses = SystemPerformanceLog::getStatuses();
        
        $metricType = $this->faker->randomElement(array_keys($metricTypes));
        $value = $this->generateValueByType($metricType);
        $status = $this->determineStatus($metricType, $value);
        
        return [
            'metric_name' => $this->generateMetricName($metricType),
            'metric_type' => $metricType,
            'value' => $value,
            'unit' => $this->generateUnit($metricType),
            'threshold' => $this->generateThreshold($metricType),
            'status' => $status,
            'server_name' => $this->faker->randomElement([
                'web-server-01', 'web-server-02', 'db-server-01', 
                'app-server-01', 'cache-server-01', 'load-balancer-01'
            ]),
            'component' => $this->generateComponent($metricType),
            'additional_data' => $this->generateAdditionalData($metricType, $value),
            'measured_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
        ];
    }
    
    /**
     * CPU 메트릭용 상태
     */
    public function cpu(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => 'CPU 사용률',
            'metric_type' => 'cpu',
            'value' => $this->faker->numberBetween(5, 95),
            'unit' => '%',
            'threshold' => '80',
            'component' => $this->faker->randomElement(['nginx', 'php-fpm', 'mysql', 'redis', 'apache']),
            'additional_data' => [
                'load_average' => [
                    '1min' => $this->faker->randomFloat(2, 0.1, 5.0),
                    '5min' => $this->faker->randomFloat(2, 0.1, 5.0),
                    '15min' => $this->faker->randomFloat(2, 0.1, 5.0)
                ],
                'cpu_cores' => $this->faker->numberBetween(4, 16),
                'temperature' => $this->faker->numberBetween(40, 80)
            ]
        ]);
    }
    
    /**
     * 메모리 메트릭용 상태
     */
    public function memory(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => '메모리 사용률',
            'metric_type' => 'memory',
            'value' => $this->faker->numberBetween(20, 95),
            'unit' => '%',
            'threshold' => '85',
            'component' => $this->faker->randomElement(['nginx', 'php-fpm', 'mysql', 'redis', 'apache']),
            'additional_data' => [
                'total_memory' => $this->faker->numberBetween(8, 64) * 1024 * 1024 * 1024,
                'free_memory' => $this->faker->numberBetween(1, 16) * 1024 * 1024 * 1024,
                'swap_usage' => $this->faker->numberBetween(0, 50)
            ]
        ]);
    }
    
    /**
     * 디스크 메트릭용 상태
     */
    public function disk(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => '디스크 사용률',
            'metric_type' => 'disk',
            'value' => $this->faker->numberBetween(30, 95),
            'unit' => '%',
            'threshold' => '90',
            'component' => $this->faker->randomElement(['/var/www', '/var/log', '/tmp', '/home', '/usr']),
            'additional_data' => [
                'total_space' => $this->faker->numberBetween(100, 2000) * 1024 * 1024 * 1024,
                'free_space' => $this->faker->numberBetween(10, 200) * 1024 * 1024 * 1024,
                'inodes_usage' => $this->faker->numberBetween(10, 80),
                'io_wait' => $this->faker->numberBetween(0, 50)
            ]
        ]);
    }
    
    /**
     * 네트워크 메트릭용 상태
     */
    public function network(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => '네트워크 대역폭',
            'metric_type' => 'network',
            'value' => $this->faker->numberBetween(10, 1000),
            'unit' => 'Mbps',
            'threshold' => '800',
            'component' => $this->faker->randomElement(['eth0', 'eth1', 'lo', 'wlan0']),
            'additional_data' => [
                'packets_sent' => $this->faker->numberBetween(1000, 100000),
                'packets_received' => $this->faker->numberBetween(1000, 100000),
                'errors' => $this->faker->numberBetween(0, 100),
                'dropped' => $this->faker->numberBetween(0, 50)
            ]
        ]);
    }
    
    /**
     * 데이터베이스 메트릭용 상태
     */
    public function database(): static
    {
        return $this->state(fn (array $attributes) => [
            'metric_name' => '데이터베이스 연결수',
            'metric_type' => 'database',
            'value' => $this->faker->numberBetween(50, 2000),
            'unit' => 'connections',
            'threshold' => '1500',
            'component' => $this->faker->randomElement(['mysql', 'postgresql', 'redis', 'mongodb']),
            'additional_data' => [
                'active_connections' => $this->faker->numberBetween(10, 500),
                'idle_connections' => $this->faker->numberBetween(5, 200),
                'slow_queries' => $this->faker->numberBetween(0, 50),
                'query_time_avg' => $this->faker->randomFloat(3, 0.01, 0.5)
            ]
        ]);
    }
    
    /**
     * 경고 상태용 상태
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'warning'
        ]);
    }
    
    /**
     * 치명적 상태용 상태
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'critical'
        ]);
    }
    
    /**
     * 최근 데이터용 상태 (24시간 이내)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'measured_at' => $this->faker->dateTimeBetween('-24 hours', 'now')
        ]);
    }
    
    /**
     * 메트릭 타입에 따른 값 생성
     */
    private function generateValueByType(string $metricType): float
    {
        return match($metricType) {
            'cpu' => $this->faker->numberBetween(5, 95),
            'memory' => $this->faker->numberBetween(20, 95),
            'disk' => $this->faker->numberBetween(30, 95),
            'network' => $this->faker->numberBetween(10, 1000),
            'database' => $this->faker->numberBetween(50, 2000),
            default => $this->faker->numberBetween(0, 100)
        };
    }
    
    /**
     * 메트릭 타입에 따른 상태 결정
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
            return $this->faker->randomElement(['normal', 'warning', 'critical']);
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
    
    /**
     * 메트릭 타입에 따른 이름 생성
     */
    private function generateMetricName(string $metricType): string
    {
        return match($metricType) {
            'cpu' => 'CPU 사용률',
            'memory' => '메모리 사용률',
            'disk' => '디스크 사용률',
            'network' => '네트워크 대역폭',
            'database' => '데이터베이스 연결수',
            'application' => '애플리케이션 응답시간',
            'service' => '서비스 가용성',
            default => '시스템 메트릭'
        };
    }
    
    /**
     * 메트릭 타입에 따른 단위 생성
     */
    private function generateUnit(string $metricType): string
    {
        return match($metricType) {
            'cpu' => '%',
            'memory' => '%',
            'disk' => '%',
            'network' => 'Mbps',
            'database' => 'connections',
            'application' => 'ms',
            'service' => '%',
            default => 'unit'
        };
    }
    
    /**
     * 메트릭 타입에 따른 임계값 생성
     */
    private function generateThreshold(string $metricType): string
    {
        return match($metricType) {
            'cpu' => '80',
            'memory' => '85',
            'disk' => '90',
            'network' => '800',
            'database' => '1500',
            'application' => '1000',
            'service' => '95',
            default => '100'
        };
    }
    
    /**
     * 메트릭 타입에 따른 컴포넌트 생성
     */
    private function generateComponent(string $metricType): string
    {
        return match($metricType) {
            'cpu' => $this->faker->randomElement(['nginx', 'php-fpm', 'mysql', 'redis', 'apache']),
            'memory' => $this->faker->randomElement(['nginx', 'php-fpm', 'mysql', 'redis', 'apache']),
            'disk' => $this->faker->randomElement(['/var/www', '/var/log', '/tmp', '/home', '/usr']),
            'network' => $this->faker->randomElement(['eth0', 'eth1', 'lo', 'wlan0']),
            'database' => $this->faker->randomElement(['mysql', 'postgresql', 'redis', 'mongodb']),
            'application' => $this->faker->randomElement(['web-app', 'api-service', 'auth-service']),
            'service' => $this->faker->randomElement(['web-server', 'db-server', 'cache-server']),
            default => 'system'
        };
    }
    
    /**
     * 메트릭 타입에 따른 추가 데이터 생성
     */
    private function generateAdditionalData(string $metricType, float $value): array
    {
        return match($metricType) {
            'cpu' => [
                'load_average' => [
                    '1min' => $this->faker->randomFloat(2, 0.1, 5.0),
                    '5min' => $this->faker->randomFloat(2, 0.1, 5.0),
                    '15min' => $this->faker->randomFloat(2, 0.1, 5.0)
                ],
                'cpu_cores' => $this->faker->numberBetween(4, 16),
                'temperature' => $this->faker->numberBetween(40, 80)
            ],
            'memory' => [
                'total_memory' => $this->faker->numberBetween(8, 64) * 1024 * 1024 * 1024,
                'free_memory' => $this->faker->numberBetween(1, 16) * 1024 * 1024 * 1024,
                'swap_usage' => $this->faker->numberBetween(0, 50)
            ],
            'disk' => [
                'total_space' => $this->faker->numberBetween(100, 2000) * 1024 * 1024 * 1024,
                'free_space' => $this->faker->numberBetween(10, 200) * 1024 * 1024 * 1024,
                'inodes_usage' => $this->faker->numberBetween(10, 80),
                'io_wait' => $this->faker->numberBetween(0, 50)
            ],
            'network' => [
                'packets_sent' => $this->faker->numberBetween(1000, 100000),
                'packets_received' => $this->faker->numberBetween(1000, 100000),
                'errors' => $this->faker->numberBetween(0, 100),
                'dropped' => $this->faker->numberBetween(0, 50)
            ],
            'database' => [
                'active_connections' => $this->faker->numberBetween(10, 500),
                'idle_connections' => $this->faker->numberBetween(5, 200),
                'slow_queries' => $this->faker->numberBetween(0, 50),
                'query_time_avg' => $this->faker->randomFloat(3, 0.01, 0.5)
            ],
            default => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0.0'
            ]
        };
    }
} 