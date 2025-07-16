<?php

namespace Jiny\Admin\Http\Controllers\Menu;

use Jiny\Admin\Services\Menu\AdminSideMenuService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminSideMenuController extends Controller
{
    protected $menuService;

    public function __construct(AdminSideMenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * 메뉴 목록을 표시합니다.
     */
    public function index()
    {
        $menuData = $this->menuService->getMenuData();
        return view('admin.menu.index', compact('menuData'));
    }

    /**
     * 메뉴를 편집합니다.
     */
    public function edit()
    {
        $menuData = $this->menuService->getMenuData();
        //dd($menuData);
        return view('admin.menu.edit', compact('menuData'));
    }

    /**
     * 메뉴를 업데이트합니다.
     */
    public function update(Request $request)
    {
        // JSON 요청 처리
        if ($request->isJson()) {
            $request->validate([
                'menu_data' => 'required|array'
            ]);

            $menuData = $request->input('menu_data');
        } else {
            // 기존 폼 요청 처리 (하위 호환성)
            $request->validate([
                'menu_data' => 'required|json'
            ]);

            $menuData = json_decode($request->menu_data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['menu_data' => '유효하지 않은 JSON 형식입니다.']);
            }
        }

        // URL 슬래시 정규화
        $menuData = $this->normalizeUrls($menuData);

        $this->menuService->saveMenuData($menuData);

        if ($request->isJson()) {
            return response()->json([
                'success' => true,
                'message' => '메뉴가 성공적으로 업데이트되었습니다.'
            ]);
        }

        return redirect()->route('admin.system.menu.index')
            ->with('success', '메뉴가 성공적으로 업데이트되었습니다.');
    }

    /**
     * 메뉴 데이터를 JSON으로 반환합니다.
     */
    public function getMenuData()
    {
        return response()->json($this->menuService->getMenuData());
    }

    /**
     * 메뉴를 다시 로드합니다.
     */
    public function reload()
    {
        $this->menuService->reloadMenuData();
        return response()->json(['message' => '메뉴가 다시 로드되었습니다.']);
    }

    /**
     * URL 슬래시를 정규화합니다.
     */
    private function normalizeUrls($data)
    {
        $normalizeUrl = function ($url) {
            if (is_string($url)) {
                // 이스케이프된 슬래시를 일반 슬래시로 변환
                return str_replace('\\/', '/', $url);
            }
            return $url;
        };

        $processMenu = function ($menuItems) use (&$processMenu, $normalizeUrl) {
            if (is_array($menuItems)) {
                foreach ($menuItems as &$item) {
                    if (isset($item['url'])) {
                        $item['url'] = $normalizeUrl($item['url']);
                    }
                    if (isset($item['children']) && is_array($item['children'])) {
                        $item['children'] = $processMenu($item['children']);
                    }
                }
            }
            return $menuItems;
        };

        foreach ($data as $key => $value) {
            $data[$key] = $processMenu($value);
        }

        return $data;
    }
}
