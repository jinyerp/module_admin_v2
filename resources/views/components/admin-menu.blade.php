@php
    $menuService = app(\Jiny\Admin\Service\Admin\AdminMenuService::class);
    $menu = $menuService->getSortedMenu();
    $currentUrl = request()->url();
@endphp

<style>
/* 스크롤바 전체 배경 */
.admin-menu-scroll {
    background: #101828 !important;
}
/* 스크롤바 트랙(배경) */
.admin-menu-scroll::-webkit-scrollbar-track {
    background: #101828 !important;
}
/* 스크롤바 바(thumb) */
.admin-menu-scroll::-webkit-scrollbar-thumb {
    background: #2d3748 !important;
    border-radius: 2px !important;
}
/* 스크롤바 두께 */
.admin-menu-scroll::-webkit-scrollbar {
    width: 3px !important;
    background: #101828 !important;
}
/* 파이어폭스용 */
.admin-menu-scroll {
    scrollbar-width: thin;
    scrollbar-color: #2d3748 #101828;
}
</style>

<nav style="background: #101828;">
  <ul class="admin-menu-scroll" style="max-height: 600px; overflow-y: auto; background: #101828;">
    <!-- 메뉴 항목들 -->
  </ul>
</nav>
