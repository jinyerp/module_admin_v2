<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class AdminSettingMailController extends Controller
{
    private $route;

    public function __construct()
    {
        $this->route = 'admin.setting.mail';
    }

    /**
     * 메일 설정 페이지 표시
     */
    public function index()
    {
        // config/admin/mail.php에서 설정값을 읽어옴, 없으면 기본값
        $mailSettings = config('admin.mail') ?? [
                'MAIL_MAILER' => 'smtp',
                'MAIL_HOST' => 'smtp.mailgun.org',
                'MAIL_PORT' => 587,
                'MAIL_USERNAME' => '',
                'MAIL_PASSWORD' => '',
                'MAIL_ENCRYPTION' => 'tls',
                'MAIL_FROM_ADDRESS' => 'hello@example.com',
                'MAIL_FROM_NAME' => 'Example',
            ];

        return view('jiny-admin::settings.mail', 
        [
            'mailSettings' => $mailSettings,
            'route' => $this->route,
        ]);
    }

    /**
     * 메일 설정 저장
     */
    public function update(Request $request)
    {
        $data = $request->only([
            'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'
        ]);

        // config/admin/mail.php 파일에 저장
        $configPath = config_path('admin/mail.php');
        if (!file_exists(dirname($configPath))) {
            mkdir(dirname($configPath), 0777, true);
        }
        $content = "<?php\nreturn " . var_export($data, true) . ";\n";
        File::put($configPath, $content);

        // config 캐시 무효화
        // Artisan::call('config:clear');
        // Artisan::call('config:cache');

        return response()->json([
            'success' => true,
            'message' => '메일 설정이 저장되었습니다.'
        ]);
    }

    /**
     * 메일 설정 테스트
     */
    public function test(Request $request)
    {
        // 현재 로그인한 관리자 이메일 사용
        $admin = auth('admin')->user();
        if (!$admin || !$admin->email) {
            return response()->json([
                'success' => false,
                'message' => '로그인된 관리자의 이메일 정보를 찾을 수 없습니다.'
            ], 400);
        }
        $testEmail = $admin->email;

        // config/admin/mail.php 값 읽기
        $adminMailConfig = config('admin.mail') ?? [
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.mailgun.org', 
            'MAIL_PORT' => 587,
            'MAIL_USERNAME' => '',
            'MAIL_PASSWORD' => '',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'hello@example.com',
            'MAIL_FROM_NAME' => 'Example',
        ];

        // 런타임 메일 설정 적용
        config([
            'mail.default' => $adminMailConfig['MAIL_MAILER'],
            'mail.mailers.smtp.host' => $adminMailConfig['MAIL_HOST'],
            'mail.mailers.smtp.port' => $adminMailConfig['MAIL_PORT'],
            'mail.mailers.smtp.username' => $adminMailConfig['MAIL_USERNAME'],
            'mail.mailers.smtp.password' => $adminMailConfig['MAIL_PASSWORD'],
            'mail.mailers.smtp.encryption' => $adminMailConfig['MAIL_ENCRYPTION'] === 'null' ? null : $adminMailConfig['MAIL_ENCRYPTION'],
            'mail.from.address' => $adminMailConfig['MAIL_FROM_ADDRESS'],
            'mail.from.name' => $adminMailConfig['MAIL_FROM_NAME'],
        ]);

        try {
            // 테스트 메일 정보 직접 생성
            $subject = '[테스트] 메일 설정 테스트';
            $content = '이것은 메일 설정 테스트 이메일입니다.<br><br>발송 시간: '.now()->format('Y-m-d H:i:s').'<br>메일 드라이버: '.($adminMailConfig['MAIL_MAILER'] ?? 'unknown').'<br>발신자: '.($adminMailConfig['MAIL_FROM_ADDRESS'] ?? 'unknown').'<br>SMTP 호스트: '.($adminMailConfig['MAIL_HOST'] ?? 'unknown').'<br><br>이 메일이 정상적으로 수신되면 메일 설정이 올바르게 작동하고 있습니다.';
            $fromEmail = $adminMailConfig['MAIL_FROM_ADDRESS'];
            $fromName = $adminMailConfig['MAIL_FROM_NAME'];
            $toEmail = $testEmail;

            // EmailMailable 사용
            
            \Mail::to($toEmail)->send(new \Jiny\Admin\Mail\EmailMailable(
                $subject, $content, $fromEmail, $fromName, $toEmail
            ));

            return response()->json([
                'success' => true,
                'message' => '테스트 이메일이 성공적으로 발송되었습니다. 수신함을 확인해주세요.'
            ]);
        } catch (\Exception $e) {
            \Log::error('메일 테스트 실패: ' . $e->getMessage(), [
                'exception' => $e,
                'mail_config' => config('mail'),
                'test_email' => $testEmail
            ]);

            return response()->json([
                'success' => false,
                'message' => '테스트 이메일 발송 실패: ' . $e->getMessage()
            ], 500);
        }
    }
}
