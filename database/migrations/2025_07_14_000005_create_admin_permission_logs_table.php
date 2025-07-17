<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 권한 로그 테이블 생성
     * - admin_permission_logs: 권한 관련 모든 활동 로그(감사, 추적, 보안)
     */
    public function up(): void
    {
        Schema::create('admin_permission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('권한 활동 수행자');
            $table->foreignId('permission_id')->constrained('admin_permissions')->onDelete('cascade')->comment('권한 ID');
            $table->string('resource_type')->nullable()->comment('리소스 타입(다형성)');
            $table->unsignedBigInteger('resource_id')->nullable()->comment('리소스 ID(다형성)');
            $table->enum('action', ['grant', 'revoke', 'check', 'deny'])->comment('권한 액션');
            $table->enum('result', ['success', 'failed', 'denied'])->comment('결과');
            $table->string('ip_address')->nullable()->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트');
            $table->text('reason')->nullable()->comment('사유');
            $table->json('context')->nullable()->comment('컨텍스트 정보');
            $table->timestamps();
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['permission_id', 'action']);
            $table->index(['resource_type', 'resource_id']);
            $table->index('result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permission_logs');
    }
}; 