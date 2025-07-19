<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * 시스템 유지보수 로그 모델
 *
 * 이 모델은 시스템의 모든 유지보수 활동을 상세히 기록합니다.
 * - 예정된 및 긴급 유지보수 일정 관리 (시스템 안정성)
 * - 유지보수 진행 상황 및 완료 상태 추적 (작업 관리)
 * - 다운타임 영향도 평가 및 서비스 영향 분석 (고객 서비스)
 * - 유지보수 책임자 및 작업 시간 기록 (책임 소재)
 * - 우선순위별 유지보수 관리 (리소스 최적화)
 *
 * 운영 목적: 시스템 안정성 보장, 고객 서비스 최소화, 유지보수 효율성 향상
 *
 * 도메인 지식:
 * - 유지보수 로그는 시스템 운영과 고객 서비스의 균형점
 * - 예정 vs 실제 시간 비교는 유지보수 계획 개선에 활용
 * - 영향도 평가는 고객 커뮤니케이션과 서비스 수준 결정에 중요
 * - 우선순위 관리는 리소스 할당과 위험 관리에 필수
 */
class SystemMaintenanceLog extends Model
{
    use HasFactory;

    /**
     * 테이블명
     */
    protected $table = 'system_maintenance_logs';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'maintenance_type',
        'title',
        'description',
        'status',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'duration_minutes',
        'notes',
        'impact_assessment',
        'initiated_by',
        'completed_by',
        'requires_downtime',
        'priority',
        'affected_services',
        'metadata',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'duration_minutes' => 'integer',
        'requires_downtime' => 'boolean',
        'affected_services' => 'array',
        'metadata' => 'array',
    ];

    /**
     * 유지보수 타입 상수
     */
    const TYPE_SCHEDULED = 'scheduled';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_REPAIR = 'repair';

    /**
     * 상태 상수
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    /**
     * 우선순위 상수
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * 유지보수 타입 목록
     */
    public static function getMaintenanceTypes(): array
    {
        return [
            self::TYPE_SCHEDULED => '예정된 유지보수',
            self::TYPE_EMERGENCY => '긴급 유지보수',
            self::TYPE_UPGRADE => '업그레이드',
            self::TYPE_REPAIR => '수리',
        ];
    }

    /**
     * 상태 목록
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => '예정',
            self::STATUS_IN_PROGRESS => '진행중',
            self::STATUS_COMPLETED => '완료',
            self::STATUS_CANCELLED => '취소',
            self::STATUS_FAILED => '실패',
        ];
    }

    /**
     * 우선순위 목록
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => '낮음',
            self::PRIORITY_MEDIUM => '보통',
            self::PRIORITY_HIGH => '높음',
            self::PRIORITY_CRITICAL => '치명적',
        ];
    }

    /**
     * 유지보수를 시작한 관리자와의 관계
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'initiated_by');
    }

    /**
     * 유지보수를 완료한 관리자와의 관계
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(\Jiny\Admin\Models\AdminUser::class, 'completed_by');
    }

    /**
     * 완료된 유지보수만 조회하는 스코프
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * 진행중인 유지보수만 조회하는 스코프
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * 예정된 유지보수만 조회하는 스코프
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * 긴급 유지보수만 조회하는 스코프
     */
    public function scopeEmergency(Builder $query): Builder
    {
        return $query->where('maintenance_type', self::TYPE_EMERGENCY);
    }

    /**
     * 다운타임이 필요한 유지보수만 조회하는 스코프
     */
    public function scopeRequiresDowntime(Builder $query): Builder
    {
        return $query->where('requires_downtime', true);
    }

    /**
     * 특정 우선순위의 유지보수만 조회하는 스코프
     */
    public function scopeOfPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * 최근 유지보수만 조회하는 스코프
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 유지보수 완료 여부 확인
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * 유지보수 진행중 여부 확인
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * 유지보수 예정 여부 확인
     */
    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /**
     * 긴급 유지보수 여부 확인
     */
    public function isEmergency(): bool
    {
        return $this->maintenance_type === self::TYPE_EMERGENCY;
    }

    /**
     * 다운타임 필요 여부 확인
     */
    public function requiresDowntime(): bool
    {
        return $this->requires_downtime;
    }

    /**
     * 실제 소요 시간 (분) 계산
     */
    public function getActualDurationMinutes(): int
    {
        if (!$this->actual_start || !$this->actual_end) {
            return 0;
        }

        return $this->actual_start->diffInMinutes($this->actual_end);
    }

    /**
     * 예정 시간과 실제 시간의 차이 (분) 계산
     */
    public function getTimeDifferenceMinutes(): int
    {
        if (!$this->scheduled_start || !$this->actual_start) {
            return 0;
        }

        return $this->scheduled_start->diffInMinutes($this->actual_start);
    }

    /**
     * 지연 여부 확인
     */
    public function isDelayed(): bool
    {
        if (!$this->scheduled_start || !$this->actual_start) {
            return false;
        }

        return $this->actual_start->isAfter($this->scheduled_start);
    }

    /**
     * 조기 시작 여부 확인
     */
    public function isEarlyStart(): bool
    {
        if (!$this->scheduled_start || !$this->actual_start) {
            return false;
        }

        return $this->actual_start->isBefore($this->scheduled_start);
    }

    /**
     * 유지보수 상태에 따른 CSS 클래스 반환
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_CANCELLED => 'secondary',
            default => 'info',
        };
    }

    /**
     * 우선순위에 따른 CSS 클래스 반환
     */
    public function getPriorityClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_MEDIUM => 'info',
            default => 'secondary',
        };
    }

    /**
     * 유지보수 타입에 따른 아이콘 반환
     */
    public function getMaintenanceTypeIcon(): string
    {
        return match($this->maintenance_type) {
            self::TYPE_SCHEDULED => 'calendar',
            self::TYPE_EMERGENCY => 'exclamation-triangle',
            self::TYPE_UPGRADE => 'arrow-up',
            self::TYPE_REPAIR => 'wrench',
            default => 'tools',
        };
    }

    /**
     * 유지보수 성공률 계산 (정적 메서드)
     */
    public static function getSuccessRate(): float
    {
        $total = self::count();
        if ($total === 0) {
            return 0;
        }

        $completed = self::where('status', self::STATUS_COMPLETED)->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * 평균 유지보수 시간 계산 (정적 메서드)
     */
    public static function getAverageDuration(): float
    {
        $completed = self::where('status', self::STATUS_COMPLETED)
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');

        return $completed ? round($completed, 2) : 0;
    }

    /**
     * 최근 유지보수 통계 조회 (정적 메서드)
     */
    public static function getRecentStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => self::where('created_at', '>=', $startDate)->count(),
            'completed' => self::where('status', self::STATUS_COMPLETED)
                ->where('created_at', '>=', $startDate)->count(),
            'in_progress' => self::where('status', self::STATUS_IN_PROGRESS)
                ->where('created_at', '>=', $startDate)->count(),
            'scheduled' => self::where('status', self::STATUS_SCHEDULED)
                ->where('created_at', '>=', $startDate)->count(),
            'emergency' => self::where('maintenance_type', self::TYPE_EMERGENCY)
                ->where('created_at', '>=', $startDate)->count(),
        ];
    }

    /**
     * 유지보수 타입별 통계 조회 (정적 메서드)
     */
    public static function getStatsByType(): array
    {
        return self::selectRaw('maintenance_type, COUNT(*) as count, AVG(duration_minutes) as avg_duration')
            ->groupBy('maintenance_type')
            ->get()
            ->keyBy('maintenance_type')
            ->toArray();
    }

    /**
     * 우선순위별 통계 조회 (정적 메서드)
     */
    public static function getStatsByPriority(): array
    {
        return self::selectRaw('priority, COUNT(*) as count, AVG(duration_minutes) as avg_duration')
            ->groupBy('priority')
            ->get()
            ->keyBy('priority')
            ->toArray();
    }

    /**
     * 다운타임 영향 분석 (정적 메서드)
     */
    public static function getDowntimeAnalysis(): array
    {
        $downtimeMaintenance = self::where('requires_downtime', true)
            ->where('status', self::STATUS_COMPLETED)
            ->get();

        return [
            'total_downtime_maintenance' => $downtimeMaintenance->count(),
            'total_downtime_minutes' => $downtimeMaintenance->sum('duration_minutes'),
            'avg_downtime_minutes' => $downtimeMaintenance->avg('duration_minutes'),
            'downtime_maintenance_types' => $downtimeMaintenance->groupBy('maintenance_type')
                ->map(fn($group) => $group->count()),
        ];
    }

    /**
     * 유지보수 계획 준수율 계산 (정적 메서드)
     */
    public static function getScheduleComplianceRate(): float
    {
        $completed = self::where('status', self::STATUS_COMPLETED)
            ->whereNotNull('scheduled_start')
            ->whereNotNull('actual_start')
            ->get();

        if ($completed->isEmpty()) {
            return 0;
        }

        $onTime = $completed->filter(function ($maintenance) {
            $diff = $maintenance->scheduled_start->diffInMinutes($maintenance->actual_start);
            return $diff <= 15; // 15분 이내 차이는 정시로 간주
        })->count();

        return round(($onTime / $completed->count()) * 100, 2);
    }

    /**
     * 유지보수 품질 분석 (정적 메서드)
     */
    public static function getQualityAnalysis(): array
    {
        $completed = self::where('status', self::STATUS_COMPLETED)
            ->whereNotNull('duration_minutes')
            ->get();

        return [
            'total_completed' => $completed->count(),
            'avg_duration' => $completed->avg('duration_minutes'),
            'min_duration' => $completed->min('duration_minutes'),
            'max_duration' => $completed->max('duration_minutes'),
            'emergency_maintenance_count' => $completed->where('maintenance_type', self::TYPE_EMERGENCY)->count(),
            'downtime_maintenance_count' => $completed->where('requires_downtime', true)->count(),
        ];
    }

    /**
     * 유지보수 권장사항 생성 (정적 메서드)
     */
    public static function getRecommendations(): array
    {
        $recommendations = [];

        $successRate = self::getSuccessRate();
        if ($successRate < 95) {
            $recommendations[] = '유지보수 성공률이 낮습니다. 유지보수 프로세스를 검토하세요.';
        }

        $complianceRate = self::getScheduleComplianceRate();
        if ($complianceRate < 90) {
            $recommendations[] = '유지보수 일정 준수율이 낮습니다. 일정 계획을 개선하세요.';
        }

        $emergencyCount = self::where('maintenance_type', self::TYPE_EMERGENCY)
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        if ($emergencyCount > 5) {
            $recommendations[] = '긴급 유지보수가 많습니다. 예방적 유지보수를 강화하세요.';
        }

        $avgDuration = self::getAverageDuration();
        if ($avgDuration > 240) { // 4시간 이상
            $recommendations[] = '유지보수 시간이 오래 걸립니다. 유지보수 효율성을 개선하세요.';
        }

        return $recommendations;
    }

    /**
     * 향후 유지보수 일정 조회 (정적 메서드)
     */
    public static function getUpcomingMaintenance(int $days = 7): array
    {
        $startDate = now();
        $endDate = now()->addDays($days);

        return self::where('status', self::STATUS_SCHEDULED)
            ->whereBetween('scheduled_start', [$startDate, $endDate])
            ->orderBy('scheduled_start')
            ->get()
            ->toArray();
    }

    /**
     * 유지보수 영향도 평가 (정적 메서드)
     */
    public static function getImpactAssessment(): array
    {
        $recentMaintenance = self::where('created_at', '>=', now()->subMonth())
            ->where('status', self::STATUS_COMPLETED)
            ->get();

        return [
            'total_maintenance' => $recentMaintenance->count(),
            'downtime_maintenance' => $recentMaintenance->where('requires_downtime', true)->count(),
            'total_downtime_minutes' => $recentMaintenance->where('requires_downtime', true)->sum('duration_minutes'),
            'emergency_maintenance' => $recentMaintenance->where('maintenance_type', self::TYPE_EMERGENCY)->count(),
            'avg_impact_score' => $recentMaintenance->avg('impact_assessment'),
        ];
    }
}
