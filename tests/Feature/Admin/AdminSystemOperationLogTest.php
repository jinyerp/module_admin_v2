<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\SystemOperationLog;
use Illuminate\Support\Facades\Hash;

/**
 * AdminSystemOperationLog 기능 테스트
 * 
 * 시스템 운영 로그 관리 기능의 전체적인 동작을 검증합니다.
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
 * @docs jiny/admin/docs/features/AdminSystemOperationLog.md
 * 
 * 테스트 완료시 @TEST.md 에 결과를 반영해 주세요.
 */
class AdminSystemOperationLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $baseUrl = '/admin/admin/system-operation-logs';

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
     * 테스트 목적: 시스템 운영 로그 목록 페이지가 정상적으로 표시되는지 검증
     * 예상 결과: 200 상태 코드, 올바른 뷰 렌더링, 로그 데이터 표시
     */
    public function test_can_view_operation_logs_index()
    {
        // 시스템 운영 로그 데이터 생성
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl);

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.system_operation_logs.index');
        $response->assertViewHas('rows');
        $response->assertViewHas('stats');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 특정 시스템 운영 로그의 상세 정보를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 올바른 뷰 렌더링, 로그 상세 데이터 표시
     */
    public function test_can_view_operation_log_detail()
    {
        $log = SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '/' . $log->id);

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.system_operation_logs.show');
        $response->assertViewHas('log', $log);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 통계 정보를 표시할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 통계 뷰 렌더링, 다양한 통계 데이터 표시
     */
    public function test_can_view_operation_logs_stats()
    {
        // 다양한 유형의 로그 생성
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_type' => 'create'
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_type' => 'update'
        ]);

        $response = $this->get($this->baseUrl . '/stats');

        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::admin.system_operation_logs.stats');
        $response->assertViewHas('operationStats');
        $response->assertViewHas('performerStats');
        $response->assertViewHas('timeStats');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 API를 통해 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 로그 데이터 배열 반환
     */
    public function test_can_get_operation_logs_api()
    {
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->getJson($this->baseUrl . '/api');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'operation_name',
                    'operation_type',
                    'performed_by_id',
                    'status',
                    'created_at'
                ]
            ],
            'meta' => [
                'current_page',
                'total',
                'per_page'
            ]
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 특정 시스템 운영 로그의 상세 정보를 API로 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 해당 로그의 상세 정보 반환
     */
    public function test_can_get_operation_log_detail_api()
    {
        $log = SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_name' => 'Test Operation',
            'operation_type' => 'test',
            'details' => 'Test operation details'
        ]);

        $response = $this->getJson($this->baseUrl . '/api/' . $log->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $log->id,
                'operation_name' => 'Test Operation',
                'operation_type' => 'test',
                'details' => 'Test operation details'
            ]
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 통계 정보를 API로 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 통계 데이터 구조 반환
     */
    public function test_can_get_operation_logs_stats_api()
    {
        // 다양한 상태의 로그 생성
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'success'
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'failed'
        ]);

        $response = $this->getJson($this->baseUrl . '/api/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_operations',
            'success_count',
            'failed_count',
            'operation_types',
            'performers'
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 운영 유형별 분석 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 운영 유형별 통계 데이터 반환
     */
    public function test_can_get_operation_type_analysis()
    {
        // 다양한 운영 유형의 로그 생성
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_type' => 'create'
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_type' => 'update'
        ]);
        
        SystemOperationLog::factory()->count(1)->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_type' => 'delete'
        ]);

        $response = $this->getJson($this->baseUrl . '/api/analysis/operation-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'operation_types' => [
                '*' => [
                    'type',
                    'count',
                    'percentage'
                ]
            ]
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 수행자별 분석 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 수행자별 통계 데이터 반환
     */
    public function test_can_get_performer_analysis()
    {
        $otherAdmin = AdminUser::factory()->create();
        
        // 다른 관리자의 로그 생성
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $otherAdmin->id
        ]);

        $response = $this->getJson($this->baseUrl . '/api/analysis/performers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'performers' => [
                '*' => [
                    'performer_id',
                    'performer_name',
                    'operation_count',
                    'success_rate'
                ]
            ]
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 성능 분석 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 성능 관련 통계 데이터 반환
     */
    public function test_can_get_performance_analysis()
    {
        // 다양한 실행 시간의 로그 생성
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'execution_time' => 100
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'execution_time' => 500
        ]);

        $response = $this->getJson($this->baseUrl . '/api/analysis/performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'average_execution_time',
            'slowest_operations',
            'fastest_operations',
            'performance_trends'
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시간별 트렌드 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 시간별 통계 데이터 반환
     */
    public function test_can_get_time_trend()
    {
        // 다양한 시간대의 로그 생성
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id,
            'created_at' => now()->subDays(1)
        ]);
        
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'created_at' => now()
        ]);

        $response = $this->getJson($this->baseUrl . '/api/analysis/time-trend');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'daily_trends',
            'hourly_trends',
            'weekly_trends',
            'monthly_trends'
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 오류 분석 데이터를 조회할 수 있는지 검증
     * 예상 결과: 200 상태 코드, JSON 응답, 오류 관련 통계 데이터 반환
     */
    public function test_can_get_error_analysis()
    {
        // 성공과 실패 로그 생성
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'success'
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'failed',
            'error_message' => 'Test error message'
        ]);

        $response = $this->getJson($this->baseUrl . '/api/analysis/errors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error_count',
            'success_rate',
            'common_errors',
            'error_trends'
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 여러 시스템 운영 로그를 일괄 삭제할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 선택된 로그들이 삭제됨
     */
    public function test_can_bulk_delete_operation_logs()
    {
        $logs = SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $ids = $logs->pluck('id')->toArray();

        $response = $this->postJson($this->baseUrl . '/bulk-delete', [
            'ids' => $ids
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // 모든 선택된 로그가 삭제되었는지 확인
        foreach ($ids as $id) {
            $this->assertDatabaseMissing('system_operation_logs', ['id' => $id]);
        }
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그를 내보낼 수 있는지 검증
     * 예상 결과: 200 상태 코드, 성공 응답, 파일명과 다운로드 URL 제공
     */
    public function test_can_export_operation_logs()
    {
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->postJson($this->baseUrl . '/export');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['filename', 'download_url']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그를 다양한 조건으로 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 필터링된 결과만 표시
     */
    public function test_can_filter_operation_logs()
    {
        // 다양한 상태의 로그 생성
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'success'
        ]);
        
        SystemOperationLog::factory()->count(2)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'failed'
        ]);

        // 성공 상태만 필터링
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
     * 테스트 목적: 시스템 운영 로그를 정렬할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 정렬된 결과 표시
     */
    public function test_can_sort_operation_logs()
    {
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '?sort=created_at&direction=desc');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->isSortedByDesc('created_at'));
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 키워드로 시스템 운영 로그를 검색할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 검색어가 포함된 로그만 표시
     */
    public function test_can_search_operation_logs()
    {
        $searchMessage = 'test search message';
        
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'details' => $searchMessage
        ]);

        $response = $this->get($this->baseUrl . '?search=' . urlencode('test search'));

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 날짜 범위로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 날짜 범위의 로그만 표시
     */
    public function test_can_filter_by_date_range()
    {
        // 특정 날짜의 로그 생성
        $specificDate = '2024-01-15';
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
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
     * 테스트 목적: IP 주소로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 IP 주소의 로그만 표시
     */
    public function test_can_filter_by_ip_address()
    {
        $specificIP = '192.168.1.100';
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
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
     * 테스트 목적: 세션 ID로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 세션 ID의 로그만 표시
     */
    public function test_can_filter_by_session_id()
    {
        $specificSession = 'test-session-id';
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'session_id' => $specificSession
        ]);

        $response = $this->get($this->baseUrl . '?session_id=' . $specificSession);

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 심각도로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 심각도의 로그만 표시
     */
    public function test_can_filter_by_severity()
    {
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'severity' => 'high'
        ]);

        $response = $this->get($this->baseUrl . '?severity=high');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 수행자 유형으로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 수행자 유형의 로그만 표시
     */
    public function test_can_filter_by_performed_by_type()
    {
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'performed_by_type' => 'admin'
        ]);

        $response = $this->get($this->baseUrl . '?performed_by_type=admin');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 운영 이름으로 시스템 운영 로그를 필터링할 수 있는지 검증
     * 예상 결과: 200 상태 코드, 지정된 운영 이름의 로그만 표시
     */
    public function test_can_filter_by_operation_name()
    {
        SystemOperationLog::factory()->create([
            'performed_by_id' => $this->adminUser->id,
            'operation_name' => 'Test Operation'
        ]);

        $response = $this->get($this->baseUrl . '?operation_name=Test');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertGreaterThan(0, $rows->count());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 페이지네이션이 정상적으로 작동하는지 검증
     * 예상 결과: 200 상태 코드, 페이지별 데이터 분할, 다음 페이지 존재 여부 확인
     */
    public function test_operation_logs_pagination()
    {
        // 25개의 로그 생성 (페이지당 15개)
        SystemOperationLog::factory()->count(25)->create([
            'performed_by_id' => $this->adminUser->id
        ]);

        $response = $this->get($this->baseUrl . '?page=2');

        $response->assertStatus(200);
        
        $rows = $response->viewData('rows');
        $this->assertTrue($rows->hasMorePages());
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 통계의 정확성이 검증되는지 확인
     * 예상 결과: 200 상태 코드, 통계 데이터가 실제 데이터와 일치
     */
    public function test_operation_stats_accuracy()
    {
        // 정확한 통계를 위한 테스트 데이터 생성
        SystemOperationLog::factory()->count(5)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'success',
            'operation_type' => 'create'
        ]);
        
        SystemOperationLog::factory()->count(3)->create([
            'performed_by_id' => $this->adminUser->id,
            'status' => 'failed',
            'operation_type' => 'update'
        ]);

        $response = $this->getJson($this->baseUrl . '/api/stats');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(8, $data['total_operations']);
        $this->assertEquals(5, $data['success_count']);
        $this->assertEquals(3, $data['failed_count']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 시스템 운영 로그 활동이 정상적으로 기록되는지 검증
     * 예상 결과: 데이터베이스에 로그 조회 활동이 기록됨
     */
    public function test_activity_logging()
    {
        $this->get($this->baseUrl);

        // Activity Log가 생성되었는지 확인
        $this->assertDatabaseHas('admin_activity_logs', [
            'admin_user_id' => $this->adminUser->id,
            'action' => 'list',
            'resource_type' => 'system_operation_log',
        ]);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 권한이 없는 사용자의 접근을 제한하는지 검증
     * 예상 결과: 로그인 페이지로 리다이렉트, 인증 필요
     */
    public function test_unauthorized_access()
    {
        // 로그아웃
        auth('admin')->logout();

        $response = $this->get($this->baseUrl);

        $response->assertRedirect('/admin/login');
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 존재하지 않는 로그 ID에 대한 처리가 적절한지 검증
     * 예상 결과: 404 상태 코드, 존재하지 않는 리소스 오류
     */
    public function test_invalid_id_access()
    {
        $invalidId = 99999;

        $response = $this->get($this->baseUrl . '/' . $invalidId);

        $response->assertStatus(404);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 잘못된 일괄 삭제 요청에 대한 처리가 적절한지 검증
     * 예상 결과: 422 상태 코드, 유효성 검사 오류
     */
    public function test_invalid_bulk_delete_request()
    {
        $response = $this->postJson($this->baseUrl . '/bulk-delete', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ids']);
    }

    /** 
     * @test 
     * 
     * 테스트 목적: 존재하지 않는 로그들의 일괄 삭제 시도에 대한 처리가 적절한지 검증
     * 예상 결과: 200 상태 코드, 성공 응답 (존재하지 않는 로그는 무시)
     */
    public function test_bulk_delete_nonexistent_logs()
    {
        $nonexistentIds = [99999, 99998, 99997];

        $response = $this->postJson($this->baseUrl . '/bulk-delete', [
            'ids' => $nonexistentIds
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}

