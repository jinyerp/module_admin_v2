<?php

namespace Jiny\Admin\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminLoginFormController 테스트
 * 
 * 관리자 로그인 폼 컨트롤러의 기능을 테스트합니다.
 * 최초 관리자 설정이 필요한 경우를 처리하는 로직을 검증합니다.
 */
class AdminLoginFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function test_can_display_login_form_when_admin_exists()
    {
        // 관리자 계정 생성
        AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        $response->assertViewHas('register_enabled', false);
    }

    /** @test */
    public function test_redirects_to_setup_when_no_admin_exists()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/login');
        
        $response->assertRedirect('/admin/setup');
    }

    /** @test */
    public function test_redirects_to_setup_when_admin_users_table_empty()
    {
        // admin_users 테이블은 존재하지만 데이터가 없는 상태
        if (!Schema::hasTable('admin_users')) {
            Schema::create('admin_users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('type');
                $table->string('status');
                $table->boolean('is_verified');
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamps();
            });
        }

        $response = $this->get('/admin/login');
        
        $response->assertRedirect('/admin/setup');
    }

    /** @test */
    public function test_redirects_to_setup_when_admin_users_table_does_not_exist()
    {
        // admin_users 테이블이 존재하지 않는 상태
        if (Schema::hasTable('admin_users')) {
            Schema::dropIfExists('admin_users');
        }

        $response = $this->get('/admin/login');
        
        $response->assertRedirect('/admin/setup');
    }

    /** @test */
    public function test_login_form_view_has_correct_data()
    {
        // 관리자 계정 생성
        AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        $response->assertViewHas('register_enabled', false);
    }

    /** @test */
    public function test_setup_redirect_uses_correct_route()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/login');
        
        $response->assertRedirect(route('admin.setup'));
    }

    /** @test */
    public function test_login_form_accessible_without_authentication()
    {
        // 관리자 계정 생성
        AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        // 인증이 필요하지 않아야 함
    }

    /** @test */
    public function test_setup_redirect_accessible_without_authentication()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/login');
        
        $response->assertRedirect('/admin/setup');
        // 인증이 필요하지 않아야 함
    }

    /** @test */
    public function test_login_form_uses_correct_view_path()
    {
        // 관리자 계정 생성
        AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertViewIs('jiny-admin::auth.login');
    }

    /** @test */
    public function test_setup_redirect_uses_correct_view_path()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/login');
        
        $response->assertRedirect('/admin/setup');
        // setup 뷰 경로 확인
    }

    /** @test */
    public function test_login_form_handles_multiple_admin_users()
    {
        // 여러 관리자 계정 생성
        AdminUser::create([
            'name' => 'Admin 1',
            'email' => 'admin1@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        AdminUser::create([
            'name' => 'Admin 2',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 관리자가 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_inactive_admin_users()
    {
        // 비활성화된 관리자 계정만 생성
        AdminUser::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'inactive',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 비활성화된 관리자도 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_pending_admin_users()
    {
        // 승인 대기 중인 관리자 계정만 생성
        AdminUser::create([
            'name' => 'Pending Admin',
            'email' => 'pending@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'pending',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 승인 대기 중인 관리자도 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_suspended_admin_users()
    {
        // 정지된 관리자 계정만 생성
        AdminUser::create([
            'name' => 'Suspended Admin',
            'email' => 'suspended@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'suspended',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 정지된 관리자도 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_unverified_admin_users()
    {
        // 이메일 인증이 되지 않은 관리자 계정만 생성
        AdminUser::create([
            'name' => 'Unverified Admin',
            'email' => 'unverified@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => false,
            'email_verified_at' => null,
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 이메일 인증이 되지 않은 관리자도 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_mixed_admin_user_statuses()
    {
        // 다양한 상태의 관리자 계정 생성
        AdminUser::create([
            'name' => 'Active Admin',
            'email' => 'active@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        AdminUser::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'inactive',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        AdminUser::create([
            'name' => 'Pending Admin',
            'email' => 'pending@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'pending',
            'is_verified' => false,
            'email_verified_at' => null,
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 어떤 상태의 관리자라도 있으면 setup으로 리다이렉트되지 않아야 함
    }

    /** @test */
    public function test_login_form_handles_different_admin_types()
    {
        // 다양한 타입의 관리자 계정 생성
        AdminUser::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'type' => 'super',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        AdminUser::create([
            'name' => 'Regular Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        AdminUser::create([
            'name' => 'Staff Admin',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'type' => 'staff',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
        // 어떤 타입의 관리자라도 있으면 setup으로 리다이렉트되지 않아야 함
    }
}
