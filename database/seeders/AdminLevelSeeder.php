<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Models\AdminLevel;

class AdminLevelSeeder extends Seeder
{
    /**
     * 관리자 등급 시드
     */
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Super',
                'code' => 'super',
                'badge_color' => 'red',
                'can_list' => true,
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'code' => 'admin',
                'badge_color' => 'blue',
                'can_list' => true,
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff',
                'code' => 'staff',
                'badge_color' => 'green',
                'can_list' => true,
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Viewer',
                'code' => 'viewer',
                'badge_color' => 'gray',
                'can_list' => true,
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 기존 등급이 있으면 삭제
        DB::table('admin_levels')->truncate();

        // 새 등급 삽입
        DB::table('admin_levels')->insert($levels);

        $this->command->info('관리자 등급이 성공적으로 생성되었습니다.');
        $this->command->info('생성된 등급: Super, Admin, Staff, Viewer');
    }
} 