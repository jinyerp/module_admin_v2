<?php

namespace Jiny\Admin\Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminLevel;

/**
 * AdminUser 기능 테스트
 * 
 * 관리자 사용자 관리 기능의 전체적인 동작을 검증합니다.
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
 * @docs jiny/admin/docs/features/AdminUser.md
 * 
 * 테스트 완료시 @TEST.md 에 결과를 반영해 주세요.
 */
class AdminUserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $adminLevel;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 관리자 등급 생성
        $this->adminLevel = AdminLevel::create([
            'name' => '테스트 등급',
            'code' => 'test',
            'badge_color' => '#ff0000',
            'can_create' => true,
            'can_read' => true,
            'can_update' => true,
            'can_delete' => true,
            'sort_order' => 1,
        ]);

        // 테스트용 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => '테스트 관리자',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'type' => 'super',
            'admin_level_id' => $this->adminLevel->id,
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        // 관리자로 로그인
        $this->actingAs($this->adminUser, 'admin');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 관리자 사용자 목록 페이지가 정상적으로 렌더링되는지 검증
     * 예상 결과: 200 상태 코드, '관리자 사용자' 제목 표시, 테스트 관리자 정보 표시
     */
    public function test_admin_user_index_page_can_be_rendered()
    {
        $response = $this->get('/admin/admin/users');

        $response->assertStatus(200);
        $response->assertSee('관리자 사용자');
        $response->assertSee('테스트 관리자');
        $response->assertSee('admin@test.com');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 새 관리자 사용자 생성 폼이 정상적으로 렌더링되는지 검증
     * 예상 결과: 200 상태 코드, '새 관리자 등록' 제목, 필수 필드들 표시
     */
    public function test_admin_user_create_form_can_be_rendered()
    {
        $response = $this->get('/admin/admin/users/create');

        $response->assertStatus(200);
        $response->assertSee('새 관리자 등록');
        $response->assertSee('이름');
        $response->assertSee('이메일');
        $response->assertSee('비밀번호');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 새로운 관리자 사용자를 성공적으로 생성할 수 있는지 검증
     * 예상 결과: 201 상태 코드, 성공 메시지, 데이터베이스에 새 사용자 저장됨
     */
    public function test_admin_user_can_be_created()
    {
        $userData = [
            'name' => '새로운 관리자',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'staff',
            'admin_level_id' => $this->adminLevel->id,
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
        ];

        $response = $this->post('/admin/admin/users', $userData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => '관리자가 성공적으로 등록되었습니다.',
        ]);

        $this->assertDatabaseHas('admin_users', [
            'name' => '새로운 관리자',
            'email' => 'newadmin@test.com',
            'type' => 'staff',
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 필수 필드가 누락된 경우 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 필수 필드에 대한 유효성 검사 오류 메시지
     */
    public function test_admin_user_creation_validates_required_fields()
    {
        $response = $this->post('/admin/admin/users', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 중복 이메일로 관리자 생성 시도 시 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 이메일 중복에 대한 유효성 검사 오류 메시지
     */
    public function test_admin_user_creation_prevents_duplicate_email()
    {
        $userData = [
            'name' => '중복 이메일 관리자',
            'email' => 'admin@test.com', // 이미 존재하는 이메일
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => 'staff',
            'admin_level_id' => $this->adminLevel->id,
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
        ];

        $response = $this->post('/admin/admin/users', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 특정 관리자 사용자의 상세 정보를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 상세 정보 뷰 렌더링, 사용자 정보 표시
     */
    public function test_admin_user_show_page_can_be_rendered()
    {
        $response = $this->get('/admin/admin/users/' . $this->adminUser->id);

        $response->assertStatus(200);
        $response->assertSee('테스트 관리자');
        $response->assertSee('admin@test.com');
        $response->assertSee('super');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 관리자 사용자 수정 폼을 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 수정 폼 뷰 렌더링, 기존 사용자 데이터 표시
     */
    public function test_admin_user_edit_form_can_be_rendered()
    {
        $response = $this->get('/admin/admin/users/' . $this->adminUser->id . '/edit');

        $response->assertStatus(200);
        $response->assertSee('관리자 정보 수정');
        $response->assertSee('테스트 관리자');
        $response->assertSee('admin@test.com');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 기존 관리자 사용자 정보를 성공적으로 수정할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에 수정된 정보 반영됨
     */
    public function test_admin_user_can_be_updated()
    {
        $updateData = [
            'name' => '수정된 관리자',
            'email' => 'admin@test.com',
            'type' => 'staff',
            'admin_level_id' => $this->adminLevel->id,
            'status' => 'active',
            'is_active' => true,
            'is_super_admin' => false,
        ];

        $response = $this->put('/admin/admin/users/' . $this->adminUser->id, $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '관리자 정보가 성공적으로 수정되었습니다.',
        ]);

        $this->assertDatabaseHas('admin_users', [
            'id' => $this->adminUser->id,
            'name' => '수정된 관리자',
            'type' => 'staff',
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 관리자 사용자를 성공적으로 삭제할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 메시지, 데이터베이스에서 사용자 삭제됨
     */
    public function test_admin_user_can_be_deleted()
    {
        $response = $this->delete('/admin/admin/users/' . $this->adminUser->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '관리자가 성공적으로 삭제되었습니다.',
        ]);

        $this->assertDatabaseMissing('admin_users', [
            'id' => $this->adminUser->id,
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 웹 인터페이스의 실제 동작을 검증하는지 확인
     * 예상 결과: 200 상태 코드, 모든 사용자 정보와 상태가 올바르게 표시됨
     */
    public function test_admin_user_web_interface_actual_behavior()
    {
        $response = $this->get('/admin/admin/users');

        $response->assertStatus(200);
        
        // 실제 HTML 내용 확인
        $response->assertSee('관리자 사용자');
        $response->assertSee('테스트 관리자');
        $response->assertSee('admin@test.com');
        $response->assertSee('super');
        $response->assertSee('active');
    }
}
