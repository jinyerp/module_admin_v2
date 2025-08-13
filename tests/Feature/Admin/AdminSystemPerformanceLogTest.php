<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\SystemPerformanceLog;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * AdminSystemPerformanceLogController 테스트
 * 
 * 시스템 성능 로그 관리 기능을 테스트합니다.
 *  
 */
class AdminSystemPerformanceLogTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $systemPerformanceLog;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 관리자 사용자 생성
        $this->adminUser = AdminUser::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);

        // 시스템 성능 로그 생성
        $this->systemPerformanceLog = SystemPerformanceLog::factory()->create([
            'metric_type' => 'memory',
            'metric_name' => 'memory_usage',
            'value' => 75.5,
            'unit' => '%',
            'threshold' => '80.0',
            'status' => 'normal',
            'measured_at' => now(),
            'additional_data' => json_encode(['hostname' => 'test-server', 'ip' => '192.168.1.100'])
        ]);
    }

    /**
     * 테스트: 성능 로그 목록 조회
     */
    public function test_can_view_performance_logs_index()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs');

        $response->assertStatus(200);
        $response->assertViewIs('admin::admin.system_performance_log.index');
        $response->assertViewHas('rows');
    }

    /**
     * 테스트: 성능 로그 생성 폼 표시
     */
    public function test_can_view_create_form()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin::admin.system_performance_log.create');
    }

    /**
     * 테스트: 성능 로그 생성
     */
    public function test_can_create_performance_log()
    {
        $logData = [
            'metric_type' => 'memory',
            'metric_name' => 'memory_usage',
            'value' => 85.2,
            'unit' => '%',
            'threshold' => '90.0',
            'status' => 'warning',
            'measured_at' => now()->format('Y-m-d H:i:s'),
            'additional_data' => json_encode(['hostname' => 'test-server', 'ip' => '192.168.1.100'])
        ];

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post('/admin/systems/performance-logs', $logData);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.systems.performance-logs.index'));
        $this->assertDatabaseHas('system_performance_logs', [
            'metric_type' => 'memory',
            'metric_name' => 'memory_usage',
            'value' => 85.2
        ]);
    }

    /**
     * 테스트: 성능 로그 상세 조회
     */
    public function test_can_view_performance_log_show()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get("/admin/systems/performance-logs/{$this->systemPerformanceLog->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin::admin.system_performance_log.show');
        
        // 뷰에 전달되는 데이터 확인
        $response->assertViewHas('performanceLog');
        $response->assertViewHas('relatedLogs');
        $response->assertViewHas('metricTypes');
        $response->assertViewHas('statuses');
    }

    /**
     * 테스트: 성능 로그 수정 폼 표시
     */
    public function test_can_view_edit_form()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get("/admin/systems/performance-logs/{$this->systemPerformanceLog->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin::admin.system_performance_log.edit');
    }

    /**
     * 테스트: 성능 로그 수정
     */
    public function test_can_update_performance_log()
    {
        $updateData = [
            'metric_name' => 'memory_usage',
            'metric_type' => 'memory',
            'value' => 90.5,
            'unit' => '%',
            'status' => 'critical',
            'threshold' => '85.0',
            'measured_at' => now()->format('Y-m-d H:i:s')
        ];

        $logId = $this->systemPerformanceLog->id;
        
        // ID와 데이터 확인
        $this->assertNotNull($logId);
        $this->assertGreaterThan(0, $logId);

        $url = "/admin/systems/performance-logs/{$logId}";
        
        // URL 확인
        $this->assertStringContainsString((string)$logId, $url);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post($url, array_merge($updateData, ['_method' => 'PUT']));

        // 기본적인 응답 확인
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.systems.performance-logs.index'));
    }

    /**
     * 테스트: 성능 로그 삭제
     */
    public function test_can_delete_performance_log()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->delete("/admin/systems/performance-logs/{$this->systemPerformanceLog->id}");

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.systems.performance-logs.index'));
    }

    /**
     * 테스트: 성능 로그 필터링
     */
    public function test_can_filter_performance_logs()
    {
        // 다양한 타입의 성능 로그 생성
        SystemPerformanceLog::factory()->create(['metric_type' => 'cpu', 'status' => 'normal']);
        SystemPerformanceLog::factory()->create(['metric_type' => 'memory', 'status' => 'warning']);
        SystemPerformanceLog::factory()->create(['metric_type' => 'disk', 'status' => 'critical']);

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs?metric_type=cpu&status=normal');

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }

    /**
     * 테스트: 성능 로그 정렬
     */
    public function test_can_sort_performance_logs()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs?sort=value&direction=desc');

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }

    /**
     * 테스트: 성능 로그 일괄 삭제
     */
    public function test_can_bulk_delete_performance_logs()
    {
        $logs = SystemPerformanceLog::factory()->count(3)->create();
        $logIds = $logs->pluck('id')->toArray();

        $response = $this->actingAs($this->adminUser, 'admin')
            ->post('/admin/systems/performance-logs/bulk-delete', [
                'selected_logs' => $logIds
            ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.systems.performance-logs.index'));
    }

    /**
     * 테스트: 성능 로그 데이터 내보내기
     */
    public function test_can_export_performance_logs()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/export');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 통계 조회
     */
    public function test_can_view_performance_stats()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/stats');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 현재 성능 상태 조회
     */
    public function test_can_view_current_performance()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/current');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 히스토리 조회
     */
    public function test_can_view_performance_history()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/history');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 트렌드 조회
     */
    public function test_can_view_performance_trends()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/trends');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 알림 목록 조회
     */
    public function test_can_view_performance_alerts()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/alerts');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 분석 조회
     */
    public function test_can_view_performance_analysis()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/analysis');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 리포트 조회
     */
    public function test_can_view_performance_reports()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/reports');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 성능 로그 대시보드 조회
     */
    public function test_can_view_performance_dashboard()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs/dashboard');

        $response->assertStatus(200);
    }

    /**
     * 테스트: 권한이 없는 사용자의 접근 제한
     */
    public function test_unauthorized_user_cannot_access()
    {
        $response = $this->get('/admin/systems/performance-logs');
        $response->assertRedirect('/admin/login');
    }

    /**
     * 테스트: 성능 로그 모델 관계
     */
    public function test_performance_log_model_relationships()
    {
        $this->assertInstanceOf(SystemPerformanceLog::class, $this->systemPerformanceLog);
        $this->assertEquals('memory', $this->systemPerformanceLog->metric_type);
        $this->assertEquals('memory_usage', $this->systemPerformanceLog->metric_name);
    }

    /**
     * 테스트: 성능 로그 데이터 검증
     */
    public function test_performance_log_data_validation()
    {
        $this->assertIsFloat($this->systemPerformanceLog->value);
        $this->assertIsString($this->systemPerformanceLog->unit);
        $this->assertIsString($this->systemPerformanceLog->status);
        $this->assertIsString($this->systemPerformanceLog->additional_data);
    }

    /**
     * 테스트: 성능 로그 상태 분류
     */
    public function test_performance_log_status_classification()
    {
        $normalLog = SystemPerformanceLog::factory()->create(['status' => 'normal']);
        $warningLog = SystemPerformanceLog::factory()->create(['status' => 'warning']);
        $criticalLog = SystemPerformanceLog::factory()->create(['status' => 'critical']);

        $this->assertEquals('normal', $normalLog->status);
        $this->assertEquals('warning', $warningLog->status);
        $this->assertEquals('critical', $criticalLog->status);
    }

    /**
     * 테스트: 성능 로그 임계값 검증
     */
    public function test_performance_log_threshold_validation()
    {
        $log = SystemPerformanceLog::factory()->create([
            'value' => 85.0,
            'threshold' => 80.0,
            'status' => 'warning'
        ]);

        $this->assertGreaterThan($log->threshold, $log->value);
        $this->assertEquals('warning', $log->status);
    }

    /**
     * 테스트: 성능 로그 추가 데이터
     */
    public function test_performance_log_additional_data()
    {
        $additionalData = json_decode($this->systemPerformanceLog->additional_data, true);
        
        $this->assertIsArray($additionalData);
        $this->assertArrayHasKey('hostname', $additionalData);
        $this->assertArrayHasKey('ip', $additionalData);
        $this->assertEquals('test-server', $additionalData['hostname']);
        $this->assertEquals('192.168.1.100', $additionalData['ip']);
    }

    /**
     * 테스트: 성능 로그 시간 정보
     */
    public function test_performance_log_timing()
    {
        $this->assertInstanceOf(Carbon::class, $this->systemPerformanceLog->measured_at);
        $this->assertInstanceOf(Carbon::class, $this->systemPerformanceLog->created_at);
        $this->assertInstanceOf(Carbon::class, $this->systemPerformanceLog->updated_at);
    }

    /**
     * 테스트: 성능 로그 검색 기능
     */
    public function test_performance_log_search()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs?search=cpu');

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }

    /**
     * 테스트: 성능 로그 페이지네이션
     */
    public function test_performance_log_pagination()
    {
        // 25개의 로그 생성 (페이지당 15개)
        SystemPerformanceLog::factory()->count(25)->create();

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get('/admin/systems/performance-logs');

        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }
}
