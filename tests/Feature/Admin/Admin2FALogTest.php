<?php

namespace Jiny\Admin\Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\Admin2FALog;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

/**
 * Admin2FALogController Feature 테스트
 * 
 * 2FA 로그 관리 기능의 전체적인 동작을 검증합니다.
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
 * @docs jiny/admin/docs/features/Admin2FALog.md
 * 
 * 테스트 완료시 @TEST.md 에 결과를 반영해 주세요.
 */
class Admin2FALogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $baseUrl = '/admin/admin/user-2fa-logs';

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 관리자 사용자 생성
        $this->adminUser = AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_super_admin' => true
        ]);

        // 관리자로 로그인
        $this->actingAs($this->adminUser, 'admin');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 목록 페이지가 정상적으로 표시되는지 검증
     * 예상 결과: 200 상태 코드, 올바른 뷰 렌더링, 로그 데이터와 통계 정보 표시
     */
    public function it_can_display_2fa_logs_index_page()
    {
        // 2FA 로그 데이터 생성
        Admin2FALog::factory()->count(5)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl);

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_2fa_logs.index');
        $response->assertViewHas('rows');
        $response->assertViewHas('stats');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 특정 관리자별로 2FA 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 필터링된 결과만 표시, 모든 로그가 지정된 관리자에 속함
     */
    public function it_can_filter_2fa_logs_by_admin_user()
    {
        $otherAdmin = AdminUser::factory()->create();
        
        // 다른 관리자의 2FA 로그 생성
        Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $otherAdmin->id
        ]);

        $response = $this->get($this->baseUrl . '?admin_user_id=' . $otherAdmin->id);

        $response->assertStatus(200);
        $response->assertViewHas('rows');
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->every(function ($log) use ($otherAdmin) {
            return $log->admin_user_id === $otherAdmin->id;
        }));
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그를 상태별로 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공/실패 상태별로 올바르게 필터링됨
     */
    public function it_can_filter_2fa_logs_by_status()
    {
        // 성공과 실패 로그 생성
        Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id,
            'status' => 'success'
        ]);
        
        Admin2FALog::factory()->count(2)->create([
            'admin_user_id' => $this->adminUser->id,
            'status' => 'fail'
        ]);

        $response = $this->get($this->baseUrl . '?status=success');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->every(function ($log) {
            return $log->status === 'success';
        }));
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 키워드로 2FA 로그를 검색할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 검색어가 포함된 로그만 표시, 검색 결과가 0보다 큼
     */
    public function it_can_search_2fa_logs_by_keyword()
    {
        $searchMessage = 'test search message';
        
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'message' => $searchMessage
        ]);

        $response = $this->get($this->baseUrl . '?search=' . urlencode('test search'));

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그를 정렬할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 생성일 기준 오름차순으로 정렬된 로그 목록
     */
    public function it_can_sort_2fa_logs()
    {
        Admin2FALog::factory()->count(5)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '?sort=created_at&direction=asc');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->isSortedBy('created_at'));
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 새로운 2FA 로그를 생성할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 데이터베이스에 로그 생성됨
     */
    public function it_can_create_2fa_log()
    {
        $logData = [
            'admin_user_id' => $this->adminUser->id,
            'action' => 'verify',
            'status' => 'success',
            'ip_address' => '192.168.1.1',
            'message' => '2FA verification successful'
        ];

        $response = $this->postJson($this->baseUrl, $logData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_2fa_logs', $logData);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 필수 필드가 누락된 경우 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 필수 필드에 대한 유효성 검사 오류 메시지
     */
    public function it_validates_required_fields_when_creating_2fa_log()
    {
        $response = $this->postJson($this->baseUrl, []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['admin_user_id', 'action', 'status']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 특정 2FA 로그의 상세 정보를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 올바른 뷰 렌더링, 로그 데이터 표시
     */
    public function it_can_show_2fa_log_details()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id);

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_2fa_logs.show');
        $response->assertViewHas('log', $log);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 수정 폼을 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 수정 폼 뷰 렌더링, 기존 로그 데이터 표시
     */
    public function it_can_edit_2fa_log()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id . '/edit');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_2fa_logs.edit');
        $response->assertViewHas('log', $log);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 정보를 수정할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 데이터베이스에 업데이트 반영됨
     */
    public function it_can_update_2fa_log()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $updateData = [
            'admin_user_id' => $this->adminUser->id,
            'action' => 'verify',
            'status' => 'fail',
            'message' => 'Updated message'
        ];

        $response = $this->putJson($this->baseUrl . '/' . $log->id, $updateData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('admin_2fa_logs', array_merge(['id' => $log->id], $updateData));
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그를 삭제할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 데이터베이스에서 로그 삭제됨
     */
    public function it_can_delete_2fa_log()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->deleteJson($this->baseUrl . '/' . $log->id);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('admin_2fa_logs', ['id' => $log->id]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 여러 2FA 로그를 일괄 삭제할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 모든 선택된 로그가 삭제됨
     */
    public function it_can_bulk_delete_2fa_logs()
    {
        $logs = Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $ids = $logs->pluck('id')->toArray();

        $response = $this->postJson($this->baseUrl . '/bulk-delete', [
            'ids' => $ids
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('admin_2fa_logs', ['id' => $id]);
        }
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그를 내보낼 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 파일명과 다운로드 URL 제공
     */
    public function it_can_export_2fa_logs()
    {
        Admin2FALog::factory()->count(5)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->postJson($this->baseUrl . '/export');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['filename', 'download_url']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 오래된 2FA 로그를 정리할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 지정된 기간 이전 로그만 삭제됨
     */
    public function it_can_cleanup_old_2fa_logs()
    {
        // 오래된 로그 생성 (90일 전)
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'created_at' => now()->subDays(100)
        ]);

        // 최근 로그 생성
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'created_at' => now()->subDays(30)
        ]);

        $response = $this->postJson($this->baseUrl . '/cleanup', ['days' => 90]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // 오래된 로그는 삭제되고 최근 로그는 유지되어야 함
        $this->assertDatabaseCount('admin_2fa_logs', 1);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 통계 정보를 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 통계 뷰 렌더링, 다양한 통계 데이터 표시
     */
    public function it_can_display_2fa_logs_statistics()
    {
        // 다양한 상태의 로그 생성
        Admin2FALog::factory()->count(5)->create([
            'admin_user_id' => $this->adminUser->id,
            'status' => 'success'
        ]);
        
        Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id,
            'status' => 'fail'
        ]);

        $response = $this->get($this->baseUrl . '/stats');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_2fa_logs.stats');
        $response->assertViewHas('dailyStats');
        $response->assertViewHas('actionStats');
        $response->assertViewHas('adminStats');
        $response->assertViewHas('ipStats');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그를 CSV 형식으로 다운로드할 수 있는지 검증
     * 예상 결과: 200 상태 코드, CSV 헤더, 다운로드 가능한 파일 형식
     */
    public function it_can_download_2fa_logs_csv()
    {
        Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '/download-csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 삭제 확인 폼을 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 삭제 확인 폼 뷰 렌더링, 필요한 데이터 표시
     */
    public function it_can_show_delete_confirmation_form()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id . '/delete-confirm');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.user_2fa_logs.form_delete');
        $response->assertViewHas('log', $log);
        $response->assertViewHas('url');
        $response->assertViewHas('title');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 인증되지 않은 사용자의 접근을 제한하는지 검증
     * 예상 결과: 로그인 페이지로 리다이렉트, 인증 필요
     */
    public function it_requires_authentication_to_access_2fa_logs()
    {
        // 로그아웃
        auth('admin')->logout();

        $response = $this->get($this->baseUrl);

        $response->assertRedirect('/admin/login');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 날짜 범위로 2FA 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 날짜 범위의 로그만 표시
     */
    public function it_can_filter_2fa_logs_by_date_range()
    {
        // 특정 날짜의 로그 생성
        $specificDate = '2024-01-15';
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'created_at' => $specificDate
        ]);

        $response = $this->get($this->baseUrl . '?date_from=' . $specificDate . '&date_to=' . $specificDate);

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 액션별로 2FA 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 액션의 로그만 표시
     */
    public function it_can_filter_2fa_logs_by_action()
    {
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'action' => 'enable'
        ]);

        $response = $this->get($this->baseUrl . '?action=enabled');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: IP 주소별로 2FA 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 IP 주소의 로그만 표시
     */
    public function it_can_filter_2fa_logs_by_ip_address()
    {
        $specificIP = '192.168.1.100';
        Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id,
            'ip_address' => $specificIP
        ]);

        $response = $this->get($this->baseUrl . '?ip_address=' . $specificIP);

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 존재하지 않는 2FA 로그 ID에 대한 처리가 적절한지 검증
     * 예상 결과: 404 상태 코드, 존재하지 않는 리소스 오류
     */
    public function it_handles_invalid_2fa_log_id_gracefully()
    {
        $invalidId = 99999;

        $response = $this->get($this->baseUrl . '/' . $invalidId);

        $response->assertStatus(404);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 수정 시 유효성 검사가 작동하는지 검증
     * 예상 결과: 422 상태 코드, 필수 필드에 대한 유효성 검사 오류
     */
    public function it_validates_2fa_log_data_on_update()
    {
        $log = Admin2FALog::factory()->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->putJson($this->baseUrl . '/' . $log->id, []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['admin_user_id', 'action', 'status']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 검색 결과가 없는 경우의 처리가 적절한지 검증
     * 예상 결과: 200 상태 코드, 빈 결과 목록, 결과 수가 0
     */
    public function it_can_handle_empty_search_results()
    {
        $response = $this->get($this->baseUrl . '?search=nonexistent');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertEquals(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 2FA 로그 페이지네이션이 정상적으로 작동하는지 검증
     * 예상 결과: 200 상태 코드, 페이지별 데이터 분할, 다음 페이지 존재 여부 확인
     */
    public function it_can_handle_pagination()
    {
        // 25개의 로그 생성 (페이지당 15개)
        Admin2FALog::factory()->count(25)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '?page=2');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->hasMorePages());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 잘못된 정렬 파라미터에 대한 처리가 적절한지 검증
     * 예상 결과: 200 상태 코드, 기본 정렬 적용, 결과 데이터 표시
     */
    public function it_can_handle_invalid_sort_parameters()
    {
        Admin2FALog::factory()->count(3)->create([
            'admin_user_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '?sort=invalid_field&direction=invalid');

        $response->assertStatus(200);
        
        // 기본 정렬이 적용되어야 함
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }
}
