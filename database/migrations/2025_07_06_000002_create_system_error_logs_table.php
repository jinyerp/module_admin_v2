<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 에러 로그 테이블 생성
     *
     * 이 테이블은 시스템에서 발생하는 모든 에러를 기록합니다.
     * - 애플리케이션 에러 추적 및 디버깅 (개발 및 운영)
     * - 에러 타입별 분류 및 중요도 관리 (우선순위 결정)
     * - 스택 트레이스 및 에러 발생 위치 정보 (문제 진단)
     * - 에러 발생 시점의 사용자 및 요청 정보 (컨텍스트 분석)
     * - 에러 해결 상태 관리 (작업 추적)
     * - 에러 해결 담당자 및 해결 노트 (책임 소재 및 지식 관리)
     *
     * 운영 목적: 시스템 안정성 모니터링 및 문제 해결
     *
     * 도메인 지식:
     * - 에러 로그는 시스템 안정성과 사용자 경험의 핵심 지표
     * - 스택 트레이스는 개발자의 디버깅에 필수 정보
     * - 중요도 분류는 운영 우선순위 결정에 활용
     * - 해결 노트는 지식 축적과 재발 방지에 중요
     */
    public function up(): void
    {
        Schema::create('system_error_logs', function (Blueprint $table) {
            $table->id(); // 에러 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->string('error_code')->nullable()->comment('에러 코드 (시스템 내부 에러 코드, 분류 및 필터링용)');
            $table->string('error_type')->comment('에러 타입 (Exception, Error, Warning 등, 에러 분류)');
            $table->string('error_message')->comment('에러 메시지 (사용자 친화적인 에러 설명, 문제 진단용)');
            $table->text('stack_trace')->nullable()->comment('스택 트레이스 (에러 발생 경로, 개발자 디버깅용)');
            $table->string('file')->nullable()->comment('파일명 (에러가 발생한 파일 경로, 코드 위치 식별)');
            $table->integer('line')->nullable()->comment('라인 번호 (에러가 발생한 코드 라인, 정확한 위치)');
            $table->string('function')->nullable()->comment('함수명 (에러가 발생한 함수명, 함수별 에러 분석)');
            $table->string('class')->nullable()->comment('클래스명 (에러가 발생한 클래스명, 클래스별 에러 분석)');
            $table->string('user_type')->nullable()->comment('사용자 타입 (User, Admin, Guest, 사용자별 에러 분석)');
            $table->unsignedBigInteger('user_id')->nullable()->comment('사용자 ID (에러 발생 시점의 사용자, 개별 사용자 문제 추적)');
            $table->string('ip_address')->nullable()->comment('IP 주소 (에러 발생 시점의 클라이언트 IP, 지리적 분석)');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트 (브라우저/클라이언트 정보, 환경별 에러 분석)');
            $table->string('url')->nullable()->comment('요청 URL (에러가 발생한 페이지 URL, 페이지별 에러 분석)');
            $table->string('method')->nullable()->comment('HTTP 메서드 (GET, POST, PUT, DELETE 등, 요청 방식별 분석)');
            $table->json('request_data')->nullable()->comment('요청 데이터 (POST 데이터, 쿼리 파라미터 등, 입력값 분석)');
            $table->json('session_data')->nullable()->comment('세션 데이터 (사용자 세션 정보, 상태별 에러 분석)');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('중요도 (low: 낮음, medium: 보통, high: 높음, critical: 매우 높음, 우선순위 결정)');
            $table->boolean('is_resolved')->default(false)->comment('해결 여부 (true: 해결됨, false: 미해결, 작업 추적)');
            $table->text('resolution_notes')->nullable()->comment('해결 노트 (에러 해결 과정 및 방법, 지식 축적)');
            $table->unsignedBigInteger('resolved_by')->nullable()->comment('해결한 관리자 ID (admin_emails 테이블 참조, 책임 소재)');
            $table->timestamp('resolved_at')->nullable()->comment('해결 시각 (에러가 해결된 시점, 해결 시간 추적)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 에러 발생 시점)

            // 외래키 제약조건: 사용자나 관리자가 삭제되어도 에러 로그는 유지 (데이터 보존)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('admin_users')->onDelete('set null');
            // 에러 타입별 조회 성능 향상 (복합 인덱스, 에러 유형별 분석)
            $table->index(['error_type', 'created_at']);
            // 중요도별 조회 성능 향상 (복합 인덱스, 우선순위별 처리)
            $table->index(['severity', 'created_at']);
            // 사용자별 조회 성능 향상 (복합 인덱스, 사용자별 에러 이력)
            $table->index(['user_type', 'user_id']);
            // 해결 상태별 조회 성능 향상 (미해결 에러 우선 처리)
            $table->index('is_resolved');
            // 에러 코드별 조회 성능 향상 (특정 에러 패턴 분석)
            $table->index('error_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 에러 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('system_error_logs');
    }
};
