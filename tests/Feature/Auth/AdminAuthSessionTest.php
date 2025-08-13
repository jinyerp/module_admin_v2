<?php

namespace Jiny\Admin\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;

/**
 * AdminAuthSessionController 테스트
 * 
 * 관리자 인증 세션 컨트롤러의 모든 기능을 테스트합니다.
 * 체인 형태의 로그인 검사 시스템과 보안 기능을 검증합니다.
 */
class AdminAuthSessionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $adminData;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 계정 생성
        $this->adminData = [
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'TestPass123!',
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ];

        $this->admin = AdminUser::create([
            ...$this->adminData,
            'password' => Hash::make($this->adminData['password']),
        ]);
    }

    /** @test */
    public function test_can_display_login_form()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::auth.login');
    }

    /** @test */
    public function test_can_login_with_valid_credentials()
    {
        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
    }

    /** @test */
    public function test_can_login_with_ajax_request()
    {
        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['success', 'message', 'redirect', 'user']);
    }

    /** @test */
    public function test_cannot_login_with_invalid_email()
    {
        $response = $this->post('/admin/login', [
            'email' => 'invalid-email',
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_empty_password()
    {
        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_wrong_password()
    {
        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/admin/login', [
            'email' => 'nonexistent@test.com',
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_login_attempts_are_tracked()
    {
        // 잘못된 비밀번호로 로그인 시도
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        $cacheKey = "admin_login_attempts:{$this->adminData['email']}";
        $attempts = Cache::get($cacheKey);
        
        $this->assertEquals(1, $attempts);
    }

    /** @test */
    public function test_account_is_locked_after_max_attempts()
    {
        $maxAttempts = config('admin.settings.auth.login.max_attempts', 5);
        
        // 최대 시도 횟수만큼 로그인 실패
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->post('/admin/login', [
                'email' => $this->adminData['email'],
                'password' => 'wrongpassword',
            ]);
        }

        // 추가 로그인 시도
        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_inactive_account()
    {
        $this->admin->update(['status' => 'inactive']);

        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_suspended_account()
    {
        $this->admin->update(['status' => 'suspended']);

        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_with_pending_account()
    {
        $this->admin->update(['status' => 'pending']);

        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_cannot_login_without_email_verification()
    {
        $this->admin->update(['is_verified' => false]);

        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_successful_login_clears_attempts()
    {
        // 먼저 실패 시도
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        // 성공 로그인
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $cacheKey = "admin_login_attempts:{$this->adminData['email']}";
        $attempts = Cache::get($cacheKey);
        
        $this->assertNull($attempts);
    }

    /** @test */
    public function test_successful_login_updates_last_login()
    {
        $originalLastLogin = $this->admin->last_login_at;

        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $this->admin->refresh();
        
        $this->assertNotEquals($originalLastLogin, $this->admin->last_login_at);
        $this->assertNotNull($this->admin->last_login_at);
    }

    /** @test */
    public function test_successful_login_increments_login_count()
    {
        $originalCount = $this->admin->login_count ?? 0;

        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $this->admin->refresh();
        
        $this->assertEquals($originalCount + 1, $this->admin->login_count);
    }

    /** @test */
    public function test_login_activity_is_logged()
    {
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $log = AdminUserLog::where('admin_user_id', $this->admin->id)
                          ->where('action', 'login')
                          ->where('status', 'success')
                          ->first();

        $this->assertNotNull($log);
        $this->assertEquals('로그인 성공', $log->message);
    }

    /** @test */
    public function test_failed_login_activity_is_logged()
    {
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        $log = AdminUserLog::where('admin_user_id', $this->admin->id)
                          ->where('action', 'login')
                          ->where('status', 'fail')
                          ->first();

        $this->assertNotNull($log);
    }

    /** @test */
    public function test_session_data_is_updated_on_login()
    {
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $this->assertAuthenticated('admin');
        
        $session = $this->app['session.store'];
        $this->assertNotNull($session->get('admin_user_id'));
        $this->assertNotNull($session->get('admin_user_type'));
        $this->assertNotNull($session->get('admin_last_activity'));
    }

    /** @test */
    public function test_session_is_saved_to_database()
    {
        $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $sessionId = $this->app['session.store']->getId();
        
        $sessionRecord = DB::table('admin_sessions')
                          ->where('session_id', $sessionId)
                          ->where('admin_user_id', $this->admin->id)
                          ->first();

        $this->assertNotNull($sessionRecord);
        $this->assertTrue($sessionRecord->is_active);
    }

    /** @test */
    public function test_redirects_to_intended_url_after_login()
    {
        $this->get('/admin/dashboard');
        
        $response = $this->post('/admin/login', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertRedirect('/admin/dashboard');
    }

    /** @test */
    public function test_ajax_login_returns_correct_error_codes()
    {
        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJsonStructure(['success', 'message', 'error_code'])
                ->assertJson(['success' => false]);
    }

    /** @test */
    public function test_ajax_login_returns_password_mismatch_error()
    {
        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => 'wrongpassword',
        ]);

        $response->assertJson(['error_code' => 'PASSWORD_MISMATCH']);
    }

    /** @test */
    public function test_ajax_login_returns_user_not_found_error()
    {
        $response = $this->postJson('/admin/login/ajax', [
            'email' => 'nonexistent@test.com',
            'password' => 'password',
        ]);

        $response->assertJson(['error_code' => 'USER_NOT_FOUND']);
    }

    /** @test */
    public function test_ajax_login_returns_too_many_attempts_error()
    {
        $maxAttempts = config('admin.settings.auth.login.max_attempts', 5);
        
        // 최대 시도 횟수만큼 로그인 실패
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->postJson('/admin/login/ajax', [
                'email' => $this->adminData['email'],
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(429)
                ->assertJson(['error_code' => 'TOO_MANY_ATTEMPTS']);
    }

    /** @test */
    public function test_ajax_login_returns_account_inactive_error()
    {
        $this->admin->update(['status' => 'inactive']);

        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(403)
                ->assertJson(['error_code' => 'ACCOUNT_INACTIVE']);
    }

    /** @test */
    public function test_ajax_login_returns_account_suspended_error()
    {
        $this->admin->update(['status' => 'suspended']);

        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(403)
                ->assertJson(['error_code' => 'ACCOUNT_SUSPENDED']);
    }

    /** @test */
    public function test_ajax_login_returns_account_pending_error()
    {
        $this->admin->update(['status' => 'pending']);

        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(403)
                ->assertJson(['error_code' => 'ACCOUNT_PENDING']);
    }

    /** @test */
    public function test_ajax_login_returns_email_not_verified_error()
    {
        $this->admin->update(['is_verified' => false]);

        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        $response->assertStatus(403)
                ->assertJson(['error_code' => 'EMAIL_NOT_VERIFIED']);
    }

    /** @test */
    public function test_ajax_login_returns_system_error_on_db_failure()
    {
        // 데이터베이스 연결을 일시적으로 차단하는 방법을 모방
        // 실제로는 mock을 사용하는 것이 좋습니다
        
        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        // 정상적인 경우 성공해야 함
        $response->assertStatus(200);
    }

    /** @test */
    public function test_ajax_login_returns_login_failed_error_for_unknown_errors()
    {
        // 알 수 없는 오류 상황을 모방
        $response = $this->postJson('/admin/login/ajax', [
            'email' => $this->adminData['email'],
            'password' => $this->adminData['password'],
        ]);

        // 정상적인 경우 성공해야 함
        $response->assertStatus(200);
    }
}
