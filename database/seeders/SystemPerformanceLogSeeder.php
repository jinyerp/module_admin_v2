<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Carbon\Carbon;

class SystemPerformanceLogSeeder extends Seeder
{
    /**
     * 시스템 성능 로그 테스트 데이터 생성
     */
    public function run(): void
    {
        $metricTypes = SystemPerformanceLog::getMetricTypes();
        $statuses = SystemPerformanceLog::getStatuses();
        
        // CPU 관련 테스트 데이터
        $this->createCpuLogs($metricTypes, $statuses);
        
        // 메모리 관련 테스트 데이터
        $this->createMemoryLogs($metricTypes, $statuses);
        
        // 디스크 관련 테스트 데이터
        $this->createDiskLogs($metricTypes, $statuses);
        
        // 네트워크 관련 테스트 데이터
        $this->createNetworkLogs($metricTypes, $statuses);
        
        // 데이터베이스 관련 테스트 데이터
        $this->createDatabaseLogs($metricTypes, $statuses);
    }
    
    private function createCpuLogs(array $metricTypes, array $statuses): void
    {
        $servers = ['web-server-01', 'web-server-02', 'db-server-01'];
        $components = ['nginx', 'php-fpm', 'mysql', 'redis'];
        
        for ($i = 0; $i < 50; $i++) {
            $value = rand(5, 95);
            $status = $value > 80 ? 'critical' : ($value > 60 ? 'warning' : 'normal');
            
            SystemPerformanceLog::create([
                'metric_name' => 'CPU 사용률',
                'metric_type' => 'cpu',
                'value' => $value,
                'unit' => '%',
                'threshold' => '80',
                'status' => $status,
                'server_name' => $servers[array_rand($servers)],
                'component' => $components[array_rand($components)],
                'additional_data' => [
                    'load_average' => [
                        '1min' => rand(10, 500) / 100,
                        '5min' => rand(10, 500) / 100,
                        '15min' => rand(10, 500) / 100
                    ],
                    'cpu_cores' => rand(4, 16),
                    'temperature' => rand(40, 80)
                ],
                'measured_at' => Carbon::now()->subMinutes(rand(0, 1440))
            ]);
        }
    }
    
    private function createMemoryLogs(array $metricTypes, array $statuses): void
    {
        $servers = ['web-server-01', 'web-server-02', 'db-server-01'];
        $components = ['nginx', 'php-fpm', 'mysql', 'redis'];
        
        for ($i = 0; $i < 40; $i++) {
            $value = rand(20, 95);
            $status = $value > 85 ? 'critical' : ($value > 70 ? 'warning' : 'normal');
            
            SystemPerformanceLog::create([
                'metric_name' => '메모리 사용률',
                'metric_type' => 'memory',
                'value' => $value,
                'unit' => '%',
                'threshold' => '85',
                'status' => $status,
                'server_name' => $servers[array_rand($servers)],
                'component' => $components[array_rand($components)],
                'additional_data' => [
                    'total_memory' => rand(8, 64) * 1024 * 1024 * 1024, // GB to bytes
                    'free_memory' => rand(1, 16) * 1024 * 1024 * 1024,
                    'swap_usage' => rand(0, 50)
                ],
                'measured_at' => Carbon::now()->subMinutes(rand(0, 1440))
            ]);
        }
    }
    
    private function createDiskLogs(array $metricTypes, array $statuses): void
    {
        $servers = ['web-server-01', 'web-server-02', 'db-server-01'];
        $components = ['/var/www', '/var/log', '/tmp', '/home'];
        
        for ($i = 0; $i < 30; $i++) {
            $value = rand(30, 95);
            $status = $value > 90 ? 'critical' : ($value > 80 ? 'warning' : 'normal');
            
            SystemPerformanceLog::create([
                'metric_name' => '디스크 사용률',
                'metric_type' => 'disk',
                'value' => $value,
                'unit' => '%',
                'threshold' => '90',
                'status' => $status,
                'server_name' => $servers[array_rand($servers)],
                'component' => $components[array_rand($components)],
                'additional_data' => [
                    'total_space' => rand(100, 2000) * 1024 * 1024 * 1024, // GB to bytes
                    'free_space' => rand(10, 200) * 1024 * 1024 * 1024,
                    'inodes_usage' => rand(10, 80),
                    'io_wait' => rand(0, 50)
                ],
                'measured_at' => Carbon::now()->subMinutes(rand(0, 1440))
            ]);
        }
    }
    
    private function createNetworkLogs(array $metricTypes, array $statuses): void
    {
        $servers = ['web-server-01', 'web-server-02', 'db-server-01'];
        $components = ['eth0', 'eth1', 'lo'];
        
        for ($i = 0; $i < 25; $i++) {
            $value = rand(10, 1000);
            $status = $value > 800 ? 'critical' : ($value > 500 ? 'warning' : 'normal');
            
            SystemPerformanceLog::create([
                'metric_name' => '네트워크 대역폭',
                'metric_type' => 'network',
                'value' => $value,
                'unit' => 'Mbps',
                'threshold' => '800',
                'status' => $status,
                'server_name' => $servers[array_rand($servers)],
                'component' => $components[array_rand($components)],
                'additional_data' => [
                    'packets_sent' => rand(1000, 100000),
                    'packets_received' => rand(1000, 100000),
                    'errors' => rand(0, 100),
                    'dropped' => rand(0, 50)
                ],
                'measured_at' => Carbon::now()->subMinutes(rand(0, 1440))
            ]);
        }
    }
    
    private function createDatabaseLogs(array $metricTypes, array $statuses): void
    {
        $servers = ['db-server-01', 'db-server-02'];
        $components = ['mysql', 'postgresql', 'redis', 'mongodb'];
        
        for ($i = 0; $i < 35; $i++) {
            $value = rand(50, 2000);
            $status = $value > 1500 ? 'critical' : ($value > 1000 ? 'warning' : 'normal');
            
            SystemPerformanceLog::create([
                'metric_name' => '데이터베이스 연결수',
                'metric_type' => 'database',
                'value' => $value,
                'unit' => 'connections',
                'threshold' => '1500',
                'status' => $status,
                'server_name' => $servers[array_rand($servers)],
                'component' => $components[array_rand($components)],
                'additional_data' => [
                    'active_connections' => rand(10, 500),
                    'idle_connections' => rand(5, 200),
                    'slow_queries' => rand(0, 50),
                    'query_time_avg' => rand(10, 500) / 1000 // ms
                ],
                'measured_at' => Carbon::now()->subMinutes(rand(0, 1440))
            ]);
        }
    }
} 