<?php

namespace Jiny\Admin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jiny\Admin\App\Models\AdminLevel;

/**
 * AdminLevel 모델 팩토리
 * 
 * 테스트 및 시드 데이터 생성을 위한 팩토리 클래스
 */
class AdminLevelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminLevel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'code' => $this->faker->unique()->slug(2),
            'badge_color' => $this->faker->hexColor(),
            'can_create' => $this->faker->boolean(70),
            'can_read' => $this->faker->boolean(90),
            'can_update' => $this->faker->boolean(60),
            'can_delete' => $this->faker->boolean(40),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Super 관리자 등급 상태
     */
    public function super()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Super 관리자',
                'code' => 'super',
                'badge_color' => '#dc3545',
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'sort_order' => 1,
            ];
        });
    }

    /**
     * 일반 관리자 등급 상태
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => '일반 관리자',
                'code' => 'admin',
                'badge_color' => '#007bff',
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => false,
                'sort_order' => 2,
            ];
        });
    }

    /**
     * 일반 직원 등급 상태
     */
    public function staff()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => '일반 직원',
                'code' => 'staff',
                'badge_color' => '#28a745',
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'sort_order' => 3,
            ];
        });
    }

    /**
     * 읽기 전용 등급 상태
     */
    public function readOnly()
    {
        return $this->state(function (array $attributes) {
            return [
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
            ];
        });
    }

    /**
     * 편집 가능 등급 상태
     */
    public function editable()
    {
        return $this->state(function (array $attributes) {
            return [
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => false,
            ];
        });
    }

    /**
     * 전체 권한 등급 상태
     */
    public function fullAccess()
    {
        return $this->state(function (array $attributes) {
            return [
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
            ];
        });
    }

    /**
     * 최소 권한 등급 상태
     */
    public function minimal()
    {
        return $this->state(function (array $attributes) {
            return [
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'sort_order' => 999,
            ];
        });
    }

    /**
     * 높은 우선순위 등급 상태
     */
    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'sort_order' => $this->faker->numberBetween(1, 10),
            ];
        });
    }

    /**
     * 낮은 우선순위 등급 상태
     */
    public function lowPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'sort_order' => $this->faker->numberBetween(90, 100),
            ];
        });
    }

    /**
     * 특정 색상 등급 상태
     */
    public function withColor($color)
    {
        return $this->state(function (array $attributes) use ($color) {
            return [
                'badge_color' => $color,
            ];
        });
    }

    /**
     * 특정 코드 등급 상태
     */
    public function withCode($code)
    {
        return $this->state(function (array $attributes) use ($code) {
            return [
                'code' => $code,
            ];
        });
    }
}
