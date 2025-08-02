<?php

namespace Jiny\Admin\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\App\Models\SystemMaintenanceLog;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AdminSystemMaintenanceLogController extends Controller
{
    /**
     * 유지보수 로그 목록 페이지
     */
    public function index(Request $request): View
    {
        $query = SystemMaintenanceLog::with(['performedBy']);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        // 정렬 적용
        $query = $this->applySorting($query, $request);

        $maintenanceLogs = $query->paginate(20);

        // 통계 데이터
        $stats = $this->getStats($request);

        return view('jiny-admin::admin.system_maintenance_logs.index', [
            'rows' => $maintenanceLogs,
            'maintenanceLogs' => $maintenanceLogs,
            'stats' => $stats,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
        ]);
    }

    /**
     * 유지보수 로그 생성 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::admin.system_maintenance_logs.create', [
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'admins' => $admins,
        ]);
    }

    /**
     * 유지보수 로그 저장
     */
    public function store(Request $request): RedirectResponse
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
            'initiated_by' => 'nullable|exists:admin_emails,id',
            'completed_by' => 'nullable|exists:admin_emails,id',
            'requires_downtime' => 'boolean',
            'priority' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getPriorities())),
            'affected_services' => 'nullable|json',
            'metadata' => 'nullable|json',
        ]);

        SystemMaintenanceLog::create($request->all());

        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', '유지보수 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 유지보수 로그 상세 조회
     */
    public function show(SystemMaintenanceLog $systemMaintenanceLog): View
    {
        $systemMaintenanceLog->load(['initiatedBy', 'completedBy']);

        return view('jiny-admin::admin.system_maintenance_logs.show', [
            'maintenanceLog' => $systemMaintenanceLog,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
        ]);
    }

    /**
     * 유지보수 로그 수정 폼
     */
    public function edit(SystemMaintenanceLog $systemMaintenanceLog): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::admin.system_maintenance_logs.edit', [
            'maintenanceLog' => $systemMaintenanceLog,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'admins' => $admins,
        ]);
    }

    /**
     * 유지보수 로그 업데이트
     */
    public function update(Request $request, SystemMaintenanceLog $systemMaintenanceLog): RedirectResponse
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
            'initiated_by' => 'nullable|exists:admin_emails,id',
            'completed_by' => 'nullable|exists:admin_emails,id',
            'requires_downtime' => 'boolean',
            'priority' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getPriorities())),
            'affected_services' => 'nullable|json',
            'metadata' => 'nullable|json',
        ]);

        $systemMaintenanceLog->update($request->all());

        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', '유지보수 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 유지보수 로그 삭제
     */
    public function destroy(SystemMaintenanceLog $systemMaintenanceLog): RedirectResponse
    {
        $systemMaintenanceLog->delete();

        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', '유지보수 로그가 성공적으로 삭제되었습니다.');
    }

    /**
     * 유지보수 로그 상태 변경
     */
    public function updateStatus(Request $request, SystemMaintenanceLog $systemMaintenanceLog): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SystemMaintenanceLog::getStatuses())),
        ]);

        $systemMaintenanceLog->update(['status' => $request->status]);

        $statusText = SystemMaintenanceLog::getStatuses()[$request->status];
        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', "유지보수 로그 상태가 '{$statusText}'로 변경되었습니다.");
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
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'integer|exists:system_maintenance_logs,id',
        ]);

        $count = SystemMaintenanceLog::whereIn('id', $request->selected_logs)->delete();

        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', "{$count}개의 유지보수 로그가 성공적으로 삭제되었습니다.");
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
     * 통계 데이터 조회
     */
    private function getStats(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $query = SystemMaintenanceLog::where('created_at', '>=', $startDate);

        // 검색 필터 적용
        $query = $this->applyFilters($query, $request);

        return [
            'total' => $query->count(),
            'scheduled' => (clone $query)->where('status', 'scheduled')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'avg_duration' => (clone $query)->whereNotNull('duration_minutes')->avg('duration_minutes'),
            'downtime_required' => (clone $query)->where('requires_downtime', true)->count(),
        ];
    }
}
