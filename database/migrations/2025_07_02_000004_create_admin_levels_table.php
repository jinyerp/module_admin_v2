<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('badge_color')->nullable();
            $table->boolean('can_list')->default(true); // 목록 진입 권한
            $table->boolean('can_create')->default(true);
            $table->boolean('can_read')->default(true);
            $table->boolean('can_update')->default(true);
            $table->boolean('can_delete')->default(true);
            $table->timestamps();
        });

        // 기본 데이터 삽입
        DB::table('admin_levels')->insert([
            [
                'name' => 'Super',
                'code' => 'super',
                'badge_color' => 'red',
                'can_list' => true,
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'code' => 'admin',
                'badge_color' => 'blue',
                'can_list' => true,
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff',
                'code' => 'staff',
                'badge_color' => 'green',
                'can_list' => true,
                'can_create' => false,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('admin_levels');
    }
}; 