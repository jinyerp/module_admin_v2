<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class TableFresh extends Command
{
    protected $signature = 'table:fresh {table}';
    protected $description = '지정한 테이블만 드롭 후 해당 마이그레이션을 다시 실행합니다.';

    public function handle()
    {
        $table = $this->argument('table');
        if (!Schema::hasTable($table)) {
            $this->warn("[SKIP] 테이블이 존재하지 않습니다: {$table}");
        } else {
            Schema::drop($table);
            $this->info("[DROP] 테이블 삭제 완료: {$table}");
        }

        // 마이그레이션 파일 경로 추정 (규칙: *_create_{table}_table.php)
        $migrationPath = null;
        $migrationDirs = [
            base_path('database/migrations'),
            base_path('jiny/auth/database/migrations'),
            base_path('jiny/admin/database/migrations'),
        ];
        $pattern = "create_{$table}_table.php";
        foreach ($migrationDirs as $dir) {
            if (!is_dir($dir)) continue;
            foreach (scandir($dir) as $file) {
                if (str_ends_with($file, $pattern)) {
                    $migrationPath = $dir . DIRECTORY_SEPARATOR . $file;
                    break 2;
                }
            }
        }
        if ($migrationPath && file_exists($migrationPath)) {
            Artisan::call('migrate', [
                '--path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationPath),
                '--force' => true
            ]);
            $this->info("[MIGRATE] 마이그레이션 재실행 완료: {$migrationPath}");
        } else {
            $this->error("[FAIL] 마이그레이션 파일을 찾을 수 없습니다: {$pattern}");
        }
    }
} 