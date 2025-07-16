<?php

namespace Jiny\Admin\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Models\AdminUser;

class AdminAuditLogController extends Controller
{
    private $filterable = [];
    private $validFilters = [];

    /**
     * 로깅 활성화
     */
    protected $activeLog = true;

    /**
     * 로그 테이블명
     */
    protected $logTableName = 'admin_audit_logs';

    public function __construct()
    {
        $this->filterable = [
            'admin_id', // admin_id: 관리자 ID (숫자)
            'action', // action: 수행된 액션 (문자열)
            'table_name', // table_name: 대상 테이블명 (문자열)
            'record_id', // record_id: 대상 레코드 ID (숫자)
            'ip_address', // ip_address: IP 주소 (문자열)
            'severity', // severity: 심각도 (enum)
            'affected_count', // affected_count: 영향받은 레코드 수 (숫자)
        ];

        $this->validFilters = [
            'admin_id' => 'integer|exists:admin_users,id',
            'action' => 'string|max:50',
            'table_name' => 'string|max:100',
            'record_id' => 'integer|min:1',
            'ip_address' => 'string|max:45',
            'severity' => 'in:low,medium,high,critical',
            'affected_count' => 'integer|min:0',
        ];
    }

    /**
     * 관리자 감사 로그 목록 조회
     */
    public function index(Request $request): View
    {
        $query = AdminAuditLog::with('admin');

        // 필터 파라미터 추출, 조건 적용용
        $filters = $this->getFilterParameters($request);
        $query = $this->applyFilter($filters, $query);

        // 추가 필터 적용
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        // 정렬
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('direction', 'desc');
        $query->orderBy($sort, $dir);

        $logs = $query->paginate(20);

        // 통계 데이터
        $stats = [
            'total' => AdminAuditLog::count(),
            'today' => AdminAuditLog::createdToday()->count(),
            'this_week' => AdminAuditLog::createdThisWeek()->count(),
            'high_severity' => AdminAuditLog::highSeverity()->count(),
        ];

        // 액션별 통계
        $actionStats = AdminAuditLog::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        // 테이블별 통계
        $tableStats = AdminAuditLog::selectRaw('table_name, COUNT(*) as count')
            ->whereNotNull('table_name')
            ->groupBy('table_name')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-admin::logs.audit-logs.index', compact(
            'logs',
            'stats',
            'actionStats',
            'tableStats',
            'filters',
            'sort',
            'dir'
        ));
    }

    /**
     * 필터링 적용
     * @param Request $request
     * @param Builder $query
     * @return Builder
     */
    public function applyFilter($filters, $query)
    {
        // 기본 필터 적용
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        // 검색어(부분일치) 별도 처리
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('ip_address', 'like', "%{$filters['search']}%")
                  ->orWhere('table_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query;
    }

    protected function getFilterParameters(Request $request)
    {
        $filters = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') && !empty($value)) {
                $filters[substr($key, 7)] = $value;
            }
        }

        return $filters;
    }

    /**
     * 관리자 감사 로그 생성 폼
     */
    public function create(): View
    {
        return view('jiny-admin::logs.audit-logs.create');
    }

    /**
     * 관리자 감사 로그 저장
     */
    public function store(Request $request): RedirectResponse
    {
        // 유효성 검사 규칙 (생성용)
        $validationRules = [
            'admin_id' => 'required|integer|exists:admin_users,id',
            'action' => 'required|string|max:50',
            'table_name' => 'nullable|string|max:100',
            'record_id' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'severity' => 'required|in:low,medium,high,critical',
            'affected_count' => 'nullable|integer|min:0',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Audit Log Store Validation Failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        $data = $request->all();

        $auditLog = AdminAuditLog::create($data);

        // 관리자 액션 로깅
        $this->logCreateAction($auditLog, $data, "새로운 감사 로그 생성: {$auditLog->action}");

        return redirect()->route('admin.admin.logs.audit.index')
            ->with('success', '성공적으로 생성되었습니다.');
    }

    /**
     * 관리자 감사 로그 상세 조회
     */
    public function show(AdminAuditLog $auditLog): View
    {
        return view('jiny-admin::logs.audit-logs.show', compact('auditLog'));
    }

    /**
     * 관리자 감사 로그 수정 폼
     */
    public function edit(AdminAuditLog $auditLog): View
    {
        return view('jiny-admin::logs.audit-logs.edit', compact('auditLog'));
    }

    /**
     * 관리자 감사 로그 업데이트
     */
    public function update(Request $request, AdminAuditLog $auditLog): RedirectResponse
    {
        // 유효성 검사 규칙 (수정용)
        $validationRules = [
            'admin_id' => 'required|integer|exists:admin_users,id',
            'action' => 'required|string|max:50',
            'table_name' => 'nullable|string|max:100',
            'record_id' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'severity' => 'required|in:low,medium,high,critical',
            'affected_count' => 'nullable|integer|min:0',
        ];

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Audit Log Update Validation Failed', [
                'audit_log_id' => $auditLog->id,
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        $data = $request->all();

        // 업데이트 전 원본 데이터 저장
        $oldValues = $auditLog->toArray();

        $auditLog->update($data);

        // 관리자 액션 로깅
        $this->logUpdateAction($auditLog, $oldValues, $data, "감사 로그 수정: {$auditLog->action}");

        return redirect()->route('admin.admin.logs.audit.index')
            ->with('success', '성공적으로 수정되었습니다.');
    }

    /**
     * 관리자 감사 로그 삭제
     */
    public function destroy(AdminAuditLog $auditLog)
    {
        try {
            // 삭제 전 원본 데이터 저장
            $oldValues = $auditLog->toArray();

            $auditLog->delete();

            // 관리자 액션 로깅
            $this->logDeleteAction($auditLog, $oldValues, "감사 로그 삭제: {$auditLog->action}");

            return response()->json([
                'success' => true,
                'message' => '성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 관리자 감사 로그 통계
     */
    public function stats(): View
    {
        $globalStats = AdminAuditLog::getGlobalAdminStats(30);
        $recentStats = AdminAuditLog::getGlobalAdminStats(7);

        // 일별 통계
        $dailyStats = AdminAuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 시간별 통계
        $hourlyStats = AdminAuditLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('jiny-admin::logs.audit-logs.stats', compact(
            'globalStats',
            'recentStats',
            'dailyStats',
            'hourlyStats'
        ));
    }

    /**
     * 관리자별 활동 통계
     */
    public function adminStats(int $adminId): View
    {
        $admin = AdminUser::findOrFail($adminId);
        $stats = AdminAuditLog::getAdminActivityStats($adminId, 30);

        // 최근 활동
        $recentActivities = AdminAuditLog::where('admin_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 액션별 통계
        $actionStats = AdminAuditLog::where('admin_id', $adminId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-admin::logs.audit-logs.admin-stats', compact(
            'admin',
            'stats',
            'recentActivities',
            'actionStats'
        ));
    }

    /**
     * 관리자 감사 로그 내보내기
     */
    public function export(Request $request): JsonResponse
    {
        $query = AdminAuditLog::with('admin');

        // 필터 적용
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', $request->table_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // CSV 형식으로 변환
        $csvData = [];
        $csvData[] = [
            'ID', '관리자', '액션', '테이블명', '레코드ID', '설명', 'IP주소', '심각도', '생성일시'
        ];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->admin->email ?? 'N/A',
                $log->action,
                $log->table_name ?? 'N/A',
                $log->record_id ?? 'N/A',
                $log->description ?? 'N/A',
                $log->ip_address ?? 'N/A',
                $log->severity,
                $log->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'filename' => 'admin_audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * 선택 삭제 (bulk delete)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        // 입력된 값이 배열인지 확인합니다.
        if (!is_array($request->input('ids'))) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 입력입니다.'
            ], 422);
        }

        // ids 배열을 정수로 변환
        $ids = array_map('intval', $request->input('ids'));

        // 삭제할 로그 정보 조회 (로깅용)
        $logsToDelete = AdminAuditLog::whereIn('id', $ids)->get();

        // 데이터를 삭제합니다.
        $deletedCount = AdminAuditLog::whereIn('id', $ids)->delete();

        // 관리자 액션 로깅
        $this->logBulkDeleteAction($ids, $deletedCount, "대량 감사 로그 삭제: {$deletedCount}개");

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount}개의 감사 로그가 성공적으로 삭제되었습니다."
        ]);
    }

    /**
     * 관리자 감사 로그 삭제 (오래된 로그 정리)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:30|max:365',
        ]);

        $days = $request->days;
        $deletedCount = AdminAuditLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$days}일 이전의 {$deletedCount}개 로그가 삭제되었습니다."
        ]);
    }

    /**
     * 관리자 ID 가져오기
     */
    protected function getAdminId(): ?int
    {
        $adminId = null;
        if (session('admin_id')) {
            $adminId = session('admin_id');
        } elseif (auth()->id()) {
            $adminId = auth()->id();
        } elseif (auth()->guard('admin')->id()) {
            $adminId = auth()->guard('admin')->id();
        } elseif (session('admin_user_id')) {
            $adminId = session('admin_user_id');
        } elseif (session('user_id')) {
            $adminId = session('user_id');
        } else {
            $adminId = 1;
        }
        return $adminId;
    }

    protected function getTableName(): string
    {
        if (isset($this->logTableName) && $this->logTableName) {
            return $this->logTableName;
        }
        $controllerName = class_basename($this);
        $tableName = str_replace('Controller', '', $controllerName);
        $tableName = str_replace('Admin', '', $tableName);
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $tableName));
        return $tableName . 's';
    }

    protected function logAdminAction(
        string $action,
        ?\Illuminate\Database\Eloquent\Model $model = null,
        array $oldValues = null,
        array $newValues = null,
        string $description = null,
        array $metadata = null,
        int $affectedCount = null
    ): void {
        if (!isset($this->activeLog) || !$this->activeLog) {
            return;
        }
        $adminId = $this->getAdminId();
        if (!$adminId) {
            return;
        }
        try {
            $tableName = $this->getTableName();
            $recordId = $model ? $model->id : null;
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
            \Jiny\Admin\Models\AdminAuditLog::createAuditLog(
                $adminId,
                $action,
                $tableName,
                $recordId,
                $oldValues,
                $newValues,
                $ipAddress,
                $userAgent,
                $description,
                $this->getSeverityForAction($action),
                $metadata,
                $affectedCount
            );
        } catch (\Exception $e) {
            \Log::error('Admin audit log creation failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'table_name' => $this->getTableName(),
                'admin_id' => $adminId,
            ]);
        }
    }

    protected function logCreateAction($model, array $newValues, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_CREATE,
            $model,
            null,
            $newValues,
            $description ?? "새로운 {$this->getTableName()} 레코드 생성"
        );
    }

    protected function logUpdateAction($model, array $oldValues, array $newValues, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_UPDATE,
            $model,
            $oldValues,
            $newValues,
            $description ?? "{$this->getTableName()} 레코드 업데이트"
        );
    }

    protected function logDeleteAction($model, array $oldValues, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_DELETE,
            $model,
            $oldValues,
            null,
            $description ?? "{$this->getTableName()} 레코드 삭제"
        );
    }

    protected function logBulkDeleteAction(array $recordIds, int $affectedCount, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_BULK_DELETE,
            null,
            null,
            null,
            $description ?? "{$this->getTableName()} 대량 삭제 ({$affectedCount}개)",
            ['record_ids' => $recordIds],
            $affectedCount
        );
    }

    protected function logActivateAction($model, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_ACTIVATE,
            $model,
            null,
            null,
            $description ?? "{$this->getTableName()} 레코드 활성화"
        );
    }

    protected function logDeactivateAction($model, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_DEACTIVATE,
            $model,
            null,
            null,
            $description ?? "{$this->getTableName()} 레코드 비활성화"
        );
    }

    protected function logApproveAction($model, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_APPROVE,
            $model,
            null,
            null,
            $description ?? "{$this->getTableName()} 레코드 승인"
        );
    }

    protected function logRejectAction($model, string $description = null): void
    {
        $this->logAdminAction(
            \Jiny\Admin\Models\AdminAuditLog::ACTION_REJECT,
            $model,
            null,
            null,
            $description ?? "{$this->getTableName()} 레코드 거부"
        );
    }

    protected function getSeverityForAction(string $action): string
    {
        $highSeverityActions = [
            \Jiny\Admin\Models\AdminAuditLog::ACTION_DELETE,
            \Jiny\Admin\Models\AdminAuditLog::ACTION_BULK_DELETE,
            \Jiny\Admin\Models\AdminAuditLog::ACTION_REJECT,
        ];
        $criticalSeverityActions = [
            // 특별히 중요한 액션들
        ];
        if (in_array($action, $criticalSeverityActions)) {
            return \Jiny\Admin\Models\AdminAuditLog::SEVERITY_CRITICAL;
        }
        if (in_array($action, $highSeverityActions)) {
            return \Jiny\Admin\Models\AdminAuditLog::SEVERITY_HIGH;
        }
        return \Jiny\Admin\Models\AdminAuditLog::SEVERITY_MEDIUM;
    }

    protected function getChangedFields($model): array
    {
        $changes = $model->getChanges();
        $original = $model->getOriginal();
        $changedFields = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changedFields[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        return $changedFields;
    }

    protected function modelToArray($model): array
    {
        return $model->toArray();
    }

    protected function extractFieldsFromRequest($request, array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }
        return $data;
    }

    protected function processCheckboxFields(array $data, array $checkboxFields): array
    {
        foreach ($checkboxFields as $field) {
            $data[$field] = request()->has($field);
        }
        return $data;
    }

    protected function createLogMetadata($request, array $additionalData = []): array
    {
        return array_merge([
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'session_id' => session()->getId(),
        ], $additionalData);
    }
}
