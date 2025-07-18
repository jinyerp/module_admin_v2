<?php
namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminResourceController extends Controller
{

    protected $route;

    public function __construct()
    {
        $this->route = 'admin.admin.resources.';
    }

    public function AjaxIndex(Request $request)
    {
        return $this->index($request);
    }

    public function AjaxCreate(Request $request)
    {
        return $this->create($request);
    }

    public function AjaxEdit(Request $request)
    {
        return $this->edit($request);
    }

    public function AjaxStore(Request $request)
    {
        return $this->store($request);
    }

    public function AjaxUpdate(Request $request)
    {
        return $this->update($request);
    }

    public function AjaxDestroy(Request $request)
    {
        return $this->destroy($request);
    }

    /**
     * 라우트 이름 추출
     */
    protected function getRouteName(Request $request)
    {
        $route = $request->route()->getName();
        $route = substr($route, 0, strrpos($route, '.')).".";
        return $route;
    }

    /**
     * 리퀘스트에서 filter_ 접두사가 붙은 파라미터를 추출합니다.
     */
    protected function getFilterParameters(Request $request)
    {
        $filters = [];
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'filter_') && !empty($value)) {
                $filters[substr($key, 7)] = $value;
            }
        }
        return $filters;
    }

}