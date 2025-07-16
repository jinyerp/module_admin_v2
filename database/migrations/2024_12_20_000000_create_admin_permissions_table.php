<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자 권한 테이블 생성
     *
     * 이 테이블은 시스템에서 사용할 수 있는 모든 권한을 정의합니다.
     * 각 권한은 특정 기능이나 리소스에 대한 접근 권한을 나타냅니다.
     *
     * 도메인 지식:
     * - 권한 기반 접근 제어: 세밀한 권한 관리로 보안 강화
     * - 모듈별 권한 분류: 기능별 권한 그룹화로 관리 효율성 향상
     * - 권한 설명: 각 권한의 목적과 범위를 명확히 정의
     * - 활성화 상태: 필요에 따라 권한을 비활성화하여 보안 강화
     */
    public function up(): void
    {
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();

            /**
             * 권한명
             *
             * 권한을 식별하는 고유한 이름입니다.
             * 일반적으로 'resource.action' 형태로 작성됩니다.
             *
             * 예시:
             * - user.create: 사용자 생성 권한
             * - user.update: 사용자 수정 권한
             * - user.delete: 사용자 삭제 권한
             * - country.manage: 국가 관리 권한
             * - admin.view: 관리자 조회 권한
             */
            $table->string('name')->unique()->comment('권한명 (예: user.create)');

            /**
             * 표시명
             *
             * 사용자에게 보여지는 권한의 이름입니다.
             * 한글로 작성하여 이해하기 쉽게 만듭니다.
             */
            $table->string('display_name')->comment('표시명 (예: 사용자 생성)');

            /**
             * 설명
             *
             * 권한에 대한 상세한 설명입니다.
             * 권한의 목적과 범위를 명확히 정의합니다.
             */
            $table->text('description')->nullable()->comment('권한 설명');

            /**
             * 모듈
             *
             * 권한이 속한 기능 모듈을 나타냅니다.
             * 권한을 그룹화하고 관리하기 쉽게 만듭니다.
             *
             * 예시:
             * - user: 사용자 관리
             * - country: 국가 관리
             * - admin: 관리자 관리
             * - system: 시스템 관리
             */
            $table->string('module')->comment('모듈명');

            /**
             * 활성화 상태
             *
             * 권한의 활성화 여부를 나타냅니다.
             * false인 경우 해당 권한은 사용할 수 없습니다.
             */
            $table->boolean('is_active')->default(true)->comment('활성화 상태');

            /**
             * 정렬 순서
             *
             * 권한 목록에서의 표시 순서를 결정합니다.
             */
            $table->integer('sort_order')->default(0)->comment('정렬 순서');

            $table->timestamps();

            // 인덱스
            $table->index(['module', 'is_active']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
    }
};
