<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DatabaseMigrationStatusController extends Controller
{
    /**
     * 마이그레이션 목록 표시
     */
    public function index(): View
    {
        $migrations = $this->getMigrationStatus();
        
        return view('jiny-admin::migrations.index', compact('migrations'));
    }
    
    /**
     * 마이그레이션 상태 확인
     */
    public function status(): View
    {
        $migrations = $this->getMigrationStatus();
        
        return view('jiny-admin::migrations.status', compact('migrations'));
    }
    
    /**
     * 마이그레이션 상태 API 응답
     */
    public function statusApi()
    {
        $migrations = $this->getMigrationStatus();
        
        return response()->json([
            'success' => true,
            'data' => $migrations
        ]);
    }
    
    /**
     * 마이그레이션 상태 가져오기
     */
    private function getMigrationStatus(): array
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return [
                    'error' => 'migrations 테이블이 존재하지 않습니다.',
                    'migrations' => [],
                    'pending' => 0,
                    'ran' => 0
                ];
            }
            
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
            $files = glob(database_path('migrations/*.php'));
            $fileMigrations = [];
            
            foreach ($files as $file) {
                $filename = basename($file, '.php');
                $fileMigrations[] = $filename;
            }
            
            $pending = array_diff($fileMigrations, $ranMigrations);
            $ran = array_intersect($fileMigrations, $ranMigrations);
            
            $migrations = [];
            
            // 실행된 마이그레이션
            foreach ($ran as $migration) {
                $migrations[] = [
                    'migration' => $migration,
                    'status' => 'ran',
                    'batch' => DB::table('migrations')->where('migration', $migration)->value('batch')
                ];
            }
            
            // 대기 중인 마이그레이션
            foreach ($pending as $migration) {
                $migrations[] = [
                    'migration' => $migration,
                    'status' => 'pending',
                    'batch' => null
                ];
            }
            
            // 마이그레이션 이름으로 정렬
            usort($migrations, function($a, $b) {
                return strcmp($a['migration'], $b['migration']);
            });
            
            return [
                'migrations' => $migrations,
                'pending' => count($pending),
                'ran' => count($ran),
                'total' => count($fileMigrations)
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'migrations' => [],
                'pending' => 0,
                'ran' => 0
            ];
        }
    }
    
    /**
     * 마이그레이션 배치 정보
     */
    public function batches()
    {
        try {
            $batches = DB::table('migrations')
                ->select('batch', DB::raw('COUNT(*) as count'), DB::raw('MIN(migration) as first_migration'), DB::raw('MAX(migration) as last_migration'))
                ->groupBy('batch')
                ->orderBy('batch', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $batches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 특정 배치의 마이그레이션 목록
     */
    public function batchMigrations($batch)
    {
        try {
            $migrations = DB::table('migrations')
                ->where('batch', $batch)
                ->orderBy('migration')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $migrations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 