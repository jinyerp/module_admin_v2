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

            $table->string('enable')->default(1);

            $table->string('code');
            $table->string('name')->nullable();
            $table->string('flag')->nullable();

            $table->string('latitude')->nullable(); // 위도
            $table->string('longitude')->nullable(); // 경도

            $table->string('lang')->nullable();

            $table->text('description')->nullable();

            $table->string('continent')->nullable(); // 대륙 정보
            $table->string('continent_manager')->nullable(); // 대륙(지역) 총괄 관리자
            $table->string('continent_manager_email')->nullable(); // 대륙(지역) 총괄 관리자 이메일
        });

        
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
