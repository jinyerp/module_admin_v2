<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 관리자 감사(변경이력) 로그 테이블 생성
     * - admin_audit_logs: 데이터 변경 전후 비교 및 규정 준수
     */
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('관리자 ID (admin_users 테이블 참조)');
            $table->string('action')->comment('수행된 액션 (create, update, delete, bulk_delete, etc.)');
            $table->string('table_name')->nullable()->comment('대상 테이블명');
            $table->unsignedBigInteger('record_id')->nullable()->comment('대상 레코드 ID');
            $table->json('old_values')->nullable()->comment('변경 전 값들');
            $table->json('new_values')->nullable()->comment('변경 후 값들');
            $table->string('ip_address')->nullable()->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트');
            $table->text('description')->nullable()->comment('상세 설명');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('심각도');
            $table->integer('affected_count')->nullable()->comment('영향받은 레코드 수 (bulk operations)');
            $table->timestamps();
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index(['severity', 'created_at']);
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
}; 