<?php

namespace Jiny\Admin\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // 요청 처리
        $response = $next($request);

        // 성능 측정
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $requestTime = ($endTime - $startTime) * 1000; // 밀리초 단위
        $memoryUsage = $endMemory - $startMemory;
        $peakMemory = memory_get_peak_usage(true);

        // 성능 로그 기록
        $this->logPerformanceMetrics($request, $response, $requestTime, $memoryUsage, $peakMemory);

        return $response;
    }

    /**
     * 성능 메트릭을 로그에 기록
     */
    private function logPerformanceMetrics(Request $request, $response, float $requestTime, int $memoryUsage, int $peakMemory): void
    {
        try {
            // 요청 시간 로그
            SystemPerformanceLog::create([
                'metric_name' => 'request_time',
                'metric_type' => 'web',
                'value' => $requestTime,
                'unit' => 'ms',
                'threshold' => '1000', // 1초 이상이면 경고
                'status' => $requestTime > 1000 ? 'warning' : 'normal',
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'additional_data' => json_encode([
                    'response_status' => $response->getStatusCode(),
                    'content_length' => $response->headers->get('content-length'),
                ]),
                'measured_at' => now(),
            ]);

            // 메모리 사용량 로그
            SystemPerformanceLog::create([
                'metric_name' => 'memory_usage',
                'metric_type' => 'memory',
                'value' => $memoryUsage / 1024 / 1024, // MB 단위
                'unit' => 'MB',
                'threshold' => '128', // 128MB 이상이면 경고
                'status' => $memoryUsage > 128 * 1024 * 1024 ? 'warning' : 'normal',
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'session_id' => $request->session()->getId(),
                'additional_data' => json_encode([
                    'peak_memory' => $peakMemory / 1024 / 1024,
                    'memory_limit' => ini_get('memory_limit'),
                ]),
                'measured_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('성능 로그 기록 실패: ' . $e->getMessage());
        }
    }
} 