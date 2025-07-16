<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\AdminPermission;

class AdminPermissionSeeder extends Seeder
{
    /**
     * 기본 권한 데이터 생성
     */
    public function run(): void
    {
        $permissions = [
            // 사용자 관리 권한
            ['name' => 'user.view', 'display_name' => '사용자 조회', 'module' => 'user', 'description' => '사용자 목록 및 상세 정보 조회 권한'],
            ['name' => 'user.create', 'display_name' => '사용자 생성', 'module' => 'user', 'description' => '새로운 사용자 계정 생성 권한'],
            ['name' => 'user.update', 'display_name' => '사용자 수정', 'module' => 'user', 'description' => '기존 사용자 정보 수정 권한'],
            ['name' => 'user.delete', 'display_name' => '사용자 삭제', 'module' => 'user', 'description' => '사용자 계정 삭제 권한'],
            ['name' => 'user.approve', 'display_name' => '사용자 승인', 'module' => 'user', 'description' => '사용자 가입 승인/거부 권한'],
            ['name' => 'user.export', 'display_name' => '사용자 내보내기', 'module' => 'user', 'description' => '사용자 데이터 내보내기 권한'],

            // 국가 관리 권한
            ['name' => 'country.view', 'display_name' => '국가 조회', 'module' => 'country', 'description' => '국가 목록 및 상세 정보 조회 권한'],
            ['name' => 'country.create', 'display_name' => '국가 생성', 'module' => 'country', 'description' => '새로운 국가 정보 생성 권한'],
            ['name' => 'country.update', 'display_name' => '국가 수정', 'module' => 'country', 'description' => '기존 국가 정보 수정 권한'],
            ['name' => 'country.delete', 'display_name' => '국가 삭제', 'module' => 'country', 'description' => '국가 정보 삭제 권한'],

            // 관리자 관리 권한
            ['name' => 'admin.view', 'display_name' => '관리자 조회', 'module' => 'admin', 'description' => '관리자 목록 및 상세 정보 조회 권한'],
            ['name' => 'admin.create', 'display_name' => '관리자 생성', 'module' => 'admin', 'description' => '새로운 관리자 계정 생성 권한'],
            ['name' => 'admin.update', 'display_name' => '관리자 수정', 'module' => 'admin', 'description' => '기존 관리자 정보 수정 권한'],
            ['name' => 'admin.delete', 'display_name' => '관리자 삭제', 'module' => 'admin', 'description' => '관리자 계정 삭제 권한'],
            ['name' => 'admin.permission', 'display_name' => '권한 관리', 'module' => 'admin', 'description' => '관리자 권한 할당/해제 권한'],

            // 시스템 관리 권한
            ['name' => 'system.view', 'display_name' => '시스템 조회', 'module' => 'system', 'description' => '시스템 설정 및 상태 조회 권한'],
            ['name' => 'system.update', 'display_name' => '시스템 수정', 'module' => 'system', 'description' => '시스템 설정 수정 권한'],
            ['name' => 'system.backup', 'display_name' => '백업 관리', 'module' => 'system', 'description' => '시스템 백업 생성 및 관리 권한'],
            ['name' => 'system.maintenance', 'display_name' => '유지보수 관리', 'module' => 'system', 'description' => '시스템 유지보수 관리 권한'],

            // 인증 관리 권한
            ['name' => 'auth.view', 'display_name' => '인증 조회', 'module' => 'auth', 'description' => '인증 관련 정보 조회 권한'],
            ['name' => 'auth.manage', 'display_name' => '인증 관리', 'module' => 'auth', 'description' => '인증 설정 및 정책 관리 권한'],
            ['name' => 'auth.approve', 'display_name' => '인증 승인', 'module' => 'auth', 'description' => '사용자 인증 승인/거부 권한'],

            // 로그 관리 권한
            ['name' => 'log.view', 'display_name' => '로그 조회', 'module' => 'log', 'description' => '시스템 로그 조회 권한'],
            ['name' => 'log.export', 'display_name' => '로그 내보내기', 'module' => 'log', 'description' => '로그 데이터 내보내기 권한'],
            ['name' => 'log.delete', 'display_name' => '로그 삭제', 'module' => 'log', 'description' => '로그 데이터 삭제 권한'],
        ];

        foreach ($permissions as $index => $permission) {
            AdminPermission::create([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'description' => $permission['description'],
                'module' => $permission['module'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }

        $this->command->info('기본 권한 데이터가 생성되었습니다.');
    }
}
