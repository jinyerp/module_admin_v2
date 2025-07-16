<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * 성공한 운영 로그 생성
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
            'status' => self::STATUS_SUCCESS,
            'execution_time' => $executionTime,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'severity' => self::SEVERITY_INFO,
        ]);
    }

    /**
     * 실패한 운영 로그 생성
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
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime,
            'request_data' => $requestData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'severity' => $severity,
        ]);
    }

    /**
     * 부분 성공한 운영 로그 생성
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
            'status' => self::STATUS_PARTIAL,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->session()->getId(),
            'severity' => self::SEVERITY_WARNING,
        ]);
    }

    /**
     * 성공한 운영만 조회
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * 실패한 운영만 조회
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * 부분 성공한 운영만 조회
     */
    public function scopePartial($query)
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    /**
     * 특정 운영 타입으로 조회
     */
    public function scopeByOperationType($query, string $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    /**
     * 특정 수행자로 조회
     */
    public function scopeByPerformer($query, string $performedByType, int $performedById)
    {
        return $query->where('performed_by_type', $performedByType)
                    ->where('performed_by_id', $performedById);
    }

    /**
     * 특정 대상으로 조회
     */
    public function scopeByTarget($query, string $targetType, ?int $targetId = null)
    {
        $query->where('target_type', $targetType);

        if ($targetId) {
            $query->where('target_id', $targetId);
        }

        return $query;
    }

    /**
     * 특정 중요도로 조회
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * 최근 운영 조회
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 느린 운영 조회 (성능 분석용)
     */
    public function scopeSlow($query, int $thresholdMs = 1000)
    {
        return $query->where('execution_time', '>', $thresholdMs);
    }

    /**
     * 성공한 운영인지 확인
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * 실패한 운영인지 확인
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * 부분 성공한 운영인지 확인
     */
    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    /**
     * 중요도가 높은 운영인지 확인
     */
    public function isHighSeverity(): bool
    {
        return in_array($this->severity, [self::SEVERITY_ERROR, self::SEVERITY_CRITICAL]);
    }

    /**
     * 느린 운영인지 확인
     */
    public function isSlow(int $thresholdMs = 1000): bool
    {
        return $this->execution_time && $this->execution_time > $thresholdMs;
    }

    /**
     * 수행자 정보 가져오기
     */
    public function getPerformerInfo(): ?array
    {
        if (!$this->performedBy) {
            return null;
        }

        return [
            'id' => $this->performed_by_id,
            'type' => $this->performed_by_type,
            'name' => $this->performedBy->name ?? $this->performedBy->email ?? 'Unknown',
            'email' => $this->performedBy->email ?? null,
        ];
    }

    /**
     * 대상 정보 가져오기
     */
    public function getTargetInfo(): ?array
    {
        if (!$this->target) {
            return null;
        }

        return [
            'id' => $this->target_id,
            'type' => $this->target_type,
            'name' => $this->target->name ?? $this->target->title ?? 'Unknown',
        ];
    }

    /**
     * 실행 시간을 사람이 읽기 쉬운 형태로 변환
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
}
