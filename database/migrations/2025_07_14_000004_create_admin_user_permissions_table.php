<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 관리자별 권한 할당 테이블 생성
     * - admin_user_permissions: 관리자별 권한 할당(권한 부여/회수 이력 포함)
     */
    public function up(): void
    {
        Schema::create('admin_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade')->comment('권한을 받을 관리자 ID');
            $table->foreignId('permission_id')->constrained('admin_permissions')->onDelete('cascade')->comment('부여할 권한 ID');
            $table->foreignId('granted_by')->nullable()->constrained('admin_users')->onDelete('set null')->comment('권한 부여자 ID');
            $table->timestamp('granted_at')->nullable()->comment('권한 부여 시각');
            $table->timestamp('expires_at')->nullable()->comment('권한 만료 시각');
            $table->text('reason')->nullable()->comment('권한 부여 사유');
            $table->boolean('is_active')->default(true)->comment('활성화 상태');
            $table->timestamps();
            $table->unique(['admin_user_id', 'permission_id']);
            $table->index(['admin_user_id', 'is_active']);
            $table->index(['permission_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_permissions');
    }
}; 