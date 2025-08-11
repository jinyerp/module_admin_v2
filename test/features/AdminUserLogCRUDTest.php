<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserLog;
use Illuminate\Support\Facades\Hash;

class AdminUserLogCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $baseUrl = '/admin/admin/user-logs';

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 사용자 생성
        $this->adminUser = AdminUser::create([
            'name' => '테스트 관리자',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'type' => 'super',
            'status' => 'active'
        ]);

        // 관리자로 로그인
        $this->actingAs($this->adminUser, 'admin');
    }

    /** @test */
    public function 관리자가_로그인_로그_목록_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_logs.index');
        $response->assertSee('로그인 로그 목록');
        $response->assertSee('로그 추가');
        $response->assertSee('CSV 다운로드');
    }

    /** @test */
    public function 관리자가_로그인_로그_생성_페이지에_접근할_수_있다()
    {
        $response = $this->get($this->baseUrl . '/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_logs.create');
        $response->assertSee('새 관리자 회원 등록');
    }

    /** @test */
    public function 관리자가_새로운_로그인_로그를_생성할_수_있다()
    {
        $logData = [
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ];

        $response = $this->postJson($this->baseUrl, $logData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_user_logs', [
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);
    }

    /** @test */
    public function 관리자가_로그인_로그_상세_페이지를_볼_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_logs.show');
        $response->assertSee('로그인 로그 상세');
        $response->assertSee($log->id);
        $response->assertSee($log->ip_address);
        $response->assertSee($log->message);
    }

    /** @test */
    public function 관리자가_로그인_로그_수정_페이지에_접근할_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_logs.edit');
        $response->assertSee('사용자 로그 관리');
    }

    /** @test */
    public function 관리자가_로그인_로그를_수정할_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $updateData = [
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.200',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'status' => 'failed',
            'message' => '수정된 로그 메시지'
        ];

        $response = $this->putJson($this->baseUrl . '/' . $log->id, $updateData);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_user_logs', [
            'id' => $log->id,
            'ip_address' => '192.168.1.200',
            'status' => 'failed',
            'message' => '수정된 로그 메시지'
        ]);
    }

    /** @test */
    public function 관리자가_로그인_로그를_삭제할_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $log->id);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('admin_user_logs', ['id' => $log->id]);
    }

    /** @test */
    public function 관리자가_로그인_로그_삭제_확인_페이지를_볼_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id . '/confirm');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_logs.form_delete');
        $response->assertSee('로그 삭제');
    }

    /** @test */
    public function 관리자가_로그인_로그를_일괄_삭제할_수_있다()
    {
        $logs = [];
        for ($i = 0; $i < 3; $i++) {
            $logs[] = AdminUserLog::create([
                'admin_user_id' => $this->adminUser->id,
                'ip_address' => '192.168.1.' . (100 + $i),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => 'success',
                'message' => '테스트 로그인 성공 ' . ($i + 1)
            ]);
        }

        $ids = collect($logs)->pluck('id')->toArray();
        
        $response = $this->postJson($this->baseUrl . '/bulk-delete', ['ids' => $ids]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('admin_user_logs', ['id' => $id]);
        }
    }

    /** @test */
    public function 관리자가_로그인_로그_CSV를_다운로드할_수_있다()
    {
        // 테스트 로그 생성
        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/download-csv');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="admin_user_logs_' . date('Ymd_His') . '.csv"');
    }

    /** @test */
    public function 관리자가_로그인_로그를_검색할_수_있다()
    {
        // 다양한 상태의 로그 생성
        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '로그인 성공'
        ]);

        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.200',
            'status' => 'failed',
            'message' => '로그인 실패'
        ]);

        // 상태별 검색
        $response = $this->get($this->baseUrl . '?filter_status=success');
        $response->assertStatus(200);
        $response->assertSee('로그인 성공');
        $response->assertDontSee('로그인 실패');

        // IP 주소별 검색
        $response = $this->get($this->baseUrl . '?filter_ip_address=192.168.1.100');
        $response->assertStatus(200);
        $response->assertSee('192.168.1.100');

        // 통합 검색
        $response = $this->get($this->baseUrl . '?filter_search=로그인');
        $response->assertStatus(200);
        $response->assertSee('로그인 성공');
        $response->assertSee('로그인 실패');
    }

    /** @test */
    public function 관리자가_로그인_로그를_정렬할_수_있다()
    {
        // 여러 로그 생성
        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '첫 번째 로그',
            'created_at' => now()->subDays(2)
        ]);

        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.200',
            'status' => 'success',
            'message' => '두 번째 로그',
            'created_at' => now()->subDays(1)
        ]);

        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.300',
            'status' => 'success',
            'message' => '세 번째 로그',
            'created_at' => now()
        ]);

        // ID 오름차순 정렬
        $response = $this->get($this->baseUrl . '?sort=id&direction=asc');
        $response->assertStatus(200);

        // 생성일시 내림차순 정렬
        $response = $this->get($this->baseUrl . '?sort=created_at&direction=desc');
        $response->assertStatus(200);
    }

    /** @test */
    public function 관리자가_로그인_로그_통계를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/stats');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::logs.login_logs.stats');
    }

    /** @test */
    public function 관리자가_특정_관리자의_로그_통계를_볼_수_있다()
    {
        $response = $this->get($this->baseUrl . '/admin/' . $this->adminUser->id . '/stats');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.logs.login_logs.admin-stats');
    }

    /** @test */
    public function 관리자가_로그인_로그를_내보낼_수_있다()
    {
        $response = $this->postJson($this->baseUrl . '/export', [
            'date_from' => now()->subDays(30)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function 관리자가_로그인_로그를_정리할_수_있다()
    {
        // 오래된 로그 생성
        AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '오래된 로그',
            'created_at' => now()->subDays(100)
        ]);

        $response = $this->postJson($this->baseUrl . '/cleanup', ['days' => 90]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /** @test */
    public function 상세_페이지에서_관리자_정보를_클릭할_수_있다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id);
        
        $response->assertStatus(200);
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->adminUser->email);
        
        // 관리자 이름이 링크로 되어 있는지 확인
        $response->assertSee('href="' . route('admin.admin.users.show', $this->adminUser->id) . '"');
    }

    /** @test */
    public function 로그인_로그_목록에서_체크박스가_올바르게_표시된다()
    {
        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        // 첫 번째 체크박스 (전체 선택) 확인
        $response->assertSee('id="bulk-checked-all"');
        // 개별 체크박스 확인
        $response->assertSee('type="checkbox"');
    }

    /** @test */
    public function 로그인_로그_목록에서_페이지네이션이_작동한다()
    {
        // 20개의 로그 생성 (페이지당 15개)
        for ($i = 0; $i < 20; $i++) {
            AdminUserLog::create([
                'admin_user_id' => $this->adminUser->id,
                'ip_address' => '192.168.1.' . (100 + $i),
                'status' => 'success',
                'message' => '테스트 로그 ' . ($i + 1)
            ]);
        }

        $response = $this->get($this->baseUrl);
        $response->assertStatus(200);
        
        // 첫 번째 페이지에 15개 로그가 있는지 확인
        $response->assertSee('테스트 로그 1');
        $response->assertSee('테스트 로그 15');
        
        // 두 번째 페이지 확인
        $response = $this->get($this->baseUrl . '?page=2');
        $response->assertStatus(200);
        $response->assertSee('테스트 로그 16');
        $response->assertSee('테스트 로그 20');
    }

    /** @test */
    public function 로그인_로그_생성_시_유효성_검사가_작동한다()
    {
        $invalidData = [
            'admin_user_id' => '', // 필수 필드 누락
            'ip_address' => 'invalid-ip', // 잘못된 IP 형식
            'status' => 'invalid-status', // 잘못된 상태값
            'message' => str_repeat('a', 501) // 최대 길이 초과
        ];

        $response = $this->postJson($this->baseUrl, $invalidData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['admin_user_id', 'status']);
    }

    /** @test */
    public function 로그인_로그_수정_시_유효성_검사가_작동한다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $invalidData = [
            'admin_user_id' => '', // 필수 필드 누락
            'ip_address' => '192.168.1.200',
            'status' => 'invalid-status', // 잘못된 상태값
            'message' => '수정된 메시지'
        ];

        $response = $this->putJson($this->baseUrl . '/' . $log->id, $invalidData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['admin_user_id', 'status']);
    }

    /** @test */
    public function 로그인_로그_삭제_시_확인_절차가_필요하다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id . '/confirm');
        
        $response->assertStatus(200);
        $response->assertSee('로그 삭제');
        $response->assertSee('이 작업은 되돌릴 수 없습니다');
        $response->assertSee('위의 난수키를 입력하세요');
    }

    /** @test */
    public function 로그인_로그_목록에서_필터_초기화가_작동한다()
    {
        $response = $this->get($this->baseUrl . '?filter_status=success&filter_ip_address=192.168.1.100');
        $response->assertStatus(200);
        
        // 초기화 버튼이 현재 URL로 연결되어 있는지 확인
        $response->assertSee('href="' . request()->url() . '"');
    }

    /** @test */
    public function 로그인_로그_목록에서_액션_버튼들이_올바르게_표시된다()
    {
        $log = AdminUserLog::create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => '192.168.1.100',
            'status' => 'success',
            'message' => '테스트 로그인 성공'
        ]);

        $response = $this->get($this->baseUrl);
        
        $response->assertStatus(200);
        // 상세보기 버튼
        $response->assertSee('href="' . route('admin.admin.user-logs.show', $log->id) . '"');
        // 수정 버튼
        $response->assertSee('href="' . route('admin.admin.user-logs.edit', $log->id) . '"');
        // 삭제 버튼
        $response->assertSee('data-delete-route="' . route('admin.admin.user-logs.destroy', $log->id) . '"');
    }
}
