<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자 메시지 테이블 생성
     *
     * 이 테이블은 관리자가 사용자에게 전송하는 메시지를 관리합니다.
     * - 관리자-사용자 간 1:1 메시지 시스템 (고객 서비스)
     * - 메시지 타입별 분류 (공지, 경고, 정보)
     * - 메시지 상태 관리 (초안, 발송, 읽음)
     * - 발송 및 읽음 시각 추적 (메시지 효과 측정)
     * - 관리자별 메시지 이력 관리 (책임 소재)
     *
     * 관계: admin_emails 테이블과 users 테이블을 연결
     *
     * 도메인 지식:
     * - 관리자 메시지는 고객 서비스와 사용자 커뮤니케이션의 핵심
     * - 메시지 타입 분류는 사용자 경험과 우선순위 결정에 중요
     * - 읽음 추적은 메시지 효과 측정과 후속 조치에 활용
     * - 전체 발송 기능은 공지사항 전파에 필수
     */
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id(); // 메시지 고유 식별자 (Primary Key, Auto Increment)
            $table->unsignedBigInteger('admin_id'); // 관리자 ID (admin_emails 테이블 참조, 메시지 발송자)
            $table->unsignedBigInteger('user_id')->nullable(); // 사용자 ID (users 테이블 참조, null: 전체 발송, 수신자)
            $table->string('title')->comment('메시지 제목 (사용자에게 표시되는 제목, 사용자 경험)');
            $table->text('content')->comment('메시지 내용 (HTML 또는 마크다운 지원, 풍부한 콘텐츠)');
            $table->enum('type', ['notice', 'warning', 'info'])->default('notice')->comment('메시지 타입 (notice: 공지, warning: 경고, info: 정보, 우선순위 결정)');
            $table->enum('status', ['draft', 'sent', 'read'])->default('draft')->comment('상태 (draft: 초안, sent: 발송, read: 읽음, 메시지 생명주기)');
            $table->timestamp('sent_at')->nullable()->comment('발송 시각 (실제 발송된 시점, 메시지 이력)');
            $table->timestamp('read_at')->nullable()->comment('읽음 시각 (사용자가 메시지를 읽은 시점, 효과 측정)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 메시지 변경 이력)

            // 외래키 제약조건: 관리자나 사용자가 삭제되면 메시지도 함께 삭제 (데이터 무결성 보장)
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // 사용자별 메시지 상태 조회 성능 향상 (복합 인덱스, 사용자별 메시지 관리)
            $table->index(['user_id', 'status']);
            // 메시지 타입별 조회 성능 향상 (타입별 메시지 분석)
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 관리자 메시지 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};
