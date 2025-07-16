<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class TableStripe extends Component
{
    public function render()
    {
        return view('jiny-admin::components.table-stripe');
    }
}
