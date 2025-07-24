<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 운영 로그 테이블 생성
     *
     * 이 테이블은 시스템의 모든 운영 활동을 상세히 기록합니다.
     * - 사용자 및 관리자의 모든 시스템 활동 추적 (감사 추적)
     * - 운영 타입별 분류 및 성능 모니터링 (시스템 분석)
     * - 보안 관련 정보 수집 (IP 주소, 세션 ID, 사용자 에이전트)
     * - 에러 및 예외 상황 기록 (문제 해결)
     * - 실행 시간 측정으로 성능 분석 (성능 최적화)
     *
     * 보안 목적: 시스템 접근 및 사용 패턴 분석, 보안 사고 대응
     *
     * 도메인 지식:
     * - 운영 로그는 시스템 보안과 성능 모니터링의 핵심
     * - 다형성 관계는 다양한 사용자 타입을 지원
     * - 실행 시간은 성능 병목 지점 식별에 활용
     * - 중요도 분류는 로그 분석과 알림 시스템에 필수
     */
    public function up(): void
    {
        Schema::create('system_operation_logs', function (Blueprint $table) {
            $table->id(); // 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->string('operation_type')->comment('운영 타입 (login, logout, create, update, delete, etc., 활동 분류)');
            $table->string('operation_name')->comment('운영명 (구체적인 작업명, 상세 활동)');
            $table->string('performed_by_type')->comment('수행자 타입 (User, Admin, System, 다형성 관계)');
            $table->unsignedBigInteger('performed_by_id')->comment('수행자 ID (다형성 관계, 실제 수행자)');
            $table->string('target_type')->nullable()->comment('대상 타입 (다형성 관계, 작업 대상)');
            $table->unsignedBigInteger('target_id')->nullable()->comment('대상 ID (다형성 관계, 구체적인 대상)');
            $table->string('ip_address')->nullable()->comment('IP 주소 (보안 감사, 접근 추적)');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트 (디바이스 정보, 브라우저 분석)');
            $table->string('session_id')->nullable()->comment('세션 ID (세션 추적, 보안 분석)');
            $table->json('request_data')->nullable()->comment('요청 데이터 (JSON 형태, 상세 요청 정보)');
            $table->json('response_data')->nullable()->comment('응답 데이터 (JSON 형태, 상세 응답 정보)');
            $table->enum('status', ['success', 'failed', 'partial'])->default('success')->comment('상태 (success: 성공, failed: 실패, partial: 부분 성공, 작업 결과)');
            $table->text('error_message')->nullable()->comment('에러 메시지 (실패 시 상세 에러 정보)');
            $table->integer('execution_time')->nullable()->comment('실행 시간 (ms, 성능 측정)');
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('info')->comment('중요도 (info: 정보, warning: 경고, error: 오류, critical: 치명적, 로그 우선순위)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 로그 이력)

            // 운영 타입별 생성일 조회 성능 향상 (복합 인덱스, 타입별 로그 분석)
            $table->index(['operation_type', 'created_at']);
            // 수행자별 조회 성능 향상 (복합 인덱스, 사용자별 활동 추적)
            $table->index(['performed_by_type', 'performed_by_id']);
            // 대상별 조회 성능 향상 (복합 인덱스, 대상별 활동 추적)
            $table->index(['target_type', 'target_id']);
            // 상태별 생성일 조회 성능 향상 (복합 인덱스, 성공/실패 분석)
            $table->index(['status', 'created_at']);
            // 중요도별 조회 성능 향상 (알림 시스템)
            $table->index('severity');
            // 세션별 조회 성능 향상 (세션 추적)
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 운영 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('system_operation_logs');
    }
};
