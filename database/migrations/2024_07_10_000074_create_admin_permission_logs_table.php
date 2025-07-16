<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자 권한 로그 테이블 생성
     *
     * 이 테이블은 관리자의 모든 권한 관련 활동을 상세히 기록합니다.
     * - 관리자 권한 부여/회수 이력 추적 (권한 관리)
     * - 권한 체크 및 접근 거부 기록 (보안 감사)
     * - 리소스별 권한 활동 추적 (세분화된 권한 관리)
     * - 보안 관련 정보 수집 (IP 주소, 사용자 에이전트)
     * - 권한 변경 사유 및 컨텍스트 정보 기록 (책임 소재)
     *
     * 보안 목적: 관리자 권한 사용 패턴 분석, 보안 사고 대응
     *
     * 도메인 지식:
     * - 권한 로그는 관리자 보안과 책임 소재의 핵심
     * - 권한 액션 분류는 권한 관리 정책 수립에 활용
     * - 리소스별 추적은 세분화된 보안 정책 구현에 필수
     * - 결과 기록은 권한 시스템의 정확성 검증에 중요
     */
    public function up(): void
    {
        Schema::create('admin_permission_logs', function (Blueprint $table) {
            $table->id(); // 권한 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->unsignedBigInteger('admin_id')->comment('관리자 ID (권한 활동 수행자)');
            $table->string('permission_name')->comment('권한명 (구체적인 권한, 세분화된 추적)');
            $table->string('resource_type')->comment('리소스 타입 (권한 대상, 다형성 관계)');
            $table->unsignedBigInteger('resource_id')->nullable()->comment('리소스 ID (다형성 관계, 구체적인 대상)');
            $table->enum('action', ['grant', 'revoke', 'check', 'deny'])->comment('권한 액션 (grant: 부여, revoke: 회수, check: 체크, deny: 거부, 권한 활동 유형)');
            $table->enum('result', ['success', 'failed', 'denied'])->comment('결과 (success: 성공, failed: 실패, denied: 거부됨, 권한 처리 결과)');
            $table->string('ip_address')->nullable()->comment('IP 주소 (보안 감사, 접근 추적)');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트 (디바이스 정보, 브라우저 분석)');
            $table->text('reason')->nullable()->comment('사유 (권한 변경 이유, 관리자 참고)');
            $table->json('context')->nullable()->comment('컨텍스트 정보 (JSON 형태, 상세 상황 정보)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 권한 활동 이력)

            // 외래키 제약조건: 관리자가 삭제되면 권한 로그도 함께 삭제 (데이터 무결성 보장)
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            // 관리자별 권한 활동 조회 성능 향상 (복합 인덱스, 관리자별 활동 추적)
            $table->index(['admin_id', 'created_at']);
            // 권한별 액션 조회 성능 향상 (복합 인덱스, 권한별 활동 분석)
            $table->index(['permission_name', 'action']);
            // 리소스별 권한 활동 조회 성능 향상 (복합 인덱스, 리소스별 권한 추적)
            $table->index(['resource_type', 'resource_id']);
            // 결과별 조회 성능 향상 (권한 처리 결과 분석)
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 관리자 권한 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_permission_logs');
    }
};
