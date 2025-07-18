<?php

namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;

class AdminSetupController extends Controller
{
    public function index(Request $request)
    {
        // admin_levels 테이블 존재 여부 확인
        $levelTableExists = Schema::hasTable('admin_levels');
        if (!$levelTableExists) {
            return redirect()
            ->route('admin.setup')
            ->with('message', 'admin_levels 테이블이 존재하지 않습니다. 마이그레이션을 먼저 실행하세요.');
        }
        
        // 접속제한
        // admin_users 테이블에 회원이 존재하는 경우 setup 접근 제한
        if (Schema::hasTable('admin_users') && DB::table('admin_users')->count() > 0) {
            return redirect()
                ->route('admin.login')
                ->with('message', '관리자 로그인이 필요합니다.');
        }

        // admin_users 테이블 존재 여부 확인
        $tableExists = Schema::hasTable('admin_users');
        $needsMigration = !$tableExists;
        $hasAdmin = false;

        // 테이블이 존재하고 회원이 없는 경우에만 슈퍼관리자 등록 가능
        if ($tableExists) {
            $adminCount = DB::table('admin_users')->count();
            $hasAdmin = $adminCount > 0;
            if ($adminCount === 0) {
                // 슈퍼관리자 등록 가능 상태
                $needsMigration = false;

                return view('jiny-admin::setup.setup2', [
                    'needsMigration' => $needsMigration,
                    'hasAdmin' => $hasAdmin
                ]);
            }
        }
        
        return redirect()->route('admin.login')
            ->with('message', '관리자 로그인이 필요합니다.');
    }

    public function migrate(Request $request)
    {
        // 마이그레이션 실행
        Artisan::call('migrate');
        return redirect()->route('admin.setup')->with('message', '마이그레이션이 완료되었습니다.');
    }

    public function createSuperAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Super 등급 id 조회
        $superLevelId = null;
        if (Schema::hasTable('admin_levels')) {
            $superLevelId = DB::table('admin_levels')->where('code', 'super')->value('id');
        }
        // 최초 슈퍼관리자 계정 생성
        DB::table('admin_users')->insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'type' => 'super',
            'admin_level_id' => $superLevelId,
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.login')
            ->with('message', '최초 슈퍼관리자 계정이 생성되었습니다.');
    }
} 