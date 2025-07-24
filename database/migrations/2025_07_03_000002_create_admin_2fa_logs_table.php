<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 2FA 관련 로그 테이블 생성
     * - admin_2fa_logs: 2FA 설정, 인증, 비활성화 등의 로그
     */
    public function up(): void
    {
        Schema::create('admin_2fa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('관리자 ID');
            $table->string('action')->comment('액션 (enable, disable, verify, backup_used 등)');
            $table->string('status')->default('success')->comment('상태 (success, fail)');
            $table->text('message')->nullable()->comment('상세 메시지');
            $table->string('ip_address')->nullable()->comment('IP 주소');
            $table->text('user_agent')->nullable()->comment('사용자 에이전트');
            $table->json('metadata')->nullable()->comment('추가 메타데이터');
            $table->timestamps();
            
            $table->index(['admin_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_2fa_logs');
    }
}; 