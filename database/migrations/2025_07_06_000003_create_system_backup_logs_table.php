<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 시스템 백업 로그 테이블 생성
     *
     * 이 테이블은 시스템의 모든 백업 활동을 상세히 기록합니다.
     * - 데이터베이스 및 파일 백업 이력 추적 (데이터 보호)
     * - 백업 성공/실패 상태 및 성능 모니터링 (백업 관리)
     * - 백업 파일 무결성 검증 (체크섬, 파일 크기)
     * - 백업 보안 설정 관리 (암호화, 압축)
     * - 백업 저장 위치 및 접근 권한 관리 (보안 강화)
     *
     * 보안 목적: 데이터 보호, 재해 복구, 규정 준수
     *
     * 도메인 지식:
     * - 백업 로그는 데이터 보호와 비즈니스 연속성의 핵심
     * - 체크섬 검증은 백업 파일 무결성 보장에 필수
     * - 암호화 및 압축은 백업 보안과 저장 공간 효율성에 중요
     * - 백업 성능 모니터링은 시스템 최적화에 활용
     */
    public function up(): void
    {
        Schema::create('system_backup_logs', function (Blueprint $table) {
            $table->id(); // 백업 로그 고유 식별자 (Primary Key, Auto Increment)
            $table->string('backup_type')->comment('백업 타입 (database, files, full, 백업 범위)');
            $table->string('backup_name')->comment('백업명 (고유 백업 식별자)');
            $table->string('file_path')->nullable()->comment('파일 경로 (백업 파일 위치)');
            $table->string('file_size')->nullable()->comment('파일 크기 (백업 파일 용량)');
            $table->string('checksum')->nullable()->comment('체크섬 (파일 무결성 검증)');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'cancelled'])->comment('상태 (pending: 대기, in_progress: 진행중, completed: 완료, failed: 실패, cancelled: 취소, 백업 생명주기)');
            $table->timestamp('started_at')->nullable()->comment('시작 시각 (백업 시작 시점)');
            $table->timestamp('completed_at')->nullable()->comment('완료 시각 (백업 완료 시점)');
            $table->integer('duration_seconds')->nullable()->comment('소요 시간 (초, 백업 성능 측정)');
            $table->text('error_message')->nullable()->comment('에러 메시지 (실패 시 상세 에러 정보)');
            $table->unsignedBigInteger('initiated_by')->nullable()->comment('시작한 관리자 ID (백업 책임자)');
            $table->string('storage_location')->nullable()->comment('저장 위치 (백업 파일 저장소)');
            $table->boolean('is_encrypted')->default(false)->comment('암호화 여부 (백업 보안)');
            $table->boolean('is_compressed')->default(true)->comment('압축 여부 (저장 공간 효율성)');
            $table->json('metadata')->nullable()->comment('추가 메타데이터 (JSON 형태, 확장 가능한 구조)');
            $table->timestamps(); // 생성 및 수정 시각 (created_at, updated_at, 백업 이력)

            // 외래키 제약조건: 관리자가 삭제되어도 백업 로그는 유지 (set null, 데이터 보존)
            $table->foreign('initiated_by')->references('id')->on('admin_users')->onDelete('set null');
            // 백업 타입별 생성일 조회 성능 향상 (복합 인덱스, 타입별 백업 분석)
            $table->index(['backup_type', 'created_at']);
            // 상태별 생성일 조회 성능 향상 (복합 인덱스, 백업 상태 분석)
            $table->index(['status', 'created_at']);
            // 관리자별 백업 조회 성능 향상 (관리자별 백업 활동)
            $table->index('initiated_by');
            // 파일 경로별 조회 성능 향상 (백업 파일 관리)
            $table->index('file_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * 마이그레이션 롤백 시 시스템 백업 로그 테이블을 삭제합니다.
     * 주의: 실제 운영 환경에서는 데이터 손실을 고려해야 함
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backup_logs');
    }
};
