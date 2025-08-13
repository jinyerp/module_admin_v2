<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Jiny\Admin\App\Models\AdminSession;
use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminSession 팩토리
 *
 * AdminSession 모델을 위한 테스트 데이터 팩토리
 * 다양한 시나리오의 세션 데이터를 생성할 수 있습니다.
 *
 * @package Jiny\Admin\Database\Factories
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSession.md
 */
class AdminSessionFactory extends Factory
{
    /**
     * 모델 클래스명
     */
    protected $model = AdminSession::class;

    /**
     * 기본 상태 정의
     */
    public function definition()
    {
        return [
            'session_id' => Str::random(40),
            'admin_user_id' => AdminUser::factory(),
            'admin_name' => $this->faker->name(),
            'admin_email' => $this->faker->email(),
            'admin_type' => $this->faker->randomElement(['super', 'admin', 'staff']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'login_location' => $this->faker->city() . ', ' . $this->faker->country(),
            'device' => $this->faker->randomElement(['Desktop', 'Mobile', 'Tablet']),
            'login_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'last_activity' => $this->faker->dateTimeBetween('-30 minutes', 'now'),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * 활성 세션 상태
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_activity' => $this->faker->dateTimeBetween('-5 minutes', 'now'),
            ];
        });
    }

    /**
     * 비활성 세션 상태
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_activity' => $this->faker->dateTimeBetween('-2 hours', '-1 hour'),
            ];
        });
    }

    /**
     * 만료된 세션 상태
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'last_activity' => $this->faker->dateTimeBetween('-2 hours', '-1 hour'),
            ];
        });
    }

    /**
     * 특정 IP 주소의 세션
     */
    public function withIpAddress(string $ipAddress)
    {
        return $this->state(function (array $attributes) use ($ipAddress) {
            return [
                'ip_address' => $ipAddress,
            ];
        });
    }

    /**
     * 특정 사용자 에이전트의 세션
     */
    public function withUserAgent(string $userAgent)
    {
        return $this->state(function (array $attributes) use ($userAgent) {
            return [
                'user_agent' => $userAgent,
            ];
        });
    }

    /**
     * 특정 관리자 사용자의 세션
     */
    public function forAdminUser(AdminUser $adminUser)
    {
        return $this->state(function (array $attributes) use ($adminUser) {
            return [
                'admin_user_id' => $adminUser->id,
            ];
        });
    }

    /**
     * 오래된 세션 (1주일 이상)
     */
    public function old()
    {
        return $this->state(function (array $attributes) {
            return [
                'login_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
                'last_activity' => $this->faker->dateTimeBetween('-1 week', '-6 days'),
            ];
        });
    }

    /**
     * 최근 세션 (1시간 이내)
     */
    public function recent()
    {
        return $this->state(function (array $attributes) {
            return [
                'login_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
                'last_activity' => $this->faker->dateTimeBetween('-10 minutes', 'now'),
            ];
        });
    }

    /**
     * 모바일 디바이스 세션
     */
    public function mobile()
    {
        return $this->state(function (array $attributes) {
            $mobileUserAgents = [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36',
                'Mozilla/5.0 (iPad; CPU OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Mobile/15E148 Safari/604.1',
            ];
            
            return [
                'user_agent' => $this->faker->randomElement($mobileUserAgents),
            ];
        });
    }

    /**
     * 데스크톱 디바이스 세션
     */
    public function desktop()
    {
        return $this->state(function (array $attributes) {
            $desktopUserAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ];
            
            return [
                'user_agent' => $this->faker->randomElement($desktopUserAgents),
            ];
        });
    }

    /**
     * 의심스러운 세션 (IP 주소가 변경된 경우)
     */
    public function suspicious()
    {
        return $this->state(function (array $attributes) {
            return [
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'last_activity' => $this->faker->dateTimeBetween('-5 minutes', 'now'),
            ];
        });
    }

    /**
     * 다중 세션 (동일 사용자의 여러 세션)
     */
    public function multipleForUser(AdminUser $adminUser, int $count = 3)
    {
        $sessions = collect();
        
        for ($i = 0; $i < $count; $i++) {
            $sessions->push($this->forAdminUser($adminUser)->make());
        }
        
        return $sessions;
    }
}
