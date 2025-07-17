<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    private $route;
    
    public function __construct()
    {
        $this->route = 'admin.database.';
    }

    /**
     * 데이터베이스 대시보드
     */
    public function index(Request $request): View
    {
        try {
            // 데이터베이스 기본 정보
            $dbInfo = [
                'driver' => DB::getDriverName(),
                'database' => DB::getDatabaseName(),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'port' => config('database.connections.' . config('database.default') . '.port'),
                'charset' => config('database.connections.' . config('database.default') . '.charset'),
                'collation' => config('database.connections.' . config('database.default') . '.collation'),
            ];

            // 테이블 목록 및 통계
            $tables = [];
            $totalTables = 0;
            $totalRecords = 0;
            
            if (Schema::hasTable('migrations')) {
                $driver = DB::getDriverName();
                
                if ($driver === 'sqlite') {
                    $tableList = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                    foreach ($tableList as $table) {
                        $recordCount = DB::table($table->name)->count();
                        $tables[] = [
                            'name' => $table->name,
                            'records' => $recordCount,
                            'size' => $this->getTableSize($table->name, $driver)
                        ];
                        $totalRecords += $recordCount;
                    }
                } elseif ($driver === 'mysql') {
                    $dbName = DB::getDatabaseName();
                    $tableList = DB::select(
                        'SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH 
                         FROM information_schema.tables 
                         WHERE table_schema = ?',
                        [$dbName]
                    );
                    
                    foreach ($tableList as $table) {
                        $recordCount = DB::table($table->TABLE_NAME)->count();
                        $tables[] = [
                            'name' => $table->TABLE_NAME,
                            'records' => $recordCount,
                            'size' => $this->formatBytes($table->DATA_LENGTH + $table->INDEX_LENGTH)
                        ];
                        $totalRecords += $recordCount;
                    }
                }
                
                $totalTables = count($tables);
            }

            // 마이그레이션 통계
            $migrationStats = [
                'total' => DB::table('migrations')->count(),
                'latest_batch' => DB::table('migrations')->max('batch'),
                'total_batches' => DB::table('migrations')->distinct('batch')->count(),
            ];

            // 최근 마이그레이션 (최근 10개)
            $recentMigrations = DB::table('migrations')
                ->select('id', 'migration', 'batch')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // 데이터베이스 성능 정보
            $performance = [
                'connection_time' => $this->testConnectionTime(),
                'query_time' => $this->testQueryTime(),
            ];

            $route = $this->route;
            
            return view('jiny-admin::databases.dashboard', compact(
                'dbInfo', 
                'tables', 
                'totalTables', 
                'totalRecords', 
                'migrationStats', 
                'recentMigrations', 
                'performance', 
                'route'
            ));
            
        } catch (\Exception $e) {
            return view('jiny-admin::databases.dashboard', [
                'error' => $e->getMessage(),
                'route' => $this->route
            ]);
        }
    }

    /**
     * 테이블 크기 가져오기
     */
    private function getTableSize($tableName, $driver)
    {
        try {
            if ($driver === 'sqlite') {
                // SQLite는 정확한 크기 계산이 어려우므로 대략적인 추정
                $recordCount = DB::table($tableName)->count();
                return $this->formatBytes($recordCount * 1024); // 대략적인 추정
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * 바이트를 읽기 쉬운 형태로 변환
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes) / log(1024);
        $unit = $units[floor($base)];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $unit;
    }

    /**
     * 데이터베이스 연결 시간 테스트
     */
    private function testConnectionTime()
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $end = microtime(true);
            return round(($end - $start) * 1000, 2); // 밀리초
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * 쿼리 실행 시간 테스트
     */
    private function testQueryTime()
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            $end = microtime(true);
            return round(($end - $start) * 1000, 2); // 밀리초
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
} 