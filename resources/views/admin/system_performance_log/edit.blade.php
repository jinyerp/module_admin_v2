@extends('jiny.admin::layouts.resource.main')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold mb-4">성능 로그 수정</h1>
    <a href="{{ route('admin.systems.performance-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">목록</a>
</div>
<form action="{{ route('admin.systems.performance-logs.update', $performanceLog->id) }}" method="POST" class="bg-white rounded shadow p-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label>메트릭명</label>
            <input type="text" name="metric_name" class="form-input w-full" value="{{ $performanceLog->metric_name }}" required>
        </div>
        <div>
            <label>타입</label>
            <select name="metric_type" class="form-input w-full" required>
                <option value="">선택하세요</option>
                @foreach($metricTypes as $key => $value)
                    <option value="{{ $key }}" @if($performanceLog->metric_type == $key) selected @endif>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>값</label>
            <input type="number" step="0.0001" name="value" class="form-input w-full" value="{{ $performanceLog->value }}" required>
        </div>
        <div>
            <label>단위</label>
            <input type="text" name="unit" class="form-input w-full" value="{{ $performanceLog->unit }}" required>
        </div>
        <div>
            <label>임계값</label>
            <input type="text" name="threshold" class="form-input w-full" value="{{ $performanceLog->threshold }}">
        </div>
        <div>
            <label>상태</label>
            <select name="status" class="form-input w-full" required>
                @foreach($statuses as $key => $value)
                    <option value="{{ $key }}" @if($performanceLog->status == $key) selected @endif>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>엔드포인트</label>
            <input type="text" name="endpoint" class="form-input w-full" value="{{ $performanceLog->endpoint }}" placeholder="/api/users">
        </div>
        <div>
            <label>HTTP 메서드</label>
            <select name="method" class="form-input w-full">
                <option value="">선택하세요</option>
                <option value="GET" @if($performanceLog->method == 'GET') selected @endif>GET</option>
                <option value="POST" @if($performanceLog->method == 'POST') selected @endif>POST</option>
                <option value="PUT" @if($performanceLog->method == 'PUT') selected @endif>PUT</option>
                <option value="DELETE" @if($performanceLog->method == 'DELETE') selected @endif>DELETE</option>
                <option value="PATCH" @if($performanceLog->method == 'PATCH') selected @endif>PATCH</option>
            </select>
        </div>
        <div>
            <label>사용자 에이전트</label>
            <input type="text" name="user_agent" class="form-input w-full" value="{{ $performanceLog->user_agent }}">
        </div>
        <div>
            <label>IP 주소</label>
            <input type="text" name="ip_address" class="form-input w-full" value="{{ $performanceLog->ip_address }}">
        </div>
        <div>
            <label>세션 ID</label>
            <input type="text" name="session_id" class="form-input w-full" value="{{ $performanceLog->session_id }}">
        </div>
        <div class="md:col-span-2">
            <label>추가데이터 (JSON)</label>
            <textarea name="additional_data" class="form-input w-full" rows="2">{{ json_encode($performanceLog->additional_data, JSON_UNESCAPED_UNICODE) }}</textarea>
        </div>
        <div>
            <label>측정시각</label>
            <input type="datetime-local" name="measured_at" class="form-input w-full" value="{{ $performanceLog->measured_at ? date('Y-m-d\TH:i', strtotime($performanceLog->measured_at)) : '' }}" required>
        </div>
    </div>
    <div class="mt-6">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">수정</button>
    </div>
</form>
@endsection 