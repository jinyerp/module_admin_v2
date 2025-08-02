<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 권한 시스템 단순화
     * - 복잡한 권한 테이블들 제거
     * - admin_levels 기반의 단순한 권한 시스템으로 통합
     */
    public function up(): void
    {
        // 복잡한 권한 테이블들 제거
        Schema::dropIfExists('admin_user_permissions');
        Schema::dropIfExists('admin_permissions');
        
        // admin_permission_logs 테이블을 단순화
        Schema::dropIfExists('admin_permission_logs');
        
        // 단순화된 권한 로그 테이블 생성
        Schema::create('admin_permission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('권한 활동 수행자');
            $table->string('action')->comment('수행한 액션 (create, read, update, delete, list)');
            $table->string('resource_type')->comment('리소스 타입 (level, user, country 등)');
            $table->unsignedBigInteger('resource_id')->nullable()->comment('리소스 ID');
            $table->enum('result', ['success', 'denied', 'failed'])->comment('결과');
            $table->string('ip_address')->nullable()->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트');
            $table->text('reason')->nullable()->comment('사유');
            $table->timestamps();
            
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
            $table->index(['action', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permission_logs');
        
        // 기존 복잡한 테이블들 복원 (필요시)
        // Schema::create('admin_permissions', ...);
        // Schema::create('admin_user_permissions', ...);
    }
}; 