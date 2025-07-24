{{-- 사용자 로그 수정 --}}
@extends('jiny-admin::layouts.app')
@section('content')
    <h1>관리자 사용자 로그 수정</h1>
    @include('jiny-admin::user-logs.errors')
    <form action="{{ route($route.'update', $userLog->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>관리자ID</label>
            <input type="text" name="admin_user_id" class="form-control" value="{{ old('admin_user_id', $userLog->admin_user_id) }}" required>
        </div>
        <div class="form-group">
            <label>IP</label>
            <input type="text" name="ip_address" class="form-control" value="{{ old('ip_address', $userLog->ip_address) }}">
        </div>
        <div class="form-group">
            <label>상태</label>
            <select name="status" class="form-control" required>
                <option value="success" @if(old('status', $userLog->status)=='success') selected @endif>성공</option>
                <option value="fail" @if(old('status', $userLog->status)=='fail') selected @endif>실패</option>
            </select>
        </div>
        <div class="form-group">
            <label>메시지</label>
            <input type="text" name="message" class="form-control" value="{{ old('message', $userLog->message) }}">
        </div>
        <button type="submit" class="btn btn-primary">수정</button>
        <x-ui::button-light href="{{ route($route.'index') }}">목록</x-ui::button-light>
    </form>
@endsection 