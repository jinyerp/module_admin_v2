<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUserDetailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $baseUrl = '/admin/admin/users';

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => '테스트 관리자',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'type' => 'super',
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'phone' => '010-1234-5678',
            'memo' => '테스트용 관리자 계정',
            'last_login_at' => now(),
            'login_count' => 5,
            'google_2fa_enabled' => false,
            'ms_2fa_enabled' => false,
            'google_2fa_required' => false,
            'ms_2fa_required' => false,
        ]);

        // 관리자로 로그인
        $this->actingAs($this->adminUser, 'admin');
    }

    /** @test */
    public function 관리자가_사용자_목록_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
        $response->assertSee('관리자 사용자 목록');
        $response->assertSee('사용자 추가');
        $response->assertSee('CSV 다운로드');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        $response->assertSee('사용자 상세 정보');
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->adminUser->email);
    }

    /** @test */
    public function 관리자가_사용자_생성_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.create');
        $response->assertSee('새 관리자 사용자 등록');
    }

    /** @test */
    public function 관리자가_사용자_수정_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.edit');
        $response->assertSee('관리자 사용자 수정');
        $response->assertSee($this->adminUser->name);
    }

    /** @test */
    public function 관리자가_새로운_사용자를_생성할_수_있다()
    {
        $userData = [
            'name' => '새 관리자',
            'email' => 'newadmin@test.com',
            'password' => 'NewPassword123!',
            'type' => 'admin',
            'status' => 'active',
        ];

        $response = $this->postJson($this->baseUrl, $userData);
        
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_users', [
            'name' => '새 관리자',
            'email' => 'newadmin@test.com',
            'type' => 'admin',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function 관리자가_사용자_정보를_수정할_수_있다()
    {
        $updateData = [
            'name' => '수정된 관리자',
            'email' => 'updated@test.com',
            'type' => 'staff',
            'status' => 'inactive',
        ];

        $response = $this->putJson($this->baseUrl . '/' . $this->adminUser->id, $updateData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_users', [
            'id' => $this->adminUser->id,
            'name' => '수정된 관리자',
            'email' => 'updated@test.com',
            'type' => 'staff',
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function 관리자가_사용자를_삭제할_수_있다()
    {
        $response = $this->deleteJson($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('admin_users', [
            'id' => $this->adminUser->id,
        ]);
    }

    /** @test */
    public function 관리자가_사용자_삭제_확인_페이지를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id . '/delete-confirm');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.form_delete');
        $response->assertSee($this->adminUser->name . ' 삭제');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_검색할_수_있다()
    {
        // 추가 테스트 사용자 생성
        AdminUser::create([
            'name' => '검색 테스트 사용자',
            'email' => 'search@test.com',
            'password' => Hash::make('password123'),
            'type' => 'staff',
            'status' => 'active',
        ]);

        $response = $this->get($this->baseUrl . '?filter_search=검색');
        
        $response->assertStatus(200);
        $response->assertSee('검색 테스트 사용자');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_필터링할_수_있다()
    {
        // 비활성 사용자 생성
        AdminUser::create([
            'name' => '비활성 사용자',
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'type' => 'staff',
            'status' => 'inactive',
        ]);

        $response = $this->get($this->baseUrl . '?filter_status=inactive');
        
        $response->assertStatus(200);
        $response->assertSee('비활성 사용자');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_정렬할_수_있다()
    {
        $response = $this->get($this->baseUrl . '?sort=name&direction=asc');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_페이지네이션을_사용할_수_있다()
    {
        // 여러 사용자 생성 (페이지네이션 테스트용)
        for ($i = 1; $i <= 25; $i++) {
            AdminUser::create([
                'name' => "테스트 사용자 {$i}",
                'email' => "user{$i}@test.com",
                'password' => Hash::make('password123'),
                'type' => 'staff',
                'status' => 'active',
            ]);
        }

        $response = $this->get($this->baseUrl . '?per_page=10');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
    }

    /** @test */
    public function 관리자가_사용자_통계를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
        
        // 통계 정보가 포함되어 있는지 확인
        $response->assertSee('total');
        $response->assertSee('active');
        $response->assertSee('inactive');
        $response->assertSee('suspended');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_액션_버튼들을_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
        
        // 액션 버튼들이 포함되어 있는지 확인
        $response->assertSee('보기');
        $response->assertSee('수정');
        $response->assertSee('삭제');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_체크박스를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.index');
        
        // 체크박스가 포함되어 있는지 확인
        $response->assertSee('checkbox');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_일괄_삭제를_할_수_있다()
    {
        // 테스트용 사용자 생성
        $user1 = AdminUser::create([
            'name' => '삭제 대상 1',
            'email' => 'delete1@test.com',
            'password' => Hash::make('password123'),
            'type' => 'staff',
            'status' => 'active',
        ]);

        $user2 = AdminUser::create([
            'name' => '삭제 대상 2',
            'email' => 'delete2@test.com',
            'password' => Hash::make('password123'),
            'type' => 'staff',
            'status' => 'active',
        ]);

        $response = $this->postJson($this->baseUrl . '/bulk-delete', [
            'ids' => [$user1->id, $user2->id]
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // 사용자들이 삭제되었는지 확인
        $this->assertDatabaseMissing('admin_users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('admin_users', ['id' => $user2->id]);
    }

    /** @test */
    public function 관리자가_사용자_목록에서_CSV를_다운로드할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/export/csv');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="admin_users.csv"');
    }

    /** @test */
    public function 관리자가_사용자_목록에서_Excel을_다운로드할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/export/excel');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="admin_users.xlsx"');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에서_2FA_정보를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        
        // 2FA 관련 정보가 포함되어 있는지 확인
        $response->assertSee('2FA 상태');
        $response->assertSee('Google 2FA');
        $response->assertSee('Microsoft 2FA');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에서_로그_정보를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        
        // 로그 관련 정보가 포함되어 있는지 확인
        $response->assertSee('활동 로그');
        $response->assertSee('로그인 기록');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에서_권한_정보를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        
        // 권한 관련 정보가 포함되어 있는지 확인
        $response->assertSee('권한 정보');
        $response->assertSee('등급');
        $response->assertSee('상태');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에서_개인정보를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        
        // 개인정보가 포함되어 있는지 확인
        $response->assertSee('개인 정보');
        $response->assertSee('이름');
        $response->assertSee('이메일');
        $response->assertSee('전화번호');
        $response->assertSee('메모');
    }

    /** @test */
    public function 관리자가_사용자_상세_페이지에서_로그인_통계를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/' . $this->adminUser->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.users.show');
        
        // 로그인 통계가 포함되어 있는지 확인
        $response->assertSee('로그인 통계');
        $response->assertSee('마지막 로그인');
        $response->assertSee('로그인 횟수');
    }
}
