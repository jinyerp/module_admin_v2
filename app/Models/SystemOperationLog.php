<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

/**
 * 시스템 운영 로그 모델
 *
 * 시스템의 모든 운영 활동을 상세히 기록합니다.
 * - 사용자 및 관리자의 모든 시스템 활동 추적
 * - 운영 타입별 분류 및 성능 모니터링
 * - 보안 관련 정보 수집
 * - 에러 및 예외 상황 기록
 * - 실행 시간 측정으로 성능 분석
 */
class SystemOperationLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'system_operation_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'operation_type',
        'operation_name',
        'performed_by_type',
        'performed_by_id',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'session_id',
        'request_data',
        'response_data',
        'status',
        'error_message',
        'execution_time',
        'severity',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'execution_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 상태 상수
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PARTIAL = 'partial';

    /**
     * 중요도 상수
     */
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_ERROR = 'error';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * 운영 타입 상수
     */
    const OPERATION_TYPE_LOGIN = 'login';
    const OPERATION_TYPE_LOGOUT = 'logout';
    const OPERATION_TYPE_CREATE = 'create';
    const OPERATION_TYPE_UPDATE = 'update';
    const OPERATION_TYPE_DELETE = 'delete';
    const OPERATION_TYPE_READ = 'read';
    const OPERATION_TYPE_SEARCH = 'search';
    const OPERATION_TYPE_EXPORT = 'export';
    const OPERATION_TYPE_IMPORT = 'import';

    /**
     * 수행자와의 다형성 관계
     */
    public function performedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 대상과의 다형성 관계
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 성공 로그 생성 (정적 메서드)
     */
    public static function logSuccess(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        ?array $responseData = null
    ): self {
        return self::create([
            'operation_type' => $operationType,
            'operation_name' => $operationName,
            'performed_by_type' => $performedByType,
            'performed_by_id' => $performedById,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'request_data' => $requestData,
            'response_data' => $responseData,
            'status' => self::STATUS_SUCCESS,
            'execution_time' => $executionTime,
            'severity' => self::SEVERITY_INFO,
        ]);
    }

    /**
     * 실패 로그 생성 (정적 메서드)
     */
    public static function logFailed(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        string $errorMessage,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        string $severity = self::SEVERITY_ERROR
    ): self {
        return self::create([
            'operation_type' => $operationType,
            'operation_name' => $operationName,
            'performed_by_type' => $performedByType,
            'performed_by_id' => $performedById,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'request_data' => $requestData,
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime,
            'severity' => $severity,
        ]);
    }

    /**
     * 부분 성공 로그 생성 (정적 메서드)
     */
    public static function logPartial(
        string $operationType,
        string $operationName,
        string $performedByType,
        int $performedById,
        string $errorMessage,
        ?string $targetType = null,
        ?int $targetId = null,
        ?int $executionTime = null,
        ?array $requestData = null,
        ?array $responseData = null
    ): self {
        return self::create([
            'operation_type' => $operationType,
            'operation_name' => $operationName,
            'performed_by_type' => $performedByType,
            'performed_by_id' => $performedById,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'request_data' => $requestData,
            'response_data' => $responseData,
            'status' => self::STATUS_PARTIAL,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime,
            'severity' => self::SEVERITY_WARNING,
        ]);
    }

    /**
     * 성공한 운영만 조회하는 스코프
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * 실패한 운영만 조회하는 스코프
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * 부분 성공한 운영만 조회하는 스코프
     */
    public function scopePartial($query)
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    /**
     * 특정 운영 타입만 조회하는 스코프
     */
    public function scopeByOperationType($query, string $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    /**
     * 특정 수행자만 조회하는 스코프
     */
    public function scopeByPerformer($query, string $performedByType, int $performedById)
    {
        return $query->where('performed_by_type', $performedByType)
                    ->where('performed_by_id', $performedById);
    }

    /**
     * 특정 대상만 조회하는 스코프
     */
    public function scopeByTarget($query, string $targetType, ?int $targetId = null)
    {
        $query = $query->where('target_type', $targetType);
        
        if ($targetId !== null) {
            $query->where('target_id', $targetId);
        }
        
        return $query;
    }

    /**
     * 특정 중요도만 조회하는 스코프
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * 최근 운영만 조회하는 스코프
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 느린 운영만 조회하는 스코프
     */
    public function scopeSlow($query, int $thresholdMs = 1000)
    {
        return $query->where('execution_time', '>', $thresholdMs);
    }

    /**
     * 성공 여부 확인
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * 실패 여부 확인
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * 부분 성공 여부 확인
     */
    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    /**
     * 높은 중요도 여부 확인
     */
    public function isHighSeverity(): bool
    {
        return in_array($this->severity, [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL]);
    }

    /**
     * 느린 운영 여부 확인
     */
    public function isSlow(int $thresholdMs = 1000): bool
    {
        return $this->execution_time > $thresholdMs;
    }

    /**
     * 수행자 정보 조회
     */
    public function getPerformerInfo(): ?array
    {
        if (!$this->performedBy) {
            return null;
        }

        return [
            'id' => $this->performed_by_id,
            'type' => $this->performed_by_type,
            'name' => $this->performedBy->name ?? 'Unknown',
            'email' => $this->performedBy->email ?? null,
        ];
    }

    /**
     * 대상 정보 조회
     */
    public function getTargetInfo(): ?array
    {
        if (!$this->target) {
            return null;
        }

        return [
            'id' => $this->target_id,
            'type' => $this->target_type,
            'name' => $this->target->name ?? 'Unknown',
        ];
    }

    /**
     * 포맷된 실행 시간 반환
     */
    public function getFormattedExecutionTime(): string
    {
        if (!$this->execution_time) {
            return 'N/A';
        }

        if ($this->execution_time < 1000) {
            return $this->execution_time . 'ms';
        }

        return round($this->execution_time / 1000, 2) . 's';
    }

    /**
     * 운영 상태에 따른 CSS 클래스 반환
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_PARTIAL => 'warning',
            default => 'info',
        };
    }

    /**
     * 중요도에 따른 CSS 클래스 반환
     */
    public function getSeverityClass(): string
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => 'danger',
            self::SEVERITY_ERROR => 'danger',
            self::SEVERITY_WARNING => 'warning',
            default => 'info',
        };
    }

    /**
     * 운영 타입에 따른 아이콘 반환
     */
    public function getOperationTypeIcon(): string
    {
        return match($this->operation_type) {
            self::OPERATION_TYPE_LOGIN => 'sign-in-alt',
            self::OPERATION_TYPE_LOGOUT => 'sign-out-alt',
            self::OPERATION_TYPE_CREATE => 'plus',
            self::OPERATION_TYPE_UPDATE => 'edit',
            self::OPERATION_TYPE_DELETE => 'trash',
            self::OPERATION_TYPE_READ => 'eye',
            self::OPERATION_TYPE_SEARCH => 'search',
            self::OPERATION_TYPE_EXPORT => 'download',
            self::OPERATION_TYPE_IMPORT => 'upload',
            default => 'cog',
        };
    }

    /**
     * 운영 성공률 계산 (정적 메서드)
     */
    public static function getSuccessRate(): float
    {
        $total = self::count();
        if ($total === 0) {
            return 0;
        }

        $successful = self::where('status', self::STATUS_SUCCESS)->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * 평균 실행 시간 계산 (정적 메서드)
     */
    public static function getAverageExecutionTime(): float
    {
        $avg = self::whereNotNull('execution_time')->avg('execution_time');
        return $avg ? round($avg, 2) : 0;
    }

    /**
     * 최근 운영 통계 조회 (정적 메서드)
     */
    public static function getRecentStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => self::where('created_at', '>=', $startDate)->count(),
            'successful' => self::where('status', self::STATUS_SUCCESS)
                ->where('created_at', '>=', $startDate)->count(),
            'failed' => self::where('status', self::STATUS_FAILED)
                ->where('created_at', '>=', $startDate)->count(),
            'partial' => self::where('status', self::STATUS_PARTIAL)
                ->where('created_at', '>=', $startDate)->count(),
            'avg_execution_time' => self::where('created_at', '>=', $startDate)
                ->whereNotNull('execution_time')->avg('execution_time'),
        ];
    }

    /**
     * 운영 타입별 통계 조회 (정적 메서드)
     */
    public static function getStatsByType(): array
    {
        return self::selectRaw('operation_type, COUNT(*) as count, AVG(execution_time) as avg_execution_time')
            ->groupBy('operation_type')
            ->get()
            ->keyBy('operation_type')
            ->toArray();
    }

    /**
     * 중요도별 통계 조회 (정적 메서드)
     */
    public static function getStatsBySeverity(): array
    {
        return self::selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->get()
            ->keyBy('severity')
            ->toArray();
    }

    /**
     * 성능 분석 (정적 메서드)
     */
    public static function getPerformanceAnalysis(): array
    {
        $logs = self::whereNotNull('execution_time')->get();

        return [
            'total_operations' => $logs->count(),
            'avg_execution_time' => $logs->avg('execution_time'),
            'min_execution_time' => $logs->min('execution_time'),
            'max_execution_time' => $logs->max('execution_time'),
            'slow_operations' => $logs->where('execution_time', '>', 1000)->count(),
            'fast_operations' => $logs->where('execution_time', '<', 100)->count(),
        ];
    }

    /**
     * 에러 분석 (정적 메서드)
     */
    public static function getErrorAnalysis(): array
    {
        $failed = self::whereIn('status', [self::STATUS_FAILED, self::STATUS_PARTIAL])
            ->whereNotNull('error_message')
            ->get();

        $errorPatterns = [];
        foreach ($failed as $log) {
            $error = strtolower($log->error_message);
            if (str_contains($error, 'database')) {
                $errorPatterns['database'] = ($errorPatterns['database'] ?? 0) + 1;
            } elseif (str_contains($error, 'permission')) {
                $errorPatterns['permission'] = ($errorPatterns['permission'] ?? 0) + 1;
            } elseif (str_contains($error, 'validation')) {
                $errorPatterns['validation'] = ($errorPatterns['validation'] ?? 0) + 1;
            } else {
                $errorPatterns['other'] = ($errorPatterns['other'] ?? 0) + 1;
            }
        }

        return $errorPatterns;
    }
}
