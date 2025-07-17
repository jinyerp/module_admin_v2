<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class AdminSideMenuService
{
    protected $menuData;
    protected $menuPath;

    public function __construct($menuPath = null)
    {
        $this->menuPath = $menuPath ?? __DIR__ . '/../../../resources/menus/admin-sidebar.json';
        //dump($this->menuPath);
        $this->loadMenuData();
    }

    /**
     * 메뉴 데이터를 로드합니다.
     */
    protected function loadMenuData()
    {
        if (File::exists($this->menuPath)) {
            $jsonContent = File::get($this->menuPath);
            //dump($jsonContent);
            $this->menuData = json_decode($jsonContent, true);
        } else {
            //dump("file not found");
            $this->menuData = [];
        }
    }

    /**
     * 메뉴 데이터를 다시 로드합니다.
     */
    public function reloadMenuData()
    {
        $this->loadMenuData();
        return $this;
    }

    /**
     * 메뉴 데이터를 가져옵니다.
     */
    public function getMenuData()
    {
        return $this->menuData;
    }

    /**
     * 메뉴 배열에 url 키가 없으면 추가하고,
     * 빈 값이면 void(0)으로 보정
     */
    protected function ensureMenuUrlKey(&$items)
    {
        foreach ($items as &$item) {
            // if (!isset($item['url'])) {
            //     $item['url'] = '';
            // }
            // if ($item['url'] === '') {
            //     $item['url'] = 'javascript:void(0)';
            // }
            if (isset($item['children']) && is_array($item['children'])) {
                $this->ensureMenuUrlKey($item['children']);
            }
        }
    }

    /**
     * 상단 메뉴를 가져옵니다.
     */
    public function getTopMenu()
    {
        $keys = array_keys($this->menuData);
        $top = [];
        // 상단 메뉴 추출
        for ($i = 0; $i < count($keys) - 1; $i++) {
            $key = $keys[$i];
            foreach($this->menuData[$key] as $item) {
                $top[] = $item;
            }
        }

        // 메뉴 배열에 url 키가 없으면 추가하고, 빈 값이면 void(0)으로 보정
        $this->ensureMenuUrlKey($top);

        return $top;
    }

    /**
     * 하단 메뉴를 가져옵니다.
     * 마지막 키의 메뉴를 가져옵니다.
     */
    public function getBottomMenu()
    {
        //dump($this->menuData);
        $keys = array_keys($this->menuData);
        //dd($keys);
        $lastKey = $keys[count($keys)-1];
        //dump($lastKey);
        $bottom = $this->menuData[$lastKey];
        //dump($bottom);
        // $lastMenu = $this->menuData[$lastKey];
        // $count = count($keys);
        // $bottom = [];
        // if ($count === 2) {
        //     $bottom[] = [
        //         'label' => $keys[1],
        //         'children' => $this->menuData[$keys[1]] ?? []
        //     ];
        // } else if ($count > 2) {
        //     $bottom[] = [
        //         'label' => $keys[$count-1],
        //         'children' => $this->menuData[$keys[$count-1]] ?? []
        //     ];
        // }
        $this->ensureMenuUrlKey($bottom);
        return $bottom;
    }

    /**
     * 사용자 권한에 따라 메뉴를 필터링합니다.
     */
    public function filterMenuByPermission($menuItems)
    {
        // 임시로 권한 필터링 비활성화 (개발 중)
        // return $menuItems;

        // 인증되지 않은 사용자의 경우 권한이 없는 메뉴는 숨김
        if (!Auth::check()) {
            $menuItems = array_filter($menuItems, function ($item) {
                return !isset($item['permission']);
            });
        } else {
            $menuItems = array_filter($menuItems, function ($item) {
                // 권한이 설정되지 않은 경우 표시
                if (!isset($item['permission'])) {
                    return true;
                }
                // 권한 체크
                return Auth::user()->can($item['permission']);
            });
        }
        // children이 있으면 재귀적으로 필터링
        foreach ($menuItems as &$item) {
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->filterMenuByPermission($item['children']);
            }
        }
        return $menuItems;
    }

    /**
     * 현재 활성 메뉴를 설정합니다.
     */
    public function setActiveMenu($menuId)
    {
        if (isset($this->menuData['top_menu'])) {
            foreach ($this->menuData['top_menu'] as &$menu) {
                $menu['active'] = ($menu['id'] === $menuId);
            }
        }
    }

    /**
     * 현재 URL을 기반으로 활성 메뉴를 설정합니다.
     */
    public function setActiveMenuByUrl($currentUrl = null)
    {
        if ($currentUrl === null) {
            $currentUrl = request()->url();
        }

        $this->setActiveMenuRecursive($currentUrl);
        return $this;
    }

    /**
     * 재귀적으로 메뉴를 순회하며 현재 URL과 일치하는 메뉴를 활성화합니다.
     */
    protected function setActiveMenuRecursive($currentUrl)
    {
        foreach ($this->menuData as $key => &$section) {
            if (is_array($section)) {
                foreach ($section as &$item) {
                    if (isset($item['url']) && $item['url'] === $currentUrl) {
                        $item['active'] = true;
                    } elseif (isset($item['children']) && is_array($item['children'])) {
                        $this->setActiveMenuRecursiveChildren($item['children'], $currentUrl);
                        // 자식 중에 활성 메뉴가 있으면 부모도 활성화
                        if ($this->hasActiveChild($item['children'])) {
                            $item['active'] = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * 자식 메뉴들을 재귀적으로 순회하며 활성 메뉴를 설정합니다.
     */
    protected function setActiveMenuRecursiveChildren(&$children, $currentUrl)
    {
        foreach ($children as &$item) {
            if (isset($item['url']) && $item['url'] === $currentUrl) {
                $item['active'] = true;
            } elseif (isset($item['children']) && is_array($item['children'])) {
                $this->setActiveMenuRecursiveChildren($item['children'], $currentUrl);
                // 자식 중에 활성 메뉴가 있으면 부모도 활성화
                if ($this->hasActiveChild($item['children'])) {
                    $item['active'] = true;
                }
            }
        }
    }

    /**
     * 자식 메뉴 중에 활성 메뉴가 있는지 확인합니다.
     */
    protected function hasActiveChild($children)
    {
        foreach ($children as $child) {
            if (isset($child['active']) && $child['active']) {
                return true;
            }
            if (isset($child['children']) && is_array($child['children'])) {
                if ($this->hasActiveChild($child['children'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 메뉴 데이터를 JSON 파일에 저장합니다.
     */
    public function saveMenuData($data)
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put($this->menuPath, $jsonContent);
        $this->menuData = $data;
        return $this;
    }

    /**
     * 아이콘 SVG를 가져옵니다.
     */
    public function getIconSvg($iconName)
    {
        $icons = [
            'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
            'folder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
            'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />',
            'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
            'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />',
            'user-group' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />',
            'lock-closed' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />',
            'wrench-screwdriver' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-2.484 2.525m-9.348 9.348L3 21l2.25-2.25L9 15l-2.25 2.25L3 21m0 0h18m0 0-3-3m3 3-3-3" />',
            'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />'
        ];

        return $icons[$iconName] ?? '';
    }
}
