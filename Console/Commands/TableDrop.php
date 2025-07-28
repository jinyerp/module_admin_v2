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
    protected $signature = 'table:drop {table : 삭제할 테이블명} {--force : 확인 없이 강제 삭제} ';

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

        // 와일드카드(*) 지원: team* 등
        $isWildcard = strpos($tableName, '*') !== false;
        $tablesToDrop = [];
        if ($isWildcard) {
            // 모든 테이블 목록에서 패턴 매칭
            $allTables = DB::select('SELECT name FROM sqlite_master WHERE type="table"');
            $tableList = array_map(function($row) { return $row->name; }, $allTables);
            // *를 임시 토큰으로 치환 후 escape, 마지막에 .*로 복원
            $wildcardToken = '___WILDCARD___';
            $patternRaw = str_replace('*', $wildcardToken, $tableName);
            $patternEscaped = preg_quote($patternRaw, '/');
            $pattern = '/^' . str_replace($wildcardToken, '.*', $patternEscaped) . '$/i';
            $tablesToDrop = array_filter($tableList, function($t) use ($pattern) {
                return preg_match($pattern, $t);
            });
            if (empty($tablesToDrop)) {
                $this->error("❌ 패턴에 매칭되는 테이블이 없습니다: {$tableName}");
                return 1;
            }
        } else {
            $tablesToDrop = [$tableName];
        }

        foreach ($tablesToDrop as $tbl) {
            if (!Schema::hasTable($tbl)) {
                $this->error("❌ 테이블 '{$tbl}'이 존재하지 않습니다.");
                // 유사 테이블명 추천
                $allTables = DB::select('SELECT name FROM sqlite_master WHERE type="table"');
                $tableList = array_map(function($row) { return $row->name; }, $allTables);
                $similar = array_filter($tableList, function($t) use ($tbl) {
                    return stripos($t, $tbl) !== false;
                });
                if ($similar) {
                    $this->info('비슷한 테이블명: ' . implode(', ', $similar));
                }
                $this->info('migrations 테이블에서 관련 레코드를 삭제하는 중...');
                $this->cleanMigrationRecords($tbl);
                $this->info('✅ migrations 레코드 삭제 작업이 완료되었습니다.');
                continue;
            }

            $this->displayTableInfo($tbl);

            // 확인 (force 옵션이 없을 때만)
            if (!$force) {
                if (!$this->confirm("정말로 테이블 '{$tbl}'을 삭제하시겠습니까?", false)) {
                    $this->info('작업이 취소되었습니다.');
                    continue;
                }
            }

            try {
                // 1. 테이블 삭제
                $this->info("데이터베이스에서 테이블 '{$tbl}'을 삭제하는 중...");
                Schema::dropIfExists($tbl);
                $this->info("✅ 테이블 '{$tbl}'이 성공적으로 삭제되었습니다.");
                $this->info('migrations 테이블에서 관련 레코드를 삭제하는 중...');
                $this->cleanMigrationRecords($tbl);
                $this->info('✅ 테이블 삭제 작업이 완료되었습니다.');
            } catch (\Exception $e) {
                $this->error("❌ 테이블 삭제 중 오류가 발생했습니다: " . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * 테이블 정보를 표시
     */
    private function displayTableInfo($tableName)
    {
        $this->line('------------------------------');
        $this->info('테이블 정보:');
        
        // DB 커넥션 드라이버 확인
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            // SQLite: PRAGMA 사용
            $columns = DB::select("PRAGMA table_info('$tableName')");
            $this->line("  컬럼 수: " . count($columns));
        } else {
            // MySQL 등: DESCRIBE 사용
            $columns = DB::select("DESCRIBE {$tableName}");
            $this->line("  컬럼 수: " . count($columns));
        }
        
        // 레코드 수
        $count = DB::table($tableName)->count();
        $this->line("  레코드 수: " . number_format($count));
        
        // (MySQL 전용) 테이블 크기 등은 생략 또는 분기
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
     * migrations 테이블에서 테이블명과 관련된 마이그레이션 레코드만 삭제
     */
    private function cleanMigrationRecords($tableName)
    {
        $migrations = DB::table('migrations')->get();
        $deleted = 0;
        foreach ($migrations as $migration) {
            if (stripos($migration->migration, $tableName) !== false) {
                DB::table('migrations')->where('id', $migration->id)->delete();
                $this->line("  - {$migration->migration} 레코드 삭제 완료");
                $deleted++;
            }
        }
        if ($deleted === 0) {
            $this->line('  - 관련된 마이그레이션 레코드가 없습니다.');
        }
    }
} 