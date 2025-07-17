@extends('jiny-admin::layouts.admin.main')

@
{{-- 리소스 index 페이지 --}}
@section('content')

    {{-- 페이지 진입시 성공 메시지 제거 --}}
    <script>
    if (localStorage.getItem('adminUserEditSuccess') === '1') {
        localStorage.removeItem('adminUserEditSuccess');
        location.reload();
    }
    // show → edit 경로에서 남아있을 수 있는 플래그 초기화
    localStorage.removeItem('adminUserFromShow');
    </script>

    @csrf {{-- ajax 통신을 위한 토큰 --}}

    @yield('heading')

    @yield('filters')
   
    @yield('table')

    {{-- 선택삭제 알림 --}}
    @includeIf('jiny-admin::bulk-delete')

    {{-- 페이지네이션 --}}
    @includeIf('jiny-admin::pagenation')

    {{-- 삭제 확인 백드롭 및 레이어 --}}
    @includeIf('jiny-admin::row-delete')

    {{-- 디버그 모드 --}}
    @includeIf('jiny-admin::debug')

@endsection
