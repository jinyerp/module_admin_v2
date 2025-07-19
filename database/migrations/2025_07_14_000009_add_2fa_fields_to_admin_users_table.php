<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * admin_users 테이블에 2FA 관련 필드 추가
     */
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            // Google Authenticator 2FA 관련 필드
            $table->string('google_2fa_secret')->nullable()->after('remember_token')->comment('Google Authenticator 시크릿 키');
            $table->boolean('google_2fa_enabled')->default(false)->after('google_2fa_secret')->comment('2FA 활성화 여부');
            $table->json('google_2fa_backup_codes')->nullable()->after('google_2fa_enabled')->comment('백업 코드 (JSON)');
            $table->timestamp('google_2fa_verified_at')->nullable()->after('google_2fa_backup_codes')->comment('2FA 설정 완료 시각');
            $table->timestamp('google_2fa_disabled_at')->nullable()->after('google_2fa_verified_at')->comment('2FA 비활성화 시각');
            
            // 인덱스 추가
            $table->index('google_2fa_enabled');
            $table->index('google_2fa_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropIndex(['google_2fa_enabled']);
            $table->dropIndex(['google_2fa_verified_at']);
            
            $table->dropColumn([
                'google_2fa_secret',
                'google_2fa_enabled',
                'google_2fa_backup_codes',
                'google_2fa_verified_at',
                'google_2fa_disabled_at'
            ]);
        });
    }
}; 