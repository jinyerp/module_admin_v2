@extends('jiny-admin::layouts.admin.main')

@section('title', '관리자 회원 정보 상세')
@section('description', '관리자 회원의 상세 정보를 확인하세요.')

@section('content')
    <div class="pt-2 pb-4">

        @yield('heading')

        <!-- 메시지 -->
        @includeIf('jiny-admin::layouts.crud.message')

        <!-- 에러 메시지 -->
        @includeIf('jiny-admin::layouts.crud.errors')

        @yield('show')
        
        @if(Route::has($route.'edit'))
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <x-ui::link-light href="{{ route($route.'index') }}">목록으로</x-ui::link-light>
            <x-ui::button-primary onclick="setShowEditFlagAndGoEdit()">수정</x-ui::button-primary>
        </div>
        @endif
        
    </div>
@endsection


@if(Route::has($route.'edit'))
<script>
function setShowEditFlagAndGoEdit() {
    localStorage.setItem('adminUserFromShow', '1');
    window.location.href = '{{ route($route.'edit', $item->id) }}';
}
</script>
@endif