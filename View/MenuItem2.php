<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class MenuItem2 extends Component
{
    public $item;
    public $depth;
    public $menuService;

    public function __construct($item, $depth = 1, $menuService = null)
    {
        $this->item = $item;
        $this->depth = $depth;
        $this->menuService = $menuService;
    }

    public function render()
    {
        return view('jiny-admin::components.menu-item2', [
            'item' => $this->item,
            'depth' => $this->depth,
            'menuService' => $this->menuService
        ]);
    }
}
