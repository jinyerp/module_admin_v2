<?php

namespace Jiny\Admin\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Jiny\Admin\Models\SystemBackupLog;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\DB;

class SystemBackupLogController extends Controller
{
    /**
     * 백업 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = SystemBackupLog::with('initiatedBy');

        // 컬럼명 기준 자동 필터링
        $filterable = [
            'backup_type', 'backup_name', 'status', 'storage_location', 'is_encrypted', 'is_compressed'
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
                $q->where('backup_name', 'like', "%{$search}%")
                  ->orWhere('file_path', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%");
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

        $backupLogs = $query->paginate(15);

        // 통계 데이터
        $stats = [
            'total' => SystemBackupLog::count(),
            'completed' => SystemBackupLog::completed()->count(),
            'failed' => SystemBackupLog::failed()->count(),
            'success_rate' => SystemBackupLog::getSuccessRate(),
            'avg_duration' => SystemBackupLog::getAverageDuration(),
        ];

        return view('jiny-admin::admin.system-backup-logs.index', [
            'backupLogs' => $backupLogs,
            'stats' => $stats,
            'backupTypes' => SystemBackupLog::getBackupTypes(),
            'statuses' => SystemBackupLog::getStatuses(),
            'sort' => $sortField,
            'dir' => $sortDirection,
        ]);
    }

    /**
     * 백업 로그 생성 폼
     */
    public function create(): View
    {
        $admins = AdminUser::where('is_active', true)->get();

        return view('jiny-admin::admin.system-backup-logs.create', [
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

        return redirect()->route('admin.system-backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 생성되었습니다.');
    }

    /**
     * 백업 로그 상세 조회
     */
    public function show(SystemBackupLog $systemBackupLog): View
    {
        $systemBackupLog->load('initiatedBy');

        return view('jiny-admin::admin.system-backup-logs.show', [
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

        return view('jiny-admin::admin.system-backup-logs.edit', [
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
            'storage_location' => 'nullable|string|max:255',
            'is_encrypted' => 'boolean',
            'is_compressed' => 'boolean',
            'metadata' => 'nullable|json',
        ]);

        $systemBackupLog->update($request->all());

        return redirect()->route('admin.system-backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 수정되었습니다.');
    }

    /**
     * 백업 로그 삭제
     */
    public function destroy(SystemBackupLog $systemBackupLog): RedirectResponse
    {
        $systemBackupLog->delete();

        return redirect()->route('admin.system-backup-logs.index')
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
        return redirect()->route('admin.system-backup-logs.index')
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

        return view('jiny-admin::admin.system-backup-logs.stats', [
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

        $count = SystemBackupLog::whereIn('id', $request->selected_logs)->delete();

        return redirect()->route('admin.system-backup-logs.index')
            ->with('success', "{$count}개의 백업 로그가 성공적으로 삭제되었습니다.");
    }

    /**
     * 백업 로그 내보내기
     */
    public function export(Request $request): RedirectResponse
    {
        $query = SystemBackupLog::with('initiatedBy');

        // 필터 적용
        $backupType = $request->get('backup_type');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($backupType) {
            $query->where('backup_type', $backupType);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $backupLogs = $query->get();

        // CSV 파일 생성 로직 (실제 구현에서는 Excel/CSV 라이브러리 사용)
        // 여기서는 간단한 예시만 제공

        return redirect()->route('admin.system-backup-logs.index')
            ->with('success', '백업 로그가 성공적으로 내보내기되었습니다.');
    }
}
