<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 관리자 활동(행동) 로그 테이블 생성
     * - admin_activity_logs: 실시간 감시 및 책임 추적
     */
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('관리자 ID (admin_users 테이블 참조, 활동 주체)');
            $table->string('action')->comment('활동 타입 (create, update, delete, login, logout 등)');
            $table->string('module')->comment('모듈명 (users, system, settings 등)');
            $table->string('description')->comment('활동 설명');
            $table->string('ip_address')->nullable()->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트');
            $table->string('target_type')->nullable()->comment('대상 타입');
            $table->unsignedBigInteger('target_id')->nullable()->comment('대상 ID');
            $table->json('old_values')->nullable()->comment('이전 값');
            $table->json('new_values')->nullable()->comment('새 값');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low')->comment('중요도');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');
            $table->timestamps();
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
}; 