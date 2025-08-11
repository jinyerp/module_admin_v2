<?php

namespace Jiny\Admin\Tests\Features;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jiny\Admin\App\Models\AdminUser;
use Jiny\Admin\App\Models\AdminUserPasswordError;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

/**
 * 관리자 콘솔 명령 기능 테스트
 * 
 * 이 테스트는 다음 콘솔 명령들을 검증합니다:
 * - admin:user (관리자 계정 생성)
 * - admin:user-delete (관리자 계정 삭제)
 * - admin:user-unlock (계정 잠금 해제)
 */
class AdminConsoleCommandsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // ===== 테스트 상수 정의 =====
    
    /** @var string 테스트용 관리자 이메일 */
    protected const TEST_ADMIN_EMAIL = 'test@admin.com';
    
    /** @var string 테스트용 관리자 이름 */
    protected const TEST_ADMIN_NAME = 'Test Admin';
    
    /** @var string 테스트용 관리자 비밀번호 */
    protected const TEST_ADMIN_PASSWORD = 'TestPassword123!';
    
    /** @var string 테스트용 슈퍼 관리자 이메일 */
    protected const TEST_SUPER_ADMIN_EMAIL = 'superadmin@test.com';
    
    /** @var string 테스트용 슈퍼 관리자 이름 */
    protected const TEST_SUPER_ADMIN_NAME = 'Super Admin';
    
    /** @var string 테스트용 슈퍼 관리자 비밀번호 */
    protected const TEST_SUPER_ADMIN_PASSWORD = 'SuperPassword123!';

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 슈퍼 관리자 계정 생성 (setup 페이지 리다이렉트 방지)
        $this->createSuperAdmin();
    }

    /**
     * 슈퍼 관리자 계정 생성
     */
    private function createSuperAdmin(): void
    {
        AdminUser::create([
            'id' => (string) Str::uuid(),
            'name' => self::TEST_SUPER_ADMIN_NAME,
            'email' => self::TEST_SUPER_ADMIN_EMAIL,
            'password' => Hash::make(self::TEST_SUPER_ADMIN_PASSWORD),
            'type' => 'super',
            'is_active' => true,
            'is_verified' => true,
            'is_super_admin' => true,
        ]);
    }

    // ===== admin:user 명령 테스트 =====

    /**
     * admin:user --test 옵션으로 테스트 관리자 계정 생성
     * 
     * 검증 항목:
     * - 테스트 모드로 기본값 사용하여 계정 생성
     * - 성공 메시지 출력
     * - 데이터베이스에 계정 저장
     */
    public function test_admin_user_command_with_test_option(): void
    {
        $this->artisan('admin:user', ['--test' => true])
            ->expectsOutput('🧪 테스트 모드로 실행합니다...')
            ->expectsOutput('✅ 테스트 관리자 계정이 성공적으로 생성되었습니다!')
            ->expectsOutput('  이메일: test@admin.com')
            ->expectsOutput('  비밀번호: TestPassword123!')
            ->assertExitCode(0);

        // 데이터베이스에 계정이 저장되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'test@admin.com',
            'name' => 'Test Admin',
            'type' => 'admin',
            'is_active' => true,
            'is_verified' => true,
            'is_super_admin' => false,
        ]);
    }

    /**
     * admin:user 옵션 기반으로 관리자 계정 생성
     * 
     * 검증 항목:
     * - 이메일, 이름, 비밀번호 옵션으로 계정 생성
     * - 성공 메시지 출력
     * - 데이터베이스에 계정 저장
     */
    public function test_admin_user_command_with_options(): void
    {
        $this->artisan('admin:user', [
                '--email' => 'custom@admin.com',
                '--name' => 'Custom Admin',
                '--password' => 'CustomPassword123!',
                '--type' => 'staff',
                '--active' => true,
                '--verified' => true,
                '--super' => false,
            ])
            ->expectsOutput('==== 관리자 계정 등록 ====')
            ->expectsOutput('✅ 관리자 계정이 성공적으로 등록되었습니다!')
            ->expectsOutput('  이메일: custom@admin.com')
            ->expectsOutput('  이름: Custom Admin')
            ->expectsOutput('  유형: staff')
            ->assertExitCode(0);

        // 데이터베이스에 계정이 저장되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'custom@admin.com',
            'name' => 'Custom Admin',
            'type' => 'staff',
            'is_active' => true,
            'is_verified' => true,
            'is_super_admin' => false,
        ]);
    }

    /**
     * admin:user 명령으로 중복 이메일 계정 생성 시 실패
     * 
     * 검증 항목:
     * - 중복 이메일 오류 메시지 출력
     * - 실패 상태 코드 반환
     */
    public function test_admin_user_command_fails_with_duplicate_email(): void
    {
        // 첫 번째 계정 생성
        $this->artisan('admin:user', ['--test' => true])->assertExitCode(0);

        // 동일한 이메일로 두 번째 계정 생성 시도
        $this->artisan('admin:user', [
                '--email' => 'test@admin.com',
                '--name' => 'Duplicate Admin',
                '--password' => 'AnotherPassword123!',
            ])
            ->expectsOutput('==== 관리자 계정 등록 ====')
            ->expectsOutput('이미 admin_users 테이블에 등록된 이메일입니다.')
            ->assertExitCode(1);
    }

    // ===== admin:user-delete 명령 테스트 =====

    /**
     * admin:user-delete --email 옵션으로 관리자 계정 삭제
     * 
     * 검증 항목:
     * - 이메일 옵션으로 계정 삭제
     * - 계정 정보 표시
     * - 삭제 확인 후 성공
     */
    public function test_admin_user_delete_command_with_email_option(): void
    {
        // 테스트 계정 생성
        $this->artisan('admin:user', ['--test' => true])->assertExitCode(0);

        $this->artisan('admin:user-delete', [
                '--email' => 'test@admin.com',
                '--force' => true,
            ])
            ->expectsOutput('==== 관리자 계정 삭제 ====')
            ->expectsOutput('------------------------------')
            ->expectsOutput('[admin_users] 테이블 정보:')
            ->expectsOutput('  이름: Test Admin')
            ->expectsOutput('  이메일: test@admin.com')
            ->expectsOutput('  유형: admin')
            ->expectsOutput('  활성화: 예')
            ->expectsOutput('  이메일 인증: 완료')
            ->expectsOutput('  슈퍼 관리자: 아니오')
            ->expectsOutput('------------------------------')
            ->expectsOutput('✅ 관리자가 성공적으로 삭제되었습니다.')
            ->assertExitCode(0);

        // 데이터베이스에서 계정이 삭제되었는지 확인
        $this->assertDatabaseMissing('admin_users', [
            'email' => 'test@admin.com',
        ]);
    }

    /**
     * admin:user-delete 명령으로 존재하지 않는 이메일 삭제 시 실패
     * 
     * 검증 항목:
     * - 존재하지 않는 이메일 오류 메시지 출력
     * - 실패 상태 코드 반환
     */
    public function test_admin_user_delete_command_fails_with_nonexistent_email(): void
    {
        $this->artisan('admin:user-delete', [
                '--email' => 'nonexistent@admin.com',
                '--force' => true,
            ])
            ->expectsOutput('==== 관리자 계정 삭제 ====')
            ->expectsOutput('해당 이메일로 등록된 관리자가 없습니다.')
            ->assertExitCode(1);
    }

    // ===== admin:user-unlock 명령 테스트 =====

    /**
     * admin:user-unlock --test 옵션으로 계정 잠금 해제
     * 
     * 검증 항목:
     * - 테스트 모드로 계정 잠금 해제
     * - 계정 정보 표시
     * - 잠금 해제 성공
     */
    public function test_admin_user_unlock_command_with_test_option(): void
    {
        // 테스트 계정 생성
        $this->artisan('admin:user', ['--test' => true])->assertExitCode(0);

        // 비밀번호 오류 기록 생성 (로그인 실패 시뮬레이션)
        AdminUserPasswordError::create([
            'admin_user_id' => AdminUser::where('email', 'test@admin.com')->first()->id,
            'email' => 'test@admin.com',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'error_at' => now(),
            'error_type' => 'password',
            'error_message' => '잘못된 비밀번호',
        ]);

        $this->artisan('admin:user-unlock', [
                'email' => 'test@admin.com',
                '--test' => true,
            ])
            ->expectsOutput('==== 관리자 계정 잠금 해제 ====')
            ->expectsOutput('대상 이메일: test@admin.com')
            ->expectsOutput('🧪 테스트 모드로 실행합니다...')
            ->expectsOutput('------------------------------')
            ->expectsOutput('[admin_users] 테이블 정보:')
            ->expectsOutput('  이름: Test Admin')
            ->expectsOutput('  이메일: test@admin.com')
            ->expectsOutput('  유형: admin')
            ->expectsOutput('  활성화: 예')
            ->expectsOutput('  이메일 인증: 완료')
            ->expectsOutput('  슈퍼 관리자: 아니오')
            ->expectsOutput('------------------------------')
            ->expectsOutput('최근 24시간 내 비밀번호 오류 횟수: 1회')
            ->expectsOutput('⚠️  이 계정은 5회 이상 비밀번호 오류로 30분간 잠긴 상태입니다.')
            ->expectsOutput('삭제된 비밀번호 오류 기록: 1건')
            ->expectsOutput('✅ 계정 잠금이 성공적으로 해제되었습니다!')
            ->expectsOutput('이제 test@admin.com로 로그인할 수 있습니다.')
            ->assertExitCode(0);

        // 비밀번호 오류 기록이 삭제되었는지 확인
        $this->assertDatabaseMissing('admin_user_password_error', [
            'email' => 'test@admin.com',
        ]);
    }

    /**
     * admin:user-unlock 명령으로 존재하지 않는 이메일 해제 시 실패
     * 
     * 검증 항목:
     * - 존재하지 않는 이메일 오류 메시지 출력
     * - 실패 상태 코드 반환
     */
    public function test_admin_user_unlock_command_fails_with_nonexistent_email(): void
    {
        $this->artisan('admin:user-unlock', [
                'email' => 'nonexistent@admin.com',
                '--test' => true,
            ])
            ->expectsOutput('==== 관리자 계정 잠금 해제 ====')
            ->expectsOutput('대상 이메일: nonexistent@admin.com')
            ->expectsOutput('❌ 해당 이메일로 등록된 관리자가 없습니다.')
            ->assertExitCode(1);
    }

    /**
     * admin:user-unlock 명령으로 잠기지 않은 계정 해제 시 경고
     * 
     * 검증 항목:
     * - 잠기지 않은 계정 경고 메시지 출력
     * - 성공 상태 코드 반환 (경고는 실패가 아님)
     */
    public function test_admin_user_unlock_command_warns_for_unlocked_account(): void
    {
        // 테스트 계정 생성 (비밀번호 오류 기록 없음)
        $this->artisan('admin:user', ['--test' => true])->assertExitCode(0);

        $this->artisan('admin:user-unlock', [
                'email' => 'test@admin.com',
                '--test' => true,
            ])
            ->expectsOutput('==== 관리자 계정 잠금 해제 ====')
            ->expectsOutput('대상 이메일: test@admin.com')
            ->expectsOutput('🧪 테스트 모드로 실행합니다...')
            ->expectsOutput('------------------------------')
            ->expectsOutput('[admin_users] 테이블 정보:')
            ->expectsOutput('  이름: Test Admin')
            ->expectsOutput('  이메일: test@admin.com')
            ->expectsOutput('  유형: admin')
            ->expectsOutput('  활성화: 예')
            ->expectsOutput('  이메일 인증: 완료')
            ->expectsOutput('  슈퍼 관리자: 아니오')
            ->expectsOutput('------------------------------')
            ->expectsOutput('최근 24시간 내 비밀번호 오류 횟수: 0회')
            ->expectsOutput('⚠️  해당 계정은 잠기지 않았습니다.')
            ->assertExitCode(0);
    }

    // ===== 통합 테스트 =====

    /**
     * 전체 관리자 생명주기 테스트 (생성 → 잠금 → 해제 → 삭제)
     * 
     * 검증 항목:
     * - 계정 생성
     * - 비밀번호 오류로 잠금
     * - 잠금 해제
     * - 계정 삭제
     */
    public function test_admin_account_lifecycle(): void
    {
        // 1. 계정 생성
        $this->artisan('admin:user', ['--test' => true])->assertExitCode(0);
        
        $admin = AdminUser::where('email', 'test@admin.com')->first();
        $this->assertNotNull($admin);

        // 2. 비밀번호 오류 기록 생성 (5회 실패로 잠금 상태 시뮬레이션)
        for ($i = 0; $i < 5; $i++) {
            AdminUserPasswordError::create([
                'admin_user_id' => $admin->id,
                'email' => 'test@admin.com',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test User Agent',
                'error_at' => now()->subMinutes($i),
                'error_type' => 'password',
                'error_message' => '잘못된 비밀번호',
            ]);
        }

        // 3. 잠금 해제
        $this->artisan('admin:user-unlock', [
                'email' => 'test@admin.com',
                '--test' => true,
            ])->assertExitCode(0);

        // 4. 계정 삭제
        $this->artisan('admin:user-delete', [
                '--email' => 'test@admin.com',
                '--force' => true,
            ])->assertExitCode(0);

        // 5. 최종 상태 확인
        $this->assertDatabaseMissing('admin_users', ['email' => 'test@admin.com']);
        $this->assertDatabaseMissing('admin_user_password_error', ['email' => 'test@admin.com']);
    }

    /**
     * 슈퍼 관리자 계정 생성 및 관리 테스트
     * 
     * 검증 항목:
     * - 슈퍼 관리자 계정 생성
     * - 슈퍼 관리자 권한 확인
     * - 계정 정보 표시
     */
    public function test_super_admin_account_creation_and_management(): void
    {
        $this->artisan('admin:user', [
                '--email' => 'newsuper@admin.com',
                '--name' => 'New Super Admin',
                '--password' => 'NewSuperPassword123!',
                '--type' => 'super',
                '--active' => true,
                '--verified' => true,
                '--super' => true,
            ])
            ->expectsOutput('==== 관리자 계정 등록 ====')
            ->expectsOutput('✅ 관리자 계정이 성공적으로 등록되었습니다!')
            ->expectsOutput('  이메일: newsuper@admin.com')
            ->expectsOutput('  이름: New Super Admin')
            ->expectsOutput('  유형: super')
            ->assertExitCode(0);

        // 데이터베이스에 슈퍼 관리자 계정이 저장되었는지 확인
        $this->assertDatabaseHas('admin_users', [
            'email' => 'newsuper@admin.com',
            'name' => 'New Super Admin',
            'type' => 'super',
            'is_active' => true,
            'is_verified' => true,
            'is_super_admin' => true,
        ]);

        // 슈퍼 관리자 계정 정보 표시 테스트
        $this->artisan('admin:user-delete', [
                '--email' => 'newsuper@admin.com',
                '--force' => true,
            ])
            ->expectsOutput('==== 관리자 계정 삭제 ====')
            ->expectsOutput('------------------------------')
            ->expectsOutput('[admin_users] 테이블 정보:')
            ->expectsOutput('  이름: New Super Admin')
            ->expectsOutput('  이메일: newsuper@admin.com')
            ->expectsOutput('  유형: super')
            ->expectsOutput('  활성화: 예')
            ->expectsOutput('  이메일 인증: 완료')
            ->expectsOutput('  슈퍼 관리자: 예')
            ->expectsOutput('------------------------------')
            ->expectsOutput('✅ 관리자가 성공적으로 삭제되었습니다.')
            ->assertExitCode(0);
    }
}
