<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use Jiny\Admin\App\Http\Controllers\AdminResourceController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\App\Models\SystemMaintenanceLog;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * AdminSystemMaintenanceLogController
 *
 * 관리자 시스템 유지보수 로그 관리 컨트롤러
 * AdminResourceController를 상속하여 템플릿 메소드 패턴으로 구현
 * 
 * 시스템 유지보수 작업의 계획, 실행, 완료 과정을 추적하고 기록:
 * - 유지보수 일정 관리 및 작업 진행 상황 모니터링
 * - 다운타임 계획 및 영향도 분석
 * - 유지보수 작업 통계 및 성능 지표 분석
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSystemMaintenanceLog.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 관리자 시스템 유지보수 로그 관리 테스트 실행
 * php artisan test jiny/admin/tests/Feature/Admin/AdminSystemMaintenanceLogTest.php
 * ```
 */
class AdminSystemMaintenanceLogController extends AdminResourceController
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-admin::admin.system_maintenance_logs.index';
    public $createPath = 'jiny-admin::admin.system_maintenance_logs.create';
    public $editPath = 'jiny-admin::admin.system_maintenance_logs.edit';
    public $showPath = 'jiny-admin::admin.system_maintenance_logs.show';

    // 필터링 및 정렬 관련 설정
    protected $filterable = ['maintenance_type', 'status', 'priority', 'requires_downtime', 'search', 'start_date', 'end_date'];
    protected $validFilters = [
        'maintenance_type' => 'string|max:100',
        'status' => 'string|max:50',
        'priority' => 'string|max:50',
        'requires_downtime' => 'boolean',
        'search' => 'string',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    protected $sortableColumns = ['created_at', 'title', 'maintenance_type', 'status', 'priority', 'scheduled_start', 'actual_start', 'duration_minutes'];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'system_maintenance_logs';

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 테이블 이름 반환
     * System Maintenance Log 테이블 이름 반환
     */
    protected function getTableName()
    {
        return 'system_maintenance_logs';
    }

    /**
     * 모듈 이름 반환
     * System Maintenance Log 모듈 이름 반환
     */
    protected function getModuleName()
    {
        return 'admin.system-maintenance-logs';
    }

    /**
     * 유지보수 로그 목록 페이지 (템플릿 메소드 구현)
     */
    protected function _index(Request $request): View
    {
        $query = SystemMaintenanceLog::with(['initiatedBy', 'completedBy']);

        // 필터 적용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query, ['search']);

        // 정렬 적용
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $maintenanceLogs = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getMaintenanceStats();

        return view($this->indexPath, [
            'rows' => $maintenanceLogs,
            'maintenanceLogs' => $maintenanceLogs,
            'filters' => $filters,
            'sort' => $sortField,
            'dir' => $sortDirection,
            'route' => 'admin.system-maintenance-logs.',
            'stats' => $stats,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 유지보수 로그 생성 폼 (템플릿 메소드 구현)
     */
    protected function _create(Request $request): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view($this->createPath, [
            'route' => 'admin.system-maintenance-logs.',
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'admins' => $admins,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 유지보수 로그 저장 (템플릿 메소드 구현)
     */
    protected function _store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'maintenance_type' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getMaintenanceTypes())),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getStatuses())),
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'actual_start' => 'nullable|date',
            'actual_end' => 'nullable|date|after:actual_start',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'impact_assessment' => 'nullable|string',
            'initiated_by' => 'nullable|exists:admin_users,id',
            'completed_by' => 'nullable|exists:admin_users,id',
            'requires_downtime' => 'boolean',
            'priority' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getPriorities())),
            'affected_services' => 'nullable|json',
            'metadata' => 'nullable|json',
        ]);

        $log = SystemMaintenanceLog::create($request->all());

        // Activity Log 기록
        $this->logActivity('create', '유지보수 로그 생성', $log->id, $request->all());

        return response()->json([
            'success' => true,
            'message' => '유지보수 로그가 성공적으로 생성되었습니다.',
            'log' => $log
        ]);
    }

    /**
     * 유지보수 로그 상세 조회 (템플릿 메소드 구현)
     */
    protected function _show(Request $request, $id): View
    {
        $systemMaintenanceLog = SystemMaintenanceLog::with(['initiatedBy', 'completedBy'])->findOrFail($id);

        return view($this->showPath, [
            'route' => 'admin.system-maintenance-logs.',
            'maintenanceLog' => $systemMaintenanceLog,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 유지보수 로그 수정 폼 (템플릿 메소드 구현)
     */
    protected function _edit(Request $request, $id): View
    {
        $systemMaintenanceLog = SystemMaintenanceLog::findOrFail($id);
        $admins = AdminUser::where('is_active', true)->get();

        return view($this->editPath, [
            'route' => 'admin.system-maintenance-logs.',
            'maintenanceLog' => $systemMaintenanceLog,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'admins' => $admins,
            'errors' => new \Illuminate\Support\ViewErrorBag()
        ]);
    }

    /**
     * 유지보수 로그 업데이트 (템플릿 메소드 구현)
     */
    protected function _update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $systemMaintenanceLog = SystemMaintenanceLog::findOrFail($id);
        
        // 수정 전 데이터 가져오기 (Audit Log용)
        $oldData = $systemMaintenanceLog->toArray();

        $request->validate([
            'maintenance_type' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getMaintenanceTypes())),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getStatuses())),
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'actual_start' => 'nullable|date',
            'actual_end' => 'nullable|date|after:actual_start',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'impact_assessment' => 'nullable|string',
            'initiated_by' => 'nullable|exists:admin_users,id',
            'completed_by' => 'nullable|exists:admin_users,id',
            'requires_downtime' => 'boolean',
            'priority' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getPriorities())),
            'affected_services' => 'nullable|json',
            'metadata' => 'nullable|json',
        ]);

        $systemMaintenanceLog->update($request->all());

        // Activity Log 기록
        $this->logActivity('update', '유지보수 로그 수정', $systemMaintenanceLog->id, $request->all());

        // Audit Log 기록
        $this->logAudit('update', $oldData, $request->all(), '유지보수 로그 수정', $systemMaintenanceLog->id);

        return response()->json([
            'success' => true,
            'message' => '유지보수 로그가 성공적으로 수정되었습니다.',
            'log' => $systemMaintenanceLog
        ]);
    }

    /**
     * 유지보수 로그 삭제 (템플릿 메소드 구현)
     */
    protected function _destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = $request->get('id') ?? $request->route('id');
        $systemMaintenanceLog = SystemMaintenanceLog::findOrFail($id);
        
        // 삭제 전 데이터 가져오기 (Audit Log용)
        $oldData = $systemMaintenanceLog->toArray();
        
        $systemMaintenanceLog->delete();

        // Activity Log 기록
        $this->logActivity('delete', '유지보수 로그 삭제', $id, $oldData);

        // Audit Log 기록
        $this->logAudit('delete', $oldData, null, '유지보수 로그 삭제', $id);

        return response()->json([
            'success' => true,
            'message' => '유지보수 로그가 성공적으로 삭제되었습니다.'
        ]);
    }

    /**
     * 유지보수 로그 상태 변경
     */
    public function updateStatus(Request $request, SystemMaintenanceLog $systemMaintenanceLog): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getStatuses())),
        ]);

        $oldStatus = $systemMaintenanceLog->status;
        $systemMaintenanceLog->update(['status' => $request->status]);

        // Activity Log 기록
        $this->logActivity('status_update', '유지보수 로그 상태 변경', $systemMaintenanceLog->id, [
            'old_status' => $oldStatus,
            'new_status' => $request->status
        ]);

        $statusText = SystemMaintenanceLog::getStatuses()[$request->status];
        return response()->json([
            'success' => true,
            'message' => "유지보수 로그 상태가 '{$statusText}'로 변경되었습니다."
        ]);
    }

    /**
     * 유지보수 로그 통계
     */
    public function stats(): View
    {
        $stats = [
            'total' => SystemMaintenanceLog::count(),
            'scheduled' => SystemMaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => SystemMaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => SystemMaintenanceLog::where('status', 'completed')->count(),
            'failed' => SystemMaintenanceLog::where('status', 'failed')->count(),
            'avg_duration' => SystemMaintenanceLog::avg('duration_minutes'),
            'downtime_required' => SystemMaintenanceLog::where('requires_downtime', true)->count(),
            'recent_stats' => SystemMaintenanceLog::getRecentStats(30),
            'stats_by_type' => SystemMaintenanceLog::getStatsByType(),
            'stats_by_priority' => SystemMaintenanceLog::getStatsByPriority(),
        ];

        return view('jiny-admin::admin.system_maintenance_logs.stats', [
            'stats' => $stats,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
        ]);
    }

    /**
     * 유지보수 로그 일괄 삭제
     */
    public function bulkDelete(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_maintenance_logs,id',
        ]);

        $count = SystemMaintenanceLog::whereIn('id', $request->selected_logs)->delete();

        // Activity Log 기록
        $this->logActivity('bulk_delete', '유지보수 로그 일괄 삭제', null, [
            'deleted_count' => $count,
            'deleted_ids' => $request->selected_logs
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count}개의 유지보수 로그가 성공적으로 삭제되었습니다."
        ]);
    }

    /**
     * 유지보수 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemMaintenanceLog::with(['initiatedBy', 'completedBy']);

        // 필터 적용
        $query = $this->applyFilters($query, $request);

        $maintenanceLogs = $query->get();

        // CSV 파일 생성
        $filename = 'maintenance_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        if (!File::exists(dirname($filepath))) {
            File::makeDirectory(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        
        // 헤더 작성
        fputcsv($handle, [
            'ID', '유지보수 타입', '제목', '상태', '우선순위', '예정 시작', '예정 종료',
            '실제 시작', '실제 종료', '소요 시간(분)', '다운타임 필요', '시작한 관리자', '완료한 관리자', '생성일'
        ]);

        // 데이터 작성
        foreach ($maintenanceLogs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->maintenance_type,
                $log->title,
                $log->status,
                $log->priority,
                $log->scheduled_start?->format('Y-m-d H:i:s'),
                $log->scheduled_end?->format('Y-m-d H:i:s'),
                $log->actual_start?->format('Y-m-d H:i:s'),
                $log->actual_end?->format('Y-m-d H:i:s'),
                $log->duration_minutes,
                $log->requires_downtime ? '예' : '아니오',
                $log->initiatedBy?->name,
                $log->completedBy?->name,
                $log->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);

        // Activity Log 기록
        $this->logActivity('export', '유지보수 로그 내보내기', null, [
            'filename' => $filename,
            'exported_count' => $maintenanceLogs->count()
        ]);

        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }

    /**
     * 검색 필터 적용
     */
    private function applyFilters($query, Request $request)
    {
        // 유지보수 타입 필터
        if ($request->filled('filter_maintenance_type')) {
            $query->where('maintenance_type', $request->filter_maintenance_type);
        }

        // 상태 필터
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // 우선순위 필터
        if ($request->filled('filter_priority')) {
            $query->where('priority', $request->filter_priority);
        }

        // 다운타임 필요 여부 필터
        if ($request->filled('filter_requires_downtime')) {
            $query->where('requires_downtime', $request->filter_requires_downtime);
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
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
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
            'created_at', 'title', 'maintenance_type', 'status', 'priority',
            'scheduled_start', 'actual_start', 'duration_minutes'
        ];

        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * 유지보수 로그 통계 데이터 조회
     */
    private function getMaintenanceStats()
    {
        return [
            'total' => SystemMaintenanceLog::count(),
            'scheduled' => SystemMaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => SystemMaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => SystemMaintenanceLog::where('status', 'completed')->count(),
            'failed' => SystemMaintenanceLog::where('status', 'failed')->count(),
            'avg_duration' => SystemMaintenanceLog::whereNotNull('duration_minutes')->avg('duration_minutes'),
            'downtime_required' => SystemMaintenanceLog::where('requires_downtime', true)->count(),
            'recent_stats' => SystemMaintenanceLog::getRecentStats(30),
            'stats_by_type' => SystemMaintenanceLog::getStatsByType(),
            'stats_by_priority' => SystemMaintenanceLog::getStatsByPriority(),
        ];
    }
}
