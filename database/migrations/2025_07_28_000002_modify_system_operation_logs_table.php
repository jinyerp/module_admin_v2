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
        Schema::table('system_operation_logs', function (Blueprint $table) {
            // 기존 컬럼들 삭제
            $table->dropColumn(['user_agent', 'request_data', 'response_data']);
            
            // severity 컬럼 수정 (CHECK 제약 조건 제거)
            $table->string('severity', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_operation_logs', function (Blueprint $table) {
            // 삭제된 컬럼들 복원
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            
            // severity 컬럼을 enum으로 복원
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->change();
        });
    }
};

