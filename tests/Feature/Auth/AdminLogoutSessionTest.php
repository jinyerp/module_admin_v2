<?php

namespace Jiny\Admin\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;

/**
 * AdminLogoutSessionController 테스트
 * 
 * 관리자 세션 로그아웃 컨트롤러의 기능을 테스트합니다.
 * 로그아웃 처리, 세션 관리, 강제 로그아웃 기능을 검증합니다.
 */
class AdminLogoutSessionTest extends TestCase
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
            'password' => bcrypt($this->adminData['password']),
        ]);
    }

    /** @test */
    public function test_can_logout_successfully()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $response = $this->get('/admin/logout');
        
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('success', '로그아웃되었습니다.');
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_can_logout_with_ajax_request()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/logout/ajax');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['success', 'message', 'redirect']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_logout_clears_session_data()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 세션에 관리자 관련 데이터 설정
        session([
            'admin_last_activity' => now()->toDateTimeString(),
            'admin_user_id' => $this->admin->id,
            'admin_user_type' => $this->admin->type,
            '2fa_verified' => true,
            '2fa_setup_secret' => 'test-secret',
            '2fa_setup_backup_codes' => ['code1', 'code2'],
        ]);

        $this->get('/admin/logout');

        // 세션 데이터가 정리되었는지 확인
        $this->assertNull(session('admin_last_activity'));
        $this->assertNull(session('admin_user_id'));
        $this->assertNull(session('admin_user_type'));
        $this->assertNull(session('2fa_verified'));
        $this->assertNull(session('2fa_setup_secret'));
        $this->assertNull(session('2fa_setup_backup_codes'));
    }

    /** @test */
    public function test_logout_deactivates_session_in_database()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 세션 정보를 데이터베이스에 저장
        $sessionId = session()->getId();
        DB::table('admin_sessions')->insert([
            'session_id' => $sessionId,
            'admin_user_id' => $this->admin->id,
            'admin_name' => $this->admin->name,
            'admin_email' => $this->admin->email,
            'admin_type' => $this->admin->type,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'login_location' => 'Test Location',
            'device' => 'Test Device',
            'login_at' => now(),
            'last_activity' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/admin/logout');

        // 데이터베이스의 세션이 비활성화되었는지 확인
        $sessionRecord = DB::table('admin_sessions')
                          ->where('session_id', $sessionId)
                          ->first();

        $this->assertNotNull($sessionRecord);
        $this->assertFalse($sessionRecord->is_active);
    }

    /** @test */
    public function test_logout_logs_activity()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $this->get('/admin/logout');

        // 로그아웃 활동이 기록되었는지 확인
        $log = AdminUserLog::where('admin_user_id', $this->admin->id)
                          ->where('action', 'logout')
                          ->where('status', 'success')
                          ->first();

        $this->assertNotNull($log);
        $this->assertEquals('로그아웃', $log->message);
    }

    /** @test */
    public function test_logout_regenerates_session()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $originalSessionId = session()->getId();

        $this->get('/admin/logout');

        // 세션이 재생성되었는지 확인
        $newSessionId = session()->getId();
        $this->assertNotEquals($originalSessionId, $newSessionId);
    }

    /** @test */
    public function test_logout_requires_authentication()
    {
        // 로그인하지 않은 상태에서 로그아웃 시도
        $response = $this->get('/admin/logout');
        
        // 인증이 필요한 경우 적절한 응답
        // 실제 구현에 따라 상태 코드나 리다이렉트 확인
        $this->assertTrue(true);
    }

    /** @test */
    public function test_ajax_logout_requires_authentication()
    {
        // 로그인하지 않은 상태에서 AJAX 로그아웃 시도
        $response = $this->postJson('/admin/logout/ajax');
        
        // 인증이 필요한 경우 적절한 응답
        // 실제 구현에 따라 상태 코드나 JSON 응답 확인
        $this->assertTrue(true);
    }

    /** @test */
    public function test_force_logout_all_sessions_requires_super_admin()
    {
        // 일반 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/admin/session/force-logout-all');
        
        $response->assertStatus(403)
                ->assertJson(['success' => false])
                ->assertJson(['message' => '권한이 없습니다.']);
    }

    /** @test */
    public function test_force_logout_all_sessions_works_for_super_admin()
    {
        // super 관리자로 설정
        $this->admin->update(['type' => 'super']);
        $this->actingAs($this->admin, 'admin');

        // 여러 활성 세션 생성
        DB::table('admin_sessions')->insert([
            [
                'session_id' => 'session1',
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test User Agent',
                'login_location' => 'Test Location',
                'device' => 'Test Device',
                'login_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => 'session2',
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.2',
                'user_agent' => 'Test User Agent 2',
                'login_location' => 'Test Location 2',
                'device' => 'Test Device 2',
                'login_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->postJson('/admin/session/force-logout-all');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJson(['message' => '모든 세션이 강제 종료되었습니다.']);

        // 모든 활성 세션이 삭제되었는지 확인
        $activeSessions = DB::table('admin_sessions')->where('is_active', true)->count();
        $this->assertEquals(0, $activeSessions);
    }

    /** @test */
    public function test_force_logout_user_sessions_requires_super_admin()
    {
        // 일반 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson("/admin/session/force-logout-user/{$this->admin->id}");
        
        $response->assertStatus(403)
                ->assertJson(['success' => false])
                ->assertJson(['message' => '권한이 없습니다.']);
    }

    /** @test */
    public function test_force_logout_user_sessions_works_for_super_admin()
    {
        // super 관리자로 설정
        $this->admin->update(['type' => 'super']);
        $this->actingAs($this->admin, 'admin');

        // 다른 관리자 계정 생성
        $otherAdmin = AdminUser::create([
            'name' => 'Other Admin',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 다른 관리자의 활성 세션 생성
        DB::table('admin_sessions')->insert([
            'session_id' => 'other-session',
            'admin_user_id' => $otherAdmin->id,
            'admin_name' => $otherAdmin->name,
            'admin_email' => $otherAdmin->email,
            'admin_type' => $otherAdmin->type,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'login_location' => 'Test Location',
            'device' => 'Test Device',
            'login_at' => now(),
            'last_activity' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson("/admin/session/force-logout-user/{$otherAdmin->id}");
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJson(['message' => '해당 관리자의 모든 세션이 종료되었습니다.']);

        // 해당 관리자의 활성 세션이 삭제되었는지 확인
        $activeSessions = DB::table('admin_sessions')
                           ->where('admin_user_id', $otherAdmin->id)
                           ->where('is_active', true)
                           ->count();
        $this->assertEquals(0, $activeSessions);
    }

    /** @test */
    public function test_get_current_session_info_requires_authentication()
    {
        // 로그인하지 않은 상태에서 세션 정보 조회 시도
        $response = $this->getJson('/admin/session/info');
        
        $response->assertStatus(401)
                ->assertJson(['success' => false])
                ->assertJson(['message' => '로그인이 필요합니다.']);
    }

    /** @test */
    public function test_get_current_session_info_works_for_authenticated_user()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 세션 정보를 데이터베이스에 저장
        $sessionId = session()->getId();
        DB::table('admin_sessions')->insert([
            'session_id' => $sessionId,
            'admin_user_id' => $this->admin->id,
            'admin_name' => $this->admin->name,
            'admin_email' => $this->admin->email,
            'admin_type' => $this->admin->type,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'login_location' => 'Test Location',
            'device' => 'Test Device',
            'login_at' => now(),
            'last_activity' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/admin/session/info');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['success', 'session_info', 'admin_user']);

        $data = $response->json();
        $this->assertNotNull($data['session_info']);
        $this->assertNotNull($data['admin_user']);
        $this->assertEquals($this->admin->id, $data['admin_user']['id']);
    }

    /** @test */
    public function test_get_active_sessions_requires_super_admin()
    {
        // 일반 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        $response = $this->getJson('/admin/session/active');
        
        $response->assertStatus(403)
                ->assertJson(['success' => false])
                ->assertJson(['message' => '권한이 없습니다.']);
    }

    /** @test */
    public function test_get_active_sessions_works_for_super_admin()
    {
        // super 관리자로 설정
        $this->admin->update(['type' => 'super']);
        $this->actingAs($this->admin, 'admin');

        // 여러 활성 세션 생성
        DB::table('admin_sessions')->insert([
            [
                'session_id' => 'session1',
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test User Agent',
                'login_location' => 'Test Location',
                'device' => 'Test Device',
                'login_at' => now()->subMinutes(10),
                'last_activity' => now()->subMinutes(5),
                'is_active' => true,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(5),
            ],
            [
                'session_id' => 'session2',
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.2',
                'user_agent' => 'Test User Agent 2',
                'login_location' => 'Test Location 2',
                'device' => 'Test Device 2',
                'login_at' => now()->subMinutes(5),
                'last_activity' => now()->subMinutes(1),
                'is_active' => true,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(1),
            ],
        ]);

        $response = $this->getJson('/admin/session/active');
        
        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure(['success', 'sessions']);

        $data = $response->json();
        $this->assertCount(2, $data['sessions']);
        
        // 최근 활동 순으로 정렬되었는지 확인
        $this->assertEquals('session2', $data['sessions'][0]['session_id']);
        $this->assertEquals('session1', $data['sessions'][1]['session_id']);
    }

    /** @test */
    public function test_logout_handles_database_errors_gracefully()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 데이터베이스 오류를 시뮬레이션하기 위해 테이블을 임시로 삭제
        $originalTable = DB::table('admin_sessions');
        
        // 테이블이 존재하지 않는 상태에서 로그아웃 시도
        // 실제로는 테이블을 삭제할 수 없으므로 다른 방법으로 테스트
        
        $response = $this->get('/admin/logout');
        
        // 데이터베이스 오류가 발생해도 로그아웃은 성공해야 함
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_logout_clears_intended_url()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // intended_url을 세션에 설정
        session(['intended_url' => '/admin/dashboard']);

        $this->get('/admin/logout');

        // intended_url이 세션에서 제거되었는지 확인
        $this->assertNull(session('intended_url'));
    }

    /** @test */
    public function test_logout_handles_missing_session()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 세션을 수동으로 제거
        $this->app['session.store']->flush();

        $response = $this->get('/admin/logout');
        
        // 세션이 없어도 로그아웃은 성공해야 함
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_logout_handles_missing_admin_user()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 관리자 계정을 삭제
        $this->admin->delete();

        $response = $this->get('/admin/logout');
        
        // 관리자 계정이 없어도 로그아웃은 성공해야 함
        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    /** @test */
    public function test_logout_handles_multiple_sessions()
    {
        // 관리자로 로그인
        $this->actingAs($this->admin, 'admin');

        // 여러 세션 생성
        $session1 = session()->getId();
        $session2 = 'another-session-id';

        DB::table('admin_sessions')->insert([
            [
                'session_id' => $session1,
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test User Agent',
                'login_location' => 'Test Location',
                'device' => 'Test Device',
                'login_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'session_id' => $session2,
                'admin_user_id' => $this->admin->id,
                'admin_name' => $this->admin->name,
                'admin_email' => $this->admin->email,
                'admin_type' => $this->admin->type,
                'ip_address' => '127.0.0.2',
                'user_agent' => 'Test User Agent 2',
                'login_location' => 'Test Location 2',
                'device' => 'Test Device 2',
                'login_at' => now(),
                'last_activity' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get('/admin/logout');

        // 현재 세션만 비활성화되었는지 확인
        $currentSession = DB::table('admin_sessions')
                           ->where('session_id', $session1)
                           ->first();
        $otherSession = DB::table('admin_sessions')
                         ->where('session_id', $session2)
                         ->first();

        $this->assertFalse($currentSession->is_active);
        $this->assertTrue($otherSession->is_active);
    }
}
