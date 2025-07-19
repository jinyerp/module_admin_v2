<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUser;
use Jiny\Admin\Services\TwoFactorService;

class AdminUser2FAController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * 특정 관리자의 2FA 설정 페이지
     */
    public function setup($id)
    {
        $user = AdminUser::findOrFail($id);
        // 현재 로그인한 관리자가 대상 관리자를 수정할 권한이 있는지 확인
        $currentAdmin = Auth::guard('admin')->user();
        if (!$this->canManageUser($currentAdmin, $user)) {
            return redirect()->route('admin.admin.users.show', $user->id)
                ->with('error', '해당 관리자의 2FA 설정을 변경할 권한이 없습니다.');
        }

        // 2FA가 이미 활성화된 경우
        if ($user->has2FAEnabled()) {
            return redirect()->route('admin.admin.users.2fa.manage', $user->id)
                ->with('info', '2FA가 이미 활성화되어 있습니다.');
        }

        // 새로운 시크릿과 백업 코드 생성
        $secret = $this->twoFactorService->generateSecret();
        $backupCodes = $this->twoFactorService->generateBackupCodes();
        $qrCodeUrl = $this->twoFactorService->generateQRCodeUrl($user, $secret);

        return view('jiny-admin::admin.users.2fa.setup', [
            'user' => $user,
            'secret' => $secret,
            'backupCodes' => $backupCodes,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * 특정 관리자의 2FA 활성화
     */
    public function enable(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        // 권한 확인
        $currentAdmin = Auth::guard('admin')->user();
        if (!$this->canManageUser($currentAdmin, $user)) {
            return redirect()->route('admin.admin.users.show', $user->id)
                ->with('error', '해당 관리자의 2FA 설정을 변경할 권한이 없습니다.');
        }

        $request->validate([
            'code' => 'required|string|size:6',
            'secret' => 'required|string',
            'backup_codes' => 'required|array',
        ]);

        $code = $request->input('code');
        $secret = $request->input('secret');
        $backupCodes = $request->input('backup_codes');

        // 임시로 시크릿을 설정하여 코드 검증
        $tempUser = clone $user;
        $tempUser->google_2fa_secret = $secret;
        
        // 코드 검증
        if (!$this->twoFactorService->verifyCode($tempUser, $code)) {
            return back()->withErrors(['code' => '잘못된 인증 코드입니다.']);
        }

        // 2FA 활성화
        $user->google_2fa_secret = $secret;
        $user->google_2fa_enabled = true;
        $user->google_2fa_backup_codes = $backupCodes;
        $user->google_2fa_verified_at = now();
        $user->save();

        // 로그 기록
        $this->twoFactorService->log2FA($user, 'enable', 'success', '관리자에 의한 2FA 활성화');

        return redirect()->route('admin.admin.users.show', $user->id)
            ->with('success', '2FA가 성공적으로 활성화되었습니다.');
    }

    /**
     * 특정 관리자의 2FA 관리 페이지
     */
    public function manage($id)
    {
        $user = AdminUser::findOrFail($id);
        // 권한 확인
        $currentAdmin = Auth::guard('admin')->user();
        if (!$this->canManageUser($currentAdmin, $user)) {
            return redirect()->route('admin.admin.users.show', $user->id)
                ->with('error', '해당 관리자의 2FA 설정을 확인할 권한이 없습니다.');
        }

        // 2FA가 활성화되지 않은 경우
        if (!$user->has2FAEnabled()) {
            return redirect()->route('admin.admin.users.2fa.setup', $user->id)
                ->with('info', '2FA가 설정되지 않았습니다.');
        }

        // google_2fa_verified_at이 null인 경우 처리
        if (!$user->google_2fa_verified_at) {
            $user->google_2fa_verified_at = now();
            $user->save();
        }

        return view('jiny-admin::admin.users.2fa.manage', [
            'user' => $user,
        ]);
    }

    /**
     * 특정 관리자의 2FA 비활성화
     */
    public function disable(Request $request, $id)
    {
        $user = AdminUser::findOrFail($id);
        // 권한 확인
        $currentAdmin = Auth::guard('admin')->user();
        if (!$this->canManageUser($currentAdmin, $user)) {
            return redirect()->route('admin.admin.users.show', $user->id)
                ->with('error', '해당 관리자의 2FA 설정을 변경할 권한이 없습니다.');
        }

        // 2FA 비활성화
        $user->google_2fa_enabled = false;
        $user->google_2fa_disabled_at = now();
        $user->save();

        // 로그 기록
        $this->twoFactorService->log2FA($user, 'disable', 'success', '관리자에 의한 2FA 비활성화');

        return redirect()->route('admin.admin.users.show', $user->id)
            ->with('success', '2FA가 비활성화되었습니다.');
    }

    /**
     * 특정 관리자의 백업 코드 재생성
     */
    public function regenerateBackupCodes(AdminUser $user)
    {
        // 권한 확인
        $currentAdmin = Auth::guard('admin')->user();
        if (!$this->canManageUser($currentAdmin, $user)) {
            return redirect()->route('admin.admin.users.show', $user->id)
                ->with('error', '해당 관리자의 2FA 설정을 변경할 권한이 없습니다.');
        }

        // 새로운 백업 코드 생성
        $backupCodes = $this->twoFactorService->generateBackupCodes();
        $user->google_2fa_backup_codes = $backupCodes;
        $user->save();

        // 로그 기록
        $this->twoFactorService->log2FA($user, 'regenerate_backup_codes', 'success', '백업 코드 재생성');

        return response()->json([
            'success' => true,
            'backup_codes' => $backupCodes,
            'message' => '백업 코드가 재생성되었습니다.'
        ]);
    }

    /**
     * 관리자 권한 확인
     */
    private function canManageUser($currentAdmin, $targetUser): bool
    {
        // super 관리자는 모든 관리자를 관리할 수 있음
        if ($currentAdmin->type === 'super') {
            return true;
        }

        // admin은 자신과 staff만 관리할 수 있음
        if ($currentAdmin->type === 'admin') {
            return $targetUser->type === 'staff' || $currentAdmin->id === $targetUser->id;
        }

        // staff는 자신만 관리할 수 있음
        if ($currentAdmin->type === 'staff') {
            return $currentAdmin->id === $targetUser->id;
        }

        return false;
    }
} 