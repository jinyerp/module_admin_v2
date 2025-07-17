<?php
namespace Jiny\Admin\View;

use Illuminate\View\Component;
use Jiny\Admin\Services\AdminSideMenuService;

class SideMenu extends Component
{
    public $sidebarBgColor = '#101828';
    public $sidebarTextColor;
    public $sidebarActiveBgColor;
    public $sidebarActiveTextColor;
    public $sidebarHoverBgColor;
    public $sidebarHoverTextColor;

    /**
     * 메뉴 JSON 파일 경로
     */
    protected $menuPath;

    /**
     * 생성자
     * @param string|null $menuPath 메뉴 JSON 파일 경로
     */
    public function __construct($menuPath = null)
    {
        // 메뉴 파일 경로 설정
        if($menuPath) {
            $this->menuPath = $menuPath;
        } else {
            // 기본 메뉴 파일 경로 설정
            $this->menuPath = __DIR__ . '/../../resources/menus/admin-sidebar.json';
        }
    }

    public function render()
    {
        //$path = app('jiny-admin') . $this->menuPath;
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $this->menuPath);
        $menuService = new AdminSideMenuService($path);

        // 현재 URL을 기반으로 활성 메뉴 설정
        $menuService->setActiveMenuByUrl();

        $topMenu = $menuService->getTopMenu();
        $bottomMenu = $menuService->getBottomMenu();

        return view('jiny-admin::components.side-menu', [
            'topMenu' => $topMenu,
            'bottomMenu' => $bottomMenu,
            'menuService' => $menuService
        ]);
    }
}
