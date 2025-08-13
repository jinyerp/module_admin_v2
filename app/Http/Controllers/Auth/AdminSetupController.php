<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Controller;

/**
 * 관리자 최초 설정 컨트롤러
 * 
 * 시스템 최초 실행 시 관리자 계정을 생성하고 초기 설정을 담당합니다.
 * 이미 관리자가 존재하는 경우 접근을 제한합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminSetup.md
 * 
 * 테스트 파일 작성 시 참조하세요.
 * @test jiny/admin/tests/Feature/Auth/AdminSetupTest.php
 * 
 * 관련 라우트 정보:
 * @route jiny/admin/routes/web.php - admin.setup, admin.setup.superadmin
 */
class AdminSetupController extends Controller
{
    /**
     * 뷰 경로 변수들
     */
    protected string $setupView = 'jiny-admin::setup.setup2';
    protected string $loginView = 'jiny-admin::auth.login';

    /**
     * 설정 관련 설정값
     */
    private $config;

    /**
     * 생성자
     * 
     * 패키지의 admin 설정을 읽어와서 초기화합니다.
     */
    public function __construct()
    {
        // 패키지의 admin config 읽어오기
        $this->config = config('admin.settings');
    }

    /**
     * 관리자 설정 페이지 표시
     * 
     * 최초 관리자 설정 페이지를 표시합니다.
     * 이미 관리자가 존재하는 경우 로그인 페이지로 리다이렉트합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * 
     * @route admin.setup (GET /admin/setup)
     * @middleware web
     * 
     * 동작 과정:
     * 1. admin_users 테이블 존재 여부 확인
     * 2. 기존 관리자 계정 수 확인
     * 3. 관리자가 있으면 로그인 페이지로 리다이렉트
     * 4. 관리자가 없으면 설정 페이지 표시
     * 
     * 반환값:
     * - 관리자 있음: 로그인 페이지로 리다이렉트
     * - 관리자 없음: 설정 페이지 뷰 렌더링
     */
    public function index(Request $request)
    {
        // 접속제한: 이미 관리자가 있으면 setup 접근 불가
        if (\Schema::hasTable('admin_users') && \DB::table('admin_users')->count() > 0) {
            return redirect()
                ->route('admin.login')
                ->with('message', '관리자 로그인이 필요합니다.');
        }

        return view($this->setupView, [
            'passwordRules' => $this->config['auth']['password'] ?? []
        ]);
    }

    /**
     * 최초 슈퍼 관리자 계정 생성
     * 
     * 시스템 최초 실행 시 슈퍼 관리자 계정을 생성합니다.
     * 비밀번호 규칙 검증과 중복 이메일 검사를 수행합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse 로그인 페이지로 리다이렉트
     * 
     * @route admin.setup.superadmin (POST /admin/setup/superadmin)
     * @middleware web
     * 
     * 동작 과정:
     * 1. 입력 데이터 유효성 검사 (이름, 이메일, 비밀번호)
     * 2. 비밀번호 규칙 검사 (길이, 특수문자, 숫자, 대문자)
     * 3. super 등급 ID 조회
     * 4. 슈퍼 관리자 계정 생성
     * 5. 로그인 페이지로 리다이렉트
     * 
     * 반환값: 로그인 페이지로 리다이렉트 (성공 메시지 포함)
     * 
     * 유효성 검사 규칙:
     * - 이름: 필수, 최소 2자
     * - 이메일: 필수, 이메일 형식, 중복 불가
     * - 비밀번호: 필수, 최소 8자, 확인과 일치
     */
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