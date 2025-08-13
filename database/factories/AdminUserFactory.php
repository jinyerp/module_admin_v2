<?php

namespace Jiny\Admin\Database\Factories;

use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * AdminUser Factory
 * 
 * 테스트 및 시드 데이터 생성을 위한 팩토리 클래스
 */
class AdminUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'type' => $this->faker->randomElement(['super', 'admin', 'staff']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'is_verified' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'is_super_admin' => false,
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'login_count' => $this->faker->numberBetween(0, 100),
            'phone' => $this->faker->optional()->phoneNumber(),
            'memo' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * 슈퍼 관리자 상태
     */
    public function super(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'super',
            'status' => 'active',
            'is_verified' => true,
            'is_active' => true,
            'is_super_admin' => true,
        ]);
    }

    /**
     * 일반 관리자 상태
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'is_active' => true,
            'is_super_admin' => false,
        ]);
    }

    /**
     * 스태프 상태
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'staff',
            'status' => 'active',
            'is_verified' => true,
            'is_active' => true,
            'is_super_admin' => false,
        ]);
    }

    /**
     * 활성 상태
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    /**
     * 비활성 상태
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    /**
     * 정지 상태
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'is_active' => false,
        ]);
    }

    /**
     * 이메일 미인증 상태
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    /**
     * 최근 로그인 상태
     */
    public function recentlyLoggedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'login_count' => $this->faker->numberBetween(1, 50),
        ]);
    }

    /**
     * 장기 미로그인 상태
     */
    public function longTimeNoLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
            'login_count' => $this->faker->numberBetween(0, 10),
        ]);
    }
}
