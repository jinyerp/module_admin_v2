<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_user_password_error', function (Blueprint $table) {
            $table->id();
            $table->string('admin_user_id'); // AdminUser의 ID (UUID)
            $table->string('email'); // 이메일 주소
            $table->string('ip_address')->nullable(); // 접속 IP 주소
            $table->text('user_agent')->nullable(); // 사용자 에이전트
            $table->timestamp('error_at'); // 오류 발생 시간
            $table->string('error_type')->default('password'); // 오류 유형 (password, account_locked, etc.)
            $table->text('error_message')->nullable(); // 오류 메시지
            $table->json('additional_data')->nullable(); // 추가 데이터 (브라우저 정보, 위치 등)
            $table->timestamps();

            // 인덱스 설정
            $table->index(['admin_user_id']);
            $table->index(['email']);
            $table->index(['ip_address']);
            $table->index(['error_at']);
            $table->index(['error_type']);

            // 외래키 제약 조건 (AdminUser 테이블과 연결)
            $table->foreign('admin_user_id')
                  ->references('id')
                  ->on('admin_users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_password_error');
    }
};
