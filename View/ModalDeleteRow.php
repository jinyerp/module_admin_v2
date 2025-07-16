<?php

namespace Jiny\Admin\View;

use Illuminate\View\Component;

/**
 * 수정 페이지에서 삭제 레이어 팝업처리
 */
class ModalDeleteRow extends Component
{
    public $url;
    public $randKey;

    public function __construct($url)
    {
        $this->url = $url;
        $this->randKey = $this->getRandKey();
    }


    public function getRandKey()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = 10;
        $randKey = '';

        for ($i = 0; $i < $length; $i++) {
            $randKey .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $randKey;
    }

    public function render()
    {
        return view('jiny-admin::components.modal-delete');
    }
}
