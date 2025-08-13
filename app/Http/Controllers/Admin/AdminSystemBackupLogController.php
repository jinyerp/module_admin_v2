<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Jiny\Admin\App\Models\SystemBackupLog;
use Jiny\Admin\App\Models\AdminUser;
use Carbon\Carbon;
use ZipArchive;

/**
 * AdminSystemBackupLogController
 *
 * 시스템 백업 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * 시스템 백업 작업의 전체 생명주기를 관리:
 * - 다양한 백업 타입 지원 (데이터베이스, 파일, 코드, 전체)
 * - 백업 상태 추적 및 모니터링
 * - 백업 파일 보안 (암호화, 압축, 체크섬)
 * - 백업 정책 준수 및 성능 분석
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSystemBackupLog.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 시스템 백업 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminSystemBackupLogTest.php
 * ```
 */
class AdminSystemBackupLogController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.system_backup_logs.index';
    public $createPath = 'jiny-admin::admin.system_backup_logs.create';
    public $editPath = 'jiny-admin::admin.system_backup_logs.edit';
    public $showPath = 'jiny-admin::admin.system_backup_logs.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['backup_type', 'status', 'initiated_by', 'search', 'date_from', 'date_to', 'is_encrypted', 'is_compressed'];
    protected $validFilters = [
        'backup_type' => 'string|in:database,files,code,full',
        'status' => 'string|in:running,completed,failed,cancelled',
        'initiated_by' => 'integer|exists:admin_users,id',
        'search' => 'string',
        'date_from' => 'date',
        'date_to' => 'date',
        'is_encrypted' => 'boolean',
        'is_compressed' => 'boolean'
    ];
    protected $sortableColumns = ['id', 'backup_type', 'status', 'started_at', 'completed_at', 'duration_seconds', 'created_at'];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'system_backup_logs';

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     * Activity Log 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'system_backup_logs';
    }

    /**
     * 모듈 이름 반환
     * Activity Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.system_backup_logs';
    }

    /**
     * 시스템 백업 로그 목록 조회 (템플릿 메소드 구현)
     * 백업 타입별, 상태별 필터링 및 정렬 지원
     */
    protected function _index(Request $request): View
    {
        $query = SystemBackupLog::with('initiatedBy');
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['search']);
        
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $rows = $query->paginate(20);

        // 통계 데이터 추가
        $stats = $this->getBackupStats();

        return view($this->indexPath, [
            'rows' => $rows,
            'backupLogs' => $rows,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.systems.backup-logs.',
            'stats' => $stats,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 백업 로그 생성 폼 (템플릿 메소드 구현)
     */
    protected function _create(Request $request): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view($this->createPath, [
            'route' => 'admin.systems.backup-logs.',
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'admins' => $admins,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 백업 로그 저장 (템플릿 메소드 구현)
     */
    protected function _store(Request $request): JsonResponse
    {
        $validationRules = [
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
            'initiated_by' => 'nullable|exists:admin_users,id',
            'storage_location' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_compressed' => 'boolean',
            'metadata' => 'nullable|json',
        ];
        
        $data = $request->validate($validationRules);
        
        $backupLog = SystemBackupLog::create($data);
        
        // Activity Log 기록
        $this->logActivity('create', '백업 로그 생성', $backupLog->id, $data);
        
        return response()->json([
            'success' => true,
            'message' => '백업 로그가 성공적으로 생성되었습니다.',
            'backupLog' => $backupLog
        ]);
    }

    /**
     * 백업 로그 상세 조회 (템플릿 메소드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $backupLog = SystemBackupLog::with('initiatedBy')->findOrFail($id);
        
        return view($this->showPath, [
            'route' => 'admin.systems.backup-logs.',
            'backupLog' => $backupLog,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 백업 로그 수정 폼 (템플릿 메소드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $backupLog = SystemBackupLog::with('initiatedBy')->findOrFail($id);
        $admins = AdminUser::where('is_active', true)->get();
        
        return view($this->editPath, [
            'route' => 'admin.systems.backup-logs.',
            'backupLog' => $backupLog,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'admins' => $admins,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 백업 로그 수정 (템플릿 메소드 구현)
     */
    protected function _update(Request $request, $id): JsonResponse
    {
        $backupLog = SystemBackupLog::findOrFail($id);
        
        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = $backupLog->toArray();
        
        $validationRules = [
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
            'initiated_by' => 'nullable|exists:admin_users,id',
            'storage_location' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_compressed' => 'boolean',
            'metadata' => 'nullable|json',
        ];
        
        $data = $request->validate($validationRules);
        
        $backupLog->update($data);
        
        // Activity Log 기록
        $this->logActivity('update', '백업 로그 수정', $backupLog->id, $data);
        
        // Audit Log 기록
        $this->logAudit('update', $oldData, $data, '시스템 백업 로그 수정', $backupLog->id);
        
        return response()->json([
            'success' => true,
            'message' => '백업 로그가 성공적으로 수정되었습니다.',
            'backupLog' => $backupLog
        ]);
    }

    /**
     * 백업 로그 삭제 (템플릿 메소드 구현)
     */
    protected function _destroy(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? $request->route('id');
        $backupLog = SystemBackupLog::findOrFail($id);
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = $backupLog->toArray();
        
        // 백업 파일도 함께 삭제
        if ($backupLog->file_path && File::exists($backupLog->file_path)) {
            File::delete($backupLog->file_path);
        }
        
        $backupLog->delete();
        
        // Activity Log 기록
        $this->logActivity('delete', '백업 로그 삭제', $id, $oldData);
        
        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '시스템 백업 로그 삭제', $id);
        
        return response()->json([
            'success' => true,
            'message' => '백업 로그가 성공적으로 삭제되었습니다.'
        ]);
    }

    /**
     * 백업 실행 페이지
     * 다양한 백업 타입을 선택하고 실행할 수 있는 폼 제공
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
     * 백그라운드에서 백업 작업을 실행하고 로그를 생성
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

            // Activity Log 기록
            $this->logActivity('execute', '백업 실행 시작', $backupLog->id, $request->all());

            return redirect()->route('admin.systems.backup-logs.index')
                ->with('success', '백업이 시작되었습니다. 완료 후 다운로드가 가능합니다.');

        } catch (\Exception $e) {
            Log::error('Backup execution failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return redirect()->back()
                ->with('error', '백업 시작 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 백업 다운로드
     * 완료된 백업 파일을 다운로드
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
        
        // Activity Log 기록
        $this->logActivity('download', '백업 파일 다운로드', $systemBackupLog->id, ['file_path' => $systemBackupLog->file_path]);
        
        return response()->download($systemBackupLog->file_path, $fileName, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * 백업 파일 삭제
     * 백업 파일만 삭제하고 로그는 유지
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

        // Activity Log 기록
        $this->logActivity('delete_file', '백업 파일 삭제', $systemBackupLog->id, ['file_path' => $systemBackupLog->file_path]);

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', '백업 파일이 삭제되었습니다.');
    }

    /**
     * 백업 로그 상태 변경
     * 백업 로그의 상태를 수동으로 변경
     */
    public function updateStatus(Request $request, SystemBackupLog $systemBackupLog): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SystemBackupLog::getStatuses())),
        ]);

        $oldStatus = $systemBackupLog->status;
        $systemBackupLog->update(['status' => $request->status]);

        $statusText = SystemBackupLog::getStatuses()[$request->status];
        
        // Activity Log 기록
        $this->logActivity('update_status', '백업 로그 상태 변경', $systemBackupLog->id, [
            'old_status' => $oldStatus,
            'new_status' => $request->status
        ]);

        return redirect()->route('admin.systems.backup-logs.index')
            ->with('success', "백업 로그 상태가 '{$statusText}'로 변경되었습니다.");
    }

    /**
     * 백업 로그 통계
     * 백업 성공률, 평균 소요시간 등 상세 통계 제공
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
     * 선택된 백업 로그들을 일괄 삭제
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_backup_logs,id',
        ]);

        $backupLogs = SystemBackupLog::whereIn('id', $request->selected_logs)->get();
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = $backupLogs->toArray();
        
        foreach ($backupLogs as $backupLog) {
            if ($backupLog->file_path && File::exists($backupLog->file_path)) {
                File::delete($backupLog->file_path);
            }
            $backupLog->delete();
        }

        // Activity Log 기록
        $this->logActivity('bulk_delete', '백업 로그 일괄 삭제', null, ['deleted_ids' => $request->selected_logs]);
        
        // Audit Log 기록
        $this->logAudit('bulk_delete', $oldData, null, '시스템 백업 로그 일괄 삭제', null);

        return response()->json([
            'success' => true,
            'message' => count($backupLogs) . "개의 백업 로그가 성공적으로 삭제되었습니다."
        ]);
    }

    /**
     * 백업 로그 내보내기
     * 백업 로그를 CSV 형태로 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = SystemBackupLog::with('initiatedBy');

            // 필터 적용
            $filters = $this->getFilterParameters($request);
            $query = $this->applyFilter($filters, $query, ['search']);

            $backupLogs = $query->get();

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

            // Activity Log 기록
            $this->logActivity('export', '백업 로그 내보내기', null, ['filename' => $filename]);

            return response()->json([
                'success' => true,
                'message' => '백업 로그가 성공적으로 내보내졌습니다.',
                'filename' => $filename,
                'download_url' => route('admin.systems.backup-logs.download-export', ['filename' => $filename])
            ]);

        } catch (\Exception $e) {
            Log::error('Backup Log Export Failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '백업 로그 내보내기 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 삭제 확인 폼 반환
     */
    public function deleteConfirm(Request $request, $id)
    {
        $backupLog = SystemBackupLog::with('initiatedBy')->findOrFail($id);
        $url = route('admin.systems.backup-logs.destroy', $id);
        $title = '백업 로그 삭제';
        
        // AJAX 요청인 경우 HTML만 반환
        if ($request->ajax()) {
            return view('jiny-admin::admin.system_backup_logs.form_delete', compact('backupLog', 'url', 'title'));
        }
        
        // 일반 요청인 경우 전체 페이지 반환
        return view('jiny-admin::admin.system_backup_logs.form_delete', compact('backupLog', 'url', 'title'));
    }

    /**
     * 백업 통계 데이터 조회
     */
    private function getBackupStats()
    {
        $days = request()->get('days', 30);
        $startDate = now()->subDays($days);

        $query = SystemBackupLog::where('created_at', '>=', $startDate);

        return [
            'total' => $query->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'running' => (clone $query)->where('status', 'running')->count(),
            'success_rate' => $this->calculateSuccessRate($query),
            'avg_duration' => (clone $query)->whereNotNull('duration_seconds')->avg('duration_seconds'),
            'by_type' => (clone $query)->selectRaw('backup_type, COUNT(*) as count')
                ->groupBy('backup_type')
                ->get(),
            'recent_activity' => (clone $query)->with('initiatedBy')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
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
     * 백그라운드에서 백업 작업을 실행
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

            // Activity Log 기록
            $this->logActivity('backup_completed', '백업 완료', $backupLog->id, [
                'file_path' => $filePath,
                'file_size' => $this->formatFileSize($fileSize),
                'duration' => now()->diffInSeconds($backupLog->started_at)
            ]);

        } catch (\Exception $e) {
            $backupLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_seconds' => now()->diffInSeconds($backupLog->started_at),
                'error_message' => $e->getMessage(),
            ]);

            // Activity Log 기록
            $this->logActivity('backup_failed', '백업 실패', $backupLog->id, [
                'error_message' => $e->getMessage()
            ]);

            Log::error('Backup failed', [
                'backup_log_id' => $backupLog->id,
                'error' => $e->getMessage(),
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
}
