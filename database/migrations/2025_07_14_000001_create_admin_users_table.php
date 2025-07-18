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
