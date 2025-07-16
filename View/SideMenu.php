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

    public function render()
    {

        $menuService = app('admin.side-menu.service');

        // 현재 URL을 기반으로 활성 메뉴 설정
        $menuService->setActiveMenuByUrl();

        $topMenu = $menuService->getTopMenu();
        $bottomMenu = $menuService->getBottomMenu();

        //dump($topMenu);
        //dd($bottomMenu);

        return view('jiny-admin::components.side-menu', [
            'topMenu' => $topMenu,
            'bottomMenu' => $bottomMenu,
            'menuService' => $menuService
        ]);
    }
}
