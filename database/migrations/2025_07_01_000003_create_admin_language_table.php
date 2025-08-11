<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_language', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('enable')->default(true)->comment('활성화 여부');
            $table->boolean('is_default')->default(false)->comment('기본 언어 여부');
            
            $table->string('code', 5)->unique()->comment('언어 코드 (ISO 639-1)');
            $table->string('name')->comment('언어명');
            $table->string('flag')->nullable()->comment('국기 이미지 파일명');

            // 단일국가 언어
            $table->string('country', 3)->nullable()->comment('주요 사용 국가 코드');

            $table->integer('users')->nullable()->comment('사용자 수');
            $table->decimal('users_percent', 5, 2)->nullable()->comment('사용자 비율 (%)');
            
            // 인덱스 추가
            $table->index('enable');
            $table->index('is_default');
            $table->index('country');
        });

        // 시더 실행
        $seeder = new \Jiny\Admin\Database\Seeders\AdminLanguageSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_language');
    }
}
