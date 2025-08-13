<?php

namespace Jiny\Admin\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\App\Models\AdminUser;

/**
 * AdminSetupController 테스트
 * 
 * 관리자 최초 설정 컨트롤러의 기능을 테스트합니다.
 * 최초 관리자 설정과 슈퍼 관리자 계정 생성을 검증합니다.
 */
class AdminSetupTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function test_can_display_setup_page_when_no_admin_exists()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/setup');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::setup.setup2');
        $response->assertViewHas('passwordRules');
    }

    /** @test */
    public function test_redirects_to_login_when_admin_exists()
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

        $response = $this->get('/admin/setup');
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '관리자 로그인이 필요합니다.');
    }

    /** @test */
    public function test_redirects_to_login_when_admin_users_table_has_data()
    {
        // admin_users 테이블에 데이터가 있는 상태
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

        // 관리자 계정 생성
        DB::table('admin_users')->insert([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/admin/setup');
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '관리자 로그인이 필요합니다.');
    }

    /** @test */
    public function test_setup_page_accessible_without_authentication()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/setup');
        
        $response->assertStatus(200);
        // 인증이 필요하지 않아야 함
    }

    /** @test */
    public function test_setup_page_uses_correct_view()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/setup');
        
        $response->assertViewIs('jiny-admin::setup.setup2');
    }

    /** @test */
    public function test_setup_page_has_password_rules()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->get('/admin/setup');
        
        $response->assertViewHas('passwordRules');
    }

    /** @test */
    public function test_can_create_super_admin_with_valid_data()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'type' => 'super',
            'status' => 'active',
            'is_verified' => true,
        ]);

        // 비밀번호가 해시되어 저장되었는지 확인
        $admin = AdminUser::where('email', 'super@test.com')->first();
        $this->assertTrue(Hash::check('SuperPass123!', $admin->password));
    }

    /** @test */
    public function test_cannot_create_super_admin_with_invalid_name()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'A', // 최소 2자
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_cannot_create_super_admin_with_invalid_email()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'invalid-email', // 유효하지 않은 이메일
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'invalid-email',
        ]);
    }

    /** @test */
    public function test_cannot_create_super_admin_with_short_password()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => '123', // 최소 8자
            'password_confirmation' => '123',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_cannot_create_super_admin_with_mismatched_password()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'DifferentPass123!', // 비밀번호 불일치
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_cannot_create_super_admin_with_duplicate_email()
    {
        // admin_users 테이블이 비어있는 상태
        
        // 첫 번째 슈퍼 관리자 생성
        $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin 1',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);

        // 동일한 이메일로 두 번째 슈퍼 관리자 생성 시도
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin 2',
            'email' => 'super@test.com', // 중복 이메일
            'password' => 'SuperPass456!',
            'password_confirmation' => 'SuperPass456!',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);

        // 데이터베이스에 두 번째 관리자 계정이 생성되지 않았는지 확인
        $adminCount = AdminUser::where('email', 'super@test.com')->count();
        $this->assertEquals(1, $adminCount);
    }

    /** @test */
    public function test_super_admin_creation_sets_correct_defaults()
    {
        // admin_users 테이블이 비어있는 상태
        
        $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);

        $admin = AdminUser::where('email', 'super@test.com')->first();
        
        $this->assertEquals('super', $admin->type);
        $this->assertEquals('active', $admin->status);
        $this->assertTrue($admin->is_verified);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertNotNull($admin->created_at);
        $this->assertNotNull($admin->updated_at);
    }

    /** @test */
    public function test_super_admin_creation_handles_admin_levels_table()
    {
        // admin_users 테이블이 비어있는 상태
        
        // admin_levels 테이블 생성 및 super 등급 추가
        if (!Schema::hasTable('admin_levels')) {
            Schema::create('admin_levels', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('badge_color')->nullable();
                $table->boolean('can_create')->default(false);
                $table->boolean('can_read')->default(false);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->timestamps();
            });
        }

        DB::table('admin_levels')->insert([
            'name' => 'Super Admin',
            'code' => 'super',
            'badge_color' => '#ff0000',
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);

        $admin = AdminUser::where('email', 'super@test.com')->first();
        $superLevel = DB::table('admin_levels')->where('code', 'super')->first();
        
        $this->assertEquals($superLevel->id, $admin->admin_level_id);
    }

    /** @test */
    public function test_super_admin_creation_works_without_admin_levels_table()
    {
        // admin_users 테이블이 비어있는 상태
        
        // admin_levels 테이블이 존재하지 않는 상태
        
        $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);

        $admin = AdminUser::where('email', 'super@test.com')->first();
        
        // admin_level_id가 null이어야 함
        $this->assertNull($admin->admin_level_id);
    }

    /** @test */
    public function test_super_admin_creation_validates_password_rules()
    {
        // admin_users 테이블이 비어있는 상태
        
        // 비밀번호 규칙을 설정 (config 파일에서 읽어옴)
        config([
            'admin.settings.auth.password.min_length' => 10,
            'admin.settings.auth.password.require_special' => true,
            'admin.settings.auth.password.require_number' => true,
            'admin.settings.auth.password.require_uppercase' => true,
        ]);

        // 규칙을 만족하지 않는 비밀번호로 시도
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'weak', // 규칙을 만족하지 않음
            'password_confirmation' => 'weak',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_with_complex_password()
    {
        // admin_users 테이블이 비어있는 상태
        
        // 복잡한 비밀번호 규칙 설정
        config([
            'admin.settings.auth.password.min_length' => 12,
            'admin.settings.auth.password.require_special' => true,
            'admin.settings.auth.password.require_number' => true,
            'admin.settings.auth.password.require_uppercase' => true,
        ]);

        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'ComplexPass123!@#', // 모든 규칙 만족
            'password_confirmation' => 'ComplexPass123!@#',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_preserves_old_input_on_validation_failure()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);
        
        $response->assertRedirect();
        
        // 이전 입력 값이 유지되었는지 확인
        $response->assertSessionHasInput('name', 'Super Admin');
        $response->assertSessionHasInput('email', 'super@test.com');
    }

    /** @test */
    public function test_super_admin_creation_handles_empty_inputs()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', []);
    }

    /** @test */
    public function test_super_admin_creation_handles_missing_inputs()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', []);
        
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password']);

        // 데이터베이스에 관리자 계정이 생성되지 않았는지 확인
        $this->assertDatabaseMissing('admin_users', []);
    }

    /** @test */
    public function test_super_admin_creation_handles_special_characters_in_name()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin @#$%^&*()',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'name' => 'Super Admin @#$%^&*()',
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_handles_long_name()
    {
        // admin_users 테이블이 비어있는 상태
        
        $longName = str_repeat('A', 100); // 100자 이름
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => $longName,
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'name' => $longName,
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_handles_unicode_characters()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => '슈퍼 관리자 🚀',
            'email' => 'super@test.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'name' => '슈퍼 관리자 🚀',
            'email' => 'super@test.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_handles_email_with_subdomain()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super@subdomain.example.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'super@subdomain.example.com',
        ]);
    }

    /** @test */
    public function test_super_admin_creation_handles_email_with_plus_sign()
    {
        // admin_users 테이블이 비어있는 상태
        
        $response = $this->post('/admin/setup/superadmin', [
            'name' => 'Super Admin',
            'email' => 'super+test@example.com',
            'password' => 'SuperPass123!',
            'password_confirmation' => 'SuperPass123!',
        ]);
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('message', '최초 슈퍼관리자 계정이 생성되었습니다.');

        // 데이터베이스에 관리자 계정이 생성되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'super+test@example.com',
        ]);
    }
}
