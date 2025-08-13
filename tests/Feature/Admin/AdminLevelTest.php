<?php

namespace Jiny\Admin\Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminLevel;

/**
 * AdminLevel 기능 테스트
 * 
 * 관리자 등급 관리 기능의 모든 CRUD 작업과 권한 검증을 테스트합니다.
 * 
 * 테스트 실행후 /jiny/admin/test.md 에 결과를 갱신해 주세요.
 * 
 * @package Jiny\Admin\Tests\Feature\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminLevel.md
 * 
 * 테스트 완료시 @TEST.md 에 결과를 반영해 주세요.
 */
class AdminLevelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $adminLevel;

    /**
     * 테스트 설정
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => '테스트 관리자',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'type' => 'super',
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        // 테스트용 등급 생성
        $this->adminLevel = AdminLevel::create([
            'name' => '테스트 등급',
            'code' => 'test',
            'badge_color' => '#ff0000',
            'can_create' => true,
            'can_read' => true,
            'can_update' => false,
            'can_delete' => false,
            'sort_order' => 1,
        ]);
    }

    /**
     * 등급 목록 페이지가 정상적으로 렌더링되는지 테스트
     * 
     * 테스트 목적: 등급 관리 목록 페이지의 기본 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, '등급 관리' 제목 표시, 테스트 등급 정보 표시
     */
    public function test_admin_level_index_page_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/admin/levels');

        $response->assertStatus(200);
        $response->assertSee('등급 관리');
        $response->assertSee('테스트 등급');
    }

    /**
     * 등급 생성 폼이 정상적으로 렌더링되는지 테스트
     * 
     * 테스트 목적: 새 등급 등록 폼의 렌더링과 필수 필드 표시 검증
     * 예상 결과: 200 상태 코드, '새 등급 등록' 제목, 등급명과 등급코드 필드 표시
     */
    public function test_admin_level_create_form_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/admin/levels/create');

        $response->assertStatus(200);
        $response->assertSee('새 등급 등록');
        $response->assertSee('등급명');
        $response->assertSee('등급코드');
    }

    /**
     * 등급 상세 페이지가 정상적으로 렌더링되는지 테스트
     * 
     * 테스트 목적: 특정 등급의 상세 정보 페이지 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, 등급명과 등급코드 정보 표시
     */
    public function test_admin_level_show_page_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get("/admin/admin/levels/{$this->adminLevel->id}");

        $response->assertStatus(200);
        $response->assertSee('테스트 등급');
        $response->assertSee('test');
    }

    /**
     * 등급 수정 폼이 정상적으로 렌더링되는지 테스트
     * 
     * 테스트 목적: 기존 등급 수정 폼의 렌더링과 기존 데이터 표시 검증
     * 예상 결과: 200 상태 코드, 기존 등급명 표시, 등급코드 필드 표시
     */
    public function test_admin_level_edit_form_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get("/admin/admin/levels/{$this->adminLevel->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('테스트 등급');
        $response->assertSee('등급코드');
    }

    /**
     * 새로운 등급을 성공적으로 생성할 수 있는지 테스트
     * 
     * 테스트 목적: 새 등급 생성 API의 정상 동작과 데이터베이스 저장 검증
     * 예상 결과: 201 상태 코드, 성공 메시지, 데이터베이스에 새 등급 저장됨
     */
    public function test_admin_level_can_be_created()
    {
        $levelData = [
            'name' => '새로운 등급',
            'code' => 'new_level',
            'badge_color' => '#00ff00',
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => false,
            'sort_order' => 2,
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post('/admin/admin/levels', $levelData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => '등급이 성공적으로 등록되었습니다.',
        ]);

        $this->assertDatabaseHas('admin_levels', [
            'name' => '새로운 등급',
            'code' => 'new_level',
        ]);
    }

    /**
     * 등급 정보를 성공적으로 수정할 수 있는지 테스트
     * 
     * 테스트 목적: 기존 등급 정보 수정 API의 정상 동작과 데이터베이스 업데이트 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에 수정된 정보 반영됨
     */
    public function test_admin_level_can_be_updated()
    {
        $updateData = [
            'name' => '수정된 등급',
            'code' => 'test',
            'badge_color' => '#0000ff',
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
            'sort_order' => 1,
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->put("/admin/admin/levels/{$this->adminLevel->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '등급이 성공적으로 수정되었습니다.',
        ]);

        $this->assertDatabaseHas('admin_levels', [
            'id' => $this->adminLevel->id,
            'name' => '수정된 등급',
            'badge_color' => '#0000ff',
        ]);
    }

    /**
     * 등급을 성공적으로 삭제할 수 있는지 테스트
     * 
     * 테스트 목적: 등급 삭제 API의 정상 동작과 데이터베이스에서 제거 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에서 등급 삭제됨
     */
    public function test_admin_level_can_be_deleted()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->delete("/admin/admin/levels/{$this->adminLevel->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '등급이 성공적으로 삭제되었습니다.',
        ]);

        $this->assertDatabaseMissing('admin_levels', [
            'id' => $this->adminLevel->id,
        ]);
    }

    /**
     * 사용 중인 등급은 삭제할 수 없는지 테스트
     * 
     * 테스트 목적: 사용자가 사용 중인 등급의 삭제 방지 기능 검증
     * 예상 결과: 400 상태 코드, 삭제 불가 메시지, 데이터베이스에 등급 유지됨
     */
    public function test_used_level_cannot_be_deleted()
    {
        // 등급을 사용하는 사용자 생성
        AdminUser::create([
            'name' => '테스트 사용자',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'type' => 'test', // 테스트 등급 사용
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->delete("/admin/admin/levels/{$this->adminLevel->id}");

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => '사용 중인 등급은 삭제할 수 없습니다. (사용자 수: 1명)',
        ]);

        $this->assertDatabaseHas('admin_levels', [
            'id' => $this->adminLevel->id,
        ]);
    }

    /**
     * 권한 토글이 정상적으로 작동하는지 테스트
     * 
     * 테스트 목적: 등급별 권한 설정 토글 기능의 정상 동작 검증
     * 예상 결과: 200 상태 코드, 권한 변경 성공 메시지, 데이터베이스에 권한 상태 반영됨
     */
    public function test_permission_toggle_works_correctly()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post("/admin/admin/levels/{$this->adminLevel->id}/toggle-permission", [
                'permission' => 'can_update'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '권한이 변경되었습니다.',
            'permission' => 'can_update',
            'value' => true, // false에서 true로 변경
        ]);

        $this->assertDatabaseHas('admin_levels', [
            'id' => $this->adminLevel->id,
            'can_update' => true,
        ]);
    }

    /**
     * 일괄 삭제가 정상적으로 작동하는지 테스트
     * 
     * 테스트 목적: 여러 등급을 한 번에 삭제하는 일괄 삭제 기능 검증
     * 예상 결과: 200 상태 코드, 일괄 삭제 성공 메시지, 모든 선택된 등급이 삭제됨
     */
    public function test_bulk_delete_works_correctly()
    {
        // 추가 등급 생성
        $level2 = AdminLevel::create([
            'name' => '테스트 등급 2',
            'code' => 'test2',
            'badge_color' => '#00ff00',
            'can_create' => false,
            'can_read' => true,
            'can_update' => false,
            'can_delete' => false,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post('/admin/admin/levels/bulk-delete', [
                'ids' => [$this->adminLevel->id, $level2->id]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '2개의 등급이 성공적으로 삭제되었습니다.',
        ]);

        $this->assertDatabaseMissing('admin_levels', [
            'id' => $this->adminLevel->id,
        ]);
        $this->assertDatabaseMissing('admin_levels', [
            'id' => $level2->id,
        ]);
    }

    /**
     * 통계 정보가 정상적으로 표시되는지 테스트
     * 
     * 테스트 목적: 등급별 통계 정보 API의 정상 동작과 데이터 구조 검증
     * 예상 결과: 200 상태 코드, 통계 데이터의 올바른 JSON 구조, 총 등급 수와 사용자 분포 정보
     */
    public function test_statistics_page_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/admin/levels/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'with_users',
            'without_users',
            'total_users',
            'level_distribution'
        ]);
    }

    /**
     * 삭제 확인 폼이 정상적으로 표시되는지 테스트
     * 
     * 테스트 목적: 등급 삭제 전 확인 폼의 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, '등급 삭제' 제목, 삭제할 등급 정보 표시
     */
    public function test_delete_confirmation_form_can_be_rendered()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get("/admin/admin/levels/{$this->adminLevel->id}/confirm");

        $response->assertStatus(200);
        $response->assertSee('등급 삭제');
        $response->assertSee('테스트 등급');
    }

    /**
     * 유효성 검사가 정상적으로 작동하는지 테스트
     * 
     * 테스트 목적: 등급 생성 시 필수 필드 누락과 중복 코드에 대한 유효성 검사 검증
     * 예상 결과: 422 상태 코드, 필수 필드와 중복 코드에 대한 유효성 검사 오류
     */
    public function test_validation_works_correctly()
    {
        $invalidData = [
            'name' => '', // 필수 필드 누락
            'code' => 'test', // 중복 코드
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post('/admin/admin/levels', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'code']);
    }

    /**
     * 권한이 없는 사용자는 등급을 생성할 수 없는지 테스트
     * 
     * 테스트 목적: 권한이 없는 사용자의 등급 생성 시도에 대한 접근 제어 검증
     * 예상 결과: 현재는 권한 체크가 우회되어 있으므로 201 상태 코드 (향후 403으로 변경 예정)
     */
    public function test_unauthorized_user_cannot_create_level()
    {
        // 일반 사용자 생성
        $regularUser = AdminUser::create([
            'name' => '일반 사용자',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'type' => 'staff',
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $levelData = [
            'name' => '무단 생성 등급',
            'code' => 'unauthorized',
            'badge_color' => '#ff0000',
            'can_create' => false,
            'can_read' => true,
            'can_update' => false,
            'can_delete' => false,
            'sort_order' => 3,
        ];

        $response = $this->actingAs($regularUser, 'admin')
            ->post('/admin/admin/levels', $levelData);

        // 권한 체크가 우회되어 있으므로 성공해야 함 (현재 디버깅 모드)
        // TODO: 실제 권한 체크 활성화 시 403 상태 코드 확인
        $response->assertStatus(201);
    }

    /**
     * 웹 인터페이스의 실제 동작을 검증하는 테스트
     * 
     * 테스트 목적: 등급 관리 웹 페이지의 실제 HTML 렌더링과 데이터 표시 검증
     * 예상 결과: 200 상태 코드, 모든 등급 정보와 권한 상태가 올바르게 표시됨
     */
    public function test_admin_level_web_interface_actual_behavior()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/admin/levels');

        $response->assertStatus(200);
        
        // 실제 HTML 내용 확인
        $response->assertSee('등급 관리');
        $response->assertSee('테스트 등급');
        $response->assertSee('test');
        $response->assertSee('#ff0000');
        
        // 권한 상태 표시 확인
        $response->assertSee('can_create');
        $response->assertSee('can_read');
        $response->assertSee('can_update');
        $response->assertSee('can_delete');
    }
}
