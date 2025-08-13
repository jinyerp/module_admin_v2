<?php

namespace Jiny\Admin\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\App\Services\TwoFactorService;

use Jiny\Admin\App\Models\AdminUser;

/**
 * 관리자 2FA 인증 컨트롤러
 * 
 * 로그인 후 2FA 인증을 처리하고, 2FA 관련 도움말을 제공합니다.
 * 관리자별 2FA 설정은 AdminUser2FAController에서 처리합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Auth
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * 상세한 기능은 관련 문서를 참조하세요.
 * @docs jiny/admin/docs/features/AdminTwoFactor.md
 * 
 * 테스트 파일 작성 시 참조하세요.
 * @test jiny/admin/tests/Feature/Auth/AdminTwoFactorTest.php
 * 
 * 관련 라우트 정보:
 * @route jiny/admin/routes/web.php - admin.2fa.challenge, admin.2fa.verify, admin.2fa.help
 */
class AdminTwoFactorController extends Controller
{
    /**
     * 뷰 경로 변수들
     */
    protected string $challengeView = 'jiny-admin::auth.auth_2fa_challenge';
    protected string $helpView = 'jiny-admin::auth.help_2fa';
    protected string $dashboardView = 'jiny-admin::dashboard.index';

    /**
     * 2FA 서비스 인스턴스
     */
    protected $twoFactorService;

    /**
     * 생성자
     * 
     * TwoFactorService를 주입받아 초기화합니다.
     * 
     * @param TwoFactorService $twoFactorService 2FA 서비스
     */
    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    // 기존 전역 2FA 설정 메서드들은 제거됨
    // 대신 관리자별 2FA 설정을 위해 AdminUser2FAController 사용

    /**
     * 2FA 인증 페이지 (로그인 후)
     * 
     * 2FA가 활성화된 관리자 계정의 인증 코드 입력 페이지를 표시합니다.
     * 
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * 
     * @route admin.2fa.challenge (GET /admin/2fa/challenge)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 로그인된 관리자 확인
     * 2. 2FA 활성화 여부 확인
     * 3. 2FA가 활성화되지 않은 경우 대시보드로 리다이렉트
     * 4. 2FA 인증 페이지 표시
     * 
     * 반환값:
     * - 2FA 비활성화: 대시보드로 리다이렉트
     * - 2FA 활성화: 2FA 인증 페이지 뷰 렌더링
     */
    public function challenge()
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user || !$user->has2FAEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        return view($this->challengeView, compact('user'));
    }

    /**
     * 2FA 인증 처리
     * 
     * 사용자가 입력한 2FA 인증 코드를 검증하고,
     * 성공 시 대시보드로 리다이렉트합니다.
     * 
     * @param Request $request HTTP 요청 객체
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @route admin.2fa.verify (POST /admin/2fa/verify)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 현재 로그인된 관리자 확인
     * 2. 2FA 활성화 여부 확인
     * 3. 인증 코드 유효성 검사
     * 4. 2FA 코드 검증 (TOTP)
     * 5. 백업 코드 검증 (실패 시)
     * 6. 성공 시 2FA 인증 완료 세션 설정
     * 7. 대시보드로 리다이렉트
     * 
     * 반환값:
     * - 성공: 대시보드로 리다이렉트 (성공 메시지 포함)
     * - 실패: 인증 페이지로 돌아가기 (오류 메시지 포함)
     * 
     * 유효성 검사 규칙:
     * - code: 필수, 문자열, 6자리
     */
    public function verify(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user || !$user->has2FAEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $code = $request->code;
        $success = false;

        // 2FA 코드 검증
        if ($this->twoFactorService->verifyCode($user, $code)) {
            $success = true;
            $this->twoFactorService->log2FAAttempt($user, $code, true);
            
            // 2FA 인증 완료 세션 설정
            session(['2fa_verified' => true]);
            
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', '2FA 인증이 완료되었습니다.');
        }

        // 백업 코드 검증
        if ($this->twoFactorService->verifyBackupCode($user, $code)) {
            $success = true;
            $this->twoFactorService->logBackupCodeUsage($user, $code, true);
            
            // 2FA 인증 완료 세션 설정
            session(['2fa_verified' => true]);
            
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', '백업 코드로 인증이 완료되었습니다.');
        }

        // 실패 로그
        $this->twoFactorService->log2FAAttempt($user, $code, false);

        return back()->withErrors(['code' => '잘못된 인증 코드입니다.']);
    }

    /**
     * 2FA 도움말 페이지
     * 
     * 2FA 설정 및 사용에 대한 도움말을 제공합니다.
     * 
     * @return \Illuminate\View\View 2FA 도움말 페이지
     * 
     * @route admin.2fa.help (GET /admin/2fa/help)
     * @middleware web, admin.auth
     * 
     * 동작 과정:
     * 1. 2FA 도움말 페이지 뷰 렌더링
     * 
     * 반환값: 2FA 도움말 페이지 뷰 렌더링
     */
    public function help()
    {
        return view($this->helpView);
    }

    // 백업 코드 다운로드 기능은 관리자별 설정으로 변경되어 제거됨
}
