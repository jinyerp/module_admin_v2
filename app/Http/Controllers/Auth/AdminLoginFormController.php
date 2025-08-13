<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 로그인 폼 컨트롤러
 * 
 * 관리자 로그인 폼을 표시하고, 최초 관리자 설정이 필요한 경우를 처리합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminLoginForm.md
 * 
 * 테스트 파일 작성 시 참조하세요.
 * @test jiny/admin/tests/Feature/Auth/AdminLoginFormTest.php
 * 
 * 관련 라우트 정보:
 * @route jiny/admin/routes/web.php - admin.login (GET /admin/login)
 */
class AdminLoginFormController extends Controller
{
    /**
     * 뷰 경로 변수들
     */
    protected string $loginView = 'jiny-admin::auth.login';
    protected string $setupView = 'jiny-admin::setup.setup2';

    /**
     * 로그인 폼 출력
     * 
     * 관리자 로그인 폼을 표시합니다. 최초 관리자 설정이 필요한 경우
     * 설정 페이지로 리다이렉트합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * 
     * @route admin.login (GET /admin/login)
     * @middleware web, admin.guest
     * 
     * 동작 과정:
     * 1. 데이터베이스에 관리자 계정 존재 여부 확인
     * 2. 관리자가 없으면 설정 페이지로 리다이렉트
     * 3. 관리자가 있으면 로그인 폼 표시
     * 
     * 반환값:
     * - 관리자 없음: 설정 페이지로 리다이렉트
     * - 관리자 있음: 로그인 폼 뷰 렌더링
     */
    public function showLoginForm()
    {
        // 최초 관리자 설정
        // 등록된 관리자 회원이 없는 경우 설정 페이지로 이동
        if (DB::table('admin_users')->count() == 0) {
            return redirect()->route('admin.setup');
        }

        return view($this->loginView, [
            'register_enabled' => false
        ]);
    }
}
