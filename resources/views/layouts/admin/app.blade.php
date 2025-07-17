<!DOCTYPE html>
<html lang="ko" class="h-full bg-white">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '관리자') | @yield('description', '관리자 페이지')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('css')
</head>

<body class="h-full">

    <div>
        {{-- 사이드바, 패키지의 resources 폴더 경로 설정 --}}
        <x-admin::side-menu 
            menu-path="{{app('jiny-admin').'/resources/menus/admin.json'}}">
        </x-admin::side-menu>

        {{-- 메인 컨텐츠 --}}
        @yield('main-content')
    </div>

    <!-- 순수 JS -->
    <script>
        // 모바일 사이드바 토글
        const openSidebarBtn = document.getElementById('openSidebarBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');
        const mobileSidebarContent = mobileSidebar.querySelector('.relative.mr-16');

        function openSidebar() {
            mobileSidebar.classList.remove('hidden');
            // 약간의 지연 후 애니메이션 시작
            setTimeout(() => {
                mobileSidebarContent.classList.remove('-translate-x-full');
            }, 10);
        }

        function closeSidebar() {
            mobileSidebarContent.classList.add('-translate-x-full');
            // 애니메이션 완료 후 숨김
            setTimeout(() => {
                mobileSidebar.classList.add('hidden');
            }, 300);
        }

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', openSidebar);
        }
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }
        if (mobileSidebarBackdrop) {
            mobileSidebarBackdrop.addEventListener('click', closeSidebar);
        }

        // ESC 키로 사이드바 닫기
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });
    </script>

    @stack('scripts')

</body>

</html>
