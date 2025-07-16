<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 성능 로그 테이블 생성
     *
     * 이 테이블은 시스템의 모든 성능 메트릭을 상세히 기록합니다.
     * - CPU, 메모리, 디스크, 네트워크, 데이터베이스 성능 모니터링 (시스템 건강도)
     * - 성능 임계값 설정 및 알림 관리 (프로액티브 모니터링)
     * - 서버별 및 컴포넌트별 성능 분석 (리소스 최적화)
     * - 성능 트렌드 분석 및 예측 (용량 계획)
     * - 성능 병목 지점 식별 및 해결 (시스템 최적화)
     *
     * 운영 목적: 시스템 안정성 보장, 성능 최적화, 용량 계획
     *
     * 도메인 지식:
     * - 성능 로그는 시스템 운영과 사용자 경험의 핵심 지표
     * - 임계값 관리는 프로액티브 문제 해결에 필수
     * - 트렌드 분석은 용량 계획과 리소스 할당에 중요
     * - 컴포넌트별 분석은 성능 병목 지점 식별에 활용
     */
    public function up(): void
    {
        Schema::create('system_performance_logs', function (Blueprint $table) {
            $table->id(); // 성능 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->string('metric_name')->comment('메트릭명 (구체적인 성능 지표)');
            $table->string('metric_type')->comment('메트릭 타입 (cpu, memory, disk, network, database, 성능 분류)');
            $table->decimal('value', 10, 4)->comment('값 (측정된 성능 수치)');
            $table->string('unit')->comment('단위 (퍼센트, 바이트, 초 등)');
            $table->string('threshold')->nullable()->comment('임계값 (경고/알림 기준)');
            $table->enum('status', ['normal', 'warning', 'critical'])->default('normal')->comment('상태 (normal: 정상, warning: 경고, critical: 치명적, 성능 상태)');
            $table->string('server_name')->nullable()->comment('서버명 (측정 대상 서버)');
            $table->string('component')->nullable()->comment('컴포넌트 (측정 대상 컴포넌트)');
            $table->json('additional_data')->nullable()->comment('추가 데이터 (JSON 형태, 상세 성능 정보)');
            $table->timestamp('measured_at')->comment('측정 시각 (성능 측정 시점)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 성능 로그 이력)

            // 메트릭명별 생성일 조회 성능 향상 (복합 인덱스, 메트릭별 성능 분석)
            $table->index(['metric_name', 'created_at']);
            // 메트릭 타입별 생성일 조회 성능 향상 (복합 인덱스, 타입별 성능 분석)
            $table->index(['metric_type', 'created_at']);
            // 상태별 생성일 조회 성능 향상 (복합 인덱스, 성능 상태 분석)
            $table->index(['status', 'created_at']);
            // 측정 시각별 조회 성능 향상 (시간별 성능 추적)
            $table->index('measured_at');
            // 서버별 조회 성능 향상 (서버별 성능 분석)
            $table->index('server_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 성능 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('system_performance_logs');
    }
};
