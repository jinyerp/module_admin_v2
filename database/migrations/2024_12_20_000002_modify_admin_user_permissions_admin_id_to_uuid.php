<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * admin_user_permissions 테이블의 admin_id와 granted_by 컬럼을 UUID로 변경
     */
    public function up(): void
    {
        Schema::table('admin_user_permissions', function (Blueprint $table) {
            // 외래키 제약조건 제거
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['granted_by']);

            // 컬럼 타입 변경
            $table->uuid('admin_id')->change();
            $table->uuid('granted_by')->nullable()->change();

            // 외래키 제약조건 다시 추가
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('admin_emails')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('admin_user_permissions', function (Blueprint $table) {
            // 외래키 제약조건 제거
            $table->dropForeign(['admin_id']);
            $table->dropForeign(['granted_by']);

            // 컬럼 타입 변경 (다시 bigint로)
            $table->unsignedBigInteger('admin_id')->change();
            $table->unsignedBigInteger('granted_by')->nullable()->change();

            // 외래키 제약조건 다시 추가
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('admin_emails')->onDelete('set null');
        });
    }
};
