<?php

namespace Jiny\Admin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

use Jiny\Uikit\App\Services\ModuleManager;
use Jiny\Uikit\App\Facades\Module;
use Jiny\Uikit\App\Providers\ModuleServiceProvider;

class JinyAdminServiceProvider extends ServiceProvider
{
    protected $package = 'jiny-admin';

    /**
     * Register services.
     * admin guard/provider를 반드시 register()에서 동적으로 등록해야 artisan/웹 모두에서 정상 동작합니다.
     */
    public function register(): void
    {
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        // $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/routes/admin.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

         // 기존 config/auth.php 병합
        $this->mergeConfigFrom(
            __DIR__.'/config/auth.php', 'auth'
        );

        // admin config 파일 등록
        $this->mergeConfigFrom(
            __DIR__.'/config/settings.php', 'admin.settings'
        );

        // 패키지 루트 경로 등록
        $this->registerPackageRoot();

        // 패키지 절대 경로 저장
        // app->instance()는 Laravel 컨테이너에 싱글톤 인스턴스를 바인딩하는 메서드입니다.
        // 첫 번째 매개변수 'jiny-admin'은 컨테이너에서 사용할 키(식별자)입니다.
        // 두 번째 매개변수 __DIR__은 현재 파일의 디렉토리 경로를 바인딩합니다.
        // 이렇게 바인딩된 값은 app('jiny-admin')으로 어디서든 접근할 수 있습니다.
        $this->app->instance('jiny-admin', __DIR__);



        
    }

    /**
     * Bootstrap services.
     * boot()에서는 guard/provider 등록을 하지 않습니다.
     */
    public function boot(): void
    {
        // 뷰 컴포넌트 등록
        Blade::component('admin::menu-dropdown', \Jiny\Admin\View\MenuDropdown::class);
        Blade::component('admin::side-menu', \Jiny\Admin\View\SideMenu::class);
        Blade::component('admin::menu-title', \Jiny\Admin\View\MenuTitle::class);
        Blade::component('admin::menu-item', \Jiny\Admin\View\MenuItem::class);
        Blade::component('admin::menu-item2', \Jiny\Admin\View\MenuItem2::class);
        Blade::component('admin::menu-dropdown2', \Jiny\Admin\View\MenuDropdown2::class);
        Blade::component('admin::modal', \Jiny\Admin\View\Backdrop::class);

        // 뷰 네임스페이스 등록
        View::addNamespace('jiny.admin', __DIR__.'/resources/views');

        // Blade 컴포넌트 네임스페이스 등록 (x-admin::side-menu)
        Blade::componentNamespace('Jiny\\Admin\\View', 'admin');

        // 서비스 바인딩
        // AdminSideMenuService 싱글톤 등록
        $this->app->singleton('admin.side-menu.service', function($app) {
            return new \Jiny\Admin\Services\AdminSideMenuService();
        });

        // 콘솔 명령 등록
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Admin\Console\Commands\AdminUsers::class,
                \Jiny\Admin\Console\Commands\AdminUserDelete::class,
                \Jiny\Admin\Console\Commands\TableDrop::class,
                \Jiny\Admin\Console\Commands\TableFresh::class,
                \Jiny\Admin\App\Console\Commands\GeneratePerformanceLogsCommand::class,
            ]);
        }



        // 커스텀 미들웨어 등록
        $router = $this->app['router'];
        $router->aliasMiddleware('admin:auth', 
            \Jiny\Admin\App\Http\Middleware\AdminAuth::class);
        $router->aliasMiddleware('admin:guest', 
            \Jiny\Admin\App\Http\Middleware\AdminGuest::class);
        $router->aliasMiddleware('admin:2fa', 
            \Jiny\Admin\App\Http\Middleware\Admin2FA::class);
        $router->aliasMiddleware('admin.session.tracker', 
            \Jiny\Admin\App\Http\Middleware\AdminSessionTracker::class);
        $router->aliasMiddleware('admin.permission', 
            \Jiny\Admin\App\Http\Middleware\CheckAdminPermission::class);

        // 플래그 이미지 publish 명령 등록
        // 사용법: php artisan vendor:publish --tag=jiny-admin-flags
        $this->publishes([
            __DIR__.'/resources/flags' => public_path('images/flags'),
        ], 'jiny-admin-flags');

        // 언어 관리 시스템 초기화
        $this->initializeLanguageSystem();

    }

    /**
     * 언어 관리 시스템 초기화
     */
    private function initializeLanguageSystem(): void
    {
        try {
            // App\Services\LanguageService가 존재하는지 확인
            if (class_exists('\App\Services\LanguageService')) {
                // 기본 언어가 없으면 초기화
                \App\Services\LanguageService::initializeDefaultLanguage();
                
                // 관리자 로케일 설정 (Config::set 대신 세션/캐시 사용)
                \App\Services\LanguageService::setLaravelLocaleFromDefaultLanguage();
            }
        } catch (\Exception $e) {
            // 데이터베이스 연결이 안 되거나 테이블이 없는 경우 무시
            \Log::info('언어 관리 시스템 초기화 중 오류 (정상적인 상황일 수 있음): ' . $e->getMessage());
        }
    }

    /**
     * 패키지 루트 경로 등록
     */
    protected function registerPackageRoot(): void
    {
        try {
            // 현재 패키지의 루트 경로를 Laravel 컨테이너에 등록
            // module() 메서드가 없는 경우를 대비하여 직접 컨테이너에 바인딩
            if (method_exists($this, 'module')) {
                $this->module()->setDir($this->package, __DIR__);
                
                // 디버깅: 등록 확인
                if ($this->module()->isRegistered($this->package)) {
                    // 등록 성공
                }
            } else {
                // module() 메서드가 없는 경우 기본적인 패키지 경로 등록
                $this->app->instance('jiny-admin.path', __DIR__);
            }
        } catch (\Exception $e) {
            // 오류 처리
            error_log("Package root registration failed: " . $e->getMessage());
        }
    }

}
