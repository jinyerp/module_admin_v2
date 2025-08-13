<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\App\Models\SystemOperationLog;
use Jiny\Admin\App\Models\AdminUser;

/**
 * SystemOperationLog 모델 팩토리
 */
class SystemOperationLogFactory extends Factory
{
    /**
     * 모델 클래스명
     */
    protected $model = SystemOperationLog::class;

    /**
     * 기본 정의
     */
    public function definition(): array
    {
        return [
            'operation_type' => $this->faker->randomElement([
                'user_management',
                'system_configuration',
                'data_backup',
                'security_audit',
                'performance_monitoring',
                'maintenance',
                'report_generation',
                'data_export',
                'data_import',
                'system_update'
            ]),
            'operation_name' => $this->faker->sentence(3, false),
            'performed_by_type' => AdminUser::class,
            'performed_by_id' => AdminUser::factory(),
            'target_type' => $this->faker->optional(0.7)->randomElement([
                'App\Models\User',
                'App\Models\Setting',
                'App\Models\Backup',
                'App\Models\Log',
                'App\Models\Report'
            ]),
            'target_id' => $this->faker->optional(0.7)->numberBetween(1, 1000),
            'status' => $this->faker->randomElement(['success', 'failed', 'partial']),
            'execution_time' => $this->faker->optional(0.8)->numberBetween(10, 5000),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'ip_address' => $this->faker->optional(0.9)->ipv4(),
            'session_id' => $this->faker->optional(0.8)->uuid(),
            'error_message' => $this->faker->optional(0.3)->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }

    /**
     * 성공 상태
     */
    public function success(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'success',
                'error_message' => null,
                'execution_time' => $this->faker->numberBetween(10, 1000),
                'severity' => $this->faker->randomElement(['low', 'medium']),
            ];
        });
    }

    /**
     * 실패 상태
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'error_message' => $this->faker->sentence(),
                'execution_time' => $this->faker->numberBetween(100, 5000),
                'severity' => $this->faker->randomElement(['high', 'critical']),
            ];
        });
    }

    /**
     * 부분 성공 상태
     */
    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'partial',
                'error_message' => $this->faker->sentence(),
                'execution_time' => $this->faker->numberBetween(500, 3000),
                'severity' => $this->faker->randomElement(['medium', 'high']),
            ];
        });
    }

    /**
     * 빠른 실행
     */
    public function fast(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'execution_time' => $this->faker->numberBetween(10, 100),
            ];
        });
    }

    /**
     * 느린 실행
     */
    public function slow(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'execution_time' => $this->faker->numberBetween(2000, 10000),
            ];
        });
    }

    /**
     * 높은 중요도
     */
    public function highSeverity(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'severity' => $this->faker->randomElement(['high', 'critical']),
            ];
        });
    }

    /**
     * 낮은 중요도
     */
    public function lowSeverity(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'severity' => $this->faker->randomElement(['low', 'medium']),
            ];
        });
    }

    /**
     * 사용자 관리 운영
     */
    public function userManagement(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'user_management',
                'operation_name' => $this->faker->randomElement([
                    '사용자 생성',
                    '사용자 수정',
                    '사용자 삭제',
                    '권한 변경',
                    '사용자 목록 조회'
                ]),
            ];
        });
    }

    /**
     * 시스템 설정 운영
     */
    public function systemConfiguration(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'system_configuration',
                'operation_name' => $this->faker->randomElement([
                    '설정 변경',
                    '환경 변수 수정',
                    '캐시 정리',
                    '로그 설정 변경',
                    '백업 설정 변경'
                ]),
            ];
        });
    }

    /**
     * 데이터 백업 운영
     */
    public function dataBackup(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'data_backup',
                'operation_name' => $this->faker->randomElement([
                    '전체 백업',
                    '증분 백업',
                    '백업 복원',
                    '백업 검증',
                    '백업 정리'
                ]),
            ];
        });
    }

    /**
     * 보안 감사 운영
     */
    public function securityAudit(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'security_audit',
                'operation_name' => $this->faker->randomElement([
                    '로그인 시도 감사',
                    '권한 변경 감사',
                    '데이터 접근 감사',
                    '보안 설정 감사',
                    '위험 행위 탐지'
                ]),
            ];
        });
    }

    /**
     * 성능 모니터링 운영
     */
    public function performanceMonitoring(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'operation_type' => 'performance_monitoring',
                'operation_name' => $this->faker->randomElement([
                    'CPU 사용률 모니터링',
                    '메모리 사용률 모니터링',
                    '디스크 사용률 모니터링',
                    '네트워크 성능 모니터링',
                    '응답 시간 모니터링'
                ]),
            ];
        });
    }

    /**
     * 최근 운영
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            ];
        });
    }

    /**
     * 오래된 운영
     */
    public function old(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-90 days', '-30 days'),
            ];
        });
    }
}
