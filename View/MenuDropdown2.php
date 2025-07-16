<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class MenuDropdown2 extends Component
{
    public $item;
    public $depth;
    public $menuService;

    public function __construct($item, $depth = 0, $menuService = null)
    {
        $this->item = $item;
        $this->depth = $depth;
        $this->menuService = $menuService;
    }

    public function render()
    {
        return view('jiny-admin::components.menu-dropdown2', [
            'item' => $this->item,
            'depth' => $this->depth,
            'menuService' => $this->menuService
        ]);
    }
}
