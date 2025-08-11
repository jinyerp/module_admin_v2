<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_country', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('enable')->default(true)->comment('활성화 여부');
            $table->string('code', 3)->unique()->comment('국가 코드 (ISO 3166-1 alpha-3)');
            $table->string('name')->comment('국가명');
            $table->string('flag')->nullable()->comment('국기 이미지 파일명');

            $table->decimal('latitude', 10, 8)->nullable()->comment('위도');
            $table->decimal('longitude', 11, 8)->nullable()->comment('경도');

            $table->string('lang', 5)->nullable()->comment('주요 언어 코드');

            $table->text('description')->nullable()->comment('국가 설명');

            $table->string('continent')->nullable()->comment('대륙 정보');
            $table->string('continent_manager')->nullable()->comment('대륙(지역) 총괄 관리자');
            $table->string('continent_manager_email')->nullable()->comment('대륙(지역) 총괄 관리자 이메일');
            
            // 인덱스 추가
            $table->index('enable');
            $table->index('continent');
        });

        // 시더 실행
        $seeder = new \Jiny\Admin\Database\Seeders\AdminCountrySeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_country');
    }
}
