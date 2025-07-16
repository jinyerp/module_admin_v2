<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

class Backdrop extends Component
{
    /**
     * 모달의 id
     */
    public $id;

    /**
     * 모달의 크기 (sm, md, lg, xl, full)
     */
    public $size;

    /**
     * 생성자
     */
    public function __construct($id = null, $size = 'md')
    {
        $this->id = $id;
        $this->size = $size;
    }

    /**
     * 컴포넌트 뷰 반환
     */
    public function render()
    {
        return view('jiny-admin::components.backdrop');
    }
} 