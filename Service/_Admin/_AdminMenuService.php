<?php

namespace Jiny\Admin\Service\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class AdminMenuService
{
    protected $menuFile;
    protected $cacheKey = 'admin_menu';

    public function __construct()
    {
        $this->menuFile = resource_path('jiny/admin/resources/menu/admin-menu.json');
    }

    /**
     * 메뉴 데이터를 로드합니다.
     */
    public function loadMenu()
    {
        return Cache::remember($this->cacheKey, 3600, function () {
            if (File::exists($this->menuFile)) {
                $content = File::get($this->menuFile);
                return json_decode($content, true);
            }
            return ['menu' => []];
        });
    }

    /**
     * 메뉴를 정렬합니다.
     */
    public function getSortedMenu()
    {
        $menuData = $this->loadMenu();
        $menu = $menuData['menu'] ?? [];

        // 메인 메뉴 정렬
        usort($menu, function ($a, $b) {
            return ($a['order'] ?? 999) - ($b['order'] ?? 999);
        });

        // 서브 메뉴 정렬
        foreach ($menu as &$item) {
            if (isset($item['children']) && is_array($item['children'])) {
                usort($item['children'], function ($a, $b) {
                    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
                });
            }
        }

        return $menu;
    }

    /**
     * 현재 활성 메뉴를 찾습니다.
     */
    public function getActiveMenu($currentUrl)
    {
        $menu = $this->getSortedMenu();
        return $this->findActiveMenu($menu, $currentUrl);
    }

    /**
     * 재귀적으로 활성 메뉴를 찾습니다.
     */
    protected function findActiveMenu($menu, $currentUrl)
    {
        foreach ($menu as $item) {
            if (isset($item['url']) && $this->isUrlMatch($item['url'], $currentUrl)) {
                return $item;
            }

            if (isset($item['children'])) {
                $activeChild = $this->findActiveMenu($item['children'], $currentUrl);
                if ($activeChild) {
                    return $activeChild;
                }
            }
        }

        return null;
    }

        /**
     * URL이 일치하는지 확인합니다.
     */
    public function isUrlMatch($menuUrl, $currentUrl)
    {
        $menuUrl = rtrim($menuUrl, '/');
        $currentUrl = rtrim($currentUrl, '/');

        return $menuUrl === $currentUrl || strpos($currentUrl, $menuUrl) === 0;
    }

    /**
     * 메뉴 캐시를 클리어합니다.
     */
    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * 메뉴 데이터를 저장합니다.
     */
    public function saveMenu($menuData)
    {
        $directory = dirname($this->menuFile);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($this->menuFile, json_encode($menuData, JSON_PRETTY_PRINT));
        $this->clearCache();
    }
}
