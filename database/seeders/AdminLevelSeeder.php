<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\App\Models\AdminLevel;

class AdminLevelSeeder extends Seeder
{
    /**
     * 관리자 등급 시드
     * 
     * 이 시더는 관리자 등급을 초기화합니다.
     * 마이그레이션에서 직접 호출하지 않고 별도로 실행해야 합니다.
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

        // 명령어 객체가 있을 때만 정보 출력
        if ($this->command) {
            $this->command->info('관리자 등급이 성공적으로 생성되었습니다.');
            $this->command->info('생성된 등급: Super, Admin, Staff, Viewer');
        }
    }
} 