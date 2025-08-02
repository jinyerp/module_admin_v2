<?php

namespace Jiny\Admin\App\Services;

use Illuminate\Support\Facades\Cache;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\Log;

class CachePerformanceService
{
    /**
     * 캐시 히트율을 기록
     */
    public function logCacheHitRate(): void
    {
        try {
            $hits = Cache::get('cache_hits', 0);
            $misses = Cache::get('cache_misses', 0);
            $totalRequests = $hits + $misses;

            if ($totalRequests > 0) {
                $hitRate = ($hits / $totalRequests) * 100;

                SystemPerformanceLog::create([
                    'metric_name' => 'cache_hit_rate',
                    'metric_type' => 'cache',
                    'value' => $hitRate,
                    'unit' => '%',
                    'threshold' => '80', // 80% 미만이면 경고
                    'status' => $hitRate < 80 ? 'warning' : 'normal',
                    'endpoint' => request()->path() ?? 'console',
                    'method' => request()->method() ?? 'CLI',
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'session_id' => request()->session()?->getId(),
                    'additional_data' => json_encode([
                        'hits' => $hits,
                        'misses' => $misses,
                        'total_requests' => $totalRequests,
                    ]),
                    'measured_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('캐시 히트율 로그 기록 실패: ' . $e->getMessage());
        }
    }

    /**
     * 캐시 크기를 기록
     */
    public function logCacheSize(): void
    {
        try {
            $cacheSize = 0;
            
            if (config('cache.default') === 'redis') {
                try {
                    $redis = Cache::getRedis();
                    $cacheSize = $redis->dbSize();
                } catch (\Exception $e) {
                    // Redis 연결 실패 시 무시
                }
            }

            SystemPerformanceLog::create([
                'metric_name' => 'cache_size',
                'metric_type' => 'cache',
                'value' => $cacheSize,
                'unit' => 'keys',
                'threshold' => '10000', // 10,000개 이상이면 경고
                'status' => $cacheSize > 10000 ? 'warning' : 'normal',
                'endpoint' => request()->path() ?? 'console',
                'method' => request()->method() ?? 'CLI',
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'session_id' => request()->session()?->getId(),
                'additional_data' => json_encode([
                    'cache_driver' => config('cache.default'),
                ]),
                'measured_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('캐시 크기 로그 기록 실패: ' . $e->getMessage());
        }
    }

    /**
     * 캐시 히트 증가
     */
    public function incrementHits(): void
    {
        Cache::increment('cache_hits');
    }

    /**
     * 캐시 미스 증가
     */
    public function incrementMisses(): void
    {
        Cache::increment('cache_misses');
    }
} 