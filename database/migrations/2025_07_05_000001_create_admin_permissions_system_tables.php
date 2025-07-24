<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 관리자 권한 정의 테이블 생성
     * - admin_permissions: 시스템 내 모든 권한 정의
     */
    public function up(): void
    {
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('권한명 (예: user.create)');
            $table->string('display_name')->comment('표시명 (예: 사용자 생성)');
            $table->text('description')->nullable()->comment('권한 설명');
            $table->string('module')->comment('모듈명');
            $table->boolean('is_active')->default(true)->comment('활성화 상태');
            $table->integer('sort_order')->default(0)->comment('정렬 순서');
            $table->timestamps();
            $table->index(['module', 'is_active']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
    }
}; 