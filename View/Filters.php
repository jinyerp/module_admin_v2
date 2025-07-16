<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class Filters extends Component
{
    public $route;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($route)
    {
        // 생성자
        $this->route = $route;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('jiny-admin::components.filters');
    }
} 