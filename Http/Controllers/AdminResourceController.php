<?php
namespace Jiny\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * AdminResourceController
 * this class is used to handle the admin resource requests
 * @package Jiny\Admin\Http\Controllers
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class AdminResourceController extends Controller
{
    protected $filterable = [];
    protected $validFilters = [];
    protected $route;

    public function __construct()
    {
        $this->route = 'admin.admin.resources.';
    }

    public function index(Request $request)
    {
        // 라우트 이름 추출
        $route = $this->getRouteName($request);

        $view = $this->_index($request);
        return $view->with('route', $route);
    }


    public function create(Request $request)
    {
        $route = $this->getRouteName($request);
        $view = $this->_create($request);
        return $view->with('route', $route);
    }


    public function edit(Request $request, $id)
    {
        $route = $this->getRouteName($request);
        $view = $this->_edit($request, $id);
        return $view->with('route', $route);
    }

    public function store(Request $request)
    {
        return $this->_store($request);
    }

    public function update(Request $request, $id)
    {
        return $this->_update($request, $id);
    }

    public function destroy(Request $request)
    {
        return $this->_destroy($request);
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

    /**
     * 필터 적용
     */
    protected function applyFilter($filters, $query, $likeFields)
    {
        foreach ($this->filterable as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                if (in_array($column, $likeFields)) {
                    $query->where($column, 'like', "%{$filters[$column]}%");
                } else {
                    $query->where($column, $filters[$column]);
                }
            }
        }

        // search는 or 조건
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function($q) use ($filters, $likeFields) {
                $first = true;
                foreach($likeFields as $field) {
                    if ($first) {
                        $q->where($field, 'like', "%{$filters['search']}%");
                        $first = false;
                    } else {
                        $q->orWhere($field, 'like', "%{$filters['search']}%");
                    }
                }
            });
        }

        return $query;
    }

}