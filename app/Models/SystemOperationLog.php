<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

/**
 * SystemOperationLog 모델
 * 
 * 시스템의 모든 운영 활동을 추적하고 기록하는 모델입니다.
 * 
 * @property int $id
 * @property string $operation_type 운영 타입
 * @property string $operation_name 운영명
 * @property string $performed_by_type 수행자 타입
 * @property int $performed_by_id 수행자 ID
 * @property string|null $target_type 대상 타입
 * @property int|null $target_id 대상 ID
 * @property string $status 상태 (success, failed, partial)
 * @property int|null $execution_time 실행 시간 (밀리초)
 * @property string $severity 중요도 (low, medium, high, critical)
 * @property string|null $ip_address IP 주소
 * @property string|null $session_id 세션 ID
 * @property string|null $error_message 에러 메시지
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SystemOperationLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'system_operation_logs';

    /**
     * 대량 할당 가능한 속성
     */
    protected $fillable = [
        'operation_type',
        'operation_name',
        'performed_by_type',
        'performed_by_id',
        'target_type',
        'target_id',
        'status',
        'execution_time',
        'severity',
        'ip_address',
        'session_id',
        'error_message',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'execution_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 숨겨진 속성
     */
    protected $hidden = [
    ];

    /**
     * 추가된 속성
     */
    protected $appends = [
        'duration_formatted',
        'status_label',
        'severity_label',
    ];

    /**
     * 수행자 관계 (MorphTo)
     */
    public function performedBy(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 대상 관계 (MorphTo)
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 관리자 사용자 관계
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'performed_by_id')
            ->where('performed_by_type', AdminUser::class);
    }

    /**
     * 성공한 운영 로그만 조회하는 스코프
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * 실패한 운영 로그만 조회하는 스코프
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * 부분 성공한 운영 로그만 조회하는 스코프
     */
    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * 특정 운영 타입의 로그만 조회하는 스코프
     */
    public function scopeByOperationType($query, string $type)
    {
        return $query->where('operation_type', $type);
    }

    /**
     * 특정 수행자의 로그만 조회하는 스코프
     */
    public function scopeByPerformer($query, string $type, int $id)
    {
        return $query->where('performed_by_type', $type)
                    ->where('performed_by_id', $id);
    }

    /**
     * 특정 IP 주소의 로그만 조회하는 스코프
     */
    public function scopeByIpAddress($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * 특정 세션의 로그만 조회하는 스코프
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * 느린 운영 로그만 조회하는 스코프 (기본값: 1000ms 이상)
     */
    public function scopeSlow($query, int $threshold = 1000)
    {
        return $query->where('execution_time', '>', $threshold);
    }

    /**
     * 특정 중요도 이상의 로그만 조회하는 스코프
     */
    public function scopeBySeverity($query, string $severity)
    {
        $severityLevels = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $level = $severityLevels[$severity] ?? 1;
        
        return $query->whereIn('severity', array_keys(array_filter($severityLevels, function($value) use ($level) {
            return $value >= $level;
        })));
    }

    /**
     * 최근 로그만 조회하는 스코프
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 특정 날짜 범위의 로그만 조회하는 스코프
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 성공률 계산
     */
    public static function getSuccessRate($days = 30): float
    {
        $startDate = now()->subDays($days);
        
        $total = static::where('created_at', '>=', $startDate)->count();
        $success = static::where('created_at', '>=', $startDate)
                       ->where('status', 'success')
                       ->count();
        
        return $total > 0 ? round(($success / $total) * 100, 2) : 0;
    }

    /**
     * 평균 실행 시간 계산
     */
    public static function getAverageExecutionTime($days = 30): float
    {
        $startDate = now()->subDays($days);
        
        return static::where('created_at', '>=', $startDate)
                    ->whereNotNull('execution_time')
                    ->avg('execution_time') ?? 0;
    }

    /**
     * 포맷된 실행 시간 반환
     */
    public function getDurationFormattedAttribute(): string
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
     * 상태 라벨 반환
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'success' => '성공',
            'failed' => '실패',
            'partial' => '부분 성공'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * 중요도 라벨 반환
     */
    public function getSeverityLabelAttribute(): string
    {
        $labels = [
            'low' => '낮음',
            'medium' => '보통',
            'high' => '높음',
            'critical' => '치명적'
        ];

        return $labels[$this->severity] ?? $this->severity;
    }

    /**
     * 안전한 배열 변환
     */
    public function toSafeArray(): array
    {
        return [
            'id' => $this->id,
            'operation_type' => $this->operation_type,
            'operation_name' => $this->operation_name,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'execution_time' => $this->execution_time,
            'duration_formatted' => $this->duration_formatted,
            'severity' => $this->severity,
            'severity_label' => $this->severity_label,
            'ip_address' => $this->ip_address,
            'session_id' => $this->session_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }

    /**
     * 팩토리 클래스 반환
     */
    protected static function newFactory()
    {
        return \Jiny\Admin\Database\Factories\SystemOperationLogFactory::new();
    }
}
