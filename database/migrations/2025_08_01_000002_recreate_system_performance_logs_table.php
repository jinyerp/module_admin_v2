<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 성능 로그 테이블을 웹 애플리케이션 성능 모니터링에 맞게 재생성
     */
    public function up(): void
    {
        // 기존 테이블 삭제
        Schema::dropIfExists('system_performance_logs');

        // 새로운 테이블 생성
        Schema::create('system_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name')->comment('메트릭명 (request_time, memory_usage, db_query_time 등)');
            $table->string('metric_type')->comment('메트릭 타입 (web, database, cache, memory, error)');
            $table->decimal('value', 10, 4)->comment('값 (측정된 성능 수치)');
            $table->string('unit')->comment('단위 (ms, MB, count 등)');
            $table->string('threshold')->nullable()->comment('임계값 (경고/알림 기준)');
            $table->enum('status', ['normal', 'warning', 'critical'])->default('normal')->comment('상태');
            $table->string('endpoint')->nullable()->comment('API 엔드포인트 또는 페이지 URL');
            $table->string('method')->nullable()->comment('HTTP 메서드 (GET, POST 등)');
            $table->string('user_agent')->nullable()->comment('사용자 에이전트');
            $table->string('ip_address')->nullable()->comment('클라이언트 IP');
            $table->string('session_id')->nullable()->comment('세션 ID');
            $table->json('additional_data')->nullable()->comment('추가 데이터 (JSON 형태)');
            $table->timestamp('measured_at')->comment('측정 시각');
            $table->timestamps();

            // 성능 향상을 위한 인덱스
            $table->index(['metric_name', 'created_at']);
            $table->index(['metric_type', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('measured_at');
            $table->index('endpoint');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_performance_logs');
    }
}; 