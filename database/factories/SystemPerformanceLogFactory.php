<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\App\Models\SystemPerformanceLog;

/**
 * SystemPerformanceLog 모델 팩토리
 */
class SystemPerformanceLogFactory extends Factory
{
    /**
     * 모델 클래스명
     */
    protected $model = SystemPerformanceLog::class;

    /**
     * 기본 정의
     */
    public function definition(): array
    {
        return [
            'metric_type' => $this->faker->randomElement([
                'cpu',
                'memory',
                'disk',
                'network',
                'database'
            ]),
            'metric_name' => $this->faker->randomElement([
                'cpu_usage',
                'memory_usage',
                'disk_read',
                'disk_write',
                'network_in',
                'network_out',
                'db_connections',
                'db_query_time'
            ]),
            'value' => $this->faker->randomFloat(2, 0, 100),
            'unit' => $this->faker->randomElement(['%', 'MB', 'GB', 'KB/s', 'MB/s', 'ms']),
            'threshold' => $this->faker->optional(0.7)->randomFloat(2, 50, 95),
            'status' => $this->faker->randomElement(['normal', 'warning', 'critical']),
            'measured_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'additional_data' => [
                'hostname' => $this->faker->randomElement([
                    'web-server-01',
                    'db-server-01',
                    'app-server-01',
                    'cache-server-01'
                ]),
                'ip' => $this->faker->ipv4,
                'environment' => $this->faker->randomElement(['production', 'staging', 'development']),
                'region' => $this->faker->randomElement(['us-east-1', 'us-west-2', 'eu-west-1', 'ap-northeast-1'])
            ],
            'additional_data' => $this->faker->optional(0.5)->randomElement([
                null,
                ['process_count' => $this->faker->numberBetween(10, 200)],
                ['thread_count' => $this->faker->numberBetween(50, 500)],
                ['cache_hit_rate' => $this->faker->randomFloat(2, 80, 99)],
                ['error_count' => $this->faker->numberBetween(0, 10)]
            ]),
        ];
    }

    /**
     * CPU 지표 상태
     */
    public function cpu(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_type' => 'cpu',
                'metric_name' => $this->faker->randomElement([
                    'cpu_usage',
                    'cpu_load_1m',
                    'cpu_load_5m',
                    'cpu_load_15m',
                    'cpu_idle',
                    'cpu_user',
                    'cpu_system'
                ]),
                'unit' => '%',
                'value' => $this->faker->randomFloat(2, 0, 100),
            ];
        });
    }

    /**
     * 메모리 지표 상태
     */
    public function memory(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_type' => 'memory',
                'metric_name' => $this->faker->randomElement([
                    'memory_usage',
                    'memory_available',
                    'memory_used',
                    'memory_free',
                    'swap_usage',
                    'swap_available'
                ]),
                'unit' => $this->faker->randomElement(['%', 'MB', 'GB']),
                'value' => $this->faker->randomFloat(2, 0, 100),
            ];
        });
    }

    /**
     * 디스크 지표 상태
     */
    public function disk(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_type' => 'disk',
                'metric_name' => $this->faker->randomElement([
                    'disk_usage',
                    'disk_read',
                    'disk_write',
                    'disk_iops',
                    'disk_latency',
                    'disk_queue_length'
                ]),
                'unit' => $this->faker->randomElement(['%', 'MB/s', 'KB/s', 'ms']),
                'value' => $this->faker->randomFloat(2, 0, 100),
            ];
        });
    }

    /**
     * 네트워크 지표 상태
     */
    public function network(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_type' => 'network',
                'metric_name' => $this->faker->randomElement([
                    'network_in',
                    'network_out',
                    'network_packets_in',
                    'network_packets_out',
                    'network_errors',
                    'network_dropped'
                ]),
                'unit' => $this->faker->randomElement(['MB/s', 'KB/s', 'packets/s']),
                'value' => $this->faker->randomFloat(2, 0, 1000),
            ];
        });
    }

    /**
     * 데이터베이스 지표 상태
     */
    public function database(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_type' => 'database',
                'metric_name' => $this->faker->randomElement([
                    'db_connections',
                    'db_query_time',
                    'db_queries_per_second',
                    'db_slow_queries',
                    'db_lock_time',
                    'db_buffer_pool_hit_rate'
                ]),
                'unit' => $this->faker->randomElement(['connections', 'ms', 'queries/s', '%']),
                'value' => $this->faker->randomFloat(2, 0, 1000),
            ];
        });
    }

    /**
     * 정상 상태
     */
    public function normal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'normal',
                'value' => $this->faker->randomFloat(2, 0, 70),
            ];
        });
    }

    /**
     * 경고 상태
     */
    public function warning(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'warning',
                'value' => $this->faker->randomFloat(2, 70, 90),
            ];
        });
    }

    /**
     * 위험 상태
     */
    public function critical(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'critical',
                'value' => $this->faker->randomFloat(2, 90, 100),
            ];
        });
    }

    /**
     * 높은 값
     */
    public function high(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => $this->faker->randomFloat(2, 80, 100),
            ];
        });
    }

    /**
     * 낮은 값
     */
    public function low(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => $this->faker->randomFloat(2, 0, 30),
            ];
        });
    }

    /**
     * 최근 데이터
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'measured_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            ];
        });
    }

    /**
     * 오래된 데이터
     */
    public function old(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'measured_at' => $this->faker->dateTimeBetween('-30 days', '-7 days'),
            ];
        });
    }

    /**
     * 프로덕션 환경
     */
    public function production(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'server_info' => array_merge($attributes['server_info'] ?? [], [
                    'environment' => 'production',
                    'region' => $this->faker->randomElement(['us-east-1', 'us-west-2'])
                ]),
            ];
        });
    }

    /**
     * 스테이징 환경
     */
    public function staging(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'server_info' => array_merge($attributes['server_info'] ?? [], [
                    'environment' => 'staging',
                    'region' => $this->faker->randomElement(['us-east-1', 'us-west-2'])
                ]),
            ];
        });
    }

    /**
     * 개발 환경
     */
    public function development(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'server_info' => array_merge($attributes['server_info'] ?? [], [
                    'environment' => 'development',
                    'region' => 'local'
                ]),
            ];
        });
    }
} 