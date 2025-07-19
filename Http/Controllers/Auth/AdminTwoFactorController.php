<?php

namespace Jiny\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUser;
use Jiny\Admin\Services\TwoFactorService;

class AdminTwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    // 기존 전역 2FA 설정 메서드들은 제거됨
    // 대신 관리자별 2FA 설정을 위해 AdminUser2FAController 사용

    /**
     * 2FA 인증 페이지 (로그인 후)
     */
    public function challenge()
    {
        $user = Auth::guard('admin')->user();
        
        if (!$user || !$user->has2FAEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        return view('jiny-admin::auth.auth_2fa_challenge', compact('user'));
    }

    /**
     * 2FA 인증 처리
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

    // 백업 코드 다운로드 기능은 관리자별 설정으로 변경되어 제거됨
}
