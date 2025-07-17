@extends('jiny.admin::layouts.admin.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">성능 로그 상세</h1>
    <a href="{{ route('admin.system.performance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>
<div class="bg-white rounded shadow p-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <dt>ID</dt><dd>{{ $log->id }}</dd>
        <dt>메트릭명</dt><dd>{{ $log->metric_name }}</dd>
        <dt>타입</dt><dd>{{ $log->metric_type }}</dd>
        <dt>값</dt><dd>{{ $log->value }}</dd>
        <dt>단위</dt><dd>{{ $log->unit }}</dd>
        <dt>임계값</dt><dd>{{ $log->threshold }}</dd>
        <dt>상태</dt><dd>{{ $log->status }}</dd>
        <dt>서버명</dt><dd>{{ $log->server_name }}</dd>
        <dt>컴포넌트</dt><dd>{{ $log->component }}</dd>
        <dt>추가데이터</dt><dd><pre>{{ json_encode($log->additional_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></dd>
        <dt>측정시각</dt><dd>{{ $log->measured_at }}</dd>
        <dt>생성일</dt><dd>{{ $log->created_at }}</dd>
    </dl>
</div>
@endsection 