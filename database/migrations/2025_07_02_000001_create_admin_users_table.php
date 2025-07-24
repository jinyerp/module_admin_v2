<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('type')->default('admin')->comment('관리자 등급(super, admin, staff 등)');
            $table->string('status')->default('active')->comment('상태(active, inactive, suspended 등)');
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->text('memo')->nullable();
            $table->rememberToken();

            


            // 2FA 관련 컬럼 추가
            $table->string('google_2fa_secret')->nullable()->after('remember_token')->comment('Google Authenticator 시크릿 키');
            $table->boolean('google_2fa_enabled')->default(false)->after('google_2fa_secret')->comment('2FA 활성화 여부');
            $table->json('google_2fa_backup_codes')->nullable()->after('google_2fa_enabled')->comment('백업 코드 (JSON)');
            $table->timestamp('google_2fa_verified_at')->nullable()->after('google_2fa_backup_codes')->comment('2FA 설정 완료 시각');
            $table->timestamp('google_2fa_disabled_at')->nullable()->after('google_2fa_verified_at')->comment('2FA 비활성화 시각');
            $table->boolean('google_2fa_required')->default(false)->after('google_2fa_disabled_at')->comment('2FA 강제 설정 여부');
            $table->index('google_2fa_enabled'); // 2FA 관련 인덱스 추가
            $table->index('google_2fa_verified_at');

            // Microsoft Authenticator 2FA 관련 컬럼 추가
            $table->string('ms_2fa_secret')->nullable()->after('google_2fa_required')->comment('Microsoft Authenticator 시크릿 키');
            $table->boolean('ms_2fa_enabled')->default(false)->after('ms_2fa_secret')->comment('Microsoft 2FA 활성화 여부');
            $table->json('ms_2fa_backup_codes')->nullable()->after('ms_2fa_enabled')->comment('Microsoft 백업 코드 (JSON)');
            $table->timestamp('ms_2fa_verified_at')->nullable()->after('ms_2fa_backup_codes')->comment('Microsoft 2FA 설정 완료 시각');
            $table->timestamp('ms_2fa_disabled_at')->nullable()->after('ms_2fa_verified_at')->comment('Microsoft 2FA 비활성화 시각');
            $table->boolean('ms_2fa_required')->default(false)->after('ms_2fa_disabled_at')->comment('Microsoft 2FA 강제 설정 여부');
            $table->index('ms_2fa_enabled'); // Microsoft 2FA 관련 인덱스 추가
            $table->index('ms_2fa_verified_at');


            $table->timestamps();
            $table->unsignedBigInteger('admin_level_id')->nullable()->after('type')->comment('관리자 등급 ID');
        });

        // 외래키 제약 추가
        Schema::table('admin_users', function (Blueprint $table) {
            $table->foreign('admin_level_id')->references('id')->on('admin_levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
