<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Jiny\Admin\App\Models\SystemBackupLog;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminSystemBackupLogController extends Controller
{
    /**
     * 백업 로그 목록 페이지
     */
    public function index(Request $request): View
    {
        $query = SystemBackupLog::with(['startedBy']);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $backupLogs = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getStats($request);

        return view('jiny-admin::admin.system_backup_logs.index', [
            'rows' => $backupLogs,
            'backupLogs' => $backupLogs,
            'stats' => $stats,
        ]);
    }

    /**
     * 백업 실행 페이지
     */
    public function createBackup(): View
    {
        $backupOptions = [
            'database' => [
                'name' => '데이터베이스 백업',
                'description' => '전체 데이터베이스 또는 특정 테이블 백업',
                'tables' => $this->getDatabaseTables(),
            ],
            'files' => [
                'name' => '파일 시스템 백업',
                'description' => '업로드된 파일, 로그, 설정 파일 백업',
                'directories' => [
                    'storage/app/public/uploads' => '사용자 업로드 파일',
                    'storage/logs' => '로그 파일',
                    'storage/framework/cache' => '캐시 파일',
                    'config' => '설정 파일',
                ],
            ],
            'code' => [
                'name' => '소스 코드 백업',
                'description' => '애플리케이션 코드 및 뷰 파일 백업',
                'directories' => [
                    'app' => '애플리케이션 코드',
                    'resources/views' => '뷰 템플릿',
                    'database/migrations' => '마이그레이션 파일',
                    'routes' => '라우트 파일',
                ],
            ],
            'full' => [
                'name' => '전체 시스템 백업',
                'description' => '데이터베이스, 파일, 코드 전체 백업',
            ],
        ];

        return view('jiny-admin::admin.system_backup_logs.create-backup', [
            'backupOptions' => $backupOptions,
        ]);
    }

    /**
     * 백업 실행
     */
    public function executeBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_type' => 'required|string|in:database,files,code,full',
            'backup_name' => 'required|string|max:255',
            'selected_tables' => 'nullable|array',
            'selected_directories' => 'nullable|array',
            'compression' => 'boolean',
            'encryption' => 'boolean',
        ]);

        try {
            $backupLog = SystemBackupLog::create([
                'backup_type' => $request->backup_type,
                'backup_name' => $request->backup_name,
                'status' => 'running',
                'started_at' => now(),
                'initiated_by' => auth()->id(),
            ]);

            // 백그라운드에서 백업 실행
            dispatch(function() use ($request, $backupLog) {
                $this->performBackup($request, $backupLog);
            })->afterResponse();

            return redirect()->route('admin.systems.backup-logs.index')
                ->with('success', '백업이 시작되었습니다. 완료 후 다운로드가 가능합니다.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '백업 시작 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 백업 다운로드
     */
    public function downloadBackup(SystemBackupLog $systemBackupLog): Response
    {
        if ($systemBackupLog->status !== 'completed') {
            abort(400, '백업이 완료되지 않았습니다.');
        }

        if (!$systemBackupLog->file_path || !File::exists($systemBackupLog->file_path)) {
            abort(404, '백업 파일을 찾을 수 없습니다.');
        }

        $fileName = basename($systemBackupLog->file_path);
        
        return response()->download($systemBackupLog->file_path, $fileName, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * 백업 파일 삭제
     */
    public function deleteBackupFile(SystemBackupLog $systemBackupLog): RedirectResponse
    {
        if ($systemBackupLog->file_path && File::exists($systemBackupLog->file_path)) {
            File::delete($systemBackupLog->file_path);
        }

        $systemBackupLog->update([
            'file_path' => null,
            'file_size' => null,
        ]);

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', '백업 파일이 삭제되었습니다.');
    }

    /**
     * 백업 로그 생성 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::admin.system_backup_logs.create', [
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'admins' => $admins,
        ]);
    }

    /**
     * 백업 로그 저장
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_type' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getBackupTypes())),
            'backup_name' => 'required|string|max:255',
            'file_path' => 'nullable|string|max:500',
            'file_size' => 'nullable|string|max:100',
            'checksum' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getStatuses())),
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after_or_equal:started_at',
            'duration_seconds' => 'nullable|integer|min:0',
            'error_message' => 'nullable|string',
            'initiated_by' => 'nullable|exists:admin_emails,id',
            'storage_location' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_compressed' => 'boolean',
            'metadata' => 'nullable|json',
        ]);

        SystemBackupLog::create($request->all());

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 백업 로그 상세 조회
     */
    public function show(SystemBackupLog $systemBackupLog): View
    {
        $systemBackupLog->load('initiatedBy');

        return view('jiny-admin::admin.system_backup_logs.show', [
            'backupLog' => $systemBackupLog,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
        ]);
    }

    /**
     * 백업 로그 수정 폼
     */
    public function edit(SystemBackupLog $systemBackupLog): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::admin.system_backup_logs.edit', [
            'backupLog' => $systemBackupLog,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'admins' => $admins,
        ]);
    }

    /**
     * 백업 로그 업데이트
     */
    public function update(Request $request, SystemBackupLog $systemBackupLog): RedirectResponse
    {
        $request->validate([
            'backup_type' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getBackupTypes())),
            'backup_name' => 'required|string|max:255',
            'file_path' => 'nullable|string|max:500',
            'file_size' => 'nullable|string|max:100',
            'checksum' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getStatuses())),
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after_or_equal:started_at',
            'duration_seconds' => 'nullable|integer|min:0',
            'error_message' => 'nullable|string',
            'initiated_by' => 'nullable|exists:admin_emails,id',
            'storage_location' => 'storage_location',
            'is_encrypted' => 'boolean',
            'is_compressed' => 'boolean',
            'metadata' => 'nullable|json',
        ]);

        $systemBackupLog->update($request->all());

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 백업 로그 삭제
     */
    public function destroy(SystemBackupLog $systemBackupLog): RedirectResponse
    {
        // 백업 파일도 함께 삭제
        if ($systemBackupLog->file_path && File::exists($systemBackupLog->file_path)) {
            File::delete($systemBackupLog->file_path);
        }

        $systemBackupLog->delete();

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 삭제되었습니다.');
    }

    /**
     * 백업 로그 상태 변경
     */
    public function updateStatus(Request $request, SystemBackupLog $systemBackupLog): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getStatuses())),
        ]);

        $systemBackupLog->update(['status' => $request->status]);

        $statusText = SystemBackupLog::getStatuses()[$request->status];
        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', "백업 로그 상태가 '{$statusText}'로 변경되었습니다.");
    }

    /**
     * 백업 로그 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => SystemBackupLog::count(),
            'completed' => SystemBackupLog::completed()->count(),
            'failed' => SystemBackupLog::failed()->count(),
            'success_rate' => SystemBackupLog::getSuccessRate(),
            'avg_duration' => SystemBackupLog::getAverageDuration(),
            'recent_stats' => SystemBackupLog::getRecentStats(30),
            'stats_by_type' => SystemBackupLog::getStatsByType(),
            'performance_analysis' => SystemBackupLog::getPerformanceAnalysis(),
            'failure_analysis' => SystemBackupLog::getFailureAnalysis(),
            'backup_policy' => SystemBackupLog::validateBackupPolicy(),
            'recommendations' => SystemBackupLog::getRecommendations(),
        ];

        return view('jiny-admin::admin.system_backup_logs.stats', [
            'stats' => $stats,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
        ]);
    }

    /**
     * 백업 로그 일괄 삭제
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_backup_logs,id',
        ]);

        $backupLogs = SystemBackupLog::whereIn('id', $request->selected_logs)->get();
        
        foreach ($backupLogs as $backupLog) {
            if ($backupLog->file_path && File::exists($backupLog->file_path)) {
                File::delete($backupLog->file_path);
            }
            $backupLog->delete();
        }

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', count($backupLogs) . "개의 백업 로그가 성공적으로 삭제되었습니다.");
    }

    /**
     * 백업 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemBackupLog::with('initiatedBy');

        // 필터 적용
        $query = $this->applyFilters($query, $request);

        $backupLogs = $query->get();

        // CSV 파일 생성 (실제 구현에서는 Excel/CSV 라이브러리 사용)
        $filename = 'backup_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!File::exists(dirname($filepath))) {
            File::makeDirectory(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        
        // 헤더 작성
        fputcsv($handle, [
            'ID', '백업 타입', '백업명', '상태', '시작 시간', '완료 시간', 
            '소요 시간(초)', '파일 크기', '시작한 관리자', '생성일'
        ]);

        // 데이터 작성
        foreach ($backupLogs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->backup_type,
                $log->backup_name,
                $log->status,
                $log->started_at?->format('Y-m-d H:i:s'),
                $log->completed_at?->format('Y-m-d H:i:s'),
                $log->duration_seconds,
                $log->file_size,
                $log->initiatedBy?->name,
                $log->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);

        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }

    /**
     * 데이터베이스 테이블 목록 조회
     */
    private function getDatabaseTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableList = [];
        
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $tableList[$tableName] = $tableName;
        }
        
        return $tableList;
    }

    /**
     * 실제 백업 수행
     */
    private function performBackup(Request $request, SystemBackupLog $backupLog): void
    {
        try {
            $backupPath = storage_path('backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $fileName = $backupLog->backup_name . '_' . date('Y-m-d_H-i-s');
            $filePath = $backupPath . '/' . $fileName;

            switch ($request->backup_type) {
                case 'database':
                    $this->backupDatabase($request, $filePath);
                    break;
                case 'files':
                    $this->backupFiles($request, $filePath);
                    break;
                case 'code':
                    $this->backupCode($request, $filePath);
                    break;
                case 'full':
                    $this->backupFull($request, $filePath);
                    break;
            }

            // 압축 처리
            if ($request->compression) {
                $filePath = $this->compressFile($filePath);
            }

            // 암호화 처리
            if ($request->encryption) {
                $filePath = $this->encryptFile($filePath);
            }

            $fileSize = File::size($filePath);
            $checksum = md5_file($filePath);

            $backupLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration_seconds' => now()->diffInSeconds($backupLog->started_at),
                'file_path' => $filePath,
                'file_size' => $this->formatFileSize($fileSize),
                'checksum' => $checksum,
                'is_compressed' => $request->compression,
                'is_encrypted' => $request->encryption,
            ]);

        } catch (\Exception $e) {
            $backupLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_seconds' => now()->diffInSeconds($backupLog->started_at),
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 데이터베이스 백업
     */
    private function backupDatabase(Request $request, string $filePath): void
    {
        $tables = $request->get('selected_tables', []);
        $database = config('database.connections.mysql.database');
        
        if (empty($tables)) {
            // 전체 데이터베이스 백업
            $command = "mysqldump -u " . config('database.connections.mysql.username') . 
                      " -p" . config('database.connections.mysql.password') . 
                      " {$database} > {$filePath}.sql";
        } else {
            // 선택된 테이블만 백업
            $tableList = implode(' ', $tables);
            $command = "mysqldump -u " . config('database.connections.mysql.username') . 
                      " -p" . config('database.connections.mysql.password') . 
                      " {$database} {$tableList} > {$filePath}.sql";
        }
        
        exec($command);
        
        if (!File::exists($filePath . '.sql')) {
            throw new \Exception('데이터베이스 백업 실패');
        }
        
        File::move($filePath . '.sql', $filePath);
    }

    /**
     * 파일 시스템 백업
     */
    private function backupFiles(Request $request, string $filePath): void
    {
        $directories = $request->get('selected_directories', []);
        $zip = new ZipArchive();
        
        if ($zip->open($filePath . '.zip', ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('ZIP 파일 생성 실패');
        }
        
        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                $this->addDirectoryToZip($zip, $directory, basename($directory));
            }
        }
        
        $zip->close();
        File::move($filePath . '.zip', $filePath);
    }

    /**
     * 코드 백업
     */
    private function backupCode(Request $request, string $filePath): void
    {
        $directories = $request->get('selected_directories', []);
        $zip = new ZipArchive();
        
        if ($zip->open($filePath . '.zip', ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('ZIP 파일 생성 실패');
        }
        
        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                $this->addDirectoryToZip($zip, $directory, basename($directory));
            }
        }
        
        $zip->close();
        File::move($filePath . '.zip', $filePath);
    }

    /**
     * 전체 시스템 백업
     */
    private function backupFull(Request $request, string $filePath): void
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filePath . '.zip', ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('ZIP 파일 생성 실패');
        }
        
        // 데이터베이스 백업
        $this->backupDatabase($request, $filePath . '_db.sql');
        $zip->addFile($filePath . '_db.sql', 'database.sql');
        
        // 파일 시스템 백업
        $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/app/public');
        $this->addDirectoryToZip($zip, storage_path('logs'), 'storage/logs');
        $this->addDirectoryToZip($zip, config_path(), 'config');
        
        // 코드 백업
        $this->addDirectoryToZip($zip, app_path(), 'app');
        $this->addDirectoryToZip($zip, resource_path('views'), 'resources/views');
        $this->addDirectoryToZip($zip, database_path('migrations'), 'database/migrations');
        
        $zip->close();
        File::move($filePath . '.zip', $filePath);
        File::delete($filePath . '_db.sql');
    }

    /**
     * ZIP에 디렉토리 추가
     */
    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($directory) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * 파일 압축
     */
    private function compressFile(string $filePath): string
    {
        $compressedPath = $filePath . '.gz';
        $command = "gzip -9 {$filePath}";
        exec($command);
        
        return $compressedPath;
    }

    /**
     * 파일 암호화
     */
    private function encryptFile(string $filePath): string
    {
        $encryptedPath = $filePath . '.enc';
        $password = config('app.key');
        $command = "openssl enc -aes-256-cbc -salt -in {$filePath} -out {$encryptedPath} -pass pass:{$password}";
        exec($command);
        
        File::delete($filePath);
        return $encryptedPath;
    }

    /**
     * 파일 크기 포맷팅
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 검색 필터 적용
     */
    private function applyFilters($query, Request $request)
    {
        // 백업 타입 필터
        if ($request->filled('filter_backup_type')) {
            $query->where('backup_type', $request->filter_backup_type);
        }

        // 상태 필터
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // 암호화 여부 필터
        if ($request->filled('filter_is_encrypted')) {
            $query->where('is_encrypted', $request->filter_is_encrypted);
        }

        // 압축 여부 필터
        if ($request->filled('filter_is_compressed')) {
            $query->where('is_compressed', $request->filter_is_compressed);
        }

        // 날짜 범위 필터
        if ($request->filled('filter_start_date')) {
            $query->where('created_at', '>=', $request->filter_start_date);
        }

        if ($request->filled('filter_end_date')) {
            $query->where('created_at', '<=', $request->filter_end_date . ' 23:59:59');
        }

        // 검색어 필터
        if ($request->filled('filter_search')) {
            $search = $request->filter_search;
            $query->where(function($q) use ($search) {
                $q->where('backup_name', 'like', '%' . $search . '%')
                  ->orWhere('file_path', 'like', '%' . $search . '%')
                  ->orWhere('error_message', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    /**
     * 정렬 적용
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('direction', 'desc');

        // 허용된 정렬 필드만 사용
        $allowedSortFields = [
            'created_at', 'backup_name', 'backup_type', 'status', 
            'duration_seconds', 'started_at', 'completed_at'
        ];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * 통계 데이터 조회
     */
    private function getStats(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $query = SystemBackupLog::where('created_at', '>=', $startDate);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        return [
            'total' => $query->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'success_rate' => $this->calculateSuccessRate($query),
            'avg_duration' => (clone $query)->whereNotNull('duration_seconds')->avg('duration_seconds'),
        ];
    }

    /**
     * 성공률 계산
     */
    private function calculateSuccessRate($query)
    {
        $total = (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        $successful = (clone $query)->where('status', 'completed')->count();
        return round(($successful / $total) * 100, 2);
    }
}
