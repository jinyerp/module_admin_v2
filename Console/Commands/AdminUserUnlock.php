<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserPasswordError;
use Illuminate\Support\Facades\DB;

/**
 * 관리자 계정 잠금 해제 콘솔 명령
 * - 비밀번호 오류로 잠긴 계정을 해제합니다.
 */
class AdminUserUnlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user-unlock {email : 잠금 해제할 관리자 이메일} {--force : 확인 없이 강제 해제} {--test : 테스트 모드로 실행}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '비밀번호 오류로 잠긴 관리자 계정을 해제합니다.';

    public function handle()
    {
        $email = $this->argument('email');
        $force = $this->option('force');
        $testMode = $this->option('test');

        $this->info('==== 관리자 계정 잠금 해제 ====');
        $this->line("대상 이메일: {$email}");

        if ($testMode) {
            $this->info('🧪 테스트 모드로 실행합니다...');
        }

        // 1. 관리자 계정 존재 확인
        $admin = AdminUser::where('email', $email)->first();
        if (!$admin) {
            $this->error('❌ 해당 이메일로 등록된 관리자가 없습니다.');
            return 1;
        }

        $this->line('------------------------------');
        $this->info('[admin_users] 테이블 정보:');
        $this->line('  이름: ' . $admin->name);
        $this->line('  이메일: ' . $admin->email);
        $this->line('  유형: ' . $admin->type);
        $this->line('  상태: ' . ($admin->status ?? 'N/A'));
        $this->line('  활성화: ' . ($admin->is_active ? '예' : '아니오'));
        $this->line('  이메일 인증: ' . ($admin->is_verified ? '완료' : '미완료'));
        $this->line('  슈퍼 관리자: ' . ($admin->is_super_admin ? '예' : '아니오'));
        $this->line('------------------------------');

        // 2. 비밀번호 오류 기록 확인
        $errorCount = AdminUserPasswordError::getErrorCount($email, 24);
        $this->line("최근 24시간 내 비밀번호 오류 횟수: {$errorCount}회");

        if ($errorCount == 0) {
            $this->warn('⚠️  해당 계정은 잠기지 않았습니다.');
            return 0;
        }

        // 3. 잠금 상태 확인
        $maxAttempts = config('admin.settings.login.security.max_attempts', 5);
        $maxAttemptsAdminLock = config('admin.settings.login.security.max_attempts_admin_lock', 25);

        if ($errorCount >= $maxAttemptsAdminLock) {
            $this->error('❌ 이 계정은 25회 이상 비밀번호 오류로 관리자 해제가 필요한 상태입니다.');
            $this->line('보안상의 이유로 콘솔 명령으로는 해제할 수 없습니다.');
            $this->line('데이터베이스에서 직접 수정하거나 관리자 권한으로 해제해야 합니다.');
            return 1;
        }

        if ($errorCount >= $maxAttempts) {
            $this->warn("⚠️  이 계정은 {$maxAttempts}회 이상 비밀번호 오류로 30분간 잠긴 상태입니다.");
        }

        // 4. 잠금 해제 확인 (강제 옵션이 있거나 테스트 모드인 경우 생략)
        if (!$force && !$testMode && !$this->confirm('정말로 이 계정의 잠금을 해제하시겠습니까?', false)) {
            $this->info('작업이 취소되었습니다.');
            return 0;
        }

        // 5. 잠금 해제 실행
        try {
            DB::beginTransaction();

            // 비밀번호 오류 기록 삭제
            $deletedCount = AdminUserPasswordError::where('email', $email)->delete();
            $this->line("삭제된 비밀번호 오류 기록: {$deletedCount}건");

            // 계정 상태 확인 및 수정
            if (isset($admin->is_active) && !$admin->is_active) {
                $admin->is_active = true;
                $admin->save();
                $this->line('✅ 계정 활성화 상태를 활성으로 변경했습니다.');
            }

            DB::commit();

            $this->info('✅ 계정 잠금이 성공적으로 해제되었습니다!');
            $this->line("이제 {$email}로 로그인할 수 있습니다.");
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ 계정 잠금 해제 중 오류가 발생했습니다.');
            $this->error('오류 내용: ' . $e->getMessage());
            return 1;
        }
    }
}
