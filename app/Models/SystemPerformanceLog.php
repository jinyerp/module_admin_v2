<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * SystemPerformanceLog 모델
 * 
 * 시스템의 성능 지표를 수집하고 분석하는 모델입니다.
 * 
 * @property int $id
 * @property string $metric_type 지표 타입
 * @property string $metric_name 지표명
 * @property float $value 지표 값
 * @property string $unit 단위
 * @property float|null $threshold 임계값
 * @property string $status 상태
 * @property string|null $endpoint API 엔드포인트 또는 페이지 URL
 * @property string|null $method HTTP 메서드
 * @property string|null $user_agent 사용자 에이전트
 * @property string|null $ip_address 클라이언트 IP
 * @property string|null $session_id 세션 ID
 * @property array|null $additional_data 추가 데이터
 * @property Carbon $measured_at 기록 시간
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SystemPerformanceLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'system_performance_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'metric_type',
        'metric_name',
        'value',
        'unit',
        'threshold',
        'status',
        'endpoint',
        'method',
        'user_agent',
        'ip_address',
        'session_id',
        'additional_data',
        'measured_at',
    ];

    /**
     * 타입 캐스팅
     */
    protected $casts = [
        'value' => 'float',
        'threshold' => 'float',
        'additional_data' => 'array',
        'measured_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 숨겨진 속성들
     */
    protected $hidden = [
        'additional_data',
    ];

    /**
     * 추가된 속성들
     */
    protected $appends = [
        'is_alert',
        'performance_level',
    ];

    /**
     * 상태 상수
     */
    const STATUS_NORMAL = 'normal';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'critical';

    /**
     * 지표 타입 상수
     */
    const TYPE_WEB = 'web';
    const TYPE_DATABASE = 'database';
    const TYPE_CACHE = 'cache';
    const TYPE_MEMORY = 'memory';
    const TYPE_ERROR = 'error';

    /**
     * 단위 상수
     */
    const UNIT_PERCENT = '%';
    const UNIT_MB = 'MB';
    const UNIT_GB = 'GB';
    const UNIT_KBPS = 'KB/s';
    const UNIT_MBPS = 'MB/s';
    const UNIT_MS = 'ms';
    const UNIT_COUNT = 'count';

    /**
     * 부트 메서드
     */
    protected static function boot()
    {
        parent::boot();

        // 생성 시 기본값 설정
        static::creating(function ($model) {
            if (empty($model->measured_at)) {
                $model->measured_at = now();
            }
            
            if (empty($model->status)) {
                $model->status = self::STATUS_NORMAL;
            }
        });
    }

    /**
     * 팩토리 클래스 해결
     */
    public static function newFactory()
    {
        return \Jiny\Admin\Database\Factories\SystemPerformanceLogFactory::new();
    }

    /**
     * 알림 상태 여부
     */
    public function getIsAlertAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_WARNING, self::STATUS_CRITICAL]);
    }

    /**
     * 성능 레벨 (1-5)
     */
    public function getPerformanceLevelAttribute(): int
    {
        if ($this->status === self::STATUS_CRITICAL) {
            return 1;
        } elseif ($this->status === self::STATUS_WARNING) {
            return 2;
        } elseif ($this->value <= 50) {
            return 5;
        } elseif ($this->value <= 70) {
            return 4;
        } else {
            return 3;
        }
    }

    /**
     * 웹 관련 지표 스코프
     */
    public function scopeWeb($query)
    {
        return $query->where('metric_type', self::TYPE_WEB);
    }

    /**
     * 데이터베이스 관련 지표 스코프
     */
    public function scopeDatabase($query)
    {
        return $query->where('metric_type', self::TYPE_DATABASE);
    }

    /**
     * 캐시 관련 지표 스코프
     */
    public function scopeCache($query)
    {
        return $query->where('metric_type', self::TYPE_CACHE);
    }

    /**
     * 메모리 관련 지표 스코프
     */
    public function scopeMemory($query)
    {
        return $query->where('metric_type', self::TYPE_MEMORY);
    }

    /**
     * 에러 관련 지표 스코프
     */
    public function scopeError($query)
    {
        return $query->where('metric_type', self::TYPE_ERROR);
    }

    /**
     * 정상 상태 스코프
     */
    public function scopeNormal($query)
    {
        return $query->where('status', self::STATUS_NORMAL);
    }

    /**
     * 경고 상태 스코프
     */
    public function scopeWarning($query)
    {
        return $query->where('status', self::STATUS_WARNING);
    }

    /**
     * 위험 상태 스코프
     */
    public function scopeCritical($query)
    {
        return $query->where('status', self::STATUS_CRITICAL);
    }

    /**
     * 알림 상태 스코프
     */
    public function scopeAlert($query)
    {
        return $query->whereIn('status', [self::STATUS_WARNING, self::STATUS_CRITICAL]);
    }

    /**
     * 최근 데이터 스코프
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('measured_at', '>=', now()->subHours($hours));
    }

    /**
     * 특정 기간 스코프
     */
    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('measured_at', [$start, $end]);
    }

    /**
     * 임계값 초과 스코프
     */
    public function scopeExceedsThreshold($query)
    {
        return $query->whereColumn('value', '>', 'threshold');
    }

    /**
     * 특정 엔드포인트 스코프
     */
    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    /**
     * 특정 세션 스코프
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * 성능 지표가 정상인지 확인
     */
    public function isNormal(): bool
    {
        return $this->status === self::STATUS_NORMAL;
    }

    /**
     * 성능 지표가 경고 상태인지 확인
     */
    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * 성능 지표가 위험 상태인지 확인
     */
    public function isCritical(): bool
    {
        return $this->status === self::STATUS_CRITICAL;
    }

    /**
     * 임계값 초과 여부 확인
     */
    public function exceedsThreshold(): bool
    {
        return $this->threshold && $this->value > $this->threshold;
    }

    /**
     * 성능 지표 개선 필요 여부 확인
     */
    public function needsAttention(): bool
    {
        return $this->isAlert() || $this->exceedsThreshold();
    }

    /**
     * 성능 지표 상태 업데이트
     */
    public function updateStatus(): void
    {
        if ($this->threshold) {
            if ($this->value >= $this->threshold * 1.2) {
                $this->status = self::STATUS_CRITICAL;
            } elseif ($this->value >= $this->threshold) {
                $this->status = self::STATUS_WARNING;
            } else {
                $this->status = self::STATUS_NORMAL;
            }
        }
    }

    /**
     * 성능 지표 요약 정보
     */
    public function getSummary(): array
    {
        return [
            'type' => $this->metric_type,
            'name' => $this->metric_name,
            'value' => $this->value,
            'unit' => $this->unit,
            'status' => $this->status,
            'is_alert' => $this->is_alert,
            'performance_level' => $this->performance_level,
            'measured_at' => $this->measured_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 성능 지표 타입별 색상
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NORMAL => 'success',
            self::STATUS_WARNING => 'warning',
            self::STATUS_CRITICAL => 'danger',
            default => 'secondary'
        };
    }

    /**
     * 성능 지표 아이콘
     */
    public function getMetricIconAttribute(): string
    {
        return match($this->metric_type) {
            self::TYPE_WEB => 'globe',
            self::TYPE_DATABASE => 'database',
            self::TYPE_CACHE => 'bolt',
            self::TYPE_MEMORY => 'memory',
            self::TYPE_ERROR => 'exclamation-triangle',
            default => 'chart-line'
        };
    }

    /**
     * 사용 가능한 메트릭 타입 목록 반환
     */
    public static function getMetricTypes(): array
    {
        return [
            self::TYPE_WEB => '웹 애플리케이션',
            self::TYPE_DATABASE => '데이터베이스',
            self::TYPE_CACHE => '캐시',
            self::TYPE_MEMORY => '메모리',
            self::TYPE_ERROR => '에러',
        ];
    }

    /**
     * 사용 가능한 상태 목록 반환
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NORMAL => '정상',
            self::STATUS_WARNING => '경고',
            self::STATUS_CRITICAL => '위험',
        ];
    }
} 