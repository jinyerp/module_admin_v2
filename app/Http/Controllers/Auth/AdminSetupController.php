<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;

class AdminSetupController extends Controller
{
    private $config;

    public function __construct()
    {
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    public function index(Request $request)
    {
        // 접속제한: 이미 관리자가 있으면 setup 접근 불가
        if (\Schema::hasTable('admin_users') && \DB::table('admin_users')->count() > 0) {
            return redirect()
                ->route('admin.login')
                ->with('message', '관리자 로그인이 필요합니다.');
        }

        return view('jiny-admin::setup.setup2', [
            'passwordRules' => $this->config['auth']['password'] ?? []
        ]);
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

        // 패스워드 규칙 검사
        $passwordRules = $this->config['auth']['password'] ?? [];
        $password = $request->input('password');
        $errors = [];
        if (isset($passwordRules['min_length']) && strlen($password) < $passwordRules['min_length']) {
            $errors[] = '비밀번호는 최소 '.$passwordRules['min_length'].'자 이상이어야 합니다.';
        }
        if (!empty($passwordRules['require_special']) && !preg_match('/[\W_]/', $password)) {
            $errors[] = '비밀번호에 특수문자가 포함되어야 합니다.';
        }
        if (!empty($passwordRules['require_number']) && !preg_match('/[0-9]/', $password)) {
            $errors[] = '비밀번호에 숫자가 포함되어야 합니다.';
        }
        if (!empty($passwordRules['require_uppercase']) && !preg_match('/[A-Z]/', $password)) {
            $errors[] = '비밀번호에 대문자가 포함되어야 합니다.';
        }
        if ($errors) {
            return redirect()->back()->withErrors($errors)->withInput();
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
            'password' => Hash::make($password),
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