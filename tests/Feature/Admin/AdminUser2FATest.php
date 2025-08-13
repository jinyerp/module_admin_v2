<?php

namespace Jiny\Admin\Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminLevel;
use Illuminate\Support\Facades\Hash;

/**
 * AdminUser2FAController 테스트
 * 
 * 관리자 사용자 2FA 관리 기능에 대한 테스트
 * 2FA는 Admin2FALogTest와 연관 관계를 가지고 있습니다.
 *  
 * 테스트 실행후 /jiny/admin/test.md 에 결과를 갱신해 주세요.
 */
class AdminUser2FATest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $adminLevel;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 등급 생성
        $this->adminLevel = AdminLevel::create([
            'name' => 'Super Admin',
            'code' => 'super_admin',
            'badge_color' => '#dc2626',
            'can_list' => true,
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true
        ]);

        // 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'type' => 'super',
            'level_id' => $this->adminLevel->id,
            'is_active' => true,
            'email_verified_at' => now()
        ]);
    }

    /**
     * 2FA 설정 페이지 접근 테스트
     * 예상결과: 200 응답 및 뷰 파일 확인
     */
    public function test_can_access_2fa_setup_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.admin.users.2fa.setup', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.2fa.setup');
    }

    /**
     * 2FA 설정 저장 테스트
     * 예상결과: 201 응답 및 데이터베이스에 저장되었는지 확인
     */
    public function test_can_store_2fa_setup()
    {
        $data = [
            'secret' => 'test_secret_key',
            'backup_codes' => ['12345678', '87654321', '11111111']
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), $data);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // 데이터베이스에 저장되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'id' => $this->adminUser->id,
            'is_2fa_enabled' => true
        ]);
    }

    /**
     * 2FA 관리 페이지 접근 테스트
     * 예상결과: 200 응답 및 뷰 파일 확인
     */
    public function test_can_access_2fa_manage_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.admin.users.2fa.manage', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.2fa.manage');
    }

    /**
     * 2FA 설정 수정 테스트
     * 예상결과: 200 응답 및 데이터베이스에 저장되었는지 확인
     */
    public function test_can_update_2fa_settings()
    {
        // 2FA 활성화
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.update', $this->adminUser->id), [
                'action' => 'enable'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2FA 비활성화
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.update', $this->adminUser->id), [
                'action' => 'disable'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * 백업 코드 재생성 테스트
     * 예상결과: 200 응답 및 데이터베이스에 저장되었는지 확인
     */
    public function test_can_regenerate_backup_codes()
    {
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.update', $this->adminUser->id), [
                'action' => 'regenerate_backup'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * 2FA 설정 삭제 테스트
     * 예상결과: 200 응답 및 데이터베이스에서 제거되었는지 확인
     */
    public function test_can_delete_2fa_settings()
    {
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('admin.admin.users.2fa.destroy', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 데이터베이스에서 제거되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'id' => $this->adminUser->id,
            'is_2fa_enabled' => false,
            'two_factor_secret' => null
        ]);
    }

    /**
     * 2FA 활성화 테스트
     * 예상결과: 200 응답 및 데이터베이스에 저장되었는지 확인
     */
    public function test_can_enable_2fa()
    {
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.enable', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * 2FA 비활성화 테스트
     * 예상결과: 200 응답 및 데이터베이스에 저장되었는지 확인
     */
    public function test_can_disable_2fa()
    {
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.disable', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * 권한 없는 사용자 접근 제한 테스트
     * 예상결과: 403 응답
     */
    public function test_unauthorized_user_cannot_access_2fa_pages()
    {
        $regularUser = AdminUser::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'type' => 'user',
            'level_id' => $this->adminLevel->id,
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($regularUser)
            ->get(route('admin.admin.users.2fa.setup', $this->adminUser->id));

        $response->assertStatus(403);
    }

    /**
     * 2FA 설정 유효성 검증 테스트
     * 예상결과: 422 응답 및 유효성 오류 메시지 확인
     */
    public function test_2fa_setup_validation()
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['secret', 'backup_codes']);
    }

    /**
     * 2FA 설정 수정 유효성 검증 테스트
     */
    public function test_2fa_update_validation()
    {
        $response = $this->actingAs($this->adminUser)
            ->patchJson(route('admin.admin.users.2fa.update', $this->adminUser->id), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['action']);
    }

    /**
     * 2FA 설정 상태 조회 테스트
     */
    public function test_can_view_2fa_status()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.admin.users.2fa.show', $this->adminUser->id));

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.2fa.show');
    }

    /**
     * 2FA 설정 완료 후 상태 확인 테스트
     */
    public function test_2fa_enabled_status_after_setup()
    {
        $data = [
            'secret' => 'test_secret_key',
            'backup_codes' => ['12345678', '87654321']
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), $data);

        $this->adminUser->refresh();

        $this->assertTrue($this->adminUser->is_2fa_enabled);
        $this->assertNotNull($this->adminUser->two_factor_secret);
        $this->assertNotNull($this->adminUser->two_factor_backup_codes);
    }

    /**
     * 2FA 설정 삭제 후 상태 확인 테스트
     */
    public function test_2fa_disabled_status_after_deletion()
    {
        // 먼저 2FA 설정
        $data = [
            'secret' => 'test_secret_key',
            'backup_codes' => ['12345678', '87654321']
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), $data);

        // 2FA 설정 삭제
        $this->actingAs($this->adminUser)
            ->deleteJson(route('admin.admin.users.2fa.destroy', $this->adminUser->id));

        $this->adminUser->refresh();

        $this->assertFalse($this->adminUser->is_2fa_enabled);
        $this->assertNull($this->adminUser->two_factor_secret);
        $this->assertNull($this->adminUser->two_factor_backup_codes);
    }

    /**
     * 백업 코드 개수 확인 테스트
     */
    public function test_backup_codes_count()
    {
        $data = [
            'secret' => 'test_secret_key',
            'backup_codes' => ['12345678', '87654321', '11111111', '22222222', '33333333']
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), $data);

        $this->adminUser->refresh();
        $backupCodes = json_decode($this->adminUser->two_factor_backup_codes, true);

        $this->assertCount(5, $backupCodes);
    }

    /**
     * 2FA 설정 시간 기록 테스트
     */
    public function test_2fa_enabled_at_timestamp()
    {
        $data = [
            'secret' => 'test_secret_key',
            'backup_codes' => ['12345678', '87654321']
        ];

        $this->actingAs($this->adminUser)
            ->postJson(route('admin.admin.users.2fa.store', $this->adminUser->id), $data);

        $this->adminUser->refresh();

        $this->assertNotNull($this->adminUser->two_factor_enabled_at);
        $this->assertEquals(now()->format('Y-m-d H:i'), 
            $this->adminUser->two_factor_enabled_at->format('Y-m-d H:i'));
    }
}
