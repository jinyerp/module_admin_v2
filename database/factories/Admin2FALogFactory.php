<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\App\Models\Admin2FALog;
use Jiny\Admin\App\Models\AdminUser;

/**
 * Admin2FALog 모델 팩토리
 */
class Admin2FALogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin2FALog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'admin_user_id' => AdminUser::factory(),
            'action' => $this->faker->randomElement(['enable', 'disable', 'verify', 'setup', 'reset']),
            'status' => $this->faker->randomElement(['success', 'fail']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'message' => $this->faker->sentence(),
        ];
    }

    /**
     * 성공한 로그 상태
     */
    public function success()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'success',
                'message' => '2FA 인증이 성공적으로 완료되었습니다.',
            ];
        });
    }

    /**
     * 실패한 로그 상태
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'fail',
                'message' => '2FA 인증에 실패했습니다.',
            ];
        });
    }

    /**
     * 특정 액션의 로그
     */
    public function action($action)
    {
        return $this->state(function (array $attributes) use ($action) {
            return [
                'action' => $action,
            ];
        });
    }

    /**
     * 특정 IP의 로그
     */
    public function ipAddress($ipAddress)
    {
        return $this->state(function (array $attributes) use ($ipAddress) {
            return [
                'ip_address' => $ipAddress,
            ];
        });
    }
}
