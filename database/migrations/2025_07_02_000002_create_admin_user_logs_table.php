<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * 관리자 로그인/로그아웃 및 시도 기록 테이블
     * - 보안 및 감사, 이상행위 탐지 용도
     */
    public function up(): void
    {
        Schema::create('admin_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id'); // 로그 고유 식별자 (auto-increment)
            $table->unsignedBigInteger('admin_user_id'); // 관리자 회원 ID
            $table->string('action', 50)->nullable()->comment('수행된 액션');
            $table->string('table_name', 100)->nullable()->comment('대상 테이블명');
            $table->unsignedBigInteger('record_id')->nullable()->comment('대상 레코드 ID');
            $table->string('ip_address', 45)->nullable()->comment('로그인 시도 IP');
            $table->string('user_agent', 512)->nullable()->comment('브라우저/클라이언트 정보');
            $table->string('status', 16)->default('success')->comment('success, fail');
            $table->string('message')->nullable()->comment('실패 사유 등');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('admin_user_id');
            $table->index('action');
            $table->index('table_name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_logs');
    }
};
