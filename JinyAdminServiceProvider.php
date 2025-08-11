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
        \Log::info('JinyAdminServiceProvider register() started');
        
        // 모듈: 라우트 설정
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes/admin.php');
        
        $this->loadViewsFrom(__DIR__.'/resources/views', $this->package);

        // 데이터베이스
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // admin config 파일 등록
        $this->mergeConfigFrom(
            __DIR__.'/config/settings.php', 'admin.settings'
        );

        // 패키지 루트 경로 등록
        $this->registerPackageRoot();

        // Module 시스템을 사용하여 패키지 정보 등록
        // 기존 app->instance 방식 대신 Module 시스템 사용
        \Jiny\Modules\App\Facades\Module::setPath('jiny-admin', __DIR__);
        \Jiny\Modules\App\Facades\Module::set('jiny-admin', 'name', 'Jiny Admin');
        \Jiny\Modules\App\Facades\Module::set('jiny-admin', 'version', '1.0.0');
        \Jiny\Modules\App\Facades\Module::set('jiny-admin', 'description', 'Jiny Admin Management System');
        \Jiny\Modules\App\Facades\Module::set('jiny-admin', 'author', 'Jiny Team');
        \Jiny\Modules\App\Facades\Module::set('jiny-admin', 'created_at', '2024-01-01 00:00:00');

        

        // Admin 가드와 프로바이더 완전 독립적 등록 (순서 중요)
        $this->registerAdminProvider();
        $this->registerAdminGuard();
        
        \Log::info('JinyAdminServiceProvider register() completed');
    }

    /**
     * Admin 프로바이더 등록
     */
    protected function registerAdminProvider(): void
    {
        \Log::info('Registering admin provider');
        
        // 프로바이더 등록
        $this->app['auth']->provider('admins', function ($app, array $config) {
            $provider = new \Illuminate\Auth\EloquentUserProvider(
                $app['hash'],
                \Jiny\Admin\App\Models\AdminUser::class
            );
            
            \Log::info('Admin provider created', [
                'provider_name' => 'admins',
                'model' => \Jiny\Admin\App\Models\AdminUser::class,
                'provider_class' => get_class($provider)
            ]);
            
            return $provider;
        });
        
        \Log::info('Admin provider registered successfully');
    }

    /**
     * Admin 가드 등록
     */
    protected function registerAdminGuard(): void
    {
        \Log::info('Registering admin guard');
        
        // config에 admin 가드 설정 추가
        $this->app['config']->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'admins',
        ]);
        
        // config에 admins 프로바이더 설정 추가
        $this->app['config']->set('auth.providers.admins', [
            'driver' => 'eloquent',
            'model' => \Jiny\Admin\App\Models\AdminUser::class,
        ]);
        
        \Log::info('Admin guard config set', [
            'guards' => $this->app['config']->get('auth.guards'),
            'providers' => $this->app['config']->get('auth.providers')
        ]);
        
        // 가드 확장 (기본 SessionGuard 사용)
        $this->app['auth']->extend('admin', function ($app, $name, array $config) {
            try {
                $provider = $app['auth']->createUserProvider($config['provider']);
                
                $guard = new \Illuminate\Auth\SessionGuard(
                    $name,
                    $provider,
                    $app['session.store']
                );
                
                \Log::info('Admin guard created successfully', [
                    'guard_name' => $name,
                    'provider' => $config['provider'],
                    'driver' => $config['driver'],
                    'provider_class' => get_class($provider),
                    'guard_class' => get_class($guard)
                ]);
                
                return $guard;
            } catch (\Exception $e) {
                \Log::error('Failed to create admin guard', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
        
        \Log::info('Admin guard registered successfully');
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
            return new \Jiny\Admin\App\Services\AdminSideMenuService();
        });

        // 콘솔 명령 등록
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Jiny\Admin\Console\Commands\AdminUsers::class,
                \Jiny\Admin\Console\Commands\AdminUserDelete::class,
                \Jiny\Admin\Console\Commands\AdminUserUnlock::class,
                \Jiny\Admin\Console\Commands\TableDrop::class,
                \Jiny\Admin\Console\Commands\TableFresh::class,
                \Jiny\Admin\App\Console\Commands\GeneratePerformanceLogsCommand::class,
            ]);
        }

        // Admin 미들웨어 등록 (가드 등록 후)
        $this->registerAdminMiddleware();

        // 플래그 이미지 publish 명령 등록
        // 사용법: php artisan vendor:publish --tag=jiny-admin-flags
        $this->publishes([
            __DIR__.'/resources/flags' => public_path('images/flags'),
        ], 'jiny-admin-flags');

        // 언어 관리 시스템 초기화
        $this->initializeLanguageSystem();
    }

    /**
     * Admin 미들웨어 등록
     */
    protected function registerAdminMiddleware(): void
    {
        $router = $this->app['router'];
        
        \Log::info('Admin middleware registration started');
        
        try {
            // Admin 인증 미들웨어 - 별칭 대신 클래스명 직접 사용
            $router->aliasMiddleware('admin.auth', 
                \Jiny\Admin\App\Http\Middleware\AdminAuth::class);
            
            // Admin 게스트 미들웨어 (로그인하지 않은 사용자만 접근)
            $router->aliasMiddleware('admin.guest', 
                \Jiny\Admin\App\Http\Middleware\AdminGuest::class);
            
            // Admin 2FA 미들웨어
            $router->aliasMiddleware('admin.2fa', 
                \Jiny\Admin\App\Http\Middleware\Admin2FA::class);
            
            // Admin 세션 트래커 미들웨어
            $router->aliasMiddleware('admin.session.tracker', 
                \Jiny\Admin\App\Http\Middleware\AdminSessionTracker::class);
            
            // Admin 권한 체크 미들웨어
            $router->aliasMiddleware('admin.permission', 
                \Jiny\Admin\App\Http\Middleware\CheckAdminPermission::class);
            
            \Log::info('All admin middlewares registered successfully');
            
        } catch (\Exception $e) {
            \Log::error('Failed to register admin middlewares: ' . $e->getMessage());
        }
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
     * Admin 패키지 정보 설정
     */
    protected function registerAdminPackageInfo(): void
    {
        try {
            // 새로운 파사드 형태 사용
            \Jiny\Modules\App\Facades\Module('admin')::setPath(__DIR__);
            \Jiny\Modules\App\Facades\Module('admin')::set('name', 'Admin Module');
            \Jiny\Modules\App\Facades\Module('admin')::set('version', '1.0.0');
            \Jiny\Modules\App\Facades\Module('admin')::set('description', 'Jiny Admin Management System');
            
            \Log::info('Admin package info registered successfully');
            
        } catch (\Exception $e) {
            \Log::error('Failed to register admin package info: ' . $e->getMessage());
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
