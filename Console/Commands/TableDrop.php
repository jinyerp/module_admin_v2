<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * 테이블 삭제 콘솔 명령
 * - 지정한 테이블을 데이터베이스에서 삭제
 * - 해당 테이블의 마이그레이션 파일도 함께 삭제
 */
class TableDrop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'table:drop {table : 삭제할 테이블명} {--force : 확인 없이 강제 삭제}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '지정한 테이블을 데이터베이스에서 삭제하고 관련 마이그레이션 파일도 함께 삭제';

    public function handle()
    {
        $tableName = $this->argument('table');
        $force = $this->option('force');

        $this->info('==== 테이블 삭제 ====');
        $this->line("테이블명: {$tableName}");

        // 테이블 존재 여부 확인
        if (!Schema::hasTable($tableName)) {
            $this->error("❌ 테이블 '{$tableName}'이 존재하지 않습니다.");
            return 1;
        }

        // 테이블 정보 표시
        $this->displayTableInfo($tableName);

        // 확인 (force 옵션이 없을 때만)
        if (!$force) {
            if (!$this->confirm("정말로 테이블 '{$tableName}'을 삭제하시겠습니까?", false)) {
                $this->info('작업이 취소되었습니다.');
                return 0;
            }
        }

        try {
            // 1. 테이블 삭제
            $this->info('데이터베이스에서 테이블을 삭제하는 중...');
            Schema::dropIfExists($tableName);
            $this->info('✅ 테이블이 성공적으로 삭제되었습니다.');

            // 2. 관련 마이그레이션 파일 찾기 및 삭제
            $this->info('관련 마이그레이션 파일을 찾는 중...');
            $migrationFiles = $this->findMigrationFiles($tableName);
            
            if (!empty($migrationFiles)) {
                $this->info('발견된 마이그레이션 파일:');
                foreach ($migrationFiles as $file) {
                    $this->line("  - {$file}");
                }

                if ($force || $this->confirm('이 마이그레이션 파일들도 삭제하시겠습니까?', false)) {
                    foreach ($migrationFiles as $file) {
                        if (File::delete($file)) {
                            $this->info("✅ {$file} 삭제 완료");
                        } else {
                            $this->warn("⚠️ {$file} 삭제 실패");
                        }
                    }
                }
            } else {
                $this->info('관련된 마이그레이션 파일을 찾을 수 없습니다.');
            }

            // 3. 마이그레이션 테이블에서 관련 레코드 삭제
            $this->cleanupMigrationTable($tableName);

            $this->info('✅ 테이블 삭제 작업이 완료되었습니다.');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ 테이블 삭제 중 오류가 발생했습니다: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * 테이블 정보를 표시
     */
    private function displayTableInfo($tableName)
    {
        $this->line('------------------------------');
        $this->info('테이블 정보:');
        
        // 컬럼 정보
        $columns = DB::select("DESCRIBE {$tableName}");
        $this->line("  컬럼 수: " . count($columns));
        
        // 레코드 수
        $count = DB::table($tableName)->count();
        $this->line("  레코드 수: " . number_format($count));
        
        // 테이블 크기 (MySQL의 경우)
        try {
            $tableSize = DB::select("
                SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size_MB'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ", [$tableName]);
            
            if (!empty($tableSize)) {
                $this->line("  테이블 크기: " . $tableSize[0]->Size_MB . " MB");
            }
        } catch (\Exception $e) {
            // MySQL이 아닌 경우 무시
        }
        
        $this->line('------------------------------');
    }

    /**
     * 테이블과 관련된 마이그레이션 파일들을 찾기
     */
    private function findMigrationFiles($tableName)
    {
        $migrationFiles = [];
        $migrationPaths = [
            database_path('migrations'),
            database_path('_migrations2'),
            jiny_path('auth/database/migrations'),
            jiny_path('admin/database/migrations'),
            jiny_path('affiliate/database/migrations'),
            jiny_path('teams/database/migrations'),
            jiny_path('wallet/database/migrations'),
        ];

        foreach ($migrationPaths as $path) {
            if (File::exists($path)) {
                $files = File::glob($path . '/*.php');
                foreach ($files as $file) {
                    $content = File::get($file);
                    
                    // 테이블명이 포함된 마이그레이션 파일 찾기
                    if (Str::contains($content, $tableName) || 
                        Str::contains($content, "'{$tableName}'") ||
                        Str::contains($content, "\"{$tableName}\"")) {
                        $migrationFiles[] = $file;
                    }
                }
            }
        }

        return $migrationFiles;
    }

    /**
     * 마이그레이션 테이블에서 관련 레코드 정리
     */
    private function cleanupMigrationTable($tableName)
    {
        try {
            // 마이그레이션 테이블에서 해당 테이블과 관련된 마이그레이션 레코드 찾기
            $migrations = DB::table('migrations')->get();
            $relatedMigrations = [];

            foreach ($migrations as $migration) {
                $migrationFile = database_path('migrations/' . $migration->migration . '.php');
                if (File::exists($migrationFile)) {
                    $content = File::get($migrationFile);
                    if (Str::contains($content, $tableName)) {
                        $relatedMigrations[] = $migration->migration;
                    }
                }
            }

            if (!empty($relatedMigrations)) {
                $this->info('마이그레이션 테이블에서 관련 레코드를 삭제하는 중...');
                foreach ($relatedMigrations as $migration) {
                    DB::table('migrations')->where('migration', $migration)->delete();
                    $this->line("  - {$migration} 레코드 삭제 완료");
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ 마이그레이션 테이블 정리 중 오류: " . $e->getMessage());
        }
    }
} 