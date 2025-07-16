<?php

namespace Jiny\Admin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

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
            ]);
        }

        // 커스텀 미들웨어 등록
        $router = $this->app['router'];
        $router->aliasMiddleware('admin:auth', \Jiny\Admin\Http\Middleware\AdminAuth::class);

    }

}
