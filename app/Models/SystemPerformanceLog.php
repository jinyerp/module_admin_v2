<?php

namespace Jiny\Admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemPerformanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_name',
        'metric_type',
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

    protected $casts = [
        'value' => 'decimal:4',
        'additional_data' => 'array',
        'measured_at' => 'datetime',
    ];

    /**
     * 메트릭 타입 목록 반환
     */
    public static function getMetricTypes(): array
    {
        return [
            'web' => '웹 요청',
            'database' => '데이터베이스',
            'cache' => '캐시',
            'memory' => '메모리',
            'error' => '에러',
        ];
    }

    /**
     * 상태 목록 반환
     */
    public static function getStatuses(): array
    {
        return [
            'normal' => '정상',
            'warning' => '경고',
            'critical' => '치명적',
        ];
    }

    /**
     * 메트릭명별 통계
     */
    public function scopeByMetricName($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    /**
     * 메트릭 타입별 통계
     */
    public function scopeByMetricType($query, $metricType)
    {
        return $query->where('metric_type', $metricType);
    }

    /**
     * 상태별 통계
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 엔드포인트별 통계
     */
    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    /**
     * 시간 범위별 통계
     */
    public function scopeByTimeRange($query, $startTime, $endTime)
    {
        return $query->whereBetween('measured_at', [$startTime, $endTime]);
    }

    /**
     * 최근 로그 조회
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('measured_at', '>=', now()->subHours($hours));
    }

    /**
     * 느린 요청 조회 (1초 이상)
     */
    public function scopeSlowRequests($query)
    {
        return $query->where('metric_name', 'request_time')
                    ->where('value', '>', 1000);
    }

    /**
     * 느린 쿼리 조회 (1초 이상)
     */
    public function scopeSlowQueries($query)
    {
        return $query->where('metric_name', 'db_query_time')
                    ->where('value', '>', 1000);
    }

    /**
     * 경고 상태 이상 조회
     */
    public function scopeWarningsAndCritical($query)
    {
        return $query->whereIn('status', ['warning', 'critical']);
    }
} 