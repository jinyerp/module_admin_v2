<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
    protected $signature = 'admin:user {--test : 테스트 모드로 실행 (기본값 사용)} {--email= : 이메일 주소} {--name= : 이름} {--password= : 비밀번호} {--type= : 관리자 유형} {--active : 계정 활성화} {--verified : 이메일 인증 완료} {--super : 슈퍼 관리자}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'admin_users 테이블에 등록하는 관리자 계정 생성';

    public function handle()
    {
        $this->info('==== 관리자 계정 등록 ====');

        // 테스트 모드 확인
        if ($this->option('test')) {
            return $this->handleTestMode();
        }

        // 옵션 기반 실행
        if ($this->option('email') && $this->option('name') && $this->option('password')) {
            return $this->handleOptionMode();
        }

        // 대화형 모드 (기본)
        return $this->handleInteractiveMode();
    }

    /**
     * 테스트 모드 실행
     */
    private function handleTestMode()
    {
        $this->info('🧪 테스트 모드로 실행합니다...');
        
        $adminData = [
            'name' => 'Test Admin',
            'email' => 'test@admin.com',
            'password' => Hash::make('TestPassword123!'),
            'type' => 'admin',
            'is_active' => true,
            'is_verified' => true,
            'is_super_admin' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // status 필드가 있는 경우 추가
        if (AdminUser::first() && AdminUser::first()->getConnection()->getSchemaBuilder()->hasColumn('admin_users', 'status')) {
            $adminData['status'] = 'active';
        }

        try {
            $admin = AdminUser::create($adminData);
            $this->info('✅ 테스트 관리자 계정이 성공적으로 생성되었습니다!');
            $this->line("  이메일: test@admin.com");
            $this->line("  비밀번호: TestPassword123!");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ 테스트 관리자 생성 실패: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 옵션 기반 실행
     */
    private function handleOptionMode()
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');
        $type = $this->option('type') ?: 'admin';
        $isActive = $this->option('active');
        $isVerified = $this->option('verified');
        $isSuperAdmin = $this->option('super');

        // 이메일 중복 체크
        if (AdminUser::where('email', $email)->exists()) {
            $this->error('이미 admin_users 테이블에 등록된 이메일입니다.');
            return 1;
        }

        $adminData = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'type' => $type,
            'is_active' => $isActive,
            'is_verified' => $isVerified,
            'is_super_admin' => $isSuperAdmin,
            'email_verified_at' => $isVerified ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // status 필드가 있는 경우 추가
        if (AdminUser::first() && AdminUser::first()->getConnection()->getSchemaBuilder()->hasColumn('admin_users', 'status')) {
            $adminData['status'] = 'active';
        }

        try {
            $admin = AdminUser::create($adminData);
            $this->info('✅ 관리자 계정이 성공적으로 등록되었습니다!');
            $this->line("  이메일: $email");
            $this->line("  이름: $name");
            $this->line("  유형: $type");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ 관리자 등록 실패: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * 대화형 모드 실행 (기존 로직)
     */
    private function handleInteractiveMode()
    {
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
        // 계정 활성화 상태
        $isActive = $this->choice('계정 활성화 상태를 선택하세요', ['활성', '비활성'], 0) === '활성';
        // 이메일 인증 상태
        $isVerified = $this->choice('이메일 인증 상태를 선택하세요', ['인증 완료', '인증 필요'], 0) === '인증 완료';
        // 슈퍼 관리자 여부
        $isSuperAdmin = $this->choice('슈퍼 관리자 여부를 선택하세요', ['일반 관리자', '슈퍼 관리자'], 0) === '슈퍼 관리자';

        // admin_users 테이블 등록
        $adminData = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'type' => $type,
            'is_active' => $isActive,
            'is_verified' => $isVerified,
            'is_super_admin' => $isSuperAdmin,
            'email_verified_at' => $isVerified ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // status 필드가 있는 경우 추가
        if (AdminUser::first() && AdminUser::first()->getConnection()->getSchemaBuilder()->hasColumn('admin_users', 'status')) {
            $status = $this->choice('계정 상태를 선택하세요', ['active', 'inactive', 'suspended'], 0);
            $adminData['status'] = $status;
        }

        $admin = AdminUser::create($adminData);

        if ($admin) {
            $this->info('✅ 관리자 계정이 성공적으로 등록되었습니다!');
            $this->line("  이메일: $email");
            $this->line("  이름: $name");
            $this->line("  유형: $type");
            $this->line("  활성화: " . ($isActive ? '예' : '아니오'));
            $this->line("  이메일 인증: " . ($isVerified ? '완료' : '미완료'));
            $this->line("  슈퍼 관리자: " . ($isSuperAdmin ? '예' : '아니오'));
            if (isset($status)) {
                $this->line("  상태: $status");
            }
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
    protected $signature = 'admin:user-delete {--email= : 삭제할 관리자 이메일} {--force : 확인 없이 강제 삭제}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '입력한 이메일의 관리자를 admin_users 테이블에서 삭제';

    public function handle()
    {
        $this->info('==== 관리자 계정 삭제 ====');
        
        $email = $this->option('email');
        $force = $this->option('force');

        // 이메일이 옵션으로 제공되지 않은 경우 대화형 입력
        if (!$email) {
            $email = $this->ask('삭제할 관리자 이메일을 입력하세요');
        }

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
            $this->line('  활성화: ' . ($admin->is_active ? '예' : '아니오'));
            $this->line('  이메일 인증: ' . ($admin->is_verified ? '완료' : '미완료'));
            $this->line('  슈퍼 관리자: ' . ($admin->is_super_admin ? '예' : '아니오'));
            if (isset($admin->status)) {
                $this->line('  상태: ' . $admin->status);
            }
        }
        $this->line('------------------------------');

        // 강제 삭제 옵션이 있거나 확인을 받은 경우
        if ($force || $this->confirm('정말로 이 관리자를 삭제하시겠습니까?', false)) {
            if ($admin->delete()) {
                $this->info('✅ 관리자가 성공적으로 삭제되었습니다.');
                return 0;
            } else {
                $this->error('❌ 관리자 삭제에 실패했습니다.');
                return 1;
            }
        } else {
            $this->info('작업이 취소되었습니다.');
            return 0;
        }
    }
}
