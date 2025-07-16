<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * 시스템 성능 로그 모델
 * 
 * 이 모델은 시스템의 모든 성능 메트릭을 상세히 기록합니다.
 * - CPU, 메모리, 디스크, 네트워크, 데이터베이스 성능 모니터링 (시스템 건강도)
 * - 성능 임계값 설정 및 알림 관리 (프로액티브 모니터링)
 * - 서버별 및 컴포넌트별 성능 분석 (리소스 최적화)
 * - 성능 트렌드 분석 및 예측 (용량 계획)
 * - 성능 병목 지점 식별 및 해결 (시스템 최적화)
 * 
 * 운영 목적: 시스템 안정성 보장, 성능 최적화, 용량 계획
 * 
 * 도메인 지식:
 * - 성능 로그는 시스템 운영과 사용자 경험의 핵심 지표
 * - 임계값 관리는 프로액티브 문제 해결에 필수
 * - 트렌드 분석은 용량 계획과 리소스 할당에 중요
 * - 컴포넌트별 분석은 성능 병목 지점 식별에 활용
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
        'metric_name',
        'metric_type',
        'value',
        'unit',
        'threshold',
        'status',
        'server_name',
        'component',
        'additional_data',
        'measured_at',
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'value' => 'decimal:4',
        'threshold' => 'string',
        'measured_at' => 'datetime',
        'additional_data' => 'array',
    ];

    /**
     * 메트릭 타입 상수
     */
    const TYPE_CPU = 'cpu';
    const TYPE_MEMORY = 'memory';
    const TYPE_DISK = 'disk';
    const TYPE_NETWORK = 'network';
    const TYPE_DATABASE = 'database';

    /**
     * 상태 상수
     */
    const STATUS_NORMAL = 'normal';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'critical';

    /**
     * 메트릭 타입 목록
     */
    public static function getMetricTypes(): array
    {
        return [
            self::TYPE_CPU => 'CPU',
            self::TYPE_MEMORY => '메모리',
            self::TYPE_DISK => '디스크',
            self::TYPE_NETWORK => '네트워크',
            self::TYPE_DATABASE => '데이터베이스',
        ];
    }

    /**
     * 상태 목록
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NORMAL => '정상',
            self::STATUS_WARNING => '경고',
            self::STATUS_CRITICAL => '치명적',
        ];
    }

    /**
     * 정상 상태의 성능 로그만 조회하는 스코프
     */
    public function scopeNormal(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NORMAL);
    }

    /**
     * 경고 상태의 성능 로그만 조회하는 스코프
     */
    public function scopeWarning(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WARNING);
    }

    /**
     * 치명적 상태의 성능 로그만 조회하는 스코프
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CRITICAL);
    }

    /**
     * 특정 타입의 메트릭만 조회하는 스코프
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('metric_type', $type);
    }

    /**
     * 특정 서버의 성능 로그만 조회하는 스코프
     */
    public function scopeOfServer(Builder $query, string $serverName): Builder
    {
        return $query->where('server_name', $serverName);
    }

    /**
     * 특정 컴포넌트의 성능 로그만 조회하는 스코프
     */
    public function scopeOfComponent(Builder $query, string $component): Builder
    {
        return $query->where('component', $component);
    }

    /**
     * 최근 성능 로그만 조회하는 스코프
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('measured_at', '>=', now()->subHours($hours));
    }

    /**
     * 임계값을 초과한 성능 로그만 조회하는 스코프
     */
    public function scopeExceedsThreshold(Builder $query): Builder
    {
        return $query->whereRaw('CAST(value AS DECIMAL(10,4)) > CAST(threshold AS DECIMAL(10,4))');
    }

    /**
     * 성능 정상 여부 확인
     */
    public function isNormal(): bool
    {
        return $this->status === self::STATUS_NORMAL;
    }

    /**
     * 성능 경고 여부 확인
     */
    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * 성능 치명적 여부 확인
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
        if (!$this->threshold) {
            return false;
        }

        return (float) $this->value > (float) $this->threshold;
    }

    /**
     * 성능 상태에 따른 CSS 클래스 반환
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            self::STATUS_NORMAL => 'success',
            self::STATUS_WARNING => 'warning',
            self::STATUS_CRITICAL => 'danger',
            default => 'info',
        };
    }

    /**
     * 메트릭 타입에 따른 아이콘 반환
     */
    public function getMetricTypeIcon(): string
    {
        return match($this->metric_type) {
            self::TYPE_CPU => 'microchip',
            self::TYPE_MEMORY => 'memory',
            self::TYPE_DISK => 'hdd',
            self::TYPE_NETWORK => 'network-wired',
            self::TYPE_DATABASE => 'database',
            default => 'chart-line',
        };
    }

    /**
     * 성능 값 포맷팅
     */
    public function getFormattedValue(): string
    {
        $value = (float) $this->value;
        
        return match($this->unit) {
            '%' => number_format($value, 2) . '%',
            'MB', 'GB', 'TB' => number_format($value, 2) . ' ' . $this->unit,
            'ms', 's' => number_format($value, 2) . ' ' . $this->unit,
            default => number_format($value, 2) . ' ' . $this->unit,
        };
    }

    /**
     * 성능 트렌드 분석 (정적 메서드)
     */
    public static function getTrendAnalysis(string $metricName, int $hours = 24): array
    {
        $logs = self::where('metric_name', $metricName)
            ->where('measured_at', '>=', now()->subHours($hours))
            ->orderBy('measured_at')
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        $values = $logs->pluck('value')->toArray();
        $timestamps = $logs->pluck('measured_at')->toArray();

        return [
            'current_value' => $logs->last()->value,
            'average_value' => $logs->avg('value'),
            'min_value' => $logs->min('value'),
            'max_value' => $logs->max('value'),
            'trend' => self::calculateTrend($values),
            'data_points' => count($values),
            'values' => $values,
            'timestamps' => $timestamps,
        ];
    }

    /**
     * 성능 병목 지점 식별 (정적 메서드)
     */
    public static function identifyBottlenecks(int $hours = 1): array
    {
        $recentLogs = self::where('measured_at', '>=', now()->subHours($hours))
            ->whereIn('status', [self::STATUS_WARNING, self::STATUS_CRITICAL])
            ->get();

        $bottlenecks = [];
        foreach ($recentLogs as $log) {
            $key = $log->server_name . '_' . $log->metric_type;
            if (!isset($bottlenecks[$key])) {
                $bottlenecks[$key] = [
                    'server_name' => $log->server_name,
                    'metric_type' => $log->metric_type,
                    'metric_name' => $log->metric_name,
                    'current_value' => $log->value,
                    'threshold' => $log->threshold,
                    'status' => $log->status,
                    'occurrences' => 0,
                ];
            }
            $bottlenecks[$key]['occurrences']++;
        }

        return array_values($bottlenecks);
    }

    /**
     * 성능 알림 생성 (정적 메서드)
     */
    public static function generateAlerts(): array
    {
        $alerts = [];

        // 치명적 상태의 메트릭들
        $criticalMetrics = self::where('status', self::STATUS_CRITICAL)
            ->where('measured_at', '>=', now()->subMinutes(5))
            ->get();

        foreach ($criticalMetrics as $metric) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "{$metric->server_name}의 {$metric->metric_name}이 치명적 수준입니다: {$metric->getFormattedValue()}",
                'metric' => $metric,
            ];
        }

        // 경고 상태의 메트릭들
        $warningMetrics = self::where('status', self::STATUS_WARNING)
            ->where('measured_at', '>=', now()->subMinutes(5))
            ->get();

        foreach ($warningMetrics as $metric) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$metric->server_name}의 {$metric->metric_name}이 경고 수준입니다: {$metric->getFormattedValue()}",
                'metric' => $metric,
            ];
        }

        return $alerts;
    }

    /**
     * 성능 통계 조회 (정적 메서드)
     */
    public static function getPerformanceStats(int $hours = 24): array
    {
        $startTime = now()->subHours($hours);

        return [
            'total_metrics' => self::where('measured_at', '>=', $startTime)->count(),
            'normal_metrics' => self::where('status', self::STATUS_NORMAL)
                ->where('measured_at', '>=', $startTime)->count(),
            'warning_metrics' => self::where('status', self::STATUS_WARNING)
                ->where('measured_at', '>=', $startTime)->count(),
            'critical_metrics' => self::where('status', self::STATUS_CRITICAL)
                ->where('measured_at', '>=', $startTime)->count(),
            'servers_monitored' => self::where('measured_at', '>=', $startTime)
                ->distinct('server_name')->count('server_name'),
            'components_monitored' => self::where('measured_at', '>=', $startTime)
                ->distinct('component')->count('component'),
        ];
    }

    /**
     * 서버별 성능 분석 (정적 메서드)
     */
    public static function getServerPerformanceAnalysis(): array
    {
        $servers = self::select('server_name')
            ->distinct()
            ->whereNotNull('server_name')
            ->pluck('server_name');

        $analysis = [];
        foreach ($servers as $server) {
            $serverMetrics = self::where('server_name', $server)
                ->where('measured_at', '>=', now()->subHour())
                ->get();

            $analysis[$server] = [
                'total_metrics' => $serverMetrics->count(),
                'critical_count' => $serverMetrics->where('status', self::STATUS_CRITICAL)->count(),
                'warning_count' => $serverMetrics->where('status', self::STATUS_WARNING)->count(),
                'normal_count' => $serverMetrics->where('status', self::STATUS_NORMAL)->count(),
                'avg_cpu' => $serverMetrics->where('metric_type', self::TYPE_CPU)->avg('value'),
                'avg_memory' => $serverMetrics->where('metric_type', self::TYPE_MEMORY)->avg('value'),
                'avg_disk' => $serverMetrics->where('metric_type', self::TYPE_DISK)->avg('value'),
            ];
        }

        return $analysis;
    }

    /**
     * 성능 용량 계획 (정적 메서드)
     */
    public static function getCapacityPlanning(): array
    {
        $cpuTrend = self::getTrendAnalysis('cpu_usage', 168); // 1주일
        $memoryTrend = self::getTrendAnalysis('memory_usage', 168);
        $diskTrend = self::getTrendAnalysis('disk_usage', 168);

        return [
            'cpu_growth_rate' => self::calculateGrowthRate($cpuTrend['values'] ?? []),
            'memory_growth_rate' => self::calculateGrowthRate($memoryTrend['values'] ?? []),
            'disk_growth_rate' => self::calculateGrowthRate($diskTrend['values'] ?? []),
            'estimated_cpu_peak' => self::estimatePeakUsage($cpuTrend['values'] ?? []),
            'estimated_memory_peak' => self::estimatePeakUsage($memoryTrend['values'] ?? []),
            'estimated_disk_peak' => self::estimatePeakUsage($diskTrend['values'] ?? []),
        ];
    }

    /**
     * 성능 최적화 권장사항 (정적 메서드)
     */
    public static function getOptimizationRecommendations(): array
    {
        $recommendations = [];

        // CPU 사용률 분석
        $avgCpu = self::where('metric_type', self::TYPE_CPU)
            ->where('measured_at', '>=', now()->subHour())
            ->avg('value');

        if ($avgCpu > 80) {
            $recommendations[] = 'CPU 사용률이 높습니다. 서버 리소스를 확장하거나 부하를 분산하세요.';
        }

        // 메모리 사용률 분석
        $avgMemory = self::where('metric_type', self::TYPE_MEMORY)
            ->where('measured_at', '>=', now()->subHour())
            ->avg('value');

        if ($avgMemory > 85) {
            $recommendations[] = '메모리 사용률이 높습니다. 메모리를 확장하거나 메모리 누수를 확인하세요.';
        }

        // 디스크 사용률 분석
        $avgDisk = self::where('metric_type', self::TYPE_DISK)
            ->where('measured_at', '>=', now()->subHour())
            ->avg('value');

        if ($avgDisk > 90) {
            $recommendations[] = '디스크 사용률이 높습니다. 불필요한 파일을 정리하거나 저장공간을 확장하세요.';
        }

        // 네트워크 성능 분석
        $networkIssues = self::where('metric_type', self::TYPE_NETWORK)
            ->whereIn('status', [self::STATUS_WARNING, self::STATUS_CRITICAL])
            ->where('measured_at', '>=', now()->subHour())
            ->count();

        if ($networkIssues > 0) {
            $recommendations[] = '네트워크 성능 문제가 감지되었습니다. 네트워크 설정을 확인하세요.';
        }

        return $recommendations;
    }

    /**
     * 트렌드 계산 (private 메서드)
     */
    private static function calculateTrend(array $values): string
    {
        if (count($values) < 2) {
            return 'stable';
        }

        $firstHalf = array_slice($values, 0, floor(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        $difference = $secondAvg - $firstAvg;
        $percentage = ($difference / $firstAvg) * 100;

        if ($percentage > 5) {
            return 'increasing';
        } elseif ($percentage < -5) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }

    /**
     * 성장률 계산 (private 메서드)
     */
    private static function calculateGrowthRate(array $values): float
    {
        if (count($values) < 2) {
            return 0;
        }

        $first = $values[0];
        $last = end($values);
        
        return (($last - $first) / $first) * 100;
    }

    /**
     * 피크 사용량 예측 (private 메서드)
     */
    private static function estimatePeakUsage(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        $max = max($values);
        $avg = array_sum($values) / count($values);
        $growthRate = self::calculateGrowthRate($values);

        // 현재 최대값 + 성장률을 고려한 예측
        return min(100, $max + ($growthRate / 100) * $avg);
    }
} 