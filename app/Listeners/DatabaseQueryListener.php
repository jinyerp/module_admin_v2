<?php

namespace Jiny\Admin\App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\Log;

class DatabaseQueryListener
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Database\Events\QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event): void
    {
        try {
            $queryTime = $event->time; // 밀리초 단위
            $sql = $event->sql;
            $bindings = $event->bindings;
            $connection = $event->connectionName;

            // 느린 쿼리만 기록 (1초 이상)
            if ($queryTime >= 1000) {
                SystemPerformanceLog::create([
                    'metric_name' => 'db_query_time',
                    'metric_type' => 'database',
                    'value' => $queryTime,
                    'unit' => 'ms',
                    'threshold' => '1000', // 1초 이상이면 경고
                    'status' => $queryTime > 5000 ? 'critical' : 'warning',
                    'endpoint' => request()->path() ?? 'console',
                    'method' => request()->method() ?? 'CLI',
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'session_id' => request()->session()?->getId(),
                    'additional_data' => json_encode([
                        'sql' => $sql,
                        'bindings' => $bindings,
                        'connection' => $connection,
                        'query_count' => 1,
                    ]),
                    'measured_at' => now(),
                ]);
            }

            // 모든 쿼리의 평균 시간을 주기적으로 기록
            $this->logAverageQueryTime($queryTime);

        } catch (\Exception $e) {
            Log::error('데이터베이스 쿼리 로그 기록 실패: ' . $e->getMessage());
        }
    }

    /**
     * 평균 쿼리 시간을 주기적으로 기록
     */
    private function logAverageQueryTime(float $queryTime): void
    {
        $cacheKey = 'db_query_times_' . date('Y-m-d-H');
        $queryTimes = cache()->get($cacheKey, []);
        $queryTimes[] = $queryTime;

        // 최근 100개 쿼리만 유지
        if (count($queryTimes) > 100) {
            $queryTimes = array_slice($queryTimes, -100);
        }

        cache()->put($cacheKey, $queryTimes, 3600); // 1시간 캐시

        // 매 10번째 쿼리마다 평균 기록
        if (count($queryTimes) % 10 === 0) {
            $avgTime = array_sum($queryTimes) / count($queryTimes);
            
            SystemPerformanceLog::create([
                'metric_name' => 'avg_query_time',
                'metric_type' => 'database',
                'value' => $avgTime,
                'unit' => 'ms',
                'threshold' => '500', // 500ms 이상이면 경고
                'status' => $avgTime > 500 ? 'warning' : 'normal',
                'endpoint' => request()->path() ?? 'console',
                'method' => request()->method() ?? 'CLI',
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'session_id' => request()->session()?->getId(),
                'additional_data' => json_encode([
                    'query_count' => count($queryTimes),
                    'max_time' => max($queryTimes),
                    'min_time' => min($queryTimes),
                ]),
                'measured_at' => now(),
            ]);
        }
    }
} 