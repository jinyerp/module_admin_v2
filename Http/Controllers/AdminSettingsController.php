<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends Controller
{
    /**
     * 환경설정 메인 페이지
     */
    public function index()
    {
        $settings = config('admin.settings');

        return view('jiny-admin::settings.index', compact('settings'));
    }

    /**
     * 인증 설정 관리
     */
    public function auth()
    {
        $authSettings = config('admin.settings.auth');

        return view('jiny-admin::settings.auth', compact('authSettings'));
    }

    /**
     * 인증 설정 업데이트
     */
    public function updateAuth(Request $request)
    {
        $request->validate([
            'login.max_attempts' => 'required|integer|min:1|max:10',
            'login.lockout_time' => 'required|integer|min:60|max:3600',
            'login.remember_me' => 'boolean',
            'regist.enable' => 'boolean',
            'regist.email_verification' => 'boolean',
            'regist.auto_approve' => 'boolean',
            'regist.terms_required' => 'boolean',
            'password.min_length' => 'required|integer|min:6|max:20',
            'password.max_length' => 'required|integer|min:10|max:255',
            'password.require_lowercase' => 'boolean',
            'password.require_uppercase' => 'boolean',
            'password.require_numbers' => 'boolean',
            'password.require_special_chars' => 'boolean',
            'password.expire_days' => 'required|integer|min:30|max:365',
            'password.history_count' => 'required|integer|min:0|max:10',
            'password.expiry_warning_days' => 'required|integer|min:1|max:30',
            'dormant.enable' => 'boolean',
            'dormant.days' => 'required|integer|min:30|max:1095',
            'dormant.auto_restore' => 'boolean',
            'login_disable.enable' => 'boolean',
            'login_disable.reason' => 'nullable|string|max:500',
        ]);

        $this->updateSettings('auth', $request->all());

        return redirect()->route('admin.settings.auth')
            ->with('success', '인증 설정이 업데이트되었습니다.');
    }

    /**
     * 데이터베이스 설정 관리
     */
    public function database()
    {
        $dbSettings = config('admin.settings.database');

        return view('jiny-admin::settings.database', compact('dbSettings'));
    }

    /**
     * 데이터베이스 설정 업데이트
     */
    public function updateDatabase(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sqlite,mysql,pgsql',
            'connection.host' => 'required|string',
            'connection.port' => 'required|integer|min:1|max:65535',
            'connection.database' => 'required|string',
            'connection.username' => 'required|string',
            'connection.password' => 'nullable|string',
            'mode' => 'required|in:single,master-slave',
        ]);

        $this->updateSettings('database', $request->all());

        return redirect()->route('admin.settings.database')
            ->with('success', '데이터베이스 설정이 업데이트되었습니다.');
    }

    /**
     * 메일 설정 관리
     */
    public function mail()
    {
        $mailSettings = config('admin.settings.mail');

        return view('jiny-admin::settings.mail', compact('mailSettings'));
    }

    /**
     * 메일 설정 업데이트
     */
    public function updateMail(Request $request)
    {
        $request->validate([
            'MAIL_MAILER' => 'required|in:smtp,mail,sendmail',
            'MAIL_HOST' => 'required|string',
            'MAIL_PORT' => 'required|integer|min:1|max:65535',
            'MAIL_ENCRYPTION' => 'required|in:tls,ssl,null',
            'MAIL_USERNAME' => 'required|string',
            'MAIL_PASSWORD' => 'required|string',
            'MAIL_FROM_ADDRESS' => 'required|email',
            'MAIL_FROM_NAME' => 'required|string|max:100',
        ]);

        $this->updateSettings('mail', $request->all());

        return redirect()->route('admin.settings.mail')
            ->with('success', '메일 설정이 업데이트되었습니다.');
    }

    /**
     * 시스템 설정 관리
     */
    public function system()
    {
        $systemSettings = config('admin.settings.system');

        return view('jiny-admin::settings.system', compact('systemSettings'));
    }

    /**
     * 시스템 설정 업데이트
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'locale' => 'required|string|max:10',
            'debug' => 'boolean',
        ]);

        $this->updateSettings('system', $request->all());

        return redirect()->route('admin.settings.system')
            ->with('success', '시스템 설정이 업데이트되었습니다.');
    }

    /**
     * 데이터베이스 연결 테스트
     */
    public function testConnection(Request $request)
    {
        $type = $request->input('type');
        $connection = $request->input('connection');

        try {
            // 데이터베이스 연결 테스트
            $config = [
                'driver' => $type,
                'host' => $connection['host'],
                'port' => $connection['port'],
                'database' => $connection['database'],
                'username' => $connection['username'],
                'password' => $connection['password'],
            ];

            // 임시 연결 생성
            $connection = \DB::connection($config);
            $connection->getPdo();

            return response()->json(['success' => true, 'message' => '연결이 성공했습니다.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '연결에 실패했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * 메일 테스트
     */
    public function testMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $email = $request->input('email');

            // 테스트 메일 발송
            \Mail::raw('이것은 테스트 메일입니다.', function($message) use ($email) {
                $message->to($email)
                        ->subject('테스트 메일')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            return response()->json(['success' => true, 'message' => '테스트 메일이 성공적으로 발송되었습니다.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '메일 발송에 실패했습니다: ' . $e->getMessage()]);
        }
    }

    /**
     * 설정 파일 업데이트
     */
    private function updateSettings($section, $data)
    {
        $settingsPath = config_path('admin/settings.php');
        $settings = include $settingsPath;

        // 섹션별 설정 업데이트
        if ($section === 'auth') {
            $settings['auth'] = array_merge($settings['auth'] ?? [], $data);
        } elseif ($section === 'database') {
            $settings['database'] = array_merge($settings['database'] ?? [], $data);
        } elseif ($section === 'mail') {
            $settings['mail'] = array_merge($settings['mail'] ?? [], $data);
        } elseif ($section === 'system') {
            $settings['system'] = array_merge($settings['system'] ?? [], $data);
        }

        // 설정 파일 저장
        $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
        File::put($settingsPath, $content);

        // 캐시 클리어
        Cache::flush();
        Config::clearResolvedInstances();
    }
}
