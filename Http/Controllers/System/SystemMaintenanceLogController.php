<?php

namespace Jiny\Admin\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\SystemMaintenanceLog;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\DB;

class SystemMaintenanceLogController extends Controller
{
    /**
     * 유지보수 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = SystemMaintenanceLog::with(['initiatedBy', 'completedBy']);

        // 컬럼명 기준 자동 필터링
        $filterable = [
            'maintenance_type', 'status', 'priority', 'requires_downtime'
        ];
        foreach ($filterable as $column) {
            $value = $request->get('filter_' . $column);
            if (!is_null($value) && $value !== '') {
                $query->where($column, $value);
            }
        }

        // 검색어(부분일치) 별도 처리
        $search = $request->get('filter_search', $request->get('search'));
        if (!is_null($search) && $search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // 날짜 범위 필터링
        $startDate = $request->get('filter_start_date');
        $endDate = $request->get('filter_end_date');
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        // 정렬
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $maintenanceLogs = $query->paginate(15);

        // 통계 데이터
        $stats = [
            'total' => SystemMaintenanceLog::count(),
            'scheduled' => SystemMaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => SystemMaintenanceLog::where('status', 'in_progress')->count(),
            'completed' => SystemMaintenanceLog::where('status', 'completed')->count(),
            'failed' => SystemMaintenanceLog::where('status', 'failed')->count(),
        ];

        return view('jiny-admin::systems.maintenance-logs.index', [
            'maintenanceLogs' => $maintenanceLogs,
            'stats' => $stats,
            'maintenanceTypes' => SystemMaintenanceLog::getMaintenanceTypes(),
            'statuses' => SystemMaintenanceLog::getStatuses(),
            'priorities' => SystemMaintenanceLog::getPriorities(),
            'sort' => $sortField,
            'dir' => $sortDirection,
        ]);
    }

    /**
     * 유지보수 로그 생성 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::systems.maintenance-logs.create', [
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

        return view('jiny-admin::systems.maintenance-logs.show', [
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

        return view('jiny-admin::systems.maintenance-logs.edit', [
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
        ];

        return view('jiny-admin::systems.maintenance-logs.stats', [
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

        return redirect()->route('systems.maintenance-logs.index')
            ->with('success', "{$count}개의 유지보수 로그가 성공적으로 삭제되었습니다.");
    }

    /**
     * 유지보수 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemMaintenanceLog::with(['initiatedBy', 'completedBy']);

        // 필터 적용
        $maintenanceType = $request->get('maintenance_type');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($maintenanceType) {
            $query->where('maintenance_type', $maintenanceType);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($priority) {
            $query->where('priority', $priority);
        }
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $maintenanceLogs = $query->get();

        // CSV 파일 생성 로직 (실제 구현에서는 Excel/CSV 라이브러리 사용)
        // 여기서는 간단한 예시만 제공

        return redirect()->route('admin.system-maintenance-logs.index')
            ->with('success', '유지보수 로그가 성공적으로 내보내기되었습니다.');
    }
}
