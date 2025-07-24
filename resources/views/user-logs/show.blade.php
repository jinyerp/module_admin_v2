{{-- 사용자 로그 상세 --}}
@extends('jiny-admin::layouts.app')
@section('content')
    <h1>관리자 사용자 로그 상세</h1>
    @include('jiny-admin::user-logs.message')
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $userLog->id }}</td></tr>
        <tr><th>관리자ID</th><td>{{ $userLog->admin_user_id }}</td></tr>
        <tr><th>IP</th><td>{{ $userLog->ip_address }}</td></tr>
        <tr><th>상태</th><td>{{ $userLog->status }}</td></tr>
        <tr><th>메시지</th><td>{{ $userLog->message }}</td></tr>
        <tr><th>생성일시</th><td>{{ $userLog->created_at }}</td></tr>
    </table>
    <x-ui::button-primary href="{{ route($route.'edit', $userLog->id) }}">수정</x-ui::button-primary>
    <x-ui::button-light href="{{ route($route.'index') }}">목록</x-ui::button-light>
@endsection 