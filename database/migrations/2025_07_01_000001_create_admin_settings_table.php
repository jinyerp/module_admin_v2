<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 설정 테이블 생성
     *
     * 이 테이블은 시스템의 모든 설정값을 중앙에서 관리합니다.
     * - 애플리케이션 설정 (데이터베이스 기반 설정 관리)
     * - 설정 그룹별 분류 및 관리 (조직화된 설정 관리)
     * - 설정값 타입 관리 (string, boolean, integer, json 등)
     * - 공개/비공개 설정 구분 (보안 강화)
     * - 설정 설명 및 메타데이터 관리 (설정 이해도 향상)
     *
     * 예시 설정: site_name, maintenance_mode, email_settings, payment_gateway 등
     *
     * 도메인 지식:
     * - 중앙화된 설정 관리는 시스템 유지보수의 핵심
     * - 설정 그룹화는 복잡한 시스템의 설정을 체계적으로 관리
     * - 타입 관리는 설정값의 유효성 검증과 안전한 처리를 보장
     * - 공개/비공개 구분은 보안과 사용자 경험의 균형
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id(); // 설정 고유 식별자 (Primary Key, Auto Increment)
            $table->string('key')->unique()->comment('설정 키 (고유 식별자, 예: site_name, maintenance_mode, 설정 접근)');
            $table->text('value')->nullable()->comment('설정 값 (실제 설정 데이터, 다양한 타입 지원)');
            $table->string('type')->default('string')->comment('값 타입 (string, boolean, integer, json, array, 데이터 검증)');
            $table->string('group')->nullable()->comment('설정 그룹 (general, email, payment, security 등, 조직화된 관리)');
            $table->text('description')->nullable()->comment('설명 (설정에 대한 상세 설명, 관리자용 참고)');
            $table->boolean('is_public')->default(false)->comment('공개 여부 (true: 공개, false: 비공개, 보안 정책)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 설정 변경 이력)

            // 설정 그룹별 조회 성능 향상 (그룹별 설정 관리)
            $table->index('group');
            // 공개 설정별 조회 성능 향상 (공개 설정만 표시)
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 설정 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
