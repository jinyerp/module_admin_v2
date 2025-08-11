<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 유지보수 로그 테이블 생성
     *
     * 이 테이블은 시스템의 모든 유지보수 활동을 상세히 기록합니다.
     * - 예정된 및 긴급 유지보수 일정 관리 (시스템 안정성)
     * - 유지보수 진행 상황 및 완료 상태 추적 (작업 관리)
     * - 다운타임 영향도 평가 및 서비스 영향 분석 (고객 서비스)
     * - 유지보수 책임자 및 작업 시간 기록 (책임 소재)
     * - 우선순위별 유지보수 관리 (리소스 최적화)
     *
     * 운영 목적: 시스템 안정성 보장, 고객 서비스 최소화, 유지보수 효율성 향상
     *
     * 도메인 지식:
     * - 유지보수 로그는 시스템 운영과 고객 서비스의 균형점
     * - 예정 vs 실제 시간 비교는 유지보수 계획 개선에 활용
     * - 영향도 평가는 고객 커뮤니케이션과 서비스 수준 결정에 중요
     * - 우선순위 관리는 리소스 할당과 위험 관리에 필수
     */
    public function up(): void
    {
        Schema::create('system_maintenance_logs', function (Blueprint $table) {
            $table->id(); // 유지보수 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->string('maintenance_type')->comment('유지보수 타입 (scheduled, emergency, upgrade, repair, 유지보수 분류)');
            $table->string('title')->comment('제목 (유지보수 작업명)');
            $table->text('description')->comment('설명 (상세 작업 내용)');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'failed'])->comment('상태 (scheduled: 예정, in_progress: 진행중, completed: 완료, cancelled: 취소, failed: 실패, 작업 생명주기)');
            $table->timestamp('scheduled_start')->nullable()->comment('예정 시작 시각 (계획된 시작 시간)');
            $table->timestamp('scheduled_end')->nullable()->comment('예정 종료 시각 (계획된 종료 시간)');
            $table->timestamp('actual_start')->nullable()->comment('실제 시작 시각 (실제 작업 시작)');
            $table->timestamp('actual_end')->nullable()->comment('실제 종료 시각 (실제 작업 완료)');
            $table->integer('duration_minutes')->nullable()->comment('소요 시간 (분, 작업 효율성 측정)');
            $table->text('notes')->nullable()->comment('노트 (작업 중 특이사항)');
            $table->text('impact_assessment')->nullable()->comment('영향도 평가 (고객 서비스 영향 분석)');
            $table->unsignedBigInteger('initiated_by')->nullable()->comment('시작한 관리자 ID (작업 책임자)');
            $table->unsignedBigInteger('completed_by')->nullable()->comment('완료한 관리자 ID (실제 완료자)');
            $table->boolean('requires_downtime')->default(false)->comment('다운타임 필요 여부 (서비스 중단 여부)');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('우선순위 (low: 낮음, medium: 보통, high: 높음, critical: 치명적, 작업 우선순위)');
            $table->json('affected_services')->nullable()->comment('영향받는 서비스 (JSON 형태, 영향 범위)');
            $table->json('metadata')->nullable()->comment('추가 메타데이터 (JSON 형태, 확장 가능한 구조)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 유지보수 이력)

            // 외래키 제약조건: 관리자가 삭제되어도 유지보수 로그는 유지 (set null, 데이터 보존)
            $table->foreign('initiated_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('completed_by')->references('id')->on('admin_users')->onDelete('set null');
            // 유지보수 타입별 생성일 조회 성능 향상 (복합 인덱스, 타입별 유지보수 분석)
            $table->index(['maintenance_type', 'created_at']);
            // 상태별 생성일 조회 성능 향상 (복합 인덱스, 유지보수 상태 분석)
            $table->index(['status', 'created_at']);
            // 예정 시간별 조회 성능 향상 (복합 인덱스, 일정 관리)
            $table->index(['scheduled_start', 'scheduled_end']);
            // 우선순위별 조회 성능 향상 (우선순위별 작업 관리)
            $table->index('priority');
            // 다운타임 필요 여부별 조회 성능 향상 (다운타임 관리)
            $table->index('requires_downtime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 유지보수 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('system_maintenance_logs');
    }
};
