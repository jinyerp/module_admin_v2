<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자 활동 로그 테이블 생성
     *
     * 이 테이블은 관리자의 모든 활동을 추적하고 기록합니다.
     * - 관리자 행동 분석 및 감사 추적 (규정 준수)
     * - 보안 감사 및 권한 남용 탐지 (보안 강화)
     * - 관리자별 활동 이력 관리 (책임 소재 명확화)
     * - 모듈별 활동 분류 (사용자 관리, 시스템 설정 등)
     * - 중요도별 로그 분류 (low, medium, high, critical)
     * - 변경 전후 값 비교 (old_values, new_values)
     *
     * 보안 목적: 관리자 활동의 완전한 추적 및 책임 소재 명확화
     *
     * 도메인 지식:
     * - 관리자 활동 로그는 보안 감사와 규정 준수의 핵심 요소
     * - 변경 전후 값 비교는 데이터 무결성 검증에 중요
     * - 중요도 분류는 보안 사고 대응과 우선순위 결정에 활용
     * - IP 주소 추적은 비정상 접근 탐지에 필수
     */
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id(); // 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->unsignedBigInteger('admin_id')->comment('관리자 ID (admin_emails 테이블 참조, 활동 주체)');
            $table->string('action')->comment('활동 타입 (create, update, delete, login, logout 등, 시스템 내부 분류)');
            $table->string('module')->comment('모듈명 (users, system, settings, payments 등, 기능별 분류)');
            $table->string('description')->comment('활동 설명 (사용자 친화적인 활동 설명, 관리자용 참고)');
            $table->string('ip_address')->nullable()->comment('IP 주소 (활동 발생 시점의 클라이언트 IP, 보안 감사용)');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트 (브라우저/클라이언트 정보, 디바이스 분석)');
            $table->string('target_type')->nullable()->comment('대상 타입 (User, System, Setting 등, 활동 대상 분류)');
            $table->unsignedBigInteger('target_id')->nullable()->comment('대상 ID (활동이 수행된 대상의 ID, 구체적인 대상 식별)');
            $table->json('old_values')->nullable()->comment('이전 값 (변경 전 데이터, JSON 형태, 데이터 무결성 검증)');
            $table->json('new_values')->nullable()->comment('새 값 (변경 후 데이터, JSON 형태, 변경 내용 추적)');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low')->comment('중요도 (low: 낮음, medium: 보통, high: 높음, critical: 매우 높음, 보안 우선순위)');
            $table->json('metadata')->nullable()->comment('추가 메타데이터 (JSON 형태로 상세 정보 저장, 확장 가능한 구조)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 활동 시점 기록)

            // 외래키 제약조건: 관리자가 삭제되어도 로그는 유지 (감사 목적, 데이터 보존)
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            // 관리자별 활동 조회 성능 향상 (복합 인덱스, 관리자별 활동 이력 조회)
            $table->index(['admin_id', 'created_at']);
            // 활동 타입별 조회 성능 향상 (복합 인덱스, 특정 활동 유형별 분석)
            $table->index(['action', 'created_at']);
            // 모듈별 조회 성능 향상 (복합 인덱스, 기능별 활동 분석)
            $table->index(['module', 'created_at']);
            // 대상별 조회 성능 향상 (복합 인덱스, 특정 대상에 대한 활동 이력)
            $table->index(['target_type', 'target_id']);
            // 중요도별 조회 성능 향상 (보안 사고 대응 및 우선순위 결정)
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 관리자 활동 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
