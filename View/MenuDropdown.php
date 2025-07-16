<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class MenuDropdown extends Component
{
    public $id;
    public $active;

    /**
     * Create a new component instance.
     */
    public function __construct($id = null, $active = false)
    {
        $this->id = $id;
        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('jiny-admin::components.menu-dropdown', [
            'id' => $this->id,
            'active' => $this->active
        ]);
    }
}
