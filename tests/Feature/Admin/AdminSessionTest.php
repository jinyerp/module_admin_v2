<?php

namespace Jiny\Admin\Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminSession;
use Jiny\Admin\App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * AdminSession 기능 테스트
 *
 * AdminSession 컨트롤러와 모델의 모든 기능을 테스트합니다.
 * 세션 생성, 조회, 수정, 삭제 및 보안 기능을 검증합니다.
 *
 * @package Jiny\Admin\Tests\Feature\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSession.md
 * 
 * 테스트 실행후 /jiny/admin/test.md 에 결과를 갱신해 주세요.
 * 
 * 테스트 완료시 @TEST.md 에 결과를 반영해 주세요.
 */
class AdminSessionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $adminSession;

    /**
     * 테스트 설정
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 사용자 생성
        $this->adminUser = AdminUser::factory()->create([
            'type' => 'super_admin',
            'status' => 'active'
        ]);
        
        // 관리자로 로그인
        Auth::guard('admin')->login($this->adminUser);
        
        // 테스트용 세션 생성
        $this->adminSession = AdminSession::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'login_at' => now()->subHours(1),
            'last_activity' => now()->subMinutes(5),
        ]);
    }

    /**
     * 세션 목록 조회 테스트
     * 
     * 테스트 목적: 세션 관리 목록 페이지의 기본 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, 올바른 뷰 렌더링, 세션 데이터와 필터 정보 표시
     */
    public function test_can_view_session_list()
    {
        // 여러 세션 생성
        AdminSession::factory()->count(5)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get(route('admin.admin.sessions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.sessions.index');
        $response->assertViewHas('rows');
        $response->assertViewHas('filters');
    }

    /**
     * 세션 검색 필터링 테스트
     * 
     * 테스트 목적: 관리자 이름으로 세션을 검색할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 검색 결과에 지정된 관리자 이름이 포함됨
     */
    public function test_can_filter_sessions_by_search()
    {
        // 특정 이름을 가진 관리자 사용자 생성
        $searchUser = AdminUser::factory()->create(['name' => 'TestUser']);
        AdminSession::factory()->create(['admin_user_id' => $searchUser->id]);

        $response = $this->get(route('admin.admin.sessions.index', [
            'filter_search' => 'TestUser'
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('rows');
        
        // 검색 결과에 TestUser가 포함되어야 함
        $this->assertTrue(
            $response->viewData('rows')->contains('adminUser.name', 'TestUser')
        );
    }

    /**
     * 세션 활성 상태 필터링 테스트
     * 
     * 테스트 목적: 활성/비활성 상태별로 세션을 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 활성/비활성 세션별로 올바르게 필터링됨
     */
    public function test_can_filter_sessions_by_active_status()
    {
        // 활성 세션 생성
        AdminSession::factory()->active()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        // 비활성 세션 생성
        AdminSession::factory()->inactive()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        // 활성 세션만 조회
        $response = $this->get(route('admin.admin.sessions.index', [
            'filter_active' => 'active'
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            $response->viewData('rows')->every('is_active', true)
        );

        // 비활성 세션만 조회
        $response = $this->get(route('admin.admin.sessions.index', [
            'filter_active' => 'inactive'
        ]));

        $response->assertStatus(200);
        $this->assertTrue(
            $response->viewData('rows')->every('is_active', false)
        );
    }

    /**
     * 세션 생성 폼 테스트
     * 
     * 테스트 목적: 새 세션 생성 폼의 렌더링 검증
     * 예상 결과: 200 상태 코드, 세션 생성 폼 뷰 렌더링
     */
    public function test_can_view_session_create_form()
    {
        $response = $this->get(route('admin.admin.sessions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.sessions.create');
    }

    /**
     * 세션 생성 테스트
     * 
     * 테스트 목적: 새로운 세션을 성공적으로 생성할 수 있는지 검증
     * 예상 결과: 201 상태 코드, 성공 메시지, 데이터베이스에 세션 생성됨
     */
    public function test_can_create_session()
    {
        $sessionData = [
            'admin_user_id' => $this->adminUser->id,
            'session_id' => Str::random(40),
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'login_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson(route('admin.admin.sessions.store'), $sessionData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => '세션이 성공적으로 생성되었습니다.'
        ]);

        // 데이터베이스에 세션이 생성되었는지 확인
        $this->assertDatabaseHas('admin_sessions', [
            'admin_user_id' => $sessionData['admin_user_id'],
            'ip_address' => $sessionData['ip_address'],
        ]);
    }

    /**
     * 세션 생성 유효성 검증 테스트
     * 
     * 테스트 목적: 잘못된 데이터로 세션 생성 시도 시 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 필수 필드에 대한 유효성 검사 오류 메시지
     */
    public function test_cannot_create_session_with_invalid_data()
    {
        $invalidData = [
            'admin_user_id' => 99999, // 존재하지 않는 사용자
            'session_id' => '', // 빈 세션 ID
            'ip_address' => 'invalid-ip', // 잘못된 IP 주소
        ];

        $response = $this->postJson(route('admin.admin.sessions.store'), $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'admin_user_id',
            'session_id',
            'ip_address'
        ]);
    }

    /**
     * 세션 상세 보기 테스트
     * 
     * 테스트 목적: 특정 세션의 상세 정보를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 세션 상세 뷰 렌더링, 세션과 관리자 정보 표시
     */
    public function test_can_view_session_details()
    {
        $response = $this->get(route('admin.admin.sessions.show', $this->adminSession->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.sessions.show');
        $response->assertViewHas('session');
        $response->assertViewHas('session.adminUser');
    }

    /**
     * 존재하지 않는 세션 조회 테스트
     * 
     * 테스트 목적: 존재하지 않는 세션 ID로 조회 시 적절한 오류 처리가 되는지 검증
     * 예상 결과: 404 상태 코드, 존재하지 않는 리소스 오류
     */
    public function test_cannot_view_nonexistent_session()
    {
        $response = $this->get(route('admin.admin.sessions.show', 'nonexistent-id'));

        $response->assertStatus(404);
    }

    /**
     * 세션 수정 폼 테스트
     * 
     * 테스트 목적: 세션 수정 폼을 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 세션 수정 폼 뷰 렌더링, 기존 세션 데이터 표시
     */
    public function test_can_view_session_edit_form()
    {
        $response = $this->get(route('admin.admin.sessions.edit', $this->adminSession->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.sessions.edit');
        $response->assertViewHas('session');
    }

    /**
     * 세션 수정 테스트
     * 
     * 테스트 목적: 기존 세션 정보를 성공적으로 수정할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에 업데이트 반영됨
     */
    public function test_can_update_session()
    {
        $updateData = [
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Updated User Agent',
            'last_activity' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->putJson(
            route('admin.admin.sessions.update', $this->adminSession->id),
            $updateData
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '세션이 성공적으로 수정되었습니다.'
        ]);

        // 데이터베이스에 업데이트가 반영되었는지 확인
        $this->assertDatabaseHas('admin_sessions', [
            'id' => $this->adminSession->id,
            'ip_address' => $updateData['ip_address'],
        ]);
    }

    /**
     * 세션 수정 유효성 검증 테스트
     * 
     * 테스트 목적: 잘못된 데이터로 세션 수정 시도 시 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 잘못된 필드에 대한 유효성 검사 오류 메시지
     */
    public function test_cannot_update_session_with_invalid_data()
    {
        $invalidData = [
            'ip_address' => 'invalid-ip',
            'user_agent' => str_repeat('a', 501), // 500자 초과
        ];

        $response = $this->putJson(
            route('admin.admin.sessions.update', $this->adminSession->id),
            $invalidData
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'ip_address',
            'user_agent'
        ]);
    }

    /**
     * 세션 삭제 테스트
     * 
     * 테스트 목적: 세션을 성공적으로 삭제할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에서 세션 삭제됨
     */
    public function test_can_delete_session()
    {
        $response = $this->deleteJson(
            route('admin.admin.sessions.destroy', $this->adminSession->id)
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '세션이 성공적으로 삭제되었습니다.'
        ]);

        // 데이터베이스에서 세션이 삭제되었는지 확인
        $this->assertDatabaseMissing('admin_sessions', [
            'id' => $this->adminSession->id
        ]);
    }

    /**
     * 세션 새로고침 테스트
     * 
     * 테스트 목적: 세션을 새로고침하여 활동 시간을 업데이트할 수 있는지 검증
     * 예상 결과: 목록 페이지로 리다이렉트, 성공 메시지, last_activity 시간 업데이트됨
     */
    public function test_can_refresh_session()
    {
        $oldActivity = $this->adminSession->last_activity;

        $response = $this->get(route('admin.admin.sessions.refresh', $this->adminSession->id));

        $response->assertRedirect(route('admin.admin.sessions.index'));
        $response->assertSessionHas('success', '세션이 새로고침되었습니다.');

        // last_activity가 업데이트되었는지 확인
        $this->adminSession->refresh();
        $this->assertTrue($this->adminSession->last_activity->gt($oldActivity));
    }

    /**
     * 일괄 삭제 테스트
     * 
     * 테스트 목적: 여러 세션을 한 번에 삭제하는 일괄 삭제 기능 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 모든 선택된 세션이 삭제됨
     */
    public function test_can_bulk_delete_sessions()
    {
        // 여러 세션 생성
        $sessions = AdminSession::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $sessionIds = $sessions->pluck('id')->toArray();

        $response = $this->postJson(route('admin.admin.sessions.bulk-delete'), [
            'ids' => $sessionIds
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '3개의 세션이 성공적으로 삭제되었습니다.'
        ]);

        // 모든 세션이 삭제되었는지 확인
        foreach ($sessionIds as $id) {
            $this->assertDatabaseMissing('admin_sessions', ['id' => $id]);
        }
    }

    /**
     * 세션 확인 페이지 테스트
     * 
     * 테스트 목적: 세션 삭제 전 확인 페이지의 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, 삭제 확인 폼 뷰 렌더링, 세션 정보와 랜덤 키 표시
     */
    public function test_can_view_session_confirm_page()
    {
        $response = $this->get(route('admin.admin.sessions.confirm', $this->adminSession->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.sessions.form_delete');
        $response->assertViewHas('session');
        $response->assertViewHas('randomKey');
    }

    /**
     * 세션 모델 관계 테스트
     * 
     * 테스트 목적: 세션과 관리자 사용자 간의 관계가 올바르게 설정되었는지 검증
     * 예상 결과: 세션에서 관리자 사용자 정보를 정상적으로 조회할 수 있음
     */
    public function test_session_has_admin_user_relationship()
    {
        $session = AdminSession::with('adminUser')->find($this->adminSession->id);

        $this->assertInstanceOf(AdminUser::class, $session->adminUser);
        $this->assertEquals($this->adminUser->id, $session->adminUser->id);
    }

    /**
     * 세션 활성 상태 속성 테스트
     * 
     * 테스트 목적: 세션의 활성/비활성 상태 속성이 올바르게 작동하는지 검증
     * 예상 결과: 활성 세션은 true, 비활성 세션은 false 반환
     */
    public function test_session_active_status_attribute()
    {
        // 활성 세션
        $activeSession = AdminSession::factory()->active()->create([
            'admin_user_id' => $this->adminUser->id
        ]);
        $this->assertTrue($activeSession->is_active);

        // 비활성 세션
        $inactiveSession = AdminSession::factory()->inactive()->create([
            'admin_user_id' => $this->adminUser->id
        ]);
        $this->assertFalse($inactiveSession->is_active);
    }

    /**
     * 세션 지속 시간 속성 테스트
     * 
     * 테스트 목적: 세션의 지속 시간 계산 속성이 올바르게 작동하는지 검증
     * 예상 결과: 지속 시간이 정수로 반환되고, 사람이 읽기 쉬운 형태로도 제공됨
     */
    public function test_session_duration_attributes()
    {
        $session = AdminSession::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'login_at' => now()->subHours(2),
            'last_activity' => now()->subHour(),
        ]);

        $this->assertIsInt($session->duration);
        $this->assertIsString($session->duration_human);
        $this->assertGreaterThan(0, $session->duration);
    }

    /**
     * 세션 스코프 테스트
     * 
     * 테스트 목적: 세션의 활성/비활성 스코프가 올바르게 작동하는지 검증
     * 예상 결과: 활성/비활성 세션별로 올바른 개수 반환
     */
    public function test_session_scopes()
    {
        // 활성 세션
        AdminSession::factory()->active()->count(3)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        // 비활성 세션
        AdminSession::factory()->inactive()->count(2)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $this->assertEquals(3, AdminSession::active()->count());
        $this->assertEquals(2, AdminSession::inactive()->count());
    }

    /**
     * 세션 만료 정리 테스트
     * 
     * 테스트 목적: 만료된 세션을 자동으로 정리하는 기능이 작동하는지 검증
     * 예상 결과: 만료된 세션이 모두 삭제되고, 삭제된 세션 수가 정확히 반환됨
     */
    public function test_can_cleanup_expired_sessions()
    {
        // 만료된 세션 생성
        AdminSession::factory()->expired()->count(5)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $deletedCount = AdminSession::cleanupExpired();

        $this->assertEquals(5, $deletedCount);
        $this->assertEquals(0, AdminSession::expired()->count());
    }

    /**
     * 세션 보안 기능 테스트
     * 
     * 테스트 목적: 세션의 보안 관련 기능(IP 변경 감지, 사용자 에이전트 변경 감지)이 작동하는지 검증
     * 예상 결과: IP나 사용자 에이전트가 변경되면 의심스러운 활동으로 감지됨
     */
    public function test_session_security_features()
    {
        $session = AdminSession::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
        ]);

        // IP 주소 변경 감지
        $this->assertTrue($session->hasIpChanged('192.168.1.2'));
        $this->assertFalse($session->hasIpChanged('192.168.1.1'));

        // 사용자 에이전트 변경 감지
        $this->assertTrue($session->hasUserAgentChanged('Different Browser'));
        $this->assertFalse($session->hasUserAgentChanged('Test Browser'));

        // 의심스러운 활동 감지
        $this->assertTrue($session->isSuspicious('192.168.1.2', 'Different Browser'));
        $this->assertFalse($session->isSuspicious('192.168.1.1', 'Test Browser'));
    }

    /**
     * 세션 안전 정보 반환 테스트
     * 
     * 테스트 목적: 세션 정보를 안전하게 변환하여 민감한 정보를 마스킹하는지 검증
     * 예상 결과: session_id와 user_agent가 적절히 축약되어 반환됨
     */
    public function test_session_safe_array_conversion()
    {
        $session = AdminSession::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'session_id' => Str::random(40),
            'user_agent' => str_repeat('a', 150), // 긴 사용자 에이전트
        ]);

        $safeArray = $session->toSafeArray();

        // session_id가 마스킹되었는지 확인
        $this->assertStringContainsString('...', $safeArray['session_id']);
        $this->assertLessThan(40, strlen($safeArray['session_id']));

        // user_agent가 축약되었는지 확인
        $this->assertStringContainsString('...', $safeArray['user_agent']);
        $this->assertLessThanOrEqual(103, strlen($safeArray['user_agent']));
    }

    /**
     * 세션 활동 로그 테스트
     * 
     * 테스트 목적: 세션 목록 조회 시 Activity Log가 생성되는지 검증
     * 예상 결과: 데이터베이스에 세션 목록 조회 활동 로그가 생성됨
     */
    public function test_session_activity_logging()
    {
        $this->get(route('admin.admin.sessions.index'));

        // Activity Log가 생성되었는지 확인
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_user_id' => $this->adminUser->id,
            'action' => 'list',
            'resource_type' => 'session',
        ]);
    }

    /**
     * 세션 감사 로그 테스트
     * 
     * 테스트 목적: 세션 수정 시 Audit Log가 생성되는지 검증
     * 예상 결과: 데이터베이스에 세션 수정 감사 로그가 생성됨
     */
    public function test_session_audit_logging()
    {
        $oldData = $this->adminSession->toArray();
        $updateData = ['ip_address' => '192.168.1.100'];

        $this->putJson(
            route('admin.admin.sessions.update', $this->adminSession->id),
            $updateData
        );

        // Audit Log가 생성되었는지 확인
        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_user_id' => $this->adminUser->id,
            'action' => 'update',
            'resource_type' => 'session',
            'resource_id' => $this->adminSession->id,
        ]);
    }

    /**
     * 권한 없는 사용자 접근 테스트
     * 
     * 테스트 목적: 인증되지 않은 사용자의 세션 관리 접근을 제한하는지 검증
     * 예상 결과: 로그인 페이지로 리다이렉트되어 인증 필요
     */
    public function test_unauthorized_user_cannot_access_sessions()
    {
        // 로그아웃
        Auth::guard('admin')->logout();

        $response = $this->get(route('admin.admin.sessions.index'));

        // 로그인 페이지로 리다이렉트되어야 함
        $response->assertRedirect();
    }
}
