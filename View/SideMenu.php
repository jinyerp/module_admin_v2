<?php
namespace Jiny\Admin\View;

use Illuminate\View\Component;

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

        // if($menuPath) {
        //     $this->menuPath = app('jiny-admin') . $menuPath;
        // } else {
        //     // 기본 메뉴 파일 경로 설정
        //     $this->menuPath = __DIR__ . '/../../resources/menus/admin-sidebar.json';
        // }

        // // 메뉴 서비스에 경로 설정
        // app('admin.side-menu.service', [
        //     'menuPath' => $this->menuPath
        // ]);

        $this->menuPath = __DIR__ . '/../../resources/menus/admin-sidebar.json';

    }

    public function render()
    {

        $menuService = app('admin.side-menu.service');

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
