<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자별 권한 할당 테이블 생성
     *
     * 이 테이블은 특정 관리자에게 특정 권한을 할당하는 관계를 관리합니다.
     * 개별 관리자에게 세밀한 권한 제어를 가능하게 합니다.
     *
     * 도메인 지식:
     * - 개별 권한 할당: 관리자별로 필요한 권한만 부여
     * - 권한 추적: 어떤 관리자가 어떤 권한을 가지고 있는지 추적
     * - 보안 강화: 최소 권한 원칙 적용으로 보안 강화
     * - 권한 변경 이력: 권한 부여/해제 시점 추적
     */
    public function up(): void
    {
        Schema::create('admin_user_permissions', function (Blueprint $table) {
            $table->id();

            /**
             * 관리자 ID
             *
             * 권한을 받을 관리자의 ID입니다.
             * admin_emails 테이블의 id를 참조합니다.
             */
            $table->uuid('admin_id')->comment('관리자 ID');

            /**
             * 권한 ID
             *
             * 부여할 권한의 ID입니다.
             * admin_permissions 테이블의 id를 참조합니다.
             */
            $table->unsignedBigInteger('permission_id')->comment('권한 ID');

            /**
             * 권한 부여자 ID
             *
             * 이 권한을 부여한 관리자의 ID입니다.
             * 권한 부여의 책임자를 추적합니다.
             */
            $table->uuid('granted_by')->nullable()->comment('권한 부여자 ID');

            /**
             * 권한 부여 시각
             *
             * 권한이 부여된 시각을 기록합니다.
             */
            $table->timestamp('granted_at')->nullable()->comment('권한 부여 시각');

            /**
             * 권한 만료 시각
             *
             * 권한이 자동으로 만료되는 시각입니다.
             * null인 경우 영구 권한을 의미합니다.
             */
            $table->timestamp('expires_at')->nullable()->comment('권한 만료 시각');

            /**
             * 권한 부여 사유
             *
             * 권한을 부여한 이유를 기록합니다.
             * 감사 및 추적 목적으로 사용됩니다.
             */
            $table->text('reason')->nullable()->comment('권한 부여 사유');

            /**
             * 활성화 상태
             *
             * 권한의 활성화 여부를 나타냅니다.
             * false인 경우 해당 권한은 사용할 수 없습니다.
             */
            $table->boolean('is_active')->default(true)->comment('활성화 상태');

            $table->timestamps();

            // 외래키 제약조건
            $table->foreign('admin_id')->references('id')->on('admin_emails')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('admin_permissions')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('admin_emails')->onDelete('set null');

            // 복합 유니크 인덱스 (한 관리자에게 같은 권한을 중복 부여 방지)
            $table->unique(['admin_id', 'permission_id']);

            // 인덱스
            $table->index(['admin_id', 'is_active']);
            $table->index(['permission_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_permissions');
    }
};
