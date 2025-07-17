<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin\AdminSetting;
use Carbon\Carbon;

/**
 * 관리자 대시보드 컨트롤러
 * - admin guard로 인증된 관리자만 접근 가능 (미들웨어는 라우트에서 처리)
 * - 인증된 관리자 정보를 blade에 전달
 */
class AdminDashboard extends Controller
{
    /**
     * 관리자 대시보드
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        
        // 시스템 정보
        $systemInfo = $this->getSystemInfo();
        
        // 관리자 목록
        // 설정값 목록 예시
        $settings = AdminSetting::all();
        
        // 데이터베이스 정보
        $databaseInfo = $this->getDatabaseInfo();
        
        // 대시보드 통계
        // $statsService = new DashboardStatsService();
        // $stats = $statsService->getStats();
        $stats = [];
        
        return view('jiny-admin::dashboard', [
            'admin' => $admin,
            'systemInfo' => $systemInfo,
            'settings' => $settings,
            'databaseInfo' => $databaseInfo,
            'stats' => $stats
        ]);
    }
    
    /**
     * 시스템 정보 가져오기
     */
    private function getSystemInfo(): array
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'current_time' => now()->format('Y-m-d H:i:s'),
            'uptime' => $this->getUptime(),
        ];
    }
    
    /**
     * 데이터베이스 정보 가져오기
     */
    private function getDatabaseInfo(): array
    {
        try {
            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();
            $driver = $connection->getDriverName();

            // 드라이버별 테이블 목록 쿼리
            $tables = [];
            switch ($driver) {
                case 'sqlite':
                    $tables = collect($connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                        ->pluck('name')->toArray();
                    break;
                case 'mysql':
                case 'mariadb':
                    $tables = collect($connection->select("SHOW TABLES"))->map(function ($row) {
                        return array_values((array)$row)[0];
                    })->toArray();
                    break;
                case 'pgsql':
                    $tables = collect($connection->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'"))
                        ->pluck('tablename')->toArray();
                    break;
                case 'sqlsrv':
                    $tables = collect($connection->select("SELECT name FROM sys.tables"))->pluck('name')->toArray();
                    break;
            }

            $additionalInfo = $this->getDatabaseSpecificInfo($connection, $driver);

            return [
                'connection' => config('database.default'),
                'driver' => $driver,
                'database_name' => $databaseName,
                'table_count' => count($tables),
                'table_list' => array_slice($tables, 0, 10), // 최대 10개 테이블명만
                'migration_table' => 'migrations',
                'has_migrations' => Schema::hasTable('migrations'),
                'migration_count' => DB::table('migrations')->count(),
                'last_migration' => DB::table('migrations')->orderBy('id', 'desc')->first(),
                'additional_info' => $additionalInfo,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
                'driver' => 'unknown',
            ];
        }
    }
    
    /**
     * 데이터베이스별 특정 정보 가져오기
     */
    private function getDatabaseSpecificInfo($connection, $driver): array
    {
        $info = [];
        
        try {
            switch ($driver) {
                case 'sqlite':
                    $info['database_file'] = $connection->getDatabaseName();
                    $info['file_size'] = file_exists($info['database_file']) ? 
                        $this->formatBytes(filesize($info['database_file'])) : 'N/A';
                    break;
                    
                case 'mysql':
                case 'mariadb':
                    $version = $connection->select('SELECT VERSION() as version')[0]->version ?? 'Unknown';
                    $info['version'] = $version;
                    $info['charset'] = $connection->select("SHOW VARIABLES LIKE 'character_set_database'")[0]->Value ?? 'Unknown';
                    $info['collation'] = $connection->select("SHOW VARIABLES LIKE 'collation_database'")[0]->Value ?? 'Unknown';
                    
                    // 데이터베이스 크기 (MySQL)
                    $sizeQuery = "SELECT 
                        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                        FROM information_schema.tables 
                        WHERE table_schema = ?";
                    $sizeResult = $connection->select($sizeQuery, [$connection->getDatabaseName()]);
                    $info['database_size'] = $sizeResult[0]->size_mb ?? 0;
                    break;
                    
                case 'pgsql':
                    $version = $connection->select('SELECT version() as version')[0]->version ?? 'Unknown';
                    $info['version'] = $version;
                    
                    // 데이터베이스 크기 (PostgreSQL)
                    $sizeQuery = "SELECT pg_size_pretty(pg_database_size(?)) as size";
                    $sizeResult = $connection->select($sizeQuery, [$connection->getDatabaseName()]);
                    $info['database_size'] = $sizeResult[0]->size ?? 'Unknown';
                    break;
                    
                case 'sqlsrv':
                    $version = $connection->select('SELECT @@VERSION as version')[0]->version ?? 'Unknown';
                    $info['version'] = $version;
                    break;
            }
        } catch (\Exception $e) {
            $info['error'] = $e->getMessage();
        }
        
        return $info;
    }
    
    /**
     * 바이트를 읽기 쉬운 형태로 변환
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 시스템 업타임 정보
     */
    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return implode(', ', array_map(function($load) {
                return number_format($load, 2);
            }, $load));
        }
        return 'N/A';
    }
}
