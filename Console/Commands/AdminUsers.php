<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Jiny\Admin\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * 관리자 등록 콘솔 명령
 * - admin_users 테이블에만 등록 (이메일 중복 불가)
 */
class AdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'admin_users 테이블에 등록하는 관리자 계정 생성';

    public function handle()
    {
        $this->info('==== 관리자 계정 등록 ====');

        // 이메일 입력 및 중복 체크
        $email = $this->askValid('이메일을 입력하세요', 'email', ['required', 'email']);
        if (AdminUser::where('email', $email)->exists()) {
            $this->error('이미 admin_users 테이블에 등록된 이메일입니다.');
            return 1;
        }

        // 이름 입력
        $name = $this->askValid('이름을 입력하세요', 'name', ['required', 'min:2']);
        // 비밀번호 입력
        $password = $this->askPassword();
        // 관리자 타입
        $type = $this->choice('관리자 유형을 선택하세요', ['super', 'admin', 'staff'], 1);
        // 상태
        $status = $this->choice('계정 상태를 선택하세요', ['active', 'inactive', 'suspended'], 0);

        // admin_users 테이블 등록
        $adminData = [
            // id는 auto-increment이므로 입력하지 않음
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'type' => $type,
            'status' => $status,
            'is_verified' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $admin = AdminUser::create($adminData);

        if ($admin) {
            $this->info('✅ 관리자 계정이 성공적으로 등록되었습니다!');
            $this->line("  이메일: $email");
            $this->line("  이름: $name");
            $this->line("  유형: $type");
            $this->line("  상태: $status");
            $this->info('이제 /admin/login으로 접속하여 로그인할 수 있습니다.');
            return 0;
        } else {
            $this->error('❌ 관리자 등록에 실패했습니다.');
            return 1;
        }
    }

    // 대화형 입력 + 검증
    private function askValid($question, $field, $rules)
    {
        do {
            $value = $this->ask($question);
            $validator = Validator::make([$field => $value], [$field => $rules]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $msg) {
                    $this->error($msg);
                }
            }
        } while ($validator->fails());
        return $value;
    }

    // 비밀번호 입력 및 확인
    private function askPassword()
    {
        do {
            $password = $this->secret('비밀번호를 입력하세요 (8자 이상)');
            $password2 = $this->secret('비밀번호를 한 번 더 입력하세요');
            if ($password !== $password2) {
                $this->error('비밀번호가 일치하지 않습니다.');
                continue;
            }
            if (strlen($password) < 8) {
                $this->error('비밀번호는 8자 이상이어야 합니다.');
                continue;
            }
            break;
        } while (true);
        return $password;
    }
}

/**
 * 관리자 삭제 콘솔 명령
 * - 입력한 이메일의 관리자를 admin_users 테이블에서만 삭제
 */
class AdminUserDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '입력한 이메일의 관리자를 admin_users 테이블에서 삭제';

    public function handle()
    {
        $this->info('==== 관리자 계정 삭제 ====');
        $email = $this->ask('삭제할 관리자 이메일을 입력하세요');

        $admin = AdminUser::where('email', $email)->first();

        if (!$admin) {
            $this->error('해당 이메일로 등록된 관리자가 없습니다.');
            return 1;
        }

        $this->line('------------------------------');
        if ($admin) {
            $this->info('[admin_users] 테이블 정보:');
            $this->line('  이름: ' . $admin->name);
            $this->line('  이메일: ' . $admin->email);
            $this->line('  유형: ' . $admin->type);
            $this->line('  상태: ' . $admin->status);
        }
        $this->line('------------------------------');

        if (!$this->confirm('정말로 이 관리자를 삭제하시겠습니까?', false)) {
            $this->info('작업이 취소되었습니다.');
            return 0;
        }

        if ($admin->delete()) {
            $this->info('✅ 관리자가 성공적으로 삭제되었습니다.');
            return 0;
        } else {
            $this->error('❌ 관리자 삭제에 실패했습니다.');
            return 1;
        }
    }
}
